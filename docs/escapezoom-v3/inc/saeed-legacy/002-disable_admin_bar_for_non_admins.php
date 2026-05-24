<?php
/**
 * disable_admin_bar_for_non_admins
 *
 * توابع: disable_admin_bar_for_non_admins هوک‌ها: init, show_admin_bar, after_setup_theme
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 17-29)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//add_action('init', 'admin_bar' );
//function admin_bar() {
//    if(is_user_logged_in())
//        add_filter( 'show_admin_bar', '__return_false' , 1000 );
//}

add_action('after_setup_theme', 'disable_admin_bar_for_non_admins');

function disable_admin_bar_for_non_admins() {
    if ( has_role( 'customer' ) || has_role( 'compiler' ) || has_role( 'sans_manager' ) ) {
        add_filter('show_admin_bar', '__return_false');
    }
}
