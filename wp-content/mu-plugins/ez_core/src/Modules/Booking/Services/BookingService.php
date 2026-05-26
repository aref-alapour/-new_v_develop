<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

/**
 * Booking availability (sans list) with short-lived object cache.
 */
final class BookingService
{
	private const CACHE_GROUP = 'ez_booking';

	private const CACHE_TTL = 45;

	/** @var array<string, array<int, array<string, mixed>>> */
	private static array $requestCache = array();

	/**
	 * Same contract as legacy get_sanses with days=1 (flat array).
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function getSanses( int $productId, int $dayStartTime, int $days = 1 ): array {
		$productId    = (int) $productId;
		$dayStartTime = (int) $dayStartTime;
		$days         = max( 1, (int) $days );

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			return array();
		}

		$cacheKey = "ez_sanses_{$productId}_{$dayStartTime}_{$days}";
		if ( isset( self::$requestCache[ $cacheKey ] ) ) {
			return self::$requestCache[ $cacheKey ];
		}
		$cached = function_exists( 'wp_cache_get' ) ? wp_cache_get( $cacheKey, self::CACHE_GROUP ) : false;
		if ( is_array( $cached ) && array() !== $cached ) {
			self::$requestCache[ $cacheKey ] = $cached;

			return $cached;
		}

		$result = ( new SansAvailabilityCalculator() )->getSanses( $productId, $dayStartTime, $days );
		if ( ! is_array( $result ) ) {
			$result = array();
		}

		// Never cache empty — avoids sticky [] after a transient DB outage.
		if ( array() !== $result && self::resultHasSlots( $result, $days ) ) {
			self::$requestCache[ $cacheKey ] = $result;
			if ( function_exists( 'wp_cache_set' ) ) {
				wp_cache_set( $cacheKey, $result, self::CACHE_GROUP, self::CACHE_TTL );
			}
		}

		return $result;
	}

	/**
	 * @param array<mixed> $result
	 */
	private static function resultHasSlots( array $result, int $days ): bool {
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
