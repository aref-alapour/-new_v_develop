<?php
/**
 * ticketing_declare
 *
 * توابع: ticketing_declare هوک‌ها: init, admin_init
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5400-5437)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'ticketing_declare' );
function ticketing_declare() {
    register_post_type( 'ticketing',
        array (
            'labels' => array (
                'name'          => 'تیکت ها',
                'singular_name' => 'ticketing',
                'search_items'  => 'جستجوی تیکت ها',
                'edit_item'     => 'پاسخ به تیکت',
            ),
            'rewrite'               => array('slug' => 'ticketing'), // rewrite url
            'supports'              => array( 'title' ),
            'publicly_queryable'    => false,
            'query_var'             => false,
            'public'                => true,
            'has_archive'           => false,
            'map_meta_cap'          => true,
            'capabilities'          => array (
                'edit_post'             => 'edit_ticket',
                'edit_posts'            => 'edit_tickets',
                'edit_others_posts'     => 'edit_other_tickets',
                'publish_posts'         => 'publish_tickets',
                'read_post'             => 'read_ticket',
                'read_private_posts'    => 'read_private_tickets',
                'delete_post'           => 'delete_ticket',
                'create_posts'          => false,
            ),
            'menu_position'         => 3,
            'menu_icon'             => get_template_directory_uri() . '/img/admin-icon.png',

        )
    );
}
//===================================================//
//add_action( 'admin_init', 'ticketing_submenu_setting' );
//function ticketing_submenu_setting() {
//    register_setting( 'ticketing_submenu_option', 'sz_template_brands_optionsx', 'template_validate_options' );
//}
