<?php
if (!defined('ABSPATH')) {
	exit;
}

/* =======================================================
    Woocommerce Func
========================================================= */
add_theme_support('woocommerce');
// WC Unset Checkout Field
/*add_filter( 'woocommerce_checkout_fields', 'unrequire_checkout_fields' );
function unrequire_checkout_fields( $fields ) {
    $fields['billing']['billing_company']['required']   = false;
    #$fields['billing']['billing_city']['required']      = false;
    $fields['billing']['billing_postcode']['required']  = false;
    #$fields['billing']['billing_phone']['required']  = false;
    #$fields['billing']['billing_email']['required']  = false;
    $fields['billing']['billing_country']['required']   = false;
    #$fields['billing']['billing_state']['required']     = false;
    #$fields['billing']['billing_address_1']['required'] = false;
    $fields['billing']['billing_address_2']['required'] = false;
    $fields['shipping']['shipping_company']['required']   = false;
    $fields['shipping']['shipping_city']['required']      = false;
    $fields['shipping']['shipping_postcode']['required']  = false;
    $fields['shipping']['shipping_phone']['required']  = false;
    $fields['shipping']['shipping_email']['required']  = false;
    $fields['shipping']['shipping_country']['required']   = false;
    $fields['shipping']['shipping_state']['required']     = false;
    $fields['shipping']['shipping_address_1']['required'] = false;
    $fields['shipping']['shipping_address_2']['required'] = false;
    return $fields;
}
add_filter('woocommerce_checkout_fields','remove_checkout_fields');
function remove_checkout_fields($fields){
    #unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_company']);
    #unset($fields['billing']['billing_last_name']);
    #unset($fields['billing']['billing_email']);
    #unset($fields['billing']['billing_phone']);
    #unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    #unset($fields['billing']['billing_city']);
    #unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);
    #unset($fields['billing']['billing_state']);
    unset($fields['shipping']['shipping_first_name']);
    unset($fields['shipping']['shipping_company']);
    unset($fields['shipping']['shipping_last_name']);
    unset($fields['shipping']['shipping_email']);
    unset($fields['shipping']['shipping_phone']);
    unset($fields['shipping']['shipping_address_1']);
    unset($fields['shipping']['shipping_address_2']);
    unset($fields['shipping']['shipping_city']);
    unset($fields['shipping']['shipping_postcode']);
    unset($fields['shipping']['shipping_country']);
    unset($fields['shipping']['shipping_state']);
    return $fields;
}*/

/*add_filter( 'wc_add_to_cart_message', 'remove_add_to_cart_message' );
function remove_add_to_cart_message() {
    return;
}
remove_action('woocommerce_before_checkout_form','woocommerce_checkout_login_form');
add_action( 'template_redirect', 'order_recevied_redirect_theme' );
function order_recevied_redirect_theme(): void
{
    global $wp;
    if ( is_checkout() && !empty( $wp->query_vars['order-received'] ) ) {
        WC()->cart->empty_cart();
        wp_redirect( home_url('/').'order-received?order=thankyou');
        exit;
    }
}*/
