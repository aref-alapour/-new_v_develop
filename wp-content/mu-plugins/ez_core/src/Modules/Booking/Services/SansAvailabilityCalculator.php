<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Modules\Booking\BookingDispatchService;
use EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter;
use EscapeZoom\Core\Modules\Booking\Infrastructure\ProductsDataRepository;

/**
 * Sans availability — native path (schedule from repository) with legacy dispatch fallback.
 *
 * Set EZ_BOOKING_NATIVE_SANSES true in wp-config when native slot logic is complete (phase B4).
 */
final class SansAvailabilityCalculator
{
	private ProductsDataRepository $products;

	public function __construct( ?ProductsDataRepository $products = null ) {
		$this->products = $products ?? new ProductsDataRepository();
	}

	/**
	 * @return array<int,array<string,mixed>>|array<int,array<int,array<string,mixed>>>
	 */
	public function getSanses( int $productId, int $dayStartTime, int $days = 1 ) {
		$productId    = (int) $productId;
		$dayStartTime = (int) $dayStartTime;
		$days         = max( 1, (int) $days );

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			return $days > 1 ? array() : array();
		}

		if ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES ) {
			return $this->getSansesNative( $productId, $dayStartTime, $days );
		}

		return ( new LegacySansAdapter() )->getSanses( $productId, $dayStartTime, $days );
	}

	/**
	 * Phase B: full slot generation in ez_core (locks, bookings, pricing).
	 *
	 * @return array<int,array<string,mixed>>|array<int,array<int,array<string,mixed>>>
	 */
	private function getSansesNative( int $productId, int $dayStartTime, int $days ) {
		$schedule = $this->products->getSchedule( $productId );
		if ( array() === $schedule ) {
			return $days > 1 ? array() : array();
		}

		// Until B3/B4 parity: use internal dispatch (same handlers, no HTTP).
		$raw = BookingDispatchService::dispatchType(
			'get_sanses',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => $days,
			)
		);

		$decoded = json_decode( (string) $raw, true );
		if ( ! is_array( $decoded ) ) {
			return $days > 1 ? array() : array();
		}

		if ( 1 === $days ) {
			return $this->normalizeFlatList( $decoded );
		}

		$out = array();
		foreach ( $decoded as $day ) {
			$out[] = is_array( $day ) ? $this->normalizeFlatList( $day ) : array();
		}

		return $out;
	}

	/**
	 * @param array<mixed> $decoded
	 * @return array<int,array<string,mixed>>
	 */
	private function normalizeFlatList( array $decoded ): array {
		$out = array();
		foreach ( $decoded as $row ) {
			if ( is_array( $row ) ) {
				$out[] = $row;
			}
		}

		return $out;
	}
}
