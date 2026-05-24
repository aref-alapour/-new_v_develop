<?php
/**
 * Cancel or delete stale Action Scheduler jobs for thankyou_background_process.
 *
 * From WordPress root:
 *   php wp-content/themes/escapezoom-v2/bin/cleanup-thankyou-background-as.php
 *   php wp-content/themes/escapezoom-v2/bin/cleanup-thankyou-background-as.php --execute
 *
 * Default is dry-run (lists IDs only).
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Cannot find wp-load.php at {$wp_load}\n" );
	exit( 1 );
}

require_once $wp_load;

global $wpdb;

$execute = in_array( '--execute', $argv, true );
$actions_table = $wpdb->prefix . 'actionscheduler_actions';

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$rows = $wpdb->get_results(
	$wpdb->prepare(
		"SELECT action_id, status, scheduled_date_gmt FROM `{$actions_table}` WHERE hook = %s AND status IN ('pending','in-progress') ORDER BY action_id ASC",
		'thankyou_background_process'
	),
	ARRAY_A
);

if ( $wpdb->last_error !== '' ) {
	fwrite( STDERR, "Query error: {$wpdb->last_error}\n" );
	exit( 2 );
}

$count = is_array( $rows ) ? count( $rows ) : 0;
fwrite( STDOUT, "pending/in-progress thankyou_background_process: {$count}\n" );

if ( $count === 0 ) {
	exit( 0 );
}

foreach ( $rows as $row ) {
	fwrite(
		STDOUT,
		sprintf(
			"  action_id=%s status=%s scheduled=%s\n",
			(string) ( $row['action_id'] ?? '' ),
			(string) ( $row['status'] ?? '' ),
			(string) ( $row['scheduled_date_gmt'] ?? '' )
		)
	);
}

if ( ! $execute ) {
	fwrite( STDOUT, "\nDry run. Re-run with --execute to cancel these actions.\n" );
	exit( 0 );
}

if ( ! function_exists( 'as_unschedule_action' ) && ! class_exists( 'ActionScheduler' ) ) {
	fwrite( STDERR, "Action Scheduler not available.\n" );
	exit( 3 );
}

$cancelled = 0;
foreach ( $rows as $row ) {
	$action_id = (int) ( $row['action_id'] ?? 0 );
	if ( $action_id <= 0 ) {
		continue;
	}
	if ( class_exists( 'ActionScheduler' ) ) {
		try {
			ActionScheduler::store()->cancel_action( $action_id );
			++$cancelled;
		} catch ( Throwable $e ) {
			fwrite( STDERR, "cancel failed action_id={$action_id}: " . $e->getMessage() . "\n" );
		}
	}
}

fwrite( STDOUT, "\nCancelled {$cancelled} action(s).\n" );

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
$remaining = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM `{$actions_table}` WHERE hook = %s AND status IN ('pending','in-progress')",
		'thankyou_background_process'
	)
);
fwrite( STDOUT, "Remaining pending/in-progress: {$remaining}\n" );

exit( $remaining > 0 ? 4 : 0 );
