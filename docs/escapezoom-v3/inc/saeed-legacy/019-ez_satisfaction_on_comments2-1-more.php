<?php
/**
 * ez_satisfaction_on_comments2 (+1 more)
 *
 * توابع: ez_satisfaction_on_comments2, ez_satisfaction_on_comments هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1084-1094)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: ez_satisfaction_on_comments
 *
 * هدف: تریگر قدیمی رضایت روی کامنت
 * استفاده: دستی
 * وابستگی: ez_satisfaction_on_comments2
 * امنیت: بدون احراز هویت
 * وضعیت: منسوخ / حذف
 * منبع: saeed-legacy/019-ez_satisfaction_on_comments2-1-more.php:14
 */
if ( isset( $_GET['ez_satisfaction_on_comments'] ) ) {
    ez_satisfaction_on_comments2();
}

function ez_satisfaction_on_comments2() {
    add_action('woocommerce_after_register_post_type', 'ez_satisfaction_on_comments');
}

function ez_satisfaction_on_comments() {
    // Replaced by wp_markting.order_satisfaction_status + wallet/comment/cancellation flows (see order_satisfaction.php).
}
