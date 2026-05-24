<?php
function get_orders_ids_by_product_id( $products_id, $order_status, $date_range=false, $limit=10000000, $page=1, $total=false ) {
    global $wpdb;

    $query = "";

    if ( $date_range && !empty( $date_range ) ) {
        $start_date = date("Y-m-d", (int)$date_range[0]);
        $end_date   = date("Y-m-d", (int)$date_range[1]);

        $query = "AND posts.post_date BETWEEN '$start_date' AND '$end_date'";
    }

    $query .= "AND (";
    $i = 0;
    foreach ( $products_id as $product_id ) {
        if ( !$i )
            $query .= "order_item_meta.meta_value = $product_id ";
        else
            $query .= "OR order_item_meta.meta_value = $product_id ";

        $i++;
    }
    $query .= ")";

    $offset = ($page - 1) * $limit;

    if ( $total ) {
        $results = $wpdb->get_col("
        SELECT SUM(total) AS total FROM (
            SELECT COUNT(*) AS total  
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            $query
            ORDER BY posts.post_date DESC
        ) AS total;
    ");

    } else {

        $results = $wpdb->get_col("
            SELECT order_items.order_id
            FROM {$wpdb->prefix}woocommerce_order_items AS order_items
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
            LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
            WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
            AND order_items.order_item_type = 'line_item'
            AND order_item_meta.meta_key = '_product_id'
            $query
            ORDER BY posts.post_date DESC
            LIMIT $limit
            OFFSET $offset;
        ");
    }

    return $results;
}
function get_owner_id_by_product_id ($product_id) {
    return get_post_meta($product_id, 'user_ebtal', true);
}