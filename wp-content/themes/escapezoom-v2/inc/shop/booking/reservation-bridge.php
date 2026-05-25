<?php
/**
 * Local replacements for hot-path ez_reservation() types (no HTTP loopback).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Try fast local handlers before internal dispatch / HTTP.
 *
 * @param array<string,mixed>|object $data Payload.
 * @return string|null Response body or null to fall through.
 */
function ez_reservation_try_shortcut( $data ) {
	$payload = function_exists( 'ez_reservation_normalize_data' )
		? ez_reservation_normalize_data( $data )
		: (object) ( is_array( $data ) ? $data : array() );

	if ( empty( $payload->type ) ) {
		return null;
	}

	switch ( $payload->type ) {
		case 'get_sans_lock':
			return ez_reservation_json_get_sans_lock( $payload->data );

		case 'add_sans_lock':
			return ez_reservation_json_add_sans_lock( $payload->data );

		case 'remove_sans_lock':
			return ez_reservation_json_remove_sans_lock( $payload->data );

		case 'query_execution':
			return ez_booking_query_execution_local( $payload->data );

		case 'get_pending_sanses':
			return ez_booking_get_pending_sanses_local( $payload->data );
	}

	return null;
}

/**
 * @param object|array<string,mixed> $args Inner data.
 */
function ez_reservation_json_get_sans_lock( $args ): string {
	$args       = (object) ( is_array( $args ) ? $args : (array) $args );
	$product_id = isset( $args->product_id ) ? (int) $args->product_id : 0;
	if ( $product_id <= 0 || ! function_exists( 'ez_get_booking_lock' ) ) {
		return wp_json_encode( array() );
	}

	$rows = ez_get_booking_lock( $product_id );
	$out  = array();
	if ( is_array( $rows ) ) {
		foreach ( $rows as $row ) {
			$out[] = array(
				'product_id'   => (int) ( $row->product_id ?? $product_id ),
				'booking_time' => (int) ( $row->booking_time ?? 0 ),
				'lock_time'    => (int) ( $row->lock_time ?? 0 ),
			);
		}
	}

	return wp_json_encode( $out );
}

/**
 * @param object|array<string,mixed> $args Inner data.
 */
function ez_reservation_json_add_sans_lock( $args ): string {
	$args         = (object) ( is_array( $args ) ? $args : (array) $args );
	$product_id   = isset( $args->product_id ) ? (int) $args->product_id : 0;
	$booking_time = isset( $args->booking_time ) ? (int) $args->booking_time : 0;
	if ( $product_id > 0 && $booking_time > 0 && function_exists( 'ez_add_booking_lock' ) ) {
		ez_add_booking_lock( $product_id, $booking_time );
	}

	return wp_json_encode( true );
}

/**
 * @param object|array<string,mixed> $args Inner data.
 */
function ez_reservation_json_remove_sans_lock( $args ): string {
	$args         = (object) ( is_array( $args ) ? $args : (array) $args );
	$product_id   = isset( $args->product_id ) ? (int) $args->product_id : 0;
	$booking_time = isset( $args->booking_time ) ? (int) $args->booking_time : 0;
	if ( $product_id > 0 && $booking_time > 0 && function_exists( 'ez_remove_booking_lock' ) ) {
		ez_remove_booking_lock( $product_id, $booking_time );
	}

	return wp_json_encode( true );
}

/**
 * @param object|array<string,mixed> $args query + single_value.
 */
function ez_booking_query_execution_local( $args ) {
	$args         = (object) ( is_array( $args ) ? $args : (array) $args );
	$query        = isset( $args->query ) ? trim( (string) $args->query ) : '';
	$single_value = ! empty( $args->single_value );

	if ( '' === $query || ! preg_match( '/^\s*(SELECT|select)\s+/i', $query ) ) {
		return wp_json_encode( $single_value ? null : array() );
	}

	if ( ! preg_match( '/\bwp_zb_booking_history\b/i', $query ) ) {
		return null;
	}

	$rows = array();
	try {
		if ( function_exists( 'medoo_queries' ) ) {
			$mq = medoo_queries();
			if ( $mq && method_exists( $mq, 'query' ) ) {
				$stmt = $mq->query( $query );
				if ( $stmt ) {
					$rows = $stmt->fetchAll( PDO::FETCH_ASSOC );
				}
			}
		}
	} catch ( Throwable $e ) {
		error_log( '[ez_booking_query_execution_local] ' . $e->getMessage() );
	}

	if ( empty( $rows ) ) {
		return wp_json_encode( $single_value ? null : array() );
	}

	$payload = $single_value ? ( $rows[0] ?? null ) : $rows;

	return wp_json_encode( $payload );
}

/**
 * @param object|array<string,mixed> $args Inner data.
 */
function ez_booking_get_pending_sanses_local( $args ): string {
	$args       = (object) ( is_array( $args ) ? $args : (array) $args );
	$product_id = isset( $args->product_id ) ? (int) $args->product_id : 0;
	$sanses     = array();

	if ( $product_id <= 0 || ! function_exists( 'medoo_queries' ) ) {
		return wp_json_encode( $sanses );
	}

	try {
		$mq   = medoo_queries();
		$now  = time();
		$rows = $mq->select(
			'wp_zb_booking_history',
			array(
				'customer_id',
				'wc_order_id',
				'room_id',
				'booking_time',
			),
			array(
				'status'       => 1,
				'booking_time[>]' => $now,
				'room_id'      => $product_id,
			)
		);
		foreach ( (array) $rows as $row ) {
			$sanses[] = array(
				'user_id'    => (int) ( $row['customer_id'] ?? 0 ),
				'order_id'   => (int) ( $row['wc_order_id'] ?? 0 ),
				'product_id' => (int) ( $row['room_id'] ?? 0 ),
				'sans_time'  => (int) ( $row['booking_time'] ?? 0 ),
			);
		}
	} catch ( Throwable $e ) {
		error_log( '[ez_booking_get_pending_sanses_local] ' . $e->getMessage() );
	}

	return wp_json_encode( $sanses );
}

/**
 * Active lock timestamps for a product (checkout / API).
 *
 * @return int[]
 */
function ez_booking_locked_timestamps( int $product_id ): array {
	$product_id = (int) $product_id;
	if ( $product_id <= 0 ) {
		return array();
	}
	$raw = ez_reservation_json_get_sans_lock( (object) array( 'product_id' => $product_id ) );
	$decoded = json_decode( $raw, true );
	if ( ! is_array( $decoded ) ) {
		return array();
	}
	$out = array();
	foreach ( $decoded as $row ) {
		if ( isset( $row['booking_time'] ) ) {
			$out[] = (int) $row['booking_time'];
		}
	}

	return $out;
}
