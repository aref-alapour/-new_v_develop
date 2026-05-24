<?php

namespace EscapeZoom\Core\Modules\Core;

use EscapeZoom\Core\Core\Logger;

final class CoreModule
{
    public static function register(): void
    {
        add_action('rest_api_init', [self::class, 'registerHealthRoute']);
    }

    public static function registerHealthRoute(): void
    {
        register_rest_route(
            'escapezoom-core/v1',
            '/health',
            [
                'methods' => 'GET',
                'permission_callback' => static fn (): bool => current_user_can('manage_options'),
                'callback' => static function () {
                    Logger::info('Health route requested.');

                    return rest_ensure_response([
                        'ok' => true,
                        'php' => PHP_VERSION,
                    ]);
                },
            ]
        );
    }
}
