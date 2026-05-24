<?php
/**
 * CLI: report WooCommerce Action Scheduler custom tables and schema options.
 *
 * From WordPress root:
 *   php wp-content/themes/escapezoom-v2/bin/verify-action-scheduler-health.php
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

$suffixes = array(
	'actionscheduler_actions',
	'actionscheduler_claims',
	'actionscheduler_groups',
	'actionscheduler_logs',
);

fwrite( STDOUT, 'DB: ' . DB_NAME . "\ntable_prefix: {$wpdb->prefix}\n\n" );

$all_ok = true;
foreach ( $suffixes as $s ) {
	$full = $wpdb->prefix . $s;
	$row  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $full ) ) );
	if ( $row === $full ) {
		fwrite( STDOUT, "OK table exists: {$full}\n" );
	} else {
		fwrite( STDOUT, "MISSING table: {$full}\n" );
		$all_ok = false;
	}
}

$store_opt = get_option( 'schema-ActionScheduler_StoreSchema', '' );
$log_opt   = get_option( 'schema-ActionScheduler_LoggerSchema', '' );
fwrite( STDOUT, "\noption schema-ActionScheduler_StoreSchema = {$store_opt}\n" );
fwrite( STDOUT, "option schema-ActionScheduler_LoggerSchema = {$log_opt}\n" );

$actions_table = $wpdb->prefix . 'actionscheduler_actions';
if ( ! $all_ok ) {
	fwrite( STDOUT, "\nSkipping action count (missing tables).\n" );
	exit( 1 );
}

// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- table name from trusted prefix.
$pending_count = (int) $wpdb->get_var(
	$wpdb->prepare(
		"SELECT COUNT(*) FROM `{$actions_table}` WHERE hook = %s AND status IN ('pending','in-progress')",
		'thankyou_background_process'
	)
);

if ( $wpdb->last_error !== '' ) {
	fwrite( STDOUT, "\ncount thankyou_background_process query error: {$wpdb->last_error}\n" );
	exit( 2 );
}

fwrite( STDOUT, "\npending/in-progress thankyou_background_process actions: {$pending_count}\n" );

exit( 0 );
