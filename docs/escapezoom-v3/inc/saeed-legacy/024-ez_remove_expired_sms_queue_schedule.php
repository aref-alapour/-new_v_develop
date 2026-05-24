<?php
/**
 * ez_remove_expired_sms_queue_schedule
 *
 * توابع: ez_remove_expired_sms_queue_schedule
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1201-1210)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_remove_expired_sms_queue_schedule() {
    global $wpdb;

    $expired = $wpdb->get_results( 'SELECT * FROM sms_sending_queue WHERE sent_time IS NOT NULL' );
    foreach ( $expired as $exp_row ) {
        if ( ( time() - (int) $exp_row->sent_time ) > ( 90 * 24 * 60 * 60 ) ) {
            $wpdb->query( $wpdb->prepare( 'DELETE FROM `sms_sending_queue` WHERE ID = %d', (int) $exp_row->ID ) );
        }
    }
}
