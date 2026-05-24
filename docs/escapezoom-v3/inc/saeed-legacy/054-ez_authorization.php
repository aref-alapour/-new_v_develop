<?php
/**
 * ez_authorization
 *
 * توابع: ez_authorization
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5318-5331)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_authorization( $required ) {

    if ( strpos( $_SERVER['REQUEST_URI'], 'api/v1' ) ) {
        $token = Jwt_Auth_Public::validate_token(false, $required);

        if ( $required )
            if ( is_wp_error($token) )
                wp_send_json_error( array('error' => $token->get_error_message()), $token->error_data[$token->get_error_code()]['status'] );

        return $token;
    }

    return false;
}
