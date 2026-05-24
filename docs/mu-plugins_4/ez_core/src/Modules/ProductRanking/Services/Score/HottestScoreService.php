<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services\Score;

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\HottestEventRepository;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductViewsRepository;

final class HottestScoreService
{
    public static function computeForProduct(int $productId): int
    {
        if ($productId < 1) {
            return 0;
        }

        if (ProductPenaltyRepository::isPenalized($productId, 'hottest')) {
            return 0;
        }

        $agg = HottestEventRepository::aggregateForProduct($productId);
        $weightSum = $agg['weight_sum'];
        if ($weightSum < 1) {
            return 0;
        }

        $averageRate = $agg['weighted_rate_sum'] / $weightSum;
        $bayesian = BayesianScoreCalculator::score($averageRate, $weightSum);
        $normalizedBayesian = (0.6 * $bayesian) + (0.4 * log($weightSum + 1));

        $views = ProductViewsRepository::totalsForProduct($productId);
        $viewCount = $views['views_30_sum'];
        $maxViews = RankingConfig::HOTTEST_MAX_VIEWS;
        $normalizedViews = (log($viewCount + 1) / log($maxViews + 1)) * 5.0;

        $hotScore = (0.67 * $normalizedBayesian) + (0.33 * $normalizedViews);

        return max(0, (int) round($hotScore * 1000));
    }
}
