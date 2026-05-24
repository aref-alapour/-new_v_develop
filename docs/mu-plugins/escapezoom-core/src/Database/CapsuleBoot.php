<?php

namespace EscapeZoom\Core\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

final class CapsuleBoot
{
    public const CONNECTION_WP = 'wordpress';

    /** Second DB (e.g. escapezo_queries). Only registered if EZ_ESCAPEZO_DB_* constants are set in wp-config.php. */
    public const CONNECTION_ESCAPEZO = 'escapezo';

    private static bool $booted = false;

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (!defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_HOST')) {
            throw new \RuntimeException('WordPress DB constants are not defined.');
        }

        $capsule = new Capsule();

        $wpConnection = [
            'driver' => 'mysql',
            'host' => DB_HOST,
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'charset' => defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4',
            'collation' => defined('DB_COLLATE') && DB_COLLATE !== '' ? DB_COLLATE : 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ];

        $capsule->addConnection($wpConnection, self::CONNECTION_WP);
        $capsule->getDatabaseManager()->setDefaultConnection(self::CONNECTION_WP);

        if (self::escapezoConfigured()) {
            $capsule->addConnection([
                'driver' => 'mysql',
                'host' => EZ_ESCAPEZO_DB_HOST,
                'database' => EZ_ESCAPEZO_DB_NAME,
                'username' => EZ_ESCAPEZO_DB_USER,
                'password' => EZ_ESCAPEZO_DB_PASSWORD,
                'charset' => defined('EZ_ESCAPEZO_DB_CHARSET') ? EZ_ESCAPEZO_DB_CHARSET : 'utf8mb4',
                'collation' => defined('EZ_ESCAPEZO_DB_COLLATE') && EZ_ESCAPEZO_DB_COLLATE !== ''
                    ? EZ_ESCAPEZO_DB_COLLATE
                    : 'utf8mb4_unicode_ci',
                'prefix' => defined('EZ_ESCAPEZO_DB_PREFIX') ? EZ_ESCAPEZO_DB_PREFIX : '',
                'strict' => false,
            ], self::CONNECTION_ESCAPEZO);
        }

        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        self::$booted = true;
    }

    public static function escapezoConfigured(): bool
    {
        return defined('EZ_ESCAPEZO_DB_HOST')
            && defined('EZ_ESCAPEZO_DB_NAME')
            && defined('EZ_ESCAPEZO_DB_USER')
            && defined('EZ_ESCAPEZO_DB_PASSWORD');
    }
}
