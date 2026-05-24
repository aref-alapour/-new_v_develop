<?php

namespace EscapeZoom\Core\Modules\Cancellation\API;

use EscapeZoom\Core\Modules\Cancellation\Services\CancellationService;

class CancellationRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/cancellations/pending', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'pending'],
        ]);
    }

    public function pending()
    {
        return rest_ensure_response(['data' => (new CancellationService())->listPending()]);
    }
}
