<?php
/**
 * ticketing_admin_seen
 *
 * توابع: ticketing_admin_seen هوک‌ها: admin_head
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5842-5848)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_head', 'ticketing_admin_seen' );
function ticketing_admin_seen() {
    global $post;

    if ( isset( $post ) && $post->post_type === 'ticketing' && is_admin() && $_GET['action'] === 'edit' )
        update_post_meta($post->ID, 'admin_seen', 1);
}
