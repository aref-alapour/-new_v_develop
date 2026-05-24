<?php

namespace EscapeZoom\Core\Modules\ProductRatings;

use EscapeZoom\Core\Modules\ProductRatings\API\ProductRatingsPublicRestController;

final class ProductRatingsModule
{
    public static function register(): void
    {
        add_action('rest_api_init', static function (): void {
            ProductRatingsPublicRestController::registerRoutes();
        });
    }
}
