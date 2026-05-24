<?php
/**
 * ez_login_automatically
 *
 * توابع: ez_login_automatically
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6370-6402)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_login_automatically($user) {

    try {
        if (is_user_logged_in())
            wp_logout();

        if ( !is_wp_error($user) ) {

            if ( $user ) {

                $user_id = $user->ID;

                wp_clear_auth_cookie();
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
//
//                if ( isset( $_GET['ref'] ) && $_GET['ref'] == 'checkout' )
//                    wp_redirect( home_url('checkout') );
//                else
//                    wp_redirect( home_url('my-account') );
            }

            if (is_user_logged_in())
                return true;
        }

    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }

    return false;
}
