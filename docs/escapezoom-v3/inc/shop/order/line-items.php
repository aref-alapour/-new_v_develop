<?php
/**
 * Helpers for resolving the "primary" bookable line item on a WooCommerce order.
 *
 * Background:
 *   The booking pipeline, marketing sync, and team-panel recovery flows all
 *   need a single (product_id, quantity) tuple per order. Previously each
 *   call site used its own ad-hoc foreach (sometimes overwriting on every
 *   iteration), which silently broke multi-item orders.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_order_primary_bookable_line_item' ) ) {
	/**
	 * Return the first WC_Order_Item_Product line on an order as a
	 * [product_id, quantity] tuple. Falls back to [null, 0] when no product
	 * line item is found (very edge-case: empty/anonymized orders).
	 *
	 * @param WC_Order $order
	 * @return array{0:int|null,1:int}
	 */
	function ez_order_primary_bookable_line_item( WC_Order $order ) {
		foreach ( $order->get_items( 'line_item' ) as $item ) {
			if ( ! $item instanceof WC_Order_Item_Product ) {
				continue;
			}
			$pid = (int) $item->get_product_id();
			if ( $pid > 0 ) {
				return array( $pid, max( 1, (int) $item->get_quantity() ) );
			}
		}
		return array( null, 0 );
	}
}
