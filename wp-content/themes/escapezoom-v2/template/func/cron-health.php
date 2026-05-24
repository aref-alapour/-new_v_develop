<?php
/**
 * CLI + shared helpers for theme cron health checks.
 */

if ( ! defined( 'ABSPATH' ) && php_sapi_name() !== 'cli' ) {
	exit;
}

/**
 * @return array<int, array{hook:string, ok:bool, message:string}>
 */
function ez_cron_health_report(): array {
	$registry  = function_exists( 'ez_cron_registry' ) ? ez_cron_registry() : array();
	$max_age   = function_exists( 'ez_cron_heartbeat_max_age' ) ? ez_cron_heartbeat_max_age() : array();
	$heartbeats = (array) get_option( 'ez_cron_heartbeats', array() );
	$now       = time();
	$report    = array();

	foreach ( $registry as $hook => $job ) {
		if ( empty( $job['enabled'] ) ) {
			$report[] = array(
				'hook'    => $hook,
				'ok'      => true,
				'message' => 'skipped (callback missing)',
			);
			continue;
		}

		$next = wp_next_scheduled( $hook );
		if ( ! $next ) {
			$report[] = array(
				'hook'    => $hook,
				'ok'      => false,
				'message' => 'MISSING schedule',
			);
			continue;
		}

		$event      = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( $hook ) : null;
		$schedule   = $event && isset( $event->schedule ) ? (string) $event->schedule : '?';
		$expected   = (string) ( $job['recurrence'] ?? '' );
		$overdue    = $next < $now;
		$bad_recurrence = $schedule !== $expected;
		$event_count = function_exists( 'ez_cron_count_events' ) ? ez_cron_count_events( $hook ) : 1;

		$hb_ts    = (int) ( $heartbeats[ $hook ]['ts'] ?? 0 );
		$hb_age   = $hb_ts > 0 ? ( $now - $hb_ts ) : null;
		$hb_max   = (int) ( $max_age[ $hook ] ?? 0 );
		$stale_hb = false;
		if ( $overdue ) {
			$stale_hb = true;
		} elseif ( $hb_max > 0 && $hb_ts > 0 && $hb_age > $hb_max ) {
			$stale_hb = true;
		}

		$messages = array();
		if ( function_exists( 'ez_cron_is_hook_paused' ) && ez_cron_is_hook_paused( $hook ) ) {
			$messages[] = 'PAUSED in WP Crontrol (resume hook to run callbacks)';
		}
		$callback_name = isset( $job['callback'] ) ? $job['callback'] : '';
		if ( is_string( $callback_name ) && $callback_name !== '' && ! has_action( $hook, $callback_name ) ) {
			$has_hook = has_action( $hook );
			$messages[] = 'callback not on hook'
				. ( false === $has_hook ? ' (no listeners)' : ' (has_action=' . (int) $has_hook . ' but not this callback)' );
		}
		if ( $bad_recurrence ) {
			$messages[] = "wrong schedule={$schedule} expected={$expected}";
		}
		if ( $event_count > 1 ) {
			$messages[] = "duplicate events={$event_count}";
		}
		if ( $stale_hb ) {
			if ( $overdue ) {
				$messages[] = 'OVERDUE next=' . gmdate( 'Y-m-d H:i:s', (int) $next ) . ' UTC';
			} elseif ( $hb_age !== null ) {
				$messages[] = 'stale heartbeat age=' . $hb_age . 's max=' . $hb_max;
			}
		} elseif ( $overdue ) {
			$messages[] = 'OVERDUE next=' . gmdate( 'Y-m-d H:i:s', (int) $next ) . ' UTC';
		}

		$ok = empty( $messages );
		if ( $ok ) {
			$messages[] = 'OK next=' . gmdate( 'Y-m-d H:i:s', (int) $next ) . ' UTC';
			if ( $hb_ts ) {
				$messages[] = 'heartbeat=' . gmdate( 'Y-m-d H:i:s', $hb_ts ) . ' UTC';
			}
		}

		$report[] = array(
			'hook'    => $hook,
			'ok'      => $ok,
			'message' => implode( '; ', $messages ),
		);
	}

	return $report;
}
