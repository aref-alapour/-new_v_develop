<?php
/**
 * Team CRM: phone reservation for sans management.
 *
 * @package Escapezoom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Roles allowed to use phone reservation (same as sans_management menu).
 *
 * @return string[]
 */
function ez_team_phone_reservation_allowed_roles(): array {
	return array( 'administrator', 'supervisor', 'poshtiban', 'team_admin' );
}

/**
 * @param int $user_id WP user id (actor).
 */
function ez_team_user_can_phone_reservation( int $user_id = 0 ): bool {
	$user_id = $user_id > 0 ? $user_id : (int) get_current_user_id();
	if ( $user_id <= 0 ) {
		return false;
	}
	$user = get_userdata( $user_id );
	if ( ! $user || empty( $user->roles ) ) {
		return false;
	}
	return (bool) array_intersect( ez_team_phone_reservation_allowed_roles(), (array) $user->roles );
}

/**
 * Slot price breakdown aligned with ez_run_thankyou_booking_pipeline / ez_booking_compute_prepaid_breakdown_for_order.
 *
 * @return array{
 *   valid:bool,
 *   message?:string,
 *   asli?:int,
 *   pish_per_person?:int,
 *   quantity?:int,
 *   deposit?:int,
 *   item_total?:int,
 *   partial_amount?:int,
 *   complete_amount?:int
 * }
 */
function ez_team_phone_reservation_compute_slot_prices( int $product_id, int $sans_ts, int $quantity ): array {
	$product_id = (int) $product_id;
	$sans_ts    = (int) $sans_ts;
	$quantity   = max( 1, (int) $quantity );

	if ( $product_id <= 0 || $sans_ts <= 0 ) {
		return array( 'valid' => false, 'message' => 'شناسه بازی یا زمان سانس نامعتبر است.' );
	}

	if ( ! function_exists( 'get_sanses' ) || ! function_exists( 'ez_get_single_reserve_like_day_type' ) ) {
		return array( 'valid' => false, 'message' => 'توابع برنامه سانس در دسترس نیست.' );
	}

	$post = get_post( $product_id );
	if ( ! $post || $post->post_type !== 'product' ) {
		return array( 'valid' => false, 'message' => 'بازی یافت نشد.' );
	}

	$pish_per_person = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
	$pish_per_person = ! empty( $pish_per_person ) ? (int) $pish_per_person : 1;

	$day_type = ez_get_single_reserve_like_day_type( $sans_ts );
	$sanses   = get_sanses( $product_id );
	$asli     = 0;

	if ( ! empty( $sanses[ $day_type ] ) && is_array( $sanses[ $day_type ] ) ) {
		foreach ( $sanses[ $day_type ] as $sans ) {
			if ( function_exists( 'wp_date' ) && wp_date( 'H:i', $sans_ts ) === $sans['time'] ) {
				$asli = (int) ( $sans['off_price'] ?: $sans['price'] );
				break;
			}
		}
	}

	if ( $asli <= 0 ) {
		return array( 'valid' => false, 'message' => 'قیمت این سانس در برنامه یافت نشد.' );
	}

	if ( get_post_meta( $product_id, 'special_discount_enable', true ) ) {
		if ( (int) get_post_meta( $product_id, 'special_discount_date', true ) > time() ) {
			$asli = (int) ( $asli * ( 1 - (float) get_post_meta( $product_id, 'special_discount_percentage', true ) / 100 ) );
		}
	}

	$deposit    = (int) $pish_per_person * (int) $asli;
	$item_total = $quantity * (int) $asli;

	return array(
		'valid'            => true,
		'asli'             => (int) $asli,
		'pish_per_person'  => (int) $pish_per_person,
		'quantity'         => $quantity,
		'deposit'          => $deposit,
		'item_total'       => $item_total,
		'partial_amount'   => $deposit,
		'complete_amount'  => $item_total,
	);
}

/**
 * JSON-friendly quote for modal (both payment types).
 *
 * @return array<string,mixed>|WP_Error
 */
function ez_team_phone_reservation_quote( int $product_id, int $sans_ts, int $quantity ) {
	$prices = ez_team_phone_reservation_compute_slot_prices( $product_id, $sans_ts, $quantity );
	if ( empty( $prices['valid'] ) ) {
		return new WP_Error( 'invalid_slot', $prices['message'] ?? 'محاسبه قیمت ناموفق بود.' );
	}

	$fmt = static function ( int $amount ): string {
		return number_format( $amount ) . ' تومان';
	};

	return array(
		'asli'              => (int) $prices['asli'],
		'asli_formatted'    => $fmt( (int) $prices['asli'] ),
		'pish_per_person'   => (int) $prices['pish_per_person'],
		'quantity'          => (int) $prices['quantity'],
		'partial_amount'    => (int) $prices['partial_amount'],
		'partial_formatted' => $fmt( (int) $prices['partial_amount'] ),
		'partial_label'     => 'تیکت بیعانه (پیش‌پرداخت)',
		'partial_hint'      => sprintf(
			'بیعانه %d نفر × %s',
			(int) $prices['pish_per_person'],
			$fmt( (int) $prices['asli'] )
		),
		'complete_amount'   => (int) $prices['complete_amount'],
		'complete_formatted'=> $fmt( (int) $prices['complete_amount'] ),
		'complete_label'    => 'تیکت پرداخت کامل',
		'complete_hint'     => sprintf(
			'%d نفر × %s',
			(int) $prices['quantity'],
			$fmt( (int) $prices['asli'] )
		),
		'item_total'        => (int) $prices['item_total'],
		'deposit'           => (int) $prices['deposit'],
	);
}

/**
 * Validate slot is bookable.
 *
 * @return true|WP_Error
 */
function ez_team_phone_reservation_validate_slot( int $product_id, int $sans_ts ) {
	if ( $sans_ts <= time() ) {
		return new WP_Error( 'past_sans', 'زمان سانس گذشته است.' );
	}

	if ( function_exists( 'ez_booking_first_confirmed_conflict_row' ) ) {
		$conflict = ez_booking_first_confirmed_conflict_row( $product_id, $sans_ts, 0 );
		if ( $conflict ) {
			return new WP_Error( 'slot_booked', 'این سانس قبلاً رزرو شده است.' );
		}
	}

	return true;
}

/**
 * Find WP user id by normalized mobile (login without leading 0, or billing_phone).
 */
function ez_team_find_player_user_id_by_phone( string $phone_11 ): int {
	$phone_11 = function_exists( 'ez_normalize_billing_phone_11' )
		? ez_normalize_billing_phone_11( $phone_11 )
		: '';

	if ( strlen( $phone_11 ) !== 11 ) {
		return 0;
	}

	$login = ltrim( $phone_11, '0' );
	$user  = get_user_by( 'login', $login );
	if ( $user && ! empty( $user->ID ) ) {
		return (int) $user->ID;
	}

	$by_meta = get_users(
		array(
			'meta_key'   => 'billing_phone',
			'meta_value' => $phone_11,
			'number'     => 1,
			'fields'     => 'ID',
		)
	);
	if ( ! empty( $by_meta[0] ) ) {
		return (int) $by_meta[0];
	}

	return 0;
}

/**
 * Resolve existing customer or create one for phone reservation (role: customer).
 *
 * @return array{user_id:int,created:bool,phone:string}|WP_Error
 */
function ez_team_resolve_or_create_player_by_phone(
	string $phone,
	string $first_name = '',
	string $last_name = '',
	int $actor_id = 0
) {
	if ( ! ez_team_user_can_phone_reservation( $actor_id ) ) {
		return new WP_Error( 'forbidden', 'شما اجازه ساخت/انتخاب پلیر را ندارید.' );
	}

	$phone_11 = function_exists( 'ez_normalize_billing_phone_11' )
		? ez_normalize_billing_phone_11( $phone )
		: '';

	if ( strlen( $phone_11 ) !== 11 || ! preg_match( '/^09\d{9}$/', $phone_11 ) ) {
		return new WP_Error( 'invalid_phone', 'شماره موبایل باید ۱۱ رقم و با 09 شروع شود.' );
	}

	$existing_id = ez_team_find_player_user_id_by_phone( $phone_11 );
	if ( $existing_id > 0 ) {
		return array(
			'user_id' => $existing_id,
			'created' => false,
			'phone'   => $phone_11,
		);
	}

	$first_name = trim( $first_name );
	$last_name  = trim( $last_name );
	if ( $first_name === '' || $last_name === '' ) {
		return new WP_Error( 'name_required', 'برای مشتری جدید، نام و نام خانوادگی الزامی است.' );
	}

	$login = ltrim( $phone_11, '0' );
	$email = $phone_11 . '@info.com';
	if ( function_exists( 'ez_get_domain' ) ) {
		$domain = ez_get_domain();
		if ( is_string( $domain ) && $domain !== '' ) {
			$email = $login . '@' . $domain;
		}
	}

	$user_id = wp_insert_user(
		array(
			'user_login' => $login,
			'user_email' => $email,
			'user_pass'  => wp_generate_password( 16, true ),
			'first_name' => $first_name,
			'last_name'  => $last_name,
			'role'       => 'customer',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		$retry_id = ez_team_find_player_user_id_by_phone( $phone_11 );
		if ( $retry_id > 0 ) {
			return array(
				'user_id' => $retry_id,
				'created' => false,
				'phone'   => $phone_11,
			);
		}
		return new WP_Error( 'user_create_failed', 'خطا در ایجاد کاربر: ' . $user_id->get_error_message() );
	}

	$user_id = (int) $user_id;
	update_user_meta( $user_id, 'billing_phone', $phone_11 );
	update_user_meta( $user_id, 'billing_first_name', $first_name );
	update_user_meta( $user_id, 'billing_last_name', $last_name );
	update_user_meta( $user_id, '_ez_created_via_phone_reservation', '1' );
	if ( $actor_id > 0 ) {
		update_user_meta( $user_id, '_ez_created_by_team_user', $actor_id );
	}

	return array(
		'user_id' => $user_id,
		'created' => true,
		'phone'   => $phone_11,
	);
}

/**
 * @param string $payment_type partial|complete
 * @return array{success:bool,order_id?:int,message:string,code?:string}|WP_Error
 */
function ez_team_create_phone_reservation(
	int $product_id,
	int $sans_ts,
	int $user_id,
	int $quantity,
	string $payment_type,
	int $actor_id,
	string $note = '',
	bool $player_was_created = false
) {
	if ( ! ez_team_user_can_phone_reservation( $actor_id ) ) {
		return new WP_Error( 'forbidden', 'شما اجازه رزرو تلفنی را ندارید.' );
	}

	$payment_type = in_array( $payment_type, array( 'partial', 'complete' ), true ) ? $payment_type : 'partial';
	$user_id      = (int) $user_id;
	$quantity     = max( 1, (int) $quantity );

	if ( $user_id <= 0 ) {
		return new WP_Error( 'invalid_user', 'پلیر انتخاب نشده است.' );
	}

	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return new WP_Error( 'invalid_user', 'کاربر یافت نشد.' );
	}

	$phone = '';
	if ( function_exists( 'ez_normalize_billing_phone_11' ) ) {
		$phone = ez_normalize_billing_phone_11( get_user_meta( $user_id, 'billing_phone', true ) ?: $user->user_login );
	}
	if ( $phone === '' || strlen( $phone ) !== 11 ) {
		return new WP_Error( 'invalid_phone', 'شماره موبایل پلیر معتبر نیست؛ برای ارسال پیامک لازم است.' );
	}

	$slot_ok = ez_team_phone_reservation_validate_slot( $product_id, $sans_ts );
	if ( is_wp_error( $slot_ok ) ) {
		return $slot_ok;
	}

	$prices = ez_team_phone_reservation_compute_slot_prices( $product_id, $sans_ts, $quantity );
	if ( empty( $prices['valid'] ) ) {
		return new WP_Error( 'pricing', $prices['message'] ?? 'محاسبه قیمت ناموفق بود.' );
	}

	$prepaid     = $payment_type === 'partial' ? (int) $prices['partial_amount'] : (int) $prices['complete_amount'];
	$item_total  = (int) $prices['item_total'];
	$deposit     = (int) $prices['deposit'];

	if ( $prepaid <= 0 ) {
		return new WP_Error( 'pricing', 'مبلغ رزرو صفر است.' );
	}

	$lock_key = '_ez_phone_reserve_lock_' . (int) $product_id . '_' . (int) $sans_ts;
	$lock_ts  = (int) get_option( $lock_key, 0 );
	if ( $lock_ts && ( time() - $lock_ts ) < 45 ) {
		return new WP_Error( 'locked', 'این سانس همین حالا در حال رزرو است؛ چند ثانیه بعد دوباره تلاش کنید.' );
	}
	update_option( $lock_key, time(), false );

	try {
		if ( ! function_exists( 'wc_create_order' ) ) {
			return new WP_Error( 'wc_missing', 'ووکامرس در دسترس نیست.' );
		}

		$first = trim( (string) get_user_meta( $user_id, 'first_name', true ) );
		$last  = trim( (string) get_user_meta( $user_id, 'last_name', true ) );
		if ( $first === '' ) {
			$first = trim( (string) get_user_meta( $user_id, 'billing_first_name', true ) );
		}
		if ( $last === '' ) {
			$last = trim( (string) get_user_meta( $user_id, 'billing_last_name', true ) );
		}

		$order = wc_create_order(
			array(
				'status'      => 'pending',
				'customer_id' => $user_id,
			)
		);

		if ( ! $order instanceof WC_Order ) {
			return new WP_Error( 'order_create', 'ایجاد سفارش ناموفق بود.' );
		}

		$order_id = (int) $order->get_id();
		$product  = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_delete_post( $order_id, true );
			return new WP_Error( 'product_missing', 'محصول یافت نشد.' );
		}

		$order->add_product(
			$product,
			$quantity,
			array(
				'totals' => array(
					'total' => $prepaid,
				),
			)
		);

		$order->set_address(
			array(
				'first_name' => $first,
				'last_name'  => $last,
				'phone'      => $phone,
				'email'      => $user->user_email,
			),
			'billing'
		);

		$order->calculate_totals();
		$order->save();

		update_post_meta( $order_id, 'players_phone', $phone );
		update_post_meta( $order_id, 'sans_time', $sans_ts );
		update_post_meta( $order_id, 'ez_payment_type', $payment_type );
		update_post_meta( $order_id, 'prepaid', $prepaid );
		update_post_meta( $order_id, 'total_payment', $item_total );
		update_post_meta( $order_id, '_order_total_2', $prepaid );
		update_post_meta( $order_id, '_ez_order_source', 'phone_reservation' );
		update_post_meta( $order_id, '_ez_phone_reservation', '1' );
		update_post_meta( $order_id, '_ez_phone_reservation_by', $actor_id );
		update_post_meta( $order_id, '_ez_phone_reservation_at', time() );
		update_post_meta( $order_id, 'order_method', 'team_phone' );

		if ( $note !== '' ) {
			update_post_meta( $order_id, '_ez_phone_reserve_note', sanitize_textarea_field( $note ) );
		}

		if ( $payment_type === 'complete' ) {
			update_post_meta( $order_id, 'deposit', $deposit );
		}

		$pish_pp = (int) ( $prices['pish_per_person'] ?? 1 );
		update_post_meta( $order_id, 'ticket_tedad', $pish_pp );

		if ( function_exists( 'ez_checkout_capture_wallet_and_totals_snapshot' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				ez_checkout_capture_wallet_and_totals_snapshot( $order_id, $order );
			}
		}

		if ( function_exists( 'save_to_markting_table' ) ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				save_to_markting_table( $order_id, array(), $order );
			}
		}

		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'phone_reservation_start',
				array(
					'product_id'    => $product_id,
					'sans_ts'       => $sans_ts,
					'user_id'       => $user_id,
					'quantity'      => $quantity,
					'payment_type'  => $payment_type,
					'prepaid'       => $prepaid,
					'actor_id'      => $actor_id,
				)
			);
		}

		$actor     = get_userdata( $actor_id );
		$txn       = 'PHONE-' . (int) $actor_id . '-' . gmdate( 'YmdHis' );
		$order     = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'order_reload', 'بارگذاری سفارش پس از ایجاد ناموفق بود.' );
		}

		$order->payment_complete( wc_clean( $txn ) );
		clean_post_cache( $order_id );

		if ( function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) {
			ez_reconcile_single_order_wp_markting_wc_booking( $order_id );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'order_reload', 'بارگذاری سفارش پس از پرداخت ناموفق بود.' );
		}

		if (
			! $order->has_status( array( 'partially-paid', 'completed-paid', 'processing', 'conflict' ) )
			&& function_exists( 'ez_run_thankyou_booking_pipeline' )
		) {
			ez_run_thankyou_booking_pipeline( $order_id );
			clean_post_cache( $order_id );
			$order = wc_get_order( $order_id );
		}

		$uname = $actor ? $actor->user_login : (string) $actor_id;
		$order->add_order_note(
			sprintf(
				'رزرو تلفنی توسط %s — نوع پرداخت: %s — مبلغ: %s تومان.%s',
				$uname,
				$payment_type === 'partial' ? 'پیش‌پرداخت (بیعانه)' : 'پرداخت کامل',
				number_format( $prepaid ),
				$player_was_created ? ' (پلیر جدید در همین رزرو ساخته شد.)' : ''
			)
		);

		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage(
				$order_id,
				'phone_reservation_done',
				array( 'status' => $order->get_status() )
			);
		}

		$st = $order->get_status();

		if ( $order->has_status( array( 'partially-paid', 'completed-paid' ) ) ) {
			return array(
				'success'  => true,
				'order_id' => $order_id,
				'code'     => 'paid',
				'message'  => 'رزرو تلفنی ثبت شد. سانس بسته شد و پیامک‌ها در صف ارسال هستند.',
			);
		}

		if ( $order->has_status( 'conflict' ) ) {
			return array(
				'success'  => true,
				'order_id' => $order_id,
				'code'     => 'conflict',
				'message'  => 'سفارش ثبت شد ولی تداخل سانس تشخیص داده شد؛ وضعیت را در سفارشات بررسی کنید.',
			);
		}

		if ( $order->has_status( 'cancelled' ) ) {
			return array(
				'success'  => false,
				'order_id' => $order_id,
				'code'     => 'cancelled',
				'message'  => 'سفارش لغو شد (مثلاً کمبود کیف پول). جزئیات را در یادداشت سفارش ببینید.',
			);
		}

		return array(
			'success'  => true,
			'order_id' => $order_id,
			'code'     => $st ?: 'unknown',
			'message'  => 'رزرو ثبت شد. وضعیت فعلی: ' . ( $st ?: '—' ),
		);
	} catch ( Throwable $e ) {
		error_log( '[ez_team_create_phone_reservation] ' . $e->getMessage() );
		return new WP_Error( 'exception', 'خطای داخلی هنگام رزرو تلفنی.' );
	} finally {
		delete_option( $lock_key );
	}
}
