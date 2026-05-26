<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Auth\SignatureVerifier;
use EscapeZoom\Core\Modules\AjaxGateway\Crypto\PayloadCipher;
use EZ\Ajax\Auth\SubKey;

beforeEach(function () {
	if ( ! function_exists( 'sodium_crypto_aead_aes256gcm_encrypt' ) ) {
		$this->markTestSkipped( 'AES-GCM requires ext-sodium.' );
	}
});

it('verifies HMAC on encrypted wire body not inner plaintext', function () {
	$master = 'test-master-secret-32chars!!!!';
	$kid    = 'v1';
	$client = 'encrypt-test-client';
	$exp    = time() + 900;
	$sub    = SubKey::deriveBase64Url( $master, $kid, $client, $exp );

	$plainJson = '{"product_id":42,"day_start_time":1700000000}';
	$wire      = PayloadCipher::encrypt( $plainJson, $sub );

	expect( $wire )->not->toBe( $plainJson );

	$action = 'booking.open_sans';
	$ts     = time();
	$nonce  = 'cafebabe';
	$canonical = SignatureVerifier::canonical( 'POST', '/ajax', $action, $client, 'web-user', $ts, $nonce, $wire );
	$sig       = SignatureVerifier::sign( $sub, $canonical );

	$err = SignatureVerifier::verify(
		'POST',
		'/ajax',
		$action,
		$wire,
		array(
			'x-ez-signature'   => $sig,
			'x-ez-timestamp'   => (string) $ts,
			'x-ez-nonce'       => $nonce,
			'x-ez-client-id'   => $client,
			'x-ez-client-kind' => 'web-user',
			'x-ez-kid'         => $kid,
			'x-ez-sub-expires' => (string) $exp,
			'x-ez-sub-secret'  => $sub,
		)
	);

	expect( $err )->toBeNull();

	$badPlainSig = SignatureVerifier::sign(
		$sub,
		SignatureVerifier::canonical( 'POST', '/ajax', $action, $client, 'web-user', $ts, $nonce, $plainJson )
	);

	$errPlain = SignatureVerifier::verify(
		'POST',
		'/ajax',
		$action,
		$wire,
		array(
			'x-ez-signature'   => $badPlainSig,
			'x-ez-timestamp'   => (string) $ts,
			'x-ez-nonce'       => $nonce,
			'x-ez-client-id'   => $client,
			'x-ez-client-kind' => 'web-user',
			'x-ez-sub-secret'  => $sub,
		)
	);

	expect( $errPlain )->toBe( 'BAD_SIGNATURE' );
});
