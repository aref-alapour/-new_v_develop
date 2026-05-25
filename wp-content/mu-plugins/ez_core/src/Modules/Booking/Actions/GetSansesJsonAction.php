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

		return $service->getSanses( $productId, $dayStartTime, $days );
	}
}
