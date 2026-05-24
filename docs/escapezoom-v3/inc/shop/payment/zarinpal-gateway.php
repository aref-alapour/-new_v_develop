<?php
/** lines 11145-11155 → shop/payment/zarinpal-gateway.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('woocommerce_available_payment_gateways', 'switch_zarinpal_gateway_by_domain');
function switch_zarinpal_gateway_by_domain($available_gateways) {
    $domain = $_SERVER['HTTP_HOST'];

    if (strpos($domain, '.ir') !== false)
        unset($available_gateways['WC_ZPal_co']);
    else
        unset($available_gateways['WC_ZPal']);

    return $available_gateways;
}
