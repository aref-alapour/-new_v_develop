<?php
/**
 * fix_quantity_if_not_allowed
 *
 * توابع: fix_quantity_if_not_allowed هوک‌ها: woocommerce_add_to_cart
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6604-6624)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'woocommerce_add_to_cart', 'fix_quantity_if_not_allowed', 10, 3 );
function fix_quantity_if_not_allowed( $cart_item_key, $product_id, $quantity ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) return;

    preg_match_all( '/\d+/', get_field("room_tedad", $product_id), $matches );
    if ( ! empty( $matches[0] ) )
        $max_quantity = intval( max( $matches[0] ) );

    if ( $quantity > $max_quantity ) {
        WC()->cart->set_quantity( $cart_item_key, $max_quantity );
        wc_add_notice(
            sprintf(
                'تعداد تیکت برای "%s" به %d عدد کاهش یافت حداکثر تعداد مجاز برای این بازی %d میباشد.',
                get_the_title( $product_id ),
                $max_quantity,
                $max_quantity,
            ),
            'notice'
        );
    }
}
