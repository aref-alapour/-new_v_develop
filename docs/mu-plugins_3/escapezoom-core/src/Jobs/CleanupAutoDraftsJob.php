<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Jobs;

use EscapeZoom\Core\Modules\Games\Models\AdvanceLog;

/**
 * پاکسازی ردیف‌های auto-draft در wp_posts (و postmeta مرتبط) که قدیمی‌تر از ۲۴ ساعت هستند.
 * فقط در Job شبانه اجرا می‌شود (لایه ۳). Idempotent.
 */
final class CleanupAutoDraftsJob
{
    public const JOB_NAME = 'CleanupAutoDraftsJob';

    private const HOURS_OLD = 24;

    public function run(): void
    {
        global $wpdb;

        $cutoff = gmdate('Y-m-d H:i:s', time() - (self::HOURS_OLD * 3600));

        try {
            $wpdb->query('START TRANSACTION');

            $ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_status = 'auto-draft' AND post_modified < %s",
                    $cutoff
                )
            );

            if ($ids !== null && $ids !== []) {
                $ids = array_map('absint', $ids);
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->postmeta} WHERE post_id IN ($placeholders)",
                        ...$ids
                    )
                );
                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM {$wpdb->posts} WHERE ID IN ($placeholders)",
                        ...$ids
                    )
                );
            }

            $wpdb->query('COMMIT');
            if ($wpdb->last_error !== '') {
                throw new \RuntimeException('CleanupAutoDraftsJob: ' . $wpdb->last_error);
            }
        } catch (\Throwable $e) {
            $wpdb->query('ROLLBACK');
            $this->logFailure($e);
            throw $e;
        }
    }

    private function logFailure(\Throwable $e): void
    {
        $message = $e->getMessage();
        if (strlen($message) > 2048) {
            $message = substr($message, 0, 2045) . '...';
        }
        AdvanceLog::create([
            'request_url'  => $message,
            'source_page'  => self::JOB_NAME,
            'duration'     => null,
            'log_time'     => new \DateTimeImmutable('now'),
            'request_type' => 'job_failure',
            'action_name'  => self::JOB_NAME,
        ]);
    }
}
