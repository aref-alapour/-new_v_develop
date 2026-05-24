<?php
/**
 * Player (customer) wallet settlement at checkout snapshot + thankyou pipeline.
 *
 * @package Escapezoom
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param WC_Order $order Order.
 */
function ez_order_online_paid_amount( WC_Order $order ): int {
	$order_id = (int) $order->get_id();

	$snap = get_post_meta( $order_id, '_ez_checkout_snapshot_online_payable', true );
	if ( $snap !== '' && $snap !== false ) {
		$snap_int = (int) $snap;
		if ( $snap_int > 0 ) {
			return $snap_int;
		}
	}

	$total_2 = (int) get_post_meta( $order_id, '_order_total_2', true );
	if ( $total_2 > 0 ) {
		return $total_2;
	}

	$order_total = (int) get_post_meta( $order_id, '_order_total', true );
	if ( $order_total > 0 ) {
		return $order_total;
	}

	return max( 0, (int) $order->get_total() );
}

/**
 * @param WC_Order $order Order.
 */
function ez_order_prepaid_amount( WC_Order $order ): int {
	$order_id = (int) $order->get_id();
	$snap     = (int) get_post_meta( $order_id, '_ez_checkout_snapshot_prepaid', true );
	if ( $snap > 0 ) {
		return $snap;
	}
	if ( function_exists( 'ez_booking_compute_prepaid_breakdown_for_order' ) ) {
		$b = ez_booking_compute_prepaid_breakdown_for_order( $order_id, $order );
		return (int) ( $b['prepaid'] ?? 0 );
	}
	return 0;
}

/**
 * @param WC_Order $order      Order.
 * @param int      $item_total Line item total for coupon/level math.
 * @return array{coupon_amount:int,user_level_discount:int}
 */
function ez_order_wallet_discount_parts( WC_Order $order, int $item_total ): array {
	$order_id            = (int) $order->get_id();
	$coupon_amount       = 0;
	$user_level_discount = 0;
	$user_id             = (int) $order->get_user_id();

	$coupons = $order->get_items( 'coupon' );
	if ( ! empty( $coupons ) && function_exists( 'ez_get_coupon_discount_amount' ) ) {
		foreach ( $coupons as $coupon_item ) {
			$coupon_amount += (int) ez_get_coupon_discount_amount( $coupon_item->get_code(), $item_total );
		}
	}

	if ( $user_id > 0 && function_exists( 'get_user_discount' ) && in_array( $user_id, array( 3325, 2, 80 ), true ) ) {
		$discount            = get_user_discount( $order_id, $user_id );
		$user_level_discount = (int) ( ( $item_total * (float) ( $discount['percentage'] ?? 0 ) ) / 100 );
	}

	return array(
		'coupon_amount'       => $coupon_amount,
		'user_level_discount' => $user_level_discount,
	);
}

/**
 * Resolve wallet share: snapshot first, then formula.
 *
 * @param WC_Order $order Order.
 * @param int      $prepaid Prepaid amount.
 * @param int      $online_paid Gateway-paid amount.
 * @param int      $coupon_amount Coupon share.
 * @param int      $user_level_discount Level discount.
 */
function ez_order_wallet_share_resolve(
	WC_Order $order,
	int $prepaid,
	int $online_paid,
	int $coupon_amount,
	int $user_level_discount
): int {
	$order_id   = (int) $order->get_id();
	$snap_share = get_post_meta( $order_id, '_ez_checkout_snapshot_wallet_share', true );
	if ( $snap_share !== '' && $snap_share !== false && (int) $snap_share > 0 ) {
		return (int) $snap_share;
	}
	return max( 0, (int) round( $prepaid - ( $online_paid + $coupon_amount + $user_level_discount ) ) );
}

/**
 * @param int $order_id Order ID.
 */
function ez_wallet_step_clear_done( int $order_id, string $step ): void {
	delete_post_meta( $order_id, '_ez_wallet_' . $step );
}

/**
 * @param int    $order_id Order ID.
 * @param string $needle   Substring in description (e.g. رزرو بازی).
 */
function ez_wallet_transaction_exists_for_order( int $order_id, string $needle ): bool {
	global $wpdb;
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! $wpdb instanceof wpdb ) {
		return false;
	}
	$table = 'wallet_transactions';
	$found = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM `{$table}` WHERE description LIKE %s AND description LIKE %s LIMIT 1",
			'%' . $wpdb->esc_like( (string) $order_id ) . '%',
			'%' . $wpdb->esc_like( $needle ) . '%'
		)
	);
	return (int) $found > 0;
}

/**
 * Stable idempotency key for gateway charge (one per order + online amount).
 */
function ez_wallet_unique_charge_key( int $order_id, int $online_paid ): string {
	return 'player_charge:' . (int) $order_id . ':' . (int) $online_paid;
}

/**
 * Stable idempotency key for reservation debit (one per order + debit magnitude).
 */
function ez_wallet_unique_reserve_key( int $order_id, int $debit_amount ): string {
	return 'player_reserve:' . (int) $order_id . ':' . (int) abs( $debit_amount );
}

/**
 * @param string $unique_description Value stored in wallet_transactions.unique_description.
 */
function ez_wallet_transaction_exists_by_unique( string $unique_description ): bool {
	global $wpdb;
	$unique_description = trim( $unique_description );
	if ( $unique_description === '' || ! $wpdb instanceof wpdb ) {
		return false;
	}
	$table = 'wallet_transactions';
	$found = $wpdb->get_var(
		$wpdb->prepare(
			"SELECT ID FROM `{$table}` WHERE unique_description = %s LIMIT 1",
			$unique_description
		)
	);
	return (int) $found > 0;
}

/**
 * Whether a gateway charge for this order already exists (unique_description per order only).
 *
 * Old rows with NULL unique_description are ignored.
 *
 * @param int $order_id    Order ID.
 * @param int $user_id     Customer user ID (unused; kept for call-site compatibility).
 * @param int $online_paid Gateway-paid amount (toman).
 */
function ez_wallet_charge_exists_for_order( int $order_id, int $user_id, int $online_paid ): bool {
	if ( $order_id <= 0 || $online_paid <= 0 ) {
		return false;
	}
	return ez_wallet_transaction_exists_by_unique(
		ez_wallet_unique_charge_key( $order_id, $online_paid )
	);
}

/**
 * Whether the order has gateway payment intent meta (ZarinPal / Zibal).
 *
 * @param WC_Order $order Order.
 */
function ez_order_has_gateway_payment_intent( WC_Order $order ): bool {
	if ( (string) $order->get_meta( '_zarinpal_authority' ) !== '' ) {
		return true;
	}
	if ( function_exists( 'ez_zibal_get_track_id' ) && ez_zibal_get_track_id( $order ) !== '' ) {
		return true;
	}
	$track = (string) $order->get_meta( 'trackId' );
	return $track !== '';
}

/**
 * Insert a wallet row without throwing; never aborts the booking pipeline.
 *
 * @param object $wldb     EZ_Transaction_CRUD (or compatible).
 * @param int    $order_id WooCommerce order ID (for logs).
 * @param string $step     Pipeline step label (charge_once, reserve_once, …).
 * @param array  $row      Columns for wallet_transactions.
 * @return array{ok:bool,duplicate:bool,error:string}
 */
function ez_wallet_safe_insert( $wldb, int $order_id, string $step, array $row ): array {
	$result = array(
		'ok'        => false,
		'duplicate' => false,
		'error'     => '',
	);

	if ( ! is_object( $wldb ) || ! method_exists( $wldb, 'insert' ) ) {
		$result['error'] = 'wldb_unavailable';
		error_log( "[ez_wallet] {$step} insert skipped — wldb unavailable — order {$order_id}" );
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				$step . '_insert_failed',
				array( 'reason' => $result['error'] )
			);
		}
		return $result;
	}

	try {
		$inserted = $wldb->insert( $row );
	} catch ( Throwable $e ) {
		$result['error'] = $e->getMessage();
		error_log( "[ez_wallet] {$step} exception — order {$order_id}: " . $result['error'] );
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				$step . '_insert_failed',
				array(
					'reason' => 'exception',
					'error'  => $result['error'],
				)
			);
		}
		return $result;
	}

	if ( $inserted !== false ) {
		$result['ok'] = true;
		return $result;
	}

	global $wpdb;
	$db_error = ( $wpdb instanceof wpdb ) ? (string) $wpdb->last_error : '';
	$is_dup   = $db_error !== '' && ( stripos( $db_error, 'Duplicate' ) !== false || strpos( $db_error, '1062' ) !== false );

	if ( $is_dup ) {
		$result['ok']        = true;
		$result['duplicate'] = true;
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				$step . '_insert_duplicate',
				array(
					'unique' => isset( $row['unique_description'] ) ? (string) $row['unique_description'] : '',
					'db'     => $db_error,
				)
			);
		}
		return $result;
	}

	$result['error'] = $db_error !== '' ? $db_error : 'insert_returned_false';
	error_log( "[ez_wallet] {$step} insert failed — order {$order_id}: " . $result['error'] );
	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage(
			$order_id,
			$step . '_insert_failed',
			array(
				'error'  => $result['error'],
				'unique' => isset( $row['unique_description'] ) ? (string) $row['unique_description'] : '',
			)
		);
	}

	return $result;
}

/**
 * Clear stale wallet step meta when DB row is missing.
 *
 * @param int    $order_id Order ID.
 * @param string $step     charge_once|reserve_once|...
 * @param string $needle   Description needle (reserve / legacy).
 */
function ez_wallet_reconcile_step_meta( int $order_id, string $step, string $needle ): void {
	if ( ! function_exists( 'ez_wallet_step_is_done' ) || ! ez_wallet_step_is_done( $order_id, $step ) ) {
		return;
	}
	if ( ! ez_wallet_transaction_exists_for_order( $order_id, $needle ) ) {
		ez_wallet_step_clear_done( $order_id, $step );
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'wallet_step_meta_cleared',
				array(
					'step'   => $step,
					'needle' => $needle,
				)
			);
		}
	}
}

/**
 * Reconcile charge_once meta using unique_description (charge rows have no order_id in description text).
 *
 * @param int $order_id    Order ID.
 * @param int $user_id     Customer user ID.
 * @param int $online_paid Gateway-paid amount (toman).
 */
function ez_wallet_reconcile_charge_step_meta( int $order_id, int $user_id, int $online_paid ): void {
	if ( $online_paid <= 0 || ! function_exists( 'ez_wallet_step_is_done' ) || ! ez_wallet_step_is_done( $order_id, 'charge_once' ) ) {
		return;
	}
	$unique = ez_wallet_unique_charge_key( $order_id, $online_paid );
	if ( ! ez_wallet_charge_exists_for_order( $order_id, $user_id, $online_paid ) ) {
		ez_wallet_step_clear_done( $order_id, 'charge_once' );
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'wallet_step_meta_cleared',
				array(
					'step'   => 'charge_once',
					'unique' => $unique,
				)
			);
		}
	}
}

/**
 * Reconcile reserve_once meta using unique_description when possible.
 *
 * @param int    $order_id     Order ID.
 * @param int    $debit_amount Negative debit amount from pipeline.
 * @param string $product_title Product title for legacy description fallback.
 */
function ez_wallet_reconcile_reserve_step_meta( int $order_id, int $debit_amount, string $product_title = '' ): void {
	if ( ! function_exists( 'ez_wallet_step_is_done' ) || ! ez_wallet_step_is_done( $order_id, 'reserve_once' ) ) {
		return;
	}
	$exists = false;
	if ( $debit_amount !== 0 ) {
		$exists = ez_wallet_transaction_exists_by_unique( ez_wallet_unique_reserve_key( $order_id, $debit_amount ) );
	}
	if ( ! $exists ) {
		$exists = ez_wallet_transaction_exists_for_order( $order_id, 'رزرو بازی' );
	}
	if ( ! $exists ) {
		ez_wallet_step_clear_done( $order_id, 'reserve_once' );
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'wallet_step_meta_cleared',
				array(
					'step' => 'reserve_once',
				)
			);
		}
	}
}

/**
 * Whether this order should have a wp_markting row at checkout.
 *
 * @param WC_Order $order Order.
 */
function ez_order_needs_markting_row( WC_Order $order ): bool {
	$order_id = (int) $order->get_id();
	if ( (int) get_post_meta( $order_id, 'sans_time', true ) > 0 ) {
		return true;
	}
	if ( function_exists( 'ez_order_primary_bookable_line_item' ) ) {
		list( $pid, ) = ez_order_primary_bookable_line_item( $order );
		if ( (int) $pid > 0 ) {
			return true;
		}
	}
	$ezpt = (string) get_post_meta( $order_id, 'ez_payment_type', true );
	if ( in_array( $ezpt, array( 'partial', 'complete' ), true ) ) {
		return true;
	}
	if ( get_post_meta( $order_id, '_ez_booking_snapshot_json', true ) ) {
		return true;
	}
	return false;
}

/**
 * Player wallet eligible balance check (supports mixed wallet + gateway).
 */
function ez_player_wallet_balance_eligible(
	int $current_balance,
	int $wallet_share,
	int $prepaid,
	int $coupon_amount,
	int $user_level_discount,
	int $online_paid,
	bool $is_paid
): bool {
	if ( $wallet_share <= 0 ) {
		return true;
	}
	if ( $current_balance >= $wallet_share ) {
		return true;
	}
	if ( ! $is_paid || $online_paid <= 0 ) {
		return false;
	}
	$required_from_wallet = max( 0, $prepaid - $coupon_amount - $user_level_discount - $online_paid );
	return $required_from_wallet <= 0 || ( $current_balance + $online_paid ) >= ( $prepaid - $coupon_amount - $user_level_discount );
}

/**
 * Apply charge_once + reserve_once (or insufficient path). Idempotent.
 *
 * @param WC_Order $order         Order.
 * @param int      $prepaid       Prepaid (toman).
 * @param int      $item_total    Item total for discounts.
 * @param string   $product_title Product title for description.
 * @return array{ok:bool,early_exit?:bool,finalize_state?:string,reason?:string}
 */
function ez_player_wallet_apply_for_order(
	WC_Order $order,
	int $prepaid,
	int $item_total,
	string $product_title
): array {
	global $wldb;

	if ( ( ! isset( $wldb ) || ! is_object( $wldb ) || ! method_exists( $wldb, 'get_balance' ) ) && class_exists( 'EZ_Transaction_CRUD' ) ) {
		$wldb = new EZ_Transaction_CRUD();
	}
	if ( ! isset( $wldb ) || ! is_object( $wldb ) || ! method_exists( $wldb, 'insert' ) ) {
		return array( 'ok' => false, 'reason' => 'wldb_unavailable' );
	}

	$order_id = (int) $order->get_id();
	$user_id  = (int) $order->get_user_id();
	if ( $order_id <= 0 || $user_id <= 0 ) {
		return array( 'ok' => false, 'reason' => 'invalid_order_or_user' );
	}

	$online_paid = ez_order_online_paid_amount( $order );
	$discounts   = ez_order_wallet_discount_parts( $order, $item_total );
	$coupon_amount       = $discounts['coupon_amount'];
	$user_level_discount = $discounts['user_level_discount'];

	$wallet_share = ez_order_wallet_share_resolve(
		$order,
		$prepaid,
		$online_paid,
		$coupon_amount,
		$user_level_discount
	);

	$snap_share_raw = get_post_meta( $order_id, '_ez_checkout_snapshot_wallet_share', true );
	$wallet_tol     = (float) apply_filters( 'ez_wallet_share_snapshot_tolerance', 3 );
	$computed_share = max( 0, (int) round( $prepaid - ( $online_paid + $coupon_amount + $user_level_discount ) ) );

	if ( $snap_share_raw !== '' && $snap_share_raw !== false ) {
		$snap_share = (float) $snap_share_raw;
		if ( $online_paid > 0 && $snap_share > 0 && abs( (float) $wallet_share - $snap_share ) > $wallet_tol ) {
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage(
					$order_id,
					'wallet_share_drift',
					array(
						'computed' => (float) $wallet_share,
						'snapshot' => $snap_share,
						'formula'  => (float) $computed_share,
					)
				);
			}
			$order->update_status( 'on-hold', 'EZ: wallet-drift — اختلاف سهم کیف با لحظهٔ چک‌اوت.' );
			return array(
				'ok'             => false,
				'early_exit'     => true,
				'finalize_state' => 'on-hold-wallet-snapshot-drift',
				'reason'         => 'wallet_share_drift',
			);
		}
	}

	$current_balance = (int) $wldb->get_balance( $user_id );

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage(
			$order_id,
			'wallet_transaction_attempt',
			array(
				'prepaid'           => $prepaid,
				'online_paid'       => $online_paid,
				'wallet_share'      => $wallet_share,
				'snap_share'        => $snap_share_raw,
				'coupon'            => $coupon_amount,
				'user_level'        => $user_level_discount,
				'current_balance'   => $current_balance,
				'charge_done'       => function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'charge_once' ),
				'reserve_done'      => function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'reserve_once' ),
				'ez_payment_type'   => get_post_meta( $order_id, 'ez_payment_type', true ),
				'order_total_2'     => get_post_meta( $order_id, '_order_total_2', true ),
			)
		);
	}

	if ( ! ez_player_wallet_balance_eligible(
		$current_balance,
		$wallet_share,
		$prepaid,
		$coupon_amount,
		$user_level_discount,
		$online_paid,
		$order->is_paid()
	) ) {
		if ( function_exists( 'saeed_store' ) ) {
			saeed_store( "Insufficient wallet at pipeline order_id={$order_id} wallet_share={$wallet_share} balance={$current_balance}" );
		}

		$amount = function_exists( 'ez_order_compute_pipeline_refund_amount' )
			? ez_order_compute_pipeline_refund_amount(
				$order,
				(float) $prepaid,
				(float) $coupon_amount,
				(float) $user_level_discount,
				(float) $online_paid
			)
			: max( 0, (float) $wallet_share );

		if ( function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'reserve_once' ) ) {
			$refund_gateway = (float) get_post_meta( $order_id, '_ez_checkout_snapshot_online_payable', true );
			if ( $refund_gateway <= 0 ) {
				$refund_gateway = (float) $online_paid;
			}
			$reserved = max( 0, (float) $prepaid - (float) $coupon_amount - (float) $user_level_discount - $refund_gateway );
			$amount   = max( $amount, $reserved );
		}

		if ( function_exists( 'ez_booking_release_for_order' ) && function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
			ez_booking_release_for_order( $order_id );
		}

		$balance     = (int) $wldb->get_balance( $user_id ) + (int) $amount;
		$description = 'برگشت مبلغ - کسری در کیف پول' . ' - سفارش: ' . $order_id;

		if ( ! function_exists( 'ez_wallet_step_is_done' ) || ( ! ez_wallet_step_is_done( $order_id, 'refund_insufficient_wallet_share' ) && ! ez_wallet_step_is_done( $order_id, 'refund_insufficient_once' ) ) ) {
			$refund_insert = ez_wallet_safe_insert(
				$wldb,
				$order_id,
				'refund_insufficient_once',
				array(
					'user_id'     => $user_id,
					'amount'      => $amount,
					'balance'     => $balance,
					'description' => $description,
					'type'        => 'transaction',
				)
			);
			if ( $refund_insert['ok'] && function_exists( 'ez_wallet_step_mark_done' ) ) {
				ez_wallet_step_mark_done( $order_id, 'refund_insufficient_wallet_share' );
				ez_wallet_step_mark_done( $order_id, 'refund_insufficient_once' );
			}
		}

		if ( function_exists( 'ez_pipeline_apply_cancel_or_hold' ) ) {
			ez_pipeline_apply_cancel_or_hold(
				$order,
				'به علت کمتر بودن مبلغ پرداختی از موجودی کیف پول، سانس لغو شد.',
				'EZ: insufficient-wallet — بررسی دستی.'
			);
			$fin = function_exists( 'ez_order_block_pipeline_auto_cancel' ) && ez_order_block_pipeline_auto_cancel( $order )
				? 'on-hold-insufficient-wallet'
				: 'cancelled-insufficient-wallet';
		} else {
			$order->update_status( 'cancelled', 'به علت کمتر بودن مبلغ پرداختی از موجودی کیف پول، سانس لغو شد.' );
			$fin = 'cancelled-insufficient-wallet';
		}

		return array(
			'ok'             => false,
			'early_exit'     => true,
			'finalize_state' => $fin,
			'reason'         => 'insufficient_wallet',
		);
	}

	$debit_amount = 0;
	if ( $prepaid > $coupon_amount ) {
		$debit_amount = (int) round( ( $prepaid - ( $coupon_amount + $user_level_discount ) ) * -1 );
	}

	ez_wallet_reconcile_charge_step_meta( $order_id, $user_id, $online_paid );
	ez_wallet_reconcile_reserve_step_meta( $order_id, $debit_amount, $product_title );

	$is_phone_reserve = (string) get_post_meta( $order_id, '_ez_phone_reservation', true ) === '1';
	$charge_desc_prefix = $is_phone_reserve ? 'رزرو تلفنی — ' : '';
	$reserve_desc_prefix = $is_phone_reserve ? 'رزرو تلفنی - ' : '';

	if ( $online_paid > 0 ) {
		$unique_charge    = ez_wallet_unique_charge_key( $order_id, $online_paid );
		$charge_exists    = ez_wallet_charge_exists_for_order( $order_id, $user_id, $online_paid );
		$charge_step_done = function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'charge_once' );
		$charge_row_ok    = $charge_exists;

		if ( ! $charge_exists && ! $charge_step_done ) {
			$current_balance = (int) $wldb->get_balance( $user_id );
			$charge_insert   = ez_wallet_safe_insert(
				$wldb,
				$order_id,
				'charge_once',
				array(
					'user_id'            => $user_id,
					'amount'             => $online_paid,
					'balance'            => $current_balance + $online_paid,
					'description'        => $charge_desc_prefix . 'شارژ کیف پول - سفارش: ' . $order_id,
					'unique_description' => $unique_charge,
					'type'               => 'transaction',
				)
			);

			if ( $charge_insert['ok'] ) {
				$charge_row_ok = true;
				if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
					$stage = ! empty( $charge_insert['duplicate'] )
						? 'charge_once_skipped_duplicate'
						: 'charge_once_executed';
					ez_log_order_pipeline_stage(
						$order_id,
						$stage,
						array(
							'amount' => $online_paid,
							'unique' => $unique_charge,
						)
					);
				}
			}
		} elseif ( $charge_exists && function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'charge_once_skipped_duplicate',
				array(
					'unique' => $unique_charge,
				)
			);
		}

		if ( $charge_row_ok && function_exists( 'ez_wallet_step_mark_done' ) ) {
			ez_wallet_step_mark_done( $order_id, 'charge_once' );
		}
	}

	if ( $prepaid > $coupon_amount && $debit_amount !== 0 ) {
		if (
			$online_paid > 0
			&& ez_order_has_gateway_payment_intent( $order )
			&& ! ez_wallet_transaction_exists_by_unique( ez_wallet_unique_charge_key( $order_id, $online_paid ) )
		) {
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage(
					$order_id,
					'wallet_reserve_blocked_missing_charge',
					array(
						'online_paid' => $online_paid,
						'unique'      => ez_wallet_unique_charge_key( $order_id, $online_paid ),
					)
				);
			}
			$order->update_status( 'on-hold', 'EZ: پرداخت درگاهی ثبت شده ولی شارژ کیف پول برای این سفارش وجود ندارد.' );
			return array(
				'ok'             => false,
				'early_exit'     => true,
				'finalize_state' => 'on-hold-missing-wallet-charge',
				'reason'         => 'missing_charge_before_reserve',
			);
		}

		$unique_reserve    = ez_wallet_unique_reserve_key( $order_id, $debit_amount );
		$reserve_exists    = ez_wallet_transaction_exists_by_unique( $unique_reserve );
		if ( ! $reserve_exists ) {
			$reserve_exists = ez_wallet_transaction_exists_for_order( $order_id, 'رزرو بازی' );
		}
		$reserve_step_done = function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'reserve_once' );
		$reserve_settled   = $reserve_exists || $reserve_step_done;

		if ( ! $reserve_exists && ! $reserve_step_done ) {
			$current_balance = (int) $wldb->get_balance( $user_id );
			$reserve_insert  = ez_wallet_safe_insert(
				$wldb,
				$order_id,
				'reserve_once',
				array(
					'user_id'            => $user_id,
					'amount'             => $debit_amount,
					'balance'            => $current_balance + $debit_amount,
					'description'        => $reserve_desc_prefix . 'رزرو بازی ' . $product_title . ' - سفارش: ' . $order_id,
					'unique_description' => $unique_reserve,
					'type'               => 'transaction',
				)
			);

			if ( $reserve_insert['ok'] ) {
				$reserve_settled = true;
				if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
					$stage = ! empty( $reserve_insert['duplicate'] )
						? 'reserve_once_skipped_duplicate'
						: 'reserve_once_executed';
					ez_log_order_pipeline_stage(
						$order_id,
						$stage,
						array(
							'amount' => $debit_amount,
							'unique' => $unique_reserve,
						)
					);
				}
			}
		} elseif ( $reserve_exists && function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'reserve_once_skipped_duplicate',
				array(
					'unique' => $unique_reserve,
				)
			);
		}

		if ( $reserve_settled && function_exists( 'ez_wallet_step_mark_done' ) ) {
			ez_wallet_step_mark_done( $order_id, 'reserve_once' );
		}
	} elseif ( $debit_amount === 0 && function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $order_id, 'wallet_reserve_skipped_zero_amount', array( 'wallet_share' => $wallet_share ) );
	}

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $order_id, 'wallet_settle_ok', array( 'wallet_share' => $wallet_share ) );
	}

	return array( 'ok' => true );
}

/**
 * Emergency settle when payment completed but pipeline skipped wallet steps.
 *
 * @param int $order_id Order ID.
 */
function ez_customer_wallet_settle_for_order( int $order_id ): void {
	$order_id = (int) $order_id;
	$order    = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order || ! $order->is_paid() ) {
		return;
	}

	$prepaid = (int) get_post_meta( $order_id, 'prepaid', true );
	if ( $prepaid <= 0 && function_exists( 'ez_order_prepaid_amount' ) ) {
		$prepaid = ez_order_prepaid_amount( $order );
	}

	$snap_wallet = (int) get_post_meta( $order_id, '_ez_checkout_snapshot_wallet_share', true );
	if ( $snap_wallet <= 0 && function_exists( 'ez_order_wallet_share_resolve' ) ) {
		$online    = function_exists( 'ez_order_online_paid_amount' ) ? ez_order_online_paid_amount( $order ) : 0;
		$discounts = function_exists( 'ez_order_wallet_discount_parts' )
			? ez_order_wallet_discount_parts( $order, $prepaid )
			: array( 'coupon_amount' => 0, 'user_level_discount' => 0 );
		$snap_wallet = ez_order_wallet_share_resolve(
			$order,
			$prepaid,
			$online,
			$discounts['coupon_amount'],
			$discounts['user_level_discount']
		);
	}

	$online_for_settle = function_exists( 'ez_order_online_paid_amount' ) ? ez_order_online_paid_amount( $order ) : 0;
	$coupon_for_settle = 0;
	if ( $prepaid > 0 && function_exists( 'ez_order_wallet_discount_parts' ) ) {
		$coupon_for_settle = (int) ez_order_wallet_discount_parts( $order, $prepaid )['coupon_amount'];
	}
	$needs_reserve = $prepaid > $coupon_for_settle;
	if ( $snap_wallet <= 0 && ! ( $needs_reserve && $online_for_settle > 0 ) ) {
		return;
	}

	if ( function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'reserve_once' ) ) {
		if ( ez_wallet_transaction_exists_for_order( $order_id, 'رزرو بازی' ) ) {
			return;
		}
		ez_wallet_step_clear_done( $order_id, 'reserve_once' );
	}

	$user_for_settle = (int) $order->get_user_id();
	if ( $online_for_settle > 0 && $user_for_settle > 0 && function_exists( 'ez_wallet_step_is_done' ) && ez_wallet_step_is_done( $order_id, 'charge_once' ) ) {
		if ( ! ez_wallet_charge_exists_for_order( $order_id, $user_for_settle, $online_for_settle ) ) {
			ez_wallet_step_clear_done( $order_id, 'charge_once' );
		}
	}

	list( $pid, ) = function_exists( 'ez_order_primary_bookable_line_item' )
		? ez_order_primary_bookable_line_item( $order )
		: array( null, 0 );
	$product_title = $pid ? (string) get_the_title( (int) $pid ) : '';
	$item_total    = $prepaid > 0 ? $prepaid : max( 0, $snap_wallet );

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $order_id, 'wallet_emergency_settle', array( 'wallet_share' => $snap_wallet ) );
	}

	ez_player_wallet_apply_for_order( $order, $prepaid, $item_total, $product_title );
}
