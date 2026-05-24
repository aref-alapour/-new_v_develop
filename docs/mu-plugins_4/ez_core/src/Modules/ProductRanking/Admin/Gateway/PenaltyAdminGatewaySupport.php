<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class PenaltyAdminGatewaySupport
{
    public static function authorizeAdmin(): ?Response
    {
        if (! function_exists('current_user_can') || ! is_user_logged_in() || ! current_user_can('manage_options')) {
            return Response::error('FORBIDDEN', 403);
        }

        return null;
    }

    /**
     * @param array<string, mixed> $inputs
     * @return array<string, mixed>
     */
    public static function mergeInput(array $inputs, Request $req): array
    {
        $body = $req->parsedBody();

        return array_merge($body, $inputs);
    }
}
