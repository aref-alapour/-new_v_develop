<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use EscapeZoom\Core\Tests\Support\SchemaAssertions;

test('product rank scores table exists when bootstrap ddl applied', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (! SchemaAssertions::hasTable(ProductRankingSchema::scoresTable())) {
        test()->markTestSkipped('wp_product_rank_scores not applied — run ez_bootstrap_custom_tables.sql');
    }

    expect(ProductRankingSchema::tablesVerified())->toBeTrue();
});
