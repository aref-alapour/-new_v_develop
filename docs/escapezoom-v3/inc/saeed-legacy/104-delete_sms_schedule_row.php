<?php
/**
 * delete_sms_schedule_row
 *
 * توابع: delete_sms_schedule_row
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6670-6673)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function delete_sms_schedule_row( $id ) {
    global $wpdb;
    $wpdb->delete('comment_sms_schedule', ['id' => $id]);
}
