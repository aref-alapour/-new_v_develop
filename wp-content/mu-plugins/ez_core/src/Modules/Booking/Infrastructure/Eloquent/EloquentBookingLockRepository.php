<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Models\BookingLock;
use Illuminate\Support\Collection;

/**
 * Active locks per product (P3).
 */
final class EloquentBookingLockRepository
{
	/**
	 * @return Collection<int, BookingLock>
	 */
	public function forProduct( int $productId ): Collection {
		if ( $productId <= 0 ) {
			return new Collection();
		}

		return BookingLock::query()
			->where( 'product_id', $productId )
			->get();
	}

	/**
	 * @param list<int> $bookingTimes
	 * @return Collection<int, BookingLock>
	 */
	public function forProductTimes( int $productId, array $bookingTimes ): Collection {
		return $this->forProductTimesActive( $productId, $bookingTimes, 0 );
	}

	/**
	 * @param list<int> $bookingTimes
	 * @return Collection<int, BookingLock>
	 */
	public function forProductTimesActive( int $productId, array $bookingTimes, int $minLockTime ): Collection {
		if ( $productId <= 0 || array() === $bookingTimes ) {
			return new Collection();
		}

		$times = array_values(
			array_unique(
				array_filter(
					array_map( 'intval', $bookingTimes ),
					static fn( int $value ): bool => $value > 0
				)
			)
		);
		if ( array() === $times ) {
			return new Collection();
		}

		$query = BookingLock::query()
			->where( 'product_id', $productId )
			->whereIn( 'booking_time', $times );

		if ( $minLockTime > 0 ) {
			$query->where( 'lock_time', '>', $minLockTime );
		}

		return $query->get( array( 'booking_time', 'lock_time' ) );
	}
}
