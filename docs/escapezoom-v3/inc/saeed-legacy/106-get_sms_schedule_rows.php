<?php
/**
 * get_sms_schedule_rows
 *
 * توابع: get_sms_schedule_rows
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6678-6696)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_sms_schedule_rows() {
    global $wpdb;

    $rows = $wpdb->get_results("SELECT * FROM comment_sms_schedule WHERE UNIX_TIMESTAMP() > sans_time + 48 * 3600");

    foreach ($rows as $row) {
        $hours_passed = (time() - $row->sans_time) / 3600;

        if ($hours_passed >= 120)
            $reminder2[] = $row;
        elseif ($hours_passed >= 48)
            $reminder1[] = $row;
    }

    return [
        'reminder1' => $reminder1,
        'reminder2' => $reminder2
    ];
}
