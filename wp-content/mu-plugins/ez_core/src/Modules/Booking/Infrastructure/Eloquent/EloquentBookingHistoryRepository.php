<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Models\BookingHistory;
use Illuminate\Support\Collection;

/**
 * Booking rows by room and slot timestamps (P3 sans availability).
 */
final class EloquentBookingHistoryRepository
{
	/**
	 * @param array<int, int> $bookingTimes Unix timestamps.
	 * @return Collection<int, BookingHistory>
	 */
	public function forRoomAndTimes( int $roomId, array $bookingTimes ): Collection {
		if ( $roomId <= 0 ) {
			return new Collection();
		}

		$query = BookingHistory::query()->where( 'room_id', $roomId );

		if ( array() !== $bookingTimes ) {
			$query->whereIn( 'booking_time', array_map( 'intval', $bookingTimes ) );
		}

		return $query->get();
	}
}
