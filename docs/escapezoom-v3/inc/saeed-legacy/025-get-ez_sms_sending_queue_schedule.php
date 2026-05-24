<?php
/**
 * GET: ez_sms_sending_queue_schedule
 *
 * با باز شدن URL و پارامتر کوئری اجرا می‌شود؛ برای نگهداری/تست/مهاجرت داده. پارامترها: ez_sms_sending_queue_schedule
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1211-1216)
 * نوع: ابزار یک‌باره (GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: ez_sms_sending_queue_schedule
 *
 * هدف: اجرای دستی ارسال صف SMS
 * استفاده: دستی از URL
 * وابستگی: ez_sms_sending_queue_schedule
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/025-get-ez_sms_sending_queue_schedule.php:14
 */
if ( isset( $_GET['ez_sms_sending_queue_schedule'] ) ) {
    global $wpdb;

    for ( $i = 0; $i < 100; $i++ )
        ez_sms_sending_queue_schedule();
}
