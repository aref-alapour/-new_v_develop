<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

/**
 * Internal reservation dispatch (no HTTP loopback).
 */
final class BookingDispatchService
{
	/**
	 * @param array<string,mixed> $data
	 */
	public static function dispatchType( string $type, array $data = array() ): string {
		if ( ! function_exists( 'ez_reservation_dispatch' ) ) {
			$dispatch = ABSPATH . 'web-service/includes/reservation-dispatch.php';
			if ( is_readable( $dispatch ) ) {
				require_once $dispatch;
			}
		}

		if ( ! function_exists( 'ez_reservation_dispatch' ) ) {
			return '';
		}

		if ( ! defined( 'EZ_BOOKING_INTERNAL_CALL' ) ) {
			define( 'EZ_BOOKING_INTERNAL_CALL', true );
		}
		if ( ! defined( 'EZ_BOOKING_USE_INTERNAL' ) ) {
			define( 'EZ_BOOKING_USE_INTERNAL', true );
		}

		$payload = function_exists( 'ez_reservation_normalize_data' )
			? ez_reservation_normalize_data(
				array(
					'type' => $type,
					'data' => $data,
				)
			)
			: (object) array(
				'type' => $type,
				'data' => (object) $data,
			);

		return ez_reservation_dispatch( $payload );
	}
}
