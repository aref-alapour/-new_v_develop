<?php
/**
 * pending_posts_bubble_wpse_89028 (+4 more)
 *
 * توابع: pending_posts_bubble_wpse_89028, recursive_array_search_php_91365, wpse246143_add_admin_quick_link, wpse246143_register_waiting, wpse246143_map_waiting هوک‌ها: admin_menu, views_edit-ticketing, init, parse_query
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5921-6004)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', 'pending_posts_bubble_wpse_89028', 999 );
function pending_posts_bubble_wpse_89028() {
    global $menu;

    $args = array( 'public' => true );
    $post_types = get_post_types( $args );

    foreach( $post_types as $pt ) {
        if ( $pt == 'ticketing' ) {
            $cpt_count = wp_count_posts( $pt );

            if ( $cpt_count->pending ) {
                $suffix = ( 'post' == $pt ) ? '' : "?post_type=$pt";
                $key = recursive_array_search_php_91365( "edit.php$suffix", $menu );

                if ( !$key ) return;

                $menu[$key][0] .= sprintf(
                    '<span class="update-plugins count-%1$s" style="background-color:#d63638;color:white"><span class="plugin-count">%1$s</span></span>',
                    $cpt_count->pending
                );
            }
        }
    }
}
//********************************************************/
function recursive_array_search_php_91365( $needle, $haystack ) {
    foreach( $haystack as $key => $value ) {
        $current_key = $key;
        if( $needle === $value OR ( is_array( $value ) && recursive_array_search_php_91365( $needle, $value ) !== false ) ) {
            return $current_key;
        }
    }
    return false;
}
//********************************************************/
add_filter( 'views_edit-ticketing', 'wpse246143_add_admin_quick_link' );
function wpse246143_add_admin_quick_link( $views ) {
    $post_type = 'ticketing';

    if ( ! isset( $_GET['post_type'] ) || $post_type !== $_GET['post_type'] )
        return $views;

    $link_class = false;
/**
 * GET: waiting
 *
 * هدف: فیلتر waiting تیکت
 * استفاده: ادمین
 * وابستگی: parse_query
 * امنیت: ادمین
 * وضعیت: ticketing
 * منبع: saeed-legacy/071-pending_posts_bubble_wpse_89028-4-more.php:58
 */
    if ( isset( $_GET['waiting'] ) && 'true' === $_GET['waiting'] )
        $link_class = 'current';

    $cpt_count = wp_count_posts( $post_type );

    $result = new WP_Query( [
        'post_type'  => $post_type,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'meta_key'   => 'ticket_type',
        'meta_value' => 'اعلام مغایرت',
    ] );

    // Generate the link for our filter.
    $views['ticketing_waiting'] = sprintf( '<a href="%s" class="%s">%s<span class="count">(%d)</span></a>',
        admin_url( "edit.php?post_type={$post_type}&waiting=true" ),
        $link_class,
        'در انتظار',
        $result->found_posts
    );

    return $views;
}
/*=========================================*/
add_action( 'init', 'wpse246143_register_waiting' );
function wpse246143_register_waiting() {
    global $wp;

    $wp->add_query_var( 'waiting' );
}
/*=========================================*/
add_action( 'parse_query', 'wpse246143_map_waiting' );
function wpse246143_map_waiting( $wp_query ) {
    $meta_value = $wp_query->get( 'waiting' );

    if ( true == $meta_value ) {
        $wp_query->set( 'meta_key', 'waiting' );
        $wp_query->set( 'meta_value', $meta_value );
    }
}
