<?php
/**
 * get_token_from_header
 *
 * توابع: get_token_from_header
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5339-5368)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_token_from_header (){

    $auth = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : false;

    if (!$auth)
        $auth = isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] : false;

    if (!$auth) {
        return new WP_Error(
            'jwt_auth_no_auth_header',
            'Authorization header not found.',
            array(
                'status' => 401,
            )
        );
    }

    list($token) = sscanf($auth, 'Bearer %s');
    if (!$token) {
        return new WP_Error(
            'jwt_auth_bad_auth_header',
            'Authorization header malformed.',
            array(
                'status' => 401,
            )
        );
    }

    return $token;
}
