<?php
/**
 * ez_get_user_ip
 *
 * توابع: ez_get_user_ip
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3855-3867)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_get_user_ip() {

    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP']))
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    elseif (!empty($_SERVER['HTTP_CLIENT_IP']))
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    else
        $ip = $_SERVER['REMOTE_ADDR'];

    return $ip;
}
