<?php
/** lines 4437-5129 → shop/booking/resolver-and-conflicts.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_booking_pipeline_is_done($order_id) {
    return (bool) get_post_meta($order_id, 'booking_pipeline_done_at', true);
}

function ez_booking_pipeline_finalize($order_id, $state = 'done') {
    update_post_meta($order_id, 'booking_pipeline_state', $state);
    update_post_meta($order_id, 'booking_pipeline_done_at', time());
    delete_post_meta($order_id, 'booking_pipeline_started_at');
}

/** @return string 11-digit form 09… or ''. */
function ez_normalize_billing_phone_11( $phone ) {
	if ( null === $phone || false === $phone ) {
		return '';
	}
	$d = preg_replace( '/\D+/', '', (string) $phone );
	if ( $d !== '' && strlen( $d ) === 10 && str_starts_with( $d, '9' ) ) {
		$d = '0' . $d;
	}
	return ( strlen( $d ) === 11 ) ? $d : '';
}

/**
 * Order IDs oldest first — same logged-in customer, product line, sans.
 *
 * @return int[]
 */
function ez_customer_pending_order_ids_same_slot( $customer_id, $product_id, $sans_ts, $exclude_order_id = 0 ) {
	global $wpdb;

	$customer_id      = (int) $customer_id;
	$product_id       = (int) $product_id;
	$sans_ts          = is_numeric( $sans_ts ) ? (string) (int) $sans_ts : '';
	$exclude_order_id = (int) $exclude_order_id;

	if ( $customer_id <= 0 || $product_id <= 0 || $sans_ts === '' ) {
		return [];
	}

	$posts       = $wpdb->posts;
	$postmeta    = $wpdb->postmeta;
	$order_ids   = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$posts} p
			INNER JOIN {$postmeta} pm_s ON pm_s.post_id = p.ID AND pm_s.meta_key = 'sans_time' AND pm_s.meta_value = %s
			INNER JOIN {$postmeta} pm_c ON pm_c.post_id = p.ID AND pm_c.meta_key = '_customer_user' AND pm_c.meta_value = %d
			WHERE p.post_type = 'shop_order'
			AND p.post_status IN ('wc-pending','wc-on-hold')
			ORDER BY p.post_date ASC",
			$sans_ts,
			$customer_id
		)
	);

	if ( empty( $order_ids ) ) {
		return [];
	}

	$items_table = $wpdb->prefix . 'woocommerce_order_items';
	$itemmeta    = $wpdb->prefix . 'woocommerce_order_itemmeta';
	$matched     = [];

	foreach ( $order_ids as $oid ) {
		$oid = (int) $oid;
		if ( $exclude_order_id > 0 && $oid === $exclude_order_id ) {
			continue;
		}
		$line_pid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT im.meta_value FROM {$items_table} oi
				INNER JOIN {$itemmeta} im ON im.order_item_id = oi.order_item_id AND im.meta_key = '_product_id'
				WHERE oi.order_id = %d AND oi.order_item_type = 'line_item'
				ORDER BY oi.order_item_id ASC LIMIT 1",
				$oid
			)
		);
		if ( (int) $line_pid === $product_id ) {
			$matched[] = $oid;
		}
	}

	return $matched;
}

/**
 * Guest orders (no customer user) matching product + sans + phone.
 *
 * @param string $phone_normalized 11 digits 09…
 * @return int[] Oldest first (by post_date).
 */
function ez_guest_pending_order_ids_same_slot_by_phone( $phone_normalized, $product_id, $sans_ts, $exclude_order_id = 0 ) {
	global $wpdb;

	$phone_normalized = ez_normalize_billing_phone_11( $phone_normalized );
	$product_id       = (int) $product_id;
	$sans_ts          = is_numeric( $sans_ts ) ? (string) (int) $sans_ts : '';
	$exclude_order_id = (int) $exclude_order_id;

	if ( strlen( $phone_normalized ) !== 11 || $product_id <= 0 || $sans_ts === '' ) {
		return [];
	}

	$posts     = $wpdb->posts;
	$postmeta  = $wpdb->postmeta;
	$order_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT p.ID FROM {$posts} p
			INNER JOIN {$postmeta} pm_s ON pm_s.post_id = p.ID AND pm_s.meta_key = 'sans_time' AND pm_s.meta_value = %s
			LEFT JOIN {$postmeta} pm_c ON pm_c.post_id = p.ID AND pm_c.meta_key = '_customer_user'
			WHERE p.post_type = 'shop_order'
			AND p.post_status IN ('wc-pending','wc-on-hold')
			AND ( pm_c.meta_id IS NULL OR pm_c.meta_value = '0' OR pm_c.meta_value = '' )
			ORDER BY p.post_date ASC",
			$sans_ts
		)
	);

	if ( empty( $order_ids ) ) {
		return [];
	}

	$items_table = $wpdb->prefix . 'woocommerce_order_items';
	$itemmeta    = $wpdb->prefix . 'woocommerce_order_itemmeta';
	$matched     = [];

	foreach ( $order_ids as $oid ) {
		$oid = (int) $oid;
		if ( $exclude_order_id > 0 && $oid === $exclude_order_id ) {
			continue;
		}

		$line_pid = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT im.meta_value FROM {$items_table} oi
				INNER JOIN {$itemmeta} im ON im.order_item_id = oi.order_item_id AND im.meta_key = '_product_id'
				WHERE oi.order_id = %d AND oi.order_item_type = 'line_item'
				ORDER BY oi.order_item_id ASC LIMIT 1",
				$oid
			)
		);
		if ( (int) $line_pid !== $product_id ) {
			continue;
		}

		$phone_ok = false;

		$players_raw = get_post_meta( $oid, 'players_phone', true );
		if ( is_string( $players_raw ) && $players_raw !== '' ) {
			$cand = ez_normalize_billing_phone_11( $players_raw );
			if ( $cand === $phone_normalized ) {
				$phone_ok = true;
			}
		}

		if ( ! $phone_ok ) {
			$order = wc_get_order( $oid );
			if ( $order && ez_normalize_billing_phone_11( $order->get_billing_phone() ) === $phone_normalized ) {
				$phone_ok = true;
			}
		}

		if ( $phone_ok ) {
			$matched[] = $oid;
		}
	}

	return $matched;
}

/**
 * Pending order IDs for resolver: logged-in customer takes precedence over guest phone.
 *
 * @return int[]
 */
function ez_pending_order_ids_same_slot( $customer_id, $phone_normalized, $product_id, $sans_ts, $exclude_order_id = 0 ) {
	if ( (int) $customer_id > 0 ) {
		return ez_customer_pending_order_ids_same_slot( (int) $customer_id, $product_id, $sans_ts, $exclude_order_id );
	}
	return ez_guest_pending_order_ids_same_slot_by_phone( $phone_normalized, $product_id, $sans_ts, $exclude_order_id );
}

/**
 * @return WC_Order[]
 */
function ez_pending_orders_same_slot( $customer_id, $phone_normalized, $product_id, $sans_ts, $exclude_order_id = 0 ) {
	$orders = [];
	foreach ( ez_pending_order_ids_same_slot( $customer_id, $phone_normalized, $product_id, $sans_ts, $exclude_order_id ) as $oid ) {
		$o = wc_get_order( (int) $oid );
		if ( $o && $o->has_status( [ 'pending', 'on-hold' ] ) ) {
			$orders[] = $o;
		}
	}
	return $orders;
}

/**
 * @return string[]
 */
function ez_booking_order_coupon_codes_sorted( WC_Order $order ) {
	$c = $order->get_coupon_codes();
	if ( ! is_array( $c ) ) {
		return [];
	}
	$c = array_map( 'strtolower', array_map( 'strval', $c ) );
	sort( $c, SORT_STRING );
	return $c;
}

/**
 * Structural match: product, qty, sans, payment type, coupons, prepaid & online totals (±۲ تومان).
 *
 * @param array $attempt Keys: product_id, sans_ts, quantity, ez_payment_type, coupon_codes (array), prepaid, online_payable.
 */
function ez_booking_attempt_matches_pending_order( WC_Order $order, array $attempt ) {
	if ( empty( $attempt['skip_amount_match'] ) ) {
		$needs = [ 'product_id', 'sans_ts', 'quantity', 'ez_payment_type', 'coupon_codes', 'prepaid', 'online_payable' ];
		foreach ( $needs as $k ) {
			if ( ! array_key_exists( $k, $attempt ) ) {
				return false;
			}
		}
	} else {
		if ( empty( $attempt['product_id'] ) || ! isset( $attempt['sans_ts'] ) ) {
			return false;
		}
	}

	$product_id = (int) $attempt['product_id'];
	$sans_ts    = is_numeric( $attempt['sans_ts'] ) ? (string) (int) $attempt['sans_ts'] : '';
	if ( $product_id <= 0 || $sans_ts === '' ) {
		return false;
	}

	$o_sans = get_post_meta( $order->get_id(), 'sans_time', true );
	$o_sans = is_numeric( $o_sans ) ? (string) (int) $o_sans : '';
	if ( $o_sans !== $sans_ts ) {
		return false;
	}

	$o_pt = (string) get_post_meta( $order->get_id(), 'ez_payment_type', true );
	if ( $o_pt === '' ) {
		$o_pt = 'partial';
	}
	$a_pt = (string) ( $attempt['ez_payment_type'] ?? 'partial' );
	if ( $a_pt === '' ) {
		$a_pt = 'partial';
	}
	if ( $o_pt !== $a_pt ) {
		return false;
	}

	$first = null;
	foreach ( $order->get_items( 'line_item' ) as $item ) {
		if ( $item instanceof WC_Order_Item_Product ) {
			$first = $item;
			break;
		}
	}
	if ( ! $first || (int) $first->get_product_id() !== $product_id ) {
		return false;
	}
	if ( empty( $attempt['skip_qty_match'] ) ) {
		if ( (int) $first->get_quantity() !== (int) $attempt['quantity'] ) {
			return false;
		}
	}

	if ( ! empty( $attempt['skip_amount_match'] ) ) {
		return true;
	}

	$attempt_coupons = $attempt['coupon_codes'];
	if ( ! is_array( $attempt_coupons ) ) {
		return false;
	}
	$attempt_coupons = array_map( 'strtolower', array_map( 'strval', $attempt_coupons ) );
	sort( $attempt_coupons, SORT_STRING );
	if ( ez_booking_order_coupon_codes_sorted( $order ) !== $attempt_coupons ) {
		return false;
	}

	$o_prepaid = (int) round( (float) get_post_meta( $order->get_id(), 'prepaid', true ) );
	$a_prepaid = (int) round( (float) $attempt['prepaid'] );

	$o_online = (int) round( (float) $order->get_total() );

	if ( abs( $o_prepaid - $a_prepaid ) > 2 ) {
		return false;
	}

	$a_online = (int) round( (float) $attempt['online_payable'] );
	if ( abs( $o_online - $a_online ) > 2 ) {
		return false;
	}

	return true;
}

/**
 * Mirror checkout ez_final_payment_amount stack for resolver (sans + qty + coupons + optional user-tier + wallet).
 *
 * @return array{ prepaid:int, online_payable:int, gross_total:int } or empty valid on failure.
 */
function ez_resolver_amounts_for_slot( int $product_id, int $book_timestamp, int $quantity, string $payment_type ) {
	global $wldb;

	if ( ! function_exists( 'WC' ) || ! WC() || ! WC()->cart ) {
		return [];
	}

	$payment_type = in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial';
	if ( $product_id <= 0 || $book_timestamp <= 0 || $quantity <= 0 ) {
		return [];
	}

	$day_type = ez_get_single_reserve_like_day_type( $book_timestamp );
	$sanses   = get_sanses( $product_id );
	$asli     = '';

	if ( ! empty( $sanses[ $day_type ] ) && is_array( $sanses[ $day_type ] ) ) {
		foreach ( $sanses[ $day_type ] as $sans ) {
			if ( wp_date( 'H:i', $book_timestamp ) === $sans['time'] ) {
				$asli = $sans['off_price'] ?: $sans['price'];
				break;
			}
		}
	}

	if ( (int) $asli <= 0 ) {
		return [];
	}

	if ( get_post_meta( $product_id, 'special_discount_enable', true ) ) {
		if ( get_post_meta( $product_id, 'special_discount_date', true ) > time() ) {
			$asli = (int) $asli * ( 1 - get_post_meta( $product_id, 'special_discount_percentage', true ) / 100 );
		}
	}

	$pish_per_person = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
	$pish_per_person = ! empty( $pish_per_person ) ? (int) $pish_per_person : 1;

	$total         = (int) $asli * $quantity;
	$deposit_line  = (int) $asli * $pish_per_person;
	$amount_to_pay = $payment_type === 'partial' ? $deposit_line : $total;

	foreach ( WC()->cart ? WC()->cart->get_coupons() : [] as $code => $coupon ) {
		$code          = is_string( $code ) ? $code : ( is_object( $coupon ) && method_exists( $coupon, 'get_code' ) ? $coupon->get_code() : '' );
		$coupon_amount = $code !== '' ? ez_get_coupon_discount_amount( $code, $total ) : 0;

		if ( $coupon_amount >= $amount_to_pay ) {
			$amount_to_pay = 0;
			break;
		}
		$amount_to_pay -= $coupon_amount;
	}

	$uid = get_current_user_id();
	if ( in_array( (int) $uid, [ 3325, 2, 80 ], true ) ) {
		$discount               = function_exists( 'get_user_discount' ) ? get_user_discount() : [ 'percentage' => 0 ];
		$user_level_discount    = ( $total * (float) ( $discount['percentage'] ?? 0 ) ) / 100;
		$amount_to_pay         -= (int) $user_level_discount;
	}

	$wallet_balance = ( is_object( $wldb ) && method_exists( $wldb, 'get_balance' ) ) ? (float) $wldb->get_balance( $uid ) : 0.0;
	if ( $wallet_balance >= $amount_to_pay ) {
		$amount_to_pay = 0;
	} else {
		$amount_to_pay -= (int) $wallet_balance;
	}

	$amount_to_pay = max( 0, (int) $amount_to_pay );

	$prepaid_meta = $payment_type === 'partial' ? $deposit_line : $total;

	return [
		'gross_total'    => $total,
		'prepaid'        => $prepaid_meta,
		'online_payable' => $amount_to_pay,
	];
}

/**
 * Resolver attempt array from REST API params + ez_api_checkout_compute_amounts().
 */
function ez_resolver_attempt_from_api_place_order( array $computed, int $quantity, $sans_ts, string $payment_type ) {
	$payment_type = in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial';
	$sans_ts      = is_numeric( $sans_ts ) ? (string) (int) $sans_ts : '';
	$product_id   = (int) ( $computed['product_id'] ?? 0 );
	if ( empty( $computed['valid'] ) || $sans_ts === '' || $product_id <= 0 || $quantity <= 0 ) {
		return [];
	}

	return [
		'product_id'       => $product_id,
		'sans_ts'          => $sans_ts,
		'quantity'         => $quantity,
		'ez_payment_type'  => $payment_type,
		'coupon_codes'     => [],
		'prepaid'          => (float) ( $computed['prepaid_amount'] ?? 0 ),
		'online_payable'   => (float) ( $computed['online_payable'] ?? 0 ),
	];
}

/** POST checkout + WC cart — used from woocommerce_after_checkout_validation */
function ez_resolver_attempt_from_booking_details_post_cart( array $booking_details, int $fallback_product_id, string $sans_ts ) {
	$product_id = isset( $booking_details['add-to-cart'] ) ? (int) $booking_details['add-to-cart'] : $fallback_product_id;
	$sans_ts    = is_numeric( $sans_ts ) ? (string) (int) $sans_ts : '';

	$qty = isset( $booking_details['quantity'] ) ? (int) $booking_details['quantity'] : 0;
	if ( $qty <= 0 && WC()->cart ) {
		foreach ( WC()->cart->get_cart() as $row ) {
			if ( isset( $row['product_id'] ) && (int) $row['product_id'] === $product_id ) {
				$qty = isset( $row['quantity'] ) ? (int) $row['quantity'] : $qty;
				break;
			}
		}
	}
	if ( $qty <= 0 ) {
		$qty = 1;
	}

	$payment_type = 'partial';
	if ( isset( $_POST['ez_payment_type'] ) ) {
		$payment_type = sanitize_text_field( wp_unslash( (string) $_POST['ez_payment_type'] ) );
	} elseif ( isset( $_POST['post_data'] ) ) {
		parse_str( wp_unslash( (string) $_POST['post_data'] ), $post_data_array );
		if ( isset( $post_data_array['ez_payment_type'] ) ) {
			$payment_type = sanitize_text_field( (string) $post_data_array['ez_payment_type'] );
		}
	}

	$coupon_codes = ( WC()->cart ) ? WC()->cart->get_applied_coupons() : [];

	if ( function_exists( 'ez_checkout_compute_amounts' ) && function_exists( 'ez_checkout_get_booking_context' ) ) {
		$bctx                      = ez_checkout_get_booking_context();
		$bctx['book_timestamp']    = (int) $sans_ts;
		$bctx['product_id']        = $product_id;
		$bctx['quantity']         = $qty;
		$bctx['effective_quantity'] = $qty;
		$bctx['requested_quantity'] = $qty;
		$bctx['source']            = 'resolver_post';
		$comp                      = ez_checkout_compute_amounts( $bctx, $payment_type );
		if ( ! empty( $comp['valid'] ) ) {
			return [
				'product_id'      => $product_id,
				'sans_ts'         => $sans_ts,
				'quantity'        => $qty,
				'ez_payment_type' => in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial',
				'coupon_codes'    => $coupon_codes,
				'prepaid'         => (float) ( $comp['prepaid_amount'] ?? 0 ),
				'online_payable'  => (float) ( $comp['online_payable'] ?? 0 ),
			];
		}
	}

	$book_ts = (int) $sans_ts;
	$am      = ez_resolver_amounts_for_slot( $product_id, $book_ts, $qty, $payment_type );
	if ( empty( $am ) ) {
		return [];
	}

	return [
		'product_id'      => $product_id,
		'sans_ts'         => $sans_ts,
		'quantity'        => $qty,
		'ez_payment_type' => in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial',
		'coupon_codes'    => $coupon_codes,
		'prepaid'         => (float) $am['prepaid'],
		'online_payable'  => (float) $am['online_payable'],
	];
}

/** GET checkout page (form-checkout template) */
function ez_resolver_attempt_from_get_cart( int $product_id, $sans_ts, int $quantity ) {
	$sans_ts = is_numeric( $sans_ts ) ? (string) (int) $sans_ts : '';
	if ( $product_id <= 0 || $sans_ts === '' || $quantity <= 0 || ! WC()->cart ) {
		return [];
	}

	$payment_type = isset( $_GET['ez_payment_type'] ) ? sanitize_text_field( wp_unslash( (string) $_GET['ez_payment_type'] ) ) : 'partial';
	$coupon_codes = WC()->cart->get_applied_coupons();

	if ( function_exists( 'ez_checkout_compute_amounts' ) ) {
		$bctx = [
			'book_timestamp'     => (int) $sans_ts,
			'product_id'         => $product_id,
			'quantity'           => $quantity,
			'effective_quantity' => $quantity,
			'requested_quantity' => $quantity,
			'source'             => 'resolver_get',
			'valid'              => true,
		];
		$comp = ez_checkout_compute_amounts( $bctx, $payment_type );
		if ( ! empty( $comp['valid'] ) ) {
			return [
				'product_id'       => $product_id,
				'sans_ts'          => $sans_ts,
				'quantity'         => $quantity,
				'ez_payment_type'  => in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial',
				'coupon_codes'     => $coupon_codes,
				'prepaid'          => (float) ( $comp['prepaid_amount'] ?? 0 ),
				'online_payable'   => (float) ( $comp['online_payable'] ?? 0 ),
				'skip_qty_match'   => true,
			];
		}
	}

	$am = ez_resolver_amounts_for_slot( $product_id, (int) $sans_ts, $quantity, $payment_type );
	if ( empty( $am ) ) {
		return [];
	}

	return [
		'product_id'      => $product_id,
		'sans_ts'         => $sans_ts,
		'quantity'        => $quantity,
		'ez_payment_type' => in_array( $payment_type, [ 'partial', 'complete' ], true ) ? $payment_type : 'partial',
		'coupon_codes'    => $coupon_codes,
		'prepaid'         => (float) $am['prepaid'],
		'online_payable'  => (float) $am['online_payable'],
		'skip_qty_match'  => true,
	];
}

/**
 * @param array $opts customer_id, phone_normalized (guest), product_id, sans_ts, exclude_order_id, attempt, use_replace_lock optional
 *
 * @return array{ status: 'none'|'reuse'|'replace', order_id?: int, payment_url?: string, cancelled_order_ids?: int[] }
 */
function ez_resolve_pending_booking_for_checkout( array $opts ) {
	$customer_id        = isset( $opts['customer_id'] ) ? (int) $opts['customer_id'] : 0;
	$phone_normalized   = isset( $opts['phone_normalized'] ) ? ez_normalize_billing_phone_11( $opts['phone_normalized'] ) : '';
	$product_id         = isset( $opts['product_id'] ) ? (int) $opts['product_id'] : 0;
	$sans_ts            = isset( $opts['sans_ts'] ) ? ( is_numeric( $opts['sans_ts'] ) ? (string) (int) $opts['sans_ts'] : '' ) : '';
	$exclude            = isset( $opts['exclude_order_id'] ) ? (int) $opts['exclude_order_id'] : 0;
	$attempt            = isset( $opts['attempt'] ) && is_array( $opts['attempt'] ) ? $opts['attempt'] : [];
	$use_replace_lock   = ! isset( $opts['use_replace_lock'] ) || ! empty( $opts['use_replace_lock'] );

	if ( $product_id <= 0 || $sans_ts === '' ) {
		return [ 'status' => 'none' ];
	}
	if ( $customer_id <= 0 && strlen( $phone_normalized ) !== 11 ) {
		return [ 'status' => 'none' ];
	}

	if ( empty( $attempt ) ) {
		return [ 'status' => 'none' ];
	}

	$pending = ez_pending_orders_same_slot( $customer_id, $phone_normalized, $product_id, $sans_ts, $exclude );
	if ( empty( $pending ) ) {
		return [ 'status' => 'none' ];
	}

	$reuse_order = null;
	foreach ( $pending as $po ) {
		if ( ez_booking_attempt_matches_pending_order( $po, $attempt ) ) {
			$reuse_order = $po;
			break;
		}
	}

	if ( $reuse_order ) {
		$url = '';
		if ( $reuse_order->needs_payment() ) {
			$url = str_replace( 'pay_for_order=true&', '', $reuse_order->get_checkout_payment_url( true ) );
		}
		return [
			'status'       => 'reuse',
			'order_id'     => $reuse_order->get_id(),
			'payment_url'  => $url,
		];
	}

	$replace_targets = [];
	foreach ( $pending as $po ) {
		if ( ! $po->needs_payment() ) {
			continue;
		}
		$replace_targets[] = $po;
	}

	if ( empty( $replace_targets ) ) {
		return [ 'status' => 'none' ];
	}

	$lock_key = sprintf(
		'ez_ck_lock_%s_%d_%s',
		$customer_id > 0 ? 'u' . $customer_id : 'ph' . md5( $phone_normalized ),
		$product_id,
		$sans_ts
	);

	if ( $use_replace_lock ) {
		if ( get_transient( $lock_key ) ) {
			return [ 'status' => 'none' ];
		}
		set_transient( $lock_key, 1, 45 );
	}

	$cancelled = [];
	$note      = 'لغو خودکار: سفارش معلق جایگزین شد (checkout resolver).';

	foreach ( $replace_targets as $po ) {
		$oid = $po->get_id();
		update_post_meta( $oid, '_ez_resolver_auto_cancel', time() );
		$po->update_status( 'cancelled', $note );
		$cancelled[] = $oid;
	}

	if ( $use_replace_lock ) {
		delete_transient( $lock_key );
	}

	return [
		'status'              => 'replace',
		'cancelled_order_ids' => $cancelled,
	];
}

/**
 * Same logged-in customer already has an unpaid Woo order for this product + sans slot.
 *
 * @param int $exclude_order_id Pass current order when paying an existing order.
 */
function ez_customer_pending_order_same_slot($customer_id, $product_id, $sans_ts, $exclude_order_id = 0) {
	return ! empty( ez_customer_pending_order_ids_same_slot( $customer_id, $product_id, $sans_ts, $exclude_order_id ) );
}

/**
 * Same guest (no WP user on order) already has pending/on-hold order for product + sans + phone.
 *
 * @param string $phone_normalized 11 digits starting with 09 (e.g. 09123456789).
 * @param int    $exclude_order_id Skip this order id when re-paying.
 */
function ez_guest_pending_order_same_slot_by_phone( $phone_normalized, $product_id, $sans_ts, $exclude_order_id = 0 ) {
	return ! empty( ez_guest_pending_order_ids_same_slot_by_phone( $phone_normalized, $product_id, $sans_ts, $exclude_order_id ) );
}

/**
 * First confirmed booking row for the given slot (status = 1), excluding $exclude_order_id.
 *
 * @param int $product_id        WC product id (room).
 * @param int $sans_ts           Slot unix timestamp.
 * @param int $exclude_order_id  WC order id to skip (e.g. when re-running pipeline for that order).
 * @return array{wc_order_id:int,customer_id:int,phone:string}|null
 */
function ez_booking_first_confirmed_conflict_row( $product_id, $sans_ts, $exclude_order_id = 0 ) {
	$product_id       = (int) $product_id;
	$sans_ts          = (int) $sans_ts;
	$exclude_order_id = (int) $exclude_order_id;

	if ( $product_id <= 0 || $sans_ts <= 0 ) {
		return null;
	}

	$data = array(
		'single_value' => false,
		'query'        => 'SELECT `wc_order_id`, `customer_id`, `phone` FROM `wp_zb_booking_history` WHERE `room_id` = ' . $product_id . ' AND `booking_time` = ' . $sans_ts . ' AND `status` = 1',
	);
	$rows = json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => $data ) ), true );
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return null;
	}

	foreach ( $rows as $row ) {
		$row_order_id = isset( $row['wc_order_id'] ) ? (int) $row['wc_order_id'] : 0;
		if ( $exclude_order_id > 0 && $row_order_id === $exclude_order_id ) {
			continue;
		}

		return array(
			'wc_order_id' => $row_order_id,
			'customer_id' => isset( $row['customer_id'] ) ? (int) $row['customer_id'] : 0,
			'phone'       => isset( $row['phone'] ) ? (string) $row['phone'] : '',
		);
	}

	return null;
}

/**
 * Whether the conflicting booking row belongs to the current viewer (logged-in id or guest phone).
 *
 * @param array{wc_order_id?:int,customer_id?:int,phone?:string} $conflict_row             From ez_booking_first_confirmed_conflict_row or booking row array.
 * @param int                                                    $viewer_customer_id       WC customer / user id (0 = guest).
 * @param string                                                 $viewer_phone_normalized  11 digits 09… when guest.
 */
function ez_booking_conflict_row_is_same_viewer( array $conflict_row, $viewer_customer_id, $viewer_phone_normalized = '' ) {
	$viewer_customer_id = (int) $viewer_customer_id;
	if ( $viewer_customer_id > 0 && isset( $conflict_row['customer_id'] ) && (int) $conflict_row['customer_id'] === $viewer_customer_id ) {
		return true;
	}
	if ( $viewer_customer_id <= 0 && strlen( (string) $viewer_phone_normalized ) === 11 ) {
		if ( ! function_exists( 'ez_normalize_billing_phone_11' ) ) {
			return false;
		}
		$stored = ez_normalize_billing_phone_11( $conflict_row['phone'] ?? '' );
		return ( $stored !== '' && $stored === $viewer_phone_normalized );
	}

	return false;
}

/**
 * Persian error message when a sans is already confirmed-booked (status = 1) for API responses.
 *
 * Distinguishes "you already booked this slot" (same viewer) from generic "someone else booked it".
 *
 * @param array  $sans_row                Row from wp_zb_booking_history (e.g. SELECT * …).
 * @param int    $viewer_wc_customer_id   Logged-in WC customer id (0 when guest).
 * @param string $viewer_phone_for_guest  Raw or digits-only phone when guest API client.
 */
function ez_api_message_sans_already_booked_confirmed( array $sans_row, $viewer_wc_customer_id, $viewer_phone_for_guest = '' ) {
	$viewer_wc_customer_id = (int) $viewer_wc_customer_id;
	$cr                    = array(
		'wc_order_id' => (int) ( $sans_row['wc_order_id'] ?? 0 ),
		'customer_id' => (int) ( $sans_row['customer_id'] ?? 0 ),
		'phone'       => isset( $sans_row['phone'] ) ? (string) $sans_row['phone'] : '',
	);
	$phone_norm = '';
	if ( $viewer_wc_customer_id <= 0 && $viewer_phone_for_guest !== '' && function_exists( 'ez_normalize_billing_phone_11' ) ) {
		$phone_norm = ez_normalize_billing_phone_11( $viewer_phone_for_guest );
	}
	if ( ez_booking_conflict_row_is_same_viewer( $cr, $viewer_wc_customer_id, $viewer_wc_customer_id > 0 ? '' : $phone_norm ) ) {
		return 'این سانس قبلاً توسط شما رزرو شده است. سفارش قبلی را در لیست سفارش‌ها بررسی کنید؛ امکان ثبت تکراری برای همان سانس وجود ندارد.';
	}

	return 'سانس توسط شخص دیگری رزرو شده است.';
}

/**
 * Any confirmed booking exists for this slot (except the given order — e.g. pipeline for that order).
 *
 * @param int $product_id  WC product id (room).
 * @param int $sans_time   Slot unix timestamp.
 * @param int $order_id    Exclude this WC order's row when re-running the same order.
 * @param int $customer_id Deprecated, ignored: same-customer duplicate orders are treated as a conflict.
 */
function ez_booking_conflict_with_other_order( $product_id, $sans_time, $order_id = 0, $customer_id = 0 ) {
	unset( $customer_id );
	return ez_booking_first_confirmed_conflict_row( (int) $product_id, (int) $sans_time, (int) $order_id ) !== null;
}

function ez_wallet_step_is_done($order_id, $step) {
    return (bool) get_post_meta($order_id, '_ez_wallet_' . $step, true);
}

function ez_wallet_step_mark_done($order_id, $step) {
    update_post_meta($order_id, '_ez_wallet_' . $step, time());
}

function ez_booking_exists_for_order($order_id) {
    $order_id = (int) $order_id;
    if ( $order_id <= 0 ) {
        return false;
    }
    if ( function_exists( 'medoo_queries' ) ) {
        try {
            $mq = medoo_queries();
            if ( $mq && method_exists( $mq, 'has' ) && $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) ) ) {
                return true;
            }
        } catch ( Throwable $e ) {
            error_log( '[ez_booking_exists_for_order] medoo_queries: ' . $e->getMessage() );
        }
    }
    $data = [
        'single_value' => true,
        'query'        => "SELECT `wc_order_id` FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$order_id} LIMIT 1",
    ];
    $row = (array) json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => $data ) ), true );
    return ! empty( $row );
}
