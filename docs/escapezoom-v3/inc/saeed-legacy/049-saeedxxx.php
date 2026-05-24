<?php
/**
 * saeedxxx
 *
 * توابع: saeedxxx هوک‌ها: init
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 4950-4977)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('init', 'saeedxxx');
function saeedxxx() {
/**
 * GET: fill_table_with_order_info
 *
 * هدف: پر کردن جدول اطلاعات سفارش
 * استفاده: غیرفعال (&& 0)
 * وابستگی: wpdb
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/049-saeedxxx.php:16
 */
    if ( isset($_GET['fill_table_with_order_info'] ) && 0) {

        global $wpdb;

        $orders = $wpdb->get_results( "
            SELECT wp_posts.ID
            FROM wp_zb_booking_history
            INNER JOIN wp_posts ON wp_posts.ID = wp_zb_booking_history.wc_order_id
            AND wp_zb_booking_history.booking_time > 1701140000
            AND wp_zb_booking_history.status LIKE 1;
        ", ARRAY_A );

        foreach ( $orders as $order_id ) {
            $order = wc_get_order( $order_id['ID'] );

            if ( !empty( $order ) ) {
                $player_name    = $order->get_billing_first_name() . " " . $order->get_billing_last_name();
                $player_phone   = $order->get_billing_phone();
                foreach ($order->get_items() as $item)
                    $item_quantity = $item->get_quantity();

                $wpdb->update('wp_zb_booking_history', array('name' => $player_name, 'phone' => $player_phone, 'quantity' => $item_quantity ), array('wc_order_id' => $order_id['ID']));
            }
        }
    }
}
