<?php
/**
 * Shop module (migrated from saeed-codes.php).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/****************************************************************************************************************************************/
add_filter( 'woocommerce_coupon_error', 'change_coupon_error_msg', 10, 3 );
function change_coupon_error_msg( $err, $err_code, $coupon ) {

    if ( false !== strpos( $err, 'مهلت استفاده از کد تخفیف به پایان رسیده است.' ) )
        $err = 'شما از این کد قبلا استفاده کرده اید.';

    return $err;
}
