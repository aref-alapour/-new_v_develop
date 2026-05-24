<?php
/**
 * normalizePhoneNumber
 *
 * توابع: normalizePhoneNumber
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6350-6366)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function normalizePhoneNumber($number) {
    $number = preg_replace('/\D/', '', persianToEnglish($number));

    if (strpos($number, '98') === 0)
        $number = substr($number, 2);
    elseif (strpos($number, '0') === 0)
        $number = substr($number, 1);
    elseif (strpos($number, '+98') === 0)
        $number = substr($number, 3);

    if (strlen($number) == 10)
        return '0' . $number;
    elseif (strlen($number) == 11 && $number[0] === '9')
        return $number;

    return false;
}
