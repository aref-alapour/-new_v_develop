<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;

/**
 * Reads product schedule metadata from escapezo_queries.products_data.
 */
final class ProductsDataRepository
{
	/** @var array<int, array<string,mixed>|null> */
	private static array $rowCache = array();

	/**
	 * @return array<string,mixed>|null
	 */
	public function findByProductId( int $productId ): ?array {
		if ( $productId <= 0 ) {
			return null;
		}
		if ( array_key_exists( $productId, self::$rowCache ) ) {
			return self::$rowCache[ $productId ];
		}

		if ( ! CapsuleManager::hasExternalConnection() ) {
			self::$rowCache[ $productId ] = null;
			return null;
		}

		try {
			$row = CapsuleManager::connection( 'external' )
				->table( 'products_data' )
				->where( 'product_id', (int) $productId )
				->first( array( 'product_id', 'schedule', 'discount_data', 'auto_disable' ) );
		} catch ( \Throwable $e ) {
			self::$rowCache[ $productId ] = null;
			return null;
		}

		if ( null === $row ) {
			self::$rowCache[ $productId ] = null;
			return null;
		}

		self::$rowCache[ $productId ] = (array) $row;

		return self::$rowCache[ $productId ];
	}

	/**
	 * Unserialized schedule map (normals / holidays / …).
	 *
	 * @return array<string,mixed>
	 */
	public function getSchedule( int $productId ): array {
		$row = $this->findByProductId( $productId );
		if ( null === $row || empty( $row['schedule'] ) ) {
			return array();
		}

		$schedule = @unserialize( (string) $row['schedule'] );
		if ( ! is_array( $schedule ) ) {
			return array();
		}

		return $schedule;
	}

	/**
	 * Active special discount percent (0 if none / expired).
	 */
	public function getHotDiscountPercent( int $productId ): int {
		$row = $this->findByProductId( $productId );
		if ( null === $row || empty( $row['discount_data'] ) ) {
			return 0;
		}

		$discount = @unserialize( (string) $row['discount_data'] );
		if ( ! is_object( $discount ) ) {
			return 0;
		}

		$expires = isset( $discount->special_discount_date ) ? (int) $discount->special_discount_date : 0;
		if ( $expires <= time() ) {
			return 0;
		}

		return (int) ( $discount->special_discount_percentage ?? 0 );
	}

	/**
	 * Unix timestamp after which new bookings auto-disable.
	 */
	public function getAutoDisableUntil( int $productId ): int {
		$row = $this->findByProductId( $productId );
		if ( null === $row ) {
			return time();
		}

		$minutes = (int) ( $row['auto_disable'] ?? 0 );

		return time() + ( $minutes * 60 );
	}

}
