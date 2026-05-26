<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Domain;

use EscapeZoom\Core\Models\ProductData;

/**
 * hot_discount, instant_off, off_price (legacy handler 1163–1183).
 */
final class SansPricingResolver
{
	public function hotDiscountPercent( ProductData $product ): int {
		$obj = $product->getDiscountObject();
		if ( null === $obj ) {
			return 0;
		}

		$date = isset( $obj->special_discount_date ) ? (int) $obj->special_discount_date : 0;
		if ( $date > time() ) {
			return (int) ( $obj->special_discount_percentage ?? 0 );
		}

		return 0;
	}

	/**
	 * @param array<string, mixed> $sans Row from schedule.
	 * @return array{off_price: int, instant_off: int|null}
	 */
	public function resolveSlotPricing(
		ProductData $product,
		array $sans,
		string $slotDayType,
		int $firstTimeTs,
		int $hotDiscount
	): array {
		$instantOffExpiryTime = null;
		$instantOffPercentage = 0;

		if ( 0 === $hotDiscount ) {
			$instantOffRoot = $product->getInstantOffDecoded();
			$instantOffData = null;
			if ( is_object( $instantOffRoot ) && isset( $instantOffRoot->{$slotDayType} ) ) {
				$instantOffData = $instantOffRoot->{$slotDayType};
			} elseif ( is_array( $instantOffRoot ) && isset( $instantOffRoot[ $slotDayType ] ) ) {
				$instantOffData = $instantOffRoot[ $slotDayType ];
			}

			if ( null !== $instantOffData ) {
				$hour       = is_object( $instantOffData ) ? ( $instantOffData->hour ?? -1 ) : ( $instantOffData['hour'] ?? -1 );
				$percentage = is_object( $instantOffData ) ? ( $instantOffData->percentage ?? -1 ) : ( $instantOffData['percentage'] ?? -1 );
				if ( -1 !== (int) $hour && -1 !== (int) $percentage ) {
					$now = time();
					if ( $firstTimeTs - $now <= (int) $hour * 3600 ) {
						$instantOffPercentage  = (int) $percentage;
						$instantOffExpiryTime    = $firstTimeTs;
					}
				}
			}
		}

		$basePrice = ! empty( $sans['off_price'] )
			? (float) $sans['off_price']
			: (float) ( $sans['price'] ?? 0 );
		$listPrice = (float) ( $sans['price'] ?? 0 );

		$discountFinal = $basePrice * ( 1 - ( $hotDiscount + $instantOffPercentage ) / 100 );
		$discountFinal = $discountFinal !== $listPrice ? (int) round( $discountFinal ) : 0;

		return array(
			'off_price'   => $discountFinal,
			'instant_off' => $instantOffExpiryTime,
		);
	}
}
