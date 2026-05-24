<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking;

/**
 * Business constants for ranking formulas (override via filters where noted).
 */
final class RankingConfig
{
    public const BAYESIAN_C = 4.3;

    public const BAYESIAN_M = 15;

    public const HOTTEST_MAX_VIEWS = 4000;

    public const POPULAR_VIEWS_DIVISOR = 925000;

    public const HOTTEST_WINDOW_DAYS = 90;

    public const TOPSALE_WINDOW_DAYS = 30;

    public const PENALTY_UNTIL_TIMESTAMP = 1739923199; // 2026-02-19 23:59:59

    /**
     * @return list<int>
     */
    public static function penaltyProductIds(): array
    {
        $default = [
            24194, 354862, 576159, 28325, 383915, 25616, 382454, 425891, 587887, 741186, 770574, 776644,
        ];

        if (! function_exists('apply_filters')) {
            return $default;
        }

        /** @var list<int> $filtered */
        $filtered = apply_filters('ez_ranking_penalty_product_ids', $default);

        return array_values(array_map('intval', $filtered));
    }

    /**
     * @return list<int>
     */
    public static function popularCommentPenaltyProductIds(): array
    {
        $default = [383915, 382454, 24194, 508099, 52537, 354862, 261593, 261541, 272235, 770574, 776644];

        if (! function_exists('apply_filters')) {
            return $default;
        }

        /** @var list<int> $filtered */
        $filtered = apply_filters('ez_ranking_popular_comment_penalty_product_ids', $default);

        return array_values(array_map('intval', $filtered));
    }

    public static function topsaleHeldPenaltyProductIds(): array
    {
        return [73114, 261541, 261593];
    }

    /**
     * @return array<int, float>
     */
    public static function topsalePowerMap(): array
    {
        return [1 => 0.2, 2 => 0.4, 3 => 1.0, 4 => 1.0];
    }

    public static function isPenaltyActive(): bool
    {
        return time() <= self::PENALTY_UNTIL_TIMESTAMP;
    }

    public static function incrementalRankingEnabled(): bool
    {
        if (! function_exists('apply_filters')) {
            return true;
        }

        return (bool) apply_filters('ez_core_incremental_ranking_enabled', true);
    }
}
