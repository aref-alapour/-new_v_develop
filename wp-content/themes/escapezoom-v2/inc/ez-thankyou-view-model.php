<?php
/**
 * Read-only bundle of variables for the WooCommerce thankyou ticket template.
 *
 * No writes, redirects, or pipeline hooks. Safe to refresh repeatedly.
 *
 * @package Escapezoom
 */

defined( 'ABSPATH' ) || exit;

/**
 * Build view data for woocommerce/checkout/thankyou.php (render-only HTML).
 *
 * @param int $order_id WooCommerce order ID.
 * @return array<string,mixed>|null  null when ticket cannot be displayed.
 */
function ez_thankyou_view_model( int $order_id ): ?array {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return null;
	}

	$markting_row = function_exists( 'ez_markting_get_row' )
		? ez_markting_get_row( $order_id )
		: null;

	$product_id       = 0;
	$product_quantity = 0;

	if ( is_array( $markting_row ) && ! empty( $markting_row['game_id'] ) ) {
		$product_id       = (int) $markting_row['game_id'];
		$product_quantity = isset( $markting_row['order_tickets_quantity'] ) ? (int) $markting_row['order_tickets_quantity'] : 0;
	}

	if ( ! $product_id || ! $product_quantity ) {
		global $wpdb;
		$items_table = $wpdb->prefix . 'woocommerce_order_items';
		$meta_table  = $wpdb->prefix . 'woocommerce_order_itemmeta';
		$item_id     = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT order_item_id FROM {$items_table} WHERE order_id = %d AND order_item_type = 'line_item' ORDER BY order_item_id ASC LIMIT 1",
				$order_id
			)
		);
		if ( $item_id ) {
			$product_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$meta_table} WHERE order_item_id = %d AND meta_key = '_product_id' LIMIT 1",
					$item_id
				)
			);
			$qty = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT meta_value FROM {$meta_table} WHERE order_item_id = %d AND meta_key = '_qty' LIMIT 1",
					$item_id
				)
			);
			if ( null !== $qty ) {
				$product_quantity = (int) $qty;
			}
		}
	}

	$sans_time = get_post_meta( $order_id, 'sans_time', true );
	if ( empty( $product_id ) || empty( $sans_time ) ) {
		return null;
	}

	// jdate() in thankyou expects numeric unix timestamp.
	$sans_time = (int) $sans_time;

	$ez_payment_type = '';
	if ( is_array( $markting_row ) && isset( $markting_row['order_payment_type'] ) && '' !== (string) $markting_row['order_payment_type'] ) {
		$ez_payment_type = (string) $markting_row['order_payment_type'];
	}
	if ( '' === $ez_payment_type ) {
		$ez_payment_type = (string) get_post_meta( $order_id, 'ez_payment_type', true );
	}

	$game_name = '';
	if ( is_array( $markting_row ) && ! empty( $markting_row['game_name'] ) ) {
		$game_name = (string) $markting_row['game_name'];
	}
	$product_title = '' !== $game_name ? $game_name : get_the_title( $product_id );
	$product_url   = get_permalink( $product_id );

	$prepaid = (int) get_post_meta( $order_id, 'prepaid', true );

	$item_total = (int) get_post_meta( $order_id, 'total_payment', true );
	if ( $item_total <= 0 ) {
		$pish_order = get_post_meta( $order_id, 'ticket_tedad', true );
		$pish_prod  = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
		$pish_per_person = ! empty( $pish_order ) ? (float) $pish_order : (float) $pish_prod;
		$pish_per_person = $pish_per_person > 0 ? $pish_per_person : 1.0;
		if ( 'complete' === $ez_payment_type ) {
			$item_total = $prepaid;
		} else {
			$item_total = (int) round( ( $prepaid / $pish_per_person ) * (int) $product_quantity );
		}
	}

	$rest = max( 0, (int) $item_total - (int) $prepaid );

	$product_meta = function_exists( 'ez_get_product_meta' ) ? ez_get_product_meta( $product_id ) : null;

	$brand_terms = get_the_terms( $product_id, 'yith_product_brand' );
	$brand_data  = null;
	if ( ! empty( $brand_terms ) && ! is_wp_error( $brand_terms ) ) {
		$brand_data = $brand_terms[0];
	}

	$lat_val = '';
	$lng_val = '';
	if ( function_exists( 'get_field' ) ) {
		$lat_raw = get_field( 'room_lat', $product_id );
		$lng_raw = get_field( 'room_long', $product_id );
		// Keep same concatenation semantics as legacy thankyou.php.
		$lat_val = null !== $lat_raw && false !== $lat_raw ? (string) $lat_raw : '';
		$lng_val = null !== $lng_raw && false !== $lng_raw ? (string) $lng_raw : '';
	}
	$geo_directions = home_url( '/geo.php?g=' . $lat_val . ',' . $lng_val );

	return array(
		'order_id'         => $order_id,
		'product_id'       => $product_id,
		'product_title'    => $product_title,
		'product_url'      => $product_url,
		'product_quantity' => $product_quantity,
		'sans_time'        => $sans_time,
		'geo_directions'   => $geo_directions,
		'brand_data'       => $brand_data,
		'product_meta'     => $product_meta,
		'prepaid'          => $prepaid,
		'item_total'       => $item_total,
		'rest'             => $rest,
		'ez_payment_type'  => $ez_payment_type,
	);
}
