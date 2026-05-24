<?php
/**
 * Thin adapter — rollup logic lives in ez_core ProductRatingRollupService.
 *
 * @package escapezoom-v3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Ez_Product_Rating_Rollup_Service {

	private static ?self $instance = null;

	public static function instance(): Ez_Product_Rating_Rollup_Service {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {}

	private static function core(): \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingRollupService {
		return \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingRollupService::instance();
	}

	public function sync_storage_from_comment_meta( int $comment_id ): bool {
		return self::core()->syncStorageFromCommentMeta( $comment_id );
	}

	public function sync_dimension_rows_only_from_comment_meta( int $comment_id ): bool {
		return self::core()->syncDimensionRowsOnlyFromCommentMeta( $comment_id );
	}

	public function sync_weight_only_from_comment_meta( int $comment_id ): bool {
		return self::core()->syncWeightOnlyFromCommentMeta( $comment_id );
	}

	/**
	 * @return array<int,int>
	 */
	public function get_scores_by_criterion_id( int $comment_id ): array {
		return self::core()->getScoresByCriterionId( $comment_id );
	}

	public function get_weight_for_comment( int $comment_id ): int {
		return self::core()->getWeightForComment( $comment_id );
	}

	public function apply_totals_if_counted( int $comment_id ): void {
		self::core()->applyTotalsIfCounted( $comment_id );
	}

	public function remove_totals_if_counted( int $comment_id ): void {
		self::core()->removeTotalsIfCounted( $comment_id );
	}

	/**
	 * @param array<int,int> $old_scores
	 * @param array<int,int> $new_scores
	 */
	public function apply_delta_approved_edit( int $product_id, array $old_scores, int $old_weight, array $new_scores, int $new_weight ): void {
		self::core()->applyDeltaApprovedEdit( $product_id, $old_scores, $old_weight, $new_scores, $new_weight );
	}

	public function rebuild_all_rollups(): void {
		self::core()->rebuildAllRollups();
	}
}
