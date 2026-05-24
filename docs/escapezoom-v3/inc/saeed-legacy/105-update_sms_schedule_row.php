<?php
/**
 * update_sms_schedule_row
 *
 * توابع: update_sms_schedule_row
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6674-6677)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function update_sms_schedule_row( $id ) {
    global $wpdb;
    $wpdb->update('comment_sms_schedule', array('reminder1' => 1), array('id' => $id));
}
