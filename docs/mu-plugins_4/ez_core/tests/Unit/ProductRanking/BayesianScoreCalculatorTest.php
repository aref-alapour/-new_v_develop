<?php

declare(strict_types=1);

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\BayesianScoreCalculator;

test('bayesian score matches legacy formula for typical inputs', function () {
    $score = BayesianScoreCalculator::score(4.5, 20);
    $v = 20;
    $m = RankingConfig::BAYESIAN_M;
    $c = RankingConfig::BAYESIAN_C;
    $expected = (($v / ($v + $m)) * 4.5) + (($m / ($v + $m)) * $c);

    expect($score)->toBeGreaterThan(4.0)
        ->and($score)->toBeLessThan(4.5)
        ->and(abs($score - $expected))->toBeLessThan(0.0001);
});

test('bayesian score returns zero when vote count and minimum are zero', function () {
    expect(BayesianScoreCalculator::score(5.0, 0, 0.0, 0))->toBe(0.0);
});

test('hottest normalized components produce stable integer score', function () {
    $bayesian = BayesianScoreCalculator::score(4.2, 25);
    $normalizedBayesian = (0.6 * $bayesian) + (0.4 * log(26));
    $viewCount = 120;
    $maxViews = RankingConfig::HOTTEST_MAX_VIEWS;
    $normalizedViews = (log($viewCount + 1) / log($maxViews + 1)) * 5.0;
    $hotScore = (0.67 * $normalizedBayesian) + (0.33 * $normalizedViews);
    $stored = max(0, (int) round($hotScore * 1000));

    expect($stored)->toBeGreaterThan(0);
});
