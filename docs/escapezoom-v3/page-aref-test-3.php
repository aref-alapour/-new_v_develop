<?php
/**
 * Template Name: Aref product ratings migration
 *
 * Wizard: verify tables + seed criteria → backfill dimension rows → backfill weights → rebuild rollups → validation sample.
 *
 * Loads automatically for a Page whose slug is exactly "aref-test-3"
 * For URL /page-aref-test-3/ use page-page-aref-test-3.php or assign this template in the editor.
 *
 * @package escapezoom-v3
 */

if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
	auth_redirect();
	exit;
}

$page_url = get_permalink();
if ( ! $page_url ) {
	wp_die( esc_html__( 'صفحه نامعتبر است.', 'escapezoom-v3' ) );
}

if ( ! headers_sent() ) {
	nocache_headers();
	header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
	header( 'Pragma: no-cache' );
}

$allowed_steps = [ 'verify', 'rows', 'weights', 'rebuild', 'validate' ];

$step = isset( $_GET['ez_step'] ) ? sanitize_key( wp_unslash( $_GET['ez_step'] ) ) : '';
if ( $step !== '' && ! in_array( $step, $allowed_steps, true ) ) {
	$step = '';
}

$nonce     = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
$nonce_ok  = $step !== '' && $nonce !== '' && wp_verify_nonce( $nonce, 'ez_pr_ratings_' . $step );
$bad_nonce = isset( $_GET['_wpnonce'] ) && ! $nonce_ok;

$after = isset( $_GET['after'] ) ? absint( $_GET['after'] ) : 0;
$batch = isset( $_GET['batch'] ) ? absint( $_GET['batch'] ) : 250;
$batch = min( 500, max( 10, $batch ) );
$auto  = isset( $_GET['auto'] ) && (string) $_GET['auto'] === '1';

$pause_sec = isset( $_GET['pause'] ) ? absint( $_GET['pause'] ) : 5;
$pause_sec = min( 120, max( 2, $pause_sec ) );

$allowed_rank_steps = [ 'verify_ranking', 'seed_penalties', 'scores', 'ratings_meta', 'topsale_held', 'purge_hottest', 'validate_ranking' ];
$rank_step            = isset( $_GET['ez_rank_step'] ) ? sanitize_key( wp_unslash( $_GET['ez_rank_step'] ) ) : '';
if ( $rank_step !== '' && ! in_array( $rank_step, $allowed_rank_steps, true ) ) {
	$rank_step = '';
}
$rank_nonce     = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
$rank_nonce_ok  = $rank_step !== '' && $rank_nonce !== '' && wp_verify_nonce( $rank_nonce, 'ez_rank_' . $rank_step );
$rank_bad_nonce = $rank_step !== '' && isset( $_GET['_wpnonce'] ) && ! $rank_nonce_ok;
$rank_batch     = isset( $_GET['batch'] ) ? absint( $_GET['batch'] ) : 50;
$rank_batch     = min( 500, max( 10, $rank_batch ) );
$rank_auto        = isset( $_GET['auto'] ) && (string) $_GET['auto'] === '1';
$ez_rank_auto_next_url = '';

/**
 * هرگز wp_nonce_url را برای خروجی در JS استفاده نکن — esc_html به &amp; تبدیل می‌کند.
 *
 * @return string
 */
$ez_ratings_step_url = function ( string $base, string $step_name, int $after_step, int $batch_size, string $auto_0_or_1, int $pause_seconds ) use ( $allowed_steps ): string {
	if ( ! in_array( $step_name, $allowed_steps, true ) ) {
		$step_name = 'validate';
	}

	return add_query_arg(
		[
			'_wpnonce' => wp_create_nonce( 'ez_pr_ratings_' . $step_name ),
			'ez_step'  => $step_name,
			'after'    => $after_step,
			'batch'    => $batch_size,
			'auto'     => $auto_0_or_1,
			'pause'    => $pause_seconds,
		],
		$base
	);
};

$ez_rank_step_url = function ( string $base, string $step_name, int $after_step, int $batch_size, string $auto_0_or_1, int $pause_seconds ) use ( $allowed_rank_steps ): string {
	if ( ! in_array( $step_name, $allowed_rank_steps, true ) ) {
		$step_name = 'verify_ranking';
	}

	return add_query_arg(
		[
			'_wpnonce'    => wp_create_nonce( 'ez_rank_' . $step_name ),
			'ez_rank_step' => $step_name,
			'after'       => $after_step,
			'batch'       => $batch_size,
			'auto'        => $auto_0_or_1,
			'pause'       => $pause_seconds,
		],
		$base
	);
};

/** @var array<string,mixed>|null */
$result = null;

/** @var array<string,mixed>|null */
$rank_result = null;

/** @var string */
$ez_auto_next_url = '';

/** @var int */
$ez_auto_pause_ms = $pause_sec * 1000;

if ( $nonce_ok ) {
	switch ( $step ) {
		case 'verify':
			if ( ! Ez_Product_Ratings_Schema::tables_exist() ) {
				$result = [
					'success' => false,
					'message' => 'جداول رتینگ در دیتابیس نیستند. ابتدا فایل ez_bootstrap_custom_tables.sql را ایمپورت کنید (پیشوند جداول با wp_prefix هماهنگ باشد).',
				];
				break;
			}
			Ez_Product_Ratings_Schema::seed_criteria();
			Ez_Product_Ratings_Schema::sync_version_option_if_ready();
			$result = [
				'success' => true,
				'message' => 'جداول تأیید شد؛ معیارها seed شدند؛ گزینه نسخه به‌روز شد (' . Ez_Product_Ratings_Schema::VERSION . ').',
			];
			break;

		case 'rows':
			$r       = Ez_Product_Ratings_Migration::backfill_rows_chunk( $after, $batch );
			$result  = array_merge( [ 'success' => true, 'step_label' => 'ردیف‌های ابعاد' ], $r );
			$next_a  = (int) $r['next_after'];
			$done_ch = ! empty( $r['done'] );
			if ( $auto && ! $done_ch && (int) $r['processed'] > 0 ) {
				$ez_auto_next_url = $ez_ratings_step_url( $page_url, 'rows', $next_a, $batch, '1', $pause_sec );
			}
			break;

		case 'weights':
			$r       = Ez_Product_Ratings_Migration::backfill_weights_chunk( $after, $batch );
			$result  = array_merge( [ 'success' => true, 'step_label' => 'وزن‌ها' ], $r );
			$next_a  = (int) $r['next_after'];
			$done_ch = ! empty( $r['done'] );
			if ( $auto && ! $done_ch && (int) $r['processed'] > 0 ) {
				$ez_auto_next_url = $ez_ratings_step_url( $page_url, 'weights', $next_a, $batch, '1', $pause_sec );
			}
			break;

		case 'rebuild':
			Ez_Product_Rating_Rollup_Service::instance()->rebuild_all_rollups();
			$result = [
				'success' => true,
				'message' => 'rollup کل بازسازی شد.',
			];
			break;

		case 'validate':
			$result = array_merge(
				[ 'success' => true ],
				Ez_Product_Ratings_Migration::spot_check_products( 8 )
			);
			break;

		default:
			$result = [ 'success' => false, 'message' => 'مرحله نامعتبر.' ];
	}
}

if ( $rank_nonce_ok ) {
	switch ( $rank_step ) {
		case 'verify_ranking':
			$verify = Ez_Product_Ranking_Migration::verify_tables();
			$tables = $verify['tables'] ?? [];
			$msg    = wp_json_encode( $verify, JSON_UNESCAPED_UNICODE );
			if ( ! empty( $verify['penalties_empty'] ) ) {
				$msg .= ' — جدول ez_product_penalties خالی است؛ مرحله seed_penalties را اجرا کنید.';
			}
			$rank_result = [
				'success' => ! in_array( false, $tables, true ),
				'message' => $msg,
				'tables'  => $tables,
				'penalty_row_count' => (int) ( $verify['penalty_row_count'] ?? 0 ),
				'penalties_empty'   => ! empty( $verify['penalties_empty'] ),
			];
			break;
		case 'seed_penalties':
			$seed = Ez_Product_Ranking_Migration::seed_penalties();
			$rank_result = array_merge( [ 'success' => true, 'message' => 'پنالتی‌های legacy seed شدند.' ], $seed );
			break;
		case 'scores':
			$r = Ez_Product_Ranking_Migration::backfill_scores_chunk( $after, $rank_batch );
			$rank_result = array_merge( [ 'success' => true, 'step_label' => 'اسکور رتبه‌بندی' ], $r );
			if ( $rank_auto && empty( $r['done'] ) && (int) $r['processed'] > 0 ) {
				$ez_rank_auto_next_url = $ez_rank_step_url( $page_url, 'scores', (int) $r['next_after'], $rank_batch, '1', $pause_sec );
			}
			break;
		case 'ratings_meta':
			$r = Ez_Product_Ranking_Migration::backfill_rating_meta_chunk( $after, $rank_batch );
			$rank_result = array_merge( [ 'success' => true, 'step_label' => 'meta امتیاز کلی' ], $r );
			if ( $rank_auto && empty( $r['done'] ) && (int) $r['processed'] > 0 ) {
				$ez_rank_auto_next_url = $ez_rank_step_url( $page_url, 'ratings_meta', (int) $r['next_after'], $rank_batch, '1', $pause_sec );
			}
			break;
		case 'topsale_held':
			$held = Ez_Product_Ranking_Migration::rebuild_held_orders();
			$rank_result = array_merge( [ 'success' => true, 'message' => 'held_orders_list بازسازی شد.' ], $held );
			break;
		case 'purge_hottest':
			Ez_Product_Ranking_Migration::purge_hottest();
			$rank_result = [ 'success' => true, 'message' => 'نگهداری روزانه (purge hottest + held) اجرا شد.' ];
			break;
		case 'validate_ranking':
			$rank_result = Ez_Product_Ranking_Migration::spot_check_ranking( 10 );
			break;
		default:
			$rank_result = [ 'success' => false, 'message' => 'مرحله رتبه‌بندی نامعتبر.' ];
	}
}

get_header();
?>

<main class="container mx-auto max-w-3xl px-4 py-10" dir="rtl">
	<h1 class="mb-4 text-xl font-bold">مهاجرت امتیاز نظرات رابطه‌ای + rollup</h1>
	<p class="mb-6 text-sm text-gray-600">
		هر مرحله با یک بار کلیک یا زنجیرهٔ خودکار اجرا می‌شود.
		<code class="rounded bg-gray-100 px-1">batch</code> پیش‌فرض <?php echo (int) $batch; ?> نظر؛ حداکثر ۵۰۰.
		<code class="rounded bg-gray-100 px-1">pause</code> بین مراحل خودکار (۲…۱۲۰ ثانیه).
	</p>

	<?php if ( $bad_nonce ) : ?>
		<p class="rounded border border-red-200 bg-red-50 p-3 text-red-800"><?php esc_html_e( 'توکن امنیتی نامعتبر است.', 'escapezoom-v3' ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $result ) ) : ?>
		<div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm space-y-1">
			<?php if ( ! empty( $result['success'] ) ) : ?>
				<p class="font-semibold text-green-800"><?php echo isset( $result['step_label'] ) ? esc_html( (string) $result['step_label'] ) . ' — انجام شد' : esc_html( (string) ( $result['message'] ?? 'OK' ) ); ?></p>
				<?php if ( isset( $result['processed'] ) ) : ?>
					<p>پردازش‌شده در این درخواست: <strong><?php echo (int) $result['processed']; ?></strong> — موفق ذخیره: <strong><?php echo (int) $result['ok']; ?></strong></p>
					<p>کرسر بعدی (after): <strong><?php echo (int) $result['next_after']; ?></strong></p>
					<?php if ( ! empty( $result['done'] ) ) : ?>
						<p class="text-green-700">این مرحله تمام شد.</p>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( isset( $result['samples'] ) ) : ?>
					<p class="font-semibold mt-2">اعتبارسنجی نمونه</p>
					<p>بیشترین اختلاف محور در نمونه‌ها: <strong><?php echo esc_html( (string) $result['worst_axis_diff'] ); ?></strong></p>
					<p>نظر با متا بدون ردیف ابعاد: <strong><?php echo (int) $result['missing_rows_cnt']; ?></strong></p>
					<p>نظر تأییدشده بدون وزن DB: <strong><?php echo (int) $result['missing_wts_cnt']; ?></strong></p>
					<details class="mt-2">
						<summary class="cursor-pointer text-blue-700">جزئیات نمونه‌ها</summary>
						<pre class="mt-2 overflow-auto rounded bg-white p-2 text-xs"><?php echo esc_html( wp_json_encode( $result['samples'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ); ?></pre>
					</details>
					<hr class="my-4 border-gray-200" />
					<p class="font-semibold">QA سناریوها (دستی روی staging)</p>
					<ul class="list-disc list-inside text-gray-700 space-y-1">
						<li>ثبت نظر تأیید خودکار؛ rollup و meta هر دو هم‌جهت شوند.</li>
						<li>pending سپس تأیید CRM؛ بدون دو بار شمارش meta.</li>
						<li>ویرایش امتیاز در حالت تأیید؛ دلتا rollup و hottest_products.</li>
						<li>hold از تأیید؛ remove rollup و meta.</li>
						<li>زبالهٔ نظر تأییدشده؛ totals کم شود.</li>
						<li>کاربر وزن ۷ (مجموعه‌دار ذخیره‌شده سطح ۱۰).</li>
					</ul>
				<?php endif; ?>
			<?php else : ?>
				<p class="text-red-700"><?php echo esc_html( (string) ( $result['message'] ?? 'خطا' ) ); ?></p>
			<?php endif; ?>
		</div>

		<?php if ( $auto && $ez_auto_next_url !== '' ) : ?>
			<p id="ez-pr-countdown" class="mb-4 rounded border border-blue-200 bg-blue-50 p-3 text-blue-900">
				صفحه بارگذاری کامل شد. حدود <strong><?php echo (int) $pause_sec; ?></strong> ثانیه دیگر مرحله بعد اجرا می‌شود…
			</p>
			<p class="text-sm text-gray-600">
				<?php
				$cancel = $ez_ratings_step_url( $page_url, $step, (int) ( $result['next_after'] ?? $after ), $batch, '0', $pause_sec );
				?>
				<a href="<?php echo esc_url( $cancel ); ?>">توقف خودکار</a>
			</p>
		<?php elseif ( isset( $result['processed'], $result['next_after'] ) && empty( $result['done'] ) && (int) $result['processed'] > 0 ) : ?>
			<p class="flex flex-wrap gap-3 mb-8">
				<?php
				$nxt_manual = $ez_ratings_step_url( $page_url, $step, (int) $result['next_after'], $batch, '0', $pause_sec );
				$nxt_auto   = $ez_ratings_step_url( $page_url, $step, (int) $result['next_after'], $batch, '1', $pause_sec );
				?>
				<a class="inline-block rounded bg-gray-800 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $nxt_manual ); ?>">مرحله بعد (دستی)</a>
				<a class="inline-block rounded bg-blue-600 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $nxt_auto ); ?>">ادامه خودکار</a>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<hr class="my-8 border-gray-200" />

	<h2 class="mb-3 text-lg font-semibold">اجرای تک‌مرحله‌ای</h2>
	<ul class="space-y-3 text-sm">
		<li>
			<a class="text-blue-700 underline" href="<?php echo esc_url( $ez_ratings_step_url( $page_url, 'verify', 0, $batch, '0', $pause_sec ) ); ?>">۱) تأیید جداول + seed معیارها</a>
			<span class="text-gray-500"> (DDL از ez_bootstrap_custom_tables.sql)</span>
		</li>
		<li>
			<a class="text-blue-700 underline" href="<?php echo esc_url( $ez_ratings_step_url( $page_url, 'rows', 0, $batch, '0', $pause_sec ) ); ?>">۲) Backfill ردیف ابعاد از comment_rating</a>
			<span class="text-gray-500"> (cursor: after=<?php echo (int) $after; ?>)</span>
		</li>
		<li>
			<a class="text-blue-700 underline" href="<?php echo esc_url( $ez_ratings_step_url( $page_url, 'weights', 0, $batch, '0', $pause_sec ) ); ?>">۳) Backfill وزن از user_level</a>
		</li>
		<li>
			<a class="text-blue-700 underline" href="<?php echo esc_url( $ez_ratings_step_url( $page_url, 'rebuild', 0, $batch, '0', $pause_sec ) ); ?>">۴) بازسازی کامل rollup</a>
		</li>
		<li>
			<a class="text-blue-700 underline" href="<?php echo esc_url( $ez_ratings_step_url( $page_url, 'validate', 0, $batch, '0', $pause_sec ) ); ?>">۵) گزارش اعتبارسنجی</a>
		</li>
	</ul>

	<p class="mt-8 text-xs text-gray-500">
		پارامترها:
		<code>?ez_step=rows&amp;after=0&amp;batch=250&amp;auto=1&amp;pause=5&amp;_wpnonce=…</code>
	</p>

	<hr class="my-10 border-gray-300" />

	<h2 class="mb-4 text-lg font-bold">رتبه‌بندی درجا (popular / hottest / topsale)</h2>

	<?php if ( $rank_bad_nonce ) : ?>
		<p class="rounded border border-red-200 bg-red-50 p-3 text-red-800"><?php esc_html_e( 'توکن رتبه‌بندی نامعتبر است.', 'escapezoom-v3' ); ?></p>
	<?php endif; ?>

	<?php if ( is_array( $rank_result ) ) : ?>
		<div class="mb-6 rounded border border-gray-200 bg-gray-50 p-4 text-sm space-y-1">
			<?php if ( ! empty( $rank_result['success'] ) ) : ?>
				<p class="font-semibold text-green-800"><?php echo isset( $rank_result['step_label'] ) ? esc_html( (string) $rank_result['step_label'] ) . ' — انجام شد' : esc_html( (string) ( $rank_result['message'] ?? 'OK' ) ); ?></p>
				<?php if ( isset( $rank_result['processed'] ) ) : ?>
					<p>پردازش‌شده: <strong><?php echo (int) $rank_result['processed']; ?></strong><?php if ( isset( $rank_result['ok'] ) ) : ?> — ok: <strong><?php echo (int) $rank_result['ok']; ?></strong><?php endif; ?></p>
					<p>after: <strong><?php echo (int) $rank_result['next_after']; ?></strong></p>
					<?php if ( ! empty( $rank_result['done'] ) ) : ?>
						<p class="text-green-700">مرحله تمام شد.</p>
					<?php endif; ?>
				<?php endif; ?>
				<?php if ( isset( $rank_result['mismatches'] ) ) : ?>
					<p>نمونه reconcile — اختلاف‌های بیش از آستانه: <strong><?php echo (int) $rank_result['mismatches']; ?></strong></p>
					<details class="mt-2">
						<summary class="cursor-pointer text-blue-700">جزئیات JSON</summary>
						<pre class="mt-2 overflow-auto rounded bg-white p-2 text-xs"><?php echo esc_html( wp_json_encode( $rank_result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) ); ?></pre>
					</details>
				<?php endif; ?>
			<?php else : ?>
				<p class="text-red-700"><?php echo esc_html( (string) ( $rank_result['message'] ?? 'خطا' ) ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( $rank_auto && $ez_rank_auto_next_url !== '' ) : ?>
			<p id="ez-rank-countdown" class="mb-4 rounded border border-blue-200 bg-blue-50 p-3 text-blue-900">
				ادامه خودکار رتبه‌بندی تا <strong><?php echo (int) $pause_sec; ?></strong> ثانیه دیگر…
			</p>
		<?php elseif ( isset( $rank_result['processed'], $rank_result['next_after'] ) && empty( $rank_result['done'] ) && (int) $rank_result['processed'] > 0 && $rank_step !== '' ) : ?>
			<p class="flex flex-wrap gap-3 mb-6">
				<a class="inline-block rounded bg-gray-800 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, $rank_step, (int) $rank_result['next_after'], $rank_batch, '0', $pause_sec ) ); ?>">مرحله بعد</a>
				<a class="inline-block rounded bg-blue-600 px-4 py-2 text-white no-underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, $rank_step, (int) $rank_result['next_after'], $rank_batch, '1', $pause_sec ) ); ?>">ادامه خودکار</a>
			</p>
		<?php endif; ?>
	<?php endif; ?>

	<ul class="space-y-3 text-sm">
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'verify_ranking', 0, $rank_batch, '0', $pause_sec ) ); ?>">تأیید جداول ranking + penalties</a></li>
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'seed_penalties', 0, $rank_batch, '0', $pause_sec ) ); ?>">seed پنالتی legacy</a></li>
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'scores', 0, $rank_batch, '0', $pause_sec ) ); ?>">backfill اسکورها</a></li>
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'ratings_meta', 0, $rank_batch, '0', $pause_sec ) ); ?>">sync meta امتیاز</a></li>
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'topsale_held', 0, $rank_batch, '0', $pause_sec ) ); ?>">rebuild held_orders_list</a></li>
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'purge_hottest', 0, $rank_batch, '0', $pause_sec ) ); ?>">purge hottest + held</a></li>
		<li><a class="text-blue-700 underline" href="<?php echo esc_url( $ez_rank_step_url( $page_url, 'validate_ranking', 0, $rank_batch, '0', $pause_sec ) ); ?>">validate / reconcile نمونه</a></li>
	</ul>
	<p class="mt-4 text-xs text-gray-500"><code>?ez_rank_step=scores&amp;after=0&amp;batch=50&amp;auto=1</code></p>
</main>

<?php if ( $ez_auto_next_url !== '' ) : ?>
	<script>
	(function () {
		var next = <?php echo wp_json_encode( $ez_auto_next_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?>;
		var delayMs = <?php echo (int) $ez_auto_pause_ms; ?>;
		var el = document.getElementById('ez-pr-countdown');
		var left = Math.ceil(delayMs / 1000);
		window.addEventListener('load', function () {
			var tick = setInterval(function () {
				left--;
				if (el && left > 0) {
					var s = el.querySelector('strong');
					if (s) {
						s.textContent = String(left);
					}
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

<?php if ( $ez_rank_auto_next_url !== '' ) : ?>
	<script>
	(function () {
		var next = <?php echo wp_json_encode( $ez_rank_auto_next_url, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE ); ?>;
		var delayMs = <?php echo (int) $ez_auto_pause_ms; ?>;
		window.addEventListener('load', function () {
			window.setTimeout(function () { window.location.assign(next); }, delayMs);
		});
	})();
	</script>
<?php endif; ?>

<?php
get_footer();
