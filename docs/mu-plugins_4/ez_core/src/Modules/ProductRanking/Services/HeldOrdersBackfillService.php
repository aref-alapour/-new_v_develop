<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\WordPressCoreTables;
use EscapeZoom\Core\Modules\ProductRanking\RankingConfig;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\TopsaleEventRepository;
use Illuminate\Database\Capsule\Manager as Capsule;

final class HeldOrdersBackfillService
{
    /**
     * @param list<int> $orderIds
     *
     * @return array<int, int> order_id => booking_time
     */
    public static function fetchBookingTimesForOrders(array $orderIds): array
    {
        $orderIds = array_values(array_filter(array_map('intval', $orderIds)));
        if ($orderIds === [] || ! CapsuleBoot::escapezoConfigured()) {
            return [];
        }

        $now = time();
        $map = [];
        $rows = Capsule::connection(CapsuleBoot::CONNECTION_ESCAPEZO)
            ->table('zb_booking_history')
            ->whereIn('wc_order_id', $orderIds)
            ->orderByDesc('booking_time')
            ->get(['wc_order_id', 'booking_time']);

        foreach ($rows as $row) {
            $orderId = (int) ($row->wc_order_id ?? 0);
            if ($orderId < 1 || isset($map[$orderId])) {
                continue;
            }
            $bookingTime = (int) ($row->booking_time ?? 0);
            if ($bookingTime < $now - (4 * 3600) && $bookingTime > $now - (30 * 24 * 3600)) {
                $map[$orderId] = $bookingTime;
            }
        }

        return $map;
    }

    /**
     * @param array<int, int> $orderToBookingTime
     *
     * @return list<array{room_id: int, order_id: int}>
     */
    public static function syncHeldRowsFromMarkting(array $orderToBookingTime): array
    {
        if ($orderToBookingTime === [] || ! CapsuleBoot::isBooted()) {
            return [];
        }

        $marktingTable = WordPressCoreTables::prefix() . 'markting';
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        if (! $conn->getSchemaBuilder()->hasTable($marktingTable)) {
            return [];
        }

        $inserted = [];
        $orderIds = array_keys($orderToBookingTime);

        $marktingRows = $conn->table($marktingTable)
            ->whereIn('order_id', $orderIds)
            ->get(['order_id', 'game_id', 'order_tickets_quantity', 'customer_id', 'customer_level']);

        foreach ($marktingRows as $mr) {
            $orderId = (int) ($mr->order_id ?? 0);
            $productId = (int) ($mr->game_id ?? 0);
            $quantity = (float) ($mr->order_tickets_quantity ?? 0);
            $userId = (int) ($mr->customer_id ?? 0);
            $level = (int) ($mr->customer_level ?? 1);

            if (! isset($orderToBookingTime[$orderId]) || $productId < 1 || $quantity <= 0) {
                continue;
            }

            $divisor = ProductPenaltyRepository::topsaleQuantityDivisor($productId);
            if ($divisor !== null && $divisor > 0) {
                $quantity = $quantity / $divisor;
            }

            $ok = TopsaleEventRepository::insertHeldRow(
                $productId,
                $orderId,
                (int) round($quantity),
                $userId,
                max(1, min(4, $level)),
                $orderToBookingTime[$orderId],
            );

            if ($ok) {
                $inserted[] = ['room_id' => $productId, 'order_id' => $orderId];
            }
        }

        return $inserted;
    }

    public static function purgeHeldWindow(): void
    {
        TopsaleEventRepository::purgeOlderThanWindow();
    }
}
