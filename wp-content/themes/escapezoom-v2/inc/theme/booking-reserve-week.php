<?php
/**
 * Shared week-grid helpers for reserve.php and booking.sans_week gateway.
 */
defined( 'ABSPATH' ) || exit;

/**
 * Bucket a flat get_sanses list into 7 day columns (Tehran midnight anchors).
 *
 * @param array<int, object|array<string, mixed>> $flat
 * @return array<int, array<int, object>>
 */
function ez_bucket_sans_into_week( array $flat, int $dayStart ): array {
	$week = array_fill( 0, 7, array() );
	$dayStart = (int) $dayStart;

	foreach ( $flat as $row ) {
		$obj = is_object( $row ) ? $row : (object) ( is_array( $row ) ? $row : array() );
		$ts  = isset( $obj->time ) ? (int) $obj->time : 0;
		if ( $ts <= 0 ) {
			continue;
		}
		$idx = (int) floor( ( $ts - $dayStart ) / 86400 );
		if ( $idx >= 0 && $idx < 7 ) {
			$week[ $idx ][] = $obj;
		}
	}

	return $week;
}

/**
 * Normalize get_sanses(days=7) payload for reserve week templates.
 *
 * @param mixed $days
 * @return array<int, array<int, object>>
 */
function ez_normalize_reserve_week_days( $days, int $dayStart ): array {
	if ( ! is_array( $days ) || array() === $days ) {
		return array_fill( 0, 7, array() );
	}

	$first = $days[0] ?? null;
	if ( is_array( $first ) || is_object( $first ) ) {
		$firstObj = is_object( $first ) ? $first : (object) $first;
		if ( isset( $firstObj->time ) ) {
			return ez_bucket_sans_into_week( $days, $dayStart );
		}
	}

	$out = array();
	for ( $i = 0; $i < 7; $i++ ) {
		$bucket = $days[ $i ] ?? array();
		if ( ! is_array( $bucket ) ) {
			$out[ $i ] = array();
			continue;
		}
		$converted = array();
		foreach ( $bucket as $row ) {
			$converted[] = is_object( $row ) ? $row : (object) ( is_array( $row ) ? $row : array() );
		}
		$out[ $i ] = $converted;
	}

	return $out;
}

/**
 * Render reserve week table HTML (tabs + sans grid).
 */
function ez_render_reserve_week_table( int $productId, int $dayStart ): string {
	$productId = (int) $productId;
	$dayStart  = (int) $dayStart;
	if ( $productId <= 0 || $dayStart <= 0 ) {
		return '';
	}

	$days = array();
	$raw  = '';

	if ( class_exists( '\EscapeZoom\Core\Modules\Booking\BookingAvailabilityService' ) ) {
		$days = \EscapeZoom\Core\Modules\Booking\BookingAvailabilityService::getSanses( $productId, $dayStart, 7 );
	} elseif ( function_exists( 'ez_reservation' ) ) {
		$raw = (string) ez_reservation(
			array(
				'type' => 'get_sanses',
				'data' => array(
					'day_start_time' => $dayStart,
					'product_id'     => $productId,
					'days'           => 7,
				),
			)
		);
	} elseif ( class_exists( '\EscapeZoom\Core\Modules\Booking\BookingDispatchService' ) ) {
		$raw = \EscapeZoom\Core\Modules\Booking\BookingDispatchService::dispatchType(
			'get_sanses',
			array(
				'day_start_time' => $dayStart,
				'product_id'     => $productId,
				'days'           => 7,
			)
		);
	}

	if ( '' !== $raw ) {
		$decoded = json_decode( $raw, true );
		if ( is_array( $decoded ) ) {
			$days = $decoded;
		}
	}

	if ( ! is_array( $days ) ) {
		$days = array();
	}

	$days    = ez_normalize_reserve_week_days( $days, $dayStart );
	$time    = $dayStart;
	$product = $productId;

	preg_match_all( '/\d+/', (string) get_field( 'room_tedad', $product ), $numbers );
	$numbers = $numbers[0] ?? array( '2' );

	ob_start();
	include get_template_directory() . '/app/ajax/callbacks/reserve-week-table-view.php';

	return (string) ob_get_clean();
}
