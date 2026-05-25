<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Modules\Booking\Services\BookingService;

/**
 * Reads sans availability via BookingService (internal dispatch, cached).
 */
final class BookingAvailabilityService
{
	/**
	 * @return array<int,array<string,mixed>>|array<int,array<int,array<string,mixed>>>
	 */
	public static function getSanses( int $productId, int $dayStartTime, int $days = 1 ) {
		return ( new BookingService() )->getSanses( $productId, $dayStartTime, $days );
	}
}
