<?php
/**
 * Internal reservation dispatch (no HTTP loopback from WordPress).
 */

declare(strict_types=1);

require_once __DIR__ . '/reservation-bootstrap.php';

/**
 * Run reservation type handler and return captured output (echo buffer).
 *
 * @param object $data Decoded request with ->type and ->data.
 */
function ez_reservation_dispatch( object $data ): string {
	ez_reservation_bootstrap_once();
	ez_reservation_assert_allowed_host();

	global $conn, $home_url;
	$home_url = ez_reservation_home_url();

	if ( ! ( $conn instanceof mysqli ) ) {
		$type = isset( $data->type ) ? (string) $data->type : '';
		error_log( '[EZ Booking] External DB unavailable for type=' . $type );
		if ( 'get_sanses' === $type ) {
			return function_exists( 'wp_json_encode' ) ? wp_json_encode( array() ) : '[]';
		}
		$err = array( 'error' => 'db_unavailable' );
		return function_exists( 'wp_json_encode' ) ? wp_json_encode( $err ) : json_encode( $err );
	}

	ob_start();
	require __DIR__ . '/reservation-handlers.inc.php';
	$out = ob_get_clean();

	return is_string( $out ) ? $out : '';
}
