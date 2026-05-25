<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Models\ProductData;

/**
 * Eloquent read path for products_data (not wired to gateway until P3).
 */
final class EloquentProductDataRepository
{
	public function findByProductId( int $productId ): ?ProductData {
		if ( $productId <= 0 || ! class_exists( ProductData::class ) ) {
			return null;
		}

		return ProductData::query()
			->where( 'product_id', $productId )
			->first();
	}
}
