<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Repositories;

use EscapeZoom\Core\Database\CapsuleBoot;
use Illuminate\Database\Capsule\Manager as Capsule;

final class ProductViewsRepository
{
    private const TABLE = 'product_views';

    /**
     * @return array{views_total: int, views_30_sum: int}
     */
    public static function totalsForProduct(int $productId): array
    {
        if ($productId < 1 || ! self::tableExists()) {
            return ['views_total' => 0, 'views_30_sum' => 0];
        }

        $table = self::tableName();
        $since = gmdate('Y-m-d', time() - (30 * 86400));
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);

        $totalRow = $conn->selectOne(
            "SELECT COALESCE(SUM(`count`), 0) AS views_total FROM `{$table}` WHERE product_id = ?",
            [$productId]
        );
        $recentRow = $conn->selectOne(
            "SELECT COALESCE(SUM(`count`), 0) AS views_30_sum FROM `{$table}` WHERE product_id = ? AND `date` >= ?",
            [$productId, $since]
        );

        return [
            'views_total' => (int) ($totalRow->views_total ?? 0),
            'views_30_sum' => (int) ($recentRow->views_30_sum ?? 0),
        ];
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
