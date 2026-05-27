<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Models\BookingHistory;
use EscapeZoom\Core\Models\BookingLock;
use EscapeZoom\Core\Modules\Booking\Services\BookingCacheInvalidator;

/**
 * Native open/close sans writes (replaces legacy dispatch for toggle actions).
 */
final class TeamSansWriteService
{
	private const LOCK_TTL_SECONDS = 300;

	/**
	 * @return array{new_status: string, error_message: string, success_message: string}
	 */
	public static function openSans( int $productId, int $sansTime ): array {
		self::assertExternalDb();

		BookingHistory::query()
			->where( 'room_id', $productId )
			->where( 'booking_time', $sansTime )
			->where( 'status', 2 )
			->delete();

		self::invalidateCaches( $productId, $sansTime );

		return array(
			'new_status'      => 'closeable',
			'error_message'   => '',
			'success_message' => 'با موفقیت باز شد!',
		);
	}

	/**
	 * @return array{new_status: string, error_message: string, success_message: string}
	 */
	public static function closeSans( int $productId, int $sansTime, int $userId ): array {
		self::assertExternalDb();

		$reserved = BookingHistory::query()
			->where( 'room_id', $productId )
			->where( 'booking_time', $sansTime )
			->where( 'status', 1 )
			->exists();

		if ( $reserved ) {
			return array(
				'new_status'      => 'reserved',
				'error_message'   => 'یک کاربر این سانس را رزرو کرده است.',
				'success_message' => '',
			);
		}

		if ( self::isSlotLocked( $productId, $sansTime ) ) {
			return array(
				'new_status'      => 'reserving',
				'error_message'   => '',
				'success_message' => '',
			);
		}

		$now = time();
		BookingHistory::query()->insert(
			array(
				'customer_id'  => $userId,
				'wc_order_id'  => null,
				'status'       => 2,
				'room_id'      => $productId,
				'booking_time' => $sansTime,
				'booked_time'  => $now,
				'name'         => null,
				'phone'        => null,
				'quantity'     => 0,
			)
		);

		self::invalidateCaches( $productId, $sansTime );

		return array(
			'new_status'      => 'openable',
			'error_message'   => '',
			'success_message' => 'با موفقیت بسته شد!',
		);
	}

	/**
	 * @return array{success: bool, data: list<string|array<string, string>>}
	 */
	public static function openAllSanses( int $productId, int $dayStartTime ): array {
		self::assertExternalDb();

		$slots = self::daySlots( $productId, $dayStartTime );
		if ( array() === $slots ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'هیچ سانسی برای باز شدن وجود ندارد.' ) ),
			);
		}

		$tsList = array_column( $slots, 'ts' );
		$rows   = BookingHistory::query()
			->where( 'room_id', $productId )
			->whereIn( 'booking_time', $tsList )
			->where( 'status', 2 )
			->get( array( 'booking_time' ) );

		if ( $rows->isEmpty() ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'هیچ سانسی برای باز شدن وجود ندارد.' ) ),
			);
		}

		foreach ( $rows as $row ) {
			BookingHistory::query()
				->where( 'room_id', $productId )
				->where( 'booking_time', (int) $row->booking_time )
				->where( 'status', 2 )
				->delete();
		}

		BookingCacheInvalidator::invalidateSansDay( $productId, $dayStartTime );

		return array(
			'success' => true,
			'data'    => array( 'تمام سانس های درخواستی باز شد.' ),
		);
	}

	/**
	 * @return array{success: bool, data: list<string|array<string, string>>}
	 */
	public static function closeAllSanses( int $productId, int $dayStartTime, int $userId ): array {
		self::assertExternalDb();

		$product = TeamSansBridge::getProductRow( $productId );
		if ( null === $product ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'محصول یافت نشد.' ) ),
			);
		}

		$autoDisable = time() + (int) ( $product['auto_disable'] ?? 0 ) * 60;
		$slots       = self::daySlots( $productId, $dayStartTime );
		$ready       = array();

		foreach ( $slots as $slot ) {
			$ts = (int) $slot['ts'];
			if ( $ts < $autoDisable ) {
				continue;
			}

			$row = BookingHistory::query()
				->where( 'room_id', $productId )
				->where( 'booking_time', $ts )
				->first( array( 'status' ) );

			if ( null !== $row && in_array( (int) $row->status, array( 1, 2 ), true ) ) {
				continue;
			}

			if ( self::isSlotLocked( $productId, $ts ) ) {
				continue;
			}

			$ready[] = $ts;
		}

		if ( array() === $ready ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'هیچ سانسی برای بسته شدن وجود ندارد.' ) ),
			);
		}

		$now = time();
		foreach ( $ready as $sansTime ) {
			BookingHistory::query()->insert(
				array(
					'customer_id'  => $userId,
					'wc_order_id'  => null,
					'status'       => 2,
					'room_id'      => $productId,
					'booking_time' => $sansTime,
					'booked_time'  => $now,
					'name'         => null,
					'phone'        => null,
					'quantity'     => 0,
				)
			);
		}

		BookingCacheInvalidator::invalidateSansDay( $productId, $dayStartTime );

		return array(
			'success' => true,
			'data'    => array( 'تمام سانس های درخواستی بسته شد.' ),
		);
	}

	/**
	 * @return list<array{ts: int, sans: array<string, mixed>}>
	 */
	private static function daySlots( int $productId, int $dayStartTime ): array {
		$product = TeamSansBridge::getProductRow( $productId );
		if ( null === $product ) {
			return array();
		}

		$dayType     = TeamSansBridge::getDayType( $dayStartTime );
		$sanses      = TeamSansBridge::getSansesFromRow( $product );
		$scheduleKey = ( 'closed' === $dayType || ! isset( $sanses[ $dayType ] ) ) ? null : $dayType;
		$sansForDay  = ( null !== $scheduleKey && isset( $sanses[ $scheduleKey ] ) ) ? $sanses[ $scheduleKey ] : array();

		return TeamSansBridge::buildDaySlots( $dayStartTime, $scheduleKey, $sansForDay );
	}

	private static function isSlotLocked( int $productId, int $sansTime ): bool {
		if ( ! class_exists( BookingLock::class ) ) {
			return false;
		}

		$lock = BookingLock::query()
			->where( 'product_id', $productId )
			->where( 'booking_time', $sansTime )
			->first( array( 'lock_time' ) );

		if ( null === $lock ) {
			return false;
		}

		$lockTime = (int) ( $lock->lock_time ?? 0 );
		$now      = time();

		return $lockTime > 0 && $now < $lockTime + self::LOCK_TTL_SECONDS;
	}

	private static function invalidateCaches( int $productId, int $sansTime ): void {
		$dayStart = TeamSansBridge::tehranMidnightUnix( $sansTime );
		if ( $dayStart > 0 ) {
			BookingCacheInvalidator::invalidateSansDay( $productId, $dayStart );
		}
	}

	private static function assertExternalDb(): void {
		if ( ! CapsuleManager::hasExternalConnection() ) {
			throw new \RuntimeException( 'External DB unavailable' );
		}
	}
}
