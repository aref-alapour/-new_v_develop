<?php
/**
 * EscapeZoom theme crons — single registry (no HTTP_HOST gate).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom cron intervals used by theme jobs.
 *
 * @return array<string, array{interval:int, display:string}>
 */
function ez_cron_schedules(): array {
	return array(
		'every_one_minute'    => array(
			'interval' => 60,
			'display'  => 'هر یک دقیقه',
		),
		'every_two_minutes'   => array(
			'interval' => 120,
			'display'  => 'Every 2 Minutes',
		),
		'every_three_minutes' => array(
			'interval' => 180,
			'display'  => 'Every 3 Minutes',
		),
		'every_30_minutes'    => array(
			'interval' => 1800,
			'display'  => 'هر 30 دقیقه',
		),
	);
}

add_filter(
	'cron_schedules',
	static function ( array $schedules ): array {
		foreach ( ez_cron_schedules() as $key => $def ) {
			if ( ! isset( $schedules[ $key ] ) ) {
				$schedules[ $key ] = $def;
			}
		}
		return $schedules;
	}
);

/**
 * Deprecated / orphan hooks to remove during one-time cleanup.
 *
 * @return string[]
 */
function ez_cron_orphan_hooks(): array {
	return array(
		'ez_zarinpal_smart_verify_cron',
		'ez_products_order_sequence_cron',
		'ez_satisfaction_on_comments_cron',
	);
}

/**
 * Max seconds since last heartbeat before health check fails.
 *
 * @return array<string, int>
 */
function ez_cron_heartbeat_max_age(): array {
	return array(
		'ez_sms_sending_queue_cron'                  => 300,
		'zarinpal_paid_transactions_process_cron'    => 240,
		'zarinpal_co_paid_transactions_process_cron' => 240,
		'zibal_unverified_orders_process_cron'       => 240,
		'ez_wp_markting_wc_reconcile_booking_cron'   => 180,
		'ez_fast_paid_missing_booking_cron'          => 240,
		'check_wallet_orders_cron'                   => 2100,
		'comment_reminder_sms_process_cron'          => 4200,
		'ez_remove_expired_sms_queue_cron'           => 90000,
		'ez_queryable_set_hottest_cron'              => 4200,
		'ez_queryable_set_popular_cron'              => 4200,
		'ez_queryable_set_topsale_cron'              => 4200,
		'ez_queryable_set_recent_cron'               => 4200,
		'ez_queryable_set_data_cron'                 => 4200,
		'ez_queryable_set_data_nactive_cron'         => 4200,
		'update_comments_stars_cron'                 => 90000,
		'wp_zb_booking_history_today_optimize_cron'  => 90000,
	);
}

/**
 * @return array<string, array{recurrence:string, callback:callable-string|null, enabled:bool}>
 */
function ez_cron_registry(): array {
	$jobs = array(
		'ez_sms_sending_queue_cron'                  => array(
			'recurrence' => 'every_three_minutes',
			'callback'   => 'ez_sms_sending_queue_schedule',
			'enabled'    => function_exists( 'ez_sms_sending_queue_schedule' ),
		),
		'zarinpal_paid_transactions_process_cron'    => array(
			'recurrence' => 'every_two_minutes',
			'callback'   => 'zarinpal_paid_transactions_process',
			'enabled'    => function_exists( 'zarinpal_paid_transactions_process' ),
		),
		'zarinpal_co_paid_transactions_process_cron' => array(
			'recurrence' => 'every_two_minutes',
			'callback'   => 'zarinpal_co_paid_transactions_process',
			'enabled'    => function_exists( 'zarinpal_co_paid_transactions_process' ),
		),
		'zibal_unverified_orders_process_cron'       => array(
			'recurrence' => 'every_two_minutes',
			'callback'   => 'zibal_unverified_orders_process',
			'enabled'    => function_exists( 'zibal_unverified_orders_process' ),
		),
		'ez_wp_markting_wc_reconcile_booking_cron'   => array(
			'recurrence' => 'every_one_minute',
			'callback'   => 'ez_wp_markting_wc_reconcile_booking_cron_handler',
			'enabled'    => function_exists( 'ez_wp_markting_wc_reconcile_booking_cron_handler' ),
		),
		'ez_fast_paid_missing_booking_cron'          => array(
			'recurrence' => 'every_two_minutes',
			'callback'   => 'ez_fast_paid_missing_booking_cron_handler',
			'enabled'    => function_exists( 'ez_fast_paid_missing_booking_cron_handler' ),
		),
		'check_wallet_orders_cron'                   => array(
			'recurrence' => 'every_30_minutes',
			'callback'   => 'check_wallet_orders',
			'enabled'    => function_exists( 'check_wallet_orders' ),
		),
		'comment_reminder_sms_process_cron'          => array(
			'recurrence' => 'hourly',
			'callback'   => 'comment_reminder_sms_process',
			'enabled'    => function_exists( 'comment_reminder_sms_process' ),
		),
		'ez_remove_expired_sms_queue_cron'           => array(
			'recurrence' => 'twicedaily',
			'callback'   => 'ez_remove_expired_sms_queue_schedule',
			'enabled'    => function_exists( 'ez_remove_expired_sms_queue_schedule' ),
		),
		'ez_queryable_set_hottest_cron'              => array(
			'recurrence' => 'hourly',
			'callback'   => 'ez_queryable_set_hottest_products',
			'enabled'    => function_exists( 'ez_queryable_set_hottest_products' ),
		),
		'ez_queryable_set_popular_cron'              => array(
			'recurrence' => 'hourly',
			'callback'   => 'ez_queryable_set_popular_products',
			'enabled'    => function_exists( 'ez_queryable_set_popular_products' ),
		),
		'ez_queryable_set_topsale_cron'              => array(
			'recurrence' => 'hourly',
			'callback'   => 'ez_queryable_set_topsale_products',
			'enabled'    => function_exists( 'ez_queryable_set_topsale_products' ),
		),
		'ez_queryable_set_recent_cron'               => array(
			'recurrence' => 'hourly',
			'callback'   => 'ez_queryable_set_recent_products',
			'enabled'    => function_exists( 'ez_queryable_set_recent_products' ),
		),
		'ez_queryable_set_data_cron'                 => array(
			'recurrence' => 'hourly',
			'callback'   => 'ez_queryable_set_products_data',
			'enabled'    => function_exists( 'ez_queryable_set_products_data' ),
		),
		'ez_queryable_set_data_nactive_cron'         => array(
			'recurrence' => 'hourly',
			'callback'   => 'ez_queryable_set_products_data_nactive',
			'enabled'    => function_exists( 'ez_queryable_set_products_data_nactive' ),
		),
		'update_comments_stars_cron'                 => array(
			'recurrence' => 'twicedaily',
			'callback'   => 'update_comments_stars',
			'enabled'    => function_exists( 'update_comments_stars' ),
		),
		'wp_zb_booking_history_today_optimize_cron'  => array(
			'recurrence' => 'daily',
			'callback'   => 'wp_zb_booking_history_today_optimize',
			'enabled'    => function_exists( 'wp_zb_booking_history_today_optimize' ),
		),
	);

	return apply_filters( 'ez_cron_registry', $jobs );
}

/**
 * @return string[]
 */
function ez_cron_theme_hook_names(): array {
	return array_keys( ez_cron_registry() );
}

/**
 * Count scheduled events for a hook in the cron array.
 */
function ez_cron_count_events( string $hook ): int {
	if ( ! function_exists( '_get_cron_array' ) ) {
		return 0;
	}

	$count = 0;
	$cron  = _get_cron_array();
	if ( ! is_array( $cron ) ) {
		return 0;
	}

	foreach ( $cron as $hooks ) {
		if ( isset( $hooks[ $hook ] ) && is_array( $hooks[ $hook ] ) ) {
			$count += count( $hooks[ $hook ] );
		}
	}

	return $count;
}

/**
 * @return array{hook:string, events_before:int, events_after:int, action:string}[]
 */
function ez_cron_run_one_time_cleanup(): array {
	$log = array();

	foreach ( ez_cron_orphan_hooks() as $hook ) {
		$before = ez_cron_count_events( $hook );
		wp_clear_scheduled_hook( $hook );
		$log[] = array(
			'hook'           => $hook,
			'events_before'  => $before,
			'events_after'   => ez_cron_count_events( $hook ),
			'action'         => 'cleared orphan',
		);
	}

	foreach ( ez_cron_theme_hook_names() as $hook ) {
		$before = ez_cron_count_events( $hook );
		wp_clear_scheduled_hook( $hook );
		$log[] = array(
			'hook'           => $hook,
			'events_before'  => $before,
			'events_after'   => ez_cron_count_events( $hook ),
			'action'         => 'cleared for reschedule',
		);
	}

	delete_option( 'ez_order_satisfaction_cron_cleared_v1' );
	delete_option( 'ez_cron_registry_bootstrapped' );

	// Re-register immediately on this request.
	ez_cron_bootstrap_jobs();

	return $log;
}

/**
 * Whether WP Crontrol has paused this hook (pauser removes all callbacks).
 */
function ez_cron_is_hook_paused( string $hook ): bool {
	$paused = get_option( 'wp_crontrol_paused', array() );
	return is_array( $paused ) && array_key_exists( $hook, $paused );
}

/**
 * @return array<string, array{status:string, ms:int}>
 */
function &ez_cron_last_run_bucket(): array {
	static $store = array();
	return $store;
}

/**
 * Record run outcome after the main callback (priority 10).
 */
function ez_cron_set_last_run( string $hook, string $status, int $ms ): void {
	$store         = &ez_cron_last_run_bucket();
	$store[ $hook ] = array(
		'status' => $status,
		'ms'     => $ms,
	);
}

/**
 * @return array{status:string, ms:int}|null
 */
function ez_cron_get_last_run( string $hook ): ?array {
	$store = ez_cron_last_run_bucket();
	return isset( $store[ $hook ] ) && is_array( $store[ $hook ] ) ? $store[ $hook ] : null;
}

/**
 * Heartbeat only — runs after the job callback at priority 20.
 *
 * @return callable
 */
function ez_cron_heartbeat_listener( string $hook ) {
	return static function () use ( $hook ) {
		$run        = ez_cron_get_last_run( $hook );
		$heartbeats = (array) get_option( 'ez_cron_heartbeats', array() );
		$heartbeats[ $hook ] = array(
			'ts'     => time(),
			'status' => $run['status'] ?? 'ok',
			'ms'     => (int) ( $run['ms'] ?? 0 ),
		);
		update_option( 'ez_cron_heartbeats', $heartbeats, false );
	};
}

/**
 * Wrap callback to capture errors for heartbeat (used when not a plain function name).
 *
 * @param callable-string|callable $callback
 * @return callable
 */
function ez_cron_wrapped_job( string $hook, $callback ) {
	return static function () use ( $hook, $callback ) {
		$started = microtime( true );
		$status  = 'ok';

		try {
			call_user_func( $callback );
		} catch ( Throwable $e ) {
			$status = 'error: ' . $e->getMessage();
			if ( function_exists( 'saeed_store' ) ) {
				saeed_store( '[ez_cron] ' . $hook . ' error: ' . $e->getMessage() );
			}
		}

		ez_cron_set_last_run(
			$hook,
			$status,
			(int) round( ( microtime( true ) - $started ) * 1000 )
		);
	};
}

/**
 * Register hook → callback once per request (direct call, like legacy saeed-codes).
 *
 * @param callable-string|callable $callback
 */
function ez_cron_register_hook_once( string $hook, $callback ): void {
	static $registered = array();

	if ( isset( $registered[ $hook ] ) ) {
		return;
	}
	$registered[ $hook ] = true;

	// Plain function names: register directly so has_action( $hook, $callback ) works in health checks.
	if ( is_string( $callback ) && is_callable( $callback ) ) {
		add_action( $hook, $callback, 10, 0 );
		add_action(
			$hook,
			static function () use ( $hook ) {
				ez_cron_set_last_run( $hook, 'ok', 0 );
			},
			19,
			0
		);
	} else {
		add_action( $hook, ez_cron_wrapped_job( $hook, $callback ), 10, 0 );
	}

	add_action( $hook, ez_cron_heartbeat_listener( $hook ), 20, 0 );
}

/**
 * Ensure a recurring event exists with the expected schedule (no per-request clear when OK).
 */
function ez_ensure_recurring_schedule( string $hook, string $recurrence ): void {
	$event = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( $hook ) : null;

	if ( ! $event && ! wp_next_scheduled( $hook ) ) {
		wp_schedule_event( time(), $recurrence, $hook );
		return;
	}

	if ( $event && (string) $event->schedule !== $recurrence ) {
		wp_clear_scheduled_hook( $hook );
		wp_schedule_event( time(), $recurrence, $hook );
	}
}

/**
 * Register action and ensure exactly one event with the expected recurrence.
 *
 * @param callable-string|callable $callback
 */
function ez_ensure_recurring_cron( string $hook, string $recurrence, $callback ): void {
	ez_cron_register_hook_once( $hook, $callback );
	ez_ensure_recurring_schedule( $hook, $recurrence );
}

/**
 * Legacy wrapper — prefer ez_cron_register_hook_once + heartbeat listener.
 *
 * @param callable-string|callable $callback
 * @return callable
 */
function ez_cron_tick( string $hook, $callback ) {
	return static function () use ( $hook, $callback ) {
		$started = microtime( true );
		$status  = 'ok';

		try {
			call_user_func( $callback );
		} catch ( Throwable $e ) {
			$status = 'error: ' . $e->getMessage();
			if ( function_exists( 'saeed_store' ) ) {
				saeed_store( '[ez_cron] ' . $hook . ' error: ' . $e->getMessage() );
			}
		}

		$heartbeats          = (array) get_option( 'ez_cron_heartbeats', array() );
		$heartbeats[ $hook ] = array(
			'ts'     => time(),
			'status' => $status,
			'ms'     => (int) round( ( microtime( true ) - $started ) * 1000 ),
		);
		update_option( 'ez_cron_heartbeats', $heartbeats, false );
	};
}

/**
 * Register all enabled jobs from the registry.
 */
function ez_cron_bootstrap_jobs(): void {
	foreach ( ez_cron_registry() as $hook => $job ) {
		if ( empty( $job['enabled'] ) || empty( $job['callback'] ) || ! is_callable( $job['callback'] ) ) {
			continue;
		}
		ez_ensure_recurring_cron( $hook, $job['recurrence'], $job['callback'] );
	}
}

add_action( 'init', 'ez_cron_bootstrap_jobs', 20 );

require_once __DIR__ . '/cron-health.php';
