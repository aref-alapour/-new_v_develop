<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure;

/**
 * Reads product schedule metadata from escapezo_queries.products_data.
 */
final class ProductsDataRepository
{
	/**
	 * @return array<string,mixed>|null
	 */
	public function findByProductId( int $productId ): ?array {
		if ( $productId <= 0 ) {
			return null;
		}

		$conn = $this->connection();
		if ( ! ( $conn instanceof \mysqli ) ) {
			return null;
		}

		$pid    = (int) $productId;
		$result = $conn->query( "SELECT * FROM products_data WHERE product_id LIKE {$pid}" );
		if ( ! $result || $result->num_rows < 1 ) {
			return null;
		}

		$rows = $result->fetch_all( MYSQLI_ASSOC );
		if ( ! is_array( $rows ) || ! isset( $rows[0] ) || ! is_array( $rows[0] ) ) {
			return null;
		}

		return $rows[0];
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

	private function connection(): ?\mysqli {
		if ( ! function_exists( 'ez_reservation_db_connect' ) ) {
			$path = defined( 'ABSPATH' ) ? ABSPATH . 'web-service/db-connect.php' : '';
			if ( '' !== $path && is_readable( $path ) ) {
				require_once $path;
			}
		}

		if ( function_exists( 'ez_reservation_get_conn' ) ) {
			return ez_reservation_get_conn();
		}

		if ( function_exists( 'ez_reservation_db_connect' ) ) {
			return ez_reservation_db_connect();
		}

		global $conn;

		return ( $conn instanceof \mysqli ) ? $conn : null;
	}
}
