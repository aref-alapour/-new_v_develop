<?php

declare(strict_types=1);

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingScoreOrchestrator;
use EscapeZoom\Core\Tests\Support\SchemaAssertions;
use Illuminate\Database\Capsule\Manager as Capsule;

test('orchestrator upserts product_rank_scores row when table exists', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (! SchemaAssertions::hasTable(ProductRankingSchema::scoresTable())) {
        test()->markTestSkipped('wp_product_rank_scores not applied');
    }

    $productId = 999_999_001;
    RankingScoreOrchestrator::recalculate($productId, ['popular', 'hottest', 'topsale']);

    $table = ProductRankingSchema::scoresTable();
    $row = Capsule::connection(CapsuleBoot::CONNECTION_WP)->selectOne(
        "SELECT score_popular, score_hottest, score_topsale FROM `{$table}` WHERE product_id = ?",
        [$productId]
    );

    expect($row)->not->toBeNull()
        ->and((int) $row->score_popular)->toBeGreaterThanOrEqual(0)
        ->and((int) $row->score_hottest)->toBeGreaterThanOrEqual(0)
        ->and((int) $row->score_topsale)->toBeGreaterThanOrEqual(0);
});
