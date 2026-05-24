<?php

namespace EscapeZoom\Core\Modules\Comments\API;

use EscapeZoom\Core\Modules\Comments\Services\CommentAuditService;

class CommentAuditRestController
{
    public static function create(): self
    {
        return new self();
    }

    public function registerRoutes(): void
    {
        register_rest_route('escapezoom-core/v1', '/comments/audit', [
            'methods' => 'GET',
            'permission_callback' => static fn (): bool => current_user_can('manage_options'),
            'callback' => [$this, 'listRecent'],
        ]);
    }

    public function listRecent()
    {
        return rest_ensure_response(['data' => (new CommentAuditService())->listRecent()]);
    }
}
