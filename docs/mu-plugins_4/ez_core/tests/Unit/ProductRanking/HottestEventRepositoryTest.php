<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\Repositories\HottestEventRepository;
use EscapeZoom\Core\Tests\Support\SchemaAssertions;

test('deleteByCommentId returns null when hottest_products table missing', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (SchemaAssertions::hasTable('hottest_products')) {
        test()->markTestSkipped('hottest_products exists — cannot test missing-table path');
    }

    expect(HottestEventRepository::deleteByCommentId(1))->toBeNull();
});

test('deleteByCommentId returns null for invalid comment id', function () {
    expect(HottestEventRepository::deleteByCommentId(0))->toBeNull();
});
