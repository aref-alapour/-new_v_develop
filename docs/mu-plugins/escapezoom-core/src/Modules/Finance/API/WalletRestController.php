<?php

namespace EscapeZoom\Core\Modules\Finance\API;

use EscapeZoom\Core\Modules\Finance\Services\WalletLedgerService;

class WalletRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/wallet/(?P<user_id>\d+)/transactions', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'listTransactions'],
        ]);
    }

    public function listTransactions(\WP_REST_Request $request)
    {
        $userId = (int) $request->get_param('user_id');
        return rest_ensure_response(['data' => (new WalletLedgerService())->listUserTransactions($userId)]);
    }
}
