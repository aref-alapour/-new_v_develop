<?php
/**
 * hooks: comment_form_default_fields
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6586-6594)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('comment_form_default_fields', function ($fields) {
    if(isset($fields['email'])){
        unset($fields['email']);
    }
    if(isset($fields['cookies'])){
        unset($fields['cookies']);
    }
    return $fields;
});
