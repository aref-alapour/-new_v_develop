<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Database;

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;
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

	/**
	 * Light /ajax: external (booking) + wordpress (prefixed WP tables).
	 */
	public static function bootLightGateway(): void {
		if ( self::$booted ) {
			return;
		}

		$extConfig = self::resolveExternalConfig();
		$wpConfig  = self::resolveWordpressConfig();
		if ( null === $extConfig && null === $wpConfig ) {
			return;
		}

		$charset   = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4';
		$collation = defined( 'DB_COLLATE' ) && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci';

		self::$capsule = new Capsule();

		if ( null !== $extConfig ) {
			self::$capsule->addConnection( self::buildConnectionArray( $extConfig, $charset, $collation, '' ), 'external' );
		}

		if ( null !== $wpConfig ) {
			self::$capsule->addConnection(
				self::buildConnectionArray( $wpConfig, $charset, $collation, '' ),
				'default'
			);
			self::$capsule->addConnection(
				self::buildConnectionArray( $wpConfig, $charset, $collation, $wpConfig['table_prefix'] ),
				'wordpress'
			);
		}

		$crmConfig = self::resolveCrmConfig();
		if ( null !== $crmConfig ) {
			self::$capsule->addConnection( self::buildConnectionArray( $crmConfig, $charset, $collation, '' ), 'crm' );
		}

		self::$capsule->setEventDispatcher( new Dispatcher( new Container() ) );
		self::$capsule->setAsGlobal();
		self::$capsule->bootEloquent();

		self::$booted = true;
	}

	/**
	 * Product view tracking: WordPress meta + CRM tables only (no external booking DB).
	 */
	/**
	 * Game search: wp_products_search table only (wordpress connection).
	 */
	public static function bootGameSearchOnly(): void {
		if ( self::$booted ) {
			return;
		}

		$wpConfig = self::resolveWordpressConfig();
		if ( null === $wpConfig ) {
			return;
		}

		$charset   = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4';
		$collation = defined( 'DB_COLLATE' ) && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci';

		self::$capsule = new Capsule();
		self::$capsule->addConnection(
			self::buildConnectionArray( $wpConfig, $charset, $collation, $wpConfig['table_prefix'] ),
			'wordpress'
		);

		self::$capsule->setEventDispatcher( new Dispatcher( new Container() ) );
		self::$capsule->setAsGlobal();
		self::$capsule->bootEloquent();

		self::$booted = true;
	}

	public static function bootProductViewOnly(): void {
		if ( self::$booted ) {
			return;
		}

		$wpConfig = self::resolveWordpressConfig();
		if ( null === $wpConfig ) {
			return;
		}

		$charset   = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4';
		$collation = defined( 'DB_COLLATE' ) && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci';

		self::$capsule = new Capsule();
		self::$capsule->addConnection(
			self::buildConnectionArray( $wpConfig, $charset, $collation, $wpConfig['table_prefix'] ),
			'wordpress'
		);

		$crmConfig = self::resolveCrmConfig();
		if ( null !== $crmConfig ) {
			self::$capsule->addConnection( self::buildConnectionArray( $crmConfig, $charset, $collation, '' ), 'crm' );
		}

		self::$capsule->setEventDispatcher( new Dispatcher( new Container() ) );
		self::$capsule->setAsGlobal();
		self::$capsule->bootEloquent();

		self::$booted = true;
	}

	public static function hasCrmConnection(): bool {
		return null !== self::resolveCrmConfig();
	}

	/**
	 * @deprecated Use bootLightGateway()
	 */
	public static function bootExternalOnly(): void {
		self::bootLightGateway();
	}

	public static function boot(): void {
		if ( self::$booted ) {
			return;
		}

		$wpConfig = self::resolveWordpressConfig();
		if ( null === $wpConfig && ( ! defined( 'DB_NAME' ) || ! defined( 'DB_USER' ) || ! defined( 'DB_PASSWORD' ) || ! defined( 'DB_HOST' ) ) ) {
			return;
		}

		self::$capsule = new Capsule();
		$charset       = defined( 'DB_CHARSET' ) ? DB_CHARSET : 'utf8mb4';
		$collation     = defined( 'DB_COLLATE' ) && DB_COLLATE ? DB_COLLATE : 'utf8mb4_unicode_ci';

		if ( null !== $wpConfig ) {
			self::$capsule->addConnection(
				self::buildConnectionArray( $wpConfig, $charset, $collation, '' ),
				'default'
			);
			self::$capsule->addConnection(
				self::buildConnectionArray( $wpConfig, $charset, $collation, $wpConfig['table_prefix'] ),
				'wordpress'
			);
		} else {
			$wp_prefix = $GLOBALS['table_prefix'] ?? 'wp_';
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
		}

		$extConfig = self::resolveExternalConfig();
		if ( null !== $extConfig ) {
			self::$capsule->addConnection( self::buildConnectionArray( $extConfig, $charset, $collation, '' ), 'external' );
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
			if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
				self::bootLightGateway();
			} else {
				self::boot();
			}
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
		if ( null === self::resolveExternalConfig() ) {
			return false;
		}

		if ( ! self::$booted ) {
			if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
				self::bootLightGateway();
			} else {
				self::boot();
			}
		}

		if ( null === self::$capsule ) {
			return false;
		}

		try {
			self::$capsule->getConnection( 'external' );

			return true;
		} catch ( \Throwable $e ) {
			return false;
		}
	}

	public static function hasWordpressConnection(): bool {
		if ( null === self::resolveWordpressConfig() ) {
			return false;
		}

		if ( ! self::$booted ) {
			if ( defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY ) {
				self::bootLightGateway();
			} else {
				self::boot();
			}
		}

		if ( null === self::$capsule ) {
			return false;
		}

		try {
			self::$capsule->getConnection( 'wordpress' );

			return true;
		} catch ( \Throwable $e ) {
			return false;
		}
	}

	/**
	 * @param array{host: string, database: string, username: string, password: string, port?: int, unix_socket?: string} $config
	 * @return array<string, mixed>
	 */
	private static function buildConnectionArray( array $config, string $charset, string $collation, string $prefix ): array {
		$connection = array(
			'driver'    => 'mysql',
			'host'      => $config['host'],
			'database'  => $config['database'],
			'username'  => $config['username'],
			'password'  => $config['password'],
			'charset'   => $charset,
			'collation' => $collation,
			'prefix'    => $prefix,
			'strict'    => false,
			'engine'    => null,
		);
		if ( isset( $config['port'] ) && null !== $config['port'] ) {
			$connection['port'] = $config['port'];
		}
		if ( isset( $config['unix_socket'] ) && null !== $config['unix_socket'] ) {
			$connection['unix_socket'] = $config['unix_socket'];
		}

		return $connection;
	}

	/**
	 * Primary: secrets.enc via SecretsLoader. Rollback: DB_EXT_* if already defined.
	 *
	 * @return array{host: string, database: string, username: string, password: string, port?: int, unix_socket?: string}|null
	 */
	private static function resolveExternalConfig(): ?array {
		$raw = SecretsLoader::externalDatabase();

		if ( null === $raw && defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
			$raw = array(
				'host'     => defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : ( defined( 'DB_HOST' ) ? DB_HOST : 'mysql' ),
				'database' => DB_EXT_NAME,
				'username' => DB_EXT_USER,
				'password' => DB_EXT_PASSWORD,
			);
		}

		if ( null === $raw ) {
			return null;
		}

		return self::applyHostParse( $raw );
	}

	/**
	 * Primary: secrets.enc. Rollback: wp-config DB_* constants.
	 *
	 * @return array{host: string, database: string, username: string, password: string, table_prefix: string, port?: int, unix_socket?: string}|null
	 */
	/**
	 * CRM DB (ip_checker, product_views) — same credentials as WP, separate database name.
	 *
	 * @return array{host: string, database: string, username: string, password: string, port?: int, unix_socket?: string}|null
	 */
	private static function resolveCrmConfig(): ?array {
		$wp = self::resolveWordpressConfig();
		if ( null === $wp ) {
			return null;
		}

		$database = '';
		if ( defined( 'EZ_MEDOO_CRM_DATABASE' ) && '' !== (string) EZ_MEDOO_CRM_DATABASE ) {
			$database = (string) EZ_MEDOO_CRM_DATABASE;
		} else {
			$env = getenv( 'EZ_MEDOO_CRM_DATABASE' );
			if ( is_string( $env ) && '' !== $env ) {
				$database = $env;
			}
		}

		if ( '' === $database ) {
			return null;
		}

		$parsed             = self::applyHostParse( $wp );
		$parsed['database'] = $database;

		return $parsed;
	}

	private static function resolveWordpressConfig(): ?array {
		$raw = SecretsLoader::wordpressDatabase();

		if ( null === $raw && defined( 'DB_NAME' ) && defined( 'DB_USER' ) && defined( 'DB_PASSWORD' ) && defined( 'DB_HOST' ) ) {
			$prefix = $GLOBALS['table_prefix'] ?? 'wp_';
			$raw    = array(
				'host'          => DB_HOST,
				'database'      => DB_NAME,
				'username'      => DB_USER,
				'password'      => DB_PASSWORD,
				'table_prefix'  => is_string( $prefix ) ? $prefix : 'wp_',
			);
		}

		if ( null === $raw ) {
			return null;
		}

		$parsed = self::applyHostParse( $raw );
		$parsed['table_prefix'] = $raw['table_prefix'];

		return $parsed;
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
