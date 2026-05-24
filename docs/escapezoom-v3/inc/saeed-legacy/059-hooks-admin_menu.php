<?php
/**
 * hooks: admin_menu
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5438-5442)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () { // remove publish button
    remove_meta_box( 'submitdiv', 'ticketing', 'side' );
    remove_meta_box( 'rmp-rate-id', 'ticketing', 'side' );
    remove_meta_box( 'litespeed_meta_boxes', 'ticketing', 'side' );
} );
