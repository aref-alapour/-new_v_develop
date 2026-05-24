<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Repositories\EzAjaxNonceRepository;

test('nonce useOnce accepts first use and rejects replay', function () {
    $this->skipUnlessDb();
    $this->skipUnlessSecret();

    $repo = new EzAjaxNonceRepository();
    $nonce = bin2hex(random_bytes(16));

    expect($repo->useOnce($nonce, 60))->toBeTrue();
    expect($repo->useOnce($nonce, 60))->toBeFalse();
})->group('gateway');
