<?php
/**
 * get_term_link_flat
 *
 * توابع: get_term_link_flat
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6753-6755)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_term_link_flat( $term, $taxonomy = 'category' ) {
    return home_url( '/city/' . $term->slug . '/' );
}
