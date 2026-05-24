<?php
/**
 * Cron Jobs Configuration
 * 
 * این فایل برای تنظیم و مدیریت تمام cron job های پروژه استفاده می‌شود
 * از این به بعد تمام cron job ها در این فایل تعریف می‌شوند
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * تعریف بازه زمانی 30 دقیقه‌ای
 */
add_filter('cron_schedules', function ($schedules) {
    $schedules['every_30_minutes'] = array(
        'interval' => 30 * 60, // 30 دقیقه = 1800 ثانیه
        'display'  => __('هر 30 دقیقه', 'escapezoom')
    );
    return $schedules;
});

/**
 * تنظیم cron job برای check_wallet_orders
 * این تابع هر 30 دقیقه یکبار اجرا می‌شود
 */
add_action('check_wallet_orders_cron', 'check_wallet_orders');
if (!wp_next_scheduled('check_wallet_orders_cron')) {
    wp_schedule_event(time(), 'every_30_minutes', 'check_wallet_orders_cron');
}

