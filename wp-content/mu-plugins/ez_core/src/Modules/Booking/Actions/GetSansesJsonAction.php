<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Actions;

use EscapeZoom\Core\Modules\Booking\BookingGatewayDiagnostics;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;
use EscapeZoom\Core\Modules\Booking\Services\BookingService;

/**
 * Gateway action: legacy-compatible flat JSON for single-product calendar.
 */
final class GetSansesJsonAction
{
	/**
	 * @param array<string,mixed> $payload
	 * @return array<int,array<string,mixed>>
	 */
	public function handle( array $payload ): array {
		$productId    = isset( $payload['product_id'] ) ? (int) $payload['product_id'] : 0;
		$dayStartTime = isset( $payload['day_start_time'] ) ? (int) $payload['day_start_time'] : 0;
		$days         = isset( $payload['days'] ) ? (int) $payload['days'] : 1;

		BookingGatewayDiagnostics::log(
			'handle_in',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => $days,
			)
		);

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			BookingReadContext::setReason( 'invalid_input' );
			BookingGatewayDiagnostics::log(
				'handle_out',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'result_count'   => 0,
					'reason'         => 'invalid_input',
				)
			);

			return array();
		}

		$service = new BookingService();
		$result  = $service->getSanses( $productId, $dayStartTime, $days );

		if ( ! is_array( $result ) || array() === $result ) {
			BookingGatewayDiagnostics::log(
				'handle_out',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'result_count'   => 0,
					'path'           => BookingReadContext::getPath(),
					'reason'         => BookingReadContext::getReason(),
				)
			);

			return array();
		}

		// days=1 must be flat [{ time, status, ... }]; days>1 stays nested by day index.
		if ( 1 !== $days ) {
			$count = self::countNestedSlots( $result );
			BookingGatewayDiagnostics::log(
				'handle_out',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'result_count'   => $count,
					'path'           => BookingReadContext::getPath(),
					'reason'         => BookingReadContext::getReason(),
				)
			);

			return $result;
		}

		if ( isset( $result[0] ) && is_array( $result[0] ) && ! isset( $result[0]['time'] ) ) {
			$first = $result[0];
			$flat  = is_array( $first ) ? $first : array();
			$count = self::countFlatSlots( $flat );
			BookingGatewayDiagnostics::log(
				'handle_out',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
					'days'           => $days,
					'result_count'   => $count,
					'path'           => BookingReadContext::getPath(),
					'reason'         => BookingReadContext::getReason(),
				)
			);

			return $flat;
		}

		$count = self::countFlatSlots( $result );
		BookingGatewayDiagnostics::log(
			'handle_out',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => $days,
				'result_count'   => $count,
				'path'           => BookingReadContext::getPath(),
				'reason'         => BookingReadContext::getReason(),
			)
		);

		return $result;
	}

	/**
	 * @param array<mixed> $result
	 */
	private static function countFlatSlots( array $result ): int {
		$count = 0;
		foreach ( $result as $row ) {
			if ( is_array( $row ) && isset( $row['time'] ) ) {
				++$count;
			}
		}

		return $count;
	}

	/**
	 * @param array<mixed> $result
	 */
	private static function countNestedSlots( array $result ): int {
		$count = 0;
		foreach ( $result as $day ) {
			if ( ! is_array( $day ) ) {
				continue;
			}
			foreach ( $day as $row ) {
				if ( is_array( $row ) && isset( $row['time'] ) ) {
					++$count;
				}
			}
		}

		return $count;
	}
}
