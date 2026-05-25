<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

/**
 * Reads sans availability via internal reservation dispatch (bridge to legacy logic).
 */
final class BookingAvailabilityService
{
	/**
	 * @return array<int,array<string,mixed>>|array<int,array<int,array<string,mixed>>>
	 */
	public static function getSanses( int $productId, int $dayStartTime, int $days = 1 ) {
		$productId    = (int) $productId;
		$dayStartTime = (int) $dayStartTime;
		$days         = max( 1, (int) $days );

		if ( $productId <= 0 || $dayStartTime <= 0 || ! function_exists( 'ez_reservation' ) ) {
			return $days > 1 ? array() : array();
		}

		if ( ! defined( 'EZ_BOOKING_INTERNAL_CALL' ) ) {
			define( 'EZ_BOOKING_INTERNAL_CALL', true );
		}
		if ( ! defined( 'EZ_BOOKING_USE_INTERNAL' ) ) {
			define( 'EZ_BOOKING_USE_INTERNAL', true );
		}

		$raw = ez_reservation(
			array(
				'type' => 'get_sanses',
				'data' => array(
					'day_start_time' => $dayStartTime,
					'product_id'     => $productId,
					'days'           => $days,
				),
			)
		);

		$decoded = json_decode( (string) $raw, true );
		if ( ! is_array( $decoded ) ) {
			return array();
		}

		return $decoded;
	}
}
