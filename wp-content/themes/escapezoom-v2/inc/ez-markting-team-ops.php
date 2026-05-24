<?php
/**
 * Team panel ops (تأیید پرداخت / بررسی سانس): eligibility from wp_markting only.
 * Loaded by theme functions.php and orders_get2.php (no full WP bootstrap required for slug helpers).
 */

if ( ! function_exists( 'ez_markting_status_slug' ) ) {
	/**
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_status_slug( array $row ): string {
		$st = isset( $row['order_status'] ) ? (string) $row['order_status'] : '';
		if ( strpos( $st, 'wc-' ) === 0 ) {
			$st = substr( $st, 3 );
		}

		return $st;
	}
}

if ( ! function_exists( 'ez_markting_row_has_complete_sans_fields' ) ) {
	/**
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_has_complete_sans_fields( array $row ): bool {
		return ! empty( $row['order_sans_time'] )
			&& ! empty( $row['order_sans_date'] )
			&& ! empty( $row['order_sans_day'] );
	}
}

if ( ! function_exists( 'ez_markting_row_has_sans_slot' ) ) {
	/**
	 * سانس از ستون‌های مارکتینگ یا (در صورت لود وردپرس) متای sans_time.
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_has_sans_slot( array $row ): bool {
		if ( ! empty( $row['order_sans_time'] ) ) {
			return true;
		}
		if ( ! empty( $row['order_sans_date'] ) ) {
			return true;
		}
		if ( ! empty( $row['order_sans_day'] ) ) {
			return true;
		}
		$oid = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $oid > 0 && function_exists( 'get_post_meta' ) ) {
			return (int) get_post_meta( $oid, 'sans_time', true ) > 0;
		}

		return false;
	}
}

if ( ! function_exists( 'ez_markting_row_is_bookable_order' ) ) {
	/**
	 * سفارش بازی/سانس دارد (برای نمایش دکمه حتی وقتی ستون‌های سانس مارکتینگ خالی‌اند).
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_is_bookable_order( array $row ): bool {
		if ( ez_markting_row_has_sans_slot( $row ) ) {
			return true;
		}
		$game = isset( $row['game_name'] ) ? trim( (string) $row['game_name'] ) : '';

		return $game !== '' && $game !== '---';
	}
}

if ( ! function_exists( 'ez_markting_row_needs_booking_recovery_work' ) ) {
	/**
	 * آیا این سفارش هنوز به بستن سانس / همگام‌سازی مارکتینگ نیاز دارد؟
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_needs_booking_recovery_work( array $row ): bool {
		$order_id = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $order_id <= 0 ) {
			return false;
		}

		$missing_booking = ez_markting_order_missing_booking( $order_id );
		if ( $missing_booking ) {
			return true;
		}

		// بوکینگ هست ولی مارکتینگ سانس را کامل نشان نمی‌دهد → همگام‌سازی
		return ! ez_markting_row_has_complete_sans_fields( $row );
	}
}

if ( ! function_exists( 'ez_markting_booking_prefetch_reset' ) ) {
	function ez_markting_booking_prefetch_reset(): void {
		$GLOBALS['ez_markting_booking_exists_ids'] = null;
	}
}

if ( ! function_exists( 'ez_markting_prefetch_booking_order_ids' ) ) {
	/**
	 * @param array<int|string> $order_ids
	 */
	function ez_markting_prefetch_booking_order_ids( array $order_ids ): void {
		ez_markting_booking_prefetch_reset();

		$ids = array_values(
			array_unique(
				array_filter(
					array_map( 'intval', $order_ids ),
					static function ( int $id ): bool {
						return $id > 0;
					}
				)
			)
		);

		if ( empty( $ids ) ) {
			$GLOBALS['ez_markting_booking_exists_ids'] = array();

			return;
		}

		if ( ! function_exists( 'medoo_queries' ) ) {
			ez_markting_booking_prefetch_reset();

			return;
		}

		$booked = array();

		try {
			$mq = medoo_queries();
			if ( ! $mq || ! method_exists( $mq, 'select' ) ) {
				ez_markting_booking_prefetch_reset();

				return;
			}

			$rows = $mq->select(
				'wp_zb_booking_history',
				array( 'wc_order_id' ),
				array( 'wc_order_id' => $ids )
			);
			foreach ( (array) $rows as $row ) {
				$oid = isset( $row['wc_order_id'] ) ? (int) $row['wc_order_id'] : 0;
				if ( $oid > 0 ) {
					$booked[ $oid ] = true;
				}
			}
		} catch ( Throwable $e ) {
			error_log( '[ez_markting_prefetch_booking_order_ids] ' . $e->getMessage() );
			ez_markting_booking_prefetch_reset();

			return;
		}

		$GLOBALS['ez_markting_booking_exists_ids'] = $booked;
	}
}

if ( ! function_exists( 'ez_markting_order_has_booking_cached' ) ) {
	function ez_markting_order_has_booking_cached( int $order_id ): ?bool {
		if ( $order_id <= 0 ) {
			return false;
		}
		if ( ! isset( $GLOBALS['ez_markting_booking_exists_ids'] ) || ! is_array( $GLOBALS['ez_markting_booking_exists_ids'] ) ) {
			return null;
		}

		return ! empty( $GLOBALS['ez_markting_booking_exists_ids'][ $order_id ] );
	}
}

if ( ! function_exists( 'ez_markting_order_missing_booking' ) ) {
	/**
	 * آیا برای این سفارش ردیف wp_zb_booking_history وجود ندارد؟
	 */
	function ez_markting_order_missing_booking( int $order_id ): bool {
		if ( $order_id <= 0 ) {
			return true;
		}

		$cached = ez_markting_order_has_booking_cached( $order_id );
		if ( $cached !== null ) {
			return ! $cached;
		}

		if ( function_exists( 'ez_booking_exists_for_order' ) ) {
			return ! ez_booking_exists_for_order( $order_id );
		}
		if ( function_exists( 'medoo_queries' ) ) {
			try {
				$mq = medoo_queries();
				if ( $mq && method_exists( $mq, 'has' ) ) {
					return ! $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );
				}
			} catch ( Throwable $e ) {
				error_log( '[ez_markting_order_missing_booking] ' . $e->getMessage() );
			}
		}

		return true;
	}
}

if ( ! function_exists( 'ez_markting_row_order_age_seconds' ) ) {
	/**
	 * @param array<string,mixed> $row
	 */
	function ez_markting_row_order_age_seconds( array $row ): ?int {
		$created_raw = isset( $row['order_created_at'] ) ? trim( (string) $row['order_created_at'] ) : '';
		if ( $created_raw === '' ) {
			return null;
		}

		if ( function_exists( 'current_time' ) && function_exists( 'wp_timezone' ) ) {
			try {
				$created_dt = date_create_immutable( $created_raw, wp_timezone() );
				if ( $created_dt ) {
					$ref_ts = (int) current_time( 'timestamp' );

					return max( 0, $ref_ts - $created_dt->getTimestamp() );
				}
			} catch ( Throwable $e ) {
				error_log( '[ez_markting_row_order_age_seconds] ' . $e->getMessage() );
			}
		}

		$created_at = strtotime( $created_raw );
		if ( ! $created_at ) {
			return null;
		}

		return max( 0, time() - (int) $created_at );
	}
}

if ( ! function_exists( 'ez_markting_row_in_team_ops_age_window' ) ) {
	/**
	 * پنجرهٔ سن برای عملیات دستی تیم (تأیید پرداخت / بررسی سانس).
	 *
	 * @param array<string,mixed> $row     wp_markting row.
	 * @param string              $context confirm_payment|booking_recovery
	 */
	function ez_markting_row_in_team_ops_age_window( array $row, string $context ): bool {
		$age = ez_markting_row_order_age_seconds( $row );
		if ( $age === null ) {
			return false;
		}

		if ( $context === 'confirm_payment' ) {
			$min_default = 0;
			$max_default = 7200;
			$min_age     = $min_default;
			$max_age     = $max_default;
			if ( function_exists( 'apply_filters' ) ) {
				$min_age = max( 0, (int) apply_filters( 'ez_team_confirm_payment_min_age_seconds', $min_default ) );
				$max_age = max( $min_age, (int) apply_filters( 'ez_team_confirm_payment_max_age_seconds', $max_default ) );
			}
		} elseif ( $context === 'booking_recovery' ) {
			$min_default = 0;
			$max_default = 7200;
			$min_age     = $min_default;
			$max_age     = $max_default;
			if ( function_exists( 'apply_filters' ) ) {
				$min_age = max( 0, (int) apply_filters( 'ez_team_booking_recovery_min_age_seconds', $min_default ) );
				$max_age = max( $min_age, (int) apply_filters( 'ez_team_booking_recovery_max_age_seconds', $max_default ) );
			}
		} else {
			return false;
		}

		return $age >= $min_age && $age <= $max_age;
	}
}

if ( ! function_exists( 'ez_markting_row_in_confirm_payment_age_window' ) ) {
	/**
	 * پنجرهٔ نمایش «تأیید پرداخت»: پیش‌فرض ۱۰ دقیقه تا ۲ ساعت پس از ثبت.
	 *
	 * @param array<string,mixed> $row
	 */
	function ez_markting_row_in_confirm_payment_age_window( array $row ): bool {
		return ez_markting_row_in_team_ops_age_window( $row, 'confirm_payment' );
	}
}

if ( ! function_exists( 'ez_markting_row_looks_paid' ) ) {
	/**
	 * پرداخت از دید مارکتینگ (بدون wc_get_order).
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_looks_paid( array $row ): bool {
		$st = ez_markting_status_slug( $row );
		if ( in_array( $st, array( 'processing', 'partially-paid', 'completed-paid', 'completed' ), true ) ) {
			return true;
		}
		$paid = isset( $row['order_paid'] ) ? (int) $row['order_paid'] : 0;

		return $paid > 0;
	}
}

if ( ! function_exists( 'ez_markting_row_team_ops_has_actionable_sans' ) ) {
	/**
	 * سفارش بازی/سانس دارد (پایهٔ نمایش دکمه‌های تیم).
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_team_ops_has_actionable_sans( array $row ): bool {
		if ( ! ez_markting_row_is_bookable_order( $row ) ) {
			return false;
		}
		$oid = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $oid > 0 && function_exists( 'get_post_meta' ) && (int) get_post_meta( $oid, 'sans_time', true ) > 0 ) {
			return true;
		}

		return ez_markting_row_has_sans_slot( $row );
	}
}

if ( ! function_exists( 'ez_markting_row_eligible_confirm_payment' ) ) {
	/**
	 * تأیید پرداخت: فقط wc-pending / wc-on-hold (در انتظار پرداخت / معلق).
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_eligible_confirm_payment( array $row ): bool {
		$st = ez_markting_status_slug( $row );
		if ( ! in_array( $st, array( 'pending', 'on-hold' ), true ) ) {
			return false;
		}

		return ez_markting_row_is_bookable_order( $row );
	}
}

if ( ! function_exists( 'ez_markting_row_is_bad_orders_list_candidate' ) ) {
	/**
	 * همان منطق فیلتر «سفارشات بد» در orders_get2 (سانس ناقص مارکتینگ یا processing گیرکرده).
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_is_bad_orders_list_candidate( array $row ): bool {
		$st = ez_markting_status_slug( $row );
		$oid = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $oid <= 0 ) {
			return false;
		}

		if ( in_array( $st, array( 'partially-paid', 'completed-paid' ), true ) ) {
			if ( ! ez_markting_row_has_complete_sans_fields( $row ) ) {
				return true;
			}

			return ez_markting_order_missing_booking( $oid );
		}

		if ( $st === 'processing' ) {
			$age = ez_markting_row_order_age_seconds( $row );

			return $age !== null && $age >= 600;
		}

		return false;
	}
}

if ( ! function_exists( 'ez_markting_row_eligible_for_booking_recovery' ) ) {
	/**
	 * بررسی سانس در لیست اصلی: فقط وقتی واقعاً کار باز است (بوکینگ/سانس ناقص).
	 * در فیلتر «سفارشات بد» از context=problematic استفاده کنید.
	 *
	 * @param array<string,mixed> $row     wp_markting row.
	 * @param string              $context main|problematic
	 */
	function ez_markting_row_eligible_for_booking_recovery( array $row, string $context = 'main' ): bool {
		$oid = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $oid <= 0 ) {
			return false;
		}

		$st = ez_markting_status_slug( $row );

		if ( $st === 'conflict' ) {
			return true;
		}

		if ( ! in_array( $st, array( 'processing', 'partially-paid', 'completed-paid' ), true ) ) {
			return false;
		}

		if ( $context === 'problematic' ) {
			return ez_markting_row_is_bad_orders_list_candidate( $row );
		}

		return ez_markting_row_needs_booking_recovery_work( $row );
	}
}

if ( ! function_exists( 'ez_markting_row_can_run_booking_recovery' ) ) {
	/**
	 * اجرای عملیات بررسی سانس (AJAX): مجاز در لیست اصلی یا سفارشات بد.
	 *
	 * @param array<string,mixed> $row wp_markting row.
	 */
	function ez_markting_row_can_run_booking_recovery( array $row ): bool {
		return ez_markting_row_eligible_for_booking_recovery( $row, 'main' )
			|| ez_markting_row_eligible_for_booking_recovery( $row, 'problematic' );
	}
}

if ( ! function_exists( 'ez_markting_team_ops_response_for_status' ) ) {
	/**
	 * پیام و code پاسخ AJAX پس از تأیید پرداخت / reconcile (بر اساس مارکتینگ).
	 *
	 * @return array{code:string,message:string}
	 */
	function ez_markting_team_ops_response_for_status( string $st ): array {
		if ( in_array( $st, array( 'partially-paid', 'completed-paid' ), true ) ) {
			return array(
				'code'    => 'paid',
				'message' => 'پرداخت ثبت شد. در صورت نبود تداخل، سانس بسته شده و پیامک برای پلیر، مجموعه و مدیر سانس در صف ارسال است.',
			);
		}
		if ( $st === 'processing' ) {
			return array(
				'code'    => 'processing',
				'message' => 'سانس یا تداخل طبق مارکتینگ بررسی شد؛ در صورت ماندن «در حال بستن سانس»، چند ثانیه بعد یا از «بررسی سانس» دوباره اقدام کنید.',
			);
		}
		if ( $st === 'conflict' ) {
			return array(
				'code'    => 'conflict',
				'message' => 'تداخل سانس تشخیص داده شد: در صورت امکان مبلغ به کیف مشتری عودت و پیامک اطلاع داده شده است؛ وضعیت در مارکتینگ «تداخل» است.',
			);
		}
		if ( $st === 'cancelled' ) {
			return array(
				'code'    => 'cancelled',
				'message' => 'سفارش پس از تأیید به «لغو شده» رفت (مثلاً کسری کیف پول یا مبلغ صفر). یادداشت سفارش را ببینید.',
			);
		}
		if ( in_array( $st, array( 'pending', 'on-hold' ), true ) ) {
			return array(
				'code'    => 'pending',
				'message' => 'پرداخت در ووکامرس ثبت شد ولی وضعیت مارکتینگ هنوز «در انتظار» است؛ چند ثانیه بعد دوباره تلاش کنید یا «بررسی سانس» را بزنید.',
			);
		}

		return array(
			'code'    => $st !== '' ? $st : 'unknown',
			'message' => 'عملیات انجام شد. وضعیت مارکتینگ: ' . ( $st !== '' ? $st : '—' ),
		);
	}
}
