<?php
/** lines 8945-8967 → shop/booking/schedule.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_day_type($day) {

    $calendar_data = get_option('ez_calendar');
    $calendar_data = json_decode(json_encode( $calendar_data ) , true);

    foreach ( explode( ',', $calendar_data['holidays']) as $calendar_day ) {
        $calendar_day = (int)$calendar_day; // to convert to local time. calendar time is based GMT+3:30
        if ( $calendar_day <= $day && $day < $calendar_day + 86400 )
            return 'holidays';
    }

    foreach ( explode( ',', $calendar_data['closed_days']) as $calendar_day ) {
        $calendar_day = (int)$calendar_day; // to convert to local time. calendar time is based GMT+3:30
        if ( $calendar_day <= $day && $day < $calendar_day + 86400 )
            return 'closed';
    }

    return 'normals';
}
/****************************************************************************************************************************************/
function get_sanses($product_id) {
    return ['normals' => get_post_meta($product_id, 'schedule_normals', true), 'holidays' => get_post_meta($product_id, 'schedule_holidays', true)];
}
