<?php
/**
 * Public API + WP AJAX endpoint that replaces the legacy
 * `ez_webservice([ 'type' => 'sort_products_get', 'data' => $args ])` call
 * for all product carousels.
 *
 * Drop-in replacement contract:
 *   ez_products_snapshot_swiper( $args )
 *       → (object){ products: string $html, max_num_pages?: int, products_id?: int[] }
 *
 * Compared to the legacy function:
 *   - reads `wp_products_snapshot` directly (no HTTP round-trip)
 *   - renders `embla__slide` HTML server-side via ez_products_snapshot_render_swiper_slides()
 *   - keeps `format` arg for forward-compat but only honors 'html_swiper' (default).
 */

defined( 'ABSPATH' ) || exit;

/**
 * Drop-in replacement for `json_decode( ez_webservice( ... ) )` carousel fetches.
 *
 * @param array<string,mixed> $args legacy `sort_products_get` data shape.
 */
function ez_products_snapshot_swiper( array $args ): object {
	$rows = ez_products_snapshot_query( $args );

	$products = ez_products_snapshot_render_swiper_slides( $rows );

	$result = (object) array(
		'products'    => $products,
		'products_id' => array_map( static fn( $r ) => (int) ( $r['product_id'] ?? 0 ), $rows ),
	);

	if ( isset( $GLOBALS['ez_products_snapshot_last_max_pages'] ) ) {
		$result->max_num_pages = (int) $GLOBALS['ez_products_snapshot_last_max_pages'];
	}

	return $result;
}

/**
 * Convenience: return only the rendered HTML (when the caller just needs the slides).
 */
function ez_products_snapshot_swiper_html( array $args ): string {
	return ez_products_snapshot_swiper( $args )->products;
}

/**
 * Drop-in replacement for the legacy
 * `json_decode( ez_webservice([ 'type' => 'get_by_products_id', 'data' => [ 'products_id' => […] ] ]) )`.
 *
 * Returns the same object shape: `(object){ products: <html> }` (snapshot does not track ad metadata).
 *
 * @param int[] $ids
 */
function ez_products_snapshot_swiper_by_ids( array $ids ): object {
	$rows     = EZ_Products_Snapshot_Repository::products_by_ids( $ids );
	$products = ez_products_snapshot_render_swiper_slides( $rows );

	return (object) array(
		'products'    => $products,
		'products_id' => array_map( static fn( $r ) => (int) ( $r['product_id'] ?? 0 ), $rows ),
	);
}

/**
 * AJAX endpoint replacing the legacy web-service POST that the JS filter buttons
 * (`#…-slider`) hit for in-page filtering. The JS payload shape is preserved.
 *
 * Endpoint: /wp-admin/admin-ajax.php?action=ez_products_snapshot_swiper
 * Response: { products: <html>, products_id?: [...] }
 */
function ez_products_snapshot_swiper_ajax(): void {
	$args = array();
	if ( isset( $_POST['data'] ) ) {
		$raw = $_POST['data']; // phpcs:ignore WordPress.Security.NonceVerification.Missing -- public read-only.
		if ( is_string( $raw ) ) {
			$decoded = json_decode( wp_unslash( $raw ), true );
			$args    = is_array( $decoded ) ? $decoded : array();
		} elseif ( is_array( $raw ) ) {
			$args = wp_unslash( $raw );
		}
	}

	$result = ez_products_snapshot_swiper( is_array( $args ) ? $args : array() );

	wp_send_json( $result );
}
add_action( 'wp_ajax_ez_products_snapshot_swiper', 'ez_products_snapshot_swiper_ajax' );
add_action( 'wp_ajax_nopriv_ez_products_snapshot_swiper', 'ez_products_snapshot_swiper_ajax' );

/**
 * Expose the AJAX URL to front-end JS as a single source of truth.
 * The script `assets/js/main.js` reads `window.EZ_PRODUCTS_SNAPSHOT_AJAX`.
 */
function ez_products_snapshot_swiper_localize(): void {
	$url = admin_url( 'admin-ajax.php?action=ez_products_snapshot_swiper' );
	wp_register_script( 'ez-products-snapshot-bridge', '', array(), '1.0', false );
	wp_enqueue_script( 'ez-products-snapshot-bridge' );
	wp_add_inline_script(
		'ez-products-snapshot-bridge',
		'window.EZ_PRODUCTS_SNAPSHOT_AJAX=' . wp_json_encode( $url ) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'ez_products_snapshot_swiper_localize', 5 );
