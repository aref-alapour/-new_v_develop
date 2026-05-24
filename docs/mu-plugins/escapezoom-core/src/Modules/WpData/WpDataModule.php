<?php

namespace EscapeZoom\Core\Modules\WpData;

use EscapeZoom\Core\Modules\WpData\API\WpDataCrudRestController;

final class WpDataModule
{
    public static function register(): void
    {
        if (!defined('EZ_CORE_WP_DATA_REST_ENABLED')) {
            define('EZ_CORE_WP_DATA_REST_ENABLED', true);
        }

        add_action('rest_api_init', static function (): void {
            if (!EZ_CORE_WP_DATA_REST_ENABLED) {
                return;
            }
            WpDataCrudRestController::create()->registerRoutes();
        });
    }
}
