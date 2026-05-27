<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

/**
 * Invalidate wp_cache entries for booking reads after writes.
 */
final class BookingCacheInvalidator
{
	private const CACHE_GROUP = 'ez_booking';

	public static function invalidateSansDay( int $productId, int $dayStartTime, int $days = 1 ): void {
		if ( $productId <= 0 || $dayStartTime <= 0 || ! function_exists( 'wp_cache_delete' ) ) {
			return;
		}

		$days = max( 1, $days );
		$key  = "ez_sanses_{$productId}_{$dayStartTime}_{$days}";
		wp_cache_delete( $key, self::CACHE_GROUP );

		if ( 1 === $days ) {
			wp_cache_delete( "ez_sanses_{$productId}_{$dayStartTime}_7", self::CACHE_GROUP );
		}
	}

	public static function invalidateProduct( int $productId ): void {
		if ( $productId <= 0 || ! function_exists( 'wp_cache_flush_group' ) ) {
			return;
		}

		wp_cache_flush_group( self::CACHE_GROUP );
	}
}
