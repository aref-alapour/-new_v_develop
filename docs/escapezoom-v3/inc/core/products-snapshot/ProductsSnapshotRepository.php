<?php
/**
 * Theme adapter for products_snapshot carousel reads — query logic lives in ez_core ProductsSnapshotReadService.
 *
 * Schema of `{prefix}products_snapshot` — see ez_core/database/sql/ez_bootstrap_custom_tables.sql.
 *
 * For server-rendered carousels; replaces legacy web-service round-trip.
 *
 * @package escapezoom-v3
 */

defined( 'ABSPATH' ) || exit;

/**
 * Public surface preserved for callers; delegates to EscapeZoom\Product snapshot service.
 */
class EZ_Products_Snapshot_Repository {

	/**
	 * @deprecated Use mirrored constant from ez_core; kept for callers referencing this class constant.
	 */
	public const CACHE_GROUP = 'ez_products_snapshot';

	public static function cache_ttl(): int {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::class ) ) {
			return \EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::cacheTtlSeconds();
		}

		$ttl = (int) apply_filters( 'ez_products_snapshot_cache_ttl', 300 );

		return $ttl > 0 ? $ttl : 300;
	}

	/**
	 * @param array<string,mixed> $args
	 * @return array<int,array<string,mixed>>
	 */
	public static function query( array $args ): array {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::class ) ) {
			return \EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::query( $args );
		}

		return array();
	}

	/**
	 * @param array<int,int> $ids
	 * @return array<int,array<string,mixed>>
	 */
	public static function products_by_ids( array $ids ): array {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::class ) ) {
			return \EscapeZoom\Core\Modules\ProductsSnapshot\ProductsSnapshotReadService::productsByIds( $ids );
		}

		return array();
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function products_by_brand( int $brand_term_id, int $exclude_product_id, int $limit = 10 ): array {
		if ( $brand_term_id <= 0 || $limit <= 0 ) {
			return array();
		}

		return self::query(
			array(
				'limit'     => $limit,
				'sort_type' => 'hottest',
				'params'    => array(
					'brand_id'         => $brand_term_id,
					'exclude_products' => array( $exclude_product_id ),
				),
			)
		);
	}
}

/**
 * Procedural helper preferred by carousel call-sites; thin wrapper over the repository.
 *
 * @param array<string,mixed> $args
 * @return array<int,array<string,mixed>>
 */
function ez_products_snapshot_query( array $args ): array {
	return EZ_Products_Snapshot_Repository::query( $args );
}
