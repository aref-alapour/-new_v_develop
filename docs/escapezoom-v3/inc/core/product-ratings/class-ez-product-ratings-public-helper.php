<?php
/**
 * Read paths: rollups first, fallback legacy clone_* postmeta.
 *
 * @package escapezoom-v3
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Per-axis average on scale ~0–5 keyed by legacy meta keys (1094…1098).
 *
 * @return array<int,float>
 */
function ez_product_rating_rollups_axis_averages_by_legacy_keys( int $product_id ): array {
	$keys = ez_get_product_review_rate_keys();
	$out  = array_fill_keys( $keys, 0.0 );

	if ( $product_id < 1 || ! Ez_Product_Ratings_Schema::is_installed() ) {
		return $out;
	}

	if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader::class ) ) {
		return \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader::axisAveragesByLegacyKeys( $product_id );
	}

	return $out;
}

/**
 * Fallback from legacy clone_product_rates meta (same formula as before rollups).
 *
 * @return array<int,float>
 */
function ez_product_rating_legacy_clone_axis_averages( int $product_id ): array {
	if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader::class ) ) {
		return \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader::legacyCloneAxisAverages( $product_id );
	}

	$keys           = ez_get_product_review_rate_keys();
	$clone          = get_post_meta( $product_id, 'clone_product_rates', true );
	$weight_sum     = (int) get_post_meta( $product_id, 'clone_comments_count_new', true );
	$out            = array_fill_keys( $keys, 0.0 );

	if ( ! is_array( $clone ) || $weight_sum < 1 ) {
		return $out;
	}

	foreach ( $keys as $k ) {
		$raw       = (int) ( $clone[ $k ] ?? 0 );
		$out[ $k ] = (float) ( $raw / $weight_sum / 20 );
	}

	return $out;
}

/**
 * Axis averages used on front (rollups if installed & data exists, else clone meta).
 *
 * @return array<int,float>
 */
function ez_product_rating_resolve_axis_averages_for_display( int $product_id ): array {
	if ( class_exists( \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader::class ) ) {
		return \EscapeZoom\Core\Modules\ProductRatings\Services\ProductRatingSummaryReader::resolveAxisAveragesForDisplay( $product_id )['axes'];
	}

	if ( Ez_Product_Ratings_Schema::is_installed() ) {
		$from_roll    = ez_product_rating_rollups_axis_averages_by_legacy_keys( $product_id );
		$has_signal = false;
		foreach ( $from_roll as $v ) {
			if ( $v > 0.0005 ) {
				$has_signal = true;
				break;
			}
		}
		if ( $has_signal ) {
			return $from_roll;
		}
	}

	return ez_product_rating_legacy_clone_axis_averages( $product_id );
}

/**
 * Overall 0–5 for UI: اتاق فرار = mean of five axes; else mean over axes with signal (> eps).
 *
 * @param array<int,float> $axis_by_legacy_key
 */
function ez_product_rating_overall_from_axes( array $axis_by_legacy_key, string $product_type ): float {
	$decor    = (float) ( $axis_by_legacy_key[1094] ?? 0 );
	$moaama   = (float) ( $axis_by_legacy_key[1095] ?? 0 );
	$tazegi   = (float) ( $axis_by_legacy_key[1098] ?? 0 );
	$act      = (float) ( $axis_by_legacy_key[1096] ?? 0 );
	$barkhord = (float) ( $axis_by_legacy_key[1097] ?? 0 );
	$dims     = [ $decor, $moaama, $tazegi, $act, $barkhord ];
	$eps      = 0.0005;

	if ( $product_type === 'اتاق فرار' ) {
		$raw = array_sum( $dims ) / 5.0;
	} else {
		$nonzero = 0;
		foreach ( $dims as $d ) {
			if ( $d > $eps ) {
				++$nonzero;
			}
		}
		$divisor = max( 1, $nonzero );
		$raw     = array_sum( $dims ) / $divisor;
	}

	return max( 0.0, min( 5.0, $raw ) );
}

/**
 * @return float|string|int Same display contract as legacy (5 as int sometimes)
 */
function ez_product_rating_format_overall_display( float $capped_0_to_5 ) {
	if ( abs( $capped_0_to_5 - 5.0 ) < 1e-9 ) {
		return 5;
	}
	$rounded = round( $capped_0_to_5, 2 );
	if ( abs( $rounded - 5.0 ) < 1e-9 ) {
		$factor = (float) pow( 10, 2 );

		return floor( $capped_0_to_5 * $factor ) / $factor;
	}

	return number_format( $rounded, 2, '.', '' );
}
