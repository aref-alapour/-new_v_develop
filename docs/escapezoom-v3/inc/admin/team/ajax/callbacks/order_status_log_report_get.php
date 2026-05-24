<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_team_order_status_log_get_data' ) ) {
	wp_send_json_error( array( 'message' => 'report helper is not loaded' ) );
}

$order_id = isset( $_POST['order_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['order_id'] ) ) : 0;
$user_id  = isset( $_POST['user_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['user_id'] ) ) : 0;
$page     = isset( $_POST['page'] ) ? max( 1, (int) sanitize_text_field( wp_unslash( $_POST['page'] ) ) ) : 1;

$data = ez_team_order_status_log_get_data( $order_id, $user_id, $page, 50 );
wp_send_json_success( $data );
