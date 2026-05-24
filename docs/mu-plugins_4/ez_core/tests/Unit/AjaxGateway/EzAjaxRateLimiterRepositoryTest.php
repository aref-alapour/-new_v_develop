<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Repositories\EzAjaxRateLimiterRepository;

test('rate limiter allows capacity consumes then rejects', function () {
    $this->skipUnlessDb();

    $repo = new EzAjaxRateLimiterRepository();
    $bucket = 'ez-pest:' . bin2hex(random_bytes(8));

    for ($i = 0; $i < 5; $i++) {
        expect($repo->consume($bucket, 5, 60))->toBeTrue();
    }

    expect($repo->consume($bucket, 5, 60))->toBeFalse();
})->group('gateway');
