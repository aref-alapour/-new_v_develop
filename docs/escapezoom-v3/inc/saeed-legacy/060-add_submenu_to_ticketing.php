<?php
/**
 * add_submenu_to_ticketing
 *
 * توابع: add_submenu_to_ticketing هوک‌ها: admin_menu
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5443-5454)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('admin_menu', 'add_submenu_to_ticketing', 10);
function add_submenu_to_ticketing(){
    add_submenu_page(
        'edit.php?post_type=ticketing',
        'مانیتورینگ',
        'مانیتورینگ',
        'manage_options',
        'monitoring',
        'ticket_monitoring_callback_func',
        2
    );
}
