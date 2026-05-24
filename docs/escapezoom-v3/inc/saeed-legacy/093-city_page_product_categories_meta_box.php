<?php
/**
 * city_page_product_categories_meta_box
 *
 * توابع: city_page_product_categories_meta_box هوک‌ها: add_meta_boxes
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6485-6494)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('add_meta_boxes', 'city_page_product_categories_meta_box');
function city_page_product_categories_meta_box() {
    add_meta_box(
        'city_page_product_categories',
        'صفحه شهر',
        'display_city_page_product_categories_meta_box',
        'page',
        'side',
    );
}
