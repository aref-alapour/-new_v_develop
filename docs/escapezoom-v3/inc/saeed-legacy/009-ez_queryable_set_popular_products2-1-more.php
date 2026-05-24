<?php
/**
 * ez_queryable_set_popular_products2 (+1 more)
 *
 * توابع: ez_queryable_set_popular_products2, ez_queryable_set_popular_products هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 300-390)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_queryable_set_popular_products2() {
    add_action('woocommerce_after_register_post_type', 'ez_queryable_set_popular_products');
}
/*===============================*/
/** @deprecated Incremental ranking in ez_core ProductRanking; cron disabled when ez_core_incremental_ranking_enabled. */
function ez_queryable_set_popular_products() {

    $penalty_products = [383915, 382454, 24194, 508099, 52537, 354862, 261593, 261541, 272235,770574,776644 ];

    $args = array (
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'meta_query'        => array (
                    array(
                            'key'     => 'product_state',
                            'value'   => 'active',
                            'compare' => 'LIKE',
                    ),
            ),
    );
    $query = new WP_Query($args);

    $popular_products = [];
    while ($query->have_posts()) : $query->the_post();

        $comments_count = $comments_count_penalty = (int)get_post_meta(get_the_ID(), 'comments_count_new', TRUE);
        $rate           = get_post_meta(get_the_ID(), 'product_rates', TRUE);

        if ( in_array(get_the_ID(), $penalty_products) )
            $comments_count_penalty = $comments_count_penalty / 2.5;

        $temp = [];
        $temp['comments_count'] = $comments_count_penalty;
        $temp['rate']           = $comments_count != 0 ? ((int)$rate[1094] + (int)$rate[1095] + (int)$rate[1096] + (int)$rate[1097] + (int)$rate[1098]) / 5 / 20 / $comments_count : 1;

        $popular_products[get_the_ID()] = $temp;
    endwhile;
    wp_reset_postdata();

    $penalty_product_ids = [24194,354862,576159,28325,383915,25616,382454,425891,587887,741186,770574]; // سیتن satan، جنگل تاریک، هوwho، موزه وارانسی، هتل لستر، ماماچه، پازوزو، اقرار، مهوا، زندان جن
    if (time() <= strtotime('2026-02-19 23:59:59'))
        foreach ($penalty_product_ids as $pid)
            if (isset($popular_products[$pid]))
                unset($popular_products[$pid]);

    // محاسبهٔ امتیاز محبوب و به‌روزرسانی مستقیم products_order.popular در دیتابیس queries
    $products_alt = [];
    if ( ! empty( $popular_products ) && defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
        $ez_host  = defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : DB_HOST;
        $ez_conn  = new \mysqli( $ez_host, DB_EXT_USER, DB_EXT_PASSWORD, DB_EXT_NAME );
        if ( ! $ez_conn->connect_error ) {
            $ez_conn->set_charset( 'utf8mb4' );
            $ids_in = implode( ',', array_map( 'absint', array_keys( $popular_products ) ) );
            $res    = $ez_conn->query( "SELECT product_id, views, views30 FROM product_views WHERE product_id IN ({$ids_in})" );
            $pv_by_id = [];
            if ( $res ) {
                while ( $row = $res->fetch_assoc() ) {
                    $pv_by_id[ (int) $row['product_id'] ] = [
                        'views'   => (int) ( $row['views'] ?? 0 ),
                        'views30' => 0,
                    ];
                    $arr = @unserialize( $row['views30'] ?? '' );
                    if ( is_array( $arr ) ) {
                        $pv_by_id[ (int) $row['product_id'] ]['views30'] = array_sum( array_slice( $arr, -31, 30, true ) );
                    }
                }
                $res->free();
            }
            foreach ( $popular_products as $product_id => $data ) {
                $comments_count = (float) ( $data['comments_count'] ?? 0 );
                $rate           = (float) ( $data['rate'] ?? 0 );
                $pv             = $pv_by_id[ (int) $product_id ] ?? [ 'views' => 0, 'views30' => 0 ];
                $views          = $pv['views'];
                $views30        = $pv['views30'];
                $products_alt[ $product_id ] = round( ( $comments_count * $rate ) + ( $views * $views30 / 925000 ) );
            }
            asort( $products_alt );
            $popular_list = array_reverse( array_keys( $products_alt ) );
            $res          = $ez_conn->query( "SELECT 1 FROM products_order LIMIT 1" );
            $row_exists   = $res && $res->num_rows > 0;
            if ( $res ) { $res->free(); }
            $ser = $ez_conn->real_escape_string( serialize( $popular_list ) );
            if ( $row_exists ) {
                $ez_conn->query( "UPDATE products_order SET popular = '{$ser}' LIMIT 1" );
            } else {
                $ez_conn->query( "INSERT INTO products_order (popular) VALUES ('{$ser}')" );
            }
            $ez_conn->close();
        }
    }
}
