<?php
/**
 * ez_queryable_set_hottest_products2 (+1 more)
 *
 * توابع: ez_queryable_set_hottest_products2, ez_queryable_set_hottest_products هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 202-299)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_queryable_set_hottest_products2() {
    add_action('woocommerce_after_register_post_type', 'ez_queryable_set_hottest_products');
}
/*===============================*/
/** @deprecated Incremental ranking in ez_core ProductRanking; cron disabled when ez_core_incremental_ranking_enabled. */
function ez_queryable_set_hottest_products() {
    global $wpdb;

    $wpdb->get_results( "DELETE FROM hottest_products WHERE time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY))");

    $C = 4.3; // میانگین کلی w_rate
    $m = 15;  // حداقل تعداد نظر برای وزن‌دهی (پارامتر بیز)

    $rows = $wpdb->get_results( "SELECT * FROM hottest_products", ARRAY_A );

    $hottest = [];
    foreach ( $rows as $row )
        if ( !isset( $hottest[$row['product_id']] ) ) {
            $hottest[$row['product_id']]['w_rate']              = $row['w_rate'] * $row['w_comments_count']; // مجموع rate ها و نه میانگین آنها
            $hottest[$row['product_id']]['w_comments_count']    = $row['w_comments_count'];
        } else {
            $hottest[$row['product_id']]['w_rate']              += $row['w_rate'] * $row['w_comments_count'];
            $hottest[$row['product_id']]['w_comments_count']    += $row['w_comments_count'];
        }

    foreach ( $hottest as $product_id => $hottest_item ) :

        $w_rate             = $hottest_item['w_rate'] / $hottest_item['w_comments_count']; // به دست آوردن میانیگن rate ها
        $w_comments_count   = $hottest_item['w_comments_count'];

        $bayesian_score = get_bayesian_score($w_rate, $w_comments_count, $C, $m); // فرمول بیزین راهی برای ترکیب پارامترها با هم که اینجا امتیاز میانگین کامنت ها و تعداد کامنت هارو ترکیب کردیم

        $normalized_bayesian_score = 0.6 * $bayesian_score + 0.4 * log($w_comments_count + 1);

        $hot_score[$product_id] = $normalized_bayesian_score;

        saeed_print([
                'product_id'                => $product_id,
                'w_rate'                    => $w_rate,
                'w_comments_count'          => $w_comments_count,
                'bayesian_score'            => $bayesian_score,
                'normalized_bayesian_score' => $normalized_bayesian_score,
                'hot_score'                 => $hot_score[$product_id],
        ]);

    endforeach;

    $penalty_product_ids = [24194,354862,576159,28325,383915,25616,382454,425891,587887,741186,770574]; // سیتن satan، جنگل تاریک، هوwho، موزه وارانسی، هتل لستر، ماماچه، پازوزو، اقرار، مهوا، زندان جن
    if (time() <= strtotime('2026-02-19 23:59:59'))
        foreach ($penalty_product_ids as $pid)
            if (isset($hot_score[$pid]) || array_key_exists($pid, $hot_score))
                unset($hot_score[$pid]);

    asort($hot_score);

//    saeed_store($hot_score);

    $product_data = [];
    foreach ( $hot_score as $product_id => $count )
        $product_data[] = $product_id;

    $hottest_products_ids = array_reverse( $product_data );

    // به‌روزرسانی مستقیم جدول products_order در دیتابیس queries (بدون ez_webservice)
    if ( defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
        $ez_host = defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : DB_HOST;
        $ez_conn = new \mysqli( $ez_host, DB_EXT_USER, DB_EXT_PASSWORD, DB_EXT_NAME );
        if ( ! $ez_conn->connect_error ) {
            $ez_conn->set_charset( 'utf8mb4' );
            $res         = $ez_conn->query( "SELECT * FROM products_order LIMIT 1" );
            $row_exists  = $res && $res->num_rows > 0;
            $recent      = [];
            if ( $row_exists && ( $row = $res->fetch_assoc() ) && ! empty( $row['recent'] ) ) {
                $recent = @unserialize( $row['recent'] );
                if ( ! is_array( $recent ) ) {
                    $recent = [];
                }
            }
            if ( $res ) {
                $res->free();
            }
            $hottest_lookup = array_flip( $hottest_products_ids );
            $recent_to_add  = [];
            foreach ( $recent as $rid ) {
                if ( ! isset( $hottest_lookup[ $rid ] ) ) {
                    $recent_to_add[] = $rid;
                }
            }
            $final_products = array_merge( $hottest_products_ids, $recent_to_add );
            $ser            = $ez_conn->real_escape_string( serialize( $final_products ) );
            if ( $row_exists ) {
                $ez_conn->query( "UPDATE products_order SET hottest = '{$ser}' LIMIT 1" );
            } else {
                $ez_conn->query( "INSERT INTO products_order (hottest) VALUES ('{$ser}')" );
            }
            $ez_conn->close();
        }
    }
}
