<?php
/**
 * misc
 *
 * بلوک بدون تابع یا GET مشخص؛ احتمالاً bootstrap یا مارک‌آپ.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6098-6105)
 * نوع: متفرقه
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_role(
    'sans_manager',
    'مدیر سانس',
    array(
        'read'          => true,  // true allows this capability
        'edit_posts'    => false,
    )
);
