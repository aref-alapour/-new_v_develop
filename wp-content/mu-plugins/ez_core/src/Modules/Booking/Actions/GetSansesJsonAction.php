<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Actions;

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

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			return array();
		}

		$service = new BookingService();
		$result  = $service->getSanses( $productId, $dayStartTime, $days );

		if ( ! is_array( $result ) || array() === $result ) {
			return array();
		}

		// days=1 must be flat [{ time, status, ... }]; days>1 stays nested by day index.
		if ( 1 !== $days ) {
			return $result;
		}

		if ( isset( $result[0] ) && is_array( $result[0] ) && ! isset( $result[0]['time'] ) ) {
			$first = $result[0];

			return is_array( $first ) ? $first : array();
		}

		return $result;
	}
}
