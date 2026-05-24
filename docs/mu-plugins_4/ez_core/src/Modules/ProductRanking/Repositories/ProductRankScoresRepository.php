<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Repositories;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use Illuminate\Database\Capsule\Manager as Capsule;

final class ProductRankScoresRepository
{
    /**
     * @param array{popular?: int, hottest?: int, topsale?: int} $scores
     */
    public static function upsertPartial(int $productId, array $scores): void
    {
        if ($productId < 1 || [] === $scores || ! ProductRankingSchema::tablesVerified()) {
            return;
        }

        $table = ProductRankingSchema::scoresTable();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $existing = $conn->table($table)->where('product_id', $productId)->first();

        $row = [
            'product_id' => $productId,
            'score_popular' => isset($scores['popular'])
                ? max(0, (int) $scores['popular'])
                : (int) ($existing->score_popular ?? 0),
            'score_hottest' => isset($scores['hottest'])
                ? max(0, (int) $scores['hottest'])
                : (int) ($existing->score_hottest ?? 0),
            'score_topsale' => isset($scores['topsale'])
                ? max(0, (int) $scores['topsale'])
                : (int) ($existing->score_topsale ?? 0),
            'scores_updated_at' => gmdate('Y-m-d H:i:s'),
        ];

        if ($existing === null) {
            $conn->table($table)->insert($row);

            return;
        }

        $conn->table($table)->where('product_id', $productId)->update($row);
    }
}
