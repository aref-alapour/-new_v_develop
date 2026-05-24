<?php
/**
 * single_schedule_changed
 *
 * توابع: single_schedule_changed هوک‌ها: single_schedule_changed
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3758-3761)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//add_action('single_schedule_changed', 'single_schedule_changed', 10, 3);
function single_schedule_changed ($product_id, $booking_time, $state) {
    ez_webservice( array('type' => 'single_schedule_products_set', 'data' => array('product_id' => $product_id, 'booking' => $booking_time, 'state' => $state)) ); // آپدیت کردن زمان سانس ها
}
