<?php
/**
 * One-off backfill for legacy wp_checkout_intent rows (state = converted).
 *
 * Run ONLY while the CRM table still has `state` and `wc_order_id` columns.
 * After migrating to the slim schema (escapezoom_ddl_manual_2026.sql section 4), this script no longer applies.
 *
 * Usage (from WP root):
 *   php wp-content/themes/escapezoom-v2/bin/migrate-checkout-intent-converted-backfill.php
 *
 * Dry run (no writes): set env EZ_CHECKOUT_INTENT_BACKFILL_DRY_RUN=1
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

$dry_run = ( getenv( 'EZ_CHECKOUT_INTENT_BACKFILL_DRY_RUN' ) === '1' || getenv( 'EZ_CHECKOUT_INTENT_BACKFILL_DRY_RUN' ) === 'true' );

if ( ! function_exists( 'medoo' ) || ! function_exists( 'medoo_queries' ) ) {
	fwrite( STDERR, "medoo / medoo_queries not available.\n" );
	exit( 1 );
}

if ( ! function_exists( 'wc_get_order' ) ) {
	fwrite( STDERR, "WooCommerce not loaded.\n" );
	exit( 1 );
}

$crm = medoo();
$mq  = medoo_queries();
$t   = function_exists( 'ez_checkout_intent_table' ) ? ez_checkout_intent_table() : 'wp_checkout_intent';

$legacy_ok = false;
	try {
		$st = $crm->query( 'SHOW COLUMNS FROM `' . $t . "` LIKE 'state'" );
	if ( $st && $st->fetch() ) {
		$legacy_ok = true;
	}
} catch ( Throwable $e ) {
	fwrite( STDERR, 'DDL check failed: ' . $e->getMessage() . "\n" );
	exit( 1 );
}

if ( ! $legacy_ok ) {
	echo "No `state` column on {$t} — table already migrated. Nothing to backfill.\n";
	exit( 0 );
}

$persian_days = array(
	0 => 'یکشنبه',
	1 => 'دوشنبه',
	2 => 'سه‌شنبه',
	3 => 'چهارشنبه',
	4 => 'پنج‌شنبه',
	5 => 'جمعه',
	6 => 'شنبه',
);

$intents = $crm->select(
	$t,
	array(
		'id',
		'intent_token',
		'user_id',
		'product_id',
		'sans_ts',
		'qty',
		'state',
		'wc_order_id',
	),
	array(
		'AND' => array(
			'state'          => 'converted',
			'wc_order_id[!]' => null,
			'wc_order_id[>]' => 0,
		),
	)
);

if ( empty( $intents ) ) {
	echo "No converted intents.\n";
	exit( 0 );
}

foreach ( $intents as $row ) {
	$order_id   = (int) $row['wc_order_id'];
	$product_id = (int) $row['product_id'];
	$sans_ts    = (int) $row['sans_ts'];
	$qty_row    = isset( $row['qty'] ) ? (int) $row['qty'] : 1;

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		echo "[skip] order {$order_id} not found\n";
		continue;
	}

	$wp_uid     = (int) $order->get_customer_id();
	$intent_uid = isset( $row['user_id'] ) ? (int) $row['user_id'] : 0;
	$customer_id = $wp_uid > 0 ? $wp_uid : $intent_uid;

	$markting = $crm->get( 'wp_markting', '*', array( 'order_id' => $order_id ) );

	$qty = $qty_row > 0 ? $qty_row : 1;
	foreach ( $order->get_items() as $item ) {
		if ( $item instanceof WC_Order_Item_Product ) {
			$qty = max( 1, (int) $item->get_quantity() );
			break;
		}
	}

	$name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$phone = $order->get_billing_phone();
	$level = null;
	if ( $customer_id && function_exists( 'get_user_level' ) ) {
		$lvl = get_user_level( $customer_id );
		$level = ( $lvl !== null && $lvl !== '' ) ? $lvl : null;
	}

	$booked_time = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : time();

	$booking_exists = $mq->has( 'wp_zb_booking_history', array( 'wc_order_id' => $order_id ) );

	if ( ! $booking_exists ) {
		$db_row = array(
			'customer_id'  => $customer_id ?: null,
			'wc_order_id'  => $order_id,
			'status'       => 1,
			'room_id'      => $product_id,
			'booking_time' => $sans_ts,
			'booked_time'  => $booked_time,
			'name'         => $name !== '' ? $name : null,
			'phone'        => $phone !== '' ? $phone : null,
			'quantity'     => $qty,
		);
		if ( $level !== null ) {
			$db_row['level'] = $level;
		}
		if ( $dry_run ) {
			echo "[dry-run] INSERT booking order={$order_id} room={$product_id} sans={$sans_ts}\n";
		} else {
			try {
				$mq->insert( 'wp_zb_booking_history', $db_row );
			} catch ( Throwable $e ) {
				unset( $db_row['level'] );
				try {
					$mq->insert( 'wp_zb_booking_history', $db_row );
				} catch ( Throwable $e2 ) {
					echo "[err] order={$order_id} " . $e2->getMessage() . "\n";
				}
			}
			echo "[ok] INSERT booking order={$order_id}\n";
		}
	} else {
		echo "[skip] booking exists wc_order_id={$order_id}\n";
	}

	if ( ! empty( $markting ) ) {
		date_default_timezone_set( 'Asia/Tehran' );
		$date = new DateTime();
		$date->setTimestamp( $sans_ts );
		$sans_update = array(
			'order_sans_date' => $date->format( 'Y-m-d' ),
			'order_sans_time' => $date->format( 'H:i' ),
			'order_sans_day'  => isset( $persian_days[ (int) $date->format( 'w' ) ] ) ? $persian_days[ (int) $date->format( 'w' ) ] : null,
		);
		if ( $dry_run ) {
			echo "[dry-run] UPDATE wp_markting order_id={$order_id} sans " . wp_json_encode( $sans_update, JSON_UNESCAPED_UNICODE ) . "\n";
		} else {
			$crm->update( 'wp_markting', $sans_update, array( 'order_id' => $order_id ) );
			echo "[ok] UPDATE markting sans order_id={$order_id}\n";
		}
	}
}

echo "Done." . ( $dry_run ? ' (dry run)' : '' ) . "\n";
