<?php

declare(strict_types=1);

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;
use EscapeZoom\Core\Tests\Support\GatewayHttpClient;
use EscapeZoom\Core\Tests\TestCase;

test('replayed nonce returns 409 NONCE_REPLAY', function () {
    $this->skipUnlessHttp();
    $this->skipUnlessSecret();

    $baseUrl = $this->gatewayBaseUrl();
    $body = '{"page":1}';
    $signer = EzAjaxSigner::forAction('brands.fragment', 'POST', $baseUrl, $body, $this->sharedSecret());
    $client = new GatewayHttpClient();

    $first = $client->post($baseUrl, $body, $signer->headers);
    expect($first['error'])->toBeNull();
    expect($first['status'])->toBe(200);

    $replay = $client->post($baseUrl, $body, $signer->headers);
    expect($replay['error'])->toBeNull();
    expect($replay['status'])->toBe(409);

    $parsed = TestCase::parseGatewayJsonError($replay['body']);
    expect($parsed['code'])->toBe('NONCE_REPLAY');
})->group('http', 'gateway');

test('bad signature returns 401 BAD_SIGNATURE', function () {
    $this->skipUnlessHttp();
    $this->skipUnlessSecret();

    $baseUrl = $this->gatewayBaseUrl();
    $signer = EzAjaxSigner::forAction('ping', 'POST', $baseUrl, '', $this->sharedSecret())->withBadSignature();
    $client = new GatewayHttpClient();
    $resp = $client->post($baseUrl, '', $signer->headers);

    expect($resp['error'])->toBeNull();
    expect($resp['status'])->toBe(401);

    $parsed = TestCase::parseGatewayJsonError($resp['body']);
    expect($parsed['code'])->toBe('BAD_SIGNATURE');
})->group('http', 'gateway');
