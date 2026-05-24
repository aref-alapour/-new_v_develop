<?php
/**
 * saeed_print
 *
 * توابع: saeed_print
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 44-48)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function saeed_print ( $val='', $die=false ) {
    echo '<pre>'; print_r($val); echo '</pre>';
    if ( $die )
        die();
}
