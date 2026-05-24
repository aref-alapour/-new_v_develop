<?php
/**
 * Criterion slug ↔ id via ez_core Eloquent lookup (request-local cache).
 *
 * @package escapezoom-v3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Ez_Product_Rating_Criteria {

	public static function reset_cache(): void {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::class ) ) {
			\EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::reset();
		}
	}

	/**
	 * @return array<string,int>
	 */
	public static function get_slug_to_id_map(): array {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::slugToIdMap();
		}

		return [];
	}

	public static function id_for_slug( string $slug ): int {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::idForSlug( $slug );
		}

		return 0;
	}

	public static function slug_for_id( int $id ): string {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\Services\RatingCriterionLookup::slugForId( $id );
		}

		return '';
	}
}
