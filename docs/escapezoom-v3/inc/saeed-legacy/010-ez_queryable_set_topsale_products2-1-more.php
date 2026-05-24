<?php
/**
 * ez_queryable_set_topsale_products2 (+1 more)
 *
 * توابع: ez_queryable_set_topsale_products2, ez_queryable_set_topsale_products هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 391-452)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_queryable_set_topsale_products2() {
    add_action('woocommerce_after_register_post_type', 'ez_queryable_set_topsale_products');
}
/*===============================*/
/** @deprecated Incremental ranking in ez_core ProductRanking; cron disabled when ez_core_incremental_ranking_enabled. */
function ez_queryable_set_topsale_products() {
    global $wpdb;

    update_held_sans_table_func();

    $rows = $wpdb->get_results( "SELECT * FROM held_orders_list", ARRAY_A );

    $power_map = [
            1 => 0.2,
            2 => 0.4,
            3 => 1,
            4 => 1,
    ];

    $topsale = [];
    foreach ( $rows as $row )
        if ( !isset( $topsale[$row['room_id']] ) )
            $topsale[$row['room_id']] = $row['count'] * $power_map[$row['level']] ?? 1;
        else
            $topsale[$row['room_id']] += $row['count'] * $power_map[$row['level']] ?? 1;

    asort($topsale);

    $penalty_product_ids = [24194,354862,576159,28325,383915,25616,382454,425891,587887,741186,770574,776644 ]; // سیتن satan، جنگل تاریک، هوwho، موزه وارانسی، هتل لستر، ماماچه، پازوزو، اقرار، مهوا، زندان جن
    if (time() <= strtotime('2026-02-19 23:59:59'))
        foreach ($penalty_product_ids as $pid)
            if (isset($topsale[$pid]) || array_key_exists($pid, $topsale))
                unset($topsale[$pid]);

//    saeed_store($topsale);

    $product_data = [];
    foreach ( $topsale as $product_id => $count )
        $product_data[] = $product_id;

    $topsale_list = array_reverse( $product_data );

    // به‌روزرسانی مستقیم ستون topsale در جدول products_order (دیتابیس queries)
    if ( defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
        $ez_host = defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : DB_HOST;
        $ez_conn = new \mysqli( $ez_host, DB_EXT_USER, DB_EXT_PASSWORD, DB_EXT_NAME );
        if ( ! $ez_conn->connect_error ) {
            $ez_conn->set_charset( 'utf8mb4' );
            $res        = $ez_conn->query( "SELECT 1 FROM products_order LIMIT 1" );
            $row_exists = $res && $res->num_rows > 0;
            if ( $res ) {
                $res->free();
            }
            $ser = $ez_conn->real_escape_string( serialize( $topsale_list ) );
            if ( $row_exists ) {
                $ez_conn->query( "UPDATE products_order SET topsale = '{$ser}' LIMIT 1" );
            } else {
                $ez_conn->query( "INSERT INTO products_order (topsale) VALUES ('{$ser}')" );
            }
            $ez_conn->close();
        }
    }
}
