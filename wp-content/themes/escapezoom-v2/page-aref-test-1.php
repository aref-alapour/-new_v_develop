<?php
/**
 * Template Name: aref-test-1
 *
 * حذف تدریجی سفارش‌های WooCommerce قدیمی‌تر از یک ماه (بر اساس post_date در wp_posts).
 * جداول: wp_postmeta، wp_woocommerce_order_items، wp_woocommerce_order_itemmeta، wp_posts
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die(
		'دسترسی ندارید. فقط مدیرکل می‌تواند این صفحه را ببیند.',
		'Forbidden',
		[ 'response' => 403 ]
	);
}

if ( ! function_exists( 'medoo' ) ) {
	wp_die(
		'تابع medoo بارگذاری نشده است.',
		'Server Error',
		[ 'response' => 500 ]
	);
}

$medoo = medoo();

/**
 * @return array{ok:bool,message:string,counts:array<string,int>}
 */
function ez_aref_test1_delete_order( $medoo, $order_id, $dry_run = false ) {
	$order_id = (int) $order_id;
	$counts   = [
		'itemmeta' => 0,
		'items'    => 0,
		'postmeta' => 0,
		'posts'    => 0,
	];

	if ( $order_id <= 0 ) {
		return [ 'ok' => false, 'message' => 'شناسه سفارش نامعتبر است.', 'counts' => $counts ];
	}

	$post = $medoo->get(
		'wp_posts',
		[ 'ID', 'post_type', 'post_date', 'post_status' ],
		[
			'ID'        => $order_id,
			'post_type' => 'shop_order',
		]
	);

	if ( empty( $post ) ) {
		return [ 'ok' => false, 'message' => "سفارش {$order_id} در wp_posts یافت نشد.", 'counts' => $counts ];
	}

	$item_ids = $medoo->select( 'wp_woocommerce_order_items', 'order_item_id', [ 'order_id' => $order_id ] );
	$item_ids = array_values( array_filter( array_map( 'intval', (array) $item_ids ) ) );

	if ( $dry_run ) {
		if ( ! empty( $item_ids ) ) {
			$counts['itemmeta'] = (int) $medoo->count( 'wp_woocommerce_order_itemmeta', [ 'order_item_id' => $item_ids ] );
		}
		$counts['items']    = count( $item_ids );
		$counts['postmeta'] = (int) $medoo->count( 'wp_postmeta', [ 'post_id' => $order_id ] );
		$counts['posts']    = 1;

		return [
			'ok'      => true,
			'message' => "[dry-run] سفارش {$order_id} — حذف شبیه‌سازی شد.",
			'counts'  => $counts,
		];
	}

	try {
		$medoo->action(
			function ( $db ) use ( $order_id, $item_ids, &$counts ) {
				if ( ! empty( $item_ids ) ) {
					$stmt = $db->delete( 'wp_woocommerce_order_itemmeta', [ 'order_item_id' => $item_ids ] );
					$counts['itemmeta'] = $stmt ? $stmt->rowCount() : 0;
				}
				$stmt = $db->delete( 'wp_woocommerce_order_items', [ 'order_id' => $order_id ] );
				$counts['items'] = $stmt ? $stmt->rowCount() : 0;

				$stmt = $db->delete( 'wp_postmeta', [ 'post_id' => $order_id ] );
				$counts['postmeta'] = $stmt ? $stmt->rowCount() : 0;

				$stmt = $db->delete(
					'wp_posts',
					[
						'ID'        => $order_id,
						'post_type' => 'shop_order',
					]
				);
				$counts['posts'] = $stmt ? $stmt->rowCount() : 0;
			}
		);
	} catch ( Throwable $e ) {
		return [
			'ok'      => false,
			'message' => "خطا در حذف سفارش {$order_id}: " . $e->getMessage(),
			'counts'  => $counts,
		];
	}

	if ( (int) $counts['posts'] < 1 ) {
		return [
			'ok'      => false,
			'message' => "ردیف wp_posts برای سفارش {$order_id} حذف نشد.",
			'counts'  => $counts,
		];
	}

	return [
		'ok'      => true,
		'message' => "سفارش {$order_id} حذف شد.",
		'counts'  => $counts,
	];
}

$tz           = new DateTimeZone( 'Asia/Tehran' );
$cutoff_dt    = new DateTime( 'now', $tz );
$cutoff_dt->modify( '-1 month' );
$cutoff_mysql = $cutoff_dt->format( 'Y-m-d H:i:s' );

$batch_size = isset( $_GET['batch'] ) ? max( 1, min( 100, (int) $_GET['batch'] ) ) : 20;
$dry_run    = isset( $_GET['dry_run'] ) && $_GET['dry_run'] === '1';
$do_step    = isset( $_GET['step'] ) && $_GET['step'] === '1';

$base_args = [
	'batch'   => $batch_size,
	'dry_run' => $dry_run ? '1' : '0',
];
$page_url  = get_permalink();
if ( ! $page_url ) {
	$page_url = home_url( '/aref-test-1/' );
}

$count_where = [
	'AND' => [
		'post_type'    => 'shop_order',
		'post_date[<]' => $cutoff_mysql,
	],
];

$total_remaining = 0;
$count_error     = '';
try {
	$total_remaining = (int) $medoo->count( 'wp_posts', $count_where );
} catch ( Throwable $e ) {
	$count_error = $e->getMessage();
}

$step_log     = [];
$step_deleted = 0;
$step_errors  = 0;
$step_totals  = [ 'itemmeta' => 0, 'items' => 0, 'postmeta' => 0, 'posts' => 0 ];

if ( $do_step && $count_error === '' ) {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'ez_aref_test1_purge' ) ) {
		wp_die( 'Nonce نامعتبر است.', 'Forbidden', [ 'response' => 403 ] );
	}

	try {
		$orders = $medoo->select(
			'wp_posts',
			[ 'ID', 'post_date', 'post_status' ],
			array_merge(
				$count_where,
				[
					'ORDER' => [ 'post_date' => 'ASC' ],
					'LIMIT' => $batch_size,
				]
			)
		);
	} catch ( Throwable $e ) {
		$step_log[] = [ 'ok' => false, 'message' => 'خطای انتخاب سفارش: ' . $e->getMessage(), 'counts' => [] ];
		$orders     = [];
	}

	foreach ( (array) $orders as $row ) {
		$oid    = (int) ( $row['ID'] ?? 0 );
		$result = ez_aref_test1_delete_order( $medoo, $oid, $dry_run );
		$step_log[] = array_merge(
			$result,
			[
				'order_id'    => $oid,
				'post_date'   => $row['post_date'] ?? '',
				'post_status' => $row['post_status'] ?? '',
			]
		);
		if ( $result['ok'] ) {
			++$step_deleted;
			foreach ( $step_totals as $k => $v ) {
				$step_totals[ $k ] += (int) ( $result['counts'][ $k ] ?? 0 );
			}
		} else {
			++$step_errors;
		}
	}

	try {
		$total_remaining = (int) $medoo->count( 'wp_posts', $count_where );
	} catch ( Throwable $e ) {
		$count_error = $e->getMessage();
	}
}

$step_url = add_query_arg(
	array_merge(
		$base_args,
		[
			'step'     => '1',
			'_wpnonce' => wp_create_nonce( 'ez_aref_test1_purge' ),
		]
	),
	$page_url
);

$auto_continue = ! $dry_run && $total_remaining > 0 && $step_errors === 0 && $do_step;

get_header();
?>

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 960px;">
	<h1 class="text-2xl font-bold mb-2">پاک‌سازی سفارش‌های قدیمی (بیش از یک ماه)</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		سفارش‌های <code dir="ltr" class="bg-gray-100 px-1 rounded">shop_order</code> که
		<code dir="ltr" class="bg-gray-100 px-1">post_date</code> آن‌ها قبل از
		<strong dir="ltr"><?php echo esc_html( $cutoff_mysql ); ?></strong>
		(تهران، یک ماه قبل از الان) است، به‌صورت دسته‌ای حذف می‌شوند.
		برای هر سفارش: <code dir="ltr">wp_woocommerce_order_itemmeta</code> →
		<code dir="ltr">wp_woocommerce_order_items</code> →
		<code dir="ltr">wp_postmeta</code> →
		<code dir="ltr">wp_posts</code>.
	</p>

	<div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded text-sm text-amber-900">
		<strong>توجه:</strong> جداول دیگر مثل <code dir="ltr">wp_markting</code>،
		<code dir="ltr">wp_zb_booking_history</code> یا <code dir="ltr">wp_wc_orders</code>
		در این اسکریپت پاک نمی‌شوند. قبل از اجرای واقعی حتماً بکاپ بگیرید.
	</div>

	<?php if ( $count_error !== '' ) : ?>
		<p class="text-red-600 mb-4">خطای شمارش: <?php echo esc_html( $count_error ); ?></p>
	<?php else : ?>
		<ul class="mb-4 text-sm space-y-1">
			<li><strong>باقی‌مانده برای حذف:</strong> <?php echo (int) $total_remaining; ?></li>
			<li><strong>اندازه هر مرحله:</strong> <?php echo (int) $batch_size; ?></li>
			<li><strong>حالت:</strong> <?php echo $dry_run ? 'فقط نمایش (dry-run)' : 'حذف واقعی'; ?></li>
		</ul>
	<?php endif; ?>

	<form method="get" class="mb-6 flex flex-wrap gap-3 items-end text-sm">
		<label>
			تعداد در هر مرحله
			<input type="number" name="batch" min="1" max="100" value="<?php echo (int) $batch_size; ?>"
				class="border rounded px-2 py-1 w-20 mr-1" dir="ltr">
		</label>
		<label class="flex items-center gap-1">
			<input type="checkbox" name="dry_run" value="1" <?php checked( $dry_run ); ?>>
			فقط dry-run (بدون حذف)
		</label>
		<button type="submit" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">بروزرسانی تنظیمات</button>
	</form>

	<?php if ( $count_error === '' && $total_remaining > 0 ) : ?>
		<p class="mb-4">
			<a href="<?php echo esc_url( $step_url ); ?>"
				class="inline-block px-5 py-2 rounded font-medium <?php echo $dry_run ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-red-600 text-white hover:bg-red-700'; ?>"
				<?php echo $dry_run ? '' : 'onclick="return confirm(\'حذف واقعی ' . (int) $batch_size . ' سفارش قدیمی؟ این عمل برگشت‌ناپذیر است.\');"'; ?>>
				اجرای یک مرحله (<?php echo (int) $batch_size; ?> سفارش)
			</a>
		</p>
	<?php elseif ( $count_error === '' ) : ?>
		<p class="text-green-700 font-medium">سفارش واجد شرایطی باقی نمانده است.</p>
	<?php endif; ?>

	<?php if ( $do_step && ! empty( $step_log ) ) : ?>
		<h2 class="text-lg font-semibold mt-6 mb-2">گزارش آخرین مرحله</h2>
		<p class="text-sm mb-2">
			موفق: <?php echo (int) $step_deleted; ?>
			| خطا: <?php echo (int) $step_errors; ?>
			| جمع ردیف‌های حذف‌شده:
			itemmeta=<?php echo (int) $step_totals['itemmeta']; ?>,
			items=<?php echo (int) $step_totals['items']; ?>,
			postmeta=<?php echo (int) $step_totals['postmeta']; ?>,
			posts=<?php echo (int) $step_totals['posts']; ?>
		</p>
		<div class="overflow-x-auto border border-gray-200 rounded-lg mb-4">
			<table class="min-w-full text-xs border-collapse">
				<thead>
					<tr class="bg-gray-100">
						<th class="text-right py-2 px-2">order_id</th>
						<th class="text-right py-2 px-2">post_date</th>
						<th class="text-right py-2 px-2">status</th>
						<th class="text-right py-2 px-2">نتیجه</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $step_log as $line ) : ?>
						<tr class="border-t border-gray-100">
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo (int) ( $line['order_id'] ?? 0 ); ?></td>
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $line['post_date'] ?? '' ) ); ?></td>
							<td class="py-1 px-2"><?php echo esc_html( (string) ( $line['post_status'] ?? '' ) ); ?></td>
							<td class="py-1 px-2 <?php echo ! empty( $line['ok'] ) ? 'text-green-700' : 'text-red-600'; ?>">
								<?php echo esc_html( (string) ( $line['message'] ?? '' ) ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>

		<?php if ( $total_remaining > 0 && $step_errors === 0 ) : ?>
			<p class="text-sm text-gray-600 mb-2">
				هنوز <?php echo (int) $total_remaining; ?> سفارش باقی است.
				<a class="text-blue-600 underline" href="<?php echo esc_url( $step_url ); ?>">مرحله بعد</a>
				یا با تأخیر ۱۰ ثانیه به‌صورت خودکار ادامه می‌دهد.
			</p>
		<?php endif; ?>
	<?php endif; ?>
</main>

<?php if ( $auto_continue ) : ?>
<script>
(function () {
	var delay = 10000;
	var next = <?php echo wp_json_encode( $step_url ); ?>;
	setTimeout(function () {
		window.location.href = next;
	}, delay);
})();
</script>
<?php endif; ?>

<?php
get_footer();
