<?php

declare(strict_types=1);

/**
 * Copy to tests/Unit/{ModuleName}/{ServiceName}Test.php when adding a new read service.
 *
 * Example: tests/Unit/ProductsSnapshot/ProductsSnapshotReadServiceTest.php
 */

use EscapeZoom\Core\Tests\Support\SchemaAssertions;

// use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotSchema;
// use EscapeZoom\Core\Modules\ProductsSnapshot\Services\ProductsSnapshotReadService;

test('read service returns expected dto', function () {
    $this->skipUnlessDb();

    // expect(SchemaAssertions::hasTable(ProductsSnapshotSchema::snapshotTable()))->toBeTrue();

    // $result = (new ProductsSnapshotReadService())->fetch(/* args */);
    // expect($result->status)->toBe(200);

    expect(true)->toBeTrue('Replace this stub when the module exists.');
})->group('gateway')->skip('Template only — copy and implement for your module.');
