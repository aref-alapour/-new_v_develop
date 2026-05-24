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
                'permission_callback' => static function (): bool {
                    return current_user_can('manage_options');
                },
                'callback' => static function () {
                    $autoloadFile = defined('EZ_CORE_PATH') ? EZ_CORE_PATH . 'vendor/autoload.php' : '';

                    $data = [
                        'booted' => true,
                        'version' => defined('EZ_CORE_VERSION') ? EZ_CORE_VERSION : 'unknown',
                        'autoload_present' => $autoloadFile !== '' && is_file($autoloadFile),
                        'php_version' => PHP_VERSION,
                    ];

                    Logger::info('Health route requested.');

                    return rest_ensure_response($data);
                },
            ]
        );
    }
}
