<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin;

/**
 * Show third-party admin_notices only on the dashboard screen.
 */
final class ProductPenaltyAdminNotices
{
    public static function register(): void
    {
        add_action('current_screen', [self::class, 'restrictNoticesToDashboard']);
    }

    public static function restrictNoticesToDashboard(\WP_Screen $screen): void
    {
        if ($screen->id === 'dashboard') {
            return;
        }

        remove_all_actions('admin_notices');
        remove_all_actions('all_admin_notices');
    }
}
