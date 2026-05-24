<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_order_satisfaction_report_build' ) ) {
	wp_send_json_error( array( 'message' => 'report helper is not loaded' ) );
}

$build_filters_from_post = static function( string $prefix = '' ): array {
	$filters = array(
		'game_id'   => isset( $_POST['game_id'] ) ? (int) sanitize_text_field( wp_unslash( $_POST['game_id'] ) ) : 0,
		'date_from' => isset( $_POST[ $prefix . 'date_from' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'date_from' ] ) ) : '',
		'date_to'   => isset( $_POST[ $prefix . 'date_to' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'date_to' ] ) ) : '',
	);

	if ( $prefix === '' ) {
		$filters['page']     = isset( $_POST['page'] ) ? max( 1, (int) sanitize_text_field( wp_unslash( $_POST['page'] ) ) ) : 1;
		$filters['per_page'] = isset( $_POST['per_page'] ) ? max( 1, (int) sanitize_text_field( wp_unslash( $_POST['per_page'] ) ) ) : 20;

		$sort_by = isset( $_POST['sort_by'] ) ? sanitize_key( wp_unslash( $_POST['sort_by'] ) ) : 'created_at';
		if ( ! in_array( $sort_by, array( 'source', 'new_status', 'old_status', 'created_at', 'updated_at' ), true ) ) {
			$sort_by = 'created_at';
		}
		$filters['sort_by'] = $sort_by;

		$sort_dir = isset( $_POST['sort_dir'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['sort_dir'] ) ) ) : 'desc';
		if ( ! in_array( $sort_dir, array( 'asc', 'desc' ), true ) ) {
			$sort_dir = 'desc';
		}
		$filters['sort_dir'] = $sort_dir;
	}

	$date_range_mode = isset( $_POST[ $prefix . 'date_range' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'date_range' ] ) ) : '';
	if ( $date_range_mode === 'calendar' ) {
		$start_date = isset( $_POST[ $prefix . 'start_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'start_date' ] ) ) : '';
		$end_date   = isset( $_POST[ $prefix . 'end_date' ] ) ? sanitize_text_field( wp_unslash( $_POST[ $prefix . 'end_date' ] ) ) : '';
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $start_date ) ) {
			$filters['date_from'] = $start_date;
		}
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $end_date ) ) {
			$filters['date_to'] = $end_date;
		}
	}

	return $filters;
};

$current_filters = $build_filters_from_post( '' );
if ( (int) ( $current_filters['game_id'] ?? 0 ) < 1 ) {
	wp_send_json_error( array( 'message' => 'انتخاب بازی الزامی است.' ) );
}

$current = ez_order_satisfaction_report_build( $current_filters );
$compare_filters = $build_filters_from_post( 'compare_' );
$has_compare     = ! empty( $compare_filters['date_from'] ) && ! empty( $compare_filters['date_to'] );
$compare         = $has_compare ? ez_order_satisfaction_report_build( $compare_filters ) : null;

$delta = null;
if ( $compare && isset( $current['kpi'], $compare['kpi'] ) ) {
	$delta = array(
		'sat_percent'       => round( (float) ( $current['kpi']['sat_percent'] ?? 0 ) - (float) ( $compare['kpi']['sat_percent'] ?? 0 ), 2 ),
		'sat_count'         => (int) ( $current['kpi']['sat_count'] ?? 0 ) - (int) ( $compare['kpi']['sat_count'] ?? 0 ),
		'reduction_percent' => round( (float) ( $current['kpi']['reduction_percent'] ?? 0 ) - (float) ( $compare['kpi']['reduction_percent'] ?? 0 ), 2 ),
		'reduction_count'   => (int) ( $current['kpi']['reduction_count'] ?? 0 ) - (int) ( $compare['kpi']['reduction_count'] ?? 0 ),
		'comment_share_percent' => round( (float) ( $current['kpi']['comment_share_percent'] ?? 0 ) - (float) ( $compare['kpi']['comment_share_percent'] ?? 0 ), 2 ),
		'comment_share_count'   => (int) ( $current['kpi']['comment_negative_count'] ?? 0 ) - (int) ( $compare['kpi']['comment_negative_count'] ?? 0 ),
		'cancel_share_percent'  => round( (float) ( $current['kpi']['cancel_share_percent'] ?? 0 ) - (float) ( $compare['kpi']['cancel_share_percent'] ?? 0 ), 2 ),
		'cancel_share_count'    => (int) ( $current['kpi']['cancel_negative_count'] ?? 0 ) - (int) ( $compare['kpi']['cancel_negative_count'] ?? 0 ),
	);
}

wp_send_json_success(
	array(
		'current'     => $current,
		'compare'     => $compare,
		'has_compare' => $has_compare,
		'delta'       => $delta,
	)
);
