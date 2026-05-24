<?php
/**
 * Backfill: wp_markting (paid) → wp_zb_booking_history + wp_markting sans update.
 * Reads sans_time from wp_postmeta for accuracy.
 * Run from WordPress root, e.g.:
 *   wp eval-file migrate-markting-backfill.php
 */

// true: فقط گزارش می‌دهد و تغییری در دیتابیس اعمال نمی‌کند
// false: تغییرات را در دیتابیس ذخیره می‌کند
$dry_run = false;

// اگر اسکریپت را مستقیماً با php اجرا می‌کنید، این خط را از کامنت خارج کرده و مسیر را تنظیم کنید
// require_once __DIR__ . '/wp-load.php';

// --- Bootstrap ---
if ( ! function_exists( 'medoo' ) || ! function_exists( 'medoo_queries' ) ) {
	fwrite( STDERR, "ERROR: Medoo functions not found. Please load theme's Medoo init or the main wp-load.php.\n" );
	exit( 1 );
}
if ( ! function_exists( 'wc_get_order' ) ) {
	fwrite( STDERR, "ERROR: WooCommerce is not loaded.\n" );
	exit( 1 );
}

$crm     = medoo();
$queries = medoo_queries();

$persian_days = [
	0 => 'یکشنبه',
	1 => 'دوشنبه',
	2 => 'سه‌شنبه',
	3 => 'چهارشنبه',
	4 => 'پنج‌شنبه',
	5 => 'جمعه',
	6 => 'شنبه',
];

// --- 1. منبع اصلی: دریافت سفارش‌های نیازمند اصلاح از wp_markting ---
$orders_to_fix = $crm->select( 'wp_markting', [
	'order_id',
	'customer_id',
], [
	'AND' => [
		'order_id[>=]'    => 814195,
		'order_status'    => [ 'wc-completed-paid', 'wc-partially-paid' ],
		// برای جلوگیری از پردازش مجدد، فقط آنهایی که زمان سانس ندارند را انتخاب می‌کنیم
		'order_sans_time' => null,
	],
	'ORDER' => [ 'order_id' => 'ASC' ],
] );

if ( empty( $orders_to_fix ) ) {
	echo "No orders to fix found.\n";
	exit( 0 );
}

echo 'Found ' . count( $orders_to_fix ) . " orders to process.\n---\n";

foreach ( $orders_to_fix as $markting_row ) {
	$order_id = (int) $markting_row['order_id'];
	echo "Processing Order ID: $order_id\n";

	// --- 2. واکشی زمان سانس از wp_postmeta ---
	$sans_ts = $crm->get( 'wp_postmeta', 'meta_value', [
		'post_id'  => $order_id,
		'meta_key' => 'sans_time',
	] );

	if ( empty( $sans_ts ) || ! is_numeric( $sans_ts ) ) {
		echo "  [skip] sans_time not found or invalid in wp_postmeta for order_id=$order_id.\n---\n";
		continue;
	}
	$sans_ts = (int) $sans_ts;

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		echo "  [skip] WC_Order object not found for order_id=$order_id.\n---\n";
		continue;
	}

	// --- 3. استخراج اطلاعات کامل از سفارش ---
	$customer_id = (int) $order->get_customer_id();
	if ( $customer_id === 0 ) {
		$customer_id = (int) $markting_row['customer_id'];
	}

	$product_id = null;
	$qty        = 1;
	foreach ( $order->get_items() as $item ) {
		if ( $item instanceof WC_Order_Item_Product ) {
			$product_id = (int) $item->get_product_id();
			$qty        = max( 1, (int) $item->get_quantity() );
			break; // فقط آیتم اول را در نظر می‌گیریم
		}
	}

	if ( ! $product_id ) {
		echo "  [skip] No product found in order_id=$order_id.\n---\n";
		continue;
	}

	$name  = trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() );
	$phone = $order->get_billing_phone();
	$level = ( $customer_id && function_exists( 'get_user_level' ) ) ? get_user_level( $customer_id ) : null;
	$booked_time = $order->get_date_created() ? $order->get_date_created()->getTimestamp() : time();

	// --- 4. افزودن رکورد به wp_zb_booking_history ---
	$booking_exists = $queries->has( 'wp_zb_booking_history', [ 'wc_order_id' => $order_id ] );

	if ( ! $booking_exists ) {
		$db_row = [
			'customer_id'  => $customer_id ?: null,
			'wc_order_id'  => $order_id,
			'status'       => 1,
			'room_id'      => $product_id,
			'booking_time' => $sans_ts,
			'booked_time'  => $booked_time,
			'name'         => $name !== '' ? $name : null,
			'phone'        => $phone !== '' ? $phone : null,
			'quantity'     => $qty,
		];
		if ( $level !== null && $level !== '' ) {
			$db_row['level'] = $level;
		}

		if ( $dry_run ) {
			echo "  [dry-run] INSERT booking: order=$order_id, room=$product_id, sans_ts=$sans_ts\n";
		} else {
			$result = $queries->insert( 'wp_zb_booking_history', $db_row );
			if ( $result ) {
				echo "  [ok] INSERT booking for order_id=$order_id.\n";
			} else {
				echo "  [fail] FAILED to insert booking for order_id=$order_id.\n";
			}
		}
	} else {
		echo "  [skip] Booking already exists for wc_order_id=$order_id.\n";
	}

	// --- 5. آپدیت جدول wp_markting ---
	date_default_timezone_set( 'Asia/Tehran' );
	$date = new DateTime( '@' . $sans_ts );
	$date->setTimezone( new DateTimeZone( 'Asia/Tehran' ) );

	$sans_update = [
		'order_sans_date' => $date->format( 'Y-m-d' ),
		'order_sans_time' => $date->format( 'H:i' ),
		'order_sans_day'  => $persian_days[ (int) $date->format( 'w' ) ] ?? null,
	];

	if ( $dry_run ) {
		echo "  [dry-run] UPDATE wp_markting: order_id=$order_id with sans data: " . json_encode( $sans_update, JSON_UNESCAPED_UNICODE ) . "\n";
	} else {
		$result = $crm->update( 'wp_markting', $sans_update, [ 'order_id' => $order_id ] );
		if ( $result->rowCount() > 0 ) {
			echo "  [ok] UPDATE markting sans for order_id=$order_id.\n";
		} else {
			echo "  [warn] Markting row for order_id=$order_id was not updated (maybe data is identical?).\n";
		}
	}
	echo "---\n";
}

echo "Done.\n";
