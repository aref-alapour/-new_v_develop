<?php

namespace EscapeZoom\Core\Modules\Commerce\API;

use EscapeZoom\Core\Modules\Commerce\Services\CheckoutLedgerService;

class CheckoutLedgerRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/checkout-ledger/(?P<order_id>\d+)', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'getByOrderId'],
        ]);
    }

    public function getByOrderId(\WP_REST_Request $request)
    {
        $service = new CheckoutLedgerService();
        $order = $service->getByOrderId((int) $request->get_param('order_id'));
        return rest_ensure_response(['data' => $order]);
    }
}
