<?php
/**
 * ez_sms_sending_queue_schedule
 *
 * توابع: ez_sms_sending_queue_schedule
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1061-1083)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_sms_sending_queue_schedule() {
    global $wpdb;

    $now        = time();

    $rows = $wpdb->get_results("SELECT * FROM `sms_sending_queue` WHERE sent_time IS NULL ORDER BY query_time ASC;");

    if ( !empty( $rows ) ) {
        foreach ( $rows as $row ) {
            $sent_flag = false;
            $sms1_response = json_decode(smsPattern($row->phone,$row->text,$row->token));
            if ( isset($sms1_response->RetStatus) && $sms1_response->RetStatus == 1 )
                $sent_flag = true;
            else {
                $sms2_response = json_decode(smsPattern($row->phone,$row->text,$row->token));
                if ( isset($sms2_response->RetStatus) && $sms2_response->RetStatus == 1 )
                    $sent_flag = true;
            }
            if ( $sent_flag || 1 )
                $wpdb->query( $wpdb->prepare("UPDATE sms_sending_queue SET sent_time = %d WHERE ID = %d;", $now, $row->ID) );
        }
    }
}
