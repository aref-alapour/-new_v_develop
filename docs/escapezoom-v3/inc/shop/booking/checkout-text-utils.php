<?php
/** lines 3833-3848 → shop/booking/checkout-text-utils.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function randString($length) {
    $char = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $char = str_shuffle($char);
    for($i = 0, $rand = '', $l = strlen($char) - 1; $i < $length; $i ++) {
        $rand .= $char[random_int(0, $l)];
    }
    return $rand;
}
/****************************************************************************************************************************************/
function base64_url_encode($input) {
    return strtr(base64_encode($input), '+/=', '._-');
}
/****************************************************************************************************************************************/
function base64_url_decode($input) {
    return base64_decode(strtr($input, '._-', '+/='));
}
