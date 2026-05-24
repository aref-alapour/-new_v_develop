<?php
/**
 * Static eligibility checks for team ops buttons (run: php bin/test-team-ops-eligibility.php).
 */
require __DIR__ . '/../inc/medoo/init.php';
require __DIR__ . '/../inc/ez-booking-checkout-validation.php';
require __DIR__ . '/../inc/ez-markting-team-ops.php';

function row( array $overrides ): array {
	return array_merge(
		array(
			'order_id'           => 1001,
			'order_status'       => 'wc-pending',
			'order_created_at'   => gmdate( 'Y-m-d H:i:s', time() - 1800 ),
			'order_sans_time'    => '18:00',
			'order_sans_date'    => '2026-05-20',
			'order_sans_day'     => 'سه‌شنبه',
			'order_paid'         => 0,
			'order_payment_type' => 'partial',
			'game_name'          => 'اتاق فرار تست',
		),
		$overrides
	);
}

$tests = array(
	array(
		'name'    => 'pending with game -> confirm yes',
		'row'     => row( array() ),
		'confirm' => true,
		'recover_main' => false,
		'recover_bad'  => false,
	),
	array(
		'name'    => 'on-hold -> confirm yes',
		'row'     => row( array( 'order_status' => 'wc-on-hold' ) ),
		'confirm' => true,
		'recover_main' => false,
		'recover_bad'  => false,
	),
	array(
		'name'    => 'paid complete sans+booking -> recover no on main',
		'row'     => row( array( 'order_status' => 'wc-partially-paid', 'order_paid' => 1 ) ),
		'confirm' => false,
		'recover_main' => false,
		'recover_bad'  => false,
		'booked_cache' => array( 1001 => true ),
	),
	array(
		'name'    => 'paid missing booking -> recover yes main',
		'row'     => row( array( 'order_status' => 'wc-partially-paid', 'order_paid' => 1 ) ),
		'confirm' => false,
		'recover_main' => true,
		'recover_bad'  => true,
	),
	array(
		'name'    => 'processing 15m complete -> recover no main, yes bad filter',
		'row'     => row(
			array(
				'order_status'     => 'wc-processing',
				'order_created_at' => gmdate( 'Y-m-d H:i:s', time() - 900 ),
			)
		),
		'confirm' => false,
		'recover_main' => false,
		'recover_bad'  => true,
		'booked_cache' => array( 1001 => true ),
	),
	array(
		'name'    => 'bad: paid sans incomplete -> recover bad',
		'row'     => row(
			array(
				'order_status'    => 'wc-partially-paid',
				'order_sans_date' => null,
				'order_sans_time' => null,
				'order_sans_day'  => null,
			)
		),
		'confirm' => false,
		'recover_main' => true,
		'recover_bad'  => true,
	),
);

$fail = 0;
foreach ( $tests as $t ) {
	if ( isset( $t['booked_cache'] ) ) {
		$GLOBALS['ez_markting_booking_exists_ids'] = $t['booked_cache'];
	} else {
		ez_markting_prefetch_booking_order_ids( array() );
	}

	$c  = ez_markting_row_eligible_confirm_payment( $t['row'] );
	$rm = ez_markting_row_eligible_for_booking_recovery( $t['row'], 'main' );
	$rb = ez_markting_row_eligible_for_booking_recovery( $t['row'], 'problematic' );
	$ok = ( $c === $t['confirm'] ) && ( $rm === $t['recover_main'] ) && ( $rb === $t['recover_bad'] );

	if ( ! $ok ) {
		++$fail;
		echo "FAIL: {$t['name']} (c=" . (int) $c . " rm=" . (int) $rm . " rb=" . (int) $rb . ")\n";
	} else {
		echo "OK: {$t['name']}\n";
	}
}

ez_markting_booking_prefetch_reset();
ez_markting_prefetch_booking_order_ids( array( 1001 ) );
$prefetch_ok = ! isset( $GLOBALS['ez_markting_booking_exists_ids'] )
	|| $GLOBALS['ez_markting_booking_exists_ids'] === null
	|| is_array( $GLOBALS['ez_markting_booking_exists_ids'] );
if ( ! $prefetch_ok ) {
	++$fail;
	echo "FAIL: prefetch should set array or reset on error\n";
} else {
	echo "OK: prefetch cache shape\n";
}

exit( $fail > 0 ? 1 : 0 );
