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
}
