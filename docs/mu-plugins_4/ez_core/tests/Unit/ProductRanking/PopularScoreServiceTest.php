<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\PopularScoreService;

test('popular score returns zero for invalid product id', function () {
    expect(PopularScoreService::computeForProduct(0))->toBe(0);
});

test('popular formula uses views divisor constant', function () {
    expect(RankingConfig::POPULAR_VIEWS_DIVISOR)->toBe(925000);
});
