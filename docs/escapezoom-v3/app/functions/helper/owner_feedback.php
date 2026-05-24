<?php

if ( ! function_exists( 'ez_owner_feedback_load_medoo' ) ) {
	/**
	 * Ensure Medoo helpers exist (AJAX edge cases).
	 */
	function ez_owner_feedback_load_medoo(): void {
		if ( function_exists( 'medoo' ) && function_exists( 'medoo_queries' ) ) {
			return;
		}
		$init = ( defined( 'Theme_PATH' ) ? Theme_PATH : trailingslashit( get_template_directory() ) ) . 'inc/medoo/init.php';
		if ( is_readable( $init ) ) {
			require_once $init;
		}
	}
}

if ( ! function_exists( 'ez_owner_feedback_markting_duration_for_room' ) ) {
	/**
	 * Latest numeric game_duration from wp_markting for this room (Medoo primary DB).
	 */
	function ez_owner_feedback_markting_duration_for_room( int $room_id ): int {
		if ( $room_id <= 0 ) {
			return 0;
		}

		ez_owner_feedback_load_medoo();
		if ( ! function_exists( 'medoo' ) ) {
			return 0;
		}

		$m = medoo();
		if ( ! $m ) {
			return 0;
		}

		// Same semantics as former SQL: numeric game_duration, latest order_id.
		$rid = (int) $room_id;
		$statement = $m->query(
			"SELECT CAST(game_duration AS UNSIGNED) FROM wp_markting WHERE game_id = {$rid} AND game_duration REGEXP '^[0-9]+$' ORDER BY order_id DESC LIMIT 1"
		);

		if ( ! $statement ) {
			return 0;
		}

		$val = $statement->fetchColumn( 0 );

		return (int) $val;
	}
}

if ( ! function_exists( 'ez_owner_feedback_resolve_duration_minutes' ) ) {
	/**
	 * Single duration rule for owner-feedback list + submit:
	 * markting (game_duration for room) → room meta room_duration → products_data on medoo_queries.
	 *
	 * @param int      $room_id  WooCommerce product / room post ID.
	 * @param int|null $order_id Optional; reserved for per-order overrides (unused, kept for API stability).
	 * @return int|WP_Error
	 */
	function ez_owner_feedback_resolve_duration_minutes( int $room_id, ?int $order_id = null ) {
		if ( $room_id <= 0 ) {
			return new WP_Error( 'invalid_room', 'شناسه اتاق نامعتبر است.' );
		}

		$duration = ez_owner_feedback_markting_duration_for_room( $room_id );
		if ( $duration <= 0 ) {
			$duration = (int) get_post_meta( $room_id, 'room_duration', true );
		}
		if ( $duration <= 0 ) {
			ez_owner_feedback_load_medoo();
			if ( function_exists( 'medoo_queries' ) ) {
				$mdb = medoo_queries();
				if ( $mdb ) {
					$duration = (int) $mdb->get( 'products_data', 'duration', [ 'product_id' => $room_id ] );
				}
			}
		}

		if ( $duration <= 0 ) {
			return new WP_Error( 'invalid_duration', 'مدت سانس معتبر نیست.' );
		}

		return $duration;
	}
}

if ( ! function_exists( 'ez_owner_feedback_get_room_duration_minutes' ) ) {
	/**
	 * Backward-compatible alias for resolve_duration (room meta was secondary before; now uses unified chain).
	 *
	 * @return int|WP_Error
	 */
	function ez_owner_feedback_get_room_duration_minutes( int $room_id ) {
		return ez_owner_feedback_resolve_duration_minutes( $room_id, null );
	}
}

if ( ! function_exists( 'ez_owner_feedback_markting_start_unix_for_order' ) ) {
	/**
	 * Parse wp_markting order_sans_date + order_sans_time using site timezone (Medoo primary DB).
	 *
	 * @return int|null Unix timestamp or null if missing/invalid.
	 */
	function ez_owner_feedback_markting_start_unix_for_order( int $order_id ): ?int {
		if ( $order_id <= 0 ) {
			return null;
		}

		ez_owner_feedback_load_medoo();
		if ( ! function_exists( 'medoo' ) ) {
			return null;
		}

		$m = medoo();
		if ( ! $m ) {
			return null;
		}

		$row = $m->get(
			'wp_markting',
			[ 'order_sans_date', 'order_sans_time' ],
			[ 'order_id' => $order_id ]
		);

		if ( empty( $row['order_sans_date'] ) || empty( $row['order_sans_time'] ) ) {
			return null;
		}

		$composite = trim( (string) $row['order_sans_date'] ) . ' ' . trim( (string) $row['order_sans_time'] );

		try {
			$tz = wp_timezone();
			$dt = new DateTimeImmutable( $composite, $tz );

			return $dt->getTimestamp();
		} catch ( Exception $e ) {
			$ts = strtotime( $composite );

			return ( $ts > 0 ) ? $ts : null;
		}
	}
}

if ( ! function_exists( 'ez_owner_feedback_booking_time_from_db' ) ) {
	/**
	 * Booking rows live in medoo_queries DB (escapezo_queries), not WordPress $wpdb.
	 *
	 * @return int|null Unix timestamp or null.
	 */
	function ez_owner_feedback_booking_time_from_db( int $order_id, int $room_id ): ?int {
		ez_owner_feedback_load_medoo();
		if ( ! function_exists( 'medoo_queries' ) ) {
			return null;
		}

		$mdb = medoo_queries();
		if ( ! $mdb ) {
			return null;
		}

		$table = 'wp_zb_booking_history';

		if ( $room_id > 0 ) {
			$times = $mdb->select(
				$table,
				'booking_time',
				[
					'wc_order_id' => $order_id,
					'room_id'     => $room_id,
					'ORDER'       => [ 'booking_time' => 'DESC' ],
					'LIMIT'       => 1,
				]
			);
		} else {
			$times = $mdb->select(
				$table,
				'booking_time',
				[
					'wc_order_id' => $order_id,
					'ORDER'       => [ 'booking_time' => 'DESC' ],
					'LIMIT'       => 1,
				]
			);
		}

		if ( is_array( $times ) && isset( $times[0] ) ) {
			$t = (int) $times[0];

			return $t > 0 ? $t : null;
		}

		return null;
	}
}

if ( ! function_exists( 'ez_owner_feedback_resolve_session_start_ts' ) ) {
	/**
	 * Canonical session start for owner feedback (list + submit).
	 * Priority: non-zero booking_time_fallback (list row) → zb_booking_history → sans_time → wp_markting.
	 *
	 * @param int      $order_id             WC order id.
	 * @param int      $room_id              Room/product id (0 allowed; skips scoped booking lookup).
	 * @param int|null $booking_time_fallback Unix ts from booking row when already known.
	 * @return int|WP_Error
	 */
	function ez_owner_feedback_resolve_session_start_ts( int $order_id, int $room_id = 0, ?int $booking_time_fallback = null ) {
		if ( $order_id <= 0 ) {
			return new WP_Error( 'invalid_order', 'شناسه سفارش نامعتبر است.' );
		}

		if ( $booking_time_fallback !== null && $booking_time_fallback > 0 ) {
			return $booking_time_fallback;
		}

		$from_booking = ez_owner_feedback_booking_time_from_db( $order_id, $room_id );
		if ( $from_booking !== null ) {
			return $from_booking;
		}

		$sans_time = (int) get_post_meta( $order_id, 'sans_time', true );
		if ( $sans_time > 0 ) {
			return $sans_time;
		}

		$mk = ez_owner_feedback_markting_start_unix_for_order( $order_id );
		if ( $mk !== null && $mk > 0 ) {
			return $mk;
		}

		return new WP_Error( 'invalid_start_time', 'زمان شروع سانس یافت نشد.' );
	}
}

if ( ! function_exists( 'ez_owner_feedback_get_order_start_time' ) ) {
	/**
	 * Resolve order start unix timestamp (used by older callers).
	 *
	 * @return int|WP_Error
	 */
	function ez_owner_feedback_get_order_start_time( int $order_id, int $room_id = 0 ) {
		return ez_owner_feedback_resolve_session_start_ts( $order_id, $room_id, null );
	}
}

if ( ! function_exists( 'ez_owner_feedback_get_window_bounds' ) ) {
	/**
	 * Feedback window is from 30m after start until 30m after end.
	 *
	 * @return array{start:int,end:int}|WP_Error
	 */
	function ez_owner_feedback_get_window_bounds( int $order_id, int $room_id ) {
		if ( $order_id <= 0 || $room_id <= 0 ) {
			return new WP_Error( 'invalid_input', 'شناسه سفارش یا اتاق نامعتبر است.' );
		}

		$start_time = ez_owner_feedback_resolve_session_start_ts( $order_id, $room_id, null );
		if ( is_wp_error( $start_time ) ) {
			return $start_time;
		}

		$duration = ez_owner_feedback_resolve_duration_minutes( $room_id, $order_id );
		if ( is_wp_error( $duration ) ) {
			return $duration;
		}

		$start = (int) $start_time + 1800;
		$end   = (int) $start_time + ( (int) $duration * 60 ) + 1800;

		if ( $end < $start ) {
			return new WP_Error( 'invalid_window', 'بازه زمانی کامنت نامعتبر است.' );
		}

		return [
			'start' => $start,
			'end'   => $end,
		];
	}
}

if ( ! function_exists( 'ez_owner_feedback_is_within_window' ) ) {
	/**
	 * @return true|WP_Error
	 */
	function ez_owner_feedback_is_within_window( int $order_id, int $room_id, ?int $current_time = null ) {
		$bounds = ez_owner_feedback_get_window_bounds( $order_id, $room_id );
		if ( is_wp_error( $bounds ) ) {
			return $bounds;
		}

		$now = $current_time ?? time();
		if ( $now >= (int) $bounds['start'] && $now <= (int) $bounds['end'] ) {
			return true;
		}

		return new WP_Error( 'outside_window', 'پنجره زمانی ثبت کامنت فعال نیست.' );
	}
}
