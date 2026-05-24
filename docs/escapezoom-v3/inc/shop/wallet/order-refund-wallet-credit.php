<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('woocommerce_order_status_changed', 'if_order_status_changed', 10, 3);
function if_order_status_changed($order_id, $old_status, $new_status) {
    global $wldb;

    $order = wc_get_order( $order_id );
    foreach ( $order->get_items() as $item ) {
        $product_title  = $item->get_name();
        $product_id     = $item['product_id'];
        $quantity       = $item->get_quantity();
    }

    if ( $new_status == 'refunded' ) {

        $oid = (int) $order_id;
        $args = [
            "single_value"  => true,
            "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$oid}",
        ];
        $row = (array)json_decode(ez_reservation( array('type' => 'query_execution', 'data' => $args) ));

        ez_reservation( array( 'type' => 'query_execution', 'data' => array( 'query' => "DELETE FROM `wp_zb_booking_history` WHERE `wc_order_id` = {$oid}" ) ) ); // باز کردن سانس

        /*------------------------------------------*/
        // برگشت مبلغ پرداخت شده به کیف پول

        $user_id = $order->get_user_id();

        $prepaid    = get_post_meta($order_id, 'prepaid', true);
        $prepaid    = is_numeric($prepaid) ? $prepaid : 0;

        $pish_per_person    = get_post_meta($order_id, 'ticket_tedad', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : get_post_meta($product_id, 'pish_pardakht_per_person', true);
        $pish_per_person    = !empty($pish_per_person) ? $pish_per_person : 1;

        $ez_payment_type = get_post_meta($order_id, 'ez_payment_type', true);
        if ( $ez_payment_type == 'partial' )
            $total = $prepaid / $pish_per_person * $quantity;
        elseif ( $ez_payment_type == 'complete' )
            $total = $prepaid;

        $user_level_discount = 0;
        if ($user_id == 3325 or $user_id == 2 or $user_id == 80) {
            $discount = get_user_discount($order_id, $user_id);

            $user_level_discount = ($total * $discount['percentage']) / 100;
        }

        $coupons = $order->get_items( 'coupon' );
        $coupon_amount = 0;

        if ( ! empty( $coupons ) ) {
            foreach ( $coupons as $coupon_item ) {
                $code   = $coupon_item->get_code();
                $coupon = new WC_Coupon( $code );
                $coupon_id = $coupon->get_id();

                // حذف کاربر از متای استفاده‌کنندگان
                delete_post_meta( $coupon_id, '_used_by', $user_id );

                // محاسبه مقدار واقعی تخفیف
                $coupon_amount += ez_get_coupon_discount_amount( $code, $total );
            }
        }

        $current_balance    = $wldb->get_balance($user_id);
        $amount             = ($prepaid - ($coupon_amount + $user_level_discount));

        if ( $amount > 0 ) {
            $balance        = $current_balance + $amount;
            $description    = 'برگشت مبلغ - استرداد' . ' - سفارش: ' . $order_id;

            $new_transaction = array (
                'user_id'       => $user_id,
                'amount'        => $amount,
                'balance'       => $balance,
                'description'   => $description,
                'type'          => 'transaction',
            );
            $wldb->insert($new_transaction);
        }

        /*------------------------------------------*/

        $t1             = jstrftime('%H:%M روز %Y/%m/%e' , $row['booking_time'] );
        $player_phone   = ltrim($order->get_billing_phone(), '0');
        $player_name    = $order->get_billing_first_name();

        $owner_id           = get_post_meta( $product_id, 'user_ebtal', true );
        $owner_phone        = get_userdata( $owner_id )->user_login;
        $chat_id            = get_user_meta( $owner_id, 'chat_id', true );

        $manager_id         = get_post_meta( $product_id, 'sans_manager', true );
        $manager_phone      = get_userdata( $manager_id )->user_login;
        $manager_chat_id    = get_user_meta( $manager_id, 'chat_id', true );

        $formatted_amount = englishToPersian(substr($amount, 0, -3));
        $player_sms_body = "$player_name;$formatted_amount;$t1;$product_title";
        $owner_sms_body = "$t1;$product_title";
        add_to_sms_queue (434392,$player_phone, $player_sms_body, $order_id, 'user');

        add_to_sms_queue (434393,$owner_phone,$owner_sms_body, $order_id, 'owner');
        if ( $manager_phone && $manager_phone != $owner_phone )
            add_to_sms_queue (434393,$manager_phone,$owner_sms_body , $order_id, 'owner2');

        if ( $chat_id ) {
            $txt_msg_maj    = "سانس$t1 بازی $product_title کنسل و برای فروش مجدد باز شد.";
            $txt_msg_maj    = str_replace(" ", "%20", $txt_msg_maj);
            $txt_msg_maj    = urlencode($txt_msg_maj);

            $hash = base64_encode($chat_id);
            file_get_contents("https://impec.ir/?chat_id=$hash&message=$txt_msg_maj");

            $hash = base64_encode($manager_chat_id);
            file_get_contents("https://impec.ir/?chat_id=$hash&message=$txt_msg_maj");
        }
    }
}
/****************************************************************************************************************************************/
