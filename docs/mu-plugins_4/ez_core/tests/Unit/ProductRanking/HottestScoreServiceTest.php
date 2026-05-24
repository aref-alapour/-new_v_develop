<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\Services\Score\HottestScoreService;

test('hottest score returns zero for invalid product id', function () {
    expect(HottestScoreService::computeForProduct(0))->toBe(0);
});
