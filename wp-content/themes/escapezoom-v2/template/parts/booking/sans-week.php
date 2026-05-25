<?php
/**
 * Week grid (gateway booking.sans_week) — same markup as reserve_get_table.
 *
 * @var int   $product_id
 * @var int   $day_start_time
 * @var array $days
 */
defined( 'ABSPATH' ) || exit;

$time    = (int) ( $day_start_time ?? 0 );
$product = (int) ( $product_id ?? 0 );
$days    = isset( $days ) && is_array( $days ) ? $days : array();

preg_match_all( '/\d+/', (string) get_field( 'room_tedad', $product ), $numbers );
$numbers = $numbers[0] ?? array( '2' );

foreach ( $days as $didx => $day ) {
	if ( is_array( $day ) ) {
		$converted = array();
		foreach ( $day as $row ) {
			$converted[] = is_object( $row ) ? $row : (object) $row;
		}
		$days[ $didx ] = $converted;
	}
}

include __DIR__ . '/sans-week-table.inc.php';
