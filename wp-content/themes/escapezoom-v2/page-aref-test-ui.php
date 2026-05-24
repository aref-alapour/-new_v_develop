<?php
/**
 * Template Name: aref-test-5
 *
 * اکسل بازه‌ای: سفارش‌های ۷ روز اخیر — بازهٔ ردیف را خودت مشخص می‌کنی (بدون سقف ثابت).
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', [ 'response' => 403 ] );
}

global $wpdb;

const EZ_AREF5_DAYS = 7;

$order_statuses = array(
	'wc-partially-paid',
	'wc-completed-paid',
	'partially-paid',
	'completed-paid',
	'wc-walletx',
	'walletx',
	'wc-refunded',
	'refunded',
);

$tz          = new DateTimeZone( 'Asia/Tehran' );
$to_dt       = new DateTime( 'now', $tz );
$to_dt->setTime( 23, 59, 59 );
$from_dt     = clone $to_dt;
$from_dt->modify( '-' . EZ_AREF5_DAYS . ' days' );
$from_dt->setTime( 0, 0, 0 );
$from_mysql  = $from_dt->format( 'Y-m-d H:i:s' );
$to_mysql    = $to_dt->format( 'Y-m-d H:i:s' );
$range_label = $from_dt->format( 'Y-m-d H:i:s' ) . ' ←→ ' . $to_dt->format( 'Y-m-d H:i:s' ) . ' (Asia/Tehran)';

/**
 * @param wpdb   $wpdb
 * @param array  $statuses
 */
function ez_aref5_count_orders( $wpdb, string $from_mysql, string $to_mysql, array $statuses ): int {
	$markting            = 'wp_markting';
	$status_placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
	$sql                 = "SELECT COUNT(*) FROM {$markting} m
		WHERE m.order_status IN ({$status_placeholders})
		  AND m.order_created_at >= %s
		  AND m.order_created_at <= %s
		  AND m.customer_id > 0";
	$args                = array_merge( $statuses, array( $from_mysql, $to_mysql ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	return (int) $wpdb->get_var( $wpdb->prepare( $sql, $args ) );
}

/**
 * @param int $row_from ۱-based
 * @param int $row_to   ۱-based
 * @return array{offset:int, limit:int}
 */
function ez_aref5_parse_row_range( int $row_from, int $row_to, int $total_orders = 0 ): array {
	$row_from = max( 1, $row_from );
	$row_to   = max( $row_from, $row_to );
	if ( $total_orders > 0 ) {
		$row_from = min( $row_from, $total_orders );
		$row_to   = min( $row_to, $total_orders );
		$row_to   = max( $row_from, $row_to );
	}
	return array(
		'offset'   => $row_from - 1,
		'limit'    => $row_to - $row_from + 1,
		'row_from' => $row_from,
		'row_to'   => $row_to,
	);
}

/**
 * @return array<int, array<string, mixed>>
 */
function ez_aref5_fetch_hits_sql(
	$wpdb,
	string $from_mysql,
	string $to_mysql,
	array $statuses,
	int $offset,
	int $limit
): array {
	$markting = 'wp_markting';
	$wallet   = 'wallet_transactions';
	$postmeta = $wpdb->postmeta;

	$status_placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

	$sql = "
		SELECT
			o.order_id,
			o.customer_id AS user_id,
			o.customer_phone AS phone,
			o.order_created_at,
			CASE o.order_status
				WHEN 'wc-partially-paid' THEN 'پیش پرداخت'
				WHEN 'partially-paid'    THEN 'پیش پرداخت'
				WHEN 'wc-completed-paid' THEN 'پرداخت کامل'
				WHEN 'completed-paid'    THEN 'پرداخت کامل'
				WHEN 'wc-walletx'        THEN 'واریز به کیف پول'
				WHEN 'walletx'           THEN 'واریز به کیف پول'
				WHEN 'wc-refunded'       THEN 'مسترد شده'
				WHEN 'refunded'          THEN 'مسترد شده'
				ELSE o.order_status
			END AS order_status,
			o.online_paid,
			o.order_paid AS prepaid,
			wt.amount AS reserve_amount,
			wt.balance AS reserve_balance,
			o.game_name,
			'شارژ کیف پول ثبت نشده — موجودی بعد از رزرو منفی شد' AS reason
		FROM (
			SELECT
				m.order_id,
				m.customer_id,
				m.customer_phone,
				m.order_created_at,
				m.order_status,
				m.order_paid,
				m.game_name,
				CAST(
					COALESCE(
						NULLIF(CAST(m.order_online_paid AS UNSIGNED), 0),
						NULLIF(CAST(pm_snap.meta_value AS UNSIGNED), 0),
						NULLIF(CAST(pm_t2.meta_value AS UNSIGNED), 0),
						NULLIF(CAST(pm_tot.meta_value AS UNSIGNED), 0),
						0
					) AS UNSIGNED
				) AS online_paid
			FROM {$markting} m
			LEFT JOIN {$postmeta} pm_snap
				ON pm_snap.post_id = m.order_id
			   AND pm_snap.meta_key = '_ez_checkout_snapshot_online_payable'
			LEFT JOIN {$postmeta} pm_t2
				ON pm_t2.post_id = m.order_id
			   AND pm_t2.meta_key = '_order_total_2'
			LEFT JOIN {$postmeta} pm_tot
				ON pm_tot.post_id = m.order_id
			   AND pm_tot.meta_key = '_order_total'
			WHERE m.order_status IN ({$status_placeholders})
			  AND m.order_created_at >= %s
			  AND m.order_created_at <= %s
			  AND m.customer_id > 0
			ORDER BY m.order_created_at DESC, m.order_id DESC
			LIMIT %d OFFSET %d
		) AS o
		INNER JOIN {$wallet} wt
			ON wt.ID = (
				SELECT wt2.ID
				FROM {$wallet} wt2
				WHERE wt2.user_id = o.customer_id
				  AND wt2.amount < 0
				  AND wt2.balance < 0
				  AND wt2.description LIKE '%رزرو بازی%'
				  AND wt2.description LIKE CONCAT('%%', o.order_id, '%%')
				ORDER BY wt2.ID DESC
				LIMIT 1
			)
		WHERE o.online_paid > 0
		  AND NOT EXISTS (
				SELECT 1
				FROM {$wallet} ch
				WHERE ch.user_id = o.customer_id
				  AND (
						ch.unique_description = CONCAT('player_charge:', o.order_id, ':', o.online_paid)
					 OR (
							ch.amount = o.online_paid
						AND ch.description LIKE '%شارژ کیف پول%'
						AND ch.description LIKE CONCAT('%%', o.order_id, '%%')
					 )
				  )
		  )
		ORDER BY o.order_created_at DESC
	";

	$args = array_merge( $statuses, array( $from_mysql, $to_mysql, $limit, $offset ) );
	// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$prepared = $wpdb->prepare( $sql, $args );
	$rows     = $wpdb->get_results( $prepared, ARRAY_A );

	if ( ! empty( $wpdb->last_error ) ) {
		throw new RuntimeException( $wpdb->last_error );
	}

	return is_array( $rows ) ? $rows : array();
}

/**
 * @param array<int, array<string, mixed>> $rows
 */
function ez_aref5_stream_csv(
	array $rows,
	int $row_from,
	int $row_to,
	int $total_orders,
	int $hit_count
): void {
	$filename = sprintf(
		'wallet_no_charge_rows_%d-%d_of_%d_%s.csv',
		$row_from,
		$row_to,
		$total_orders,
		wp_date( 'Y-m-d', null, new DateTimeZone( 'Asia/Tehran' ) )
	);

	while ( ob_get_level() ) {
		ob_end_clean();
	}

	header( 'Content-Type: text/csv; charset=UTF-8' );
	header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	header( 'Pragma: no-cache' );
	header( 'Expires: 0' );

	echo "\xEF\xBB\xBF";

	$out = fopen( 'php://output', 'w' );
	fputcsv( $out, array( 'بازه بررسی‌شده (ردیف)', $row_from . ' تا ' . $row_to, 'از کل سفارش', $total_orders ) );
	fputcsv( $out, array( 'تعداد مشکل در این بازه', $hit_count ) );
	fputcsv( $out, array() );
	fputcsv(
		$out,
		array(
			'شماره سفارش',
			'شناسه کاربر',
			'موبایل',
			'تاریخ ثبت سفارش',
			'وضعیت سفارش',
			'مبلغ آنلاین (شارژ نشده)',
			'پیش پرداخت',
			'مبلغ رزرو',
			'balance بعد از رزرو',
			'بازی',
			'توضیح',
		)
	);

	foreach ( $rows as $r ) {
		fputcsv(
			$out,
			array(
				$r['order_id'] ?? '',
				$r['user_id'] ?? '',
				$r['phone'] ?? '',
				$r['order_created_at'] ?? '',
				$r['order_status'] ?? '',
				$r['online_paid'] ?? '',
				$r['prepaid'] ?? '',
				$r['reserve_amount'] ?? '',
				$r['reserve_balance'] ?? '',
				$r['game_name'] ?? '',
				$r['reason'] ?? '',
			)
		);
	}
	fclose( $out );
	exit;
}

$page_url     = get_permalink() ?: home_url( '/aref-test-5/' );
$nonce        = wp_create_nonce( 'ez_aref_test5_export' );
$total_orders = 0;
$count_error  = '';

try {
	$total_orders = ez_aref5_count_orders( $wpdb, $from_mysql, $to_mysql, $order_statuses );
} catch ( Throwable $e ) {
	$count_error = $e->getMessage();
}

$row_from = isset( $_REQUEST['row_from'] ) ? max( 1, (int) $_REQUEST['row_from'] ) : 1;
$row_to   = isset( $_REQUEST['row_to'] ) ? max( 1, (int) $_REQUEST['row_to'] ) : min( 100, max( 1, $total_orders ) );
$parsed   = ez_aref5_parse_row_range( $row_from, $row_to, $total_orders );
$row_from = $parsed['row_from'];
$row_to   = $parsed['row_to'];

// --- دانلود اکسل این بازه ---
if ( isset( $_GET['export'] ) && $_GET['export'] === '1' ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ez_aref_test5_export' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', [ 'response' => 403 ] );
	}

	$row_from = isset( $_GET['row_from'] ) ? max( 1, (int) $_GET['row_from'] ) : 1;
	$row_to   = isset( $_GET['row_to'] ) ? max( 1, (int) $_GET['row_to'] ) : $row_from;
	$parsed   = ez_aref5_parse_row_range( $row_from, $row_to, $total_orders );
	$row_from = $parsed['row_from'];
	$row_to   = $parsed['row_to'];

	if ( $total_orders > 0 && $row_from > $total_orders ) {
		wp_die( 'ردیف شروع بیشتر از تعداد کل سفارش‌هاست.', 'خطا', array( 'response' => 400 ) );
	}

	$span = (int) $parsed['limit'];
	@set_time_limit( max( 60, min( 600, 30 + (int) ceil( $span * 0.4 ) ) ) );

	try {
		$rows = ez_aref5_fetch_hits_sql(
			$wpdb,
			$from_mysql,
			$to_mysql,
			$order_statuses,
			$parsed['offset'],
			$parsed['limit']
		);
		ez_aref5_stream_csv( $rows, $row_from, $row_to, $total_orders, count( $rows ) );
	} catch ( Throwable $e ) {
		wp_die( 'خطا: ' . esc_html( $e->getMessage() ), 'خطا', array( 'response' => 500 ) );
	}
}

$next_from = $row_to + 1;
$next_to   = min( $next_from + 99, max( $total_orders, $next_from ) );

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 760px;">
	<h1 class="text-2xl font-bold mb-2">گزارش کیف پول — شارژ ثبت‌نشده (بازه‌ای)</h1>
	<p class="text-gray-600 mb-2 text-sm">
		بازه را خودت مشخص کن: <strong>از ردیف</strong> تا <strong>تا ردیف</strong>
		(مثلاً ۱ تا ۳۰۰ یا ۲۰۱ تا ۵۰۰). سقف ثابت ۱۰۰تایی ندارد.
	</p>
	<p class="text-xs text-gray-500 mb-4 font-mono" dir="ltr"><?php echo esc_html( $range_label ); ?></p>

	<?php if ( $count_error !== '' ) : ?>
		<p class="text-red-600 text-sm">خطا در شمارش: <?php echo esc_html( $count_error ); ?></p>
	<?php else : ?>
		<p class="text-sm mb-4 p-3 bg-gray-50 border rounded">
			<strong>کل سفارش‌های بازه:</strong>
			<span dir="ltr"><?php echo (int) $total_orders; ?></span>
			<span class="text-gray-500 text-xs block mt-1">
				مرتب‌سازی: جدیدترین <code>order_created_at</code> اول — ردیف ۱ = جدیدترین سفارش
			</span>
		</p>
	<?php endif; ?>

	<form method="get" class="mb-4 p-4 border rounded-lg bg-white text-sm space-y-3">
		<input type="hidden" name="export" value="1">
		<input type="hidden" name="_wpnonce" value="<?php echo esc_attr( $nonce ); ?>">

		<div class="flex flex-wrap gap-4 items-end">
			<label>
				از ردیف
				<input type="number" name="row_from" min="1" max="<?php echo max( 1, (int) $total_orders ); ?>"
					value="<?php echo (int) $row_from; ?>"
					class="border rounded px-2 py-1 w-24 mr-1" dir="ltr" required>
			</label>
			<label>
				تا ردیف
				<input type="number" name="row_to" min="1" max="<?php echo max( 1, (int) $total_orders ); ?>"
					value="<?php echo (int) $row_to; ?>"
					class="border rounded px-2 py-1 w-24 mr-1" dir="ltr" required>
			</label>
			<button type="submit" class="px-5 py-2 bg-green-700 text-white rounded-lg font-bold hover:bg-green-800">
				دانلود اکسل این بازه
			</button>
		</div>

		<p class="text-xs text-amber-800">
			مثال: ۱ تا ۳۰۰، یا ۱۰۱ تا ۴۰۰.
			اگر بازه خیلی بزرگ باشد ممکن است زمان اجرا طول بکشد یا 502 بدهد — در آن صورت بازه را کوچک‌تر کن.
		</p>
	</form>

	<?php if ( $total_orders > 0 && $next_from <= $total_orders ) : ?>
		<p class="text-sm text-gray-600">
			بازهٔ پیشنهادی بعدی:
			<a class="text-blue-600 underline font-mono" dir="ltr"
				href="<?php echo esc_url(
					add_query_arg(
						array(
							'export'   => '1',
							'row_from' => $next_from,
							'row_to'   => $next_to,
							'_wpnonce' => $nonce,
						),
						$page_url
					)
				); ?>">
				<?php echo (int) $next_from; ?> – <?php echo (int) $next_to; ?>
			</a>
		</p>
	<?php endif; ?>

	<p class="text-xs text-gray-500 mt-4">
		فقط وضعیت: پیش‌پرداخت، پرداخت کامل، واریز به کیف پول، مسترد.
		فایل CSV در Excel باز می‌شود؛ اگر مشکلی نبود، فایل فقط سطر عنوان دارد.
	</p>
</main>

<?php
get_footer();
