<?php
/**
 * Order satisfaction: wp_markting.order_satisfaction_status + wp_orders_satisfaction_history.
 *
 * Status values must match DB ENUM on wp_markting.order_satisfaction_status.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EZ_ORDER_SATISFACTION_PENDING', 'PENDING' );
define( 'EZ_ORDER_SATISFACTION_SATISFIED', 'SATISFIED' );
define( 'EZ_ORDER_SATISFACTION_DISSATISFIED', 'DISSATISFIED' );
define( 'EZ_ORDER_SATISFACTION_META_ORDER_ID', 'ez_satisfaction_order_id' );
define( 'EZ_ORDER_SATISFACTION_META_BOUND_AT', 'ez_satisfaction_bound_at' );
define( 'EZ_ORDER_SATISFACTION_META_BINDING_SOURCE', 'ez_satisfaction_binding_source' );
define( 'EZ_ORDER_SATISFACTION_META_BINDING_CONFIDENCE', 'ez_satisfaction_binding_confidence' );

/**
 * Table names (project uses literal wp_ prefix elsewhere).
 */
function ez_order_satisfaction_markting_table(): string {
	return 'wp_markting';
}

/**
 * History table — expected columns:
 * id, order_id, game_id, old_status, new_status, source, details, created_at, updated_at.
 * Adjust if your DDL differs.
 */
function ez_order_satisfaction_history_table(): string {
	return 'wp_orders_satisfaction_history';
}

/**
 * @param string|null $status Raw DB value.
 */
function ez_order_satisfaction_normalize_status( $status ): string {
	if ( $status === null || $status === '' ) {
		return EZ_ORDER_SATISFACTION_PENDING;
	}
	$s = strtoupper( trim( (string) $status ) );
	if ( in_array( $s, array( EZ_ORDER_SATISFACTION_PENDING, EZ_ORDER_SATISFACTION_SATISFIED, EZ_ORDER_SATISFACTION_DISSATISFIED ), true ) ) {
		return $s;
	}
	return EZ_ORDER_SATISFACTION_PENDING;
}

/**
 * @param int         $order_id Woo order ID.
 * @param int         $game_id  Product / game ID.
 * @param string|null $old_status
 * @param string      $new_status
 * @param string      $source Machine key, e.g. wallet_conversion, comment_rating.
 * @param array       $details Structured context saved in details.
 */
function ez_order_satisfaction_log_history( int $order_id, int $game_id, $old_status, string $new_status, string $source, array $details = array() ): void {
	ez_order_satisfaction_history_upsert( $order_id, $game_id, $old_status, $new_status, $source, $details );
}

/**
 * Read single-row history by order_id.
 *
 * @param int $order_id
 * @return array<string,mixed>|null
 */
function ez_order_satisfaction_history_get_by_order_id( int $order_id ): ?array {
	global $wpdb;

	if ( $order_id < 1 ) {
		return null;
	}

	$table = ez_order_satisfaction_history_table();
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name fixed in theme.
	$sql = $wpdb->prepare( "SELECT * FROM `{$table}` WHERE order_id = %d LIMIT 1", $order_id );
	$row = $wpdb->get_row( $sql, ARRAY_A );

	return is_array( $row ) && ! empty( $row ) ? $row : null;
}

/**
 * Upsert single-row snapshot history for an order.
 *
 * @param int         $order_id
 * @param int         $game_id
 * @param string|null $old_status
 * @param string      $new_status
 * @param string      $source
 * @param array       $details
 */
function ez_order_satisfaction_history_upsert( int $order_id, int $game_id, $old_status, string $new_status, string $source, array $details = array() ): void {
	global $wpdb;

	if ( $order_id < 1 ) {
		return;
	}

	$table   = ez_order_satisfaction_history_table();
	$old_n   = $old_status === null || $old_status === '' ? null : ez_order_satisfaction_normalize_status( $old_status );
	$new_n   = ez_order_satisfaction_normalize_status( $new_status );
	$src     = sanitize_key( $source );
	$details = ! empty( $details ) ? wp_json_encode( $details ) : null;
	$now_dt  = current_time( 'mysql' );
	$exists  = ez_order_satisfaction_history_get_by_order_id( $order_id );

	$data   = array(
		'order_id'   => $order_id,
		'old_status' => $old_n,
		'new_status' => $new_n,
		'source'     => $src,
		'details'    => $details,
		'updated_at' => $now_dt,
	);
	$format = array( '%d', '%s', '%s', '%s', '%s', '%s' );

	if ( $game_id > 0 ) {
		$data['game_id'] = $game_id;
		$format = array( '%d', '%s', '%s', '%s', '%s', '%s', '%d' );
	}

	if ( $exists ) {
		$wpdb->update(
			$table,
			$data,
			array( 'order_id' => $order_id ),
			$format,
			array( '%d' )
		);
	} else {
		$data['created_at'] = $now_dt;
		$insert_format = $format;
		$insert_format[] = '%s';
		$wpdb->insert( $table, $data, $insert_format );
	}

	if ( $wpdb->last_error ) {
		error_log( 'ez_order_satisfaction_log_history: ' . $wpdb->last_error );
	}
}

/**
 * Check if snapshot history row exists for order.
 *
 * @param int    $order_id
 */
function ez_order_satisfaction_history_exists( int $order_id ): bool {
	return null !== ez_order_satisfaction_history_get_by_order_id( $order_id );
}

/**
 * @param int    $order_id
 * @param string $new_status One of EZ_ORDER_SATISFACTION_*.
 * @param string $source
 * @param array  $context Optional: game_id override, details.
 * @return bool True if marketing row updated (or already equal).
 */
function ez_order_satisfaction_set_status( int $order_id, string $new_status, string $source, array $context = array() ): bool {
	$new_status = ez_order_satisfaction_normalize_status( $new_status );
	if ( ! in_array( $new_status, array( EZ_ORDER_SATISFACTION_PENDING, EZ_ORDER_SATISFACTION_SATISFIED, EZ_ORDER_SATISFACTION_DISSATISFIED ), true ) ) {
		return false;
	}

	$medoo = medoo();
	if ( ! $medoo ) {
		return false;
	}

	$table = ez_order_satisfaction_markting_table();
	$row   = $medoo->get(
		$table,
		array( 'order_satisfaction_status', 'game_id' ),
		array( 'order_id' => $order_id )
	);

	if ( ! $row ) {
		return false;
	}

	$old = isset( $row['order_satisfaction_status'] ) ? $row['order_satisfaction_status'] : null;
	$game_id = isset( $context['game_id'] ) ? (int) $context['game_id'] : (int) ( $row['game_id'] ?? 0 );
	$details = isset( $context['details'] ) && is_array( $context['details'] ) ? $context['details'] : array();

	if ( ez_order_satisfaction_normalize_status( $old ) === $new_status ) {
		// PENDING is marketing-only in final policy; do not upsert history.
		if ( $new_status !== EZ_ORDER_SATISFACTION_PENDING && ! ez_order_satisfaction_history_exists( $order_id ) ) {
			ez_order_satisfaction_log_history( $order_id, $game_id, $old, $new_status, $source, $details );
		}
		return true;
	}

	$updated = $medoo->update(
		$table,
		array( 'order_satisfaction_status' => $new_status ),
		array( 'order_id' => $order_id )
	);

	if ( $updated === false ) {
		return false;
	}

	// Final policy: PENDING should update marketing only.
	if ( $new_status === EZ_ORDER_SATISFACTION_PENDING ) {
		return true;
	}

	ez_order_satisfaction_log_history( $order_id, $game_id, $old, $new_status, $source, $details );
	return true;
}

/**
 * When a cancellation refund is approved (manual approve or cron auto-approve).
 *
 * @param int    $order_id       WooCommerce order ID.
 * @param string $requester_type cancellation_requests.requester_type: owner | customer.
 * @param array  $context        Optional: request_id, request_created_at.
 */
function ez_order_satisfaction_on_cancellation_refund_approved( int $order_id, string $requester_type, array $context = array() ): void {
	$medoo = medoo();
	if ( ! $medoo || $order_id < 1 ) {
		return;
	}

	$game_id = 0;
	$mr      = $medoo->get( ez_order_satisfaction_markting_table(), array( 'game_id' ), array( 'order_id' => $order_id ) );
	if ( $mr && ! empty( $mr['game_id'] ) ) {
		$game_id = (int) $mr['game_id'];
	}

	$details = array(
		'requester_type' => (string) $requester_type,
	);
	if ( ! empty( $context['request_id'] ) ) {
		$details['cancellation_request_id'] = (int) $context['request_id'];
	}
	if ( ! empty( $context['request_created_at'] ) ) {
		$details['request_created_at'] = (string) $context['request_created_at'];
	}

	if ( $requester_type === 'owner' ) {
		ez_order_satisfaction_set_status( $order_id, EZ_ORDER_SATISFACTION_DISSATISFIED, 'cancellation_owner_refund', array( 'game_id' => $game_id, 'details' => $details ) );
	} elseif ( $requester_type === 'customer' ) {
		ez_order_satisfaction_set_status( $order_id, EZ_ORDER_SATISFACTION_PENDING, 'cancellation_customer_refund', array( 'game_id' => $game_id, 'details' => $details ) );
	}
}

/**
 * After successful wallet conversion: SATISFIED unless already DISSATISFIED.
 */
function ez_order_satisfaction_on_wallet_conversion( int $order_id ): void {
	$medoo = medoo();
	if ( ! $medoo ) {
		return;
	}

	$table = ez_order_satisfaction_markting_table();
	$row   = $medoo->get(
		$table,
		array( 'order_satisfaction_status', 'game_id' ),
		array( 'order_id' => $order_id )
	);

	if ( ! $row ) {
		return;
	}

	$current = ez_order_satisfaction_normalize_status( $row['order_satisfaction_status'] ?? null );
	if ( $current === EZ_ORDER_SATISFACTION_DISSATISFIED ) {
		return;
	}

	$game_id = (int) ( $row['game_id'] ?? 0 );
	ez_order_satisfaction_set_status(
		$order_id,
		EZ_ORDER_SATISFACTION_SATISFIED,
		'wallet_conversion',
		array(
			'game_id' => $game_id,
			'details' => array(
				'reason' => 'wallet_conversion',
			),
		)
	);
}

/**
 * Apply product review average only for the order leader (customer_id on marketing row).
 *
 * @param int   $order_id           Marketing order_id.
 * @param int   $game_id            Product ID.
 * @param int   $leader_customer_id wp_markting.customer_id.
 * @param int   $comment_user_id    comment user_id.
 * @param float $rating             Stored average (commentmeta rating).
 */
function ez_order_satisfaction_apply_leader_rating( int $order_id, int $game_id, int $leader_customer_id, int $comment_user_id, float $rating, array $details = array() ): void {
	if ( $order_id < 1 || (int) $comment_user_id !== (int) $leader_customer_id ) {
		return;
	}

	$target = ( $rating < 3.0 ) ? EZ_ORDER_SATISFACTION_DISSATISFIED : EZ_ORDER_SATISFACTION_SATISFIED;
	$details = array_merge(
		array(
			'comment_user_id' => (int) $comment_user_id,
			'rating'          => (float) $rating,
		),
		$details
	);
	ez_order_satisfaction_set_status( $order_id, $target, 'comment_rating', array( 'game_id' => $game_id, 'details' => $details ) );
}

/**
 * Comment binding metadata by comment_id.
 *
 * @return array<string,mixed>
 */
function ez_order_satisfaction_get_comment_binding( int $comment_id ): array {
	if ( $comment_id < 1 ) {
		return array();
	}
	return array(
		'order_id'   => (int) get_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_ORDER_ID, true ),
		'bound_at'   => (string) get_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BOUND_AT, true ),
		'source'     => (string) get_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BINDING_SOURCE, true ),
		'confidence' => (string) get_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BINDING_CONFIDENCE, true ),
	);
}

/**
 * @param array<string,mixed> $binding
 */
function ez_order_satisfaction_store_comment_binding( int $comment_id, array $binding ): void {
	if ( $comment_id < 1 ) {
		return;
	}

	$order_id   = (int) ( $binding['order_id'] ?? 0 );
	$bound_at   = (string) ( $binding['bound_at'] ?? current_time( 'mysql' ) );
	$source     = sanitize_key( (string) ( $binding['source'] ?? 'live_sync' ) );
	$confidence = sanitize_key( (string) ( $binding['confidence'] ?? 'high' ) );

	update_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_ORDER_ID, $order_id );
	update_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BOUND_AT, $bound_at );
	update_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BINDING_SOURCE, $source );
	update_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BINDING_CONFIDENCE, $confidence );
}

function ez_order_satisfaction_clear_comment_binding( int $comment_id ): void {
	if ( $comment_id < 1 ) {
		return;
	}
	delete_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_ORDER_ID );
	delete_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BOUND_AT );
	delete_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BINDING_SOURCE );
	delete_comment_meta( $comment_id, EZ_ORDER_SATISFACTION_META_BINDING_CONFIDENCE );
}

/**
 * Resolve runtime binding: latest valid row + leader check.
 *
 * @return array<string,mixed>
 */
function ez_order_satisfaction_resolve_runtime_binding( WP_Comment $comment ): array {
	$product_id = (int) $comment->comment_post_ID;
	$user_id    = (int) $comment->user_id;
	if ( $product_id < 1 || $user_id < 1 || ! function_exists( 'ez_resolve_user_latest_valid_markting_row' ) || ! function_exists( 'ez_user_billing_phone_10' ) ) {
		return array( 'order_id' => 0, 'confidence' => 'high', 'eligible' => false );
	}
	$user = get_user_by( 'id', $user_id );
	if ( ! $user ) {
		return array( 'order_id' => 0, 'confidence' => 'high', 'eligible' => false );
	}

	$row = ez_resolve_user_latest_valid_markting_row( $user_id, $product_id, ez_user_billing_phone_10( $user ) );
	if ( is_wp_error( $row ) || empty( $row->order_id ) ) {
		return array( 'order_id' => 0, 'confidence' => 'high', 'eligible' => false );
	}

	$is_leader = ( (int) ( $row->customer_id ?? 0 ) === $user_id );
	return array(
		'order_id'     => $is_leader ? (int) $row->order_id : 0,
		'game_id'      => $product_id,
		'customer_id'  => (int) ( $row->customer_id ?? 0 ),
		'comment_user' => $user_id,
		'eligible'     => $is_leader,
		'confidence'   => 'high',
	);
}

/**
 * Resolve latest leader order in wp_markting for a product.
 *
 * @return array<string,mixed>
 */
function ez_order_satisfaction_resolve_latest_leader_order_row( int $user_id, int $product_id ): array {
	global $wpdb;

	if ( $user_id < 1 || $product_id < 1 ) {
		return array();
	}

	$sql = $wpdb->prepare(
		"SELECT order_id, game_id
		 FROM wp_markting
		 WHERE game_id = %d
		   AND customer_id = %d
		 ORDER BY order_sans_date DESC, order_sans_time DESC, id DESC
		 LIMIT 1",
		$product_id,
		$user_id
	);
	$row = $wpdb->get_row( $sql, ARRAY_A );
	return is_array( $row ) && ! empty( $row['order_id'] ) ? $row : array();
}

function ez_order_satisfaction_resolve_latest_leader_order_id( int $user_id, int $product_id ): int {
	$row = ez_order_satisfaction_resolve_latest_leader_order_row( $user_id, $product_id );
	return (int) ( $row['order_id'] ?? 0 );
}

/**
 * Resolve order from history.details.comment_id as high-confidence legacy fallback.
 *
 * @return array<string,mixed>
 */
function ez_order_satisfaction_resolve_legacy_binding_from_history( int $comment_id ): array {
	global $wpdb;

	if ( $comment_id < 1 ) {
		return array();
	}

	$table = ez_order_satisfaction_history_table();
	$like  = '%"comment_id":' . $comment_id . '%';
	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table is fixed in theme.
	$sql = $wpdb->prepare( "SELECT order_id, game_id FROM `{$table}` WHERE source = %s AND details LIKE %s ORDER BY id DESC LIMIT 1", 'comment_rating', $like );
	$row = $wpdb->get_row( $sql, ARRAY_A );
	if ( ! is_array( $row ) || empty( $row['order_id'] ) ) {
		return array();
	}

	return array(
		'order_id'   => (int) $row['order_id'],
		'game_id'    => (int) ( $row['game_id'] ?? 0 ),
		'confidence' => 'high',
		'source'     => 'history_match',
	);
}

/**
 * Resolve legacy binding by nearest leader order as-of comment datetime.
 *
 * @return array<string,mixed>
 */
function ez_order_satisfaction_resolve_legacy_binding_by_comment_time( WP_Comment $comment ): array {
	global $wpdb;

	$product_id = (int) $comment->comment_post_ID;
	$user_id    = (int) $comment->user_id;
	$comment_at = (string) $comment->comment_date;
	if ( $product_id < 1 || $user_id < 1 || $comment_at === '' ) {
		return array();
	}

	$sql = $wpdb->prepare(
		"SELECT order_id, game_id
		 FROM wp_markting
		 WHERE game_id = %d
		   AND customer_id = %d
		   AND order_status IN ('wc-completed', 'wc-completed-paid', 'wc-walletx', 'wc-partially-paid', 'wc-refunded')
		   AND CONCAT(order_sans_date, ' ', order_sans_time) <= %s
		 ORDER BY order_sans_date DESC, order_sans_time DESC, id DESC
		 LIMIT 1",
		$product_id,
		$user_id,
		$comment_at
	);
	$row = $wpdb->get_row( $sql, ARRAY_A );
	if ( ! is_array( $row ) || empty( $row['order_id'] ) ) {
		return array();
	}

	return array(
		'order_id'   => (int) $row['order_id'],
		'game_id'    => (int) ( $row['game_id'] ?? 0 ),
		'confidence' => 'medium',
		'source'     => 'comment_time_match',
	);
}

/**
 * Legacy fallback resolver for comments missing binding metadata.
 *
 * @return array<string,mixed>
 */
function ez_order_satisfaction_resolve_comment_order_binding_legacy( WP_Comment $comment ): array {
	$comment_id = (int) $comment->comment_ID;
	$user_id    = (int) $comment->user_id;
	$product_id = (int) $comment->comment_post_ID;

	$high = ez_order_satisfaction_resolve_legacy_binding_from_history( $comment_id );
	if ( ! empty( $high['order_id'] ) ) {
		return $high;
	}

	$medium = ez_order_satisfaction_resolve_legacy_binding_by_comment_time( $comment );
	if ( ! empty( $medium['order_id'] ) ) {
		return $medium;
	}

	$low = ez_order_satisfaction_resolve_latest_leader_order_row( $user_id, $product_id );
	if ( ! empty( $low['order_id'] ) ) {
		return array(
			'order_id'   => (int) $low['order_id'],
			'game_id'    => (int) ( $low['game_id'] ?? 0 ),
			'confidence' => 'low',
			'source'     => 'latest_leader',
		);
	}

	return array( 'order_id' => 0, 'confidence' => 'low', 'source' => 'not_found' );
}

/**
 * Read wp_markting row used in revert decision.
 */
function ez_order_satisfaction_get_markting_row( int $order_id ): ?array {
	$medoo = medoo();
	if ( ! $medoo || $order_id < 1 ) {
		return null;
	}

	$row = $medoo->get(
		ez_order_satisfaction_markting_table(),
		array( 'order_id', 'game_id', 'customer_id', 'order_status', 'order_sans_date', 'order_sans_time', 'order_satisfaction_status' ),
		array( 'order_id' => $order_id )
	);

	return is_array( $row ) && ! empty( $row ) ? $row : null;
}

/**
 * Was this order refunded due to owner cancellation?
 */
function ez_order_satisfaction_has_owner_refund( int $order_id ): bool {
	$medoo = medoo();
	if ( ! $medoo || $order_id < 1 ) {
		return false;
	}

	$count = (int) $medoo->count(
		'cancellation_requests',
		array(
			'order_id'       => $order_id,
			'requester_type' => 'owner',
			'status'         => 'approved',
		)
	);

	return $count > 0;
}

/**
 * True when order is wallet-converted and its session time has passed.
 */
function ez_order_satisfaction_is_wallet_and_time_passed( array $markting_row ): bool {
	$status = (string) ( $markting_row['order_status'] ?? '' );
	if ( $status !== 'wc-walletx' ) {
		return false;
	}

	$date = (string) ( $markting_row['order_sans_date'] ?? '' );
	$time = (string) ( $markting_row['order_sans_time'] ?? '' );
	if ( $date === '' || $time === '' ) {
		return false;
	}

	$sans_ts = strtotime( $date . ' ' . $time );
	if ( ! $sans_ts ) {
		return false;
	}

	return current_time( 'timestamp' ) >= $sans_ts;
}

/**
 * Revert target after deleting comment effect.
 */
function ez_order_satisfaction_resolve_revert_status_after_comment_delete( int $order_id, array $markting_row ): string {
	if ( ez_order_satisfaction_has_owner_refund( $order_id ) ) {
		return EZ_ORDER_SATISFACTION_DISSATISFIED;
	}

	if ( ez_order_satisfaction_is_wallet_and_time_passed( $markting_row ) ) {
		return EZ_ORDER_SATISFACTION_SATISFIED;
	}

	return EZ_ORDER_SATISFACTION_PENDING;
}

/**
 * Delete history snapshot row for order_id.
 */
function ez_order_satisfaction_history_delete_by_order_id( int $order_id ): bool {
	global $wpdb;

	if ( $order_id < 1 ) {
		return false;
	}

	$table   = ez_order_satisfaction_history_table();
	$deleted = $wpdb->delete( $table, array( 'order_id' => $order_id ), array( '%d' ) );
	return $deleted !== false;
}

/**
 * Delete history only when snapshot source is comment_rating and matches comment_id.
 */
function ez_order_satisfaction_history_delete_comment_effect( int $order_id, int $comment_id = 0 ): bool {
	$row = ez_order_satisfaction_history_get_by_order_id( $order_id );
	if ( ! $row ) {
		return false;
	}

	$source = sanitize_key( (string) ( $row['source'] ?? '' ) );
	if ( $source !== 'comment_rating' ) {
		return false;
	}

	if ( $comment_id > 0 && ! empty( $row['details'] ) ) {
		$details = json_decode( (string) $row['details'], true );
		if ( is_array( $details ) && isset( $details['comment_id'] ) && (int) $details['comment_id'] !== $comment_id ) {
			return false;
		}
	}

	return ez_order_satisfaction_history_delete_by_order_id( $order_id );
}

/**
 * Remove one comment effect from a specific order and revert order status.
 */
function ez_order_satisfaction_detach_comment_effect_from_order( int $order_id, int $comment_id, string $event ): bool {
	$markting_row = ez_order_satisfaction_get_markting_row( $order_id );
	if ( ! $markting_row ) {
		return false;
	}

	$old_history = ez_order_satisfaction_history_get_by_order_id( $order_id );
	$target      = ez_order_satisfaction_resolve_revert_status_after_comment_delete( $order_id, $markting_row );
	$source      = 'comment_delete_revert_pending';
	if ( $target === EZ_ORDER_SATISFACTION_DISSATISFIED ) {
		$source = 'comment_delete_revert_owner_refund';
	} elseif ( $target === EZ_ORDER_SATISFACTION_SATISFIED ) {
		$source = 'comment_delete_revert_wallet_passed';
	}

	$details = array(
		'comment_id'                     => $comment_id,
		'event'                          => sanitize_key( $event ),
		'previous_history_source'        => sanitize_key( (string) ( $old_history['source'] ?? '' ) ),
		'deleted_comment_effect_history' => ez_order_satisfaction_history_delete_comment_effect( $order_id, $comment_id ),
	);

	return ez_order_satisfaction_set_status(
		$order_id,
		$target,
		$source,
		array(
			'game_id' => (int) ( $markting_row['game_id'] ?? 0 ),
			'details' => $details,
		)
	);
}

/**
 * Central orchestrator for approved comment add/edit/approve actions.
 */
function ez_order_satisfaction_sync_comment_effect( int $comment_id, string $source = 'comment_sync' ): void {
	$c = get_comment( $comment_id );
	if ( ! $c || $c->comment_type !== 'review' || (string) $c->comment_approved !== '1' ) {
		return;
	}

	$product_id = (int) $c->comment_post_ID;
	if ( $product_id < 1 || get_post_type( $product_id ) !== 'product' ) {
		return;
	}

	$rating_raw = get_comment_meta( $comment_id, 'rating', true );
	$rating     = is_numeric( $rating_raw ) ? (float) $rating_raw : 0.0;
	$old_bind   = ez_order_satisfaction_get_comment_binding( $comment_id );
	$old_order  = (int) ( $old_bind['order_id'] ?? 0 );

	$new_bind  = ez_order_satisfaction_resolve_runtime_binding( $c );
	$new_order = (int) ( $new_bind['order_id'] ?? 0 );

	// If user is currently teammate for latest order, keep existing bound effect untouched.
	if ( $new_order < 1 ) {
		return;
	}

	if ( $old_order > 0 && $old_order !== $new_order ) {
		ez_order_satisfaction_detach_comment_effect_from_order( $old_order, $comment_id, 'comment_migrate_detach' );
	}

	$leader_customer = (int) ( $new_bind['customer_id'] ?? 0 );
	$comment_user_id = (int) $c->user_id;
	if ( $leader_customer !== $comment_user_id ) {
		return;
	}

	$bind_source = sanitize_key( $source );
	if ( $bind_source === '' ) {
		$bind_source = 'live_sync';
	}

	ez_order_satisfaction_apply_leader_rating(
		$new_order,
		$product_id,
		$leader_customer,
		$comment_user_id,
		$rating,
		array(
			'comment_id'      => (int) $comment_id,
			'comment_user_id' => $comment_user_id,
		)
	);

	ez_order_satisfaction_store_comment_binding(
		$comment_id,
		array(
			'order_id'   => $new_order,
			'bound_at'   => current_time( 'mysql' ),
			'source'     => $bind_source,
			'confidence' => 'high',
		)
	);
}

/**
 * Meta-first remove flow for trashed/deleted comments.
 */
function ez_order_satisfaction_remove_comment_effect( int $comment_id, string $event = 'deleted_comment', $comment = null ): void {
	$c = ( $comment instanceof WP_Comment ) ? $comment : get_comment( $comment_id );
	if ( ! $c || $c->comment_type !== 'review' ) {
		return;
	}

	$product_id = (int) $c->comment_post_ID;
	$user_id    = (int) $c->user_id;
	if ( $product_id < 1 || $user_id < 1 || get_post_type( $product_id ) !== 'product' ) {
		return;
	}

	$binding    = ez_order_satisfaction_get_comment_binding( $comment_id );
	$order_id   = (int) ( $binding['order_id'] ?? 0 );
	$confidence = sanitize_key( (string) ( $binding['confidence'] ?? '' ) );
	if ( $order_id < 1 ) {
		$legacy = ez_order_satisfaction_resolve_comment_order_binding_legacy( $c );
		$order_id = (int) ( $legacy['order_id'] ?? 0 );
		$confidence = sanitize_key( (string) ( $legacy['confidence'] ?? '' ) );
		// Runtime fallback is strict: reject low confidence.
		if ( $confidence === 'low' || $order_id < 1 ) {
			return;
		}
	}

	$markting_row = ez_order_satisfaction_get_markting_row( $order_id );
	if ( ! $markting_row || (int) ( $markting_row['customer_id'] ?? 0 ) !== $user_id ) {
		return;
	}

	$ok = ez_order_satisfaction_detach_comment_effect_from_order( $order_id, $comment_id, $event );
	if ( ! $ok ) {
		return;
	}

	ez_order_satisfaction_clear_comment_binding( $comment_id );
}

/**
 * Keep backward compatibility for existing call sites.
 */
function ez_order_satisfaction_try_apply_from_comment_id( int $comment_id ): void {
	ez_order_satisfaction_sync_comment_effect( $comment_id, 'comment_sync' );
}

/**
 * Deprecated with current policy: hold/unapprove must not change satisfaction effect.
 */
function ez_order_satisfaction_on_comment_unpublished( int $order_id, int $game_id ): void {
	unset( $order_id, $game_id );
}

/**
 * Deprecated with current policy: hold/unapprove must not change satisfaction effect.
 */
function ez_order_satisfaction_try_pending_if_leader_review_was_approved( int $comment_id, string $old_approved_before_action, string $source = 'crm_comment_unpublished' ): void {
	unset( $comment_id, $old_approved_before_action, $source );
}

/**
 * Trash/delete hooks apply comment-effect cleanup + status revert.
 */
function ez_order_satisfaction_on_trashed_comment( int $comment_id, $previous_status = null ): void {
	unset( $previous_status );
	ez_order_satisfaction_remove_comment_effect( $comment_id, 'trashed_comment', null );
}
add_action( 'trashed_comment', 'ez_order_satisfaction_on_trashed_comment', 20, 2 );

function ez_order_satisfaction_on_deleted_comment( int $comment_id, $comment = null ): void {
	ez_order_satisfaction_remove_comment_effect( $comment_id, 'deleted_comment', $comment );
}
add_action( 'deleted_comment', 'ez_order_satisfaction_on_deleted_comment', 20, 2 );

/**
 * Public satisfaction % for product from snapshot history table:
 * 100 * SATISFIED / (SATISFIED + DISSATISFIED). PENDING excluded.
 *
 * @param int $product_id Woo product (game) ID.
 * @return float|null Null if denominator is zero.
 */
function ez_product_satisfaction_percent_from_markting( int $product_id ): ?float {
	global $wpdb;

	if ( $product_id < 1 ) {
		return null;
	}

	$h_table = ez_order_satisfaction_history_table();

	// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name is fixed in theme.
	$sql = $wpdb->prepare(
		"SELECT 
			SUM(CASE WHEN new_status = %s THEN 1 ELSE 0 END) AS sat,
			SUM(CASE WHEN new_status = %s THEN 1 ELSE 0 END) AS dissat
		 FROM `{$h_table}` 
		 WHERE game_id = %d 
		   AND new_status IN (%s, %s)",
		EZ_ORDER_SATISFACTION_SATISFIED,
		EZ_ORDER_SATISFACTION_DISSATISFIED,
		$product_id,
		EZ_ORDER_SATISFACTION_SATISFIED,
		EZ_ORDER_SATISFACTION_DISSATISFIED
	);

	$row = $wpdb->get_row( $sql, ARRAY_A );
	if ( ! $row ) {
		return null;
	}

	$sat   = (int) ( $row['sat'] ?? 0 );
	$diss  = (int) ( $row['dissat'] ?? 0 );
	$denom = $sat + $diss;
	if ( $denom < 1 ) {
		return null;
	}

	return round( 100.0 * $sat / $denom, 2 );
}
