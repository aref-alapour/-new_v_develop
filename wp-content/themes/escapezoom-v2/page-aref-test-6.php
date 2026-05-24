<?php
/**
 * Template Name: aref-test-6
 *
 * پاکسازی یک‌بارمصرف cronهای یتیم/duplicate + نمایش وضعیت registry.
 * فقط مدیرکل. slug پیشنهادی: aref-test-6
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

$cleanup_log   = array();
$cleanup_ran   = false;
$cleanup_error = '';

if (
	isset( $_POST['ez_cron_cleanup'] )
	&& isset( $_POST['_wpnonce'] )
	&& wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'ez_cron_cleanup_once' )
) {
	if ( ! function_exists( 'ez_cron_run_one_time_cleanup' ) ) {
		$cleanup_error = 'template/func/cron.php لود نشده است.';
	} else {
		$cleanup_log = ez_cron_run_one_time_cleanup();
		$cleanup_ran = true;
	}
}

$disabled_wp_cron = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
$registry         = function_exists( 'ez_cron_registry' ) ? ez_cron_registry() : array();
$orphans          = function_exists( 'ez_cron_orphan_hooks' ) ? ez_cron_orphan_hooks() : array();
$heartbeats       = (array) get_option( 'ez_cron_heartbeats', array() );
$health           = function_exists( 'ez_cron_health_report' ) ? ez_cron_health_report() : array();
$crontrol_paused  = get_option( 'wp_crontrol_paused', array() );
if ( ! is_array( $crontrol_paused ) ) {
	$crontrol_paused = array();
}

/**
 * @param string $hook
 */
function ez_aref6_hook_row( string $hook, array $registry, array $heartbeats ): array {
	$events = function_exists( 'ez_cron_count_events' ) ? ez_cron_count_events( $hook ) : 0;
	$next   = wp_next_scheduled( $hook );
	$event  = function_exists( 'wp_get_scheduled_event' ) ? wp_get_scheduled_event( $hook ) : null;
	$hb     = isset( $heartbeats[ $hook ] ) && is_array( $heartbeats[ $hook ] ) ? $heartbeats[ $hook ] : array();

	$callback = isset( $registry[ $hook ]['callback'] ) ? (string) $registry[ $hook ]['callback'] : '';
	$paused   = function_exists( 'ez_cron_is_hook_paused' ) && ez_cron_is_hook_paused( $hook );
	$on_hook  = ( $callback !== '' && has_action( $hook, $callback ) );

	return array(
		'hook'      => $hook,
		'events'    => $events,
		'next'      => $next ? wp_date( 'Y-m-d H:i:s', (int) $next ) : '—',
		'overdue'   => $next && $next < time(),
		'schedule'  => $event && isset( $event->schedule ) ? (string) $event->schedule : '—',
		'expected'  => isset( $registry[ $hook ]['recurrence'] ) ? (string) $registry[ $hook ]['recurrence'] : '—',
		'enabled'   => ! empty( $registry[ $hook ]['enabled'] ),
		'paused'    => $paused,
		'on_hook'   => $on_hook,
		'callback'  => $callback,
		'hb_ts'     => ! empty( $hb['ts'] ) ? wp_date( 'Y-m-d H:i:s', (int) $hb['ts'] ) : '—',
		'hb_status' => isset( $hb['status'] ) ? (string) $hb['status'] : '—',
	);
}

get_header();
?>

<main class="container mx-auto px-4 py-8 max-w-5xl" dir="rtl">
	<h1 class="text-2xl font-bold mb-2">aref-test-6 — Cron cleanup &amp; health</h1>
	<p class="text-sm text-gray-600 mb-6">
		Registry واحد: <code dir="ltr">template/func/cron.php</code>.
		SMS: <code dir="ltr">ez_sms_sending_queue_cron</code> هر ۳ دقیقه.
	</p>

	<?php if ( $disabled_wp_cron ) : ?>
		<p class="text-sm text-amber-900 bg-amber-50 border border-amber-200 rounded p-3 mb-4">
			<code dir="ltr">DISABLE_WP_CRON</code> فعال است — حتماً system cron به
			<code dir="ltr">wp-cron.php</code> تنظیم کنید.
		</p>
	<?php endif; ?>

	<?php if ( ! empty( $crontrol_paused ) ) : ?>
		<p class="text-sm text-red-900 bg-red-50 border border-red-200 rounded p-3 mb-4">
			<strong>WP Crontrol Paused:</strong>
			<code dir="ltr"><?php echo esc_html( implode( ', ', array_keys( $crontrol_paused ) ) ); ?></code>
			— در Crontrol روی هر hook دکمه Resume بزنید؛ وگرنه <code dir="ltr">do_action</code> تابع تم را اجرا نمی‌کند.
		</p>
	<?php endif; ?>

	<?php if ( $cleanup_error !== '' ) : ?>
		<p class="text-sm text-red-800 bg-red-50 border border-red-200 rounded p-3 mb-4"><?php echo esc_html( $cleanup_error ); ?></p>
	<?php endif; ?>

	<?php if ( $cleanup_ran ) : ?>
		<p class="text-sm text-green-800 bg-green-50 border border-green-200 rounded p-3 mb-4">
			پاکسازی انجام شد و cronها دوباره schedule شدند (<?php echo (int) count( $cleanup_log ); ?> ردیف).
		</p>
	<?php endif; ?>

	<section class="mb-8">
		<h2 class="text-lg font-semibold mb-3">پاکسازی یک‌بارمصرف</h2>
		<p class="text-sm text-gray-600 mb-3">
			Orphanها حذف می‌شوند؛ همه hookهای تم clear و با interval درست دوباره register می‌شوند.
		</p>
		<form method="post" class="mb-4">
			<?php wp_nonce_field( 'ez_cron_cleanup_once' ); ?>
			<button type="submit" name="ez_cron_cleanup" value="1"
				class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm"
				onclick="return confirm('همه cronهای تم reset شوند؟');">
				اجرای پاکسازی cron
			</button>
		</form>

		<?php if ( ! empty( $cleanup_log ) ) : ?>
			<div class="overflow-x-auto border rounded">
				<table class="min-w-full text-sm">
					<thead class="bg-gray-100">
						<tr>
							<th class="py-2 px-3 text-right">Hook</th>
							<th class="py-2 px-3 text-right">قبل</th>
							<th class="py-2 px-3 text-right">بعد</th>
							<th class="py-2 px-3 text-right">عمل</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $cleanup_log as $row ) : ?>
							<tr class="border-t">
								<td class="py-1 px-3 font-mono" dir="ltr"><?php echo esc_html( (string) $row['hook'] ); ?></td>
								<td class="py-1 px-3"><?php echo (int) $row['events_before']; ?></td>
								<td class="py-1 px-3"><?php echo (int) $row['events_after']; ?></td>
								<td class="py-1 px-3"><?php echo esc_html( (string) $row['action'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		<?php endif; ?>
	</section>

	<section class="mb-8">
		<h2 class="text-lg font-semibold mb-3">Orphan hooks (باید ۰ event باشند)</h2>
		<ul class="text-sm font-mono space-y-1" dir="ltr">
			<?php foreach ( $orphans as $orphan ) : ?>
				<li><?php echo esc_html( $orphan ); ?> — events: <?php echo (int) ( function_exists( 'ez_cron_count_events' ) ? ez_cron_count_events( $orphan ) : 0 ); ?></li>
			<?php endforeach; ?>
		</ul>
	</section>

	<section class="mb-8">
		<h2 class="text-lg font-semibold mb-3">Health report</h2>
		<div class="overflow-x-auto border rounded">
			<table class="min-w-full text-sm">
				<thead class="bg-gray-100">
					<tr>
						<th class="py-2 px-3 text-right">Hook</th>
						<th class="py-2 px-3 text-right">OK</th>
						<th class="py-2 px-3 text-right">Detail</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $health as $row ) : ?>
						<tr class="border-t <?php echo $row['ok'] ? '' : 'bg-red-50'; ?>">
							<td class="py-1 px-3 font-mono" dir="ltr"><?php echo esc_html( (string) $row['hook'] ); ?></td>
							<td class="py-1 px-3"><?php echo $row['ok'] ? 'OK' : 'FAIL'; ?></td>
							<td class="py-1 px-3" dir="ltr"><?php echo esc_html( (string) $row['message'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</section>

	<section>
		<h2 class="text-lg font-semibold mb-3">Registry (<?php echo (int) count( $registry ); ?> jobs)</h2>
		<div class="overflow-x-auto border rounded">
			<table class="min-w-full text-xs">
				<thead class="bg-gray-100">
					<tr>
						<th class="py-2 px-2 text-right">Hook</th>
						<th class="py-2 px-2 text-right">Events</th>
						<th class="py-2 px-2 text-right">Paused</th>
						<th class="py-2 px-2 text-right">Callback</th>
						<th class="py-2 px-2 text-right">Schedule</th>
						<th class="py-2 px-2 text-right">Expected</th>
						<th class="py-2 px-2 text-right">Next run</th>
						<th class="py-2 px-2 text-right">Heartbeat</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( array_keys( $registry ) as $hook ) :
						$r = ez_aref6_hook_row( $hook, $registry, $heartbeats );
						$row_class = '';
						if ( $r['overdue'] || $r['events'] > 1 || $r['schedule'] !== $r['expected'] || $r['paused'] || ! $r['on_hook'] ) {
							$row_class = 'bg-amber-50';
						}
						if ( $r['paused'] || ! $r['on_hook'] ) {
							$row_class = 'bg-red-50';
						}
						?>
						<tr class="border-t <?php echo esc_attr( $row_class ); ?>">
							<td class="py-1 px-2 font-mono" dir="ltr"><?php echo esc_html( $r['hook'] ); ?></td>
							<td class="py-1 px-2"><?php echo (int) $r['events']; ?></td>
							<td class="py-1 px-2"><?php echo $r['paused'] ? 'YES' : '—'; ?></td>
							<td class="py-1 px-2" dir="ltr"><?php echo $r['on_hook'] ? 'yes' : 'NO'; ?> <span class="text-gray-500"><?php echo esc_html( $r['callback'] ); ?></span></td>
							<td class="py-1 px-2" dir="ltr"><?php echo esc_html( $r['schedule'] ); ?></td>
							<td class="py-1 px-2" dir="ltr"><?php echo esc_html( $r['expected'] ); ?></td>
							<td class="py-1 px-2" dir="ltr"><?php echo esc_html( $r['next'] ); ?><?php echo $r['overdue'] ? ' (OVERDUE)' : ''; ?></td>
							<td class="py-1 px-2" dir="ltr"><?php echo esc_html( $r['hb_ts'] ); ?> — <?php echo esc_html( $r['hb_status'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			</div>
	</section>

	<p class="text-xs text-gray-500 border-t pt-4 mt-8">
		CLI: <code dir="ltr">php wp-content/themes/escapezoom-v2/bin/ez-cron-health.php</code>
	</p>
</main>

<?php
get_footer();
