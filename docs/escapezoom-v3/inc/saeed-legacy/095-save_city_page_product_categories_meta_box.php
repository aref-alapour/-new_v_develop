<?php
/**
 * save_city_page_product_categories_meta_box
 *
 * توابع: save_city_page_product_categories_meta_box هوک‌ها: save_post
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6546-6557)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('save_post', 'save_city_page_product_categories_meta_box');
function save_city_page_product_categories_meta_box($post_id) {
/**
 * POST: city_page_product_categories
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/095-save_city_page_product_categories_meta_box.php:16
 */
    if (isset($_POST['city_page_product_categories']))
        update_post_meta($post_id, 'city_page_product_categories', $_POST['city_page_product_categories']);
    else
        delete_post_meta($post_id, 'city_page_product_categories');

/**
 * POST: assign_as_city_page
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/095-save_city_page_product_categories_meta_box.php:21
 */
    if (isset($_POST['assign_as_city_page']))
        update_post_meta($post_id, 'assign_as_city_page', '1');
    else
        delete_post_meta($post_id, 'assign_as_city_page');
}
