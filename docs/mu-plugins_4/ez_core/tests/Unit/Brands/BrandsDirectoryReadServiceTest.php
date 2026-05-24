<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\Brands\Services\BrandsDirectoryReadService;
use EscapeZoom\Core\Tests\Support\FragmentAssertions;

test('buildFragment page 1 returns 200 and substantial html', function () {
    $this->skipUnlessDb();

    $result = (new BrandsDirectoryReadService())->buildFragment(1);

    expect($result->status)->toBe(200);
    expect(strlen($result->html))->toBeGreaterThan(1000);

    $structure = FragmentAssertions::inspect($result->html);
    expect($structure['swap_root'])->toBeTrue();
})->group('gateway');
