<?php
/**
 * Backfill: heal paid orders stuck on wc-processing after booking closed.
 *
 * Usage (from WordPress root):
 *   php wp-content/themes/escapezoom-v2/bin/ez-heal-stuck-processing-orders.php
 *   php wp-content/themes/escapezoom-v2/bin/ez-heal-stuck-processing-orders.php --order-id=822910,822924
 *   php wp-content/themes/escapezoom-v2/bin/ez-heal-stuck-processing-orders.php --apply --limit=100
 *
 * Default is dry-run (no writes). Pass --apply to run ez_heal_post_booking_order_integrity.
 */

if ( php_sapi_name() !== 'cli' ) {
	exit( 'CLI only.' );
}

$apply      = in_array( '--apply', $argv, true );
$limit      = 100;
$order_ids  = array();

foreach ( $argv as $arg ) {
	if ( preg_match( '/^--limit=(\d+)$/', $arg, $m ) ) {
		$limit = max( 1, min( 500, (int) $m[1] ) );
	}
	if ( preg_match( '/^--order(?:-id)?=(.+)$/', $arg, $m ) ) {
		foreach ( preg_split( '/\s*,\s*/', (string) $m[1] ) as $part ) {
			$oid = (int) $part;
			if ( $oid > 0 ) {
				$order_ids[ $oid ] = true;
			}
		}
	}
}

$wp_load = dirname( __DIR__, 4 ) . '/wp-load.php';
if ( ! is_readable( $wp_load ) ) {
	fwrite( STDERR, "Cannot find wp-load.php at {$wp_load}\n" );
	exit( 1 );
}

require_once $wp_load;

if ( ! function_exists( 'ez_heal_post_booking_order_integrity' ) ) {
	fwrite( STDERR, "ez_heal_post_booking_order_integrity not available.\n" );
	exit( 1 );
}

global $wpdb;

if ( empty( $order_ids ) && $wpdb instanceof wpdb ) {
	$sql = $wpdb->prepare(
		"SELECT DISTINCT p.ID
		 FROM {$wpdb->posts} AS p
		 INNER JOIN {$wpdb->postmeta} AS pmd ON pmd.post_id = p.ID AND pmd.meta_key = %s
		   AND pmd.meta_value != '' AND pmd.meta_value != %s
		 WHERE p.post_type = %s
		   AND p.post_status = %s
		   AND (
		     EXISTS (
		       SELECT 1 FROM {$wpdb->postmeta} AS pmr
		       WHERE pmr.post_id = p.ID AND pmr.meta_key = %s AND pmr.meta_value = %s
		     )
		     OR EXISTS (
		       SELECT 1 FROM {$wpdb->prefix}zb_booking_history AS bh
		       WHERE bh.wc_order_id = p.ID
		     )
		   )
		 ORDER BY p.post_date ASC
		 LIMIT %d",
		'_date_paid',
		'0',
		'shop_order',
		'wc-processing',
		'booking_pipeline_state',
		'running',
		$limit
	);

	$found = $wpdb->get_col( $sql );
	foreach ( (array) $found as $pid ) {
		$oid = (int) $pid;
		if ( $oid > 0 ) {
			$order_ids[ $oid ] = true;
		}
	}
}

if ( empty( $order_ids ) ) {
	fwrite( STDOUT, "No stuck processing orders matched.\n" );
	exit( 0 );
}

/**
 * @param int $order_id Order ID.
 * @return array<string, string>
 */
function ez_heal_cli_snapshot( int $order_id ): array {
	$order = wc_get_order( $order_id );
	return array(
		'post_status' => $order instanceof WC_Order ? $order->get_status() : 'missing',
		'pipeline'    => (string) get_post_meta( $order_id, 'booking_pipeline_state', true ),
		'done_at'     => (string) get_post_meta( $order_id, 'booking_pipeline_done_at', true ),
		'total_2'     => (string) get_post_meta( $order_id, '_order_total_2', true ),
	);
}

fwrite( STDOUT, ( $apply ? '[APPLY] ' : '[DRY RUN] ' ) . count( $order_ids ) . " order(s)\n" );

$healed = 0;
foreach ( array_keys( $order_ids ) as $order_id ) {
	$before = ez_heal_cli_snapshot( (int) $order_id );
	fwrite( STDOUT, "order_id={$order_id} BEFORE status={$before['post_status']} pipeline={$before['pipeline']} done_at={$before['done_at']} _order_total_2={$before['total_2']}\n" );

	if ( ! $apply ) {
		fwrite( STDOUT, "  would call ez_heal_post_booking_order_integrity\n" );
		++$healed;
		continue;
	}

	try {
		$result = ez_heal_post_booking_order_integrity( (int) $order_id );
		clean_post_cache( (int) $order_id );
		$after = ez_heal_cli_snapshot( (int) $order_id );
		fwrite( STDOUT, '  heal=>' . ( $result ? 'acted' : 'no-op' ) . " AFTER status={$after['post_status']} pipeline={$after['pipeline']} done_at={$after['done_at']} _order_total_2={$after['total_2']}\n" );
		++$healed;
	} catch ( Throwable $e ) {
		fwrite( STDERR, "  ERROR: {$e->getMessage()}\n" );
	}
}

fwrite( STDOUT, "Done: {$healed}/" . count( $order_ids ) . " processed.\n" );
exit( 0 );
