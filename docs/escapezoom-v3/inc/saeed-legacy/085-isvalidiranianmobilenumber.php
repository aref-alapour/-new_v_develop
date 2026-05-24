<?php
/**
 * isValidIranianMobileNumber
 *
 * توابع: isValidIranianMobileNumber
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6367-6369)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function isValidIranianMobileNumber($number) {
    return preg_match('/^09\d{9}$/', $number) === 1;
}
