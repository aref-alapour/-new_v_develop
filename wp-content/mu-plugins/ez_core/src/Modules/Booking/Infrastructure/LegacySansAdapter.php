<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure;

use EscapeZoom\Core\Modules\Booking\BookingDispatchService;
use EscapeZoom\Core\Modules\Booking\BookingGatewayDiagnostics;

/**
 * Reads sans list via internal reservation dispatch (no HTTP loopback).
 */
final class LegacySansAdapter
{
	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function getSanses( int $productId, int $dayStartTime, int $days = 1 ): array {
		$productId    = (int) $productId;
		$dayStartTime = (int) $dayStartTime;
		$days         = max( 1, (int) $days );

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			return array();
		}

		$viaEzReservation = function_exists( 'ez_reservation' );
		$raw              = '';

		if ( $viaEzReservation ) {
			$raw = (string) ez_reservation(
				array(
					'type' => 'get_sanses',
					'data' => array(
						'product_id'     => $productId,
						'day_start_time' => $dayStartTime,
						'days'           => $days,
					),
				)
			);
		} else {
			$raw = BookingDispatchService::dispatchType(
				'get_sanses',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
				)
			);
		}

		$decoded = json_decode( $raw, true );
		if ( ! is_array( $decoded ) ) {
			BookingGatewayDiagnostics::log(
				'legacy_result',
				array(
					'product_id'              => $productId,
					'day_start_time'          => $dayStartTime,
					'days'                    => $days,
					'ez_reservation_available' => $viaEzReservation,
					'legacy_raw_len'          => strlen( $raw ),
					'decoded_count'           => 0,
					'reason'                  => 'invalid_json',
				)
			);

			return array();
		}

		// days=1 → flat list; days>1 → nested by day index.
		if ( $days === 1 ) {
			$out = $this->normalizeFlatList( $decoded );
			BookingGatewayDiagnostics::log(
				'legacy_result',
				array(
					'product_id'              => $productId,
					'day_start_time'          => $dayStartTime,
					'days'                    => $days,
					'ez_reservation_available' => $viaEzReservation,
					'legacy_raw_len'          => strlen( $raw ),
					'decoded_count'           => count( $out ),
				)
			);

			return $out;
		}

		$out = array();
		foreach ( $decoded as $day ) {
			$out[] = is_array( $day ) ? $this->normalizeFlatList( $day ) : array();
		}

		$count = 0;
		foreach ( $out as $day ) {
			$count += count( $day );
		}

		BookingGatewayDiagnostics::log(
			'legacy_result',
			array(
				'product_id'              => $productId,
				'day_start_time'          => $dayStartTime,
				'days'                    => $days,
				'ez_reservation_available' => $viaEzReservation,
				'legacy_raw_len'          => strlen( $raw ),
				'decoded_count'           => $count,
			)
		);

		return $out;
	}

	/**
	 * @param array<mixed> $decoded
	 * @return array<int,array<string,mixed>>
	 */
	private function normalizeFlatList( array $decoded ): array {
		$out = array();
		foreach ( $decoded as $row ) {
			if ( is_array( $row ) ) {
				$out[] = $row;
			}
		}

		return $out;
	}
}
