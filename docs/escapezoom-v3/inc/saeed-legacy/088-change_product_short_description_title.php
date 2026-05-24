<?php
/**
 * change_product_short_description_title
 *
 * توابع: change_product_short_description_title هوک‌ها: gettext
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6432-6438)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'gettext', 'change_product_short_description_title', 20, 3 );
function change_product_short_description_title( $translated_text, $text, $domain ) {
    if ( 'woocommerce' === $domain && 'Product short description' === $text ) {
        $translated_text = 'نقد';
    }
    return $translated_text;
}
