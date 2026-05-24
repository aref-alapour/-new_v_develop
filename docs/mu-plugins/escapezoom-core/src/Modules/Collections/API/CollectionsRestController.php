<?php

namespace EscapeZoom\Core\Modules\Collections\API;

use EscapeZoom\Core\Modules\Collections\Services\CollectionsService;

class CollectionsRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/collections/active', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'active'],
        ]);
    }

    public function active()
    {
        return rest_ensure_response(['data' => (new CollectionsService())->listActive()]);
    }
}
