<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

/**
 * Whitelisted booking history queries (replaces raw query_execution over HTTP).
 */
final class BookingQueryService
{
	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function historyRowsForOrder( int $orderId ): array {
		$orderId = (int) $orderId;
		if ( $orderId <= 0 ) {
			return array();
		}

		if ( function_exists( 'ez_booking_query_execution_local' ) ) {
			$raw = ez_booking_query_execution_local(
				(object) array(
					'query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$orderId} ORDER BY `booking_id` DESC",
				)
			);
			$rows = json_decode( (string) $raw, true );

			return is_array( $rows ) ? $rows : array();
		}

		$raw = BookingDispatchService::dispatchType(
			'query_execution',
			array(
				'query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$orderId} ORDER BY `booking_id` DESC",
			)
		);
		$rows = json_decode( (string) $raw, true );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @return array<string,mixed>|null
	 */
	public static function firstHistoryRowForOrder( int $orderId ): ?array {
		$rows = self::historyRowsForOrder( $orderId );

		return isset( $rows[0] ) && is_array( $rows[0] ) ? $rows[0] : null;
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public static function pendingSansesForProduct( int $productId ): array {
		$productId = (int) $productId;
		if ( $productId <= 0 ) {
			return array();
		}

		if ( function_exists( 'ez_booking_get_pending_sanses_local' ) ) {
			$raw = ez_booking_get_pending_sanses_local(
				(object) array( 'product_id' => $productId )
			);
			$rows = json_decode( (string) $raw, true );

			return is_array( $rows ) ? $rows : array();
		}

		$raw = BookingDispatchService::dispatchType(
			'get_pending_sanses',
			array( 'product_id' => $productId )
		);
		$rows = json_decode( (string) $raw, true );

		return is_array( $rows ) ? $rows : array();
	}
}
