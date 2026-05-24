<?php
/**
 * contact_us_declare
 *
 * توابع: contact_us_declare هوک‌ها: init
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5369-5399)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'contact_us_declare' );
function contact_us_declare() {
    $args = array(
        'public' => true,
        'labels' => array (
            'name'          => 'تماس با',
            'singular_name' => 'contacting',
            'search_items'  => 'جستجوی تماس با',
            'edit_item'     => 'پاسخ به تماس با ما',
        ),
        'supports' => array( 'title', 'editor', 'thumbnail' ),
        'rewrite'               => array('slug' => 'contacting'),
        'publicly_queryable'    => false,
        'query_var'             => false,
        'has_archive'           => false,
        'map_meta_cap'          => true,
        'capabilities'          => array (
            'edit_post'             => 'edit_contacting',
            'edit_posts'            => 'edit_contactings',
            'edit_others_posts'     => 'edit_other_contactings',
            'publish_posts'         => 'publish_contactings',
            'read_post'             => 'read_contacting',
            'read_private_posts'    => 'read_private_contactings',
            'delete_post'           => 'delete_contacting',
            'create_posts'          => false,
        ),
        'menu_position'         => 2,
        'menu_icon'             => get_template_directory_uri() . '/img/admin-icon.png',
    );
    register_post_type( 'contacting', $args );
}
