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

		if ( defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
			self::$capsule->addConnection(
				array(
					'driver'    => 'mysql',
					'host'      => defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : DB_HOST,
					'database'  => DB_EXT_NAME,
					'username'  => DB_EXT_USER,
					'password'  => DB_EXT_PASSWORD,
					'charset'   => $charset,
					'collation' => $collation,
					'prefix'    => '',
					'strict'    => false,
					'engine'    => null,
				),
				'external'
			);
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
}
