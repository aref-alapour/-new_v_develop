<?php
/**
 * wp_zb_booking_history_today_optimize2 (+1 more)
 *
 * توابع: wp_zb_booking_history_today_optimize2, wp_zb_booking_history_today_optimize هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1003-1016)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wp_zb_booking_history_today_optimize2() {
    add_action('woocommerce_after_register_post_type', 'wp_zb_booking_history_today_optimize');
}
/*===============================*/
function wp_zb_booking_history_today_optimize() {

    $today = strtotime(date("Y-m-d") . ' 00:00');

    $args = [
        "single_value"  => false,
        "query"         => "DELETE FROM `wp_zb_booking_history_today` WHERE `booking_time` < $today",
    ];
    json_decode(ez_reservation( array('type' => 'query_execution', 'data' => $args) ));
}
