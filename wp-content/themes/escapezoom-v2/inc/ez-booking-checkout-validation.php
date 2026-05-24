<?php
/**
 * Shared checkout sans validation + gateway return cancel policy + team ops ineligible reasons.
 */

if ( ! function_exists( 'ez_sans_auto_disable_lead_minutes' ) ) {
	/**
	 * Minutes before session start when booking is blocked (product meta auto_disable).
	 */
	function ez_sans_auto_disable_lead_minutes( int $product_id ): int {
		if ( $product_id <= 0 || ! function_exists( 'get_post_meta' ) ) {
			return 0;
		}

		return max( 0, (int) get_post_meta( $product_id, 'auto_disable', true ) );
	}
}

if ( ! function_exists( 'ez_sans_checkout_slot_state' ) ) {
	/**
	 * @return string bookable|past|too_soon|invalid
	 */
	function ez_sans_checkout_slot_state( int $product_id, int $sans_ts ): string {
		if ( $sans_ts <= 0 ) {
			return 'invalid';
		}
		if ( $sans_ts < time() ) {
			return 'past';
		}

		return 'bookable';
	}
}

if ( ! function_exists( 'ez_sans_checkout_slot_message' ) ) {
	function ez_sans_checkout_slot_message( int $product_id, int $sans_ts, ?string $state = null ): string {
		$state = $state ?? ez_sans_checkout_slot_state( $product_id, $sans_ts );
		if ( $state === 'past' ) {
			return 'زمان این سانس گذشته است. لطفاً سانس دیگری انتخاب کنید.';
		}
		if ( $state === 'invalid' ) {
			return 'زمان سانس نامعتبر است.';
		}

		return '';
	}
}

if ( ! function_exists( 'ez_zarinpal_inquiry_for_order' ) ) {
	/**
	 * @return array{paid:bool,status:string,raw:array<string,mixed>|null}
	 */
	function ez_zarinpal_inquiry_for_order( WC_Order $order ): array {
		$empty = array(
			'paid'   => false,
			'status' => '',
			'raw'    => null,
		);

		if ( ! in_array( $order->get_payment_method(), array( 'WC_ZPal', 'WC_ZPal_co' ), true ) ) {
			return $empty;
		}

		$authority = (string) $order->get_meta( '_zarinpal_authority' );
		if ( $authority === '' || ! class_exists( 'ZarinpalHelperClass' ) ) {
			return $empty;
		}

		$settings_option = ( $order->get_payment_method() === 'WC_ZPal_co' )
			? 'woocommerce_WC_ZPal_co_settings'
			: 'woocommerce_WC_ZPal_settings';
		$settings        = get_option( $settings_option, array() );
		$merchant          = isset( $settings['merchantcode'] ) ? (string) $settings['merchantcode'] : '';
		$sandbox           = isset( $settings['sandbox'] ) && $settings['sandbox'] === 'yes';
		$access_token      = isset( $settings['access_token'] ) ? (string) $settings['access_token'] : '';

		if ( $merchant === '' || $access_token === '' ) {
			return $empty;
		}

		try {
			$zarinpal = new ZarinpalHelperClass( $merchant, $sandbox, $access_token );
			$inquiry  = $zarinpal->inquiryPayment( $authority );
			if ( ! is_array( $inquiry ) ) {
				return $empty;
			}
			$status = isset( $inquiry['status'] ) ? (string) $inquiry['status'] : '';

			return array(
				'paid'   => ( $status === 'PAID' ),
				'status' => $status,
				'raw'    => $inquiry,
			);
		} catch ( Throwable $e ) {
			error_log( '[ez_zarinpal_inquiry_for_order] ' . $e->getMessage() );

			return $empty;
		}
	}
}

if ( ! function_exists( 'ez_zibal_inquiry_for_order' ) ) {
	/**
	 * @return array{paid:bool,status:string,raw:array<string,mixed>|null}
	 */
	function ez_zibal_inquiry_for_order( WC_Order $order ): array {
		$empty = array(
			'paid'   => false,
			'status' => '',
			'raw'    => null,
		);

		if ( $order->get_payment_method() !== 'WC_Zibal' || ! function_exists( 'ez_zibal_get_track_id' ) || ! function_exists( 'ez_zibal_inquiry' ) ) {
			return $empty;
		}

		$track_id = ez_zibal_get_track_id( $order );
		if ( $track_id === '' || ! function_exists( 'ez_zibal_get_merchant' ) ) {
			return $empty;
		}

		$inquiry = ez_zibal_inquiry( $track_id, ez_zibal_get_merchant( $order ) );
		if ( ! is_array( $inquiry ) ) {
			return $empty;
		}

		$inq_result = isset( $inquiry['result'] ) ? (int) $inquiry['result'] : 0;
		$inq_status = isset( $inquiry['status'] ) ? $inquiry['status'] : null;
		$paidish    = ( $inq_result === 100 ) || ( function_exists( 'ez_zibal_inquiry_indicates_paid' ) && ez_zibal_inquiry_indicates_paid( $inq_status ) );
		$cancelled  = (int) $inq_status === 3;

		return array(
			'paid'   => $paidish && ! $cancelled,
			'status' => $cancelled ? 'CANCELLED' : ( $paidish ? 'PAID' : (string) $inq_status ),
			'raw'    => $inquiry,
		);
	}
}

if ( ! function_exists( 'ez_gateway_inquiry_for_order' ) ) {
	/**
	 * @return array{paid:bool,status:string,raw:array<string,mixed>|null,gateway:string}
	 */
	function ez_gateway_inquiry_for_order( WC_Order $order ): array {
		$pm = $order->get_payment_method();
		if ( in_array( $pm, array( 'WC_ZPal', 'WC_ZPal_co' ), true ) ) {
			$r            = ez_zarinpal_inquiry_for_order( $order );
			$r['gateway'] = 'zarinpal';

			return $r;
		}
		if ( $pm === 'WC_Zibal' ) {
			$r            = ez_zibal_inquiry_for_order( $order );
			$r['gateway'] = 'zibal';

			return $r;
		}

		return array(
			'paid'    => false,
			'status'  => '',
			'raw'     => null,
			'gateway' => '',
		);
	}
}

if ( ! function_exists( 'ez_gateway_inquiry_definitively_not_paid' ) ) {
	/**
	 * True only when gateway inquiry clearly says payment did not succeed.
	 *
	 * @param array{paid:bool,status:string,raw:?array,gateway?:string} $inquiry
	 */
	function ez_gateway_inquiry_definitively_not_paid( array $inquiry ): bool {
		if ( ! empty( $inquiry['paid'] ) ) {
			return false;
		}
		$status = isset( $inquiry['status'] ) ? strtoupper( (string) $inquiry['status'] ) : '';
		if ( $status === '' ) {
			return false;
		}
		$gateway = isset( $inquiry['gateway'] ) ? (string) $inquiry['gateway'] : '';
		if ( $gateway === 'zarinpal' ) {
			return in_array( $status, array( 'NOT_PAID', 'FAILED', 'REVERSED', 'EXPIRED' ), true );
		}
		if ( $gateway === 'zibal' ) {
			return $status === 'CANCELLED' || $status === '3' || $status === '-1';
		}

		return false;
	}
}

if ( ! function_exists( 'ez_gateway_return_should_cancel_order' ) ) {
	/**
	 * Cancel only on definitive gateway failure — not while payment may still complete.
	 *
	 * @param WC_Order                             $order
	 * @param array{ok:bool,code:string,message?:string} $verify_result
	 * @param array{paid:bool,status:string,raw:?array,gateway?:string} $inquiry
	 */
	function ez_gateway_return_should_cancel_order( WC_Order $order, array $verify_result, array $inquiry ): bool {
		if ( $order->is_paid() ) {
			return false;
		}
		if ( ! empty( $verify_result['ok'] ) ) {
			return false;
		}
		$verify_code = isset( $verify_result['code'] ) ? (string) $verify_result['code'] : '';
		if ( in_array( $verify_code, array( 'locked', 'already_paid' ), true ) ) {
			return false;
		}
		if ( ! empty( $inquiry['paid'] ) ) {
			return false;
		}
		if ( ! ez_gateway_inquiry_definitively_not_paid( $inquiry ) ) {
			return false;
		}
		if ( $verify_code === 'not_paid_at_zp' || $verify_code === 'not_paid_at_zibal' ) {
			return true;
		}

		return (bool) apply_filters( 'ez_gateway_return_should_cancel_order', ez_gateway_inquiry_definitively_not_paid( $inquiry ), $order, $verify_result, $inquiry );
	}
}

if ( ! function_exists( 'ez_gateway_return_defer_to_on_hold' ) ) {
	/**
	 * @param WC_Order $order
	 * @param array{ok:bool,code:string} $verify_result
	 * @param array{paid:bool,status:string} $inquiry
	 */
	function ez_gateway_return_defer_to_on_hold( WC_Order $order, array $verify_result, array $inquiry ): bool {
		if ( $order->is_paid() || ! empty( $verify_result['ok'] ) ) {
			return false;
		}
		if ( ! empty( $inquiry['paid'] ) ) {
			return true;
		}
		$verify_code = isset( $verify_result['code'] ) ? (string) $verify_result['code'] : '';
		if ( in_array( $verify_code, array( 'locked', 'bad_status' ), true ) ) {
			return true;
		}
		if ( function_exists( 'ez_zp_order_age_seconds' ) ) {
			$grace = max( 60, (int) apply_filters( 'ez_zp_reconcile_grace_seconds', 60 * MINUTE_IN_SECONDS, $order ) );
			if ( (int) ez_zp_order_age_seconds( $order ) <= $grace ) {
				return true;
			}
		}

		return (bool) apply_filters( 'ez_gateway_return_defer_to_on_hold', false, $order, $verify_result, $inquiry );
	}
}

if ( ! function_exists( 'ez_gateway_handle_payment_return_failure' ) ) {
	/**
	 * Shared NOK / Zibal-failed handler: verify, inquiry, on-hold grace, cancel only if definitive fail.
	 *
	 * @return array{action:string,redirect_url:string}
	 */
	function ez_gateway_handle_payment_return_failure( int $order_id, string $gateway_label ): array {
		$order_id = (int) $order_id;
		$fail_url = home_url( '/order-failed/' . ( $order_id > 0 ? '?order=' . $order_id : '' ) );

		if ( $order_id <= 0 ) {
			return array(
				'action'       => 'redirect',
				'redirect_url' => home_url( '/order-failed/' ),
			);
		}

		$order = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order ) {
			return array(
				'action'       => 'redirect',
				'redirect_url' => $fail_url,
			);
		}

		if ( $order->is_paid() ) {
			return array(
				'action'       => 'redirect',
				'redirect_url' => $order->get_checkout_order_received_url(),
			);
		}

		$verify = array(
			'ok'   => false,
			'code' => 'skipped',
		);

		if ( $gateway_label === 'zarinpal' && function_exists( 'ez_zp_action_based_enabled' ) && ez_zp_action_based_enabled() && function_exists( 'ez_zarinpal_try_verify_now' ) ) {
			$verify = ez_zarinpal_try_verify_now( $order_id );
		} elseif ( $gateway_label === 'zibal' && function_exists( 'ez_zibal_try_verify_now' ) ) {
			$verify = ez_zibal_try_verify_now( $order_id );
		}

		if ( ! empty( $verify['ok'] ) ) {
			clean_post_cache( $order_id );
			$order = wc_get_order( $order_id );
			if ( $order instanceof WC_Order && $order->is_paid() ) {
				return array(
					'action'       => 'redirect',
					'redirect_url' => $order->get_checkout_order_received_url(),
				);
			}
		}

		$inquiry = function_exists( 'ez_gateway_inquiry_for_order' ) ? ez_gateway_inquiry_for_order( $order ) : array(
			'paid'   => false,
			'status' => '',
			'raw'    => null,
		);

		if ( function_exists( 'ez_gateway_return_defer_to_on_hold' ) && ez_gateway_return_defer_to_on_hold( $order, $verify, $inquiry ) ) {
			$note = $gateway_label === 'zibal'
				? 'زیبال: بازگشت ناموفق — در انتظار verify (inquiry/grace).'
				: 'زرین‌پال: بازگشت NOK — در انتظار verify (inquiry/grace).';
			if ( $order->get_status() !== 'cancelled' && $order->get_status() !== 'on-hold' ) {
				$order->update_status( 'on-hold', $note );
			} elseif ( $order->get_status() === 'on-hold' ) {
				$order->add_order_note( $note );
			}
			if ( function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) {
				ez_reconcile_single_order_wp_markting_wc_booking( $order_id );
			}

			return array(
				'action'       => 'redirect',
				'redirect_url' => $order->get_checkout_order_received_url(),
			);
		}

		if ( function_exists( 'ez_gateway_return_should_cancel_order' ) && ez_gateway_return_should_cancel_order( $order, $verify, $inquiry ) ) {
			if ( $order->get_status() !== 'cancelled' ) {
				$cancel_note = $gateway_label === 'zibal'
					? 'پرداخت در درگاه زیبال ناموفق یا لغو شد (تأیید inquiry).'
					: 'پرداخت توسط کاربر لغو شد (تأیید inquiry).';
				$order->update_status( 'cancelled', $cancel_note );
			}

			return array(
				'action'       => 'redirect',
				'redirect_url' => $fail_url,
			);
		}

		if ( $order->get_status() !== 'cancelled' && $order->get_status() !== 'on-hold' ) {
			$order->update_status( 'on-hold', 'بازگشت از درگاه: بدون لغو خودکار — verify بعدی.' );
		}

		return array(
			'action'       => 'redirect',
			'redirect_url' => $order->get_checkout_order_received_url(),
		);
	}
}

if ( ! function_exists( 'ez_markting_team_ops_ineligible_reason' ) ) {
	/**
	 * @param array<string,mixed> $row
	 */
	function ez_markting_team_ops_ineligible_reason( array $row, string $action ): string {
		if ( ! function_exists( 'ez_markting_row_team_ops_has_actionable_sans' ) || ! ez_markting_row_team_ops_has_actionable_sans( $row ) ) {
			return 'زمان سانس در سفارش ثبت نشده یا سفارش بازی/سانس ندارد.';
		}

		$st = function_exists( 'ez_markting_status_slug' ) ? ez_markting_status_slug( $row ) : '';

		if ( $action === 'confirm_payment' ) {
			if ( function_exists( 'ez_markting_row_eligible_confirm_payment' ) && ez_markting_row_eligible_confirm_payment( $row ) ) {
				return '';
			}

			return 'تأیید پرداخت فقط برای سفارش‌های «در انتظار پرداخت» (pending)، «معلق» (on-hold) یا «لغو شده» (cancelled) است.';
		}

		if ( $action === 'booking_recovery' ) {
			if ( function_exists( 'ez_markting_row_can_run_booking_recovery' ) && ez_markting_row_can_run_booking_recovery( $row ) ) {
				return '';
			}

			return 'بررسی سانس فقط برای «در حال بستن سانس» (processing)، «پیش‌پرداخت»، «پرداخت کامل» یا «تداخل» است.';
		}

		return 'عملیات نامعتبر.';
	}
}

if ( ! function_exists( 'ez_api_sans_slot_error_message' ) ) {
	/**
	 * @return string|null Error message or null if slot is bookable.
	 */
	function ez_api_sans_slot_error_message( int $product_id, int $sans_time ): ?string {
		$state = ez_sans_checkout_slot_state( $product_id, $sans_time );
		if ( $state === 'bookable' ) {
			return null;
		}

		return ez_sans_checkout_slot_message( $product_id, $sans_time, $state );
	}
}
