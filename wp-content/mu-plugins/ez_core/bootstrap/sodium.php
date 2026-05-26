<?php
/**
 * Ensure libsodium (ext or paragonie/sodium_compat via ez_core vendor).
 */
declare(strict_types=1);

if ( function_exists( 'sodium_crypto_secretbox_open' ) ) {
	return;
}

$corePath = defined( 'EZ_CORE_PATH' ) ? EZ_CORE_PATH : dirname( __DIR__ );
$autoload = $corePath . '/vendor/autoload.php';

if ( is_readable( $autoload ) ) {
	require_once $autoload;
}

if ( function_exists( 'sodium_crypto_secretbox_open' ) ) {
	return;
}

throw new RuntimeException(
	'libsodium required: enable PHP ext-sodium or run composer install in ' . $corePath
);
