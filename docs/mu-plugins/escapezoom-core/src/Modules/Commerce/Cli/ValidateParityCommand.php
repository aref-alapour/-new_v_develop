<?php

namespace EscapeZoom\Core\Modules\Commerce\Cli;

use EscapeZoom\Core\Modules\Commerce\Models\CheckoutOrder;
use EscapeZoom\Core\Modules\Commerce\Models\OrderFinance;
use EscapeZoom\Core\Modules\Commerce\Models\PaymentTransaction;

class ValidateParityCommand
{
    /**
     * ## OPTIONS
     *
     * [--output-dir=<dir>]
     * : Output directory for reports. Default: wp-content/uploads/ez-parity
     */
    public function __invoke(array $args, array $assocArgs): void
    {
        global $wpdb;

        $uploadDir = wp_upload_dir();
        $outputDir = $assocArgs['output-dir'] ?? ($uploadDir['basedir'] . '/ez-parity');
        if (!is_dir($outputDir)) {
            wp_mkdir_p($outputDir);
        }

        $legacyCount = (int) $wpdb->get_var("SELECT COUNT(*) FROM wp_markting");
        $ordersCount = (int) CheckoutOrder::query()->count();
        $financeCount = (int) OrderFinance::query()->count();
        $txCount = (int) PaymentTransaction::query()->count();
        $hardFailures = [];
        $softFailures = [];

        $negativeMoney = (int) OrderFinance::query()
            ->where('gross_amount', '<', 0)
            ->orWhere('payable_amount', '<', 0)
            ->orWhere('paid_amount', '<', 0)
            ->count();
        if ($negativeMoney > 0) {
            $hardFailures[] = ['type' => 'negative_money', 'count' => $negativeMoney];
        }

        $paidMoreThanPayable = (int) OrderFinance::query()
            ->whereColumn('paid_amount', '>', 'payable_amount')
            ->count();
        if ($paidMoreThanPayable > 0) {
            $hardFailures[] = ['type' => 'paid_gt_payable', 'count' => $paidMoreThanPayable];
        }

        $missingSnapshots = max(0, $ordersCount - (int) $wpdb->get_var("SELECT COUNT(*) FROM wp_ez_order_user_snapshot"));
        if ($missingSnapshots > 0) {
            $softFailures[] = ['type' => 'missing_user_snapshots', 'count' => $missingSnapshots];
        }

        $summary = [
            'legacy_count' => $legacyCount,
            'orders_count' => $ordersCount,
            'finance_count' => $financeCount,
            'transactions_count' => $txCount,
            'hard_failures' => $hardFailures,
            'soft_failures' => $softFailures,
            'generated_at' => gmdate('c'),
        ];

        $summaryPath = rtrim($outputDir, '/\\') . '/parity_summary.json';
        file_put_contents($summaryPath, wp_json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $csvPath = rtrim($outputDir, '/\\') . '/parity_summary.csv';
        $csvHandle = fopen($csvPath, 'w');
        if ($csvHandle !== false) {
            fputcsv($csvHandle, ['metric', 'value']);
            foreach ([
                'legacy_count' => $legacyCount,
                'orders_count' => $ordersCount,
                'finance_count' => $financeCount,
                'transactions_count' => $txCount,
                'hard_failures_count' => count($hardFailures),
                'soft_failures_count' => count($softFailures),
            ] as $metric => $value) {
                fputcsv($csvHandle, [$metric, (string) $value]);
            }
            fclose($csvHandle);
        }
    }
}
