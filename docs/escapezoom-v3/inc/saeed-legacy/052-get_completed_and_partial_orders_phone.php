<?php
/**
 * get_completed_and_partial_orders_phone
 *
 * توابع: get_completed_and_partial_orders_phone هوک‌ها: init
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5164-5294)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('init', 'get_completed_and_partial_orders_phone');
function get_completed_and_partial_orders_phone() {
/**
 * GET: get_completed_and_partial_orders_phone
 *
 * هدف: استخراج تلفن سفارش‌های completed/partial
 * استفاده: دستی
 * وابستگی: wpdb, orders
 * امنیت: بدون احراز هویت
 * وضعیت: بررسی حذف
 * منبع: saeed-legacy/052-get_completed_and_partial_orders_phone.php:16
 */
    if ( isset( $_GET['get_completed_and_partial_orders_phone'] ) ) {
        global $wpdb;

//    $orders = $wpdb->get_results( "SELECT ID FROM wp_posts WHERE post_type = 'shop_order' AND (post_status = 'wc-partially-paid' OR post_status = 'wc-completed')", ARRAY_A );
//
//    $products_phones = [];
//    $products_phones = get_option('1703946962134', true);
//
//    foreach ( $orders as $order_id ) {
//        $order_id = $order_id['ID'];
//
//        $product_id = get_post_meta($order_id, 'code_otagh', true);
//
//        if ( !$product_id ) {
//            $order = wc_get_order( $order_id );
//            if ( !empty( $order ) )
//                foreach ($order->get_items() as $item)
//                    $product_id = $item['product_id'];
//        }
//
//        $players_phone  = get_post_meta($order_id, 'players_phone', true);
//
//        if ( empty( $players_phone ) )
//            $players_phone = [get_post_meta($order_id, '_billing_phone', true)];
//
//        foreach ( $players_phone as $player_phone ) {
//
//            $persian        = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
//            $english        = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
//            $player_phone   = str_replace($persian, $english, $player_phone);
//            $player_phone   = preg_replace('/^\+?98|\|98|\D/', '', ($player_phone));
//            $player_phone   = ltrim($player_phone, '0');
//
//            if( !in_array($player_phone, (array)$products_phones[$product_id]) )
//                if (strlen((int)$player_phone) == 10)
//                    $products_phones[$product_id][] = $player_phone;
//        }
//    }
//
//    saeed_store($products_phones);

        /************************************************************************************************/

//    $products_phones = get_option('1703946962135', true);
//
//    foreach ( $products_phones as $product_id => $product_phones ) {
//        $product_phones = implode(',', $product_phones);
//
//        $comments = $wpdb->get_results( "SELECT comment_ID  FROM `wp_comments` WHERE `comment_post_ID` = {$product_id} AND comment_author NOT IN ({$product_phones}) AND comment_approved LIKE 1 AND comment_type = 'review' AND comment_date > '2023-06-01 00:00:00';", ARRAY_A );
//
//        foreach ( $comments as $comment )
//            $comments_arr[] = $comment['comment_ID'];
//    }
//
//
//    saeed_store($comments_arr);

        /************************************************************************************************/

//    $args_query = array (
//        'post_type'         => 'product',
//        'post_status'       => 'publish',
//        'posts_per_page'    => -1,
//        'orderby'           => 'ID',
//        'order'             =>'ASC',
//    );
//    $the_query = new WP_Query( $args_query );
//
//    if ( $the_query->have_posts() ) :
//        while ( $the_query->have_posts() ) : $the_query->the_post();
//        global $product;
//
        //        $product_id = $product->get_id();
        //
        ////        $comments = $wpdb->get_results( "SELECT comment_ID  FROM `wp_comments` WHERE `comment_post_ID` = {$product_id} AND comment_approved LIKE 1 AND comment_type = 'review'", ARRAY_A );
        ////
        ////        $comments_count[$product_id] = 0;
        ////
        ////        foreach ( $comments as $comment )
        ////            $comments_count[$product_id]++;
        //            ;
        //            $comments_count[$product_id] = get_the_terms($product_id, 'product_cat')[0]->name;
        //
//
//        endwhile;
//    endif;
//
//
//    saeed_store($comments_count);

        /************************************************************************************************/
        $args_query = array (
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'orderby'           => 'ID',
            'order'             => 'ASC',
//        'post__in'          => array(2762),
        );
        $the_query = new WP_Query( $args_query );

        if ( $the_query->have_posts() ) :
            while ( $the_query->have_posts() ) : $the_query->the_post();
                global $product;

                $product_id = $product->get_id();

                $comments = $wpdb->get_results( "SELECT *  FROM `wp_comments` WHERE `comment_post_ID` = {$product_id} AND comment_approved LIKE 1 AND comment_type = 'review' AND comment_date > '2023-05-01 17:06:53'", ARRAY_A );

                foreach ( $comments as $comment )
                    if ( $comment['comment_author'] && is_numeric ( $comment['comment_author'] ) )
                        $comments_phones[$product_id][$comment['comment_author']][] = $comment['comment_ID'];

                foreach ( $comments_phones as $product_id => $phones )
                    foreach ( $phones as $phone => $comment_ids )
                        if ( count( $comment_ids ) > 1 )
                            $comments_phones2[$product_id][$phone] = $comment_ids;

            endwhile;
        endif;

        foreach ( $comments_phones2 as $product_id => $phones )
            foreach ( $phones as $phone => $comment_ids )
                foreach ( $comment_ids as $key => $comment_id )
                    if ( $key > 0 )
                        $comments_arr[] = $comment_id;

    }
}
