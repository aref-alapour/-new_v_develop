<?php

$order_data = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? $_POST['data'] : [];
$order_id   = isset( $order_data['order_id'] ) ? (int) $order_data['order_id'] : 0;
$room_id    = isset( $order_data['room_id'] ) ? (int) $order_data['room_id'] : 0;

if ( $order_id <= 0 || $room_id <= 0 ) {
	wp_send_json_error( [ 'status' => 'invalid_payload', 'message' => 'اطلاعات سفارش نامعتبر است.' ] );
}

$current_user_id = get_current_user_id();
if ( $current_user_id <= 0 ) {
	wp_send_json_error( [ 'status' => 'unauthorized', 'message' => 'برای این عملیات وارد شوید.' ] );
}

$order_room_id = (int) get_post_meta( $order_id, 'code_otagh', true );
if ( $order_room_id <= 0 || $order_room_id !== $room_id ) {
	wp_send_json_error( [ 'status' => 'room_mismatch', 'message' => 'اطلاعات اتاق با سفارش هم‌خوانی ندارد.' ] );
}

if ( ! current_user_can( 'manage_options' ) ) {
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
		wp_send_json_error( [ 'status' => 'unauthorized', 'message' => 'دسترسی شما برای این اتاق مجاز نیست.' ] );
	}
}

$window_check = ez_owner_feedback_is_within_window( $order_id, $room_id, time() );
if ( is_wp_error( $window_check ) ) {
	wp_send_json_error(
		[
			'status'  => $window_check->get_error_code(),
			'message' => $window_check->get_error_message(),
		]
	);
}

wp_send_json_success( [ 'status' => 'success' ] );