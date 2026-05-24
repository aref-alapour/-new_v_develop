<?php

declare(strict_types=1);

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;

test('signer produces canonical v1 string and signature header', function () {
    $signer = (new EzAjaxSigner(
        'ping',
        'POST',
        'http://wo.escapezoom.local/ajax',
        '',
        'test-secret-key-for-unit-tests-only',
    ))->sign();

    expect($signer->canonical)->toStartWith('v1|POST|/ajax|ping|');
    expect($signer->headers)->toHaveKey('X-EZ-Signature');
    expect($signer->headers['X-EZ-Signature'])->not->toBe('');
    expect($signer->headers['X-EZ-Action'])->toBe('ping');
});

test('signature changes when body changes', function () {
    $secret = 'test-secret-key-for-unit-tests-only';
    $url = 'http://wo.escapezoom.local/ajax';

    $a = (new EzAjaxSigner('brands.fragment', 'POST', $url, '{"page":1}', $secret))->sign();
    $b = (new EzAjaxSigner('brands.fragment', 'POST', $url, '{"page":2}', $secret))->sign();

    expect($a->headers['X-EZ-Signature'])->not->toBe($b->headers['X-EZ-Signature']);
});

test('forAction factory returns signed headers', function () {
    $signer = EzAjaxSigner::forAction(
        'ping',
        'POST',
        'http://wo.escapezoom.local/ajax',
        '',
        'test-secret',
    );

    expect($signer->headers)->toHaveKey('X-EZ-Nonce');
});
