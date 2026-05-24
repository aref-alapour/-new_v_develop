<?php
/**
 * Order satisfaction report helpers for CRM pages.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<int,array<string,mixed>>
 */
function ez_order_satisfaction_report_get_games(): array {
	global $wpdb;

	$table = 'wp_orders_satisfaction_history';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed table name.
	$rows = $wpdb->get_results( "SELECT DISTINCT game_id FROM `{$table}` WHERE game_id IS NOT NULL AND game_id > 0 ORDER BY game_id DESC", ARRAY_A );
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$out = array();
	foreach ( $rows as $r ) {
		$gid = (int) ( $r['game_id'] ?? 0 );
		if ( $gid < 1 ) {
			continue;
		}
		$title = get_the_title( $gid );
		$out[] = array(
			'game_id'    => $gid,
			'game_title' => $title ? (string) $title : ( 'بازی #' . $gid ),
		);
	}
	return $out;
}

/**
 * @param string $status
 */
function ez_order_satisfaction_report_status_label( string $status ): string {
	$map = array(
		'SATISFIED'    => 'راضی',
		'DISSATISFIED' => 'ناراضی',
		'PENDING'      => 'در انتظار',
	);
	return $map[ strtoupper( $status ) ] ?? ( $status !== '' ? $status : '-' );
}

/**
 * @param string $source
 */
function ez_order_satisfaction_report_source_label( string $source ): string {
	$map = array(
		'comment_rating'             => 'امتیاز کامنت',
		'cancellation_owner_refund'  => 'کنسلی مالک',
		'refund_owner_backfill'      => 'بک‌فیل کنسلی مالک',
		'wallet_conversion'          => 'تبدیل کیف پول',
		'walletx_backfill'           => 'بک‌فیل کیف پول',
		'comment_unpublished'        => 'عدم نمایش کامنت',
		'crm_comment_unpublished'    => 'عدم نمایش کامنت CRM',
		'crm_comment_trash'          => 'حذف کامنت CRM',
		'cancellation_customer_refund' => 'کنسلی مشتری',
		'refund_pending_backfill'    => 'بک‌فیل در انتظار',
	);
	$key = sanitize_key( $source );
	return $map[ $key ] ?? ( $source !== '' ? $source : '-' );
}

/**
 * @param string|null $datetime
 */
function ez_order_satisfaction_report_jalali_datetime( ?string $datetime ): string {
	if ( empty( $datetime ) ) {
		return '-';
	}
	$ts = strtotime( (string) $datetime );
	if ( ! $ts ) {
		return (string) $datetime;
	}
	if ( function_exists( 'jdate' ) ) {
		return (string) jdate( 'Y/m/d H:i', $ts );
	}
	return date_i18n( 'Y/m/d H:i', $ts );
}

/**
 * @param string $term
 * @param int    $limit
 * @return array<int,array<string,mixed>>
 */
function ez_order_satisfaction_report_search_games( string $term, int $limit = 20 ): array {
	global $wpdb;

	$term  = trim( $term );
	$limit = max( 1, min( 50, $limit ) );
	if ( mb_strlen( $term ) < 2 ) {
		return array();
	}

	$h_table  = 'wp_orders_satisfaction_history';
	$ps_table = 'wp_products_search';

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed table names.
	$sql = $wpdb->prepare(
		"SELECT
		 ps.product_id AS game_id,
		 ps.product_name AS game_title,
		 ps.product_type AS game_type,
		 ps.product_image_url AS game_image_url,
		 CASE WHEN hx.game_id IS NULL THEN 0 ELSE 1 END AS has_satisfaction_data
		 FROM `{$ps_table}` ps
		 LEFT JOIN (
			SELECT DISTINCT game_id
			FROM `{$h_table}`
			WHERE game_id IS NOT NULL AND game_id > 0
		 ) hx ON hx.game_id = ps.product_id
		 WHERE ps.product_id > 0
		   AND ps.product_name LIKE %s
		 ORDER BY ps.product_name ASC
		 LIMIT %d",
		'%' . $wpdb->esc_like( $term ) . '%',
		$limit
	);
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$rows = $wpdb->get_results( $sql, ARRAY_A );
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$out = array();
	foreach ( $rows as $row ) {
		$gid = (int) ( $row['game_id'] ?? 0 );
		if ( $gid < 1 ) {
			continue;
		}
		$out[] = array(
			'game_id'               => $gid,
			'game_title'            => (string) ( $row['game_title'] ?? ( 'بازی #' . $gid ) ),
			'game_type'             => (string) ( $row['game_type'] ?? '' ),
			'game_image_url'        => (string) ( $row['game_image_url'] ?? '' ),
			'has_satisfaction_data' => (int) ( $row['has_satisfaction_data'] ?? 0 ) === 1,
		);
	}
	return $out;
}

/**
 * @param array<string,mixed> $filters
 * @return array<string,mixed>
 */
function ez_order_satisfaction_report_build( array $filters = array() ): array {
	global $wpdb;

	$table        = 'wp_orders_satisfaction_history';
	$game_id      = isset( $filters['game_id'] ) ? (int) $filters['game_id'] : 0;
	$date_from    = isset( $filters['date_from'] ) ? sanitize_text_field( (string) $filters['date_from'] ) : '';
	$date_to      = isset( $filters['date_to'] ) ? sanitize_text_field( (string) $filters['date_to'] ) : '';
	$page         = isset( $filters['page'] ) ? (int) $filters['page'] : 1;
	$per_page     = isset( $filters['per_page'] ) ? (int) $filters['per_page'] : 20;
	$sort_by_req  = isset( $filters['sort_by'] ) ? sanitize_key( (string) $filters['sort_by'] ) : 'created_at';
	$sort_dir_req = isset( $filters['sort_dir'] ) ? strtolower( sanitize_text_field( (string) $filters['sort_dir'] ) ) : 'desc';

	$page     = max( 1, $page );
	$per_page = max( 1, min( 100, $per_page ) );

	$sortable_columns = array(
		'source'     => 'source',
		'new_status' => 'new_status',
		'old_status' => 'old_status',
		'created_at' => 'created_at',
		'updated_at' => 'updated_at',
	);
	$sort_by_sql = $sortable_columns[ $sort_by_req ] ?? 'created_at';
	$sort_by     = array_key_exists( $sort_by_req, $sortable_columns ) ? $sort_by_req : 'created_at';
	$sort_dir    = in_array( $sort_dir_req, array( 'asc', 'desc' ), true ) ? strtoupper( $sort_dir_req ) : 'DESC';

	$where_sql = '1=1';
	$params    = array();

	if ( $game_id > 0 ) {
		$where_sql .= ' AND game_id = %d';
		$params[] = $game_id;
	}
	if ( $date_from !== '' ) {
		$where_sql .= ' AND updated_at >= %s';
		$params[] = $date_from . ' 00:00:00';
	}
	if ( $date_to !== '' ) {
		$where_sql .= ' AND updated_at <= %s';
		$params[] = $date_to . ' 23:59:59';
	}

	// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed table name.
	$sql_kpi = "SELECT
		COUNT(*) AS total_rows,
		SUM(CASE WHEN new_status = %s THEN 1 ELSE 0 END) AS sat_count,
		SUM(CASE WHEN new_status = %s THEN 1 ELSE 0 END) AS dissat_count,
		SUM(CASE WHEN new_status = %s AND source = %s THEN 1 ELSE 0 END) AS comment_negative_count,
		SUM(CASE WHEN new_status = %s AND source IN (%s, %s) THEN 1 ELSE 0 END) AS cancel_negative_count
		FROM `{$table}`
		WHERE {$where_sql}";
	$sql_kpi = $wpdb->prepare(
		$sql_kpi,
		array_merge(
			array(
				EZ_ORDER_SATISFACTION_SATISFIED,
				EZ_ORDER_SATISFACTION_DISSATISFIED,
				EZ_ORDER_SATISFACTION_DISSATISFIED,
				'comment_rating',
				EZ_ORDER_SATISFACTION_DISSATISFIED,
				'cancellation_owner_refund',
				'refund_owner_backfill',
			),
			$params
		)
	);
	$kpi = $wpdb->get_row( $sql_kpi, ARRAY_A );

	$sql_table_count = "SELECT COUNT(*) FROM `{$table}` WHERE {$where_sql}";
	if ( ! empty( $params ) ) {
		$sql_table_count = $wpdb->prepare( $sql_table_count, $params );
	}
	$table_total_rows = (int) $wpdb->get_var( $sql_table_count );
	$total_pages      = (int) ceil( $table_total_rows / $per_page );
	$total_pages      = max( 1, $total_pages );
	$page             = min( $page, $total_pages );
	$offset           = ( $page - 1 ) * $per_page;

	$sql_rows = "SELECT order_id, game_id, old_status, new_status, source, details, created_at, updated_at
		FROM `{$table}`
		WHERE {$where_sql}
		ORDER BY {$sort_by_sql} {$sort_dir}, id DESC
		LIMIT %d OFFSET %d";
	$sql_rows = $wpdb->prepare( $sql_rows, array_merge( $params, array( $per_page, $offset ) ) );
	$rows     = $wpdb->get_results( $sql_rows, ARRAY_A );
	// phpcs:enable WordPress.DB.PreparedSQL.InterpolatedNotPrepared

	$game_has_any_history = true;
	if ( $game_id > 0 ) {
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- fixed table name.
		$exists_sql = $wpdb->prepare(
			"SELECT 1 FROM `{$table}` WHERE game_id = %d LIMIT 1",
			$game_id
		);
		$game_has_any_history = (bool) $wpdb->get_var( $exists_sql );
	}

	$total_rows             = (int) ( $kpi['total_rows'] ?? 0 );
	$sat_count              = (int) ( $kpi['sat_count'] ?? 0 );
	$dissat_count           = (int) ( $kpi['dissat_count'] ?? 0 );
	$comment_negative_count = (int) ( $kpi['comment_negative_count'] ?? 0 );
	$cancel_negative_count  = (int) ( $kpi['cancel_negative_count'] ?? 0 );

	$reduction_count        = max( 0, $total_rows - $sat_count );
	$other_negative_count   = max( 0, $reduction_count - $comment_negative_count - $cancel_negative_count );

	$sat_percent            = $total_rows > 0 ? round( ( $sat_count * 100 ) / $total_rows, 2 ) : 0;
	$reduction_percent      = $total_rows > 0 ? round( ( $reduction_count * 100 ) / $total_rows, 2 ) : 0;
	$comment_share_percent  = $reduction_count > 0 ? round( ( $comment_negative_count * 100 ) / $reduction_count, 2 ) : 0;
	$cancel_share_percent   = $reduction_count > 0 ? round( ( $cancel_negative_count * 100 ) / $reduction_count, 2 ) : 0;
	$other_share_percent    = $reduction_count > 0 ? round( ( $other_negative_count * 100 ) / $reduction_count, 2 ) : 0;

	$normalized_rows = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$old_status = (string) ( $row['old_status'] ?? '' );
			$new_status = (string) ( $row['new_status'] ?? '' );
			$source     = (string) ( $row['source'] ?? '' );
			$details_raw = (string) ( $row['details'] ?? '' );
			$details_json = json_decode( $details_raw, true );
			if ( ! is_array( $details_json ) ) {
				$details_json = array();
			}
			$comment_id             = (int) ( $details_json['comment_id'] ?? 0 );
			$cancellation_request_id = (int) ( $details_json['cancellation_request_id'] ?? 0 );
			$has_comment_detail      = ( $source === 'comment_rating' && $comment_id > 0 );
			$has_owner_cancel_detail = in_array( $source, array( 'cancellation_owner_refund', 'refund_owner_backfill' ), true ) && ( $cancellation_request_id > 0 || (int) ( $row['order_id'] ?? 0 ) > 0 );

			$normalized_rows[] = array(
				'order_id'            => (int) ( $row['order_id'] ?? 0 ),
				'game_id'             => (int) ( $row['game_id'] ?? 0 ),
				'old_status'          => $old_status,
				'new_status'          => $new_status,
				'source'              => $source,
				'details'             => $details_raw,
				'details_json'        => $details_json,
				'created_at'          => (string) ( $row['created_at'] ?? '' ),
				'updated_at'          => (string) ( $row['updated_at'] ?? '' ),
				'has_comment_detail'  => $has_comment_detail,
				'comment_id'          => $comment_id,
				'has_owner_cancel_detail' => $has_owner_cancel_detail,
				'cancellation_request_id' => $cancellation_request_id,
				'old_status_label'    => ez_order_satisfaction_report_status_label( $old_status ),
				'new_status_label'    => ez_order_satisfaction_report_status_label( $new_status ),
				'source_label'        => ez_order_satisfaction_report_source_label( $source ),
				'created_at_jalali'   => ez_order_satisfaction_report_jalali_datetime( (string) ( $row['created_at'] ?? '' ) ),
				'updated_at_jalali'   => ez_order_satisfaction_report_jalali_datetime( (string) ( $row['updated_at'] ?? '' ) ),
			);
		}
	}

	return array(
		'filters' => array(
			'game_id'   => $game_id,
			'date_from' => $date_from,
			'date_to'   => $date_to,
		),
		'kpi' => array(
			'total_rows'            => $total_rows,
			'sat_count'             => $sat_count,
			'reduction_count'       => $reduction_count,
			'comment_negative_count'=> $comment_negative_count,
			'cancel_negative_count' => $cancel_negative_count,
			'other_negative_count'  => $other_negative_count,
			'sat_percent'           => $sat_percent,
			'reduction_percent'     => $reduction_percent,
			'comment_share_percent' => $comment_share_percent,
			'cancel_share_percent'  => $cancel_share_percent,
			'other_share_percent'   => $other_share_percent,
			'sat' => array(
				'count'   => $sat_count,
				'percent' => $sat_percent,
			),
			'reduction' => array(
				'count'   => $reduction_count,
				'percent' => $reduction_percent,
			),
			'comment_share' => array(
				'count'   => $comment_negative_count,
				'percent' => $comment_share_percent,
			),
			'cancel_share' => array(
				'count'   => $cancel_negative_count,
				'percent' => $cancel_share_percent,
			),
		),
		'chart' => array(
			'satisfaction_vs_reduction' => array(
				'labels' => array( 'رضایت', 'کاهش' ),
				'data'   => array( $sat_count, $reduction_count ),
			),
			'reduction_breakdown' => array(
				'labels' => array( 'کامنت منفی', 'کنسلی', 'سایر' ),
				'data'   => array( $comment_negative_count, $cancel_negative_count, $other_negative_count ),
			),
		),
		'rows'  => $normalized_rows,
		'table_meta' => array(
			'page'       => $page,
			'per_page'   => $per_page,
			'total_rows' => $table_total_rows,
			'total_pages' => $total_pages,
			'sort_by'    => $sort_by,
			'sort_dir'   => strtolower( $sort_dir ),
		),
		'meta'  => array(
			'game_has_any_history' => $game_has_any_history,
		),
		'games' => ez_order_satisfaction_report_get_games(),
	);
}
