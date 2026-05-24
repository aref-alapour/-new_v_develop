<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Scheduler;

use EscapeZoom\Core\Jobs\CleanupAutoDraftsJob;
use EscapeZoom\Core\Jobs\ExpirePendingSlotsJob;

/**
 * Schedules recurring jobs. Prefers Action Scheduler (e.g. from WooCommerce); does not use wp_schedule_event (rule 22).
 */
final class JobScheduler
{
    private const GROUP = 'escapezoom';

    public static function register(): void
    {
        add_action('escapezoom_run_expire_pending_slots', [self::class, 'runExpirePendingSlots']);
        add_action('escapezoom_run_cleanup_auto_drafts', [self::class, 'runCleanupAutoDrafts']);

        add_action('init', [self::class, 'scheduleRecurring'], 20);
    }

    public static function runExpirePendingSlots(): void
    {
        (new ExpirePendingSlotsJob())->run();
    }

    public static function runCleanupAutoDrafts(): void
    {
        (new CleanupAutoDraftsJob())->run();
    }

    public static function scheduleRecurring(): void
    {
        if (!function_exists('as_schedule_recurring_action')) {
            return;
        }
        self::scheduleExpirePendingSlots();
        self::scheduleCleanupAutoDrafts();
    }

    private static function scheduleExpirePendingSlots(): void
    {
        $hook = 'escapezoom_run_expire_pending_slots';
        if (as_next_scheduled_action($hook, [], self::GROUP) !== null) {
            return;
        }
        as_schedule_recurring_action(
            time() + 60,
            120,
            $hook,
            [],
            self::GROUP
        );
    }

    private static function scheduleCleanupAutoDrafts(): void
    {
        $hook = 'escapezoom_run_cleanup_auto_drafts';
        if (as_next_scheduled_action($hook, [], self::GROUP) !== null) {
            return;
        }
        $tz = function_exists('wp_timezone') ? wp_timezone() : new \DateTimeZone('UTC');
        $now = new \DateTimeImmutable('now', $tz);
        $four_am = $now->setTime(4, 0, 0);
        if ($four_am <= $now) {
            $four_am = $four_am->modify('+1 day');
        }
        $first_run = $four_am->getTimestamp();
        as_schedule_recurring_action(
            $first_run,
            \DAY_IN_SECONDS,
            $hook,
            [],
            self::GROUP
        );
    }
}
