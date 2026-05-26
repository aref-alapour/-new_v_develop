<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Models\BookingHistory;
use EscapeZoom\Core\Models\ProductData;
use EscapeZoom\Core\Modules\Booking\Domain\DaySlotBuilder;
use EscapeZoom\Core\Modules\Booking\Domain\DayTypeResolver;
use EscapeZoom\Core\Modules\Booking\Domain\SansPricingResolver;
use EscapeZoom\Core\Modules\Booking\Domain\SansStatusResolver;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\EloquentBookingHistoryRepository;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\EloquentProductDataRepository;

/**
 * Native get_sanses (no web-service bootstrap). Eloquent + domain helpers.
 */
final class SansAvailabilityService
{
	public function __construct(
		private ?EloquentProductDataRepository $products = null,
		private ?EloquentBookingHistoryRepository $history = null,
		private ?DayTypeResolver $dayTypes = null,
		private ?DaySlotBuilder $slotBuilder = null,
		private ?SansStatusResolver $statusResolver = null,
		private ?SansPricingResolver $pricingResolver = null
	) {
		$this->products        = $products ?? new EloquentProductDataRepository();
		$this->history         = $history ?? new EloquentBookingHistoryRepository();
		$this->dayTypes        = $dayTypes ?? new DayTypeResolver();
		$this->slotBuilder     = $slotBuilder ?? new DaySlotBuilder();
		$this->statusResolver  = $statusResolver ?? new SansStatusResolver();
		$this->pricingResolver = $pricingResolver ?? new SansPricingResolver();
	}

	/**
	 * @return array<int, array<string, mixed>>|array<int, array<int, array<string, mixed>>>
	 */
	public function getSanses( int $productId, int $dayStartTime, int $days = 1 ): array {
		$productId    = (int) $productId;
		$dayStartTime = (int) $dayStartTime;
		$days         = max( 1, (int) $days );

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			return array();
		}

		if ( ! CapsuleManager::isBooted() ) {
			CapsuleManager::boot();
		}

		if ( ! $this->hasExternalConnection() ) {
			$hint = function_exists( 'ez_reservation_db_last_error' ) ? ez_reservation_db_last_error() : '';
			error_log(
				'[EZ Booking] Native sans: external DB not available'
				. ( '' !== $hint ? ' — ' . $hint : '' )
			);

			return array();
		}

		$product = $this->products->findByProductId( $productId );
		if ( ! $product instanceof ProductData ) {
			return array();
		}

		$previousTz = date_default_timezone_get();
		date_default_timezone_set( 'Asia/Tehran' );

		try {
			return $this->buildSanses( $product, $productId, $dayStartTime, $days );
		} finally {
			date_default_timezone_set( $previousTz );
		}
	}

	/**
	 * @return array<int, array<string, mixed>>|array<int, array<int, array<string, mixed>>>
	 */
	private function buildSanses( ProductData $product, int $productId, int $dayStartTime, int $days ): array {
		$hotDiscount  = $this->pricingResolver->hotDiscountPercent( $product );
		$autoDisable  = time() + (int) ( $product->getAttribute( 'auto_disable' ) ?? 0 ) * 60;
		$sanses       = $product->getScheduleForSans();
		$lockTimes    = array(); // P3.1: legacy dispatch get_sans_lock() always [] — parity.

		$daysTimeArr = array();
		for ( $i = 0; $i < $days; $i++ ) {
			$daysTimeArr[] = $dayStartTime + ( $i * 86400 );
		}

		$reservationData = array();

		foreach ( $daysTimeArr as $key => $timeRes ) {
			$dayType      = $this->dayTypes->resolve( (int) $timeRes );
			$scheduleKey  = ( 'closed' === $dayType || ! isset( $sanses[ $dayType ] ) ) ? null : $dayType;
			$reservationData[ $key ] = array();

			if ( null === $scheduleKey ) {
				continue;
			}

			$daySlots = $this->slotBuilder->buildForDay( (int) $timeRes, $scheduleKey, $sanses );
			if ( array() === $daySlots ) {
				continue;
			}

			$sansesList = array_column( $daySlots, 'ts' );
			$orderObjs  = $this->loadOrderObjects( $productId, $sansesList );

			foreach ( $daySlots as $slot ) {
				$firstTimeTs   = (int) $slot['ts'];
				$sans          = $slot['sans'];
				$slotDayType   = (string) $slot['day_type'];

				if ( $firstTimeTs < $autoDisable ) {
					continue;
				}

				$status = $this->statusResolver->resolve( $firstTimeTs, $orderObjs, $lockTimes );
				$pricing = $this->pricingResolver->resolveSlotPricing(
					$product,
					$sans,
					$slotDayType,
					$firstTimeTs,
					$hotDiscount
				);

				$reservationData[ $key ][] = array(
					'time'        => $firstTimeTs,
					'price'       => (int) ( $sans['price'] ?? 0 ),
					'off_price'   => $pricing['off_price'],
					'status'      => $status,
					'instant_off' => $pricing['instant_off'],
				);
			}
		}

		if ( array() === $reservationData ) {
			return array();
		}

		if ( 1 === count( $reservationData ) ) {
			return $reservationData[0];
		}

		return array_values( $reservationData );
	}

	/**
	 * @param array<int, int> $sansesList
	 * @return array<string, array{status: int|string, booking_time: int|string}>
	 */
	private function loadOrderObjects( int $productId, array $sansesList ): array {
		$rows = $this->history->forRoomAndTimes( $productId, $sansesList );
		$orderObjs = array();

		foreach ( $rows as $row ) {
			if ( $row instanceof BookingHistory ) {
				$orderObjs[ (string) $row->booking_time ] = array(
					'status'       => $row->status,
					'booking_time' => $row->booking_time,
				);
			}
		}

		return $orderObjs;
	}

	private function hasExternalConnection(): bool {
		if ( ! CapsuleManager::isBooted() ) {
			return false;
		}

		try {
			CapsuleManager::connection( 'external' )->getPdo();

			return true;
		} catch ( \Throwable $e ) {
			return false;
		}
	}
}
