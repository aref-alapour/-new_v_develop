<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Models\BookingHistory;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\EloquentBookingLockRepository;

/**
 * Query layer for team/panel sans-management grid.
 */
final class SansManagementDataFetcher
{
	private const LOCK_TTL_SECONDS = 300;

	/**
	 * @return array<string, mixed>
	 */
	public static function fetchDay( int $productId, int $dayStartTime ): array {
		if ( ! CapsuleManager::hasExternalConnection() ) {
			throw new \RuntimeException( 'External DB unavailable' );
		}

		$product = TeamSansBridge::getProductRow( $productId );
		if ( null === $product ) {
			return array(); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing
		}

		$dayType     = TeamSansBridge::getDayType( $dayStartTime );
		$sanses      = TeamSansBridge::getSansesFromRow( $product );
		$scheduleKey = ( 'closed' === $dayType || ! isset( $sanses[ $dayType ] ) ) ? null : $dayType;
		$sansForDay  = ( null !== $scheduleKey && isset( $sanses[ $scheduleKey ] ) ) ? $sanses[ $scheduleKey ] : array();
		$daySlots    = TeamSansBridge::buildDaySlots( $dayStartTime, $scheduleKey, $sansForDay );

		$tsList = array_column( $daySlots, 'ts' );
		$orders = array();
		$activeLocks = array();

		if ( array() !== $tsList ) {
			$bookingRows = BookingHistory::query()
				->where( 'room_id', $productId )
				->whereIn( 'booking_time', $tsList )
				->whereIn( 'status', array( 1, 2 ) )
				->get(
					array(
						'customer_id',
						'wc_order_id',
						'status',
						'booking_time',
						'booked_time',
						'name',
						'level',
						'phone',
						'quantity',
					)
				);

			foreach ( $bookingRows as $row ) {
				$bookingTime = (int) ( $row->booking_time ?? 0 );
				if ( $bookingTime <= 0 ) {
					continue;
				}
				$key       = (string) $bookingTime;
				$candidate = method_exists( $row, 'toArray' ) ? $row->toArray() : (array) $row;
				$current   = $orders[ $key ] ?? null;
				$orders[ $key ] = SansManagementStateResolver::resolveEffectiveSlotRow(
					is_array( $current ) ? $current : null,
					$candidate
				);
			}

			$activeLocks = self::activeLockTimes( $productId, $tsList );
		}

		return array(
			'product_id'     => $productId,
			'day_start_time' => $dayStartTime,
			'day_slots'      => $daySlots,
			'orders'         => $orders,
			'active_locks'   => $activeLocks,
		);
	}

	/**
	 * @param list<int> $tsList
	 * @return array<int, true>
	 */
	private static function activeLockTimes( int $productId, array $tsList ): array {
		if ( $productId <= 0 || array() === $tsList ) {
			return array();
		}

		$repo  = new EloquentBookingLockRepository();
		$now   = time();
		$locks = $repo->forProductTimesActive( $productId, $tsList, $now - self::LOCK_TTL_SECONDS );
		$out   = array();
		$tsSet = array_fill_keys( array_map( 'intval', $tsList ), true );

		foreach ( $locks as $lock ) {
			$bookingTime = (int) ( $lock->booking_time ?? 0 );
			if ( $bookingTime <= 0 || ! isset( $tsSet[ $bookingTime ] ) ) {
				continue;
			}
			$out[ $bookingTime ] = true;
		}

		return $out;
	}
}
