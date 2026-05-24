<?php

declare(strict_types=1);

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotSearchService;

test('products snapshot search returns empty for short term', function () {
    expect(ProductsSnapshotSearchService::searchByName('a'))->toBe([]);
    expect(ProductsSnapshotSearchService::productIdsMatchingName(''))->toBe([]);
});

test('products snapshot search queries database when booted', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (! CapsuleBoot::isBooted()) {
        test()->markTestSkipped('Capsule not booted');
    }

    $results = ProductsSnapshotSearchService::searchByName('اتاق', 5);
    expect($results)->toBeArray();

    foreach ($results as $row) {
        expect($row)->toHaveKeys(['product_id', 'product_name', 'product_image_url', 'product_url']);
        expect($row['product_id'])->toBeInt();
    }
});
