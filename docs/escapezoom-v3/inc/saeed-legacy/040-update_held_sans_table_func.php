<?php
/**
 * update_held_sans_table_func
 *
 * توابع: update_held_sans_table_func
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3762-3854)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @deprecated Use EscapeZoom\Core\Modules\ProductRanking\Services\RankingBackfillService::rebuildHeldOrdersList()
 */
function update_held_sans_table_func () {
    if ( class_exists( '\EscapeZoom\Core\Modules\ProductRanking\Services\RankingBackfillService' ) ) {
        \EscapeZoom\Core\Modules\ProductRanking\Services\RankingBackfillService::rebuildHeldOrdersList();

        return;
    }

    global $wpdb;

    $penalty_products = [73114, 261541, 261593];

    $partially_orders = [];
    $temp = $wpdb->get_results( "SELECT wp_markting.order_id FROM wp_markting WHERE order_status = 'wc-partially-paid' OR order_status = 'wc-held' OR order_status = 'wc-completed'OR order_status = 'wc-walletx'OR order_status = 'wc-completed-paid'", ARRAY_A );
    foreach ( $temp as $order_arr )
        $partially_orders[] = $order_arr['order_id'];
    $partially_orders = implode(',', $partially_orders);

    // خواندن مستقیم از دیتابیس queries (بدون ajax/ez_reservation)
    $rows = [];
    if ( ! empty( $partially_orders ) && defined( 'DB_EXT_NAME' ) && defined( 'DB_EXT_USER' ) && defined( 'DB_EXT_PASSWORD' ) ) {
        $ez_host = defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : DB_HOST;
        $ez_conn = new \mysqli( $ez_host, DB_EXT_USER, DB_EXT_PASSWORD, DB_EXT_NAME );
        if ( ! $ez_conn->connect_error ) {
            $ez_conn->set_charset( 'utf8mb4' );
            $ids = array_filter( array_map( 'absint', explode( ',', $partially_orders ) ) );
            if ( ! empty( $ids ) ) {
                $ids_str = implode( ',', $ids );
                $res = $ez_conn->query( "SELECT wc_order_id AS ID, booking_time AS booking_time FROM wp_zb_booking_history WHERE wc_order_id IN ({$ids_str})" );
                if ( $res ) {
                    while ( $row = $res->fetch_assoc() ) {
                        $rows[] = $row;
                    }
                    $res->free();
                }
            }
            $ez_conn->close();
        }
    }

    // جمع‌آوری order_id و booking_time برای رزروهای واجد شرایط
    $order_to_booking_time = [];
    foreach ( $rows as $row ) {
        if ( (int) $row['booking_time'] < time() - 4 * 3600 && time() - 30 * 24 * 3600 < (int) $row['booking_time'] ) {
            $order_to_booking_time[ (int) $row['ID'] ] = (int) $row['booking_time'];
        }
    }

    if ( empty( $order_to_booking_time ) ) {
        $wpdb->query( "DELETE FROM held_orders_list WHERE held_time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))" );
        return;
    }

    $tbl_markting = $wpdb->prefix . 'markting';
    $order_ids_in = implode( ',', array_map( 'absint', array_keys( $order_to_booking_time ) ) );
    $markting_rows = $wpdb->get_results(
        "SELECT order_id, game_id, order_tickets_quantity, customer_id, customer_level
         FROM `{$tbl_markting}`
         WHERE order_id IN ({$order_ids_in})",
        ARRAY_A
    );

    foreach ( $markting_rows as $mr ) {
        $order_id   = (int) ( $mr['order_id'] ?? 0 );
        $product_id = (int) ( $mr['game_id'] ?? 0 );
        $quantity   = (float) ( $mr['order_tickets_quantity'] ?? 0 );
        $user_id    = (int) ( $mr['customer_id'] ?? 0 );
        $level      = (int) ( $mr['customer_level'] ?? 1 );
        if ( $level < 1 || $level > 4 ) {
            $level = 1;
        }

        if ( ! isset( $order_to_booking_time[ $order_id ] ) || $product_id <= 0 || $quantity <= 0 ) {
            continue;
        }
        if ( in_array( $product_id, $penalty_products, true ) ) {
            $quantity = $quantity / 1.5;
        }

        $duplicate_check = $wpdb->get_var( $wpdb->prepare(
            "SELECT 1 FROM held_orders_list WHERE order_id = %d AND room_id = %d LIMIT 1",
            $order_id,
            $product_id
        ) );
        if ( $duplicate_check ) {
            continue;
        }

        $wpdb->insert( 'held_orders_list', array(
            'room_id'   => $product_id,
            'order_id'  => $order_id,
            'count'     => (int) round( $quantity ),
            'user_id'   => $user_id,
            'level'     => $level,
            'held_time' => $order_to_booking_time[ $order_id ]
        ) );
    }

    $wpdb->query( "DELETE FROM held_orders_list WHERE held_time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))" );
}
