<?php
/** lines 4256-4297 → shop/booking/checkout-coupon-hooks.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_coupon_message', '__return_empty_string' );

// پیام کاستوم بعد از محاسبه تخفیف
add_action( 'woocommerce_after_calculate_totals', function () {

    if ( ! WC()->cart )
        return;

    $coupons = WC()->cart->get_applied_coupons();

    if ( empty( $coupons ) )
        return;

    foreach ( $coupons as $coupon_code ) {
        $coupon = new WC_Coupon( $coupon_code );

        if ( ! $coupon->get_id() )
            continue;

        $discount_type  = $coupon->get_discount_type();
        $coupon_amount  = $coupon->get_amount();

        if ( $discount_type === 'percent' )
            $value = $coupon_amount . 'درصدی';
        else
            $value = wc_price( $coupon_amount ) . 'ی';

        wc_add_notice(
            sprintf(
                'کدتخفیف %s ثبت شد.',
                $value
            ),
            'success'
        );
    }
});
/****************************************************************************************************************************************/
add_action( 'woocommerce_checkout_update_order_meta', 'store_ez_payment_method');
function store_ez_payment_method( $order_id ) {
    add_post_meta($order_id, 'ez_payment_type', $_POST['ez_payment_type'] ? : 'partial');
}
/****************************************************************************************************************************************/
