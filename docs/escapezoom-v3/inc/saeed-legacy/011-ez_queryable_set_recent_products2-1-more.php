<?php
/**
 * ez_queryable_set_recent_products2 (+1 more)
 *
 * توابع: ez_queryable_set_recent_products2, ez_queryable_set_recent_products هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 453-487)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_queryable_set_recent_products2() {
    add_action('woocommerce_after_register_post_type', 'ez_queryable_set_recent_products');
}
/*===============================*/
function ez_queryable_set_recent_products() {

//    file_get_contents("https://impec.ir/ads_managment.php?send");

    $args = array (
        'post_type'         => 'product',
        'post_status'       => 'publish',
        'posts_per_page'    => -1,
        'meta_query'        => array(
            'relation' => 'OR',
            array(
                'key'     => 'product_state',
                'value'   => 'active',
                'compare' => '==',
            ),
            array(
                'key'     => 'product_state',
                'value'   => 'updated',
                'compare' => '==',
            ),
        ),
    );
    $query = new WP_Query($args);

    while ($query->have_posts()) : $query->the_post();
        $product_data[] = get_the_ID();
    endwhile;
    wp_reset_postdata();

    ez_webservice( array('type' => 'recent_products_set', 'data' => $product_data) );
}
