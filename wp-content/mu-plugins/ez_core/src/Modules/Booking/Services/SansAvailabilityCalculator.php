<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter;

/**
 * Sans availability — native Eloquent path or legacy dispatch fallback.
 *
 * Set EZ_BOOKING_NATIVE_SANSES true in wp-config when native slot logic passes parity (P3).
 * Alias documented as EZ_BOOKING_READ_ELOQUENT in rule 07.
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

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			return $days > 1 ? array() : array();
		}

		if ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES ) {
			$native = ( new SansAvailabilityService() )->getSanses( $productId, $dayStartTime, $days );
			if ( self::hasSlots( $native, $days ) ) {
				return $native;
			}
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				error_log( '[EZ Booking] Native sans empty; falling back to legacy dispatch' );
			}
		}

		return ( new LegacySansAdapter() )->getSanses( $productId, $dayStartTime, $days );
	}

	/**
	 * @param array<mixed> $result
	 */
	private static function hasSlots( array $result, int $days ): bool {
		if ( 1 === $days ) {
			foreach ( $result as $row ) {
				if ( is_array( $row ) && isset( $row['time'] ) ) {
					return true;
				}
			}

			return false;
		}

		foreach ( $result as $day ) {
			if ( is_array( $day ) && array() !== $day ) {
				return true;
			}
		}

		return false;
	}
}
