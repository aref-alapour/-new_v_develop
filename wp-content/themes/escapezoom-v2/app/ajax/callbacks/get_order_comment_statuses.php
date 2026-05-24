<?php
$orders_data = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? $_POST['data'] : [];
if ( empty( $orders_data ) ) {
	wp_send_json_error( [ 'status' => 'invalid_payload', 'message' => 'داده سفارش‌ها نامعتبر است.' ] );
}

$current_user_id = get_current_user_id();
if ( $current_user_id <= 0 ) {
	wp_send_json_error( [ 'status' => 'unauthorized', 'message' => 'برای این عملیات وارد شوید.' ] );
}

$is_admin = current_user_can( 'manage_options' );
$order_result = [];

foreach ( $orders_data as $order_data ) {
    if ( ! is_array( $order_data ) ) {
        continue;
    }

    $order_id = isset( $order_data['order_id'] ) ? (int) $order_data['order_id'] : 0;
    $room_id = isset( $order_data['room_id'] ) ? (int) $order_data['room_id'] : 0;
    if ( $order_id <= 0 ) {
        continue;
    }

    if ( $room_id <= 0 ) {
        $room_id = (int) get_post_meta( $order_id, 'code_otagh', true );
    }
    if ( $room_id <= 0 ) {
        continue;
    }

    if ( ! $is_admin ) {
        $owner_ids = array_values(
            array_unique(
                array_filter(
                    array_map(
                        'intval',
                        [
                            get_post_meta( $room_id, 'user_ebtal', true ),
                            get_post_meta( $room_id, 'sans_manager', true ),
                        ]
                    )
                )
            )
        );
        if ( ! in_array( (int) $current_user_id, $owner_ids, true ) ) {
            continue;
        }
    }

    $hasComment = (int) get_post_meta( $order_id, 'comment_status', true ) === 1;

    // شروع ساختن آرایه‌ی نهایی بر اساس اطلاعات اولیه
    $item = $order_data;
    $item['room_id'] = $room_id;
    // $_POST round-trip strings values; keep JSON numeric so JS switch works.
    $item['user_level'] = isset( $item['user_level'] ) ? (int) $item['user_level'] : 0;

    if ( ! $hasComment ) {
        $product_id = (int) get_post_meta( $order_id, 'code_otagh', true );
        if ( $product_id <= 0 ) {
            continue;
        }

        $product_meta = function_exists( 'ez_get_product_meta' ) ? ez_get_product_meta( $product_id ) : null;
        $item['product_id']    = $product_id;
        $item['product_type']  = is_object( $product_meta ) && isset( $product_meta->product_type ) ? $product_meta->product_type : '';
        $item['product_title'] = get_the_title( $product_id );
        $item['product_image'] = get_the_post_thumbnail_url( $product_id );
        $order_result[] = $item;  
    } else {
        $order_result[] = [ 'has_comment' => true, 'order_id' => $order_id ];  
    }
}
wp_send_json_success( $order_result );