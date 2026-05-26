<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Crypto\PayloadCipher;

beforeEach(function () {
	if ( ! function_exists( 'sodium_crypto_aead_aes256gcm_encrypt' ) ) {
		$this->markTestSkipped( 'AES-GCM requires ext-sodium.' );
	}
});

function ez_test_sub_secret_b64url(): string {
	$raw = random_bytes( SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES );

	return rtrim( strtr( base64_encode( $raw ), '+/', '-_' ), '=' );
}

it('round-trips plaintext through envelope', function () {
	$sub   = ez_test_sub_secret_b64url();
	$plain = '{"product_id":1,"day_start_time":1700000000}';

	$wire   = PayloadCipher::encrypt( $plain, $sub );
	$out    = PayloadCipher::decrypt( $wire, $sub );

	expect( PayloadCipher::isEnvelope( $wire ) )->toBeTrue();
	expect( $out )->toBe( $plain );
});

it('detects non-envelope bodies', function () {
	expect( PayloadCipher::isEnvelope( '{"product_id":1}' ) )->toBeFalse();
	expect( PayloadCipher::isEnvelope( '' ) )->toBeFalse();
});

it('fails decrypt with wrong key', function () {
	$sub  = ez_test_sub_secret_b64url();
	$wire = PayloadCipher::encrypt( '{"a":1}', $sub );

	expect( fn () => PayloadCipher::decrypt( $wire, ez_test_sub_secret_b64url() ) )
		->toThrow( RuntimeException::class );
});
