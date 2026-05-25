<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Auth\SignatureVerifier;
use EZ\Ajax\Auth\SubKey;

it('builds canonical v1 line', function () {
	$line = SignatureVerifier::canonical(
		'POST',
		'/ajax',
		'booking.sans_day',
		'client-1',
		'web-anon',
		1700000000,
		'abc123',
		'{"product_id":1}'
	);

	expect( $line )->toBe(
		'v1|POST|/ajax|booking.sans_day|client-1|web-anon|1700000000|abc123|' . hash( 'sha256', '{"product_id":1}' )
	);
});

it('verifies a signed gateway request', function () {
	$master = 'test-master-secret-32chars!!!!';
	$kid    = 'v1';
	$client = 'unit-test-client';
	$exp    = time() + 900;
	$sub    = SubKey::deriveBase64Url( $master, $kid, $client, $exp );

	$body   = '{"product_id":99,"day_start_time":1700000000}';
	$action = 'booking.sans_day';
	$ts     = time();
	$nonce  = 'deadbeef';
	$canonical = SignatureVerifier::canonical( 'POST', '/ajax', $action, $client, 'web-anon', $ts, $nonce, $body );
	$sig    = SignatureVerifier::sign( $sub, $canonical );

	$err = SignatureVerifier::verify(
		'POST',
		'/ajax',
		$action,
		$body,
		array(
			'x-ez-signature'   => $sig,
			'x-ez-timestamp'   => (string) $ts,
			'x-ez-nonce'       => $nonce,
			'x-ez-client-id'   => $client,
			'x-ez-client-kind' => 'web-anon',
			'x-ez-kid'         => $kid,
			'x-ez-sub-expires' => (string) $exp,
			'x-ez-sub-secret'  => $sub,
		)
	);

	expect( $err )->toBeNull();
});

it('rejects bad signature', function () {
	$sub = SubKey::deriveBase64Url( 'secret', 'v1', 'c', time() + 60 );
	$err = SignatureVerifier::verify(
		'POST',
		'/ajax',
		'booking.sans_day',
		'{}',
		array(
			'x-ez-signature'   => 'bad',
			'x-ez-timestamp'   => (string) time(),
			'x-ez-nonce'       => 'n',
			'x-ez-client-id'   => 'c',
			'x-ez-client-kind' => 'web-anon',
			'x-ez-sub-secret'  => $sub,
		)
	);

	expect( $err )->toBe( 'BAD_SIGNATURE' );
});
