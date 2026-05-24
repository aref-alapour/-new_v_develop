<?php
/**
 * Template Name: aref-test-5
 *
 * پردازش صف SMS (sms_sending_queue) — هر بار لود صفحه و سپس رفرش خودکار هر ۳ دقیقه.
 * فقط مدیرکل. در وردپرس یک برگه بسازید و این قالب را انتخاب کنید (slug پیشنهادی: aref-test-5).
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

global $wpdb;

$refresh_seconds = 180;
$run_at          = time();
$run_at_human    = wp_date( 'Y-m-d H:i:s', $run_at );

$pending_before = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM `sms_sending_queue` WHERE sent_time IS NULL"
);

if ( function_exists( 'ez_sms_sending_queue_schedule' ) ) {
	ez_sms_sending_queue_schedule();
} else {
	$rows = $wpdb->get_results(
		"SELECT * FROM `sms_sending_queue` WHERE sent_time IS NULL ORDER BY query_time DESC LIMIT 50;"
	);

	if ( ! empty( $rows ) ) {
		foreach ( $rows as $row ) {
			$sent_flag = false;

			$sms1_response = json_decode( smsPattern( $row->phone, $row->text, $row->token ) );

			if ( isset( $sms1_response->RetStatus ) && (int) $sms1_response->RetStatus === 1 ) {
				$sent_flag = true;
			} else {
				$sms2_response = json_decode( smsPattern( $row->phone, $row->text, $row->token ) );

				if ( isset( $sms2_response->RetStatus ) && (int) $sms2_response->RetStatus === 1 ) {
					$sent_flag = true;
				}
			}

			if ( $sent_flag ) {
				$now = time();
				$wpdb->query(
					$wpdb->prepare(
						'UPDATE `sms_sending_queue` SET sent_time = %d WHERE ID = %d',
						$now,
						$row->ID
					)
				);
			}
		}
	}
}

$pending_after = (int) $wpdb->get_var(
	"SELECT COUNT(*) FROM `sms_sending_queue` WHERE sent_time IS NULL"
);

$sent_this_run = max( 0, $pending_before - $pending_after );

$recent_pending = $wpdb->get_results(
	"SELECT ID, phone, token, type, order_id, query_time
	 FROM `sms_sending_queue`
	 WHERE sent_time IS NULL
	 ORDER BY query_time DESC
	 LIMIT 15"
);

get_header();
?>
<meta http-equiv="refresh" content="<?php echo (int) $refresh_seconds; ?>">

<main id="primary" class="site-main container mx-auto px-4 py-8" style="direction: rtl; max-width: 900px;">
	<h1 class="text-2xl font-bold mb-2">صف ارسال SMS</h1>
	<p class="text-gray-600 mb-4 text-sm leading-relaxed">
		هر <strong><?php echo (int) ( $refresh_seconds / 60 ); ?> دقیقه</strong> این صفحه خودکار رفرش می‌شود و حداکثر
		<strong>۵۰</strong> ردیف در صف (<code dir="ltr">sent_time IS NULL</code>) پردازش می‌شود.
		تابع: <code dir="ltr">ez_sms_sending_queue_schedule</code>
	</p>

	<div class="mb-6 grid gap-3 sm:grid-cols-2 text-sm">
		<div class="border border-gray-200 rounded-lg p-4">
			<p class="text-gray-500 mb-1">آخرین اجرا</p>
			<p class="font-mono text-base" dir="ltr"><?php echo esc_html( $run_at_human ); ?></p>
		</div>
		<div class="border border-gray-200 rounded-lg p-4">
			<p class="text-gray-500 mb-1">رفرش بعدی</p>
			<p class="font-mono text-base" dir="ltr">
				<span id="ez-sms-countdown"><?php echo (int) $refresh_seconds; ?></span> ثانیه
			</p>
		</div>
		<div class="border border-green-200 bg-green-50 rounded-lg p-4">
			<p class="text-gray-600 mb-1">ارسال‌شده در این اجرا (تقریبی)</p>
			<p class="text-2xl font-bold text-green-800"><?php echo (int) $sent_this_run; ?></p>
		</div>
		<div class="border border-amber-200 bg-amber-50 rounded-lg p-4">
			<p class="text-gray-600 mb-1">باقی‌مانده در صف</p>
			<p class="text-2xl font-bold text-amber-900">
				<?php echo (int) $pending_after; ?>
				<span class="text-sm font-normal text-gray-600">(قبل: <?php echo (int) $pending_before; ?>)</span>
			</p>
		</div>
	</div>

	<?php if ( ! empty( $recent_pending ) ) : ?>
		<h2 class="text-lg font-semibold mb-2">نمونه ردیف‌های در انتظار</h2>
		<div class="overflow-x-auto border border-gray-200 rounded-lg mb-6">
			<table class="min-w-full text-xs border-collapse">
				<thead>
					<tr class="bg-gray-100">
						<th class="text-right py-2 px-2">ID</th>
						<th class="text-right py-2 px-2">phone</th>
						<th class="text-right py-2 px-2">token</th>
						<th class="text-right py-2 px-2">type</th>
						<th class="text-right py-2 px-2">order_id</th>
						<th class="text-right py-2 px-2">query_time</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $recent_pending as $row ) : ?>
						<tr class="border-t border-gray-100">
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo (int) $row->ID; ?></td>
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) $row->phone ); ?></td>
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) $row->token ); ?></td>
							<td class="py-1 px-2"><?php echo esc_html( (string) ( $row->type ?? '' ) ); ?></td>
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $row->order_id ?? '' ) ); ?></td>
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( (string) ( $row->query_time ?? '' ) ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php else : ?>
		<p class="text-sm text-green-800 bg-green-50 border border-green-200 rounded p-3 mb-6">
			صف خالی است — ردیفی با <code dir="ltr">sent_time IS NULL</code> نیست.
		</p>
	<?php endif; ?>

	<p class="text-xs text-gray-500 border-t pt-4">
		برای اجرای دستی بدون این صفحه: <code dir="ltr">?ez_sms_sending_queue_schedule=1</code>.
		Cron وردپرس: <code dir="ltr">ez_sms_sending_queue_cron</code> هر ۳ دقیقه
		(<code dir="ltr">template/func/cron.php</code>).
	</p>
</main>

<script>
(function () {
	var seconds = <?php echo (int) $refresh_seconds; ?>;
	var el = document.getElementById('ez-sms-countdown');
	if (!el) return;

	var tick = function () {
		if (seconds <= 0) {
			window.location.reload();
			return;
		}
		el.textContent = String(seconds);
		seconds -= 1;
	};

	tick();
	setInterval(tick, 1000);
})();
</script>

<?php
get_footer();
