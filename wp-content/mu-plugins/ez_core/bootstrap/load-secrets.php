<?php
/**
 * Load encrypted secrets and define bridge constants for ez_core + legacy theme.
 */
declare(strict_types=1);

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', dirname( __DIR__ ) );
}

$autoload = EZ_CORE_PATH . '/vendor/autoload.php';
if ( is_readable( $autoload ) ) {
	require_once $autoload;
}

if ( ! function_exists( 'sodium_crypto_secretbox_open' ) ) {
	$sodiumCompat = dirname( EZ_CORE_PATH, 2 ) . '/plugins/wordfence/crypto/vendor/paragonie/sodium_compat/autoload.php';
	if ( is_readable( $sodiumCompat ) ) {
		require_once $sodiumCompat;
	}
}

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

$secretsOk = class_exists( SecretsLoader::class ) && SecretsLoader::boot();

if ( $secretsOk ) {
	$ext = SecretsLoader::externalDatabase();
	if ( null !== $ext ) {
		if ( ! defined( 'DB_EXT_HOST' ) ) {
			define( 'DB_EXT_HOST', $ext['host'] );
		}
		if ( ! defined( 'DB_EXT_NAME' ) ) {
			define( 'DB_EXT_NAME', $ext['database'] );
		}
		if ( ! defined( 'DB_EXT_USER' ) ) {
			define( 'DB_EXT_USER', $ext['username'] );
		}
		if ( ! defined( 'DB_EXT_PASSWORD' ) ) {
			define( 'DB_EXT_PASSWORD', $ext['password'] );
		}
	}

	if ( ! defined( 'EZ_BOOKING_USE_INTERNAL' ) ) {
		define( 'EZ_BOOKING_USE_INTERNAL', SecretsLoader::bookingUseInternal() );
	}

	if ( ! defined( 'EZ_BOOKING_NATIVE_SANSES' ) ) {
		define( 'EZ_BOOKING_NATIVE_SANSES', SecretsLoader::bookingNativeSanses() );
	}
}

if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( EZ_CORE_PATH, 3 ) . '/' );
}

if ( ! defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && defined( 'EZ_AJAX_LIGHT_GATEWAY_REQUEST' ) && EZ_AJAX_LIGHT_GATEWAY_REQUEST ) {
	define( 'EZ_AJAX_LIGHT_GATEWAY', true );
}

if ( ! defined( 'DB_CHARSET' ) ) {
	define( 'DB_CHARSET', 'utf8mb4' );
}

if ( ! defined( 'DB_COLLATE' ) ) {
	define( 'DB_COLLATE', '' );
}

if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', filter_var( getenv( 'WORDPRESS_DEBUG' ) ?: '0', FILTER_VALIDATE_BOOLEAN ) );
}

if ( ! isset( $GLOBALS['table_prefix'] ) ) {
	$GLOBALS['table_prefix'] = SecretsLoader::isLoaded()
		? SecretsLoader::tablePrefix()
		: ( getenv( 'WORDPRESS_TABLE_PREFIX' ) ?: 'wp_' );
}

require __DIR__ . '/ajax-secret.php';
