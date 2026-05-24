<?php
/**
 * hooks: cron_schedules, ez_queryable_set_hottest_cron, ez_queryable_set_popular_cron, ez_queryable_set_topsale_cron
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 131-190)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $_SERVER['HTTP_HOST'] == 'escapezoom.ir' ) {

    add_filter('cron_schedules', function ($schedules) {
        $schedules['every_10_secs'] = array(
            'interval' => 10,
            'display'  => __('Once every 10 seconds')
        );

        $schedules['every_two_minutes'] = array(
            'interval' => 120,
            'display'  => __('Every 2 Minutes')
        );

        return $schedules;
    });

    $ranking_incremental = apply_filters( 'ez_core_incremental_ranking_enabled', true );

    if ( ! $ranking_incremental ) {
        add_action( 'ez_queryable_set_hottest_cron', 'ez_queryable_set_hottest_products' );
        if ( ! wp_next_scheduled( 'ez_queryable_set_hottest_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'ez_queryable_set_hottest_cron' );
        }

        add_action( 'ez_queryable_set_popular_cron', 'ez_queryable_set_popular_products' );
        if ( ! wp_next_scheduled( 'ez_queryable_set_popular_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'ez_queryable_set_popular_cron' );
        }

        add_action( 'ez_queryable_set_topsale_cron', 'ez_queryable_set_topsale_products' );
        if ( ! wp_next_scheduled( 'ez_queryable_set_topsale_cron' ) ) {
            wp_schedule_event( time(), 'hourly', 'ez_queryable_set_topsale_cron' );
        }
    }

    add_action( 'ez_queryable_set_recent_cron', 'ez_queryable_set_recent_products' );
    if (!wp_next_scheduled('ez_queryable_set_recent_cron'))
        wp_schedule_event( time(), 'hourly', 'ez_queryable_set_recent_cron' );

    add_action( 'ez_queryable_set_data_cron', 'ez_queryable_set_products_data' );
    if (!wp_next_scheduled('ez_queryable_set_data_cron'))
        wp_schedule_event( time(), 'hourly', 'ez_queryable_set_data_cron' );

    add_action( 'ez_queryable_set_data_nactive_cron', 'ez_queryable_set_products_data_nactive' );
    if (!wp_next_scheduled('ez_queryable_set_data_nactive_cron'))
        wp_schedule_event( time(), 'hourly', 'ez_queryable_set_data_nactive_cron' );

    add_action( 'ez_sms_sending_queue_cron', 'ez_sms_sending_queue_schedule' );
    if (!wp_next_scheduled('ez_sms_sending_queue_cron'))
        wp_schedule_event( time(), 'every_10_secs', 'ez_sms_sending_queue_cron' );

    add_action( 'ez_remove_expired_sms_queue_cron', 'ez_remove_expired_sms_queue_schedule' );
    if (!wp_next_scheduled('ez_remove_expired_sms_queue_cron'))
        wp_schedule_event( time(), 'twicedaily', 'ez_remove_expired_sms_queue_cron' );

    add_action( 'wp_zb_booking_history_today_optimize_cron', 'wp_zb_booking_history_today_optimize' );
    if (!wp_next_scheduled('wp_zb_booking_history_today_optimize_cron'))
        wp_schedule_event( time(), 'daily', 'wp_zb_booking_history_today_optimize_cron' );

    if ( ! $ranking_incremental ) {
        add_action( 'update_comments_stars_cron', 'update_comments_stars' );
        if ( ! wp_next_scheduled( 'update_comments_stars_cron' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'update_comments_stars_cron' );
        }
    }

    add_action( 'comment_reminder_sms_process_cron', 'comment_reminder_sms_process' );
    if (!wp_next_scheduled('comment_reminder_sms_process_cron'))
        wp_schedule_event( time(), 'hourly', 'comment_reminder_sms_process_cron' );
}
