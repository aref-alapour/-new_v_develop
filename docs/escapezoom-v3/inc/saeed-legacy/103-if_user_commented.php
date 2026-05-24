<?php
/**
 * if_user_commented
 *
 * توابع: if_user_commented
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6653-6669)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function if_user_commented ($phone, $product_id) {

    $user = get_user_by( 'login', $phone );
    if ( $user && isset( $user->ID ) ) {
        $user_id = $user->ID;

        $comments = get_comments([
            'user_id'      => $user_id,
            'type'         => 'review',
            'status'       => 'approve',
            'post_id'      => $product_id
        ]);

        return $comments ? true : false;
    }
    return false;
}
