<?php

/**
 * External queries DB (escapezo_queries). Lazy connect via ez_reservation_get_conn().
 */

/**
 * @return array{host: string, database: string, username: string, password: string}
 */
function ez_reservation_db_config(): array {
	if ( defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
		return array(
			'host'     => defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : ( defined( 'DB_HOST' ) ? DB_HOST : 'mysql' ),
			'database' => DB_EXT_NAME,
			'username' => DB_EXT_USER,
			'password' => DB_EXT_PASSWORD,
		);
	}

	$host = getenv( 'WORDPRESS_DB_EXT_HOST' ) ?: ( getenv( 'WORDPRESS_DB_HOST' ) ?: 'mysql' );
	$db   = getenv( 'WORDPRESS_DB_EXT_NAME' ) ?: 'escapezo_queries';
	$user = getenv( 'WORDPRESS_DB_EXT_USER' ) ?: ( getenv( 'WORDPRESS_DB_USER' ) ?: 'root' );
	$pass = getenv( 'WORDPRESS_DB_EXT_PASSWORD' );
	if ( false === $pass ) {
		$pass = getenv( 'WORDPRESS_DB_PASSWORD' ) ?: 'arefpassword';
	}

	return array(
		'host'     => (string) $host,
		'database' => (string) $db,
		'username' => (string) $user,
		'password' => (string) $pass,
	);
}

/**
 * @return mysqli|null
 */
/**
 * @return array{host: string, port: ?int, socket: ?string}
 */
function ez_booking_parse_mysql_host( string $host ): array {
	if ( class_exists( \EscapeZoom\Core\Infrastructure\Database\MysqlHost::class ) ) {
		return \EscapeZoom\Core\Infrastructure\Database\MysqlHost::parse( $host );
	}

	$host = trim( $host );
	if ( str_starts_with( $host, '/' ) || str_ends_with( $host, '.sock' ) ) {
		return array(
			'host'   => 'localhost',
			'port'   => null,
			'socket' => $host,
		);
	}
	$port = null;
	if ( str_contains( $host, ':' ) ) {
		$parts = explode( ':', $host, 2 );
		$host  = $parts[0];
		if ( isset( $parts[1] ) && is_numeric( $parts[1] ) ) {
			$port = (int) $parts[1];
		}
	}

	return array(
		'host'   => $host,
		'port'   => $port,
		'socket' => null,
	);
}

function ez_reservation_db_connect(): ?mysqli {
	$config = ez_reservation_db_config();
	$parsed = ez_booking_parse_mysql_host( (string) $config['host'] );

	if ( null !== $parsed['socket'] ) {
		$conn = @new mysqli(
			'localhost',
			$config['username'],
			$config['password'],
			$config['database'],
			null,
			$parsed['socket']
		);
	} else {
		$conn = @new mysqli(
			$parsed['host'],
			$config['username'],
			$config['password'],
			$config['database'],
			$parsed['port'] ?? (int) ini_get( 'mysqli.default_port' )
		);
	}

	if ( $conn->connect_error ) {
		$internal = defined( 'EZ_BOOKING_INTERNAL_CALL' ) && EZ_BOOKING_INTERNAL_CALL;
		$msg      = sprintf(
			'[EZ Booking] External DB connection failed (host=%s db=%s): %s',
			$config['host'],
			$config['database'],
			$conn->connect_error
		);
		$GLOBALS['ez_reservation_db_last_error'] = $msg;
		if ( $internal ) {
			error_log( $msg );
			return null;
		}
		die( 'Connection failed: ' . $conn->connect_error );
	}

	$conn->set_charset( 'utf8' );

	return $conn;
}

/**
 * Last connection error message (for gateway logs).
 */
function ez_reservation_db_last_error(): string {
	return isset( $GLOBALS['ez_reservation_db_last_error'] )
		? (string) $GLOBALS['ez_reservation_db_last_error']
		: '';
}

/**
 * Cached mysqli for reservation handlers (sets global $conn).
 */
function ez_reservation_get_conn(): ?mysqli {
	static $cached = null;

	if ( $cached instanceof mysqli ) {
		global $conn;
		$conn = $cached;

		return $cached;
	}

	$cached = ez_reservation_db_connect();
	global $conn;
	$conn = $cached;

	return $cached;
}

if ( ! defined( 'EZ_BOOKING_DEFER_DB_CONNECT' ) ) {
	global $conn;
	$conn = ez_reservation_get_conn();
}
