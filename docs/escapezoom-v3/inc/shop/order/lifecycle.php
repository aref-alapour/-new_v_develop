<?php
/** lines 5665-5668 → shop/order/lifecycle.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wc_order_is_editable', 'wc_make_processing_orders_editable', 99, 2 );
function wc_make_processing_orders_editable( $is_editable, $order ) {
    return true;
}
