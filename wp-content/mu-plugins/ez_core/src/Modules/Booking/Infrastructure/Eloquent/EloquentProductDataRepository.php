<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Models\ProductData;
use EscapeZoom\Core\Modules\Booking\BookingGatewayDiagnostics;

/**
 * Eloquent read path for products_data (not wired to gateway until P3).
 */
final class EloquentProductDataRepository
{
	public function findByProductId( int $productId ): ?ProductData {
		if ( $productId <= 0 || ! class_exists( ProductData::class ) ) {
			BookingGatewayDiagnostics::log(
				'product_lookup',
				array(
					'product_id'    => $productId,
					'product_found' => false,
					'reason'        => 'invalid_or_model_missing',
				)
			);

			return null;
		}

		$product = ProductData::query()
			->where( 'product_id', $productId )
			->first();

		if ( ! $product instanceof ProductData ) {
			BookingGatewayDiagnostics::log(
				'product_lookup',
				array(
					'product_id'    => $productId,
					'product_found' => false,
				)
			);

			return null;
		}

		$scheduleRaw = $product->getAttribute( 'schedule' );
		$scheduleLen = is_string( $scheduleRaw ) ? strlen( $scheduleRaw ) : 0;

		BookingGatewayDiagnostics::log(
			'product_lookup',
			array(
				'product_id'    => $productId,
				'product_found' => true,
				'active'        => (int) ( $product->getAttribute( 'active' ) ?? 0 ),
				'auto_disable'  => (int) ( $product->getAttribute( 'auto_disable' ) ?? 0 ),
				'schedule_len'  => $scheduleLen,
			)
		);

		return $product;
	}
}
