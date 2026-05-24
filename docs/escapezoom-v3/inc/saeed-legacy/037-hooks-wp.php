<?php
/**
 * hooks: wp
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3694-3701)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('wp', function() {
    $term = substr($_SERVER['REQUEST_URI'], 1);

    if ( is_numeric( explode('/', $term)[0] ) ) {
        wp_redirect(get_permalink($term), 301);
        exit();
    }
});
