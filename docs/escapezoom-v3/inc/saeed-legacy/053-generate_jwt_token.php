<?php
/**
 * generate_jwt_token
 *
 * توابع: generate_jwt_token
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5295-5317)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function generate_jwt_token($user) {

    $secret_key = defined('JWT_AUTH_SECRET_KEY') ? JWT_AUTH_SECRET_KEY : false;
    $issuedAt   = time();
    $notBefore  = apply_filters('jwt_auth_not_before', $issuedAt, $issuedAt);
    $expire     = apply_filters('jwt_auth_expire', $issuedAt + (DAY_IN_SECONDS * 10000), $issuedAt);

    $token = array(
        'iss' => get_bloginfo('url'),
        'iat' => $issuedAt,
        'nbf' => $notBefore,
        'exp' => $expire,
        'data' => array(
            'user' => array(
                'id' => $user->ID,
            ),
        ),
    );

    $token = \Firebase\JWT\JWT::encode(apply_filters('jwt_auth_token_before_sign', $token, $user), $secret_key);

    return array ( 'token' => $token );
}
