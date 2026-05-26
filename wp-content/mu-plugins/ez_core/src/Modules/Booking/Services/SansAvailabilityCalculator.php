<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Modules\Booking\BookingGatewayDiagnostics;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;
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

		BookingGatewayDiagnostics::log(
			'path_start',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => $days,
				'native_flag'    => defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES,
			)
		);

		if ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES ) {
			$native = ( new SansAvailabilityService() )->getSanses( $productId, $dayStartTime, $days );
			$nativeCount = self::countSlots( $native, $days );

			BookingGatewayDiagnostics::log(
				'native_result',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'count'          => $nativeCount,
				)
			);

			if ( self::hasSlots( $native, $days ) ) {
				BookingReadContext::setPath( 'native' );
				BookingReadContext::setCount( $nativeCount );
				BookingReadContext::setReason( 'success' );
				BookingGatewayDiagnostics::log(
					'final_count',
					array(
						'path'           => 'native',
						'product_id'     => $productId,
						'day_start_time' => $dayStartTime,
						'days'           => $days,
						'count'          => $nativeCount,
					)
				);

				return $native;
			}

			BookingGatewayDiagnostics::log(
				'fallback_to_legacy',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'native_count'   => $nativeCount,
					'reason'         => BookingReadContext::getReason() ?: 'native_empty',
				)
			);
		}

		$legacy = ( new LegacySansAdapter() )->getSanses( $productId, $dayStartTime, $days );
		$legacyCount = self::countSlots( $legacy, $days );

		$path = ( defined( 'EZ_BOOKING_NATIVE_SANSES' ) && EZ_BOOKING_NATIVE_SANSES )
			? 'native_empty_fallback'
			: 'legacy';

		BookingReadContext::setPath( $path );
		BookingReadContext::setCount( $legacyCount );
		if ( 0 === $legacyCount && null === BookingReadContext::getReason() ) {
			BookingReadContext::setReason( 'legacy_empty' );
		}

		BookingGatewayDiagnostics::log(
			'final_count',
			array(
				'path'           => $path,
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => $days,
				'count'          => $legacyCount,
				'reason'         => BookingReadContext::getReason(),
			)
		);

		return $legacy;
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
