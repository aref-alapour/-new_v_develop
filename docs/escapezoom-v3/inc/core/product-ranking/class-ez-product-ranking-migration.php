<?php
/**
 * Chunked ranking backfill for page-aref-test-3 wizard.
 *
 * @package escapezoom-v3
 */

use EscapeZoom\Core\Modules\ProductRanking\ProductPenaltySchema;
use EscapeZoom\Core\Modules\ProductRanking\ProductRankingSchema;
use EscapeZoom\Core\Modules\ProductRanking\Repositories\ProductPenaltyRepository;
use EscapeZoom\Core\Modules\ProductRanking\Services\ProductRatingScoreWriter;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingBackfillService;
use EscapeZoom\Core\Modules\ProductRanking\Services\RankingMaintenanceService;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Ez_Product_Ranking_Migration {

	/**
	 * @return array{tables: array<string, bool>, penalty_row_count: int, penalties_empty: bool}
	 */
	public static function verify_tables(): array {
		$penalty_count = ProductPenaltyRepository::countRows();

		return [
			'tables' => [
				'product_rank_scores'  => ProductRankingSchema::tablesVerified(),
				'ez_product_penalties' => ProductPenaltySchema::tablesVerified(),
			],
			'penalty_row_count' => $penalty_count,
			'penalties_empty'   => ProductPenaltySchema::tablesVerified() && $penalty_count === 0,
		];
	}

	/**
	 * @return array{inserted: int, updated: int}
	 */
	public static function seed_penalties(): array {
		return ProductPenaltyRepository::seedFromLegacyDefaults();
	}

	/**
	 * @return array{processed: int, next_after: int, done: bool}
	 */
	public static function backfill_scores_chunk( int $after_product_id, int $limit ): array {
		return RankingBackfillService::recalculateProductsChunk(
			$after_product_id,
			$limit,
			[ 'popular', 'hottest', 'topsale' ]
		);
	}

	/**
	 * @return array{processed: int, ok: int, next_after: int, done: bool}
	 */
	public static function backfill_rating_meta_chunk( int $after_product_id, int $limit ): array {
		$limit  = max( 1, min( 500, $limit ) );
		$ids    = RankingBackfillService::fetchActiveProductIdsAfter( $after_product_id, $limit );
		$last   = $after_product_id;
		$synced = 0;

		foreach ( $ids as $product_id ) {
			ProductRatingScoreWriter::syncProduct( $product_id );
			++$synced;
			$last = max( $last, $product_id );
		}

		return [
			'processed'  => count( $ids ),
			'ok'         => $synced,
			'next_after' => $last,
			'done'       => count( $ids ) < $limit,
		];
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function rebuild_held_orders(): array {
		return RankingBackfillService::rebuildHeldOrdersList();
	}

	public static function purge_hottest(): void {
		RankingMaintenanceService::runDailyMaintenance();
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function spot_check_ranking( int $n ): array {
		return array_merge(
			[ 'success' => true ],
			RankingBackfillService::reconcileSample( $n )
		);
	}
}
