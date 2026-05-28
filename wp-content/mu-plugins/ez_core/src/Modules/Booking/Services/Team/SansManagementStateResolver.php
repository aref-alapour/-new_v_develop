<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

/**
 * Deterministic slot status resolution for sans-management.
 */
final class SansManagementStateResolver
{
	/**
	 * @param array<string, mixed> $fetch
	 * @return array<string, mixed>
	 */
	public static function buildDayPayload( array $fetch ): array {
		$productId    = (int) ( $fetch['product_id'] ?? 0 );
		$dayStartTime = (int) ( $fetch['day_start_time'] ?? 0 );
		$daySlots     = is_array( $fetch['day_slots'] ?? null ) ? $fetch['day_slots'] : array();
		$orders       = is_array( $fetch['orders'] ?? null ) ? $fetch['orders'] : array();
		$activeLocks  = is_array( $fetch['active_locks'] ?? null ) ? $fetch['active_locks'] : array();

		$reservationData = array();
		foreach ( $daySlots as $slot ) {
			$firstTimeTs = (int) $slot['ts'];
			$orderObj    = $orders[ (string) $firstTimeTs ] ?? null;
			$status      = 'closeable';
			$reserved    = null;

			if ( is_array( $orderObj ) && isset( $orderObj['status'] ) && 1 === (int) $orderObj['status'] ) {
				$status   = 'reserved';
				$reserved = array(
					'customer_id' => (int) ( $orderObj['customer_id'] ?? 0 ),
					'name'        => (string) ( $orderObj['name'] ?? '' ),
					'level'       => (int) ( $orderObj['level'] ?? 0 ),
					'phone'       => (string) ( $orderObj['phone'] ?? '' ),
					'quantity'    => (int) ( $orderObj['quantity'] ?? 0 ),
					'order_id'    => (int) ( $orderObj['wc_order_id'] ?? 0 ),
				);
			} elseif ( is_array( $orderObj ) && isset( $orderObj['status'] ) && 2 === (int) $orderObj['status'] ) {
				$status = 'openable';
			} elseif ( isset( $activeLocks[ $firstTimeTs ] ) ) {
				$status = 'reserving';
			}

			$reservationData[] = array(
				'time'          => $firstTimeTs,
				'time_lbl'      => TeamSansBridge::formatJalaliTime( $firstTimeTs ),
				'status'        => $status,
				'reserved_data' => $reserved,
			);
		}

		$mojMap = SansManagementPresenter::buildMojavezedarMap( $reservationData );
		foreach ( $reservationData as &$row ) {
			if ( 'reserved' === $row['status'] && is_array( $row['reserved_data'] ) ) {
				$cid = (int) ( $row['reserved_data']['customer_id'] ?? 0 );
				$row['reserved_data']['is_mojavezedar'] = ! empty( $mojMap[ $cid ] );
				$theme = SansManagementPresenter::resolveTheme(
					$row['reserved_data'],
					! empty( $mojMap[ $cid ] )
				);
				$row['reserved_data']['level_title'] = $theme['text'];
				$row['reserved_data']['level_color'] = $theme['color'];
			}
		}
		unset( $row );

		$totalChangeable = 0;
		$totalClosed     = 0;
		foreach ( $reservationData as $row ) {
			if ( 'closeable' === $row['status'] || 'openable' === $row['status'] ) {
				++$totalChangeable;
				if ( 'openable' === $row['status'] ) {
					++$totalClosed;
				}
			}
		}

		return array(
			'product_id'       => $productId,
			'day_start_time'   => $dayStartTime,
			'is_all_closed'    => $totalChangeable > 0 && $totalChangeable === $totalClosed,
			'reservation_data' => $reservationData,
		);
	}

	/**
	 * @param array<string,mixed>|null $current
	 * @param array<string,mixed> $candidate
	 * @return array<string,mixed>
	 */
	public static function resolveEffectiveSlotRow( ?array $current, array $candidate ): array {
		if ( null === $current ) {
			return $candidate;
		}

		$currentStatus   = (int) ( $current['status'] ?? 0 );
		$candidateStatus = (int) ( $candidate['status'] ?? 0 );

		if ( $candidateStatus !== $currentStatus ) {
			if ( 1 === $candidateStatus ) {
				return $candidate;
			}
			if ( 1 === $currentStatus ) {
				return $current;
			}
		}

		$currentBooked   = (int) ( $current['booked_time'] ?? 0 );
		$candidateBooked = (int) ( $candidate['booked_time'] ?? 0 );
		if ( $candidateBooked > $currentBooked ) {
			return $candidate;
		}
		if ( $candidateBooked < $currentBooked ) {
			return $current;
		}

		$currentOrder   = (int) ( $current['wc_order_id'] ?? 0 );
		$candidateOrder = (int) ( $candidate['wc_order_id'] ?? 0 );

		return $candidateOrder >= $currentOrder ? $candidate : $current;
	}
}
