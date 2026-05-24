<?php
/**
 * «بازی‌های دیگر برند» روی single-product از wp_products_snapshot (نه وب‌سرویس).
 *
 * این فایل به صورت یک adapter نازک عمل می‌کند روی Repository/Renderer مشترک
 * در inc/core/products-snapshot/* تا یک پیاده‌سازی واحد برای رندر کارت داشته باشیم.
 */
defined( 'ABSPATH' ) || exit;

/**
 * همان شکل آبجکتی که sort_products_get بعد از json_decode داشت ( property products = HTML ).
 *
 * @return object{products: string}
 */
function ez_get_brand_related_products_swiper_from_snapshot( int $brand_term_id, int $exclude_product_id, int $limit = 10 ): object {
	return (object) array(
		'products' => ez_render_brand_related_products_html_from_snapshot( $brand_term_id, $exclude_product_id, $limit ),
	);
}

/**
 * @return string HTML داخل embla (فقط article.embla__slideها، بدون wrapper).
 */
function ez_render_brand_related_products_html_from_snapshot( int $brand_term_id, int $exclude_product_id, int $limit ): string {
	$rows = EZ_Products_Snapshot_Repository::products_by_brand( $brand_term_id, $exclude_product_id, $limit );

	return ez_products_snapshot_render_swiper_slides( $rows );
}
