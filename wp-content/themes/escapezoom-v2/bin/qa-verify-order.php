<?php
/**
 * QA helper: verify order flow metas, wp_markting, booking, SMS queue dedupe.
 *
 * From WordPress root:
 *   php wp-content/themes/escapezoom-v2/bin/qa-verify-order.php --static
 *   php wp-content/themes/escapezoom-v2/bin/qa-verify-order.php --order=12345
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$order_id = 0;
$static   = in_array( '--static', $argv, true );
foreach ( $argv as $arg ) {
	if ( preg_match( '/^--order=(\d+)$/', $arg, $m ) ) {
		$order_id = (int) $m[1];
	}
}

$theme_root = dirname( __DIR__ );

$failures = 0;
$passes   = 0;

/**
 * @param bool   $ok
 * @param string $label
 */
function ez_qa_line( $ok, $label ) {
	global $failures, $passes;
	if ( $ok ) {
		++$passes;
		fwrite( STDOUT, "[PASS] {$label}\n" );
	} else {
		++$failures;
		fwrite( STDOUT, "[FAIL] {$label}\n" );
	}
}

if ( $static ) {
	$needles = array(
		array( 'inc/checkout-booking-meta.php', 'ez_order_meta_claim_once' ),
		array( 'inc/saeed-codes.php', 'ez_queue_reservation_confirmation_sms_bundle' ),
		array( 'inc/saeed-codes.php', 'ez_run_thankyou_booking_pipeline' ),
		array( 'inc/saeed-codes.php', 'ez_refund_status_sms_queued_at' ),
		array( 'inc/saeed-codes.php', 'ez_comment_post_sans_schedule_done' ),
		array( 'functions.php', 'save_to_markting_table' ),
		array( 'functions.php', 'check_and_update_markting_table' ),
		array( 'functions.php', 'ez_heal_post_booking_order_integrity' ),
		array( 'functions.php', 'ez_ensure_order_total_2_meta' ),
		array( 'inc/wp-markting-persistence.php', 'ez_markting_upsert_from_order' ),
		array( 'inc/ez-thankyou-view-model.php', 'function ez_thankyou_view_model' ),
		array( 'inc/player-wallet-settle.php', 'ez_player_wallet_apply_for_order' ),
		array( 'inc/player-wallet-settle.php', 'ez_order_online_paid_amount' ),
		array( 'inc/ez-zibal-verify.php', 'function ez_zibal_try_verify_now' ),
		array( 'inc/ez-zibal-verify.php', 'function ez_zibal_verify_with_retries' ),
		array( 'inc/ez-zibal-verify.php', 'function ez_zibal_action_based_enabled' ),
		array( 'functions.php', 'inc/ez-zibal-verify.php' ),
		array( 'template/team/functions/cancellation_functions.php', 'ez_cancellation_create_sms_' ),
		array( 'template/team/ajax/callbacks/comments_actions.php', 'ez_comment_approve_sms_done' ),
	);
	foreach ( $needles as $item ) {
		$rel    = $item[0];
		$needle = $item[1];
		$path   = $theme_root . '/' . str_replace( '/', DIRECTORY_SEPARATOR, $rel );
		$body   = is_readable( $path ) ? file_get_contents( $path ) : false;
		ez_qa_line( is_string( $body ) && strpos( $body, $needle ) !== false, "{$needle} in {$rel}" );
	}

	$saeed = is_readable( $theme_root . '/inc/saeed-codes.php' ) ? file_get_contents( $theme_root . '/inc/saeed-codes.php' ) : '';
	if ( is_string( $saeed ) ) {
		ez_qa_line( strpos( $saeed, "'partially-paid', 'completed-paid'" ) !== false, 'pipeline trigger only partially-paid/completed-paid' );
		ez_qa_line( strpos( $saeed, 'ez_conflict_refund_page_sms_done' ) !== false, 'conflict page SMS claim' );
		ez_qa_line( strpos( $saeed, 'ez_zibal_verify_with_retries' ) !== false, 'thankyou/template_redirect Zibal verify' );
		ez_qa_line( strpos( $saeed, 'zibal_unverified_orders_process_cron' ) !== false, 'Zibal unverified cron registered' );
		ez_qa_line( strpos( $saeed, 'function ez_zp_fetch_unverified_authorities' ) !== false, 'ZarinPal ez_zp_fetch_unverified_authorities' );
		ez_qa_line( strpos( $saeed, 'unVerified.json' ) !== false, 'ZarinPal unVerified.json cron API' );
		ez_qa_line( strpos( $saeed, 'ez_zp_process_unverified_for_settings' ) !== false, 'ZarinPal ez_zp_process_unverified_for_settings' );
		ez_qa_line( strpos( $saeed, "ez_zp_process_unverified_for_settings( 'woocommerce_WC_ZPal_settings'" ) !== false, 'ZarinPal main cron delegates to unVerified' );
		ez_qa_line( strpos( $saeed, 'filter:PAID' ) === false, 'ZarinPal cron no GraphQL filter:PAID' );
		ez_qa_line( strpos( $saeed, 'ez_gateway_handle_payment_return_failure' ) !== false, 'ZarinPal/Zibal NOK uses gateway failure handler' );
		ez_qa_line( strpos( $saeed, 'zp_cancelled_paid_recovery' ) !== false, 'ZarinPal cancelled paid recovery stage' );
		$finalize_pos = strpos( $saeed, "ez_booking_pipeline_finalize( \$order_id, 'done' );" );
		$sms_after    = $finalize_pos !== false
			? strpos( $saeed, 'ez_queue_reservation_confirmation_sms_bundle', $finalize_pos )
			: false;
		ez_qa_line( $finalize_pos !== false && $sms_after !== false, 'pipeline finalize before reservation SMS bundle' );
		ez_qa_line( strpos( $saeed, 'CURLOPT_TIMEOUT' ) !== false, 'reservation SMS telegram curl timeout' );
	}

	$zibal = is_readable( $theme_root . '/inc/ez-zibal-verify.php' ) ? file_get_contents( $theme_root . '/inc/ez-zibal-verify.php' ) : '';
	if ( is_string( $zibal ) ) {
		ez_qa_line( strpos( $zibal, 'gateway.zibal.ir/v1/' ) !== false, 'Zibal gateway API base URL' );
		ez_qa_line( strpos( $zibal, 'result === 100 || $result === 201' ) !== false, 'Zibal verify accepts 100 and 201' );
	}

	$thankyou = file_get_contents( $theme_root . '/woocommerce/checkout/thankyou.php' );
	if ( is_string( $thankyou ) ) {
		ez_qa_line( strpos( $thankyou, 'add_to_sms_queue' ) === false, 'thankyou.php has no add_to_sms_queue' );
		ez_qa_line( strpos( $thankyou, 'ez_run_thankyou_booking_pipeline' ) === false, 'thankyou.php does not call pipeline' );
		ez_qa_line( strpos( $thankyou, 'thankyou_render:' ) !== false, 'thankyou.php has thankyou_render label' );
		ez_qa_line( strpos( $thankyou, 'ez_thankyou_view_model' ) !== false, 'thankyou.php uses ez_thankyou_view_model' );
		ez_qa_line( strpos( $thankyou, 'wp_redirect' ) === false, 'thankyou.php has no wp_redirect' );
		ez_qa_line( strpos( $thankyou, 'update_post_meta' ) === false, 'thankyou.php has no update_post_meta' );
		ez_qa_line( strpos( $thankyou, 'seen_tnx_page' ) === false, 'thankyou.php has no seen_tnx_page' );
		ez_qa_line( strpos( $thankyou, 'thankyou_already_processed' ) === false, 'thankyou.php has no thankyou_already_processed' );
	}

	$functions = is_readable( $theme_root . '/functions.php' ) ? file_get_contents( $theme_root . '/functions.php' ) : '';
	if ( is_string( $functions ) ) {
		ez_qa_line( strpos( $functions, 'min( 120, (int) apply_filters( \'ez_wp_markting_wc_reconcile_batch_total\', 80 )' ) !== false, 'reconcile cron budget default 80 cap 120' );
		ez_qa_line( strpos( $functions, 'ceil( $budget_all * 0.6 )' ) !== false, 'reconcile cron quota 60% pending' );
		ez_qa_line( strpos( $functions, 'ez_reconcile_cron_pending_max_age_seconds\', 60 * MINUTE_IN_SECONDS' ) !== false, 'reconcile cron pending max age 60min' );
		ez_qa_line( strpos( $functions, "'order_created_at' => 'ASC'" ) !== false, 'reconcile cron pending ORDER ASC' );
		ez_qa_line( strpos( $functions, 'wc-partially-paid\', \'wc-completed-paid\'' ) !== false, 'reconcile cron query2 includes partial/complete-paid' );
		ez_qa_line( strpos( $functions, 'ez_zp_reconcile_grace_seconds\', 60 * MINUTE_IN_SECONDS' ) !== false, 'ZarinPal reconcile grace 60min' );
		ez_qa_line( strpos( $functions, 'ez_zibal_reconcile_grace_seconds\', 60 * MINUTE_IN_SECONDS' ) !== false, 'Zibal reconcile grace 60min' );
		ez_qa_line( strpos( $functions, 'ez_reconcile_on_payment_complete' ) !== false, 'payment_complete reconcile hook' );
		ez_qa_line( strpos( $functions, 'ez_heal_post_booking_order_integrity_on_payment' ) !== false, 'payment_complete heal hook @6' );
		ez_qa_line(
			strpos( $functions, 'function ez_reconcile_should_skip_paid_in_fast_cron_window' ) !== false
			&& strpos( $functions, "has_status( 'processing' )" ) !== false
			&& strpos( $functions, 'ez_booking_exists_for_order( $order_id )' ) !== false,
			'reconcile skip exception for processing+booking'
		);
		ez_qa_line( strpos( $functions, 'ez_fast_paid_missing_booking_min_age_seconds\', 60 )' ) !== false, 'fast paid cron min age 60s default' );
		ez_qa_line( strpos( $functions, 'ez_reconcile_cron_last_run' ) !== false, 'reconcile cron heartbeat option' );
	}

	$validation = is_readable( $theme_root . '/inc/ez-booking-checkout-validation.php' ) ? file_get_contents( $theme_root . '/inc/ez-booking-checkout-validation.php' ) : '';
	if ( is_string( $validation ) ) {
		ez_qa_line( strpos( $validation, 'ez_sans_checkout_slot_state' ) !== false, 'sans checkout slot state helper' );
		ez_qa_line( strpos( $validation, 'ez_gateway_handle_payment_return_failure' ) !== false, 'gateway return failure handler' );
		ez_qa_line( strpos( $validation, 'ez_markting_team_ops_ineligible_reason' ) !== false, 'team ops ineligible reason helper' );
	}

	ez_qa_line( is_readable( $theme_root . '/bin/ez-cron-health.php' ), 'bin/ez-cron-health.php exists' );
	ez_qa_line( is_readable( $theme_root . '/bin/ez-heal-stuck-processing-orders.php' ), 'bin/ez-heal-stuck-processing-orders.php exists' );

	if ( is_string( $zibal ) ) {
		ez_qa_line( strpos( $zibal, 'ez_zibal_default_grace_seconds\', 60 * MINUTE_IN_SECONDS' ) !== false, 'Zibal default grace 60min' );
		ez_qa_line( strpos( $zibal, "'order'          => 'ASC'" ) !== false, 'Zibal unverified cron ORDER ASC' );
	}

	fwrite( STDOUT, "\nStatic summary: {$passes} pass, {$failures} fail\n" );
	exit( $failures > 0 ? 1 : 0 );
}

if ( $order_id <= 0 ) {
	fwrite( STDERR, "Usage: --static | --order=ORDER_ID\n" );
	exit( 1 );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Cannot find wp-load.php at {$wp_load}\n" );
	exit( 1 );
}

require_once $wp_load;

$order = wc_get_order( $order_id );
if ( ! $order ) {
	fwrite( STDERR, "Order {$order_id} not found.\n" );
	exit( 1 );
}

global $wpdb;

// M1–M2: markting row + sans_time meta.
$has_markting = function_exists( 'ez_markting_row_exists' ) && ez_markting_row_exists( $order_id );
ez_qa_line( $has_markting, "M1 wp_markting row exists for order {$order_id}" );

$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
ez_qa_line( $sans_time > 0, 'M2 postmeta sans_time set' );

if ( $has_markting && function_exists( 'ez_markting_get_row' ) ) {
	$mrow = ez_markting_get_row( $order_id );
	$sans_null_ok = empty( $mrow['order_sans_date'] ) && empty( $mrow['order_sans_time'] );
	$sans_filled  = ! empty( $mrow['order_sans_date'] ) && ! empty( $mrow['order_sans_time'] ) && ! empty( $mrow['order_sans_day'] );
	ez_qa_line( $sans_null_ok || $sans_filled, 'M3/B2 order_sans_* either pre-booking null or filled after booking' );
	if ( $sans_filled ) {
		ez_qa_line(
			in_array( (string) ( $mrow['order_status'] ?? '' ), array( 'wc-partially-paid', 'wc-completed-paid', 'partially-paid', 'completed-paid' ), true )
			|| $order->is_paid(),
			'B2 markting order_status reflects paid flow'
		);
	}
}

// B1: booking.
$booking = function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id );
if ( ! $booking && function_exists( 'medoo_queries' ) ) {
	$mq = medoo_queries();
	if ( $mq && method_exists( $mq, 'has' ) ) {
		$booking = (bool) $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );
	}
}
ez_qa_line( $booking, 'B1 wp_zb_booking_history row for paid success path' );

// B3 metas.
ez_qa_line( (bool) get_post_meta( $order_id, 'booking_pipeline_done_at', true ), 'B3 booking_pipeline_done_at' );
ez_qa_line( (bool) get_post_meta( $order_id, 'ez_reservation_confirm_sms_queued_at', true ), 'B3 ez_reservation_confirm_sms_queued_at' );

if ( $booking && $order->is_paid() ) {
	$pipe_state = (string) get_post_meta( $order_id, 'booking_pipeline_state', true );
	$done_at    = (bool) get_post_meta( $order_id, 'booking_pipeline_done_at', true );
	ez_qa_line( ! $order->has_status( 'processing' ), 'C5 paid+booking: WC not stuck on processing' );
	ez_qa_line( $done_at || $pipe_state === 'done', 'C6 paid+booking: pipeline done_at or state=done' );
	$pm_gateway = in_array( (string) $order->get_payment_method(), array( 'WC_ZPal', 'WC_ZPal_co', 'WC_Zibal' ), true );
	$total_2    = (int) get_post_meta( $order_id, '_order_total_2', true );
	$snap_on    = (int) get_post_meta( $order_id, '_ez_checkout_snapshot_online_payable', true );
	if ( $pm_gateway ) {
		ez_qa_line( $total_2 > 0 || $snap_on > 0, 'C7 gateway order: _order_total_2 or snapshot online payable' );
	}
}

// B4 SMS dedupe: no duplicate pending rows per type+phone+token.
$queue_rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT token, type, phone, COUNT(*) AS c FROM sms_sending_queue WHERE order_id = %s AND sent_time IS NULL AND token IN ('434387','434389') GROUP BY token, type, phone HAVING c > 1",
		(string) $order_id
	),
	ARRAY_A
);
ez_qa_line( empty( $queue_rows ), 'B4 no duplicate pending SMS rows (434387/434389)' );

$pending_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM sms_sending_queue WHERE order_id = %s AND sent_time IS NULL AND token IN ('434387','434389')",
		(string) $order_id
	)
);
fwrite( STDOUT, "INFO pending reservation SMS rows: {$pending_count}\n" );

// Wallet snapshot + steps.
$snap_wallet = (int) get_post_meta( $order_id, '_ez_checkout_snapshot_wallet_share', true );
$snap_online = (int) get_post_meta( $order_id, '_ez_checkout_snapshot_online_payable', true );
$snap_prepaid = (int) get_post_meta( $order_id, '_ez_checkout_snapshot_prepaid', true );
fwrite( STDOUT, "INFO snapshot prepaid={$snap_prepaid} online={$snap_online} wallet_share={$snap_wallet}\n" );

foreach ( array( 'charge_once', 'reserve_once' ) as $step ) {
	$done = function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, $step );
	fwrite( STDOUT, 'INFO wallet step ' . $step . ': ' . ( $done ? 'done' : 'no' ) . "\n" );
}

$customer_id = (int) $order->get_customer_id();
if ( $customer_id > 0 ) {
	$wallet_txn = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, amount, description FROM wallet_transactions WHERE user_id = %d AND description LIKE %s ORDER BY ID",
			$customer_id,
			'%' . $wpdb->esc_like( (string) $order_id ) . '%'
		),
		ARRAY_A
	);
	$net = 0;
	foreach ( (array) $wallet_txn as $tx ) {
		$net += (int) ( $tx['amount'] ?? 0 );
		fwrite( STDOUT, 'INFO wallet_txn id=' . (int) $tx['ID'] . ' amount=' . (int) $tx['amount'] . ' desc=' . substr( (string) $tx['description'], 0, 80 ) . "\n" );
	}
	if ( $snap_wallet > 0 ) {
		$expected_net = -1 * $snap_wallet;
		$tol          = (int) apply_filters( 'ez_wallet_share_snapshot_tolerance', 3 );
		ez_qa_line( abs( $net - $expected_net ) <= max( $tol, (int) ( $snap_wallet * 0.02 ) ), "W1 wallet net {$net} vs expected ~{$expected_net} (snap_wallet={$snap_wallet})" );
	}
	$has_reserve = false;
	foreach ( (array) $wallet_txn as $tx ) {
		if ( strpos( (string) ( $tx['description'] ?? '' ), 'رزرو بازی' ) !== false && (int) ( $tx['amount'] ?? 0 ) < 0 ) {
			$has_reserve = true;
			break;
		}
	}
	if ( $snap_wallet > 0 ) {
		ez_qa_line( $has_reserve, 'W2 reserve_once transaction (رزرو بازی) exists when snap wallet_share > 0' );
	} elseif ( $snap_prepaid > 0 && $snap_online > 0 && $order->is_paid() ) {
		ez_qa_line( $has_reserve, 'W2 reserve_once transaction (رزرو بازی) exists for full online paid order' );
	}
}

fwrite( STDOUT, "\nOrder {$order_id} summary: {$passes} pass, {$failures} fail\n" );
fwrite( STDOUT, "WC status: " . $order->get_status() . ' paid=' . ( $order->is_paid() ? 'yes' : 'no' ) . "\n" );

exit( $failures > 0 ? 1 : 0 );
