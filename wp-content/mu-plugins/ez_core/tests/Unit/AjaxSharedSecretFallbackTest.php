<?php

declare(strict_types=1);

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;

it( 'derives deterministic ajax shared secret from wp keys fallback', function () {
	if ( SecretsLoader::isLoaded() ) {
		test()->markTestSkipped( 'secrets.enc is loaded; fallback path not active in this environment' );
	}

	if ( ! defined( 'AUTH_KEY' ) ) {
		define( 'AUTH_KEY', 'ez-test-auth-key-123456789' );
	}
	if ( ! defined( 'SECURE_AUTH_KEY' ) ) {
		define( 'SECURE_AUTH_KEY', 'ez-test-secure-key-987654321' );
	}
	if ( ! defined( 'DB_NAME' ) ) {
		define( 'DB_NAME', 'ez_test_db' );
	}
	if ( ! defined( 'DB_HOST' ) ) {
		define( 'DB_HOST', 'localhost' );
	}

	$secret1 = SecretsLoader::resolveAjaxSharedSecret();
	$secret2 = SecretsLoader::resolveAjaxSharedSecret();

	expect( $secret1 )->toStartWith( 'v1:' );
	expect( $secret1 )->toBe( $secret2 );
	expect( strlen( $secret1 ) )->toBeGreaterThan( 16 );
} );

