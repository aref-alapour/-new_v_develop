<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\TopsaleScoreService;

test('topsale score returns zero for invalid product id', function () {
    expect(TopsaleScoreService::computeForProduct(0))->toBe(0);
});

test('topsale power map has four levels', function () {
    expect(RankingConfig::topsalePowerMap())->toHaveKeys([1, 2, 3, 4]);
});
