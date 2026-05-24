<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_team_order_status_log_delete_old' ) ) {
	wp_send_json_error( array( 'message' => 'report helper is not loaded' ) );
}

$result = ez_team_order_status_log_delete_old();
if ( ! empty( $result['success'] ) ) {
	wp_send_json_success( $result );
}

wp_send_json_error(
	array(
		'message'       => (string) ( $result['message'] ?? 'خطا در حذف لاگ‌های قدیمی' ),
		'deleted_count' => (int) ( $result['deleted_count'] ?? 0 ),
	)
);
