<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Repositories;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use Illuminate\Database\Capsule\Manager as Capsule;

final class TopsaleEventRepository
{
    private const TABLE = 'held_orders_list';

    public static function scoreForProduct(int $productId): int
    {
        if ($productId < 1 || ! self::tableExists()) {
            return 0;
        }

        if (ProductPenaltyRepository::isPenalized($productId, 'topsale')) {
            return 0;
        }

        $table = self::tableName();
        $powerMap = RankingConfig::topsalePowerMap();
        $rows = Capsule::connection(CapsuleBoot::CONNECTION_WP)->select(
            "SELECT `count`, `level` FROM `{$table}` WHERE room_id = ?",
            [$productId]
        );

        $sum = 0.0;
        foreach ($rows as $row) {
            $level = (int) ($row->level ?? 1);
            $power = $powerMap[$level] ?? 1.0;
            $sum += (int) ($row->count ?? 0) * $power;
        }

        return (int) round($sum);
    }

    public static function insertHeldRow(
        int $roomId,
        int $orderId,
        int $count,
        int $userId,
        int $level,
        int $heldTime,
    ): bool {
        if ($roomId < 1 || $orderId < 1 || $count < 1 || ! self::tableExists()) {
            return false;
        }

        $table = self::tableName();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $exists = $conn->selectOne(
            "SELECT 1 FROM `{$table}` WHERE order_id = ? AND room_id = ? LIMIT 1",
            [$orderId, $roomId]
        );
        if ($exists !== null) {
            return false;
        }

        $divisor = ProductPenaltyRepository::topsaleQuantityDivisor($roomId);
        if ($divisor !== null && $divisor > 0) {
            $count = (int) round($count / $divisor);
        }

        $conn->table($table)->insert([
            'room_id' => $roomId,
            'order_id' => $orderId,
            'count' => $count,
            'user_id' => $userId,
            'level' => max(1, min(4, $level)),
            'held_time' => (string) $heldTime,
        ]);

        return true;
    }

    public static function purgeOlderThanWindow(): void
    {
        if (! self::tableExists()) {
            return;
        }

        $table = self::tableName();
        $days = RankingConfig::TOPSALE_WINDOW_DAYS;
        Capsule::connection(CapsuleBoot::CONNECTION_WP)->affectingStatement(
            "DELETE FROM `{$table}` WHERE CAST(held_time AS UNSIGNED) < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL {$days} DAY))"
        );
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
