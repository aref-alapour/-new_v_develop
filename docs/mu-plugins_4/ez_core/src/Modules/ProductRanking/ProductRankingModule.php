<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking;

use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminNotices;
use EscapeZoom\Core\Modules\ProductRanking\Admin\ProductPenaltyAdminPage;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\HottestEventRepository;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingBackfillService;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingMaintenanceService;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingScoreOrchestrator;
use EscapeZoom\Core\Modules\ProductRanking\Services\TopsaleEligibilityService;

final class ProductRankingModule
{
    public static function register(): void
    {
        add_action('ez_ranking_recalculate', [self::class, 'onActionRecalculate'], 10, 2);
        add_action('woocommerce_order_status_changed', [self::class, 'onOrderStatusChanged'], 20, 4);
        add_action('ez_ranking_daily_maintenance', [RankingMaintenanceService::class, 'runDailyMaintenance']);
        add_action('ez_ranking_reconcile_weekly', [self::class, 'onWeeklyReconcile']);
        add_action('trash_comment', [self::class, 'onTrashComment'], 5, 1);
        add_action('delete_comment', [self::class, 'onDeleteComment'], 10, 2);

        add_action('init', [self::class, 'scheduleMaintenance'], 20);
        add_action('init', [self::class, 'maybeDisableLegacyRankingCrons'], 99);

        ProductPenaltyAdminNotices::register();

        add_action('admin_menu', [ProductPenaltyAdminPage::class, 'registerMenu']);
        add_action('admin_enqueue_scripts', [ProductPenaltyAdminPage::class, 'registerAssets']);
        add_action('wp_ajax_ez_penalty_list', [ProductPenaltyAdminPage::class, 'ajaxList']);
        add_action('wp_ajax_ez_penalty_form', [ProductPenaltyAdminPage::class, 'ajaxForm']);
        add_action('wp_ajax_ez_penalty_save', [ProductPenaltyAdminPage::class, 'ajaxSave']);
        add_action('wp_ajax_ez_penalty_delete', [ProductPenaltyAdminPage::class, 'ajaxDelete']);
        add_action('wp_ajax_ez_penalty_product_search', [ProductPenaltyAdminPage::class, 'ajaxProductSearch']);
    }

    /**
     * @param list<string>|string $facets
     */
    public static function onActionRecalculate(int $productId, $facets = ['popular', 'hottest', 'topsale']): void
    {
        if ($productId < 1) {
            return;
        }
        $facetList = is_array($facets) ? $facets : [$facets];
        RankingScoreOrchestrator::recalculate($productId, $facetList);
    }

    public static function onOrderStatusChanged(
        int $orderId,
        string $oldStatus,
        string $newStatus,
        $order,
    ): void {
        if (! RankingConfig::incrementalRankingEnabled()) {
            return;
        }

        unset($oldStatus, $newStatus, $order);
        TopsaleEligibilityService::tryRecordOrder($orderId);
    }

    public static function scheduleMaintenance(): void
    {
        if (! RankingConfig::incrementalRankingEnabled()) {
            return;
        }

        if (! function_exists('wp_next_scheduled') || ! function_exists('wp_schedule_event')) {
            return;
        }

        if (! wp_next_scheduled('ez_ranking_daily_maintenance')) {
            wp_schedule_event(time() + 3600, 'daily', 'ez_ranking_daily_maintenance');
        }

        if (! wp_next_scheduled('ez_ranking_reconcile_weekly')) {
            wp_schedule_event(time() + 7200, 'weekly', 'ez_ranking_reconcile_weekly');
        }
    }

    public static function onWeeklyReconcile(): void
    {
        if (! RankingConfig::incrementalRankingEnabled()) {
            return;
        }

        $result = RankingBackfillService::reconcileSample(20);

        if (function_exists('apply_filters')) {
            $result = apply_filters('ez_ranking_reconcile_report', $result);
        }

        $mismatches = (int) ($result['mismatches'] ?? 0);
        if ($mismatches < 1) {
            return;
        }

        $samples = is_array($result['samples'] ?? null) ? $result['samples'] : [];
        $summary = [];
        foreach (array_slice($samples, 0, 5) as $sample) {
            if (! is_array($sample)) {
                continue;
            }
            $summary[] = sprintf(
                'product_id=%d max_diff=%s',
                (int) ($sample['product_id'] ?? 0),
                (string) ($sample['max_diff'] ?? '?')
            );
        }

        error_log(sprintf(
            'ez_core ranking reconcile: %d mismatch(es) in sample of 20. Examples: %s',
            $mismatches,
            implode('; ', $summary)
        ));
    }

    public static function onTrashComment(int $commentId): void
    {
        if (! RankingConfig::incrementalRankingEnabled()) {
            return;
        }

        $comment = get_comment($commentId);
        if (! self::isApprovedProductReview($comment)) {
            return;
        }

        self::purgeHottestForReviewComment($commentId);
    }

    /**
     * @param \WP_Comment|int $comment
     */
    public static function onDeleteComment(int $commentId, $comment): void
    {
        if (! RankingConfig::incrementalRankingEnabled()) {
            return;
        }

        if (! $comment instanceof \WP_Comment) {
            $comment = get_comment($commentId);
        }
        if (! self::isApprovedProductReview($comment)) {
            return;
        }

        $productId = (int) $comment->comment_post_ID;

        if (function_exists('ez_product_review_remove_totals_for_comment')) {
            ez_product_review_remove_totals_for_comment($commentId);
        }

        self::purgeHottestForReviewComment($commentId);

        if (function_exists('ez_product_ranking_sync_after_review_change')) {
            ez_product_ranking_sync_after_review_change($productId);

            return;
        }

        self::onActionRecalculate($productId, ['popular', 'hottest']);
    }

    public static function maybeDisableLegacyRankingCrons(): void
    {
        if (! RankingConfig::incrementalRankingEnabled() || ! function_exists('wp_clear_scheduled_hook')) {
            return;
        }

        foreach (
            [
                'ez_queryable_set_hottest_cron',
                'ez_queryable_set_popular_cron',
                'ez_queryable_set_topsale_cron',
                'update_comments_stars_cron',
            ] as $hook
        ) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Remove hottest_products event row before rollup/recalc (trash or permanent delete).
     */
    private static function purgeHottestForReviewComment(int $commentId): void
    {
        if ($commentId < 1) {
            return;
        }

        HottestEventRepository::deleteByCommentId($commentId);
    }

    private static function isApprovedProductReview(?\WP_Comment $comment): bool
    {
        if (! $comment || $comment->comment_type !== 'review') {
            return false;
        }

        return (string) $comment->comment_approved === '1' && (int) $comment->comment_post_ID > 0;
    }
}
