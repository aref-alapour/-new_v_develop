<?php
/**
 * Native PHP session + booking payload from checkout (GET, POST, or WC post_data).
 * WooCommerce update_order_review AJAX does not load theme header; ensure session and $_SESSION['book'] stay in sync locally and on production.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_shop_ensure_session' ) ) {
	/**
	 * Start PHP session once per request (safe for wc-ajax, admin-ajax, CLI skipped).
	 */
	function ez_shop_ensure_session() {
		if ( defined( 'WP_CLI' ) && constant( 'WP_CLI' ) ) {
			return;
		}
		if ( session_status() === PHP_SESSION_ACTIVE ) {
			return;
		}
		if ( headers_sent() ) {
			return;
		}
		session_start();
	}
}

add_action( 'init', 'ez_shop_ensure_session', 1 );

if ( ! function_exists( 'ez_shop_get_booking_details_array_from_request' ) ) {
	/**
	 * Decode booking_details JSON from direct POST or WooCommerce serialized checkout form (post_data).
	 *
	 * @return array<string, mixed>|null
	 */
	function ez_shop_get_booking_details_array_from_request() {
		$raw = null;

		if ( ! empty( $_POST['booking_details'] ) && is_string( $_POST['booking_details'] ) ) {
			$raw = wp_unslash( $_POST['booking_details'] );
		} elseif ( ! empty( $_POST['post_data'] ) && is_string( $_POST['post_data'] ) ) {
			parse_str( wp_unslash( $_POST['post_data'] ), $post_data_array );
			if ( ! empty( $post_data_array['booking_details'] ) && is_string( $post_data_array['booking_details'] ) ) {
				$raw = $post_data_array['booking_details'];
			}
		}

		if ( $raw === null || $raw === '' ) {
			return null;
		}

		$decoded = json_decode( str_replace( '\\', '', $raw ), true );

		return is_array( $decoded ) ? $decoded : null;
	}
}

/**
 * Before cart totals on checkout AJAX: mirror hidden booking_details into $_SESSION so lock/totals match server behavior.
 */
add_action( 'woocommerce_checkout_update_order_review', 'ez_shop_hydrate_native_session_from_checkout_post', 5, 1 );
function ez_shop_hydrate_native_session_from_checkout_post( $post_data_raw ) {
	ez_shop_ensure_session();

	if ( ! is_string( $post_data_raw ) || $post_data_raw === '' ) {
		return;
	}

	parse_str( $post_data_raw, $pd );
	if ( empty( $pd['booking_details'] ) || ! is_string( $pd['booking_details'] ) ) {
		return;
	}

	$decoded = json_decode( str_replace( '\\', '', $pd['booking_details'] ), true );
	if ( ! is_array( $decoded ) || empty( $decoded['book'] ) ) {
		return;
	}

	$_SESSION['book']       = htmlspecialchars( (string) $decoded['book'] );
	$_SESSION['quantity']   = isset( $decoded['quantity'] ) ? htmlspecialchars( (string) $decoded['quantity'] ) : '';
	$_SESSION['product_id'] = isset( $decoded['add-to-cart'] ) ? htmlspecialchars( (string) $decoded['add-to-cart'] ) : '';
	$_SESSION['user_id']    = get_current_user_id();
	$_SESSION['c_time']     = time();
}
