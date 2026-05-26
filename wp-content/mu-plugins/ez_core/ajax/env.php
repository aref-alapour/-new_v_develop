<?php
/**
 * Minimal config for light /ajax (no wp-settings.php).
 */
declare(strict_types=1);

if ( ! function_exists( 'getenv_docker' ) ) {
	function getenv_docker( string $env, $default ) {
		$file_env = getenv( $env . '_FILE' );
		if ( false !== $file_env && is_readable( $file_env ) ) {
			return rtrim( (string) file_get_contents( $file_env ), "\r\n" );
		}
		$val = getenv( $env );
		if ( false !== $val ) {
			return $val;
		}

		return $default;
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__, 4 ) . '/' );
}

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', dirname( __DIR__ ) );
}

if ( ! defined( 'EZ_AJAX_LIGHT_GATEWAY' ) ) {
	define( 'EZ_AJAX_LIGHT_GATEWAY', true );
}

if ( ! defined( 'DB_NAME' ) ) {
	define( 'DB_NAME', getenv_docker( 'WORDPRESS_DB_NAME', 'wordpress' ) );
}
if ( ! defined( 'DB_USER' ) ) {
	define( 'DB_USER', getenv_docker( 'WORDPRESS_DB_USER', 'root' ) );
}
if ( ! defined( 'DB_PASSWORD' ) ) {
	define( 'DB_PASSWORD', getenv_docker( 'WORDPRESS_DB_PASSWORD', '' ) );
}
if ( ! defined( 'DB_HOST' ) ) {
	define( 'DB_HOST', getenv_docker( 'WORDPRESS_DB_HOST', 'mysql' ) );
}
if ( ! defined( 'DB_CHARSET' ) ) {
	define( 'DB_CHARSET', getenv_docker( 'WORDPRESS_DB_CHARSET', 'utf8mb4' ) );
}
if ( ! defined( 'DB_COLLATE' ) ) {
	define( 'DB_COLLATE', getenv_docker( 'WORDPRESS_DB_COLLATE', '' ) );
}

if ( ! defined( 'DB_EXT_NAME' ) ) {
	define( 'DB_EXT_NAME', getenv_docker( 'WORDPRESS_DB_EXT_NAME', 'escapezo_queries' ) );
}
if ( ! defined( 'DB_EXT_USER' ) ) {
	define( 'DB_EXT_USER', getenv_docker( 'WORDPRESS_DB_EXT_USER', DB_USER ) );
}
if ( ! defined( 'DB_EXT_PASSWORD' ) ) {
	define( 'DB_EXT_PASSWORD', getenv_docker( 'WORDPRESS_DB_EXT_PASSWORD', DB_PASSWORD ) );
}
if ( ! defined( 'DB_EXT_HOST' ) ) {
	define( 'DB_EXT_HOST', getenv_docker( 'WORDPRESS_DB_EXT_HOST', DB_HOST ) );
}

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) ) {
	$ez_ajax_secret = getenv_docker( 'EZ_AJAX_SHARED_SECRET', '' );
	if ( '' === $ez_ajax_secret ) {
		$auth = getenv_docker( 'WORDPRESS_AUTH_KEY', 'ez-ajax-dev' );
		$sec  = getenv_docker( 'WORDPRESS_SECURE_AUTH_KEY', 'ez-ajax-dev' );
		$ez_ajax_secret = hash( 'sha256', $auth . $sec . 'ez-ajax-gateway-v1' );
	}
	define( 'EZ_AJAX_SHARED_SECRET', $ez_ajax_secret );
}

if ( ! defined( 'EZ_BOOKING_NATIVE_SANSES' ) ) {
	define(
		'EZ_BOOKING_NATIVE_SANSES',
		filter_var( getenv_docker( 'EZ_BOOKING_NATIVE_SANSES', '1' ), FILTER_VALIDATE_BOOLEAN )
	);
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define(
		'WP_DEBUG',
		filter_var( getenv_docker( 'WORDPRESS_DEBUG', '1' ), FILTER_VALIDATE_BOOLEAN )
	);
}

$GLOBALS['table_prefix'] = getenv_docker( 'WORDPRESS_TABLE_PREFIX', 'wp_' );
