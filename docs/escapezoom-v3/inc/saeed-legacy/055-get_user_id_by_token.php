<?php
/**
 * get_user_id_by_token
 *
 * توابع: get_user_id_by_token
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5332-5338)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_user_id_by_token ( $token ) {

    if ( !empty ( $token ) )
        return $token->data->user->id;

    return false;
}
