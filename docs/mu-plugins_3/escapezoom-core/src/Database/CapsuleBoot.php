<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Database;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

/**
 * Boots Eloquent from WordPress config (DB_NAME, DB_USER, DB_PASSWORD, DB_HOST).
 * Uses 'default' connection for custom tables (no WP prefix).
 * Respects environment: wp-config (local/production) defines DB_*.
 */
final class CapsuleBoot
{
    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (!defined('DB_NAME') || !defined('DB_USER') || !defined('DB_HOST')) {
            return;
        }

        $capsule = new Capsule();

        $prefix = isset($GLOBALS['table_prefix']) ? $GLOBALS['table_prefix'] : 'wp_';
        $capsule->addConnection([
            'driver'    => 'mysql',
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => defined('DB_PASSWORD') ? DB_PASSWORD : '',
            'charset'   => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
            'collation' => defined('DB_COLLATE') && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci',
            'prefix'    => $prefix,
            'strict'    => false,
            'engine'    => null,
        ], 'default');

        $capsule->setEventDispatcher(new Dispatcher(new Container()));
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$booted = true;
    }
}
