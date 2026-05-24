<?php
/** lines 5602-5663 → shop/booking/pipeline-player-sms.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_cm_add_phone ($product_id, $order_id, $phone) {
    global $wpdb;

    $phone = preg_replace('/\D+/', '', $phone);

    if (substr($phone, 0, 3) === '009')
        $phone = substr($phone, 3);

    if (substr($phone, 0, 2) === '98')
        $phone = substr($phone, 2);
    elseif (substr($phone, 0, 3) === '098')
        $phone = substr($phone, 3);

    if (substr($phone, 0, 1) === '0')
        $phone = substr($phone, 1);

    if (strlen($phone) !== 10 || $phone[0] !== '9')
        return false;

    $wpdb->insert( 'comment_phones_list', array(
        'product_id' => $product_id,
        'order_id'   => $order_id,
        'phone'      => $phone
    ));

    return $wpdb->insert_id ? true : false;
}
/********************************************************************************************************************************/
function send_sms_scheduled($phone, $text, $date) {

    $data = [
        'username'      => "xescape",
        'password'      => "2kkh7Gm36%#X91h",
        'text'          => $text,
        'to'            => $phone,
        "from"          => '2191307900',
        'scheduleDate'  => $date,
        'period'        => 0
    ];

    $post_data  = http_build_query($data);
    $handle     = curl_init('https://rest.payamak-panel.com/api/SendSMS/SendSchedule');

    curl_setopt($handle, CURLOPT_HTTPHEADER, [
        'content-type' => 'application/x-www-form-urlencoded'
    ]);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($handle, CURLOPT_POST, true);
    curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);

    return curl_exec($handle);
}
/********************************************************************************************************************************/
function ez_cm_get_order_id ( $product_id, $phone ) {
    global $wpdb;

    $res = $wpdb->get_results(
        $wpdb->prepare(
            'SELECT * FROM comment_phones_list WHERE product_id = %s AND phone LIKE %s',
            (string) $product_id,
            (string) $phone
        )
    );

    return $res;
}
