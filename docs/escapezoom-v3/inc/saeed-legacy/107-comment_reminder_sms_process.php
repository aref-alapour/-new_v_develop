<?php
/**
 * comment_reminder_sms_process
 *
 * توابع: comment_reminder_sms_process
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6697-6752)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function comment_reminder_sms_process () {
    $rows = get_sms_schedule_rows();

    foreach ($rows['reminder1'] as $row) {

        $id         = $row->id;
        $order_id   = $row->order_id;
        $order = wc_get_order($order_id);
        if ( ! in_array($order ? $order->get_status() : '', ['partially-paid', 'walletx', 'completed'], true) ) {
            delete_sms_schedule_row( $id );
            continue;
        }

        $phone          = $row->mobile;
        $product_id     = $row->product_id;
        $player_name    = $row->name;
        $url            = "escapezoom.ir/$product_id";
        $product_title  = get_the_title($product_id);

        if ( if_user_commented ($phone, $product_id) )
            delete_sms_schedule_row($id);

        else {
            if ( !($row->reminder1) ) { // اسمس یادآور اول ارسال نشده
                $text = "$player_name;$product_title;$url";
                add_to_sms_queue(434378,$phone, $text, $order_id, 'reminder1');
                update_sms_schedule_row( $id );
            }
        }
    }

    foreach ($rows['reminder2'] as $row) {

        $id         = $row->id;
        $order_id   = $row->order_id;

        $order = wc_get_order($order_id);
        if ( ! in_array($order ? $order->get_status() : '', ['partially-paid', 'walletx', 'completed'], true) ) {
            delete_sms_schedule_row( $id );
            continue;
        }

        $phone          = $row->mobile;
        $product_id     = $row->product_id;
        $player_name    = $row->name;
        $url            = "escapezoom.ir/$product_id";
        $product_title  = get_the_title($product_id);

        if ( !if_user_commented ($phone, $product_id) ) {
            $text = "$player_name;$product_title;$url";
            add_to_sms_queue(434381,$phone, $text, $order_id, 'reminder2');
        }

        delete_sms_schedule_row( $id );
    }
}
