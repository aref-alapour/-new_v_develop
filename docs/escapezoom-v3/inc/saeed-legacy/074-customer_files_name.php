<?php
/**
 * customer_files_name
 *
 * توابع: customer_files_name
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6021-6023)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function customer_files_name ($dir, $name, $ext){
    return floor(microtime(true) * 1000) . $ext;
}
