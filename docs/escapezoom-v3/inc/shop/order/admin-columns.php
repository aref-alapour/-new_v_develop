<?php
/** lines 49-118 → shop/order/admin-columns.php */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'manage_edit-shop_order_columns', 'custom_shop_order_column', 20 );
function custom_shop_order_column($columns) {
    $reordered_columns = array();

    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if ( $key ==  'order_status' ) {
            $reordered_columns['sans_time'] = __( 'سانس','theme_domain');
            $reordered_columns['deposit']   = __( 'سپرده','theme_domain');
        }
    }
    return $reordered_columns;
}

add_filter( 'manage_edit-shop_order_sortable_columns', 'custom_shop_order_sortable_columns' );
function custom_shop_order_sortable_columns( $sortable_columns ) {
    $sortable_columns['sans_time'] = 'sans_time';
    $sortable_columns['deposit']    = 'deposit';
    return $sortable_columns;
}
/****************************************************************************************************************************************/
add_action( 'manage_shop_order_posts_custom_column' , 'custom_orders_list_column_content', 20, 2 );
function custom_orders_list_column_content( $column, $post_id ) {
    global $wpdb;
    static $booking_cache = [];

    switch ( $column )
    {
        case 'sans_time' :
            $m = $post_id;

            // Use cache to avoid repeated queries
            if (!isset($booking_cache[$m])) {
                static $booking_table_exists = null;

                if ( null === $booking_table_exists ) {
                    $booking_table_exists = (bool) $wpdb->get_var(
                        $wpdb->prepare( 'SHOW TABLES LIKE %s', 'wp_zb_booking_history' )
                    );
                }

                if ( ! $booking_table_exists ) {
                    $booking_cache[ $m ] = null;
                } elseif (function_exists('medoo')) {
                    // Try to use medoo directly first (faster)
                    $medoo = medoo();
                    if ($medoo) {
                        try {
                            $booking = $medoo->get('wp_zb_booking_history', 'booking_time', [
                                'wc_order_id' => $m,
                                'ORDER' => ['booking_id' => 'DESC']
                            ]);
                            $booking_cache[$m] = $booking;
                        } catch ( PDOException $e ) {
                            $booking_cache[ $m ] = null;
                        }
                    } else {
                        $booking_cache[$m] = null;
                    }
                } else {
                    // Fallback to original method
                    $args = [
                        "single_value"  => true,
                        "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $m ORDER BY `booking_id` DESC",
                    ];
                    $response   = ez_reservation( array('type' => 'query_execution', 'data' => $args) );
                    $row        = (array)json_decode($response);
                    $booking_cache[$m] = isset($row['booking_time']) ? $row['booking_time'] : null;
                }
            }

            $n = $booking_cache[$m];
            if( $n )
                echo '<span style="color: #00a500;font-weight: bold;font-size: 14px;">' . wp_date('H:i ..... Y-m-d', $n) . '</span>';

            break;

        case 'deposit' :
            $pish = get_post_meta( $post_id, "_order_total_2", true );
            echo number_format( (int)($pish ? : get_post_meta( $post_id, "_order_total", true )) ) . 'تومان';

            break;
    }
}
