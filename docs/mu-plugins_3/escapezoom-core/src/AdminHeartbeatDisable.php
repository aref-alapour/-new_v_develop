<?php

declare(strict_types=1);

namespace EscapeZoom\Core;

/**
 * غیرفعال کردن Heartbeat در ادمین تا وردپرس با هر بار باز شدن "Add New" ردیف auto-draft جدید نسازد.
 *
 * پیشنهاد برای wp-config.php (توسعه‌دهنده خودش اضافه می‌کند؛ بدون ویرایش خودکار):
 *   define('AUTOSAVE_INTERVAL', 300);
 *   define('WP_POST_REVISIONS', false);   // یا 2
 *   define('EMPTY_TRASH_DAYS', 7);
 */
final class AdminHeartbeatDisable
{
    public static function register(): void
    {
        // Disabled: Deregistering heartbeat breaks wp-auth-check dependency.
        // Use AUTOSAVE_INTERVAL constant in wp-config.php to slow down autosave instead.
        // add_action('admin_enqueue_scripts', [self::class, 'deregisterHeartbeat'], 5);
    }

    public static function deregisterHeartbeat(): void
    {
        // Disabled: This breaks wp-auth-check which depends on heartbeat.
        // wp_deregister_script('heartbeat');
    }
}
