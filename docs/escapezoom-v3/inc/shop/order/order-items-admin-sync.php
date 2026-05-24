<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/****************************************************************************************************************************************/
/****************************************************************************************************************************************/
add_action( 'woocommerce_before_save_order_items', 'custom_checkout_create_order_line_item', 10, 2 ); // آپدیت کردن تعداد پلیرها در db2 از ویرایش سفارش توسط پشتیبان
function custom_checkout_create_order_line_item( $order_id, $items ) {
    global $wpdb;

    foreach ( $items['order_item_qty'] as $temp )
        $quantity = $temp;

    $wpdb->update('held_orders_list', array('count' => $quantity ), array('order_id' => $order_id));
    if ( class_exists( '\EscapeZoom\Core\Modules\ProductRanking\Services\TopsaleEligibilityService' ) ) {
        \EscapeZoom\Core\Modules\ProductRanking\Services\TopsaleEligibilityService::refreshTopsaleForOrder( (int) $order_id );
    }
    $q_qty   = (int) $quantity;
    $q_order = (int) $order_id;
    ez_reservation( array( 'type' => 'query_execution', 'data' => array( 'query' => "UPDATE `wp_zb_booking_history` SET `quantity` = {$q_qty} WHERE `wc_order_id` = {$q_order}" ) ) );
}
