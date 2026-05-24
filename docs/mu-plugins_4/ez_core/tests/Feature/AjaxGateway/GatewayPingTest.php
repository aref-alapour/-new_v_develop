<?php

declare(strict_types=1);

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;
use EscapeZoom\Core\Tests\Support\GatewayHttpClient;

test('ping returns pong over http', function () {
    $this->skipUnlessHttp();
    $this->skipUnlessSecret();

    $baseUrl = $this->gatewayBaseUrl();
    $signer = EzAjaxSigner::forAction('ping', 'POST', $baseUrl, '', $this->sharedSecret());
    $client = new GatewayHttpClient();
    $resp = $client->post($baseUrl, '', $signer->headers);

    expect($resp['error'])->toBeNull();
    expect($resp['status'])->toBe(200);

    $data = json_decode($resp['body'], true);
    expect($data)->toBeArray();
    expect($data['ok'] ?? false)->toBeTrue();
    expect($data['data']['pong'] ?? false)->toBeTrue();
})->group('http', 'gateway');
