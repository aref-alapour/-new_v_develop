<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Jobs;

use EscapeZoom\Core\Modules\Games\Models\AdvanceLog;
use EscapeZoom\Core\Modules\Games\Models\Slot;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Releases expired temporary (pending) slots so capacity is available again.
 * Rule 22: run every 1–2 minutes. In this schema "available" = no row; release = DELETE.
 * Idempotent: running twice only deletes rows that still match (no duplicate effect).
 */
final class ExpirePendingSlotsJob
{
    public const JOB_NAME = 'ExpirePendingSlotsJob';

    public function run(): void
    {
        try {
            $now = new \DateTimeImmutable('now');
            Capsule::connection('default')->transaction(function () use ($now): void {
                $ids = Slot::query()
                    ->where('status', Slot::STATUS_PENDING)
                    ->whereNotNull('pending_expires_at')
                    ->where('pending_expires_at', '<', $now)
                    ->pluck('id')
                    ->all();
                if ($ids !== []) {
                    Slot::whereIn('id', $ids)->delete();
                }
            });
        } catch (\Throwable $e) {
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
