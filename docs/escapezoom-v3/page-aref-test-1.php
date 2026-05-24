<?php
/**
 * Template Name: Aref snapshot backfill
 *
 * Backfill wp_products_snapshot in steps (default 100 products per request).
 *
 * Loads automatically for a Page whose slug is exactly "aref-test-1" (URL usually /aref-test-1/).
 * For URL /page-aref-test-1/ use the companion file page-page-aref-test-1.php, or pick this template in the editor.
 *
 * @package escapezoom-v2
 */

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	auth_redirect();
	exit;
}

$page_url = get_permalink();
if ( ! $page_url ) {
	wp_die( esc_html__( 'صفحه نامعتبر است.', 'escapezoom-v2' ) );
}

// جلوگیری از کش و از دست رفتن query string.
if ( ! headers_sent() ) {
	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
}

$nonce_action = 'ez_snapshot_backfill';
$nonce        = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
$nonce_ok     = $nonce !== '' && wp_verify_nonce( $nonce, $nonce_action );
$bad_nonce    = isset( $_GET['_wpnonce'] ) && ! $nonce_ok;

$after = isset( $_GET['after'] ) ? absint( $_GET['after'] ) : 0;
$batch = isset( $_GET['batch'] ) ? absint( $_GET['batch'] ) : 100;
$batch = min( 100, max( 1, $batch ) );
$auto  = isset( $_GET['auto'] ) && (string) $_GET['auto'] === '1';

/** فاصله بین دو درخواست (ثانیه)، بعد از رویداد load مرورگر */
$pause_sec = isset( $_GET['pause'] ) ? absint( $_GET['pause'] ) : 5;
$pause_sec = min( 120, max( 2, $pause_sec ) );

/**
 * هرگز wp_nonce_url را برای URL خروجی در JS/ریدایرکت استفاده نکن — esc_html به &amp; تبدیل می‌کند.
 */
$ez_snapshot_step_url = static function ( string $base, string $action, int $after_step, int $batch_size, string $auto_0_or_1, int $pause_seconds ) : string {
	return add_query_arg(
		[
			'_wpnonce' => wp_create_nonce( $action ),
			'after'    => $after_step,
			'batch'    => $batch_size,
			'auto'     => $auto_0_or_1,
			'pause'    => $pause_seconds,
		],
		$base
	);
};

$service_class = \EscapeZoom\Core\Modules\Common\Services\ProductSnapshotService::class;

/** @var array<string, mixed>|null */
$batch_result = null;

/** @var string */
$ez_snapshot_auto_next_url = '';
/** @var int میلی‌ثانیه بعد از load */
$ez_snapshot_auto_pause_ms = $pause_sec * 1000;

if ( $nonce_ok ) {
	if ( ! class_exists( $service_class ) ) {
		$batch_result = [ 'error' => __( 'کلاس ProductSnapshotService بارگذاری نشده (ez_core).', 'escapezoom-v2' ) ];
	} else {
		/** @var \EscapeZoom\Core\Modules\Common\Services\ProductSnapshotService $service */
		$service = new $service_class();
		$ids     = $service->getNextProductIdBatch( $after, $batch );

		if ( $ids !== [] ) {
			$ok   = 0;
			$fail = 0;
			foreach ( $ids as $pid ) {
				if ( $service->upsertSnapshot( (int) $pid ) ) {
					++$ok;
				} else {
					++$fail;
				}
			}
			$next_after = max( $ids );
			$batch_result = [
				'ids_count'  => count( $ids ),
				'ok'         => $ok,
				'fail'       => $fail,
				'prev_after' => $after,
				'next_after' => $next_after,
				'finished'   => false,
			];

			if ( $auto ) {
				$ez_snapshot_auto_next_url = $ez_snapshot_step_url( $page_url, $nonce_action, $next_after, $batch, '1', $pause_sec );
			}
		} else {
			$batch_result = [
				'ids_count'     => 0,
				'ok'            => 0,
				'fail'          => 0,
				'prev_after'    => $after,
				'next_after'    => $after,
				'finished'      => true,
				'empty_catalog' => $after === 0,
			];
		}
	}
}

get_header();
?>

<main class="container mx-auto max-w-3xl px-4 py-10" dir="rtl">
	<h1 class="mb-4 text-xl font-bold">پر کردن جدول snapshot محصولات</h1>
	<p class="mb-6 text-sm text-gray-600">
		هر بار حداکثر <?php echo (int) $batch; ?> محصول <code class="rounded bg-gray-100 px-1">publish</code> با <code class="rounded bg-gray-100 px-1">ID &gt; after</code> پردازش می‌شود.
		در حالت خودکار یک‌بار صفحه کامل لود می‌شود؛ بعد از اتمام لود، حدود <strong><?php echo (int) $pause_sec; ?></strong> ثانیه صبر می‌کند و سپس خودش به دسته بعد می‌رود (بدون کلیک).
		می‌توانی با پارامتر <code class="rounded bg-gray-100 px-1">?pause=8</code> این فاصله را عوض کنی (۲…۱۲۰ ثانیه).
	</p>

	<?php if ( $bad_nonce ) : ?>
		<p class="rounded border border-red-200 bg-red-50 p-3 text-red-800"><?php esc_html_e( 'توکن امنیتی نامعتبر است.', 'escapezoom-v2' ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $batch_result ) && isset( $batch_result['error'] ) ) : ?>
		<p class="rounded border border-amber-200 bg-amber-50 p-3 text-amber-900"><?php echo esc_html( (string) $batch_result['error'] ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $batch_result ) && ! isset( $batch_result['error'] ) && ! empty( $batch_result['finished'] ) ) : ?>
		<p class="rounded border border-green-200 bg-green-50 p-3 text-green-900">
			<?php if ( ! empty( $batch_result['empty_catalog'] ) ) : ?>
				هیچ محصول منتشرشده‌ای برای پردازش پیدا نشد.
			<?php else : ?>
				تمام؛ محصولی با ID بزرگ‌تر از <?php echo (int) $batch_result['prev_after']; ?> باقی نماند.
			<?php endif; ?>
		</p>
		<?php if ( $auto ) : ?>
			<p class="text-sm text-gray-600">زنجیره خودکار به پایان رسید.</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( is_array( $batch_result ) && empty( $batch_result['error'] ) && empty( $batch_result['finished'] ) ) : ?>
		<div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm">
			<p>دسته قبلی: بعد از ID <strong><?php echo (int) $batch_result['prev_after']; ?></strong></p>
			<p>تعداد در این مرحله: <strong><?php echo (int) $batch_result['ids_count']; ?></strong></p>
			<p>ذخیره موفق: <strong><?php echo (int) $batch_result['ok']; ?></strong> — رد/ناموفق: <strong><?php echo (int) $batch_result['fail']; ?></strong></p>
			<p>کرسر بعدی: <strong><?php echo (int) $batch_result['next_after']; ?></strong></p>
		</div>
		<?php if ( $auto && $ez_snapshot_auto_next_url !== '' ) : ?>
			<p id="ez-snapshot-countdown" class="mb-4 rounded border border-blue-200 bg-blue-50 p-3 text-blue-900">
				صفحه بارگذاری کامل شد. حدود <strong><?php echo (int) $pause_sec; ?></strong> ثانیه دیگر به‌طور خودکار دسته بعد اجرا می‌شود…
			</p>
			<p class="text-sm text-gray-600">
				<?php
				$cancel_auto = $ez_snapshot_step_url( $page_url, $nonce_action, (int) $batch_result['next_after'], $batch, '0', $pause_sec );
				?>
				<a href="<?php echo esc_url( $cancel_auto ); ?>">توقف خودکار و ادامه دستی</a>
			</p>
		<?php else : ?>
			<?php
			$continue_manual = $ez_snapshot_step_url( $page_url, $nonce_action, (int) $batch_result['next_after'], $batch, '0', $pause_sec );
			$continue_auto   = $ez_snapshot_step_url( $page_url, $nonce_action, (int) $batch_result['next_after'], $batch, '1', $pause_sec );
			?>
			<p class="flex flex-wrap gap-3">
				<a class="inline-block rounded bg-gray-800 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $continue_manual ); ?>">مرحله بعد (دستی)</a>
				<a class="inline-block rounded bg-blue-600 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $continue_auto ); ?>">ادامه خودکار (مکث <?php echo (int) $pause_sec; ?> ثانیه بین مراحل)</a>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<?php if ( ! $nonce_ok || ( is_array( $batch_result ) && ( ! empty( $batch_result['finished'] ) || isset( $batch_result['error'] ) ) ) ) : ?>
		<hr class="my-8 border-gray-200" />
		<h2 class="mb-3 text-lg font-semibold">شروع از اول</h2>
		<p class="mb-4 text-sm text-gray-600">برای پر کردن از کوچک‌ترین ID، مقدار after صفر است.</p>
		<?php
		$start_manual = $ez_snapshot_step_url( $page_url, $nonce_action, 0, $batch, '0', $pause_sec );
		$start_auto   = $ez_snapshot_step_url( $page_url, $nonce_action, 0, $batch, '1', $pause_sec );
		?>
		<p class="flex flex-wrap gap-3">
			<a class="inline-block rounded border border-gray-300 bg-white px-4 py-2 text-gray-900 no-underline" href="<?php echo esc_url( $start_manual ); ?>">شروع (دستی)</a>
			<a class="inline-block rounded bg-blue-600 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $start_auto ); ?>">شروع خودکار تا انتها</a>
		</p>
	<?php endif; ?>
</main>

<?php if ( $ez_snapshot_auto_next_url !== '' ) : ?>
	<script>
	(function () {
		var next = <?php echo wp_json_encode( $ez_snapshot_auto_next_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?>;
		var delayMs = <?php echo (int) $ez_snapshot_auto_pause_ms; ?>;
		var el = document.getElementById('ez-snapshot-countdown');
		var left = Math.ceil(delayMs / 1000);
		window.addEventListener('load', function () {
			var tick = setInterval(function () {
				left--;
				if (el && left > 0) {
					el.querySelector('strong').textContent = String(left);
				}
				if (left <= 0) {
					clearInterval(tick);
				}
			}, 1000);
			window.setTimeout(function () {
				window.location.assign(next);
			}, delayMs);
		});
	})();
	</script>
<?php endif; ?>

<?php
get_footer();
