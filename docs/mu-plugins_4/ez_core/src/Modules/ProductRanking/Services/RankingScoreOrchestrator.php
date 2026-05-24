<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services;

use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductRankScoresRepository;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\HottestScoreService;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\PopularScoreService;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\TopsaleScoreService;

final class RankingScoreOrchestrator
{
    /**
     * @param list<string> $facets popular|hottest|topsale
     */
    public static function recalculate(int $productId, array $facets = ['popular', 'hottest', 'topsale']): void
    {
        if ($productId < 1 || ! ProductRankingSchema::tablesVerified()) {
            return;
        }

        $payload = [];
        foreach ($facets as $facet) {
            switch ($facet) {
                case 'popular':
                    $payload['popular'] = PopularScoreService::computeForProduct($productId);
                    break;
                case 'hottest':
                    $payload['hottest'] = HottestScoreService::computeForProduct($productId);
                    break;
                case 'topsale':
                    $payload['topsale'] = TopsaleScoreService::computeForProduct($productId);
                    break;
            }
        }

        if ([] !== $payload) {
            ProductRankScoresRepository::upsertPartial($productId, $payload);
            self::bustSnapshotCache();
        }
    }

    /**
     * @param list<int> $productIds
     * @param list<string> $facets
     */
    public static function recalculateMany(array $productIds, array $facets = ['hottest']): void
    {
        foreach (array_unique(array_filter(array_map('intval', $productIds))) as $productId) {
            if ($productId > 0) {
                self::recalculate($productId, $facets);
            }
        }
    }

    private static function bustSnapshotCache(): void
    {
        if (function_exists('wp_cache_flush_group')) {
            wp_cache_flush_group(\EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::CACHE_GROUP);
        }
    }
}
