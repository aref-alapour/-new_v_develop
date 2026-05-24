<?php
/**
 * GET: w
 *
 * با باز شدن URL و پارامتر کوئری اجرا می‌شود؛ برای نگهداری/تست/مهاجرت داده. پارامترها: w
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6570-6585)
 * نوع: ابزار یک‌باره (GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// مدیریت کوئری استرینگ w

add_filter('request', function ($request) {
    if (isset($request['w']))
        unset($request['w']);
    return $request;
});

add_action( 'template_redirect', function() {
/**
 * GET: w
 *
 * هدف: ریدایرکت/تست کوتاه
 * استفاده: دستی
 * وابستگی: template_redirect
 * امنیت: بدون احراز هویت
 * وضعیت: بررسی حذف
 * منبع: saeed-legacy/097-get-w.php:23
 */
    if ( isset($_GET['w']) ) {
        status_header(410);
        nocache_headers();
        echo '<p>اشتباه اومدی داداش</p>';
        exit;
    }
});
