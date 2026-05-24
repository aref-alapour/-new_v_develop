<?php
/**
 * One-off backfill: run ez_reconcile_single_order_wp_markting_wc_booking for stuck orders.
 *
 * Targets orders older than the reconcile cron window (default: >60 min) or explicit IDs.
 *
 * Usage (from WordPress root):
 *   php wp-content/themes/escapezoom-v2/bin/reconcile-stuck-orders-backfill.php
 *   php wp-content/themes/escapezoom-v2/bin/reconcile-stuck-orders-backfill.php --order=12345
 *   php wp-content/themes/escapezoom-v2/bin/reconcile-stuck-orders-backfill.php --min-age-minutes=60 --limit=50
 *
 * Dry run (no reconcile calls): EZ_RECONCILE_BACKFILL_DRY_RUN=1
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$order_id        = 0;
$min_age_minutes = 60;
$max_age_hours   = 168;
$limit           = 50;

foreach ( $argv as $arg ) {
	if ( preg_match( '/^--order=(\d+)$/', $arg, $m ) ) {
		$order_id = (int) $m[1];
	}
	if ( preg_match( '/^--min-age-minutes=(\d+)$/', $arg, $m ) ) {
		$min_age_minutes = max( 0, (int) $m[1] );
	}
	if ( preg_match( '/^--max-age-hours=(\d+)$/', $arg, $m ) ) {
		$max_age_hours = max( 1, (int) $m[1] );
	}
	if ( preg_match( '/^--limit=(\d+)$/', $arg, $m ) ) {
		$limit = max( 1, min( 500, (int) $m[1] ) );
	}
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Cannot find wp-load.php at {$wp_load}\n" );
	exit( 1 );
}

require_once $wp_load;

if ( ! function_exists( 'medoo' ) || ! function_exists( 'ez_reconcile_single_order_wp_markting_wc_booking' ) ) {
	fwrite( STDERR, "medoo or ez_reconcile_single_order_wp_markting_wc_booking not available.\n" );
	exit( 1 );
}

$dry_run = in_array( getenv( 'EZ_RECONCILE_BACKFILL_DRY_RUN' ), array( '1', 'true' ), true );

$order_ids = array();
if ( $order_id > 0 ) {
	$order_ids[] = $order_id;
} else {
	$medoo = medoo();
	if ( ! $medoo ) {
		fwrite( STDERR, "medoo() failed.\n" );
		exit( 1 );
	}

	$ref_ts = (int) current_time( 'timestamp' );
	$from   = wp_date( 'Y-m-d H:i:s', $ref_ts - ( $max_age_hours * HOUR_IN_SECONDS ) );
	$to     = wp_date( 'Y-m-d H:i:s', $ref_ts - ( $min_age_minutes * MINUTE_IN_SECONDS ) );

	$rows = $medoo->select(
		'wp_markting',
		array( 'order_id' ),
		array(
			'order_status'         => array( 'wc-pending', 'wc-on-hold', 'wc-cancelled' ),
			'order_created_at[>=]' => $from,
			'order_created_at[<=]' => $to,
			'ORDER'                => array( 'order_created_at' => 'ASC' ),
			'LIMIT'                => $limit,
		)
	);

	foreach ( (array) $rows as $row ) {
		$oid = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
		if ( $oid > 0 ) {
			$order_ids[] = $oid;
		}
	}
}

if ( empty( $order_ids ) ) {
	fwrite( STDOUT, "No orders matched (min_age={$min_age_minutes}m, limit={$limit}).\n" );
	exit( 0 );
}

fwrite( STDOUT, ( $dry_run ? '[DRY RUN] ' : '' ) . 'Reconciling ' . count( $order_ids ) . " order(s)...\n" );

$ok = 0;
foreach ( $order_ids as $oid ) {
	if ( $dry_run ) {
		fwrite( STDOUT, "  would reconcile order_id={$oid}\n" );
		++$ok;
		continue;
	}
	try {
		$result = ez_reconcile_single_order_wp_markting_wc_booking( (int) $oid );
		fwrite( STDOUT, '  order_id=' . (int) $oid . ' => ' . ( $result ? 'ok' : 'no-op' ) . "\n" );
		++$ok;
	} catch ( Throwable $e ) {
		fwrite( STDERR, '  order_id=' . (int) $oid . ' ERROR: ' . $e->getMessage() . "\n" );
	}
}

fwrite( STDOUT, "Done: {$ok}/" . count( $order_ids ) . " processed.\n" );
exit( 0 );
