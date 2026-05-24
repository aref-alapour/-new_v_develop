<?php
/** lines 8982-8999 → shop/booking/pipeline-sms-queue.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function add_to_sms_queue ($token,$phone, $text, $order_id, $type) {
    global $wpdb;

    $query_time = time();

    if ( $phone ) {
        $wpdb->query(
            $wpdb->prepare(
                'INSERT INTO `sms_sending_queue` (`token`,`phone`,`text`,`order_id`,`type`,`query_time`,`sent_time`) VALUES (%s,%s,%s,%s,%s,%d,NULL)',
                (string) $token,
                (string) $phone,
                (string) $text,
                (string) $order_id,
                (string) $type,
                (int) $query_time
            )
        );
    }
}