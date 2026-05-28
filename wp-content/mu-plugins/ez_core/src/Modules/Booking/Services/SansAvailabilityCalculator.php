<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Modules\Booking\BookingGatewayDiagnostics;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;

/**
 * Sans availability — native Eloquent path only (Project-only reservation mode).
 */
final class SansAvailabilityCalculator
{
	/**
	 * @return array<int,array<string,mixed>>|array<int,array<int,array<string,mixed>>>
	 */
	public function getSanses( int $productId, int $dayStartTime, int $days = 1 ) {
		$productId    = (int) $productId;
		$dayStartTime = (int) $dayStartTime;
		$days         = max( 1, (int) $days );

		BookingReadContext::reset();

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			BookingReadContext::setReason( 'invalid_input' );
			BookingGatewayDiagnostics::log(
				'path_start',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'reason'         => 'invalid_input',
				)
			);

			return $days > 1 ? array() : array();
		}

		$native = ( new SansAvailabilityService() )->getSanses( $productId, $dayStartTime, $days );
		$count  = self::countSlots( $native, $days );

		BookingReadContext::setPath( 'native' );
		BookingReadContext::setCount( $count );
		if ( 0 === $count && null === BookingReadContext::getReason() ) {
			BookingReadContext::setReason( 'native_empty' );
		}

		BookingGatewayDiagnostics::log(
			'final_count',
			array(
				'path'           => 'native',
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => $days,
				'count'          => $count,
				'reason'         => BookingReadContext::getReason(),
			)
		);

		return $native;
	}

	/**
	 * @param array<mixed> $result
	 */
	private static function hasSlots( array $result, int $days ): bool {
		return self::countSlots( $result, $days ) > 0;
	}

	/**
	 * @param array<mixed> $result
	 */
	private static function countSlots( array $result, int $days ): int {
		if ( 1 === $days ) {
			$count = 0;
			foreach ( $result as $row ) {
				if ( is_array( $row ) && isset( $row['time'] ) ) {
					++$count;
				}
			}

			return $count;
		}

		$count = 0;
		foreach ( $result as $day ) {
			if ( ! is_array( $day ) ) {
				continue;
			}
			foreach ( $day as $row ) {
				if ( is_array( $row ) && isset( $row['time'] ) ) {
					++$count;
				}
			}
		}

		return $count;
	}
}
