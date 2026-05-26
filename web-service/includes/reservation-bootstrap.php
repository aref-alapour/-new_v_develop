<?php
/**
 * Shared bootstrap for reservation HTTP endpoint and internal WordPress dispatch.
 */

declare(strict_types=1);

if ( ! defined( 'EZ_RESERVATION_ROOT' ) ) {
	define( 'EZ_RESERVATION_ROOT', dirname( __DIR__ ) );
}

/**
 * Load DB + helpers once per request.
 *
 * @param string|null $dispatchType When internal + get_sanses, skips heavy md-connect (wp-load).
 */
function ez_reservation_bootstrap_once( ?string $dispatchType = null ): void {
	static $loaded = false;
	if ( $loaded ) {
		return;
	}
	$loaded = true;

	error_reporting( E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED );
	date_default_timezone_set( 'Asia/Tehran' );

	if ( null !== $dispatchType ) {
		$GLOBALS['ez_reservation_dispatch_type'] = $dispatchType;
	}

	define( 'EZ_BOOKING_DEFER_DB_CONNECT', true );
	require EZ_RESERVATION_ROOT . '/db-connect.php';

	$lightBootstrap = defined( 'EZ_BOOKING_INTERNAL_CALL' ) && EZ_BOOKING_INTERNAL_CALL
		&& 'get_sanses' === ( $GLOBALS['ez_reservation_dispatch_type'] ?? '' );

	if ( ! $lightBootstrap ) {
		require EZ_RESERVATION_ROOT . '/md-connect.php';
		require_once EZ_RESERVATION_ROOT . '/ez-sans-mojavezedar-wp.php';
		if ( ! function_exists( 'jdate' ) ) {
			require_once EZ_RESERVATION_ROOT . '/jdf.php';
		}
	}

	require EZ_RESERVATION_ROOT . '/helper-functions.php';
	require_once __DIR__ . '/reservation-functions.inc.php';

	global $conn;
	$conn = ez_reservation_get_conn();
}

/**
 * @return string Home URL for reservation handlers.
 */
function ez_reservation_home_url(): string {
	if ( isset( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] === 'localhost' ) {
		return 'http://localhost/escapezoom_wp';
	}
	return 'https://escapezoom.ir';
}

/**
 * Host allowlist (skipped for internal WP dispatch).
 */
function ez_reservation_assert_allowed_host(): void {
	if ( defined( 'EZ_BOOKING_INTERNAL_CALL' ) && EZ_BOOKING_INTERNAL_CALL ) {
		return;
	}

	global $conn;

	$allowed = array(
		'escapezoom.ir',
		'escapezoom.co',
		'dev.escapezoom.local',
		'dev-api.escapezoom.ir',
		'goriza.ir',
		'localhost',
	);

	$host = isset( $_SERVER['HTTP_HOST'] ) ? (string) $_SERVER['HTTP_HOST'] : '';
	if ( in_array( $host, $allowed, true ) ) {
		return;
	}

	if ( isset( $conn ) && $conn instanceof mysqli ) {
		$conn->query(
			sprintf(
				"INSERT INTO hackers (host, referer) VALUES ('%s', '%s')",
				$conn->real_escape_string( $host ),
				$conn->real_escape_string( isset( $_SERVER['HTTP_REFERER'] ) ? (string) $_SERVER['HTTP_REFERER'] : '' )
			)
		);
	}
	die( 'Get outta here' );
}

/**
 * Normalize array payload from ez_reservation() to object tree.
 *
 * @param array<string,mixed>|object $data Raw payload.
 */
function ez_reservation_normalize_data( $data ): object {
	if ( is_object( $data ) ) {
		return $data;
	}
	if ( ! is_array( $data ) ) {
		return (object) array( 'type' => '', 'data' => (object) array() );
	}

	$type = isset( $data['type'] ) ? (string) $data['type'] : '';
	$inner = isset( $data['data'] ) ? $data['data'] : array();
	if ( is_array( $inner ) ) {
		$inner = (object) $inner;
	} elseif ( ! is_object( $inner ) ) {
		$inner = (object) array();
	}

	return (object) array(
		'type' => $type,
		'data' => $inner,
	);
}

/**
 * Emit JSON for reservation handlers. Internal dispatch (EZ_BOOKING_INTERNAL_CALL) must not exit
 * so ez_reservation_dispatch() can return output to the AJAX gateway for encryption.
 *
 * @param mixed $payload
 */
function ez_reservation_emit_json( $payload ): void {
	$json = function_exists( 'wp_json_encode' )
		? wp_json_encode( $payload, JSON_UNESCAPED_UNICODE )
		: json_encode( $payload );
	if ( ! is_string( $json ) ) {
		$json = '[]';
	}
	echo $json;
	if ( defined( 'EZ_BOOKING_INTERNAL_CALL' ) && EZ_BOOKING_INTERNAL_CALL ) {
		return;
	}
	exit;
}
