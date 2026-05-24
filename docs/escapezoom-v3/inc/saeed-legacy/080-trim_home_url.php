<?php
/**
 * trim_home_url
 *
 * توابع: trim_home_url
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6117-6121)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function trim_home_url($url) {
    return str_replace(home_url(), '', $url);
}
$admin_role = get_role( 'shopist' );
$admin_role->add_cap( 'shopist_cap', true );
