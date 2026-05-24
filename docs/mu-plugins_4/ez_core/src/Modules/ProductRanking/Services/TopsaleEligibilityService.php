<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Services;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\WordPressCoreTables;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\TopsaleEventRepository;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Records eligible held orders incrementally (legacy update_held_sans_table_func per order).
 */
final class TopsaleEligibilityService
{
    private const ELIGIBLE_STATUSES = [
        'wc-partially-paid',
        'wc-held',
        'wc-completed',
        'wc-walletx',
        'wc-completed-paid',
    ];

    public static function tryRecordOrder(int $orderId): bool
    {
        if ($orderId < 1 || ! CapsuleBoot::isBooted()) {
            return false;
        }

        $markting = self::fetchMarktingRow($orderId);
        if ($markting === null) {
            return false;
        }

        $status = (string) ($markting['order_status'] ?? '');
        if (! in_array($status, self::ELIGIBLE_STATUSES, true)) {
            return false;
        }

        $bookingTime = self::fetchBookingTime($orderId);
        if ($bookingTime === null) {
            return false;
        }

        $now = time();
        if ($bookingTime >= $now - (4 * 3600) || $bookingTime <= $now - (30 * 24 * 3600)) {
            return false;
        }

        $productId = (int) ($markting['game_id'] ?? 0);
        $quantity = (float) ($markting['order_tickets_quantity'] ?? 0);
        $userId = (int) ($markting['customer_id'] ?? 0);
        $level = (int) ($markting['customer_level'] ?? 1);
        if ($productId < 1 || $quantity <= 0) {
            return false;
        }

        $inserted = TopsaleEventRepository::insertHeldRow(
            $productId,
            $orderId,
            (int) round($quantity),
            $userId,
            max(1, min(4, $level)),
            $bookingTime,
        );

        if ($inserted) {
            RankingScoreOrchestrator::recalculate($productId, ['topsale']);
        }

        return $inserted;
    }

    public static function refreshTopsaleForOrder(int $orderId): void
    {
        if ($orderId < 1 || ! CapsuleBoot::isBooted()) {
            return;
        }

        $table = 'held_orders_list';
        if (! Capsule::connection(CapsuleBoot::CONNECTION_WP)->getSchemaBuilder()->hasTable($table)) {
            return;
        }

        $rows = Capsule::connection(CapsuleBoot::CONNECTION_WP)->select(
            "SELECT room_id FROM `{$table}` WHERE order_id = ?",
            [$orderId]
        );
        foreach ($rows as $row) {
            $productId = (int) ($row->room_id ?? 0);
            if ($productId > 0) {
                RankingScoreOrchestrator::recalculate($productId, ['topsale']);
            }
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function fetchMarktingRow(int $orderId): ?array
    {
        $table = WordPressCoreTables::prefix() . 'markting';
        if (! Capsule::connection(CapsuleBoot::CONNECTION_WP)->getSchemaBuilder()->hasTable($table)) {
            return null;
        }

        $row = Capsule::connection(CapsuleBoot::CONNECTION_WP)->selectOne(
            "SELECT order_id, game_id, order_tickets_quantity, customer_id, customer_level, order_status
             FROM `{$table}` WHERE order_id = ? LIMIT 1",
            [$orderId]
        );

        return $row === null ? null : (array) $row;
    }

    private static function fetchBookingTime(int $orderId): ?int
    {
        if (! CapsuleBoot::escapezoConfigured()) {
            error_log('ez_core ProductRanking: Escapezo DB not configured; skipping booking_time lookup for order ' . $orderId);

            return null;
        }

        $value = Capsule::connection(CapsuleBoot::CONNECTION_ESCAPEZO)
            ->table('zb_booking_history')
            ->where('wc_order_id', $orderId)
            ->orderByDesc('booking_time')
            ->value('booking_time');

        $bookingTime = is_numeric($value) ? (int) $value : 0;

        return $bookingTime > 0 ? $bookingTime : null;
    }
}
