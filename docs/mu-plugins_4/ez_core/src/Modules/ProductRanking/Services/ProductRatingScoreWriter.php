<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services;

use EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader;

final class ProductRatingScoreWriter
{
    public static function syncProduct(int $productId): void
    {
        if ($productId < 1 || ! function_exists('update_post_meta')) {
            return;
        }

        $overall = self::resolveOverallRating($productId);
        update_post_meta($productId, 'ez_weighted_rating_overall', $overall);

        RankingScoreOrchestrator::recalculate($productId, ['popular', 'hottest']);
    }

    private static function resolveOverallRating(int $productId): float
    {
        $resolved = ProductRatingSummaryReader::resolveAxisAveragesForDisplay($productId);
        $axes = $resolved['axes'] ?? [];
        $productType = self::productTypeLabel($productId);

        if (function_exists('ez_product_rating_overall_from_axes')) {
            return (float) ez_product_rating_overall_from_axes($axes, $productType);
        }

        $values = array_filter($axes, static fn(float $v): bool => $v > 0.0005);
        if ([] === $values) {
            return 0.0;
        }

        return max(0.0, min(5.0, array_sum($values) / count($values)));
    }

    private static function productTypeLabel(int $productId): string
    {
        if (! function_exists('get_the_terms')) {
            return '';
        }

        $terms = get_the_terms($productId, 'product_cat');
        if (! is_array($terms) || [] === $terms) {
            return '';
        }

        return (string) ($terms[0]->name ?? '');
    }
}
