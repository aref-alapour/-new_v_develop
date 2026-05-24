<?php
/**
 * CLI: sanity-check booking + markting for one WooCommerce order ID (post-paid flow).
 *
 * Usage (from WordPress root):
 *   php wp-content/themes/escapezoom-v2/bin/verify-booking-chain.php 12345
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$order_id = isset( $argv[1] ) ? (int) $argv[1] : 0;
if ( $order_id <= 0 ) {
	fwrite( STDERR, "Usage: php verify-booking-chain.php <order_id>\n" );
	exit( 1 );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Cannot find wp-load.php at {$wp_load}\n" );
	exit( 1 );
}

require_once $wp_load;

if ( ! function_exists( 'wc_get_order' ) || ! function_exists( 'medoo' ) || ! function_exists( 'medoo_queries' ) ) {
	fwrite( STDERR, "WooCommerce or medoo helpers not loaded.\n" );
	exit( 1 );
}

$order = wc_get_order( $order_id );
if ( ! $order ) {
	fwrite( STDOUT, "No WC order #{$order_id}\n" );
	exit( 2 );
}

fwrite(
	STDOUT,
	sprintf(
		"Order #%d status=%s is_paid=%s\n",
		$order_id,
		$order->get_status(),
		$order->is_paid() ? 'yes' : 'no'
	)
);

$mq = medoo_queries();
if ( ! $mq || ! method_exists( $mq, 'get' ) ) {
	fwrite( STDERR, "medoo_queries() unavailable.\n" );
	exit( 1 );
}

$booking = $mq->get( 'wp_zb_booking_history', '*', array( 'wc_order_id' => $order_id ) );
if ( $booking && is_array( $booking ) ) {
	fwrite(
		STDOUT,
		sprintf(
			"booking: status=%s room_id=%s booking_time=%s quantity=%s\n",
			isset( $booking['status'] ) ? (string) $booking['status'] : '-',
			isset( $booking['room_id'] ) ? (string) $booking['room_id'] : '-',
			isset( $booking['booking_time'] ) ? (string) $booking['booking_time'] : '-',
			isset( $booking['quantity'] ) ? (string) $booking['quantity'] : '-'
		)
	);
} else {
	fwrite( STDOUT, "booking: MISSING row for wc_order_id={$order_id}\n" );
	if ( function_exists( 'ez_maybe_sync_booking_after_payment_complete' ) && $order->is_paid() ) {
		fwrite( STDOUT, "(If AS failed, payment_complete hook ez_maybe_sync_booking_after_payment_complete should insert when booking absent.)\n" );
	}
}

$m = medoo();
if ( ! $m || ! method_exists( $m, 'get' ) ) {
	fwrite( STDERR, "medoo() unavailable.\n" );
	exit( 1 );
}

$mark = $m->get(
	'wp_markting',
	array(
		'order_id',
		'game_id',
		'order_status',
		'order_sans_date',
		'order_sans_time',
		'order_sans_day',
	),
	array( 'order_id' => $order_id )
);

if ( $mark && is_array( $mark ) ) {
	fwrite(
		STDOUT,
		sprintf(
			"markting: order_status=%s game_id=%s sans_date=%s sans_time=%s sans_day=%s\n",
			isset( $mark['order_status'] ) ? (string) $mark['order_status'] : '-',
			isset( $mark['game_id'] ) ? (string) $mark['game_id'] : '-',
			isset( $mark['order_sans_date'] ) ? (string) $mark['order_sans_date'] : '-',
			isset( $mark['order_sans_time'] ) ? (string) $mark['order_sans_time'] : '-',
			isset( $mark['order_sans_day'] ) ? (string) $mark['order_sans_day'] : '-'
		)
	);
} else {
	fwrite( STDOUT, "markting: MISSING row for order_id={$order_id}\n" );
}

exit( 0 );
