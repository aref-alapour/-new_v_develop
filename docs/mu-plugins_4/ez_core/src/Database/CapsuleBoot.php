<?php

namespace EscapeZoom\Core\Database;

use Illuminate\Database\Capsule\Manager as Capsule;

final class CapsuleBoot
{
    public const CONNECTION_WP = 'wordpress';

    public const CONNECTION_ESCAPEZO = 'escapezo';

    private static bool $booted = false;

    public static function isBooted(): bool
    {
        return self::$booted;
    }

    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        if (!defined('DB_NAME') || !defined('DB_USER') || !defined('DB_PASSWORD') || !defined('DB_HOST')) {
            throw new \RuntimeException('WordPress DB constants are not defined.');
        }

        $capsule = new Capsule();

        $wpCharset = defined('DB_CHARSET') && '' !== trim((string) DB_CHARSET)
            ? trim((string) DB_CHARSET)
            : 'utf8mb4';
        $wpCollate = defined('DB_COLLATE') ? trim((string) DB_COLLATE) : '';

        /** @var array<string, mixed> $wpConnection */
        $wpConnection = [
            'driver' => 'mysql',
            'host' => '',
            'database' => DB_NAME,
            'username' => DB_USER,
            'password' => DB_PASSWORD,
            'charset' => $wpCharset,
            'collation' => self::resolveMysqlCollation($wpCharset, $wpCollate),
            'prefix' => '',
            'strict' => false,
        ];
        self::applyMysqlHostFlags($wpConnection, (string) DB_HOST);

        $capsule->addConnection($wpConnection, self::CONNECTION_WP);
        $capsule->getDatabaseManager()->setDefaultConnection(self::CONNECTION_WP);

        if (self::escapezoConfigured()) {
            $ezCharset = defined('EZ_ESCAPEZO_DB_CHARSET') && '' !== trim((string) EZ_ESCAPEZO_DB_CHARSET)
                ? trim((string) EZ_ESCAPEZO_DB_CHARSET)
                : 'utf8mb4';
            $ezCollate = defined('EZ_ESCAPEZO_DB_COLLATE') ? trim((string) EZ_ESCAPEZO_DB_COLLATE) : '';

            /** @var array<string, mixed> $escapezoConn */
            $escapezoConn = [
                'driver' => 'mysql',
                'host' => '',
                'database' => EZ_ESCAPEZO_DB_NAME,
                'username' => EZ_ESCAPEZO_DB_USER,
                'password' => EZ_ESCAPEZO_DB_PASSWORD,
                'charset' => $ezCharset,
                'collation' => self::resolveMysqlCollation($ezCharset, $ezCollate),
                'prefix' => defined('EZ_ESCAPEZO_DB_PREFIX') ? EZ_ESCAPEZO_DB_PREFIX : '',
                'strict' => false,
            ];
            self::applyMysqlHostFlags($escapezoConn, (string) EZ_ESCAPEZO_DB_HOST);
            $capsule->addConnection($escapezoConn, self::CONNECTION_ESCAPEZO);
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

    /**
     * Pair charset with a valid default collation — avoids MySQL error 1253 when `DB_CHARSET` is
     * legacy `utf8` (=utf8mb3) but Laravel would otherwise default to utf8mb4_unicode_ci alone.
     */
    private static function resolveMysqlCollation(string $charset, string $explicitFromConfig): string
    {
        if ('' !== $explicitFromConfig) {
            return $explicitFromConfig;
        }

        $c = strtolower($charset);

        if (str_contains($c, 'utf8mb4')) {
            return 'utf8mb4_unicode_ci';
        }

        // MySQL treats `utf8` as utf8mb3 — must not pair with utf8mb4_* collation.
        if ('utf8' === $c || str_starts_with($c, 'utf8mb3')) {
            return 'utf8_unicode_ci';
        }

        if (str_starts_with($c, 'latin1')) {
            return 'latin1_swedish_ci';
        }

        if (str_starts_with($c, 'latin2')) {
            return 'latin2_general_ci';
        }

        // Unknown — prefer WordPress-modern default unless tables say otherwise (caller can set DB_COLLATE).
        return 'utf8mb4_unicode_ci';
    }

    /**
     * Match {@see DbConnection}: split `host[:port|:socket]` into PDO keys Laravel understands.
     * Raw `127.0.0.1:3306` in `host` alone produces invalid `mysql:` DSNs and breaks Capsule selects.
     *
     * @param array<string, mixed> $config
     */
    private static function applyMysqlHostFlags(array &$config, string $hostRaw): void
    {
        $host = $hostRaw;
        if (! str_contains($host, ':')) {
            $config['host'] = $host;

            return;
        }

        [$h, $rest] = explode(':', $host, 2);
        $h = trim($h);
        if ($h === '') {
            $config['host'] = $hostRaw;

            return;
        }

        $config['host'] = $h;
        if (ctype_digit($rest)) {
            $config['port'] = (int) $rest;

            return;
        }

        $config['unix_socket'] = $rest;
        unset($config['port']);
    }
}
