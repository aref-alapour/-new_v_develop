<?php
/**
 * CRM Medoo lazily — do not connect on every frontend request (fixes MySQL 1040 exhaustion).
 *
 * @return object|null Medoo instance
 */
function ez_team_cancellation_medoo() {
	if ( ! function_exists( 'medoo' ) ) {
		return null;
	}
	return medoo();
}

$cancellation_reasons = cancellation_reasons();

$role_map = [
    'administrator' => 'admin',
    'accounting'    => 'admin',
    'supervisor'    => 'admin',
    'poshtiban'     => 'admin',
    'compiler'      => 'owner',
    'sans_manager'  => 'owner',
    'customer'      => 'customer',
];

/**
 * Roles allowed to run direct cancellation + refund from team Mali modal.
 */
function ez_team_direct_cancellation_refund_allowed_roles() {
	return array( 'administrator', 'accounting' );
}

function ez_team_user_can_direct_cancellation_refund( $user_id = 0 ) {
	$user = $user_id ? get_userdata( (int) $user_id ) : wp_get_current_user();
	if ( ! $user || ! $user->ID ) {
		return false;
	}
	return (bool) array_intersect(
		ez_team_direct_cancellation_refund_allowed_roles(),
		(array) $user->roles
	);
}

/**
 * WC statuses eligible for team direct cancellation refund (normalized, no wc- prefix).
 */
function ez_team_direct_cancellation_refund_allowed_order_statuses() {
	return array( 'partially-paid', 'completed-paid', 'walletx', 'processing', 'completed' );
}

/**
 * Team (admin/accounting): register cancellation + refund in one step (post-session accounting).
 *
 * @param int         $order_id
 * @param string      $requester_type customer|owner — who is attributed for satisfaction
 * @param int         $actor_id       logged-in team user
 * @param int|null    $reason_id      required when requester_type is owner
 * @return true|WP_Error
 */
function ez_team_direct_cancellation_refund( $order_id, $requester_type, $actor_id, $reason_id = null ) {
	global $cancellation_reasons, $role_map;

	$order_id       = (int) $order_id;
	$actor_id       = (int) $actor_id;
	$requester_type = sanitize_key( (string) $requester_type );

	if ( $order_id < 1 || $actor_id < 1 ) {
		return new WP_Error( 'invalid_input', 'ورودی‌های نامعتبر.' );
	}

	if ( ! ez_team_user_can_direct_cancellation_refund( $actor_id ) ) {
		return new WP_Error( 'forbidden', 'شما اجازه این عملیات را ندارید.' );
	}

	if ( ! in_array( $requester_type, array( 'customer', 'owner' ), true ) ) {
		return new WP_Error( 'invalid_side', 'طرف کنسلی نامعتبر است.' );
	}

	if ( $requester_type === 'owner' && ( ! $reason_id || ! isset( $cancellation_reasons[ $reason_id ] ) ) ) {
		return new WP_Error( 'invalid_reason', 'دلیل کنسلی مجموعه را انتخاب کنید.' );
	}

	$lock_key = '_ez_direct_cancellation_refund_lock';
	$lock_ts  = (int) get_post_meta( $order_id, $lock_key, true );
	if ( $lock_ts && ( time() - $lock_ts ) < 45 ) {
		return new WP_Error( 'locked', 'این عملیات همین حالا در حال انجام است؛ چند ثانیه بعد دوباره تلاش کنید.' );
	}
	update_post_meta( $order_id, $lock_key, time() );

	try {
		$medoo = ez_team_cancellation_medoo();
		if ( ! $medoo ) {
			return new WP_Error( 'db', 'خطا در اتصال به دیتابیس.' );
		}

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', 'سفارش وجود ندارد.' );
		}

		$st = $order->get_status();
		if ( strpos( $st, 'wc-' ) === 0 ) {
			$st = substr( $st, 3 );
		}

		if ( in_array( $st, array( 'refunded', 'trash', 'cancelled', 'failed' ), true ) ) {
			return new WP_Error( 'invalid_status', 'این سفارش قبلاً مسترد یا لغو شده است.' );
		}

		if ( ! in_array( $st, ez_team_direct_cancellation_refund_allowed_order_statuses(), true ) ) {
			return new WP_Error( 'invalid_status', 'وضعیت سفارش برای ثبت کنسلی و استرداد مجاز نیست.' );
		}

		$pending = $medoo->get( 'cancellation_requests', 'ID', array(
			'order_id' => $order_id,
			'status'   => 'pending',
		) );
		if ( $pending ) {
			return new WP_Error( 'pending_request', 'برای این سفارش درخواست کنسلی در انتظار وجود دارد. ابتدا از صفحه درخواست‌ها رسیدگی کنید.' );
		}

		$product_id = 0;
		foreach ( $order->get_items() as $item ) {
			$product_id = (int) $item->get_product_id();
			break;
		}
		if ( $product_id < 1 ) {
			return new WP_Error( 'invalid_product', 'محصول سفارش یافت نشد.' );
		}

		$oid = (int) $order_id;
		$sans_rows = json_decode(
			ez_reservation(
				array(
					'type' => 'query_execution',
					'data' => array( 'query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$oid} ORDER BY `booking_id` DESC LIMIT 1" ),
				)
			),
			true
		);
		$sans = is_array( $sans_rows ) && ! empty( $sans_rows[0] ) ? $sans_rows[0] : null;
		if ( ! $sans ) {
			return new WP_Error( 'invalid_order', 'سانس نامعتبر است یا قبلاً لغو شده.' );
		}

		$actor = get_userdata( $actor_id );
		if ( ! $actor ) {
			return new WP_Error( 'invalid_user', 'کاربر پیدا نشد.' );
		}

		$mapped_roles = array_intersect_key( $role_map, array_flip( (array) $actor->roles ) );
		if ( empty( $mapped_roles ) ) {
			return new WP_Error( 'invalid_role', 'نقش کاربر نامعتبر است.' );
		}
		$actor_role = reset( $mapped_roles );

		$now = time();

		$request_data = array(
			'order_id'       => $order_id,
			'product_id'     => $product_id,
			'requester_id'   => $actor_id,
			'requester_role' => $actor_role,
			'requester_type' => $requester_type,
			'status'         => 'approved',
			'sans_time'      => $sans['booking_time'],
			'created_at'     => $now,
			'updated_at'     => $now,
		);
		if ( $requester_type === 'owner' ) {
			$request_data['reason_id'] = (int) $reason_id;
		}

		$medoo->insert( 'cancellation_requests', $request_data );
		$request_id = (int) $medoo->id();

		$medoo->insert(
			'cancellation_log',
			array(
				'request_id'  => $request_id,
				'product_id'  => $product_id,
				'user_id'     => $actor_id,
				'user_role'   => $actor_role,
				'action'      => 'team_direct_refund',
				'action_time' => $now,
			)
		);

		$side_label = $requester_type === 'owner' ? 'مجموعه' : 'پلیر';
		$note       = sprintf(
			'کنسلی از طرف %s — ثبت و استرداد توسط %s (درخواست #%d)',
			$side_label,
			$actor->user_login,
			$request_id
		);

		$order->update_status( 'refunded', $note );

		if ( function_exists( 'ez_order_satisfaction_on_cancellation_refund_approved' ) ) {
			ez_order_satisfaction_on_cancellation_refund_approved(
				$order_id,
				$requester_type,
				array(
					'request_id'         => $request_id,
					'request_created_at' => (string) $now,
					'source'             => 'team_direct_refund',
				)
			);
		}

		$order->add_order_note( $note );

		return true;
	} finally {
		delete_post_meta( $order_id, $lock_key );
	}
}

/**
 * Reverse owner wallet deposit for wc-walletx orders (idempotent).
 *
 * @return true|WP_Error
 */
function ez_team_master_refund_reverse_owner_walletx( $order_id, $owner_id, $game_name ) {
	global $wldb;

	$order_id   = (int) $order_id;
	$owner_id   = (int) $owner_id;
	$game_name  = trim( (string) $game_name );

	if ( $order_id < 1 || $owner_id < 1 || $game_name === '' ) {
		return true;
	}

	if ( ! isset( $wldb ) || ! is_object( $wldb ) || ! method_exists( $wldb, 'get_balance' ) ) {
		if ( class_exists( 'EZ_Transaction_CRUD' ) ) {
			$wldb = new EZ_Transaction_CRUD();
		}
	}
	if ( ! isset( $wldb ) || ! is_object( $wldb ) || ! method_exists( $wldb, 'insert' ) ) {
		return new WP_Error( 'wallet', 'سیستم کیف پول در دسترس نیست.' );
	}

	$owner_description     = 'فروش تیکت بازی ' . $game_name . ' - سفارش: ' . $order_id;
	$reversal_description  = 'کنسلی ' . $owner_description;

	$medoo = function_exists( 'medoo' ) ? medoo() : null;
	if ( $medoo ) {
		$existing_reversal = $medoo->get( 'wallet_transactions', 'ID', array( 'description' => $reversal_description ) );
		if ( $existing_reversal ) {
			return true;
		}
		$original_rows = $medoo->select( 'wallet_transactions', array( 'amount', 'balance' ), array(
			'description' => $owner_description,
			'user_id'     => $owner_id,
			'ORDER'       => array( 'ID' => 'DESC' ),
			'LIMIT'       => 1,
		) );
	} else {
		$original_rows = array();
	}

	$owner_amount = 0;
	if ( ! empty( $original_rows[0]['amount'] ) ) {
		$owner_amount = (int) $original_rows[0]['amount'];
	}

	if ( $owner_amount <= 0 ) {
		return true;
	}

	$current_balance = (float) $wldb->get_balance( $owner_id );
	$reversal_amount = -1 * $owner_amount;
	$new_balance     = $current_balance + $reversal_amount;

	$wldb->insert(
		array(
			'user_id'     => $owner_id,
			'amount'      => $reversal_amount,
			'balance'     => $new_balance,
			'description' => $reversal_description,
			'type'        => 'transaction',
		)
	);

	return true;
}

/**
 * Team master refund: open sans if booked, refund player wallet, reverse owner walletx deposit — no SMS.
 *
 * @param int $order_id
 * @param int $actor_id
 * @return true|WP_Error
 */
function ez_team_master_refund( $order_id, $actor_id ) {
	$order_id = (int) $order_id;
	$actor_id = (int) $actor_id;

	if ( $order_id < 1 || $actor_id < 1 ) {
		return new WP_Error( 'invalid_input', 'ورودی‌های نامعتبر.' );
	}

	if ( ! ez_team_user_can_direct_cancellation_refund( $actor_id ) ) {
		return new WP_Error( 'forbidden', 'شما اجازه این عملیات را ندارید.' );
	}

	$lock_key = '_ez_team_master_refund_lock';
	$lock_ts  = (int) get_post_meta( $order_id, $lock_key, true );
	if ( $lock_ts && ( time() - $lock_ts ) < 45 ) {
		return new WP_Error( 'locked', 'این عملیات همین حالا در حال انجام است؛ چند ثانیه بعد دوباره تلاش کنید.' );
	}
	update_post_meta( $order_id, $lock_key, time() );

	try {
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return new WP_Error( 'invalid_order', 'سفارش وجود ندارد.' );
		}

		$st = $order->get_status();
		if ( strpos( $st, 'wc-' ) === 0 ) {
			$st = substr( $st, 3 );
		}

		if ( in_array( $st, array( 'refunded', 'trash', 'cancelled', 'failed' ), true ) ) {
			return new WP_Error( 'invalid_status', 'این سفارش قبلاً مسترد یا لغو شده است.' );
		}

		if ( ! in_array( $st, ez_team_direct_cancellation_refund_allowed_order_statuses(), true ) ) {
			return new WP_Error( 'invalid_status', 'وضعیت سفارش برای مسترد مجاز نیست.' );
		}

		$actor = get_userdata( $actor_id );
		if ( ! $actor ) {
			return new WP_Error( 'invalid_user', 'کاربر پیدا نشد.' );
		}

		if ( $st === 'walletx' ) {
			$game_name = '';
			$owner_id  = 0;

			if ( function_exists( 'ez_markting_get_row' ) ) {
				$mrow = ez_markting_get_row( $order_id );
				if ( is_array( $mrow ) ) {
					$game_name = (string) ( $mrow['game_name'] ?? '' );
					$owner_id  = (int) ( $mrow['game_user_ebtal_id'] ?? 0 );
				}
			}
			if ( $game_name === '' || $owner_id < 1 ) {
				$medoo = function_exists( 'medoo' ) ? medoo() : null;
				if ( $medoo ) {
					$mrow = $medoo->get( 'wp_markting', array( 'game_name', 'game_user_ebtal_id' ), array( 'order_id' => $order_id ) );
					if ( is_array( $mrow ) ) {
						$game_name = (string) ( $mrow['game_name'] ?? '' );
						$owner_id  = (int) ( $mrow['game_user_ebtal_id'] ?? 0 );
					}
				}
			}
			if ( $game_name === '' ) {
				foreach ( $order->get_items() as $item ) {
					$pid = (int) $item->get_product_id();
					if ( $pid > 0 ) {
						$game_name = get_the_title( $pid );
						$owner_id  = (int) get_post_meta( $pid, 'user_ebtal', true );
						break;
					}
				}
			}

			$owner_result = ez_team_master_refund_reverse_owner_walletx( $order_id, $owner_id, $game_name );
			if ( is_wp_error( $owner_result ) ) {
				return $owner_result;
			}
		}

		if ( function_exists( 'ez_booking_release_for_order' ) ) {
			ez_booking_release_for_order( $order_id );
		}

		if ( function_exists( 'check_and_update_markting_table' ) ) {
			check_and_update_markting_table( $order_id, false );
		}

		// جلوگیری از ارسال پیامک استرداد در woocommerce_order_status_changed
		update_post_meta( $order_id, 'ez_refund_status_sms_queued_at', time() );

		$note = sprintf( 'مسترد (بدون پیامک) توسط %s', $actor->user_login );
		$order->update_status( 'refunded', $note );
		$order->add_order_note( $note );

		return true;
	} finally {
		delete_post_meta( $order_id, $lock_key );
	}
}

function ez_cancellation_session_ts($sans_booking_time)
{
    return is_numeric($sans_booking_time) ? (int) $sans_booking_time : (int) strtotime((string) $sans_booking_time);
}

function ez_cancellation_owner_deadline_ts($sans_booking_time)
{
    $grace = defined('OWNER_CANCELLATION_GRACE_SECONDS') ? (int) OWNER_CANCELLATION_GRACE_SECONDS : 1800;

    return ez_cancellation_session_ts($sans_booking_time) + $grace;
}

// فانکشن ایجاد درخواست کنسلی
function create_cancellation_request($order_id, $requester_id, $requester_type, $reason_id = null)
{
	global $cancellation_reasons, $role_map;

    $medoo = ez_team_cancellation_medoo();
    if (! $medoo) {
        return new WP_Error('db', 'خطا در اتصال به دیتابیس.');
    }

    $now = time();

    // اعتبارسنجی دلیل کنسلی برای owner
    if ($requester_type === 'owner' && (!isset($reason_id) || !isset($cancellation_reasons[$reason_id])))
        return new WP_Error('invalid_reason', 'دلیل کنسلی نامعتبر است.');

    $order = wc_get_order($order_id);
    if (! $order)
        return new WP_Error('existing_order', 'این سفارش وجود ندارد!');

    foreach ($order->get_items() as $item)
        $product_id = $item->get_product_id();

    // بررسی نقش کاربر
    $user = get_userdata($requester_id);
    if (!$user)
        return new WP_Error('invalid_user', 'کاربر پیدا نشد.');

    // پیدا کردن نقش معادل
    $mapped_roles = array_intersect_key($role_map, array_flip($user->roles));
    if (empty($mapped_roles))
        return new WP_Error('invalid_role', 'نقش کاربر نامعتبر است.');

    $user_role = reset($mapped_roles);

    // بررسی اینکه نقش جزو لیست مجاز هست یا نه
    $allowed_roles = ['admin', 'owner', 'customer'];
    if (!in_array($user_role, $allowed_roles))
        return new WP_Error('invalid_role', 'نقش کاربر مجاز نیست.');

    if ( $user_role != 'admin' ) { // برای ادمین سایت نیازی نیست که مالک سفارش بودن بررسی شود.
        if ($requester_type === 'owner') {
            // هم مجموعه دار و هم مدیرسانس مجاز به درخواست کنسلی هستند

            $owner_id   = get_post_meta($product_id, 'user_ebtal', true);
            $manager_id = get_post_meta($product_id, 'sans_manager', true);
            if (! ($requester_id == $owner_id || $requester_id == $manager_id))
                return new WP_Error('order_not_belong', 'این سفارش به شما تعلق ندارد!');

        } elseif ($requester_type === 'customer')
            if ($order->get_user_id() !== $requester_id) // سفارش دهنده با درخواست کننده باید یکسان باشد.
                return new WP_Error('order_not_belong', 'این سفارش به شما تعلق ندارد!');
    }

    // بررسی وجود درخواست فعال
    $existing_request = $medoo->get('cancellation_requests', '*', [
        'order_id'  => $order_id,
        'status'    => 'pending'
    ]);
    if ($existing_request)
        return new WP_Error('existing_request', 'برای این سانس یک درخواست کنسلی در انتظار وجود دارد.');

    $sans = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id ORDER BY `booking_id` DESC"])), true)[0];
    if (!$sans)
        return new WP_Error('invalid_order', 'سانس نامعتبر است یا قبلاً لغو شده.');

    $session_ts    = ez_cancellation_session_ts($sans['booking_time']);
    $hours_to_sans = ($session_ts - $now) / 3600;

    if ($user_role !== 'admin') {
        if ($requester_type === 'owner') {
            if ($now > ez_cancellation_owner_deadline_ts($sans['booking_time'])) {
                return new WP_Error('invalid_user', 'مهلت ثبت درخواست کنسلی مجموعه (تا ۳۰ دقیقه پس از شروع سانس) به پایان رسیده است.');
            }
        } elseif ($hours_to_sans < TIME_TO_DISABLE_REQUEST) {
            return new WP_Error('invalid_user', 'تا ' . TIME_TO_DISABLE_REQUEST . ' ساعت مانده به سانس اجازه کنسلی داشتید.');
        }
    }

    $user_phone_no  =  ltrim($order->get_billing_phone(), '0');
    $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $english        = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $user_phone_no  = str_replace($persian, $english, $user_phone_no);
    $user_phone_no  = preg_replace('/^\+?98|\|98|\D/', '', ($user_phone_no));
    $player_phone   = ltrim($user_phone_no, '0');
    $owner_id       = get_post_meta($product_id, 'user_ebtal', true);
    $owner_phone    = get_userdata($owner_id)->user_login;
    $sans_time      = get_post_meta($order_id, 'sans_time', true);
    $product_title  = get_the_title($product_id);
    $order_date     = jdate('j F', $sans_time);
    $order_time     = jdate('H:i', $sans_time);
//    $sans_time_txt  = "$order_date\، ساعت $order_time";
    $sans_time_txt  = "$order_time روز $order_date";
    $player_fname   = $order->get_billing_first_name();
    $player_lname   = $order->get_billing_last_name();
    $player_fullname = $player_fname.' '.$player_lname;

    $sms_list = [];
    if ($requester_type == 'customer') {

        $sms_list[] = [
            'phone' => $player_phone,
            'text'  => "$player_fname;$product_title;$sans_time_txt",
            'token' => 434807
        ];

        if ($hours_to_sans < CRISIS_TIME)
            $sms_list[] = [
                'phone' => $owner_phone,
                'text'  => "$player_fullname;$product_title;$sans_time_txt",
                'token' => 434808
            ];

    } elseif ($requester_type == 'owner')
        $sms_list[] = [
            'phone' => $player_phone,
            'text'  => "$player_fname;$product_title;$sans_time_txt",
            'token' => 434809
        ];

    $cancellation_create_claim_key = 'ez_cancellation_create_sms_' . (int) $order_id . '_' . sanitize_key( (string) $requester_type );
    $send_create_cancellation_sms    = function_exists( 'ez_order_meta_claim_once' )
        && ez_order_meta_claim_once( (int) $order_id, $cancellation_create_claim_key );

    if ( $send_create_cancellation_sms ) {
        foreach ( $sms_list as $sms ) {
            add_to_sms_queue( $sms['token'], $sms['phone'], $sms['text'], $order_id, 'cancellation_request' );
        }
    }

    // داده‌های درخواست
    $data = [
        'order_id'          => $order_id,
        'product_id'        => $product_id,
        'requester_id'      => $requester_id,
        'requester_role'    => $user_role,
        'requester_type'    => $requester_type,
        'status'            => 'pending',
        'sans_time'         => $sans['booking_time'],
        'created_at'        => $now,
        'updated_at'        => $now
    ];
    if ($requester_type === 'owner' && $reason_id) // دلیل کنسلی مخصوص مجموعه دار هست.
        $data['reason_id'] = $reason_id;

    // ثبت درخواست
    $medoo->insert('cancellation_requests', $data);
    $request_id = $medoo->id();

    // ثبت لاگ create
    $medoo->insert('cancellation_log', [
        'request_id'    => $request_id,
        'product_id'    => $product_id,
        'user_id'       => $requester_id,
        'user_role'     => $user_role,
        'action'        => 'create',
        'action_time'   => $now
    ]);

    return $request_id;
}

// فانکشن رد یا درخواست دستی کنسلی
function process_cancellation_request($request_id, $user_id, $action)
{
    global $role_map;

    $medoo = ez_team_cancellation_medoo();
    if (! $medoo) {
        return new WP_Error('db', 'خطا در اتصال به دیتابیس.');
    }

    $now = time();

    // اعتبارسنجی اکشن
    if (!in_array($action, ['approve', 'reject']))
        return new WP_Error('invalid_action', 'اکشن نامعتبر است.');

    // گرفتن درخواست کنسلی
    $request = $medoo->get('cancellation_requests', '*', [
        'ID'        => $request_id,
        'status'    => 'pending'
    ]);
    if (!$request)
        return new WP_Error('invalid_request', 'درخواست نامعتبر است یا قبلاً پردازش شده.');

    $product_id = $request['product_id'];
    $order_id   = $request['order_id'];

    $under_24 = true;
    if (($request['sans_time'] - $request['created_at']) / 3600 > CRISIS_TIME)
        $under_24 = false;

    // گرفتن اطلاعات سفارش و محصول
    $order = wc_get_order($order_id);
    if (!$order)
        return new WP_Error('invalid_order', 'سفارش وجود ندارد.');

    // گرفتن اطلاعات سانس
    $sans = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$order_id} ORDER BY `booking_id` DESC"])), true)[0];
    if (!$sans)
        return new WP_Error('invalid_sans', 'سانس نامعتبر است.');

    // گرفتن اطلاعات کاربر پردازش‌کننده
    $user = get_userdata($user_id);
    if (!$user)
        return new WP_Error('invalid_user', 'کاربر پیدا نشد.');

    $mapped_roles = array_intersect_key($role_map, array_flip($user->roles));
    if (empty($mapped_roles))
        return new WP_Error('invalid_role', 'نقش کاربر نامعتبر است.');

    $user_role = reset($mapped_roles);

    // بررسی اینکه نقش جزو لیست مجاز هست یا نه
    $allowed_roles = ['admin', 'owner'];
    if (!in_array($user_role, $allowed_roles))
        return new WP_Error('invalid_role', 'نقش کاربر مجاز نیست.');

    /*
    پلیر که هرگز حق تغییر وضعییت درخواست کنسلی را ندارد.
    ادمین سایت هم هیچ محدودیتی ندارد پس شرطی براش نمیزاریم.
    مجموعه دار هم تنها در شرایطی حق تغییر وضعیت یک درخواست کنسلی را دارد
    */
    if ($user_role === 'owner') {
        if ($request['requester_type'] === 'customer') { // مجموعه دار فقط میتواند درخواست هایی که از سمت پلیر درخواست شده را مدیریت کند.

            if (!$under_24) // مجموعه دار برای درخواست های بالای 24 ساعت کنترلی ندارد.
                return new WP_Error('invalid_role', 'فقط ادمین می‌تواند درخواست پلیر با بیش از 24 ساعت را پردازش کند.');

            $owner_id   = get_post_meta($product_id, 'user_ebtal', true);
            $manager_id = get_post_meta($product_id, 'sans_manager', true);
            if (!($user_id == $owner_id || $user_id == $manager_id))
                return new WP_Error('invalid_role', 'فقط مجموعه‌دار می‌تواند درخواست پلیر با کمتر از 24 ساعت را پردازش کند.');
        } else
            return new WP_Error('invalid_role', 'فقط ادمین می‌تواند درخواست مجموعه‌دار را پردازش کند.');
    }

    // آپدیت وضعیت سفارش (فقط برای approve)
    if ($action === 'approve') {
        $order->update_status('refunded');

        if ( function_exists( 'ez_order_satisfaction_on_cancellation_refund_approved' ) ) {
            ez_order_satisfaction_on_cancellation_refund_approved(
                (int) $order_id,
                (string) $request['requester_type'],
                array(
                    'request_id' => (int) $request['ID'],
                    'request_created_at' => (string) $request['created_at'],
                )
            );
        }

    } else { // اینجا تنها قسمتی از سیستم هست که رد درخواست داریم. تنها رولی که میتونه یک درخواست رو رد کنه مجموعه داره.

        $user_phone_no  =  ltrim($order->get_billing_phone(), '0');
        $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english        = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $user_phone_no  = str_replace($persian, $english, $user_phone_no);
        $user_phone_no  = preg_replace('/^\+?98|\|98|\D/', '', ($user_phone_no));
        $player_phone   = ltrim($user_phone_no, '0');
        $sans_time      = get_post_meta($order_id, 'sans_time', true);
        $product_title  = get_the_title($product_id);
        $order_date     = jdate('j F', $sans_time);
        $order_time     = jdate('H:i', $sans_time);
        $sans_time_txt  = "$order_time روز $order_date";
        $player_fname   = $order->get_billing_first_name();
        $text           = "$player_fname;$product_title;$sans_time_txt";

        $reject_sms_claim_key = 'ez_cancellation_reject_sms_' . (int) $request_id;
        if (
            function_exists( 'ez_order_meta_claim_once' )
            && ez_order_meta_claim_once( (int) $order_id, $reject_sms_claim_key )
        ) {
            add_to_sms_queue( 434811, $player_phone, $text, $order_id, 'cancellation_request' );
        }
    }

    // آپدیت درخواست
    $medoo->update('cancellation_requests', [
        'status'        => $action === 'approve' ? 'approved' : 'rejected',
        'updated_at'    => $now
    ], ['ID' => $request_id]);

    // ثبت لاگ
    $medoo->insert('cancellation_log', [
        'request_id'    => $request_id,
        'product_id'    => $product_id,
        'user_id'       => $user_id,
        'user_role'     => $user_role,
        'action'        => $action,
        'action_time'   => $now
    ]);

    return true;
}

// فانکشن لغو درخواست
function cancel_cancellation_request($request_id, $user_id)
{
    global $cancellation_reasons, $role_map;

    $medoo = ez_team_cancellation_medoo();
    if (! $medoo) {
        return new WP_Error('db', 'خطا در اتصال به دیتابیس.');
    }

    $now = time();

    // گرفتن درخواست کنسلی
    $request = $medoo->get('cancellation_requests', '*', [
        'ID'        => $request_id,
        'status'    => 'pending'
    ]);
    if (!$request)
        return new WP_Error('invalid_request', 'درخواست نامعتبر است یا قبلاً پردازش شده.');

    // گرفتن اطلاعات سفارش و محصول
    $order = wc_get_order($request['order_id']);
    if (!$order)
        return new WP_Error('invalid_order', 'سفارش وجود ندارد.');

    // گرفتن اطلاعات سانس
    $sans = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$request['order_id']} ORDER BY `booking_id` DESC"])), true)[0];
    if (!$sans)
        return new WP_Error('invalid_sans', 'سانس نامعتبر است.');

    // گرفتن اطلاعات کاربر لغوکننده
    $user = get_userdata($user_id);
    if (!$user)
        return new WP_Error('invalid_user', 'کاربر پیدا نشد.');

    $mapped_roles = array_intersect_key($role_map, array_flip($user->roles));
    if (empty($mapped_roles))
        return new WP_Error('invalid_role', 'نقش کاربر نامعتبر است.');

    $user_role = reset($mapped_roles);

    if ($request['requester_type'] === 'owner') {
        if ($now > ez_cancellation_owner_deadline_ts($sans['booking_time'])) {
            return new WP_Error('invalid_time', 'مهلت لغو این درخواست کنسلی مجموعه به پایان رسیده است.');
        }
    } elseif ((ez_cancellation_session_ts($sans['booking_time']) - $now) / 3600 <= 0) {
        return new WP_Error('invalid_time', 'سانس در حال برگزاری میباشد و دیگر امکان اکشن جدیدی نیست.');
    }

    if ($user_role !== 'admin')
        if ($user_id != $request['requester_id'])  // آی دی کنسل کننده درخواست لغو، باید با آی دی درخواست دهنده یکی باشد.
            return new WP_Error('invalid_time', 'شما این درخواست را ایجاد نکردید، بنابراین قادر به لغو آن نیز نیستید.');

    // آپدیت درخواست به وضعیت cancelled
    $medoo->update('cancellation_requests', [
        'status'        => 'cancelled',
        'updated_at'    => $now
    ], ['ID' => $request_id]);

    // ثبت لاگ cancel
    $medoo->insert('cancellation_log', [
        'request_id'    => $request_id,
        'product_id'    => $request['product_id'],
        'user_id'       => $user_id,
        'user_role'     => $user_role,
        'action'        => 'cancel',
        'action_time'   => $now
    ]);

    return true;
}

function cancellation_reasons()
{
    return [
        1 => 'تداخل سانس',
        2 => 'نقص فنی غیرمنتظره',
        3 => 'تعطیل بودن مجموعه',
        4 => 'قطعی برق',
        5 => 'عدم تکمیل کادر پرسنل',
        6 => 'سایر',
    ];
}

function cron_job_process_pending_cancellations()
{
    $medoo = ez_team_cancellation_medoo();
    if (! $medoo) {
        return;
    }

    $now = time();
    $requests = $medoo->select('cancellation_requests', [
        'ID',
        'order_id',
        'requester_type',
        'sans_time',
        'created_at'
    ], [
        'status' => 'pending'
    ]);

    if (empty($requests)) {
        return;
    }

    foreach ($requests as $request) {
        $request_time = strtotime($request['created_at']);
        $session_time = strtotime($request['sans_time']);
        $time_to_session = ($session_time - $request_time) / 3600;

        $update_data = [
            'updated_at'       => date('Y-m-d H:i:s', $now),
            'auto_processed_at' => date('Y-m-d H:i:s', $now)
        ];

        if ($request['requester_type'] === 'customer') {
            if ($time_to_session > CRISIS_TIME) {
                // بالای 24 ساعت: بعد از 2 ساعت تأیید خودکار
                if (($now - $request_time) > 2 * 3600) {
                    $update_data['status'] = 'expired';
                    $update_data['auto_status'] = 'approved';
                    $medoo->update('cancellation_requests', $update_data, ['ID' => $request['ID']]);
                    $medoo->update('wp_wc_orders', ['status' => 'cancelled'], ['id' => $request['order_id']]);
                    $medoo->insert('cancellation_log', [
                        'request_id'   => $request['ID'],
                        'user_id'      => 0,
                        'user_role'    => 'system',
                        'action'       => 'auto_approve',
                        'action_time'  => date('Y-m-d H:i:s', $now)
                    ]);
                    if ( function_exists( 'ez_order_satisfaction_on_cancellation_refund_approved' ) ) {
                        ez_order_satisfaction_on_cancellation_refund_approved(
                            (int) $request['order_id'],
                            'customer',
                            array(
                                'request_id' => (int) $request['ID'],
                                'request_created_at' => (string) $request['created_at'],
                            )
                        );
                    }
                }
            } else {
                // زیر 24 ساعت: اگر 3 ساعت تا سانس مونده، رد خودکار
                if (($session_time - $now) <= 3 * 3600) {
                    $update_data['status'] = 'expired';
                    $update_data['auto_status'] = 'rejected';
                    $medoo->update('cancellation_requests', $update_data, ['ID' => $request['ID']]);
                    $medoo->insert('cancellation_log', [
                        'request_id'   => $request['ID'],
                        'user_id'      => 0,
                        'user_role'    => 'system',
                        'action'       => 'auto_reject',
                        'action_time'  => date('Y-m-d H:i:s', $now)
                    ]);
                }
            }
        }
        // درخواست owner: بدون تأیید خودکار — فقط ادمین (یا مسیرهای دیگر) رسیدگی می‌کند.
    }
}
