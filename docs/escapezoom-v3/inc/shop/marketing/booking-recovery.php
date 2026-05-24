<?php
/**
 * Manual booking-recovery helpers used by the team panel's "بررسی سانس" button.
 *
 * Three responsibilities:
 *   1. Decide whether a wp_markting row is eligible for manual recovery
 *      (ez_markting_row_eligible_for_booking_recovery).
 *   2. Compute the wallet refund amount on a true conflict, mirroring the
 *      thankyou-pipeline formula (prepaid minus coupon and level discounts)
 *      (ez_team_conflict_refund_amount_for_order).
 *   3. Sync wp_markting + WooCommerce + wallet when the order is conflict, or
 *      drive the booking insertion when it is genuinely stuck
 *      (ez_team_recover_conflict_wallet_marketing_finalize + ez_team_recover_booking_sans_run).
 *
 * The HTTP-facing wp-ajax handler lives in
 * inc/admin/team/ajax/callbacks/orders_actions.php under the `recover_booking_sans`
 * operation, which delegates to ez_team_recover_booking_sans_run().
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_markting_row_eligible_for_booking_recovery' ) ) {
	/**
	 * آیا این ردیف مارکتینگ برای دکمه‌ی دستی «بررسی سانس» تیم مجاز است؟
	 *
	 * شرایط:
	 *   — processing با order_created_at معتبر و حداقل ۱۰ دقیقه از آن گذشته
	 *   — یا پیش‌پرداخت / پرداخت کامل بدون سانس کامل در مارکتینگ.
	 *
	 * @param array $row از wp_markting (must contain order_status, order_created_at, order_sans_*).
	 * @return bool
	 */
	function ez_markting_row_eligible_for_booking_recovery( array $row ) {
		$st = isset( $row['order_status'] ) ? (string) $row['order_status'] : '';
		if ( strpos( $st, 'wc-' ) === 0 ) {
			$st = substr( $st, 3 );
		}
		if ( in_array( $st, array( 'cancelled', 'refunded', 'conflict', 'failed', 'trash', 'on-hold', 'draft' ), true ) ) {
			return false;
		}
		$created_raw = isset( $row['order_created_at'] ) ? (string) $row['order_created_at'] : '';
		$created_at  = $created_raw !== '' ? strtotime( $created_raw ) : false;
		$no_sans     = empty( $row['order_sans_time'] ) || empty( $row['order_sans_date'] ) || empty( $row['order_sans_day'] );

		if ( $st === 'processing' && $created_at && ( time() - (int) $created_at ) >= 600 ) {
			return true;
		}
		if ( in_array( $st, array( 'partially-paid', 'completed-paid' ), true ) && $no_sans ) {
			return true;
		}

		return false;
	}
}

if ( ! function_exists( 'ez_team_conflict_refund_amount_for_order' ) ) {
	/**
	 * مبلغ عودت تداخل (هم‌راستا با منطق pipeline: prepaid منهای کوپن و تخفیف سطح).
	 *
	 * @param WC_Order $order
	 * @return int|null مبلغ به تومان یا null اگر نامعتبر.
	 */
	function ez_team_conflict_refund_amount_for_order( WC_Order $order ) {
		if ( ! $order ) {
			return null;
		}
		$order_id = $order->get_id();
		$prepaid  = (int) get_post_meta( $order_id, 'prepaid', true );
		if ( $prepaid <= 0 ) {
			$prepaid = (int) ( get_post_meta( $order_id, '_order_total_2', true ) ?: round( floatval( $order->get_total() ) ) );
		}
		if ( $prepaid <= 0 ) {
			return null;
		}

		$item_total_approx = null;
		foreach ( $order->get_items() as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$item_total_approx = max( $item_total_approx, (float) $item->get_total() + (float) $item->get_total_tax() );
		}
		if ( $item_total_approx === null || $item_total_approx <= 0 ) {
			$item_total_approx = (float) $prepaid;
		}

		$coupon_amount = 0.0;
		foreach ( $order->get_coupon_codes() as $code ) {
			if ( function_exists( 'ez_get_coupon_discount_amount' ) ) {
				$coupon_amount += ez_get_coupon_discount_amount( $code, $item_total_approx );
			}
		}

		$user_level_discount = 0.0;
		$user_id             = (int) $order->get_customer_id();
		if ( $user_id && function_exists( 'get_user_discount' ) && in_array( $user_id, array( 3325, 2, 80 ), true ) ) {
			$discount            = get_user_discount( $order_id, $user_id );
			$pct                 = isset( $discount['percentage'] ) ? (float) $discount['percentage'] : 0.0;
			$user_level_discount = $item_total_approx * $pct / 100;
		}

		$amt = $prepaid - (int) round( $coupon_amount + $user_level_discount );

		return max( 0, (int) $amt );
	}
}

if ( ! function_exists( 'ez_team_recover_conflict_wallet_marketing_finalize' ) ) {
	/**
	 * عودت تداخل به کیف پول (در صورت امکان)، ثبت wc-conflict در مارکتینگ و finalize بوکینگ.
	 * هر دو مسیر «تشخیص تداخل روی سانس» و «ووکامرس از قبل conflict ولی مارکتینگ کهنه» از این استفاده می‌کنند.
	 *
	 * @param WC_Order    $order
	 * @param object      $medoo                   Medoo handle for primary DB (wp_markting writes).
	 * @param int         $actor_id                ID of staff user (for status-change log).
	 * @param bool        $slot_recently_blocked   true وقتی الان اسلات اشغال است (پیام بازخورد متفاوت).
	 * @return array{success:bool, code:string, message:string}
	 */
	function ez_team_recover_conflict_wallet_marketing_finalize( WC_Order $order, $medoo, int $actor_id, bool $slot_recently_blocked = false ) {
		global $wldb;

		$result = array(
			'success' => false,
			'code'    => 'error',
			'message' => 'خطای ناشناخته.',
		);

		$order_id  = $order->get_id();
		$wc_before = (string) $order->get_status();
		$user_id   = (int) $order->get_customer_id();

		$guest = $user_id <= 0;
		$svc   = isset( $wldb ) && is_object( $wldb ) && method_exists( $wldb, 'get_balance' );

		$refund_amt = function_exists( 'ez_team_conflict_refund_amount_for_order' )
			? ez_team_conflict_refund_amount_for_order( $order )
			: null;

		$wallet_credited = false;
		if ( ! $guest && $svc && $refund_amt !== null && $refund_amt > 0 ) {
			$step    = 'refund_conflict_team_once';
			$already = function_exists( 'ez_wallet_step_is_done' )
				&& ( ez_wallet_step_is_done( $order_id, 'refund_conflict_once' )
					|| ez_wallet_step_is_done( $order_id, $step ) );

			if ( ! $already ) {
				$current_balance = (float) $wldb->get_balance( $user_id );
				$balance         = $current_balance + $refund_amt;
				$description     = 'برگشت مبلغ - تداخل (بررسی سانس تیم) - سفارش: ' . $order_id;
				$transaction_row = array(
					'user_id'     => $user_id,
					'amount'      => $refund_amt,
					'balance'     => $balance,
					'description' => $description,
					'type'        => 'transaction',
				);
				$wldb->insert( $transaction_row );
				if ( function_exists( 'ez_wallet_step_mark_done' ) ) {
					ez_wallet_step_mark_done( $order_id, $step );
				}
				$wallet_credited = true;
			}
		}

		if ( ! $order->has_status( 'conflict' ) ) {
			$order->update_status(
				'conflict',
				$guest
					? 'همگام‌سازی تداخل (بررسی سانس تیم): مارکتینگ؛ مشتری مهمان — بدون ولت خودکار.'
					: 'تداخل سانس (بررسی سانس تیم): همگام‌سازی مارکتینگ و عودت به کیف در صورت امکان.'
			);
		}

		$medoo->update(
			'wp_markting',
			array( 'order_status' => standardize_order_status( 'conflict' ) ),
			array( 'order_id' => $order_id )
		);
		if ( function_exists( 'ez_booking_pipeline_finalize' ) ) {
			ez_booking_pipeline_finalize( $order_id, 'conflict' );
		}
		if ( function_exists( 'log_order_status_change' ) && $actor_id > 0 ) {
			$norm_before = standardize_order_status( $wc_before );
			$norm_cf     = standardize_order_status( 'conflict' );
			if ( $norm_before !== $norm_cf ) {
				log_order_status_change( $order_id, $wc_before, 'wc-conflict', 'ez_team_recover_conflict_wallet_marketing_finalize', $actor_id );
			}
		}

		$result['success'] = true;
		$result['code']    = 'conflict';

		$prefix = $slot_recently_blocked ? 'سانس الان توسط سفارش دیگری اشغال است؛ ' : '';

		if ( $wallet_credited ) {
			$result['message'] = $prefix . sprintf(
				'مارکتینگ به «تداخل» همگام شد و %s تومان به کیف پول مشتری عودت داده شد.',
				number_format( (int) $refund_amt )
			);

			return $result;
		}
		if ( $guest ) {
			$result['message'] = $prefix . 'ووکامرس یا اسلات در وضعیت تداخل؛ مارکتینگ به «تداخل» همگام شد. مشتری مهمان است — عودت کیف پول را دستی انجام دهید.';
			return $result;
		}
		if ( ! $svc && $refund_amt !== null && $refund_amt > 0 ) {
			$result['message'] = $prefix . 'مارکتینگ به «تداخل» همگام شد؛ سرویس کیف پول در دسترس نبود — عودت را دستی انجام دهید.';
			return $result;
		}
		if ( $refund_amt !== null && $refund_amt > 0 ) {
			$result['message'] = $prefix . sprintf(
				'مارکتینگ به «تداخل» همگام شد؛ عودت کیف قبلاً ثبت شده بود (%s تومان).',
				number_format( $refund_amt )
			);
			return $result;
		}
		$result['message'] = $prefix . 'مارکتینگ به «تداخل» همگام شد؛ مبلغ قابل اعتمادی برای عودت محاسبه نشد.';
		return $result;
	}
}

if ( ! function_exists( 'ez_team_recover_booking_sans_run' ) ) {
	/**
	 * بازیابی دستی سانس برای سفارش‌های گیرکرده از دید مارکتینگ/بوکینگ (پنل تیم).
	 *
	 * @param int $order_id   شماره‌ی سفارش WC.
	 * @param int $actor_id   کاربر همکار (برای لاگ یادداشت).
	 * @return array{success:bool, code:string, message:string}
	 */
	function ez_team_recover_booking_sans_run( int $order_id, int $actor_id = 0 ) {

		$result = array(
			'success' => false,
			'code'    => 'error',
			'message' => 'خطای ناشناخته.',
		);

		$order_id = (int) $order_id;
		if ( $order_id <= 0 ) {
			$result['message'] = 'شماره سفارش نامعتبر است.';

			return $result;
		}

		$lock_key = '_ez_team_recover_booking_lock';
		$lock_ts  = (int) get_post_meta( $order_id, $lock_key, true );
		if ( $lock_ts && ( time() - $lock_ts ) < 30 ) {
			$result['code']    = 'locked';
			$result['message'] = 'این عملیات برای این سفارش در حال اجراست؛ چند ثانیه بعد دوباره تلاش کنید.';

			return $result;
		}
		update_post_meta( $order_id, $lock_key, time() );

		try {
			$medoo = function_exists( 'medoo' ) ? medoo() : null;
			if ( ! $medoo ) {
				$result['message'] = 'اتصال به دیتابیس مارکتینگ برقرار نیست.';

				return $result;
			}

			$mrow = $medoo->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );
			if ( empty( $mrow['order_id'] ) ) {
				$result['code']    = 'no_markting';
				$result['message'] = 'سفارش در wp_markting یافت نشد.';

				return $result;
			}

			$order = wc_get_order( $order_id );
			if ( ! $order ) {
				$result['message'] = 'سفارش ووکامرس یافت نشد.';

				return $result;
			}

			// --- ووکامرس از قبل conflict ولی مارکتینگ مانده پیش‌پرداخت/پردازش (یا ولت مانده) ---
			if ( $order->has_status( 'conflict' ) ) {
				return ez_team_recover_conflict_wallet_marketing_finalize( $order, $medoo, $actor_id, false );
			}

			if ( ! function_exists( 'ez_markting_row_eligible_for_booking_recovery' ) || ! ez_markting_row_eligible_for_booking_recovery( $mrow ) ) {
				$result['code']    = 'ineligible';
				$result['message'] = 'این سفارش با قوانین «بررسی سانس» پنل سازگار نیست.';

				return $result;
			}

			$wc_st = (string) $order->get_status();
			if ( ! in_array( $wc_st, array( 'processing', 'partially-paid', 'completed-paid' ), true ) ) {
				$result['code']    = 'bad_wc_status';
				$result['message'] = 'وضعیت سفارش در ووکامرس برای این اقدام مجاز نیست.';

				return $result;
			}

			$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
			if ( $sans_time <= 0 ) {
				$result['code']    = 'no_sans_meta';
				$result['message'] = 'در متای سفارش (sans_time) زمان سانس ثبت نشده است.';

				return $result;
			}

			if ( ! function_exists( 'ez_order_primary_bookable_line_item' ) ) {
				$result['message'] = 'تابع ez_order_primary_bookable_line_item در دسترس نیست.';

				return $result;
			}

			list( $product_id, $qty ) = ez_order_primary_bookable_line_item( $order );
			$product_id = $product_id ? (int) $product_id : null;
			$qty        = max( 1, (int) $qty );
			if ( ! $product_id ) {
				$result['message'] = 'محصول قابل رزرو در سفارش یافت نشد.';

				return $result;
			}

			$game_id = isset( $mrow['game_id'] ) ? (int) $mrow['game_id'] : 0;
			if ( $game_id > 0 && $game_id !== $product_id ) {
				$result['code']    = 'game_mismatch';
				$result['message'] = 'شناسه بازی در مارکتینگ با آیتم سفارش هم‌خوان نیست؛ ابتدا مارکتینگ را اصلاح کنید.';

				return $result;
			}

			$mq = function_exists( 'medoo_queries' ) ? medoo_queries() : null;
			if ( ! $mq || ! method_exists( $mq, 'has' ) ) {
				$result['message'] = 'اتصال به دیتابیس booking در دسترس نیست.';

				return $result;
			}

			if ( function_exists( 'ez_booking_exists_for_order' ) && ez_booking_exists_for_order( $order_id ) ) {
				if ( function_exists( 'check_and_update_markting_table' ) ) {
					check_and_update_markting_table( $order_id, true );
				}
				if ( $order->has_status( 'processing' ) ) {
					$ez_pt  = (string) get_post_meta( $order_id, 'ez_payment_type', true );
					$target = ( $ez_pt === 'complete' ) ? 'completed-paid' : 'partially-paid';
					$note   = 'همگام‌سازی پس از تأیید وجود بوکینگ (بررسی سانس تیم).';
					$order->update_status( $target, $note );
					$medoo->update(
						'wp_markting',
						array( 'order_status' => standardize_order_status( $target ) ),
						array( 'order_id' => $order_id )
					);
				}
				$result['success'] = true;
				$result['code']    = 'already_booked';
				$result['message'] = 'برای این سفارش قبلاً در wp_zb_booking_history رزرو ثبت شده بود؛ فیلدهای سانس مارکتینگ به‌روز شد و در صورت نیاز وضعیت پردازش اصلاح شد.';

				return $result;
			}

			$user_id = (int) $order->get_customer_id();
			if ( function_exists( 'ez_booking_conflict_with_other_order' ) && ez_booking_conflict_with_other_order( $product_id, $sans_time, $order_id, $user_id ) ) {
				return ez_team_recover_conflict_wallet_marketing_finalize( $order, $medoo, $actor_id, true );
			}

			$player_name = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
			$user_phone  = $order->get_billing_phone();
			$user_level  = ( $user_id && function_exists( 'get_user_level' ) ) ? get_user_level( $user_id ) : null;
			$now         = time();

			$row_ins = array(
				'customer_id'  => $user_id,
				'wc_order_id'  => $order_id,
				'status'       => 1,
				'room_id'      => $product_id,
				'booking_time' => $sans_time,
				'booked_time'  => $now,
				'name'         => $player_name !== '' ? $player_name : null,
				'phone'        => $user_phone !== '' ? $user_phone : null,
				'quantity'     => $qty,
			);
			if ( $user_level !== null && $user_level !== '' ) {
				$row_ins['level'] = $user_level;
			}

			$success = false;
			for ( $attempt = 0; $attempt < 3 && ! $success; $attempt++ ) {
				try {
					$mq->insert( 'wp_zb_booking_history', $row_ins );
				} catch ( Throwable $e ) {
					unset( $row_ins['level'] );
					error_log( '[ez_team_recover_booking_sans_run] insert: ' . $e->getMessage() );
				}
				$success = $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );
				if ( ! $success ) {
					usleep( 200000 );
				}
			}

			if ( ! $success ) {
				$result['message'] = 'درج رزرو در wp_zb_booking_history ناموفق بود.';

				return $result;
			}

			if ( function_exists( 'check_and_update_markting_table' ) ) {
				check_and_update_markting_table( $order_id, true );
			}

			if ( $order->has_status( 'processing' ) ) {
				$ez_pt  = (string) get_post_meta( $order_id, 'ez_payment_type', true );
				$target = ( $ez_pt === 'complete' ) ? 'completed-paid' : 'partially-paid';
				$note   = 'بستن سانس دستی توسط تیم (بررسی سانس).';
				$order->update_status( $target, $note );
				$medoo->update(
					'wp_markting',
					array(
						'order_status' => standardize_order_status( $target ),
					),
					array( 'order_id' => $order_id )
				);
			}

			$order = wc_get_order( $order_id );
			if ( function_exists( 'log_order_status_change' ) && $actor_id > 0 && $order ) {
				$new_st = (string) $order->get_status();
				if ( $new_st !== $wc_st ) {
					log_order_status_change( $order_id, $wc_st, standardize_order_status( $new_st ), 'ez_team_recover_booking_sans_run::booked', $actor_id );
				}
			}

			$result['success'] = true;
			$result['code']    = 'booked';
			$result['message'] = 'سانس برای این سفارش ثبت شد و زمان سانس در مارکتینگ به‌روزرسانی شد.';

			return $result;
		} finally {
			delete_post_meta( $order_id, $lock_key );
		}
	}
}

if ( ! function_exists( 'ez_ensure_wp_markting_after_payment_complete' ) ) {
	/**
	 * پس از پرداخت موفق، مطمئن می‌شویم ردیف wp_markting برای سفارش وجود دارد و تازه است.
	 *
	 * — اگر ردیف نباشد (مثلاً insert اولیه در checkout خطا داده)، save_to_markting_table را صدا می‌زند.
	 * — در هر حال check_and_update_markting_table را اجرا می‌کند تا sans/transaction/code_otagh هم‌گام شوند.
	 *
	 * این هوک با priority 25 پس از my_change_status_function (که وضعیت را به completed-paid/partially-paid عوض می‌کند)
	 * اجرا می‌شود، و پیش از ez_maybe_sync_booking_after_payment_complete (priority 150).
	 *
	 * @param int $order_id WC order id.
	 */
	function ez_ensure_wp_markting_after_payment_complete( $order_id ) {
		$order_id = (int) $order_id;
		if ( $order_id <= 0 ) {
			return;
		}
		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->is_paid() ) {
			return;
		}
		$medoo = function_exists( 'medoo' ) ? medoo() : null;
		if ( ! $medoo || ! method_exists( $medoo, 'has' ) ) {
			return;
		}
		try {
			if ( ! $medoo->has( 'wp_markting', array( 'order_id' => $order_id ) ) ) {
				if ( function_exists( 'save_to_markting_table' ) ) {
					save_to_markting_table( $order_id, array(), $order );
				}
			}
			if ( function_exists( 'check_and_update_markting_table' ) ) {
				check_and_update_markting_table( $order_id, true );
			}
		} catch ( Throwable $e ) {
			error_log( '[ez_ensure_wp_markting_after_payment_complete] order=' . $order_id . ' ' . $e->getMessage() );
		}
	}

	add_action( 'woocommerce_payment_complete', 'ez_ensure_wp_markting_after_payment_complete', 25, 1 );
}
