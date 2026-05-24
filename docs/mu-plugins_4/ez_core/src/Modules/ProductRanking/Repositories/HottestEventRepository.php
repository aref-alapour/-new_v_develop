<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Repositories;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use Illuminate\Database\Capsule\Manager as Capsule;

final class HottestEventRepository
{
    private const TABLE = 'hottest_products';

    /**
     * @return array{weighted_rate_sum: float, weight_sum: int}
     */
    public static function aggregateForProduct(int $productId): array
    {
        if ($productId < 1 || ! self::tableExists()) {
            return ['weighted_rate_sum' => 0.0, 'weight_sum' => 0];
        }

        $table = self::tableName();
        $cutoff = time() - (RankingConfig::HOTTEST_WINDOW_DAYS * 86400);
        $row = Capsule::connection(CapsuleBoot::CONNECTION_WP)->selectOne(
            "SELECT
                COALESCE(SUM(CAST(w_rate AS DECIMAL(12,4)) * w_comments_count), 0) AS weighted_rate_sum,
                COALESCE(SUM(w_comments_count), 0) AS weight_sum
             FROM `{$table}`
             WHERE product_id = ?
               AND CAST(`time` AS UNSIGNED) >= ?",
            [$productId, $cutoff]
        );

        return [
            'weighted_rate_sum' => (float) ($row->weighted_rate_sum ?? 0),
            'weight_sum' => (int) ($row->weight_sum ?? 0),
        ];
    }

    /**
     * Delete expired rows; return affected product IDs.
     *
     * @return list<int>
     */
    public static function deleteByCommentId(int $commentId): ?int
    {
        if ($commentId < 1 || ! self::tableExists()) {
            return null;
        }

        $table = self::tableName();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $row = $conn->selectOne(
            "SELECT product_id FROM `{$table}` WHERE comment_id = ? LIMIT 1",
            [$commentId]
        );
        if ($row === null) {
            return null;
        }

        $productId = (int) ($row->product_id ?? 0);
        $conn->affectingStatement(
            "DELETE FROM `{$table}` WHERE comment_id = ?",
            [$commentId]
        );

        return $productId > 0 ? $productId : null;
    }

    public static function purgeExpiredAndAffectedProductIds(): array
    {
        if (! self::tableExists()) {
            return [];
        }

        $table = self::tableName();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $cutoff = time() - (RankingConfig::HOTTEST_WINDOW_DAYS * 86400);

        $ids = $conn->select(
            "SELECT DISTINCT product_id FROM `{$table}` WHERE CAST(`time` AS UNSIGNED) < ?",
            [$cutoff]
        );
        $productIds = [];
        foreach ($ids as $row) {
            $productIds[] = (int) $row->product_id;
        }

        $conn->affectingStatement(
            "DELETE FROM `{$table}` WHERE CAST(`time` AS UNSIGNED) < ?",
            [$cutoff]
        );

        return array_values(array_unique(array_filter($productIds)));
    }

    private static function tableExists(): bool
    {
        if (! CapsuleBoot::isBooted()) {
            return false;
        }

        return Capsule::connection(CapsuleBoot::CONNECTION_WP)->getSchemaBuilder()->hasTable(self::TABLE);
    }

    private static function tableName(): string
    {
        return self::TABLE;
    }
}
