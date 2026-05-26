<?php

declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

test('secrets encrypt and decrypt round-trip', function () {
	$key = base64_encode( sodium_crypto_secretbox_keygen() );
	$plain = json_encode(
		array(
			'external'  => array(
				'host'     => 'mysql',
				'database' => 'escapezo_queries',
				'username' => 'root',
				'password' => 'test-pass',
			),
			'wordpress' => array(
				'host'         => 'mysql',
				'database'     => 'escapezo_ez9920',
				'username'     => 'root',
				'password'     => 'test-pass',
				'table_prefix' => 'wp_',
			),
			'gateway'   => array(
				'ajax_shared_secret'    => 'test-secret-at-least-16-chars',
				'booking_use_internal'  => true,
				'booking_native_sanses' => true,
			),
		),
		JSON_THROW_ON_ERROR
	);

	$blob = SecretsLoader::encrypt( $plain, $key );
	$out  = SecretsLoader::decrypt( $blob, $key );

	expect( $out )->toBe( $plain );
} )->skip( ! function_exists( 'sodium_crypto_secretbox_keygen' ), 'sodium extension required' );

test('externalDatabase returns parsed host and credentials', function () {
	$key = base64_encode( sodium_crypto_secretbox_keygen() );
	putenv( 'EZ_CORE_SECRETS_KEY=' . $key );

	if ( ! defined( 'EZ_CORE_PATH' ) ) {
		define( 'EZ_CORE_PATH', dirname( __DIR__, 2 ) );
	}
	$corePath = EZ_CORE_PATH;
	$encPath  = $corePath . '/config/secrets.enc';
	$plain    = json_encode(
		array(
			'external'  => array(
				'host'     => 'mysql:3306',
				'database' => 'escapezo_queries',
				'username' => 'root',
				'password' => 'secret',
			),
			'wordpress' => array(
				'host'         => 'mysql',
				'database'     => 'escapezo_ez9920',
				'username'     => 'root',
				'password'     => 'secret',
				'table_prefix' => 'wp_',
			),
			'gateway'   => array(
				'ajax_shared_secret'    => 'abcdefghijklmnop',
				'booking_use_internal'  => true,
				'booking_native_sanses' => false,
			),
		),
		JSON_THROW_ON_ERROR
	);

	file_put_contents( $encPath, SecretsLoader::encrypt( $plain, $key ) );

	try {
		$ref = new ReflectionClass( SecretsLoader::class );
		foreach ( array( 'secrets', 'bootAttempted', 'bootError' ) as $prop ) {
			if ( $ref->hasProperty( $prop ) ) {
				$p = $ref->getProperty( $prop );
				$p->setAccessible( true );
				$p->setValue( null );
			}
		}

		expect( SecretsLoader::boot() )->toBeTrue();
		$db = SecretsLoader::externalDatabase();
		expect( $db )->not->toBeNull();
		expect( $db['database'] )->toBe( 'escapezo_queries' );
		expect( $db['username'] )->toBe( 'root' );
		expect( SecretsLoader::bookingNativeSanses() )->toBeFalse();
		$wp = SecretsLoader::wordpressDatabase();
		expect( $wp )->not->toBeNull();
		expect( $wp['database'] )->toBe( 'escapezo_ez9920' );
		expect( $wp['table_prefix'] )->toBe( 'wp_' );
		expect( SecretsLoader::resolveAjaxSharedSecret() )->toBe( 'abcdefghijklmnop' );
	} finally {
		@unlink( $encPath );
		putenv( 'EZ_CORE_SECRETS_KEY' );
	}
} )->skip( ! function_exists( 'sodium_crypto_secretbox_keygen' ), 'sodium extension required' );
