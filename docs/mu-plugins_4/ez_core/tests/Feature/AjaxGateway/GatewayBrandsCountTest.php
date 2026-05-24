<?php

declare(strict_types=1);

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;
use EscapeZoom\Core\Tests\Support\GatewayHttpClient;

test('brands count returns json without full wp load', function () {
    $this->skipUnlessHttp();
    $this->skipUnlessSecret();

    $baseUrl = $this->gatewayBaseUrl();
    $signer = EzAjaxSigner::forAction('brands.count', 'POST', $baseUrl, '', $this->sharedSecret());
    $client = new GatewayHttpClient();
    $resp = $client->post($baseUrl, '', $signer->headers);

    expect($resp['error'])->toBeNull();
    expect($resp['status'])->toBe(200);

    $data = json_decode($resp['body'], true);
    expect($data['ok'] ?? false)->toBeTrue();
    expect($data['data']['count'] ?? null)->toBeInt();
    expect($data['data']['count'])->toBeGreaterThan(0);
    expect($data['data']['wp_loaded'] ?? true)->toBeFalse();
})->group('http', 'gateway');
