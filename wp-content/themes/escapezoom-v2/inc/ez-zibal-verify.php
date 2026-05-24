<?php
/**
 * Action-Based Zibal (WC_Zibal) verification — mirror of ZarinPal layer in saeed-codes.php.
 *
 * API: https://gateway.zibal.ir/v1/inquiry | /v1/verify
 * Meta keys: confirm on staging via wp_postmeta (see bin/e2e-checkout-matrix.txt Zibal discovery).
 *
 * @package EscapeZoom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature flag (toggle: update_option( 'ez_zibal_action_based_enabled', '0' );).
 */
function ez_zibal_action_based_enabled(): bool {
	return (bool) get_option( 'ez_zibal_action_based_enabled', 1 );
}

/**
 * Default grace seconds (under Zibal ~20m auto-refund if verify never sent).
 *
 * @param \WC_Order|null $order Order for filters.
 */
function ez_zibal_default_grace_seconds( $order = null ): int {
	return max( 60, (int) apply_filters( 'ez_zibal_default_grace_seconds', 60 * MINUTE_IN_SECONDS, $order ) );
}

/**
 * Meta keys that may hold Zibal trackId (first non-empty wins).
 *
 * @return string[]
 */
function ez_zibal_track_id_meta_keys(): array {
	$keys = array(
		'_zibal_track_id',
		'zibal_track_id',
		'_zibal_trackId',
		'ZibalTrackId',
		'_transaction_id_track_id',
	);

	return array_values(
		array_unique(
			array_filter(
				(array) apply_filters( 'ez_zibal_track_id_meta_keys', $keys )
			)
		)
	);
}

/**
 * @param \WC_Order $order Order.
 * @return string Numeric track id or empty.
 */
function ez_zibal_get_track_id( $order ): string {
	if ( ! $order instanceof WC_Order ) {
		return '';
	}

	foreach ( ez_zibal_track_id_meta_keys() as $key ) {
		$val = trim( (string) $order->get_meta( $key ) );
		if ( $val !== '' && ctype_digit( $val ) ) {
			return $val;
		}
	}

	return '';
}

/**
 * @param \WC_Order|null $order Order.
 * @return string Merchant id.
 */
function ez_zibal_get_merchant( $order = null ): string {
	$settings = get_option( 'woocommerce_WC_Zibal_settings', array() );
	if ( ! is_array( $settings ) ) {
		$settings = array();
	}

	$merchant = '';
	foreach ( array( 'merchant', 'merchantcode', 'merchant_id', 'merchantCode' ) as $k ) {
		if ( ! empty( $settings[ $k ] ) && is_string( $settings[ $k ] ) ) {
			$merchant = trim( $settings[ $k ] );
			break;
		}
	}

	if ( $merchant === '' ) {
		$merchant = 'zibal';
	}

	return (string) apply_filters( 'ez_zibal_merchant', $merchant, $order );
}

/**
 * POST JSON to Zibal gateway.
 *
 * @param string               $endpoint inquiry|verify.
 * @param array<string, mixed> $body     Request body.
 * @return array<string, mixed>|null
 */
function ez_zibal_api_post( string $endpoint, array $body ) {
	$endpoint = trim( $endpoint, '/' );
	$url      = 'https://gateway.zibal.ir/v1/' . $endpoint;

	$response = wp_remote_post(
		$url,
		array(
			'timeout' => 15,
			'headers' => array( 'Content-Type' => 'application/json' ),
			'body'    => wp_json_encode( $body ),
		)
	);

	if ( is_wp_error( $response ) ) {
		if ( function_exists( 'saeed_store' ) ) {
			saeed_store( 'ez_zb_api[' . $endpoint . ']: ' . $response->get_error_message() );
		}
		return null;
	}

	$decoded = json_decode( (string) wp_remote_retrieve_body( $response ), true );
	return is_array( $decoded ) ? $decoded : null;
}

/**
 * Zibal paid statuses (API status table): 1 = paid verified, 2 = paid unverified.
 *
 * @param mixed $status Status from inquiry.
 */
function ez_zibal_inquiry_indicates_paid( $status ): bool {
	return in_array( (int) $status, array( 1, 2 ), true );
}

/**
 * @param string $track_id Track id.
 * @param string $merchant Merchant.
 * @return array<string, mixed>|null
 */
function ez_zibal_inquiry( string $track_id, string $merchant ) {
	$track_id = trim( $track_id );
	if ( $track_id === '' || ! ctype_digit( $track_id ) ) {
		return null;
	}

	return ez_zibal_api_post(
		'inquiry',
		array(
			'merchant' => $merchant,
			'trackId'  => (int) $track_id,
		)
	);
}

/**
 * @param int $order_id Order ID.
 * @return array{ok:bool, code:string, message:string, refNumber?:string}
 */
function ez_zibal_try_verify_now( int $order_id ): array {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return array(
			'ok'      => false,
			'code'    => 'invalid_order',
			'message' => 'invalid order id',
		);
	}

	$order = wc_get_order( $order_id );
	if ( ! $order instanceof WC_Order ) {
		return array(
			'ok'      => false,
			'code'    => 'order_not_found',
			'message' => 'order not found',
		);
	}

	if ( $order->get_payment_method() !== 'WC_Zibal' ) {
		return array(
			'ok'      => false,
			'code'    => 'not_zibal',
			'message' => $order->get_payment_method(),
		);
	}

	if ( $order->is_paid() || ( function_exists( 'ez_booking_pipeline_is_done' ) && ez_booking_pipeline_is_done( $order_id ) ) ) {
		return array(
			'ok'      => true,
			'code'    => 'already_paid',
			'message' => 'already paid',
		);
	}

	$track_id = ez_zibal_get_track_id( $order );
	if ( $track_id === '' ) {
		return array(
			'ok'      => false,
			'code'    => 'no_track_id',
			'message' => 'no trackId meta',
		);
	}

	$status               = (string) $order->get_status();
	$allowed_statuses     = array( 'pending', 'failed', 'on-hold' );
	$allow_cancelled_recovery = false;

	if ( $status === 'cancelled' ) {
		$age_seconds = function_exists( 'ez_zp_order_age_seconds' )
			? (int) ez_zp_order_age_seconds( $order )
			: PHP_INT_MAX;
		$grace_sec   = max( 60, (int) apply_filters( 'ez_zibal_cancelled_recovery_grace_seconds', ez_zibal_default_grace_seconds( $order ), $order ) );
		$allow_cancelled_recovery = $age_seconds <= $grace_sec;
	}

	if ( ! in_array( $status, $allowed_statuses, true ) && ! $allow_cancelled_recovery ) {
		return array(
			'ok'      => false,
			'code'    => 'bad_status',
			'message' => $status,
		);
	}

	$lock_key = '_ez_zibal_verify_lock';
	$lock_at  = (int) $order->get_meta( $lock_key );
	if ( $lock_at && ( time() - $lock_at ) < 30 ) {
		return array(
			'ok'      => false,
			'code'    => 'locked',
			'message' => 'verify locked',
		);
	}

	$order->update_meta_data( $lock_key, time() );
	$order->save();

	$merchant = ez_zibal_get_merchant( $order );

	try {
		$inquiry = ez_zibal_inquiry( $track_id, $merchant );
		if ( is_array( $inquiry ) ) {
			$inq_result = isset( $inquiry['result'] ) ? (int) $inquiry['result'] : 0;
			$inq_status = isset( $inquiry['status'] ) ? $inquiry['status'] : null;
			$paidish    = ( $inq_result === 100 ) || ez_zibal_inquiry_indicates_paid( $inq_status );
			// status 3 = cancelled by user at gateway (Zibal status table).
			if ( ! $paidish && (int) $inq_status === 3 ) {
				if ( function_exists( 'saeed_store' ) ) {
					saeed_store( "ez_zb_verify[{$order_id}]: inquiry cancelled -> " . wp_json_encode( $inquiry ) );
				}
				return array(
					'ok'      => false,
					'code'    => 'not_paid_at_zibal',
					'message' => (string) ( $inquiry['message'] ?? $inq_result ),
				);
			}
		}

		$verify = ez_zibal_api_post(
			'verify',
			array(
				'merchant' => $merchant,
				'trackId'  => (int) $track_id,
			)
		);

		if ( ! is_array( $verify ) ) {
			return array(
				'ok'      => false,
				'code'    => 'network_error',
				'message' => 'verify request failed',
			);
		}

		$result = isset( $verify['result'] ) ? (int) $verify['result'] : 0;

		if ( $result === 100 || $result === 201 ) {
			$ref = (string) ( $verify['refNumber'] ?? $verify['ref_number'] ?? '' );
			if ( ! $order->is_paid() ) {
				$order->payment_complete( $ref !== '' ? $ref : (string) $track_id );
				$order->add_order_note(
					sprintf(
						'زیبال: تأیید پرداخت (Action-Based). result=%d ref=%s',
						$result,
						$ref !== '' ? $ref : $track_id
					)
				);
			}
			if ( function_exists( 'saeed_store' ) ) {
				saeed_store( "ez_zb_verify[{$order_id}]: SUCCESS result={$result} ref={$ref}" );
			}
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage( $order_id, 'zibal_verify_success', array( 'result' => $result ) );
			}
			return array(
				'ok'        => true,
				'code'      => $result === 201 ? 'already_verified' : 'verified',
				'message'   => 'ok',
				'refNumber' => $ref,
			);
		}

		if ( function_exists( 'saeed_store' ) ) {
			saeed_store( "ez_zb_verify[{$order_id}]: verify failed result={$result} -> " . wp_json_encode( $verify ) );
		}

		return array(
			'ok'      => false,
			'code'    => 'verify_failed',
			'message' => (string) ( $verify['message'] ?? $result ),
		);
	} catch ( Throwable $e ) {
		if ( function_exists( 'saeed_store' ) ) {
			saeed_store( "ez_zb_verify[{$order_id}]: EXCEPTION " . $e->getMessage() );
		}
		return array(
			'ok'      => false,
			'code'    => 'exception',
			'message' => $e->getMessage(),
		);
	} finally {
		$fresh = wc_get_order( $order_id );
		if ( $fresh instanceof WC_Order ) {
			$fresh->delete_meta_data( $lock_key );
			$fresh->save();
		}
	}
}

/**
 * @param int $order_id     Order ID.
 * @param int $max_attempts Max tries.
 * @return array{ok:bool, code:string, message:string, attempts:int, refNumber?:string}
 */
function ez_zibal_verify_with_retries( int $order_id, int $max_attempts = 3 ): array {
	$order_id     = (int) $order_id;
	$max_attempts = max( 1, $max_attempts );
	$delay_us     = max( 0, (int) apply_filters( 'ez_zibal_verify_retry_delay_us', 300000, $order_id ) );

	$last = array(
		'ok'      => false,
		'code'    => 'not_run',
		'message' => 'no attempts',
	);

	for ( $i = 1; $i <= $max_attempts; $i++ ) {
		$last = ez_zibal_try_verify_now( $order_id );

		if ( ! empty( $last['ok'] ) && in_array( (string) ( $last['code'] ?? '' ), array( 'verified', 'already_verified', 'already_paid' ), true ) ) {
			$last['attempts'] = $i;
			return $last;
		}

		if ( $i < $max_attempts && $delay_us > 0 ) {
			usleep( $delay_us );
		}
	}

	$last['attempts'] = $max_attempts;
	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $order_id, 'zibal_verify_failed_after_retries', array( 'code' => $last['code'] ?? '' ) );
	}
	return $last;
}

/**
 * Cron: pending/on-hold WC_Zibal orders with trackId inside grace window.
 */
function zibal_unverified_orders_process(): void {
	if ( ! ez_zibal_action_based_enabled() || ! function_exists( 'wc_get_orders' ) ) {
		return;
	}

	$limit = max( 1, min( 25, (int) apply_filters( 'ez_zibal_cron_batch_limit', 10 ) ) );
	$grace = ez_zibal_default_grace_seconds();

	$order_ids = wc_get_orders(
		array(
			'limit'          => $limit,
			'status'         => array( 'pending', 'on-hold' ),
			'payment_method' => 'WC_Zibal',
			'return'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'ASC',
		)
	);

	if ( ! is_array( $order_ids ) ) {
		return;
	}

	foreach ( $order_ids as $oid ) {
		$order_id = (int) $oid;
		$order    = wc_get_order( $order_id );
		if ( ! $order instanceof WC_Order || $order->is_paid() ) {
			continue;
		}
		if ( ez_zibal_get_track_id( $order ) === '' ) {
			continue;
		}
		if ( function_exists( 'ez_zp_order_age_seconds' ) && (int) ez_zp_order_age_seconds( $order ) > $grace ) {
			continue;
		}
		ez_zibal_verify_with_retries( $order_id, 2 );
	}
}
