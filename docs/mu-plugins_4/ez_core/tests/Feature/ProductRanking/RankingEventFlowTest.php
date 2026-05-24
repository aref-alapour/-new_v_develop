<?php

declare(strict_types=1);

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingScoreOrchestrator;
use EscapeZoom\Core\Tests\Support\SchemaAssertions;
use Illuminate\Database\Capsule\Manager as Capsule;

test('ez_ranking_recalculate action persists scores row', function () {
    if (! extension_loaded('pdo_mysql')) {
        test()->markTestSkipped('pdo_mysql not loaded — run tests inside Docker PHP');
    }

    if (! SchemaAssertions::hasTable(ProductRankingSchema::scoresTable())) {
        test()->markTestSkipped('wp_product_rank_scores not applied');
    }

    $productId = 999_999_002;
    do_action('ez_ranking_recalculate', $productId, ['popular', 'hottest', 'topsale']);
    RankingScoreOrchestrator::recalculate($productId, ['popular', 'hottest', 'topsale']);

    $table = ProductRankingSchema::scoresTable();
    $row = Capsule::connection(CapsuleBoot::CONNECTION_WP)->selectOne(
        "SELECT product_id, score_popular, score_hottest, score_topsale FROM `{$table}` WHERE product_id = ?",
        [$productId]
    );

    expect($row)->not->toBeNull()
        ->and((int) $row->product_id)->toBe($productId);
});
