<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Modules\Booking\Infrastructure\LegacySansAdapter;

/**
 * Booking availability (sans list) with short-lived object cache.
 */
final class BookingService
{
	private const CACHE_GROUP = 'ez_booking';

	private const CACHE_TTL = 45;

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
		$cached   = wp_cache_get( $cacheKey, self::CACHE_GROUP );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$adapter = new LegacySansAdapter();
		$result  = $adapter->getSanses( $productId, $dayStartTime, $days );

		wp_cache_set( $cacheKey, $result, self::CACHE_GROUP, self::CACHE_TTL );

		return $result;
	}
}
