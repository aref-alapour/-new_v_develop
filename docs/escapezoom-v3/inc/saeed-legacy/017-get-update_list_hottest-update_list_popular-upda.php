<?php
/**
 * GET: update_list_hottest, update_list_popular, update_list_topsale, update_recent, …
 *
 * با باز شدن URL و پارامتر کوئری اجرا می‌شود؛ برای نگهداری/تست/مهاجرت داده. پارامترها: update_list_hottest, update_list_popular, update_list_topsale, update_recent, update_product_data, update_product_data_nactive, update_marketing_data, ez_owner_wallet_held_24hrs, update_comments_stars
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1017-1060)
 * نوع: ابزار یک‌باره (GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: update_list_hottest
 *
 * هدف: بازسازی لیست hottest و ریدایرکت به ادمین
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_hottest_products2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:14
 */
if ( isset($_GET["update_list_hottest"]) ) {
    ez_queryable_set_hottest_products2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
/**
 * GET: update_list_popular
 *
 * هدف: بازسازی لیست popular
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_popular_products2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:19
 */
if ( isset($_GET["update_list_popular"]) ) {
    ez_queryable_set_popular_products2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
/**
 * GET: update_list_topsale
 *
 * هدف: بازسازی topsale
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_topsale_products2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:24
 */
if ( isset($_GET["update_list_topsale"]) ) {
    ez_queryable_set_topsale_products2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
/**
 * GET: update_recent
 *
 * هدف: بازسازی recent
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_recent_products2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:29
 */
if ( isset($_GET["update_recent"]) ) {
    ez_queryable_set_recent_products2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
/**
 * GET: update_product_data
 *
 * هدف: بازسازی داده محصولات فعال
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_products_data2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:34
 */
if ( isset($_GET["update_product_data"]) ) {
    ez_queryable_set_products_data2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
/**
 * GET: update_product_data_nactive
 *
 * هدف: بازسازی داده محصولات غیرفعال
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_products_data2_nactive
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:39
 */
if ( isset($_GET["update_product_data_nactive"]) ) {
    ez_queryable_set_products_data2_nactive();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
/**
 * GET: update_marketing_data
 *
 * هدف: بازسازی داده مارکتینگ
 * استفاده: دستی از URL
 * وابستگی: ez_queryable_set_marketing_data2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:44
 */
if ( isset($_GET["update_marketing_data"]) ) {
    ez_queryable_set_marketing_data2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
/*===============================*/
// if ( isset( $_GET['ez_owner_wallet_held_24hrs'] ) ) {
//     ez_owner_wallet_held_24hrs2();
//     wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
// }
/*===============================*/
/**
 * GET: update_comments_stars
 *
 * هدف: بازسازی ستاره کامنت‌ها
 * استفاده: دستی از URL
 * وابستگی: update_comments_stars2
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/017-get-update_list_hottest-update_list_popular-upda.php:54
 */
if ( isset( $_GET['update_comments_stars'] ) ) {
    update_comments_stars2();
    wp_redirect("https://escapezoom.ir/wp-admin/admin.php?page=month_best_sell");
}
