<?php
/**
 * Template Name: aref-user
 *
 * لیست سانس‌های آینده در wp_zb_booking_history که برای wc_order_id مربوطه
 * در wp_markting ردیفی وجود ندارد (یتیم مارکتینگ).
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die(
		'دسترسی ندارید. فقط مدیرکل می‌تواند این صفحه را ببیند.',
		'Forbidden',
		[ 'response' => 403 ]
	);
}

if ( ! function_exists( 'medoo' ) || ! function_exists( 'medoo_queries' ) ) {
	wp_die(
		'تابع‌های medoo / medoo_queries بارگذاری نشده‌اند.',
		'Server Error',
		[ 'response' => 500 ]
	);
}

$queries = medoo_queries();
$crm     = medoo();

$tz     = new DateTimeZone( 'Asia/Tehran' );
$now_ts = time();

/**
 * سانس با booking_time بزرگتر از «الان» (سانس‌های آتی).
 * فقط wc_order_id معتبر.
 */
$futures = [];
try {
	$futures = $queries->select(
		'wp_zb_booking_history',
		[
			'booking_id',
			'wc_order_id',
			'room_id',
			'booking_time',
			'booked_time',
			'status',
			'customer_id',
			'name',
			'phone',
			'quantity',
		],
		[
			'AND'   => [
				'booking_time[>]'  => $now_ts,
				'wc_order_id[>]'   => 0,
			],
			'ORDER' => [ 'booking_time' => 'ASC' ],
			'LIMIT' => 5000,
		]
	);
} catch ( Throwable $e ) {
	$futures       = [];
	$query_error_msg = $e->getMessage();
}

$orphans = [];
if ( ! empty( $futures ) ) {
	foreach ( $futures as $row ) {
		$oid = isset( $row['wc_order_id'] ) ? (int) $row['wc_order_id'] : 0;
		if ( $oid <= 0 ) {
			continue;
		}
		try {
			$in_markting = $crm->has( 'wp_markting', [ 'order_id' => $oid ] );
		} catch ( Throwable $e ) {
			// در صورت خطای CRM، برای دیده شدن موضوع ردیف را لیست می‌کنیم + یادداشت خطا
			$in_markting                = false;
			$row['_markting_check_error'] = $e->getMessage();
		}
		if ( ! $in_markting ) {
			$orphans[] = $row;
		}
	}
}

get_header();

function ez_aref_user_format_ts( $ts, DateTimeZone $tz ) {
	if ( $ts === null || $ts === '' || ! is_numeric( $ts ) ) {
		return '—';
	}
	try {
		$d = new DateTime( '@' . (int) $ts );
		$d->setTimezone( $tz );

		return $d->format( 'Y-m-d H:i:s' );
	} catch ( Throwable $e ) {
		return '—';
	}
}
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl;">
	<h1 class="text-2xl font-bold mb-2">سانس‌های آیندهٔ بوکینگ بدون مارکتینگ</h1>
	<p class="text-gray-600 mb-6 text-sm leading-relaxed max-w-3xl">
		در <code dir="ltr" class="bg-gray-100 px-1 rounded">wp_zb_booking_history</code> رکوردهایی که
		<code dir="ltr" class="bg-gray-100 px-1">booking_time &gt; <?php echo (int) $now_ts; ?></code>
		(سانس بعد از الان) هستند و برای <code dir="ltr" class="bg-gray-100 px-1">wc_order_id</code> آن‌ها
		در <code dir="ltr" class="bg-gray-100 px-1">wp_markting</code> با همان <code dir="ltr" class="bg-gray-100 px-1">order_id</code> ردیفی نیست در جدول زیر است.
		سقف ۵۰۰۰ رکورد اخیر (مرتب بر زمان سانس).
	</p>

	<?php if ( isset( $query_error_msg ) ) : ?>
		<p class="text-red-600 mb-4">خطای کوئری بوکینگ: <?php echo esc_html( $query_error_msg ); ?></p>
	<?php endif; ?>

	<p class="mb-4">
		<strong>تعداد آتی در بوکینگ (بعد از فیلتر wc_order_id):</strong>
		<?php echo isset( $futures ) && is_array( $futures ) ? count( $futures ) : 0; ?>
		&nbsp;|&nbsp;
		<strong>بدون ردیف در مارکتینگ:</strong> <?php echo count( $orphans ); ?>
	</p>

	<?php if ( empty( $orphans ) ) : ?>
		<p class="text-green-700">موردی یافت نشد.</p>
	<?php else : ?>
		<div class="overflow-x-auto border border-gray-200 rounded-lg">
			<table class="min-w-full text-sm border-collapse">
				<thead>
					<tr class="bg-gray-100 border-b border-gray-200">
						<th class="text-right py-2 px-3 whitespace-nowrap">booking_id</th>
						<th class="text-right py-2 px-3 whitespace-nowrap">wc_order_id</th>
						<th class="text-right py-2 px-3 whitespace-nowrap">room_id</th>
						<th class="text-right py-2 px-3 whitespace-nowrap">booking_time (سانس)</th>
						<th class="text-right py-2 px-3 whitespace-nowrap">booked_time</th>
						<th class="text-right py-2 px-3">status</th>
						<th class="text-right py-2 px-3">نام / تماس</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $orphans as $r ) :
						$nm  = isset( $r['name'] ) ? (string) $r['name'] : '';
						$ph  = isset( $r['phone'] ) ? (string) $r['phone'] : '';
						$who = trim( $nm );
						if ( $ph !== '' ) {
							$who .= ( $who !== '' ? ' · ' : '' ) . $ph;
						}
						?>
						<tr class="border-b border-gray-100 hover:bg-gray-50">
							<td class="py-2 px-3 font-mono"><?php echo (int) ( $r['booking_id'] ?? 0 ); ?></td>
							<td class="py-2 px-3 font-mono">
								<a class="text-blue-600 underline" href="<?php echo esc_url( admin_url( 'post.php?post=' . (int) ( $r['wc_order_id'] ?? 0 ) . '&action=edit' ) ); ?>">
									<?php echo (int) ( $r['wc_order_id'] ?? 0 ); ?>
								</a>
							</td>
							<td class="py-2 px-3 font-mono"><?php echo (int) ( $r['room_id'] ?? 0 ); ?></td>
							<td class="py-2 px-3 font-mono" dir="ltr"><?php echo esc_html( ez_aref_user_format_ts( $r['booking_time'] ?? '', $tz ) ); ?></td>
							<td class="py-2 px-3 font-mono" dir="ltr"><?php echo esc_html( ez_aref_user_format_ts( $r['booked_time'] ?? '', $tz ) ); ?></td>
							<td class="py-2 px-3"><?php echo isset( $r['status'] ) ? esc_html( (string) $r['status'] ) : ''; ?></td>
							<td class="py-2 px-3"><?php echo esc_html( $who !== '' ? $who : '—' ); ?>
								<?php if ( ! empty( $r['_markting_check_error'] ) ) : ?>
									<br><span class="text-red-600 text-xs"><?php echo esc_html( $r['_markting_check_error'] ); ?></span>
								<?php endif; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
