<?php

declare(strict_types=1);

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;
use EscapeZoom\Core\Tests\Support\FragmentAssertions;
use EscapeZoom\Core\Tests\Support\GatewayHttpClient;

test('brands fragment returns html with swap root and grid', function () {
    $this->skipUnlessHttp();
    $this->skipUnlessSecret();

    $baseUrl = $this->gatewayBaseUrl();
    $body = '{"page":1}';
    $signer = EzAjaxSigner::forAction('brands.fragment', 'POST', $baseUrl, $body, $this->sharedSecret());
    $client = new GatewayHttpClient();
    $resp = $client->post($baseUrl, $body, $signer->headers);

    expect($resp['error'])->toBeNull();
    expect($resp['status'])->toBe(200);
    expect($resp['body'])->toContain('id="brands-directory-swap"');
    expect($resp['body'])->toContain('grid grid-cols-2');

    $structure = FragmentAssertions::inspect($resp['body']);
    expect($structure['swap_root'])->toBeTrue();
    expect($structure['persian_nav'])->toBeTrue();
})->group('http', 'gateway');
