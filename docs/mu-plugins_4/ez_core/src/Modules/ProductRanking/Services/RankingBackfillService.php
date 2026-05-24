<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\WordPressCoreTables;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\HottestScoreService;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\PopularScoreService;
use EscapeZoom\Core\Modules\ProductRanking\Services\Score\TopsaleScoreService;
use EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotTable;
use Illuminate\Database\Capsule\Manager as Capsule;

final class RankingBackfillService
{
    /**
     * @param list<string> $facets
     *
     * @return array{processed: int, next_after: int, done: bool}
     */
    public static function recalculateProductsChunk(int $afterProductId, int $limit, array $facets = ['popular', 'hottest', 'topsale']): array
    {
        $limit = max(1, min(500, $limit));
        $ids = self::fetchActiveProductIdsAfter($afterProductId, $limit);
        $last = $afterProductId;

        foreach ($ids as $productId) {
            RankingScoreOrchestrator::recalculate($productId, $facets);
            $last = max($last, $productId);
        }

        return [
            'processed' => count($ids),
            'next_after' => $last,
            'done' => count($ids) < $limit,
        ];
    }

    /**
     * @return array{inserted: int, skipped_escapezo: bool, affected_product_ids: list<int>}
     */
    public static function rebuildHeldOrdersList(): array
    {
        if (! CapsuleBoot::isBooted()) {
            return ['inserted' => 0, 'skipped_escapezo' => true, 'affected_product_ids' => []];
        }

        $eligibleStatuses = [
            'wc-partially-paid',
            'wc-held',
            'wc-completed',
            'wc-walletx',
            'wc-completed-paid',
        ];

        $marktingTable = WordPressCoreTables::prefix() . 'markting';
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        if (! $conn->getSchemaBuilder()->hasTable($marktingTable)) {
            return ['inserted' => 0, 'skipped_escapezo' => true, 'affected_product_ids' => []];
        }

        $orderRows = $conn->table($marktingTable)
            ->whereIn('order_status', $eligibleStatuses)
            ->pluck('order_id')
            ->all();

        $orderIds = array_values(array_filter(array_map('intval', $orderRows)));
        if ($orderIds === []) {
            HeldOrdersBackfillService::purgeHeldWindow();

            return ['inserted' => 0, 'skipped_escapezo' => ! CapsuleBoot::escapezoConfigured(), 'affected_product_ids' => []];
        }

        $bookingMap = HeldOrdersBackfillService::fetchBookingTimesForOrders($orderIds);
        $inserted = HeldOrdersBackfillService::syncHeldRowsFromMarkting($bookingMap);
        HeldOrdersBackfillService::purgeHeldWindow();

        $affected = array_values(array_unique(array_map('intval', array_column($inserted, 'room_id'))));
        RankingScoreOrchestrator::recalculateMany($affected, ['topsale']);

        return [
            'inserted' => count($inserted),
            'skipped_escapezo' => ! CapsuleBoot::escapezoConfigured(),
            'affected_product_ids' => $affected,
        ];
    }

    /**
     * @return array{samples: list<array<string, mixed>>, mismatches: int}
     */
    public static function reconcileSample(int $n): array
    {
        $n = max(1, min(50, $n));
        if (! ProductRankingSchema::tablesVerified()) {
            return ['samples' => [], 'mismatches' => 0];
        }

        $table = ProductRankingSchema::scoresTable();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $rows = $conn->select(
            "SELECT product_id, score_popular, score_hottest, score_topsale FROM `{$table}` ORDER BY score_popular DESC LIMIT ?",
            [$n]
        );

        $samples = [];
        $mismatches = 0;
        $threshold = 1;

        foreach ($rows as $row) {
            $productId = (int) $row->product_id;
            $storedPopular = (int) $row->score_popular;
            $storedHottest = (int) $row->score_hottest;
            $storedTopsale = (int) $row->score_topsale;
            $calcPopular = PopularScoreService::computeForProduct($productId);
            $calcHottest = HottestScoreService::computeForProduct($productId);
            $calcTopsale = TopsaleScoreService::computeForProduct($productId);

            $diff = max(
                abs($storedPopular - $calcPopular),
                abs($storedHottest - $calcHottest),
                abs($storedTopsale - $calcTopsale)
            );

            if ($diff > $threshold) {
                ++$mismatches;
            }

            $samples[] = [
                'product_id' => $productId,
                'stored' => ['popular' => $storedPopular, 'hottest' => $storedHottest, 'topsale' => $storedTopsale],
                'computed' => ['popular' => $calcPopular, 'hottest' => $calcHottest, 'topsale' => $calcTopsale],
                'max_diff' => $diff,
            ];
        }

        return ['samples' => $samples, 'mismatches' => $mismatches];
    }

    /**
     * @return list<int>
     */
    public static function fetchActiveProductIdsAfter(int $afterProductId, int $limit): array
    {
        if (! CapsuleBoot::isBooted()) {
            return [];
        }

        $snapshotTable = ProductsSnapshotTable::name();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        if ($conn->getSchemaBuilder()->hasTable($snapshotTable)) {
            $rows = $conn->table($snapshotTable)
                ->where('product_id', '>', $afterProductId)
                ->whereIn('product_state', ['active', 'updated'])
                ->orderBy('product_id')
                ->limit($limit)
                ->pluck('product_id');

            return array_values(array_map('intval', $rows->all()));
        }

        $posts = WordPressCoreTables::posts();
        $sql = "SELECT ID FROM `{$posts}` WHERE post_type = 'product' AND post_status = 'publish' AND ID > ? ORDER BY ID ASC LIMIT ?";
        $rows = $conn->select($sql, [$afterProductId, $limit]);

        return array_map(static fn (object $row): int => (int) $row->ID, $rows);
    }
}
