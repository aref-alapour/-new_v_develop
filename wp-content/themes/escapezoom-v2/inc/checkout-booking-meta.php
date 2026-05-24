<?php
/**
 * Checkout booking extraction (sans_time, product, qty) and checkout snapshot metas.
 *
 * @package Escapezoom
 */

defined( 'ABSPATH' ) || exit;

/** Official cart policy: snapshot lives on order meta; WooCommerce may empty cart per default (no delayed empty_cart). */
if ( ! function_exists( 'ez_checkout_cart_policy' ) ) {
	function ez_checkout_cart_policy(): string {
		return (string) apply_filters( 'ez_checkout_cart_policy', 'snapshot_only' );
	}
}

/**
 * Unpaid order: delete and send user back to checkout. Paid order: on-hold for manual fix.
 *
 * @param WC_Order $order          Order.
 * @param string   $reason_code    Short internal reason.
 * @param string   $customer_message Notice text.
 */
function ez_abort_checkout_order_marketing_failure( WC_Order $order, string $reason_code, string $customer_message ): void {
	$oid = (int) $order->get_id();
	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $oid, 'checkout_aborted_markting', array( 'reason' => $reason_code ) );
	}
	if ( $order->is_paid() ) {
		$order->update_status( 'on-hold', 'EZ marketing: ' . $reason_code );
		wc_add_notice( $customer_message, 'error' );
		return;
	}
	if ( function_exists( 'wc_delete_order' ) ) {
		wc_delete_order( $oid, true );
	} else {
		wp_delete_post( $oid, true );
	}
	wc_add_notice( $customer_message, 'error' );
	wp_safe_redirect( wc_get_checkout_url() );
	exit;
}

/**
 * Block redirect to payment gateway when order still needs payment but payable total is zero.
 *
 * @param WC_Order $order Order.
 */
function ez_checkout_assert_gateway_payable( WC_Order $order ): void {
	if ( ! $order->needs_payment() ) {
		return;
	}

	$payable = max( 0, (int) $order->get_total() );
	if ( $payable > 0 ) {
		return;
	}

	ez_abort_checkout_order_marketing_failure(
		$order,
		'gateway_zero_payable_anomaly',
		__( 'مبلغ پرداخت آنلاین نامعتبر است. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2' )
	);
}

/**
 * Orders that already paid (bank/gateway) must not be auto-cancelled by the booking pipeline.
 */
function ez_order_block_pipeline_auto_cancel( WC_Order $order ): bool {
	if ( $order->is_paid() ) {
		return true;
	}
	return $order->has_status(
		array(
			'processing',
			'partially-paid',
			'completed-paid',
			'completed',
			'on-hold',
		)
	);
}

/**
 * Pipeline cancel path: unpaid → cancelled; paid/post-gateway → on-hold for manual review.
 *
 * @param WC_Order $order       Order.
 * @param string   $cancel_note Note for cancelled.
 * @param string   $hold_note   Optional note for on-hold.
 */
function ez_pipeline_apply_cancel_or_hold( WC_Order $order, string $cancel_note, string $hold_note = '' ): void {
	if ( function_exists( 'ez_order_block_pipeline_auto_cancel' ) && ez_order_block_pipeline_auto_cancel( $order ) ) {
		$order->update_status( 'on-hold', $hold_note !== '' ? $hold_note : 'EZ: ' . $cancel_note );
		return;
	}
	$order->update_status( 'cancelled', $cancel_note );
}

/**
 * Refund amount for pipeline conflict / reversal paths (bank + wallet snapshot).
 *
 * @param WC_Order $order               Order.
 * @param float    $prepaid             Prepaid meta.
 * @param float    $coupon_amount       Coupon share.
 * @param float    $user_level_discount Level discount.
 * @param float    $online_paid         Gateway-paid amount meta.
 */
function ez_order_compute_pipeline_refund_amount(
	WC_Order $order,
	float $prepaid,
	float $coupon_amount,
	float $user_level_discount,
	float $online_paid
): float {
	$order_id      = (int) $order->get_id();
	$refund_amount = max( 0.0, $prepaid - $coupon_amount - $user_level_discount );

	$snap_share_raw = get_post_meta( $order_id, '_ez_checkout_snapshot_wallet_share', true );
	if ( $snap_share_raw !== '' && $snap_share_raw !== false ) {
		$refund_amount = max( $refund_amount, (float) $snap_share_raw );
	}

	$refund_gateway = (float) get_post_meta( $order_id, '_ez_checkout_snapshot_online_payable', true );
	if ( $refund_gateway <= 0 ) {
		$refund_gateway = $online_paid;
	}

	return max( $refund_amount, $refund_gateway );
}

/**
 * Parse raw booking_details POST string to array (same normalization as legacy checkout).
 *
 * @param string $raw Raw POST value.
 * @return array|null Normalized associative array or null.
 */
function ez_parse_booking_details_string( $raw ): ?array {
	if ( $raw === '' || ! is_string( $raw ) ) {
		return null;
	}
	$raw   = wp_unslash( $raw );
	$first = json_decode( str_replace( '\\', '', $raw ) );
	$arr   = json_decode( wp_json_encode( $first ), true );
	return is_array( $arr ) ? $arr : null;
}

/**
 * Single source for booking payload on checkout: booking_details JSON, POST book (billing), session fallback.
 *
 * @param array|null     $post_server Typically $_POST; null uses $_POST.
 * @param bool           $use_cart_single When product_id/qty still empty, infer from WC cart if exactly one line.
 * @return array{
 *   sans_ts:int,
 *   product_id:int,
 *   qty:int,
 *   source:string,
 *   raw_json:?string,
 *   booking_details:?array,
 *   error:?string
 * }
 */
function ez_extract_booking_from_checkout_request( ?array $post_server = null, bool $use_cart_single = true ): array {
	$out = [
		'sans_ts'          => 0,
		'product_id'       => 0,
		'qty'              => 1,
		'source'           => '',
		'raw_json'         => null,
		'booking_details'  => null,
		'error'            => null,
	];
	if ( null === $post_server ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$post_server = $_POST;
	}

	$raw_json = isset( $post_server['booking_details'] ) ? (string) wp_unslash( $post_server['booking_details'] ) : '';
	if ( $raw_json === '' && isset( $post_server['post_data'] ) && is_string( $post_server['post_data'] ) ) {
		$parsed = array();
		parse_str( $post_server['post_data'], $parsed );
		if ( ! empty( $parsed['booking_details'] ) ) {
			$raw_json = (string) wp_unslash( $parsed['booking_details'] );
		}
	}
	if ( $raw_json !== '' ) {
		$out['raw_json'] = $raw_json;
	}

	$booking_details = $raw_json !== '' ? ez_parse_booking_details_string( $raw_json ) : null;

	if ( is_array( $booking_details ) ) {
		$out['booking_details'] = $booking_details;
		$sans_raw               = isset( $booking_details['book'] ) ? $booking_details['book'] : null;
		if ( $sans_raw !== '' && $sans_raw !== null && is_numeric( $sans_raw ) ) {
			$out['sans_ts'] = (int) $sans_raw;
			$out['source']  = $out['source'] ? $out['source'] . '+booking_details' : 'booking_details';
		}
		if ( ! empty( $booking_details['add-to-cart'] ) && is_numeric( $booking_details['add-to-cart'] ) ) {
			$out['product_id'] = (int) $booking_details['add-to-cart'];
		}
		if ( isset( $booking_details['quantity'] ) && is_numeric( $booking_details['quantity'] ) ) {
			$out['qty'] = max( 1, (int) $booking_details['quantity'] );
		}
	}

	// billing_book reserved for future/custom theme fields.
	if ( 0 === $out['sans_ts'] && isset( $post_server['billing_book'] ) && (string) $post_server['billing_book'] !== '' && is_numeric( $post_server['billing_book'] ) ) {
		$out['sans_ts'] = (int) $post_server['billing_book'];
		$out['source']  = $out['source'] ? $out['source'] . '+billing_book' : 'billing_book';
	}

	if ( 0 === $out['sans_ts'] && isset( $post_server['book'] ) && (string) $post_server['book'] !== '' && is_numeric( $post_server['book'] ) ) {
		$out['sans_ts'] = (int) $post_server['book'];
		$out['source']  = $out['source'] ? $out['source'] . '+post_book' : 'post_book';
	}

	if ( 0 === $out['sans_ts'] && isset( $_SESSION ) && is_array( $_SESSION ) && ! empty( $_SESSION['book'] ) && is_numeric( $_SESSION['book'] ) ) {
		$out['sans_ts'] = (int) $_SESSION['book'];
		$out['source']  = $out['source'] ? $out['source'] . '+session_book' : 'session_book';
	}

	if ( $use_cart_single && function_exists( 'WC' ) && WC()->cart && ! WC()->cart->is_empty() ) {
		$contents = WC()->cart->get_cart_contents();
		if ( 0 === $out['product_id'] && count( $contents ) === 1 ) {
			$one           = reset( $contents );
			$out['product_id'] = isset( $one['product_id'] ) ? (int) $one['product_id'] : 0;
			$bd = $out['booking_details'];
			if ( ( ! is_array( $bd ) || empty( $bd['quantity'] ) ) && isset( $one['quantity'] ) ) {
				$out['qty'] = max( 1, (int) $one['quantity'] );
			}
		}
	}

	return $out;
}

/**
 * Store sans_time + booking snapshot on the order during checkout.
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order object.
 */
function ez_checkout_update_order_booking_meta( $order_id, WC_Order $order ): void {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! $order instanceof WC_Order ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	$ex = ez_extract_booking_from_checkout_request( null, true );

	if ( (int) $ex['sans_ts'] > 0 ) {
		$order->update_meta_data( 'sans_time', (string) (int) $ex['sans_ts'] );
	}

	if ( ! empty( $ex['booking_details'] ) && is_array( $ex['booking_details'] ) ) {
		$order->update_meta_data( '_ez_booking_snapshot_json', wp_json_encode( $ex['booking_details'], JSON_UNESCAPED_UNICODE ) );
	}

	$order->update_meta_data( '_ez_booking_captured_at', (string) time() );
	$order->update_meta_data( '_ez_booking_capture_source', (string) $ex['source'] );
	$order->save_meta_data();
}

/**
 * Snapshot amounts at checkout for pipeline reconciliation (online total, wallet fee line).
 *
 * @param int      $order_id Order ID.
 * @param WC_Order $order    Order.
 */
function ez_checkout_capture_wallet_and_totals_snapshot( $order_id, WC_Order $order ): void {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! $order instanceof WC_Order ) {
		return;
	}

	$online = max( 0, (int) $order->get_total() );

	$wallet_line = 0;
	foreach ( $order->get_items( 'fee' ) as $fee ) {
		if ( $fee->get_name() === 'پرداخت با کیف پول' ) {
			$t = (int) $fee->get_total();
			// Fee is typically negative (deduction); snapshot stores positive "wallet applied".
			$wallet_line = $t <= 0 ? - $t : $t;
			break;
		}
	}

	if ( $online > 0 ) {
		update_post_meta( $order_id, '_ez_checkout_snapshot_online_payable', (string) $online );
	} else {
		delete_post_meta( $order_id, '_ez_checkout_snapshot_online_payable' );
	}
	update_post_meta( $order_id, '_ez_checkout_snapshot_wallet_applied', (string) (int) $wallet_line );
	update_post_meta( $order_id, '_ez_checkout_snapshot_captured_at', (string) time() );

	$breakdown = ez_booking_compute_prepaid_breakdown_for_order( $order_id, $order );
	if ( ! empty( $breakdown['prepaid'] ) ) {
		update_post_meta( $order_id, '_ez_checkout_snapshot_prepaid', (string) (int) $breakdown['prepaid'] );
	}

	$wallet_share_snap = ez_compute_wallet_share_for_order( $order, $breakdown );
	update_post_meta( $order_id, '_ez_checkout_snapshot_wallet_share', (string) (int) $wallet_share_snap );
}

/**
 * Wallet share at checkout/pipeline: prepaid minus (online + coupons + level discount).
 *
 * @param WC_Order     $order     Order.
 * @param array|null   $breakdown Optional prepaid breakdown from ez_booking_compute_prepaid_breakdown_for_order.
 */
function ez_compute_wallet_share_for_order( WC_Order $order, ?array $breakdown = null ): int {
	$order_id = (int) $order->get_id();
	if ( null === $breakdown || empty( $breakdown['prepaid'] ) ) {
		$breakdown = ez_booking_compute_prepaid_breakdown_for_order( $order_id, $order );
	}
	$prepaid    = (int) ( $breakdown['prepaid'] ?? 0 );
	$item_total = (int) ( $breakdown['item_total'] ?? 0 );
	if ( $prepaid <= 0 ) {
		return 0;
	}
	if ( $item_total <= 0 ) {
		$item_total = $prepaid;
	}

	$online = function_exists( 'ez_order_online_paid_amount' )
		? (float) ez_order_online_paid_amount( $order )
		: (float) $order->get_total();

	$coupon_amount = 0;
	$coupons       = $order->get_items( 'coupon' );
	if ( ! empty( $coupons ) && function_exists( 'ez_get_coupon_discount_amount' ) ) {
		foreach ( $coupons as $coupon_item ) {
			$coupon_amount += (int) ez_get_coupon_discount_amount( $coupon_item->get_code(), $item_total );
		}
	}

	$user_level_discount = 0;
	$user_id             = (int) $order->get_user_id();
	if ( $user_id > 0 && function_exists( 'get_user_discount' ) && in_array( $user_id, array( 3325, 2, 80 ), true ) ) {
		$discount            = get_user_discount( $order_id, $user_id );
		$user_level_discount = (int) ( ( $item_total * (float) ( $discount['percentage'] ?? 0 ) ) / 100 );
	}

	return max( 0, (int) round( $prepaid - ( $online + $coupon_amount + $user_level_discount ) ) );
}

/**
 * @return array{prepaid:int,deposit:int,item_total:int,asli:int,ez_payment_type:string}
 */
function ez_booking_compute_prepaid_breakdown_for_order( int $order_id, WC_Order $order ): array {
	$empty = [ 'prepaid' => 0, 'deposit' => 0, 'item_total' => 0, 'asli' => 0, 'ez_payment_type' => '' ];
	if ( ! function_exists( 'ez_order_primary_bookable_line_item' ) || ! function_exists( 'get_sanses' ) || ! function_exists( 'ez_get_single_reserve_like_day_type' ) ) {
		return $empty;
	}

	list( $product_id, $product_quantity ) = ez_order_primary_bookable_line_item( $order );
	$product_id       = $product_id ? (int) $product_id : 0;
	$product_quantity = max( 1, (int) $product_quantity );
	if ( $product_id <= 0 ) {
		return $empty;
	}

	$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
	if ( $sans_time <= 0 ) {
		return $empty;
	}

	$pish_per_person = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
	$pish_per_person = ! empty( $pish_per_person ) ? $pish_per_person : 1;

	$day_type = ez_get_single_reserve_like_day_type( $sans_time );
	$sanses   = get_sanses( $product_id );
	$asli     = 0;
	if ( ! empty( $sanses[ $day_type ] ) && is_array( $sanses[ $day_type ] ) ) {
		foreach ( $sanses[ $day_type ] as $sans ) {
			if ( function_exists( 'wp_date' ) && wp_date( 'H:i', (int) $sans_time ) === $sans['time'] ) {
				$asli = (int) ( $sans['off_price'] ?: $sans['price'] );
				break;
			}
		}
	}

	if ( get_post_meta( $product_id, 'special_discount_enable', true ) ) {
		if ( get_post_meta( $product_id, 'special_discount_date', true ) > time() ) {
			$asli = (int) ( $asli * ( 1 - get_post_meta( $product_id, 'special_discount_percentage', true ) / 100 ) );
		}
	}

	$deposit    = (int) $pish_per_person * (int) $asli;
	$item_total = $product_quantity * (int) $asli;

	$ez_payment_type = (string) get_post_meta( $order_id, 'ez_payment_type', true );
	if ( $ez_payment_type === 'partial' ) {
		$prepaid = $deposit;
	} else {
		$prepaid = $item_total;
	}

	return [
		'prepaid'         => (int) $prepaid,
		'deposit'         => (int) $deposit,
		'item_total'      => (int) $item_total,
		'asli'            => (int) $asli,
		'ez_payment_type' => $ez_payment_type,
	];
}

/**
 * Structured one-line log for checkout → pipeline tracing.
 *
 * @param int         $order_id Order ID or 0.
 * @param string      $stage    Machine-readable stage name.
 * @param array       $extra    Key-value pairs (JSON-encoded).
 */
function ez_log_order_pipeline_stage( int $order_id, string $stage, array $extra = [] ): void {
	$row = [
		'ts'       => gmdate( 'c' ),
		'order_id' => $order_id,
		'stage'    => $stage,
		'extra'    => $extra,
	];
	error_log( '[ez_order_flow] ' . wp_json_encode( $row, JSON_UNESCAPED_UNICODE ) );
}

/**
 * Orders in wc-pending (or pending payment) without sans_time — reporting only.
 *
 * @param int $limit Max rows.
 * @return array<int,array{id:int,status:string,created:string}>
 */
function ez_report_pending_orders_missing_sans_time( int $limit = 200 ): array {
	global $wpdb;
	$limit = max( 1, min( 500, $limit ) );

	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$wpdb->posts} p
			LEFT JOIN {$wpdb->postmeta} m ON m.post_id = p.ID AND m.meta_key = %s
			WHERE p.post_type = %s AND p.post_status = %s
			AND (m.meta_id IS NULL OR m.meta_value = '' OR m.meta_value = %s)
			ORDER BY p.post_date DESC
			LIMIT %d",
			'sans_time',
			'shop_order',
			'wc-pending',
			'0',
			$limit
		)
	);

	$rows = [];
	foreach ( $ids as $id ) {
		$oid  = (int) $id;
		$post = get_post( $oid );
		if ( ! $post ) {
			continue;
		}
		$rows[] = [
			'id'      => $oid,
			'status'  => (string) $post->post_status,
			'created' => (string) $post->post_date_gmt,
		];
	}
	return $rows;
}

/**
 * Admin / WP-CLI: print pending-without-sans report.
 */
function ez_run_pending_missing_sans_report_cli(): void {
	if ( ! current_user_can( 'manage_woocommerce' ) && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		return;
	}
	$rows = ez_report_pending_orders_missing_sans_time( 300 );
	ez_log_order_pipeline_stage( 0, 'report_pending_missing_sans', [ 'count' => count( $rows ) ] );
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		foreach ( $rows as $r ) {
			// phpcs:ignore WordPress.Security.EscapeAnalysis
			WP_CLI::log( $r['id'] . "\t" . $r['status'] . "\t" . $r['created'] );
		}
		return;
	}
	if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
		error_log( '[ez_pending_missing_sans] ' . wp_json_encode( $rows, JSON_UNESCAPED_UNICODE ) );
	}
}

/**
 * Atomically claim one-time post meta (true only for the first caller).
 *
 * @param int    $post_id  WooCommerce order / post ID.
 * @param string $meta_key Meta key to set once.
 */
function ez_order_meta_claim_once( int $post_id, string $meta_key ): bool {
	$post_id = (int) $post_id;
	if ( $post_id <= 0 || $meta_key === '' ) {
		return false;
	}
	if ( get_post_meta( $post_id, $meta_key, true ) ) {
		return false;
	}
	return (bool) add_post_meta( $post_id, $meta_key, time(), true );
}

/**
 * Short-lived lock so concurrent pipeline runs do not double-charge wallet or insert booking twice.
 *
 * @param int $order_id Order ID.
 * @param int $ttl      Seconds.
 */
function ez_booking_pipeline_acquire_lock( int $order_id, int $ttl = 45 ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return false;
	}
	$key = '_ez_pipeline_lock';
	$now = time();
	if ( add_post_meta( $order_id, $key, $now, true ) ) {
		return true;
	}
	$lock = (int) get_post_meta( $order_id, $key, true );
	if ( $lock > 0 && ( $now - $lock ) < $ttl ) {
		return false;
	}
	update_post_meta( $order_id, $key, $now );
	return true;
}

/**
 * @param int $order_id Order ID.
 */
function ez_booking_pipeline_release_lock( int $order_id ): void {
	delete_post_meta( (int) $order_id, '_ez_pipeline_lock' );
}

/**
 * Clear transient pipeline metas when exiting before finalize (avoids stuck booking_pipeline_state=running).
 *
 * @param int    $order_id Order ID.
 * @param string $state    Meta booking_pipeline_state value.
 */
function ez_booking_pipeline_abort_early( int $order_id, string $state = 'skipped' ): void {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return;
	}
	delete_post_meta( $order_id, 'booking_pipeline_started_at' );
	update_post_meta( $order_id, 'booking_pipeline_state', $state );
	if ( function_exists( 'ez_booking_pipeline_release_lock' ) ) {
		ez_booking_pipeline_release_lock( $order_id );
	}
}

/**
 * Operational notes for support: paths that set order to cancelled.
 *
 * @return array<int, string>
 */
function ez_get_order_cancel_audit_notes(): array {
	return [
		'Resolver replace: inc/saeed-codes.php ~L5350 ez_resolve_pending_booking_for_checkout — update_status cancelled, meta _ez_resolver_auto_cancel; wp_markting row kept, status synced via functions.php ez_markting_sync_on_resolver_cancel.',
		'Pipeline zero prepaid: inc/saeed-codes.php ez_run_thankyou_booking_pipeline — cancelled-zero-prepaid (fallback when snapshot/on-hold branches do not apply).',
		'Pipeline insufficient wallet: inc/saeed-codes.php — cancelled-insufficient-wallet; refund max(wallet_share, snapshot, online). Wallet drift only when online_paid>0 and snapshot wallet>0.',
		'ZarinPal NOK URL: inc/saeed-codes.php init Status=NOK wc-api/WC_ZPal — verify first; grace on-hold; else cancelled + order-failed.',
		'Zibal fail hook: WC_Zibal_Return_from_Gateway_Failed — verify/on-hold grace then cancelled; inc/ez-zibal-verify.php.',
		'Zibal recovery: ez_zibal_try_verify_now + reconcile grace + zibal_unverified_orders_process_cron; wp_markting never auto-deleted.',
	];
}

if ( defined( 'WP_CLI' ) && WP_CLI && class_exists( 'WP_CLI', false ) ) {
	WP_CLI::add_command(
		'ez-report-pending-sans',
		static function () {
			ez_run_pending_missing_sans_report_cli();
		}
	);
}
