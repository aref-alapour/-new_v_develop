<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Domain;

/**
 * Map booking history + lock times to legacy status string.
 */
final class SansStatusResolver
{
	/**
	 * @param array<string, array{status: int|string, booking_time: int|string}> $orderObjs
	 * @param array<int, int> $activeLockTimes Legacy get_sans_lock returns [] in dispatch; pass empty for parity.
	 */
	public function resolve(
		int $slotTs,
		array $orderObjs,
		array $activeLockTimes
	): string {
		$key      = (string) $slotTs;
		$orderObj = $orderObjs[ $key ] ?? null;

		if ( null !== $orderObj && (int) $orderObj['status'] === 1 ) {
			return 'reserved';
		}
		if ( null !== $orderObj && (int) $orderObj['status'] === 2 ) {
			return 'non_reservable';
		}
		if ( in_array( $slotTs, $activeLockTimes, true ) ) {
			return 'reserving';
		}

		return 'reservable';
	}
}
