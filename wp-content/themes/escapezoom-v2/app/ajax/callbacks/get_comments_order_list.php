<?php
/**
 * Orders eligible for owner feedback form (same window rules as post_order_comment / owner_feedback.php).
 * status=1 active booking; status=2 closed session (excluded).
 * Booking rows are read from medoo_queries DB (wp_zb_booking_history), not WordPress $wpdb.
 */
if ( ! function_exists( 'medoo_queries' ) ) {
	$ez_medoo_init = ( defined( 'Theme_PATH' ) ? Theme_PATH : trailingslashit( get_template_directory() ) ) . 'inc/medoo/init.php';
	if ( is_readable( $ez_medoo_init ) ) {
		require_once $ez_medoo_init;
	}
}

$payload = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? $_POST['data'] : [];
$raw_product_ids = isset( $payload['product_id'] ) && is_array( $payload['product_id'] ) ? $payload['product_id'] : [];

$product_ids = array_values(
	array_unique(
		array_filter(
			array_map( 'intval', $raw_product_ids )
		)
	)
);
if ( empty( $product_ids ) ) {
	wp_send_json_success( [] );
}

$current_user_id = get_current_user_id();
if ( $current_user_id <= 0 ) {
	wp_send_json_error(
		[
			'status'  => 'unauthorized',
			'message' => 'برای دریافت لیست دیدگاه وارد شوید.',
		]
	);
}

$mdb = function_exists( 'medoo_queries' ) ? medoo_queries() : null;
if ( ! $mdb ) {
	wp_send_json_success( [] );
}

$is_admin = current_user_can( 'manage_options' );
$allowed_product_ids = [];
foreach ( $product_ids as $pid ) {
	if ( $pid <= 0 ) {
		continue;
	}
	if ( $is_admin ) {
		$allowed_product_ids[] = $pid;
		continue;
	}

	$owner_ids = array_values(
		array_unique(
			array_filter(
				array_map(
					'intval',
					[
						get_post_meta( $pid, 'user_ebtal', true ),
						get_post_meta( $pid, 'sans_manager', true ),
					]
				)
			)
		)
	);

	if ( in_array( $current_user_id, $owner_ids, true ) ) {
		$allowed_product_ids[] = $pid;
	}
}
$allowed_product_ids = array_values( array_unique( $allowed_product_ids ) );
if ( empty( $allowed_product_ids ) ) {
	wp_send_json_success( [] );
}

$now            = time();
$orders_by_id   = [];
$slack_seconds  = 3600;
$debug_counts   = [
	'rooms_checked'         => 0,
	'skip_no_duration'      => 0,
	'booking_rows_total'    => 0,
	'skip_status_not_1'     => 0,
	'skip_bad_order_id'     => 0,
	'skip_duplicate_order'  => 0,
	'skip_start_resolve'    => 0,
	'skip_duration'         => 0,
	'skip_outside_window'   => 0,
	'included'              => 0,
];

foreach ( $allowed_product_ids as $room_id ) {
	++$debug_counts['rooms_checked'];

	$duration_res = ez_owner_feedback_resolve_duration_minutes( $room_id, null );
	if ( is_wp_error( $duration_res ) ) {
		++$debug_counts['skip_no_duration'];
		continue;
	}
	$duration = (int) $duration_res;

	$lookback_start = $now - ( (int) $duration * 60 ) - 1800 - $slack_seconds;
	$lookback_end   = $now - 1800;

	$rows = $mdb->select(
		'wp_zb_booking_history',
		[
			'wc_order_id',
			'booking_time',
			'name',
			'quantity',
			'level',
			'status',
		],
		[
			'room_id'            => $room_id,
			'booking_time[<>]'  => [ $lookback_start, $lookback_end ],
		]
	);

	if ( empty( $rows ) || ! is_array( $rows ) ) {
		continue;
	}

	$debug_counts['booking_rows_total'] += count( $rows );

	foreach ( $rows as $row ) {
		$order_id = (int) ( $row['wc_order_id'] ?? 0 );
		$name     = trim( (string) ( $row['name'] ?? '' ) );
		$booking_status = isset( $row['status'] ) ? (int) $row['status'] : 0;

		if ( $booking_status !== 1 ) {
			++$debug_counts['skip_status_not_1'];
			continue;
		}

		if ( $order_id <= 0 ) {
			++$debug_counts['skip_bad_order_id'];
			continue;
		}

		if ( isset( $orders_by_id[ $order_id ] ) ) {
			++$debug_counts['skip_duplicate_order'];
			continue;
		}

		$booking_ts = isset( $row['booking_time'] ) ? (int) $row['booking_time'] : 0;
		$start_ts   = ez_owner_feedback_resolve_session_start_ts(
			$order_id,
			$room_id,
			$booking_ts > 0 ? $booking_ts : null
		);
		if ( is_wp_error( $start_ts ) ) {
			++$debug_counts['skip_start_resolve'];
			continue;
		}

		$dur_row = ez_owner_feedback_resolve_duration_minutes( $room_id, $order_id );
		if ( is_wp_error( $dur_row ) ) {
			++$debug_counts['skip_duration'];
			continue;
		}
		$duration_min = (int) $dur_row;

		$window_start = (int) $start_ts + 1800;
		$window_end   = (int) $start_ts + ( $duration_min * 60 ) + 1800;

		if ( $now < $window_start || $now > $window_end ) {
			++$debug_counts['skip_outside_window'];
			continue;
		}

		$user_level = (int) ( $row['level'] ?? 0 );
		if ( $user_level <= 0 && function_exists( 'wc_get_order' ) && function_exists( 'get_user_level' ) ) {
			$wc_order = wc_get_order( $order_id );
			if ( $wc_order ) {
				$cid = (int) $wc_order->get_user_id();
				if ( $cid > 0 ) {
					$user_level = (int) get_user_level( $cid );
				}
			}
		}

		$orders_by_id[ $order_id ] = [
			'order_id'       => $order_id,
			'order_quantity' => (int) ( $row['quantity'] ?? 0 ),
			'user_name'      => $name !== '' ? $name : 'بازیکن',
			'user_level'     => $user_level > 0 ? $user_level : 1,
			'room_id'        => (int) $room_id,
		];
		++$debug_counts['included'];
	}
}

ksort( $orders_by_id );

if ( empty( $orders_by_id ) && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
	error_log( '[get_comments_order_list] empty ' . wp_json_encode( $debug_counts ) );
}

wp_send_json_success( array_values( $orders_by_id ) );
