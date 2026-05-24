<?php

namespace EscapeZoom\Core\Modules\Commerce\Cli;

use EscapeZoom\Core\Modules\Commerce\Services\CheckoutLedgerService;

class MigrateOrdersCommand
{
    /**
     * ## OPTIONS
     *
     * [--dry-run]
     * : Only simulate writes.
     *
     * [--write]
     * : Persist migrated rows.
     *
     * [--from-order-id=<id>]
     * : Minimum order_id.
     *
     * [--to-order-id=<id>]
     * : Maximum order_id.
     *
     * [--chunk=<size>]
     * : Batch size. Default: 500.
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        global $wpdb;

        $dryRun = isset($assocArgs['dry-run']) || !isset($assocArgs['write']);
        $from = isset($assocArgs['from-order-id']) ? (int) $assocArgs['from-order-id'] : 0;
        $to = isset($assocArgs['to-order-id']) ? (int) $assocArgs['to-order-id'] : PHP_INT_MAX;
        $chunk = max(1, (int) ($assocArgs['chunk'] ?? 500));
        $checkpointFile = WP_CONTENT_DIR . '/uploads/ez-parity/migration-checkpoint.json';

        $service = new CheckoutLedgerService();
        if (!is_dir(dirname($checkpointFile))) {
            wp_mkdir_p(dirname($checkpointFile));
        }

        $checkpoint = ['last_order_id' => $from - 1];
        if (is_file($checkpointFile)) {
            $loaded = json_decode((string) file_get_contents($checkpointFile), true);
            if (is_array($loaded) && isset($loaded['last_order_id'])) {
                $checkpoint['last_order_id'] = (int) $loaded['last_order_id'];
            }
        }
        $cursor = max($from - 1, (int) $checkpoint['last_order_id']);

        while (true) {
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM wp_markting WHERE order_id > %d AND order_id <= %d ORDER BY order_id ASC LIMIT %d",
                    $cursor,
                    $to,
                    $chunk
                ),
                ARRAY_A
            );

            if (empty($rows)) {
                break;
            }

            foreach ($rows as $row) {
                $orderId = (int) ($row['order_id'] ?? 0);
                if ($orderId < 1) {
                    continue;
                }
                $cursor = $orderId;

                if ($dryRun) {
                    file_put_contents($checkpointFile, wp_json_encode(['last_order_id' => $cursor]));
                    continue;
                }

                $service->createOrUpdateIncart([
                    'order_id' => $orderId,
                    'user_id' => isset($row['customer_id']) ? (int) $row['customer_id'] : null,
                    'product_id' => isset($row['game_id']) ? (int) $row['game_id'] : null,
                    'quantity' => max(1, (int) ($row['order_tickets_quantity'] ?? 1)),
                    'stage_status' => 'wc-checkout',
                ]);

                $service->markCheckoutFilled($orderId, [
                    'finance' => [
                        'gross_amount' => (int) ($row['order_finall_price'] ?? 0),
                        'coupon_discount_amount' => (int) ($row['order_coupon_used'] ?? 0),
                        'level_discount_amount' => (int) ($row['order_level_discount'] ?? 0),
                        'paid_amount' => (int) ($row['order_paid'] ?? 0),
                        'payment_type' => (($row['order_method'] ?? '') === 'partial') ? 'prepaid' : 'complete',
                        'coupon_code' => $row['order_coupon'] ?? null,
                    ],
                ]);

                file_put_contents($checkpointFile, wp_json_encode(['last_order_id' => $cursor]));
            }
        }
    }
}
