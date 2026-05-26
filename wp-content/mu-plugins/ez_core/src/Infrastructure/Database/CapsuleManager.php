<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Database;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Events\Dispatcher;

/**
 * Eloquent Capsule: default (WP custom tables), wordpress (prefixed), external (escapezo_queries).
 */
final class CapsuleManager
{
	private static ?Capsule $capsule = null;

	private static bool $booted = false;

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		if ( ! defined( 'DB_NAME' ) || ! defined( 'DB_USER' ) || ! defined( 'DB_PASSWORD' ) || ! defined( 'DB_HOST' ) ) {
			return;
		}

		self::$capsule = new Capsule();
		$wp_prefix     = $GLOBALS['table_prefix'] ?? 'wp_';
		$charset       = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4';
		$collation     = defined( 'DB_COLLATE' ) && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci';

		self::$capsule->addConnection(
			array(
				'driver'    => 'mysql',
				'host'      => DB_HOST,
				'database'  => DB_NAME,
				'username'  => DB_USER,
				'password'  => DB_PASSWORD,
				'charset'   => $charset,
				'collation' => $collation,
				'prefix'    => '',
				'strict'    => false,
				'engine'    => null,
			),
			'default'
		);

		self::$capsule->addConnection(
			array(
				'driver'    => 'mysql',
				'host'      => DB_HOST,
				'database'  => DB_NAME,
				'username'  => DB_USER,
				'password'  => DB_PASSWORD,
				'charset'   => $charset,
				'collation' => $collation,
				'prefix'    => $wp_prefix,
				'strict'    => false,
				'engine'    => null,
			),
			'wordpress'
		);

		$extConfig = self::resolveExternalConfig();
		if ( null !== $extConfig ) {
			$connection = array(
				'driver'    => 'mysql',
				'host'      => $extConfig['host'],
				'database'  => $extConfig['database'],
				'username'  => $extConfig['username'],
				'password'  => $extConfig['password'],
				'charset'   => $charset,
				'collation' => $collation,
				'prefix'    => '',
				'strict'    => false,
				'engine'    => null,
			);
			if ( isset( $extConfig['port'] ) && null !== $extConfig['port'] ) {
				$connection['port'] = $extConfig['port'];
			}
			if ( isset( $extConfig['unix_socket'] ) && null !== $extConfig['unix_socket'] ) {
				$connection['unix_socket'] = $extConfig['unix_socket'];
			}
			self::$capsule->addConnection( $connection, 'external' );
		}

		self::$capsule->setEventDispatcher( new Dispatcher( new Container() ) );
		self::$capsule->setAsGlobal();
		self::$capsule->bootEloquent();

		self::$booted = true;
	}

	public static function getCapsule(): ?Capsule {
		if ( ! self::$booted ) {
			self::boot();
		}

		return self::$capsule;
	}

	public static function connection( ?string $name = null ): Connection {
		if ( ! self::$booted ) {
			self::boot();
		}

		if ( null === self::$capsule ) {
			throw new \RuntimeException( 'EZ Core: database capsule not initialized.' );
		}

		return self::$capsule->getConnection( $name );
	}

	public static function isBooted(): bool {
		return self::$booted;
	}

	public static function hasExternalConnection(): bool {
		if ( ! self::$booted ) {
			self::boot();
		}
		if ( null === self::$capsule ) {
			return false;
		}

		try {
			self::$capsule->getConnection( 'external' )->getPdo();

			return true;
		} catch ( \Throwable $e ) {
			return false;
		}
	}

	/**
	 * wp-config constants or Docker WORDPRESS_DB_EXT_* (same as web-service/db-connect.php).
	 *
	 * @return array{host: string, database: string, username: string, password: string}|null
	 */
	private static function resolveExternalConfig(): ?array {
		$raw = null;
		if ( defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
			$raw = array(
				'host'     => defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : DB_HOST,
				'database' => DB_EXT_NAME,
				'username' => DB_EXT_USER,
				'password' => DB_EXT_PASSWORD,
			);
		} else {
			$database = getenv( 'WORDPRESS_DB_EXT_NAME' ) ?: getenv( 'DB_EXT_NAME' );
			$username = getenv( 'WORDPRESS_DB_EXT_USER' ) ?: getenv( 'DB_EXT_USER' );
			$password = getenv( 'WORDPRESS_DB_EXT_PASSWORD' ) ?: getenv( 'DB_EXT_PASSWORD' );
			if ( ! $database || ! $username || false === $password ) {
				return null;
			}

			$host = getenv( 'WORDPRESS_DB_EXT_HOST' ) ?: getenv( 'WORDPRESS_DB_HOST' );
			if ( ! $host && defined( 'DB_HOST' ) ) {
				$host = DB_HOST;
			}
			if ( ! $host ) {
				$host = 'mysql';
			}

			$raw = array(
				'host'     => (string) $host,
				'database' => (string) $database,
				'username' => (string) $username,
				'password' => (string) $password,
			);
		}

		return self::applyHostParse( $raw );
	}

	/**
	 * @param array{host: string, database: string, username: string, password: string} $raw
	 * @return array{host: string, database: string, username: string, password: string, port?: int, unix_socket?: string}
	 */
	private static function applyHostParse( array $raw ): array {
		$parsed = MysqlHost::parse( (string) $raw['host'] );
		$out    = array(
			'host'     => $parsed['host'],
			'database' => $raw['database'],
			'username' => $raw['username'],
			'password' => $raw['password'],
		);
		if ( null !== $parsed['port'] ) {
			$out['port'] = $parsed['port'];
		}
		if ( null !== $parsed['socket'] ) {
			$out['unix_socket'] = $parsed['socket'];
		}

		return $out;
	}
}
