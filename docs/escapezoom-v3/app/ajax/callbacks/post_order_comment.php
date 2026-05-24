<?php
global $wpdb;

$order_data = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? $_POST['data'] : [];

$order_id        = isset( $order_data['order_id'] ) ? (int) $order_data['order_id'] : 0;
$room_id         = isset( $order_data['room_id'] ) ? (int) $order_data['room_id'] : 0;
$comment_message = isset( $order_data['commentMessage'] ) ? sanitize_textarea_field( (string) $order_data['commentMessage'] ) : '';
$vote_value      = isset( $order_data['voteValue'] ) ? sanitize_key( (string) $order_data['voteValue'] ) : '';
$owner_user_id   = get_current_user_id();

if ( $order_id <= 0 || $room_id <= 0 ) {
	wp_send_json_error( [ 'status' => 'invalid_payload', 'message' => 'اطلاعات سفارش کامل نیست.' ] );
}
if ( $comment_message === '' ) {
	wp_send_json_error( [ 'status' => 'invalid_payload', 'message' => 'متن دیدگاه ضروری است.' ] );
}
if ( ! in_array( $vote_value, [ 'like', 'dislike' ], true ) ) {
	wp_send_json_error( [ 'status' => 'invalid_payload', 'message' => 'نوع بازخورد نامعتبر است.' ] );
}
if ( $owner_user_id <= 0 ) {
	wp_send_json_error( [ 'status' => 'unauthorized', 'message' => 'برای ثبت دیدگاه وارد شوید.' ] );
}

$order_room_id = (int) get_post_meta( $order_id, 'code_otagh', true );
if ( $order_room_id <= 0 || $order_room_id !== $room_id ) {
	wp_send_json_error( [ 'status' => 'room_mismatch', 'message' => 'اطلاعات اتاق با سفارش هم‌خوانی ندارد.' ] );
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

// فقط مدیر سانس/مجموعه‌دار مربوط به اتاق (یا ادمین) بتواند بازخورد ثبت کند.
$owner_ids = array_map(
	'intval',
	[
		get_post_meta( $room_id, 'user_ebtal', true ),
		get_post_meta( $room_id, 'sans_manager', true ),
	]
);
$owner_ids = array_values( array_unique( array_filter( $owner_ids ) ) );
if ( ! current_user_can( 'manage_options' ) && ! in_array( (int) $owner_user_id, $owner_ids, true ) ) {
	wp_send_json_error( [ 'status' => 'unauthorized', 'message' => 'شما مجاز به ثبت دیدگاه برای این اتاق نیستید.' ] );
}

$normalize_phone = static function ( $raw ) {
	$digits = preg_replace( '/[^0-9]/', '', (string) $raw );
	if ( $digits === '' ) {
		return null;
	}
	$last10 = substr( $digits, -10 );
	if ( ! preg_match( '/^9[0-9]{9}$/', $last10 ) ) {
		return null;
	}
	return [
		'last10' => $last10,
		'phone11' => '0' . $last10,
	];
};

$to_title = static function ( $name ) {
	$name = trim( (string) $name );
	return $name !== '' ? $name : '';
};

$created_users = [];
$resolve_or_create_user = static function ( string $last10, string $phone11, string $title ) use ( &$created_users ) {
	$created_now = false;
	$user = get_user_by( 'login', $last10 );
	if ( $user instanceof WP_User ) {
		$uid = (int) $user->ID;
	} else {
		$users_by_phone = get_users(
			[
				'meta_key'   => 'billing_phone',
				'meta_value' => $phone11,
				'number'     => 1,
				'fields'     => [ 'ID' ],
			]
		);
		if ( ! empty( $users_by_phone ) && ! empty( $users_by_phone[0]->ID ) ) {
			$uid = (int) $users_by_phone[0]->ID;
		} else {
			$created = wp_create_user( $last10, wp_generate_password( 14, false ), '' );
			if ( is_wp_error( $created ) ) {
				return [ 'uid' => 0, 'created' => false ];
			}
			$uid = (int) $created;
			$created_now = true;
			$created_users[] = $uid;
		}
	}

	if ( $uid <= 0 ) {
		return [ 'uid' => 0, 'created' => false ];
	}

	update_user_meta( $uid, 'billing_phone', $phone11 );
	$current = get_userdata( $uid );
	if ( ! $current instanceof WP_User ) {
		return [ 'uid' => $uid, 'created' => $created_now ];
	}

	$fallback_title = 'کاربر اسکیپ زوم ' . $uid;
	$final_title = $title !== '' ? $title : $fallback_title;

	$display_name = trim( (string) $current->display_name );
	$first_name = trim( (string) get_user_meta( $uid, 'first_name', true ) );
	$billing_first_name = trim( (string) get_user_meta( $uid, 'billing_first_name', true ) );
	$nickname = trim( (string) get_user_meta( $uid, 'nickname', true ) );

	$is_display_non_human = (
		$display_name === '' ||
		$display_name === (string) $current->user_login ||
		$display_name === $last10 ||
		$display_name === $phone11 ||
		(bool) preg_match( '/^0?9[0-9]{9}$/', $display_name )
	);
	$has_human_name = $first_name !== '' || $billing_first_name !== '' || ( $display_name !== '' && ! $is_display_non_human );
	$needs_fallback_name = $title === '' && ! $has_human_name;

	$u = [ 'ID' => $uid ];
	$should_update_user = false;

	if ( $title !== '' ) {
		if ( $is_display_non_human ) {
			$u['display_name'] = $final_title;
			$should_update_user = true;
		}
		if ( $first_name === '' ) {
			$u['first_name'] = $final_title;
			$should_update_user = true;
		}
		if ( $billing_first_name === '' ) {
			update_user_meta( $uid, 'billing_first_name', $final_title );
		}
		if ( $nickname === '' ) {
			update_user_meta( $uid, 'nickname', $final_title );
		}
	} elseif ( $needs_fallback_name ) {
		$u['display_name'] = $fallback_title;
		$should_update_user = true;
		if ( $first_name === '' ) {
			$u['first_name'] = $fallback_title;
			$should_update_user = true;
		}
		if ( $billing_first_name === '' ) {
			update_user_meta( $uid, 'billing_first_name', $fallback_title );
		}
		if ( $nickname === '' ) {
			update_user_meta( $uid, 'nickname', $fallback_title );
		}
	}

	if ( $should_update_user ) {
		wp_update_user( $u );
	}

	$role_user = new WP_User( $uid );
	if ( ! in_array( 'customer', (array) $role_user->roles, true ) ) {
		if ( empty( $role_user->roles ) ) {
			$role_user->set_role( 'customer' );
		} else {
			$role_user->add_role( 'customer' );
		}
	}

	return [ 'uid' => $uid, 'created' => $created_now ];
};

$recipients = [];
$name_by_phone = [];
$skipped_users = [];

// نام/شماره اعضای تیم از wp_markting.order_phones (در صورت وجود).
$markting_phones = $wpdb->get_var(
	$wpdb->prepare( 'SELECT order_phones FROM wp_markting WHERE order_id = %d LIMIT 1', $order_id )
);
if ( ! empty( $markting_phones ) ) {
	$decoded = maybe_unserialize( $markting_phones );
	if ( is_string( $decoded ) ) {
		$json = json_decode( $decoded, true );
		if ( is_array( $json ) ) {
			$decoded = $json;
		}
	}
	if ( is_array( $decoded ) ) {
		foreach ( $decoded as $teammate ) {
			if ( ! is_array( $teammate ) || empty( $teammate['phone'] ) ) {
				continue;
			}
			$n = $normalize_phone( $teammate['phone'] );
			if ( ! $n ) {
				$skipped_users[] = [ 'phone' => (string) $teammate['phone'], 'reason' => 'invalid_phone' ];
				continue;
			}
			$recipients[ $n['last10'] ] = $n;
			$name_by_phone[ $n['last10'] ] = $to_title( $teammate['name'] ?? '' );
		}
	}
}

// players_phone سفارش.
$players_phone = get_post_meta( $order_id, 'players_phone', true );
$players_phone = is_array( $players_phone ) ? $players_phone : (array) maybe_unserialize( $players_phone );
foreach ( $players_phone as $entry ) {
	$phone_raw = is_array( $entry ) ? ( $entry['phone'] ?? '' ) : $entry;
	$n = $normalize_phone( $phone_raw );
	if ( ! $n ) {
		if ( ! empty( $phone_raw ) ) {
			$skipped_users[] = [ 'phone' => (string) $phone_raw, 'reason' => 'invalid_phone' ];
		}
		continue;
	}
	$recipients[ $n['last10'] ] = $n;
	if ( is_array( $entry ) && ! isset( $name_by_phone[ $n['last10'] ] ) ) {
		$name_by_phone[ $n['last10'] ] = $to_title( $entry['name'] ?? '' );
	}
}

// سرگروه (customer_user + billing phone) همیشه داخل recipientها باشد.
$leader_phone = (string) get_post_meta( $order_id, '_billing_phone', true );
$leader_norm  = $normalize_phone( $leader_phone );
if ( $leader_norm ) {
	$recipients[ $leader_norm['last10'] ] = $leader_norm;
}

$leader_uid = (int) get_post_meta( $order_id, '_customer_user', true );
if ( $leader_uid > 0 ) {
	$leader_data = get_userdata( $leader_uid );
	$leader_name = '';
	if ( $leader_data instanceof WP_User ) {
		$leader_name = trim( (string) $leader_data->first_name . ' ' . (string) $leader_data->last_name );
		if ( $leader_name === '' ) {
			$leader_name = trim( (string) $leader_data->display_name );
		}
	}
	if ( $leader_norm && ! isset( $name_by_phone[ $leader_norm['last10'] ] ) ) {
		$name_by_phone[ $leader_norm['last10'] ] = $leader_name;
	}
}

$target_user_ids = [];
foreach ( $recipients as $last10 => $meta ) {
	$title = $name_by_phone[ $last10 ] ?? '';
	$resolved = $resolve_or_create_user( $meta['last10'], $meta['phone11'], $title );
	$uid = (int) ( $resolved['uid'] ?? 0 );
	if ( $uid > 0 ) {
		$target_user_ids[] = $uid;
	} else {
		$skipped_users[] = [ 'phone' => $meta['phone11'], 'reason' => 'user_resolve_failed' ];
	}
}
if ( $leader_uid > 0 ) {
	$target_user_ids[] = $leader_uid;
}
$target_user_ids = array_values( array_unique( array_map( 'intval', $target_user_ids ) ) );

if ( empty( $target_user_ids ) ) {
	wp_send_json_error( [ 'status' => 'no_recipients', 'message' => 'هیچ کاربری برای ثبت بازخورد یافت نشد.' ] );
}

$now = time();
$state_key = '_owner_feedback_submitted';
$progress_key = '_owner_feedback_progress';
$lock_timeout = 300;
$encode_payload = static function ( array $payload ) {
	if ( function_exists( 'wp_json_encode' ) ) {
		return (string) wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );
	}
	return (string) json_encode( $payload, JSON_UNESCAPED_UNICODE );
};
$log_event = static function ( string $level, array $payload ) use ( $encode_payload ) {
	error_log( '[owner_feedback][' . $level . '] ' . $encode_payload( $payload ) );
};

$state = get_post_meta( $order_id, $state_key, true );
$state = is_array( $state ) ? $state : ( ! empty( $state ) ? [ 'legacy_lock' => $state ] : [] );

if ( isset( $state['legacy_lock'] ) ) {
	$legacy_time = (int) $state['legacy_lock'];
	$legacy_progress = get_post_meta( $order_id, $progress_key, true );
	$legacy_progress = is_array( $legacy_progress ) ? $legacy_progress : [];
	$is_completed = (int) get_post_meta( $order_id, 'comment_status', true ) === 1;

	$state = [
		'status'        => $is_completed ? 'completed' : 'failed',
		'updated_at'    => $legacy_time > 0 ? $legacy_time : $now,
		'started_at'    => $legacy_time > 0 ? $legacy_time : $now,
		'owner_user_id' => (int) $owner_user_id,
		'legacy_lock'   => true,
		'summary'       => isset( $legacy_progress['summary'] ) && is_array( $legacy_progress['summary'] ) ? $legacy_progress['summary'] : [],
	];
	update_post_meta( $order_id, $state_key, $state );
}

$current_state = sanitize_key( (string) ( $state['status'] ?? '' ) );
$was_failed = false;

if ( $current_state === 'completed' ) {
	$state_summary = isset( $state['summary'] ) && is_array( $state['summary'] ) ? $state['summary'] : [];
	wp_send_json_success(
		[
			'status'               => 'already_completed',
			'processed_users'      => (int) ( $state_summary['processed_users'] ?? 0 ),
			'created_users'        => (array) ( $state_summary['created_users'] ?? [] ),
			'skipped_users'        => (array) ( $state_summary['skipped_users'] ?? [] ),
			'pointed_users'        => (array) ( $state_summary['pointed_users'] ?? [] ),
			'order_feedback_state' => 'completed',
		]
	);
}

if ( $current_state === 'processing' ) {
	$updated_at = (int) ( $state['updated_at'] ?? 0 );
	if ( $updated_at > 0 && ( $now - $updated_at ) < $lock_timeout ) {
		$log_event(
			'warning',
			[
				'module'              => 'owner_feedback',
				'order_id'            => $order_id,
				'room_id'             => $room_id,
				'owner_user_id'       => (int) $owner_user_id,
				'status'              => 'in_progress',
				'processing_age_sec'  => ( $now - $updated_at ),
				'lock_timeout_sec'    => $lock_timeout,
			]
		);
		wp_send_json_success(
			[
				'status'               => 'in_progress',
				'processed_users'      => 0,
				'created_users'        => [],
				'skipped_users'        => [],
				'pointed_users'        => [],
				'order_feedback_state' => 'processing',
			]
		);
	}
	$current_state = 'failed';
	$was_failed = true;
}

if ( $current_state === 'failed' ) {
	$was_failed = true;
}

$processing_state = [
	'status'        => 'processing',
	'updated_at'    => $now,
	'started_at'    => isset( $state['started_at'] ) ? (int) $state['started_at'] : $now,
	'owner_user_id' => (int) $owner_user_id,
];
update_post_meta( $order_id, $state_key, $processing_state );

$progress = get_post_meta( $order_id, $progress_key, true );
$progress = is_array( $progress ) ? $progress : [];
$progress['processed_user_ids'] = array_values( array_unique( array_map( 'intval', (array) ( $progress['processed_user_ids'] ?? [] ) ) ) );
$progress['pointed_user_ids'] = array_values( array_unique( array_map( 'intval', (array) ( $progress['pointed_user_ids'] ?? [] ) ) ) );
$progress['skipped_users'] = array_values( array_filter( (array) ( $progress['skipped_users'] ?? [] ), 'is_array' ) );
$progress['created_users'] = array_values( array_unique( array_map( 'intval', array_merge( (array) ( $progress['created_users'] ?? [] ), $created_users ) ) ) );
$skipped_users = array_values( array_filter( array_merge( $progress['skipped_users'], $skipped_users ), 'is_array' ) );
update_post_meta( $order_id, $progress_key, $progress );

$feedback_payload = [
	'room_id'       => $room_id,
	'order_id'      => $order_id,
	'owner_comment' => $comment_message,
	'vote'          => $vote_value,
	'owner_user_id' => (int) $owner_user_id,
	'created_at'    => $now,
];

$processed_users_map = array_fill_keys( $progress['processed_user_ids'], true );
$pointed_users_map = array_fill_keys( $progress['pointed_user_ids'], true );

try {
	foreach ( $target_user_ids as $uid ) {
		if ( $uid <= 0 || isset( $processed_users_map[ $uid ] ) ) {
			continue;
		}

		$already_for_order = false;
		$feedback_list = get_user_meta( $uid, 'owners_feedback', true );
		$feedback_list = is_array( $feedback_list ) ? $feedback_list : [];

		foreach ( $feedback_list as $row ) {
			if ( is_array( $row ) && (int) ( $row['order_id'] ?? 0 ) === $order_id ) {
				$already_for_order = true;
				break;
			}
		}

		if ( ! $already_for_order ) {
			$feedback_list[] = $feedback_payload;
			update_user_meta( $uid, 'owners_feedback', $feedback_list );
		}

		$vote_lock_key = '_owner_feedback_vote_counted_' . $order_id;
		if ( add_user_meta( $uid, $vote_lock_key, 1, true ) ) {
			if ( $vote_value === 'like' ) {
				$current_like = (int) get_user_meta( $uid, 'owners_like', true );
				update_user_meta( $uid, 'owners_like', $current_like + 1 );
			} else {
				$current_dislike = (int) get_user_meta( $uid, 'owners_dislike', true );
				update_user_meta( $uid, 'owners_dislike', $current_dislike + 1 );
			}
		}

		if ( $vote_value === 'like' && ! isset( $pointed_users_map[ $uid ] ) ) {
			$point_lock_key = '_owner_satisfaction_order_' . $order_id;
			if ( add_user_meta( $uid, $point_lock_key, 1, true ) && function_exists( 'add_point' ) ) {
				add_point( 'owner_satisfaction', $uid, 'رضایت مجموعه دار - سفارش ' . $order_id );
			}
			$pointed_users_map[ $uid ] = true;
		}

		if ( $already_for_order ) {
			$skipped_users[] = [ 'user_id' => $uid, 'reason' => 'feedback_exists' ];
		}

		$processed_users_map[ $uid ] = true;
		$progress['processed_user_ids'] = array_values( array_map( 'intval', array_keys( $processed_users_map ) ) );
		$progress['pointed_user_ids'] = array_values( array_map( 'intval', array_keys( $pointed_users_map ) ) );
		$progress['skipped_users'] = $skipped_users;
		$progress['created_users'] = array_values( array_unique( array_map( 'intval', array_merge( $progress['created_users'], $created_users ) ) ) );
		update_post_meta( $order_id, $progress_key, $progress );
		$processing_state['updated_at'] = time();
		update_post_meta( $order_id, $state_key, $processing_state );
	}
} catch ( Throwable $e ) {
	$failed_state = [
		'status'        => 'failed',
		'updated_at'    => time(),
		'started_at'    => (int) $processing_state['started_at'],
		'owner_user_id' => (int) $owner_user_id,
		'last_error'    => $e->getMessage(),
	];
	update_post_meta( $order_id, $state_key, $failed_state );
	$log_event(
		'error',
		[
			'module'             => 'owner_feedback',
			'order_id'           => $order_id,
			'room_id'            => $room_id,
			'owner_user_id'      => (int) $owner_user_id,
			'status'             => 'failed_recoverable',
			'processed_users'    => count( $processed_users_map ),
			'skipped_count'      => count( $skipped_users ),
			'pointed_users_count'=> count( $pointed_users_map ),
			'error'              => $e->getMessage(),
		]
	);

	wp_send_json_error(
		[
			'status'               => 'failed_recoverable',
			'message'              => 'ثبت بازخورد با خطای موقت روبه‌رو شد. دوباره تلاش کنید.',
			'processed_users'      => count( $processed_users_map ),
			'created_users'        => array_values( array_unique( array_map( 'intval', array_merge( $progress['created_users'], $created_users ) ) ) ),
			'skipped_users'        => $skipped_users,
			'pointed_users'        => array_values( array_map( 'intval', array_keys( $pointed_users_map ) ) ),
			'order_feedback_state' => 'failed',
		]
	);
}

update_post_meta( $order_id, 'comment_status', 1 );

$processed_users = count( array_keys( $processed_users_map ) );
$pointed_users = array_values( array_map( 'intval', array_keys( $pointed_users_map ) ) );
$created_users = array_values( array_unique( array_map( 'intval', array_merge( $progress['created_users'], $created_users ) ) ) );
$final_status = $was_failed ? 'partial_recovered' : 'success';
if ( count( $skipped_users ) >= 3 ) {
	$log_event(
		'warning',
		[
			'module'             => 'owner_feedback',
			'order_id'           => $order_id,
			'room_id'            => $room_id,
			'owner_user_id'      => (int) $owner_user_id,
			'status'             => $final_status,
			'processed_users'    => $processed_users,
			'skipped_count'      => count( $skipped_users ),
			'pointed_users_count'=> count( $pointed_users ),
		]
	);
}

$summary = [
	'processed_users' => $processed_users,
	'created_users'   => $created_users,
	'skipped_users'   => $skipped_users,
	'pointed_users'   => $pointed_users,
];

$completed_state = [
	'status'        => 'completed',
	'updated_at'    => time(),
	'started_at'    => (int) $processing_state['started_at'],
	'owner_user_id' => (int) $owner_user_id,
	'summary'       => $summary,
];
update_post_meta( $order_id, $state_key, $completed_state );
update_post_meta(
	$order_id,
	$progress_key,
	[
		'status'             => 'completed',
		'processed_user_ids' => array_values( array_map( 'intval', array_keys( $processed_users_map ) ) ),
		'pointed_user_ids'   => $pointed_users,
		'created_users'      => $created_users,
		'skipped_users'      => $skipped_users,
		'summary'            => $summary,
	]
);

wp_send_json_success(
	[
		'status'               => $final_status,
		'processed_users'      => $processed_users,
		'created_users'        => $created_users,
		'skipped_users'        => $skipped_users,
		'pointed_users'        => $pointed_users,
		'order_feedback_state' => 'completed',
	]
);
