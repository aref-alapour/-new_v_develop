<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$term = isset( $_POST['term'] ) ? sanitize_text_field( wp_unslash( $_POST['term'] ) ) : '';
if ( mb_strlen( trim( $term ) ) < 2 ) {
	wp_send_json_success(
		array(
			'items' => array(),
		)
	);
}

if ( ! function_exists( 'ez_order_satisfaction_report_search_games' ) ) {
	wp_send_json_error( array( 'message' => 'report helper is not loaded' ) );
}

$items = ez_order_satisfaction_report_search_games( $term, 20 );
wp_send_json_success(
	array(
		'items' => $items,
	)
);
