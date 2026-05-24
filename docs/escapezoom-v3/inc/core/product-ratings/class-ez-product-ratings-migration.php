<?php
/**
 * Chunked migration helpers for admin wizard page (Capsule + core table names from ez_core).
 *
 * @package escapezoom-v3
 */

use EscapeZoom\Core\Database\CapsuleBoot;
use EscapeZoom\Core\Database\WordPressCoreTables;
use Illuminate\Database\Capsule\Manager as Capsule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Ez_Product_Ratings_Migration {

	private static function conn(): \Illuminate\Database\Connection {
		return Capsule::connection( CapsuleBoot::CONNECTION_WP );
	}

	/**
	 * @return int[]
	 */
	public static function fetch_review_comment_ids_with_rating_meta_after( int $after_id, int $limit ): array {
		if ( $limit < 1 ) {
			return [];
		}

		$c  = WordPressCoreTables::comments();
		$cm = WordPressCoreTables::commentmeta();

		$sql = "SELECT DISTINCT c.comment_ID FROM `{$c}` c
			INNER JOIN `{$cm}` cm ON cm.comment_id = c.comment_ID AND cm.meta_key = ?
			WHERE c.comment_ID > ? AND c.comment_type = ?
			AND c.comment_approved NOT IN ('spam','trash')
			ORDER BY c.comment_ID ASC LIMIT ?";

		$rows = self::conn()->select( $sql, [ 'comment_rating', $after_id, 'review', $limit ] );

		return array_map(
			static fn ( object $row ): int => (int) $row->comment_ID,
			$rows
		);
	}

	/**
	 * @return array{processed:int,ok:int,next_after:int,done:bool}
	 */
	public static function backfill_rows_chunk( int $after_id, int $limit ): array {
		$svc = Ez_Product_Rating_Rollup_Service::instance();
		$ids = self::fetch_review_comment_ids_with_rating_meta_after( $after_id, $limit );
		$last = $after_id;
		$ok   = 0;
		foreach ( $ids as $cid ) {
			if ( $svc->sync_dimension_rows_only_from_comment_meta( $cid ) ) {
				++$ok;
			}
			$last = max( $last, $cid );
		}

		return [
			'processed'  => count( $ids ),
			'ok'         => $ok,
			'next_after' => $last,
			'done'       => count( $ids ) < $limit,
		];
	}

	/**
	 * @return array{processed:int,ok:int,next_after:int,done:bool}
	 */
	public static function backfill_weights_chunk( int $after_id, int $limit ): array {
		$svc = Ez_Product_Rating_Rollup_Service::instance();
		$ids = self::fetch_review_comment_ids_with_rating_meta_after( $after_id, $limit );
		$last = $after_id;
		$ok   = 0;
		foreach ( $ids as $cid ) {
			if ( $svc->sync_weight_only_from_comment_meta( $cid ) ) {
				++$ok;
			}
			$last = max( $last, $cid );
		}

		return [
			'processed'  => count( $ids ),
			'ok'         => $ok,
			'next_after' => $last,
			'done'       => count( $ids ) < $limit,
		];
	}

	/**
	 * Comments that have comment_rating meta but no rows in ez_comment_rating_rows.
	 */
	public static function count_comments_missing_rows(): int {
		if ( ! Ez_Product_Ratings_Schema::is_installed() ) {
			return 0;
		}

		$c    = WordPressCoreTables::comments();
		$cm   = WordPressCoreTables::commentmeta();
		$rows = Ez_Product_Ratings_Schema::rows_table();

		$sql = "SELECT COUNT(*) AS agg FROM `{$c}` c
			INNER JOIN `{$cm}` cm ON cm.comment_id = c.comment_ID AND cm.meta_key = 'comment_rating'
			WHERE c.comment_type = 'review'
			AND c.comment_approved NOT IN ('spam','trash')
			AND NOT EXISTS ( SELECT 1 FROM `{$rows}` r WHERE r.comment_id = c.comment_ID LIMIT 1 )";

		$result = self::conn()->selectOne( $sql );

		return (int) ( $result->agg ?? 0 );
	}

	/**
	 * Approved product reviews missing a weight row.
	 */
	public static function count_approved_missing_weights(): int {
		if ( ! Ez_Product_Ratings_Schema::is_installed() ) {
			return 0;
		}

		$c       = WordPressCoreTables::comments();
		$p       = WordPressCoreTables::posts();
		$weights = Ez_Product_Ratings_Schema::weights_table();

		$sql = "SELECT COUNT(*) AS agg FROM `{$c}` c
			INNER JOIN `{$p}` p ON p.ID = c.comment_post_ID AND p.post_type = 'product'
			WHERE c.comment_type = 'review' AND c.comment_approved = '1'
			AND NOT EXISTS ( SELECT 1 FROM `{$weights}` w WHERE w.comment_id = c.comment_ID LIMIT 1 )";

		$result = self::conn()->selectOne( $sql );

		return (int) ( $result->agg ?? 0 );
	}

	/**
	 * Spot-check a few products: max per-axis delta between rollup averages and legacy clone meta.
	 *
	 * @return array<string,mixed>
	 */
	public static function spot_check_products( int $sample_size ): array {
		$sample_size = max( 1, min( 20, $sample_size ) );

		$c = WordPressCoreTables::comments();

		$sql = "SELECT DISTINCT c.comment_post_ID AS pid FROM `{$c}` c
			WHERE c.comment_type = ?
			AND c.comment_approved = ?
			ORDER BY c.comment_post_ID DESC LIMIT ?";

		$rows = self::conn()->select( $sql, [ 'review', '1', $sample_size ] );

		$worst_axis_diff = 0.0;
		$samples         = [];

		foreach ( $rows as $row ) {
			$product_id = (int) $row->pid;
			if ( $product_id < 1 ) {
				continue;
			}

			$from_roll = ez_product_rating_rollups_axis_averages_by_legacy_keys( $product_id );
			$legacy    = ez_product_rating_legacy_clone_axis_averages( $product_id );

			$max_diff = 0.0;
			foreach ( ez_get_product_review_rate_keys() as $k ) {
				$d = abs( (float) ( $from_roll[ $k ] ?? 0 ) - (float) ( $legacy[ $k ] ?? 0 ) );
				if ( $d > $max_diff ) {
					$max_diff = $d;
				}
			}

			$samples[] = [
				'product_id' => $product_id,
				'max_diff'   => round( $max_diff, 4 ),
			];

			if ( $max_diff > $worst_axis_diff ) {
				$worst_axis_diff = $max_diff;
			}
		}

		return [
			'samples'          => $samples,
			'worst_axis_diff'  => round( $worst_axis_diff, 4 ),
			'missing_rows_cnt' => self::count_comments_missing_rows(),
			'missing_wts_cnt'  => self::count_approved_missing_weights(),
		];
	}
}
