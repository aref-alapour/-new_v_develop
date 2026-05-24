<?php

namespace EscapeZoom\Core\Modules\ProductRatings\Services;

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Modules\ProductRatings\Models\CommentRatingRow;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsLegacyMap;
use EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema;
use Illuminate\Database\Capsule\Manager as Capsule;

final class ProductRatingRollupService
{
    private static ?self $instance = null;

    public static function instance(): self
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct() {}

    /**
     * Replace dimension rows + weight row from current comment_meta (dual-write era).
     */
    public function syncStorageFromCommentMeta(int $comment_id): bool
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return false;
        }

        $c = get_comment($comment_id);
        if (! $c instanceof \WP_Comment || ! $this->commentAppliesToTotals($c)) {
            return false;
        }

        $rating_meta = get_comment_meta($comment_id, 'comment_rating', true);
        if (! is_array($rating_meta)) {
            $rating_meta = [];
        }

        $this->replaceDimensionRowsFromRatingMeta($comment_id, $rating_meta);

        $stored_level = (int) get_comment_meta($comment_id, 'user_level', true);
        $this->replaceWeightRowForComment($comment_id, $stored_level);

        return true;
    }

    public function syncDimensionRowsOnlyFromCommentMeta(int $comment_id): bool
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return false;
        }

        $c = get_comment($comment_id);
        if (! $c instanceof \WP_Comment || ! $this->commentAppliesToTotals($c)) {
            return false;
        }

        $rating_meta = get_comment_meta($comment_id, 'comment_rating', true);
        if (! is_array($rating_meta)) {
            $rating_meta = [];
        }

        $this->replaceDimensionRowsFromRatingMeta($comment_id, $rating_meta);

        return true;
    }

    public function syncWeightOnlyFromCommentMeta(int $comment_id): bool
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return false;
        }

        $c = get_comment($comment_id);
        if (! $c instanceof \WP_Comment || ! $this->commentAppliesToTotals($c)) {
            return false;
        }

        $stored_level = (int) get_comment_meta($comment_id, 'user_level', true);
        $this->replaceWeightRowForComment($comment_id, $stored_level);

        return true;
    }

    /**
     * @param array<int|string,int|float|string> $rating_meta
     */
    private function replaceDimensionRowsFromRatingMeta(int $comment_id, array $rating_meta): void
    {
        CommentRatingRow::query()->where('comment_id', $comment_id)->delete();

        $slug_map = ProductRatingsLegacyMap::termKeyToSlugMap();
        foreach ($this->legacyRateKeys() as $legacy_key) {
            $raw = (int) ($rating_meta[ $legacy_key ] ?? $rating_meta[ (string) $legacy_key ] ?? 0);
            if ($raw <= 0) {
                continue;
            }
            $slug = $slug_map[ $legacy_key ] ?? '';
            if ($slug === '') {
                continue;
            }
            $crit_id = RatingCriterionLookup::idForSlug($slug);
            if ($crit_id < 1) {
                continue;
            }
            CommentRatingRow::query()->insert([
                'comment_id' => $comment_id,
                'criterion_id' => $crit_id,
                'score_raw' => $raw,
            ]);
        }
    }

    private function replaceWeightRowForComment(int $comment_id, int $stored_level): void
    {
        $weight = $this->storedLevelToRatingPower($stored_level);

        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $weights_table = ProductRatingsSchema::weightsTable();
        $conn->statement(
            "REPLACE INTO `{$weights_table}` (`comment_id`, `weight`, `stored_level`) VALUES (?, ?, ?)",
            [ $comment_id, $weight, $stored_level ]
        );
    }

    /**
     * @return array<int,int>
     */
    public function getScoresByCriterionId(int $comment_id): array
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return [];
        }

        $rows = CommentRatingRow::query()
            ->where('comment_id', $comment_id)
            ->get([ 'criterion_id', 'score_raw' ]);

        $out = [];
        foreach ($rows as $row) {
            $out[ (int) $row->criterion_id ] = (int) $row->score_raw;
        }

        return $out;
    }

    public function getWeightForComment(int $comment_id): int
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return 1;
        }

        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);
        $table = ProductRatingsSchema::weightsTable();
        $w = (int) $conn->table($table)->where('comment_id', $comment_id)->value('weight');

        return $w > 0 ? $w : 1;
    }

    public function applyTotalsIfCounted(int $comment_id): void
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return;
        }

        $c = get_comment($comment_id);
        if (! $c instanceof \WP_Comment || ! $this->commentAppliesToTotals($c)) {
            return;
        }
        if ((string) $c->comment_approved !== '1') {
            return;
        }

        $product_id = (int) $c->comment_post_ID;
        $scores = $this->getScoresByCriterionId($comment_id);
        if ($scores === []) {
            return;
        }

        $weight = $this->getWeightForComment($comment_id);

        $this->runTransaction(function () use ($product_id, $scores, $weight): void {
            foreach ($scores as $crit_id => $score_raw) {
                $this->rollupDelta($product_id, $crit_id, $score_raw * $weight, $weight);
            }
        });
    }

    public function removeTotalsIfCounted(int $comment_id): void
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return;
        }

        $c = get_comment($comment_id);
        if (! $c instanceof \WP_Comment || ! $this->commentAppliesToTotals($c)) {
            return;
        }

        $product_id = (int) $c->comment_post_ID;
        $scores = $this->getScoresByCriterionId($comment_id);
        if ($scores === []) {
            return;
        }

        $weight = $this->getWeightForComment($comment_id);

        $this->runTransaction(function () use ($product_id, $scores, $weight): void {
            foreach ($scores as $crit_id => $score_raw) {
                $this->rollupDelta($product_id, $crit_id, - ($score_raw * $weight), - $weight);
            }
        });
    }

    /**
     * @param array<int,int> $old_scores
     * @param array<int,int> $new_scores
     */
    public function applyDeltaApprovedEdit(int $product_id, array $old_scores, int $old_weight, array $new_scores, int $new_weight): void
    {
        if (! ProductRatingsSchema::tablesVerified() || $product_id < 1) {
            return;
        }

        $all_crit_ids = array_unique(array_merge(array_keys($old_scores), array_keys($new_scores)));
        sort($all_crit_ids);

        $this->runTransaction(function () use ($product_id, $all_crit_ids, $old_scores, $new_scores, $old_weight, $new_weight): void {
            foreach ($all_crit_ids as $crit_id) {
                $o = (int) ($old_scores[ $crit_id ] ?? 0);
                $n = (int) ($new_scores[ $crit_id ] ?? 0);
                if ($o <= 0 && $n <= 0) {
                    continue;
                }
                $d_weighted = ($n * $new_weight) - ($o * $old_weight);
                $old_eff_w = $o > 0 ? $old_weight : 0;
                $new_eff_w = $n > 0 ? $new_weight : 0;
                $d_weight = $new_eff_w - $old_eff_w;
                $this->rollupDelta($product_id, (int) $crit_id, $d_weighted, $d_weight);
            }
        });
    }

    public function rebuildAllRollups(): void
    {
        if (! ProductRatingsSchema::tablesVerified()) {
            return;
        }

        global $wpdb;

        $roll = ProductRatingsSchema::rollupsTable();
        $rows = ProductRatingsSchema::rowsTable();
        $wts = ProductRatingsSchema::weightsTable();

        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);

        $conn->statement("TRUNCATE TABLE `{$roll}`");

        $sql = "
			INSERT INTO `{$roll}` (product_id, criterion_id, sum_weighted_score, sum_weight)
			SELECT c.comment_post_ID AS product_id,
			       r.criterion_id,
			       SUM(r.score_raw * w.weight) AS sumw,
			       SUM(w.weight) AS sumwt
			FROM `{$rows}` r
			INNER JOIN `{$wpdb->comments}` c ON c.comment_ID = r.comment_id
			INNER JOIN `{$wpdb->posts}` p ON p.ID = c.comment_post_ID AND p.post_type = 'product'
			INNER JOIN `{$wts}` w ON w.comment_id = r.comment_id
			WHERE c.comment_approved = '1'
			  AND c.comment_type = 'review'
			GROUP BY c.comment_post_ID, r.criterion_id
		";

        $conn->statement($sql);
    }

    /**
     * @param callable():void $fn
     */
    private function runTransaction(callable $fn): void
    {
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);

        try {
            $conn->transaction($fn);
        } catch (\Throwable $e) {
            if (defined('WP_DEBUG') && WP_DEBUG) {
                // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
                error_log('ProductRatingRollupService transaction failed: ' . $e->getMessage());
            }
        }
    }

    private function rollupDelta(int $product_id, int $criterion_id, int $d_weighted_delta, int $d_weight_delta): void
    {
        if ($product_id < 1 || $criterion_id < 1) {
            return;
        }

        $table = ProductRatingsSchema::rollupsTable();
        $conn = Capsule::connection(CapsuleBoot::CONNECTION_WP);

        $conn->statement(
            "INSERT INTO `{$table}` (product_id, criterion_id, sum_weighted_score, sum_weight)
				VALUES (?, ?, ?, ?)
				ON DUPLICATE KEY UPDATE
					sum_weighted_score = sum_weighted_score + ?,
					sum_weight = sum_weight + ?",
            [
                $product_id,
                $criterion_id,
                $d_weighted_delta,
                $d_weight_delta,
                $d_weighted_delta,
                $d_weight_delta,
            ]
        );
    }

    private function commentAppliesToTotals(\WP_Comment $comment): bool
    {
        return function_exists('ez_product_review_comment_applies_to_totals')
            && ez_product_review_comment_applies_to_totals($comment);
    }

    /**
     * @return int[]
     */
    private function legacyRateKeys(): array
    {
        return function_exists('ez_get_product_review_rate_keys')
            ? ez_get_product_review_rate_keys()
            : [ 1098, 1097, 1096, 1095, 1094 ];
    }

    private function storedLevelToRatingPower(int $stored_level): int
    {
        return function_exists('ez_comment_stored_user_level_to_rating_power')
            ? ez_comment_stored_user_level_to_rating_power($stored_level)
            : 1;
    }
}
