<?php
/**
 * CLI: WordPress cron health for theme hooks.
 *
 * From WordPress root:
 *   php wp-content/themes/escapezoom-v2/bin/ez-cron-health.php
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

$theme_root = dirname( __DIR__ );
if ( is_readable( $theme_root . '/template/func/cron-health.php' ) ) {
	require_once $theme_root . '/template/func/cron-health.php';
}

$disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
fwrite( STDOUT, 'DISABLE_WP_CRON: ' . ( $disabled ? 'true (use system cron → wp-cron.php)' : 'false' ) . "\n\n" );

$fail = 0;
if ( ! function_exists( 'ez_cron_health_report' ) ) {
	fwrite( STDOUT, "ERROR: ez_cron_health_report() not loaded\n" );
	exit( 1 );
}

foreach ( ez_cron_health_report() as $row ) {
	$line = ( $row['ok'] ? 'OK' : 'FAIL' ) . ': ' . $row['hook'] . ' — ' . $row['message'];
	fwrite( STDOUT, $line . "\n" );
	if ( ! $row['ok'] ) {
		++$fail;
	}
}

$pending_sms = 0;
global $wpdb;
if ( $wpdb instanceof wpdb ) {
	$pending_sms = (int) $wpdb->get_var( 'SELECT COUNT(*) FROM `sms_sending_queue` WHERE sent_time IS NULL' );
}
fwrite( STDOUT, "\nsms_sending_queue pending (sent_time IS NULL): {$pending_sms}\n" );

$last_run   = (int) get_option( 'ez_reconcile_cron_last_run', 0 );
$last_batch = get_option( 'ez_reconcile_cron_last_batch', array() );
fwrite( STDOUT, 'ez_reconcile_cron_last_run: ' . ( $last_run ? gmdate( 'Y-m-d H:i:s', $last_run ) . ' UTC' : 'never' ) . "\n" );
if ( is_array( $last_batch ) && ! empty( $last_batch['ts'] ) ) {
	fwrite( STDOUT, 'ez_reconcile_cron_last_batch: processed=' . (int) ( $last_batch['processed'] ?? 0 )
		. ' candidates=' . (int) ( $last_batch['candidates'] ?? 0 ) . "\n" );
}

exit( $fail > 0 ? 1 : 0 );
