<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services\Score;

use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductViewsRepository;

final class PopularScoreService
{
    public static function computeForProduct(int $productId): int
    {
        if ($productId < 1) {
            return 0;
        }

        if (ProductPenaltyRepository::isPenalized($productId, 'popular')) {
            return 0;
        }

        $commentsCount = self::commentsCountForPopular($productId);
        $rate = self::legacyAverageRate($productId, $commentsCount);
        $views = ProductViewsRepository::totalsForProduct($productId);

        $score = ($commentsCount * $rate) + (($views['views_total'] * $views['views_30_sum']) / RankingConfig::POPULAR_VIEWS_DIVISOR);

        return max(0, (int) round($score));
    }

    private static function commentsCountForPopular(int $productId): int
    {
        if (! function_exists('get_post_meta')) {
            return 0;
        }

        $count = (int) get_post_meta($productId, 'comments_count_new', true);
        $divisor = ProductPenaltyRepository::popularCommentDivisor($productId);
        if ($divisor !== null && $divisor > 0) {
            $count = (int) round($count / $divisor);
        }

        return max(0, $count);
    }

    private static function legacyAverageRate(int $productId, int $commentsCount): float
    {
        if (! function_exists('get_post_meta')) {
            return 1.0;
        }

        if ($commentsCount < 1) {
            return 1.0;
        }

        $rate = get_post_meta($productId, 'product_rates', true);
        if (! is_array($rate)) {
            $overall = (float) get_post_meta($productId, 'ez_weighted_rating_overall', true);

            return $overall > 0 ? $overall : 1.0;
        }

        $keys = function_exists('ez_get_product_review_rate_keys')
            ? ez_get_product_review_rate_keys()
            : [1098, 1097, 1096, 1095, 1094];
        $sum = 0;
        foreach ($keys as $k) {
            $sum += (int) ($rate[ $k ] ?? 0);
        }

        return (float) ($sum / 5 / 20 / $commentsCount);
    }
}
