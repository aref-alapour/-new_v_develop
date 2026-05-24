<?php
/**
 * DB tables for relational product comment ratings + rollups (DDL outside theme).
 * Table resolution delegates to ez_core where available.
 *
 * @package escapezoom-v3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Ez_Product_Ratings_Schema {

	public const VERSION        = '1';
	public const VERSION_OPTION = 'ez_product_ratings_db_version';

	/**
	 * Legacy comment_rating meta keys → criterion slug.
	 *
	 * @return array<int,string>
	 */
	public static function legacy_term_key_to_slug_map(): array {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsLegacyMap::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsLegacyMap::termKeyToSlugMap();
		}

		return [
			1094 => 'atmosphere',
			1095 => 'puzzle',
			1098 => 'creativity',
			1096 => 'acting',
			1097 => 'staff',
		];
	}

	public static function criteria_table(): string {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::criteriaTable();
		}
		global $wpdb;

		return $wpdb->prefix . 'ez_rating_criteria';
	}

	public static function rows_table(): string {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::rowsTable();
		}
		global $wpdb;

		return $wpdb->prefix . 'ez_comment_rating_rows';
	}

	public static function weights_table(): string {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::weightsTable();
		}
		global $wpdb;

		return $wpdb->prefix . 'ez_comment_rating_weights';
	}

	public static function rollups_table(): string {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::rollupsTable();
		}
		global $wpdb;

		return $wpdb->prefix . 'ez_product_rating_rollups';
	}

	/**
	 * Whether expected rating tables exist (import ez_bootstrap_custom_tables.sql first).
	 */
	public static function tables_exist(): bool {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::class ) ) {
			return \EscapeZoom\Core\Modules\ProductRatings\ProductRatingsSchema::tablesVerified();
		}

		global $wpdb;

		foreach ( [ 'ez_rating_criteria', 'ez_comment_rating_rows', 'ez_comment_rating_weights', 'ez_product_rating_rollups' ] as $suffix ) {
			$full  = $wpdb->prefix . $suffix;
			$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $full ) );
			if ( $found !== $full ) {
				return false;
			}
		}

		return true;
	}

	public static function is_installed(): bool {
		return self::tables_exist();
	}

	/**
	 * After verifying tables exist: refresh VERSION_OPTION for tooling / QA.
	 */
	public static function sync_version_option_if_ready(): void {
		if ( self::tables_exist() ) {
			update_option( self::VERSION_OPTION, self::VERSION );
		}
	}

	/**
	 * Seed criteria rows only (no DDL).
	 */
	public static function seed_criteria(): void {
		if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\RatingCriteriaSeeder::class ) ) {
			\EscapeZoom\Core\Modules\ProductRatings\RatingCriteriaSeeder::ensure();
		}

		Ez_Product_Rating_Criteria::reset_cache();
	}

	/**
	 * @deprecated Tables are created via ez_core/database/sql/ez_bootstrap_custom_tables.sql — use seed_criteria() after import.
	 */
	public static function install_or_upgrade(): void {
		self::seed_criteria();
		self::sync_version_option_if_ready();
	}
}
