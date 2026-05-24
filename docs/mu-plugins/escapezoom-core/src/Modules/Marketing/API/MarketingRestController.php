<?php

namespace EscapeZoom\Core\Modules\Marketing\API;

use EscapeZoom\Core\Modules\Marketing\Services\MarketingService;

class MarketingRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/marketing/orders', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'latestOrders'],
        ]);
    }

    public function latestOrders()
    {
        return rest_ensure_response(['data' => (new MarketingService())->latestOrders()]);
    }
}
