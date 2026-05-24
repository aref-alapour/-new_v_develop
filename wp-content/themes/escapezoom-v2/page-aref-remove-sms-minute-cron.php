<?php
/**
 * Template Name: aref-remove-sms-minute-cron
 *
 * یک‌بار: حذف کامل ez_sms_queue_every_minute_cron از wp-cron و optionها.
 * فقط مدیرکل. بعد از اجرا این برگه را حذف کنید یا قالب را عوض کنید.
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_die( 'دسترسی ندارید.', 'Forbidden', array( 'response' => 403 ) );
}

$hook = 'ez_sms_queue_every_minute_cron';

$before_events = function_exists( 'ez_cron_count_events' )
	? ez_cron_count_events( $hook )
	: ( wp_next_scheduled( $hook ) ? 1 : 0 );

wp_clear_scheduled_hook( $hook );

$paused = get_option( 'wp_crontrol_paused', array() );
if ( is_array( $paused ) && array_key_exists( $hook, $paused ) ) {
	unset( $paused[ $hook ] );
	update_option( 'wp_crontrol_paused', $paused, true );
}

$heartbeats = (array) get_option( 'ez_cron_heartbeats', array() );
if ( isset( $heartbeats[ $hook ] ) ) {
	unset( $heartbeats[ $hook ] );
	update_option( 'ez_cron_heartbeats', $heartbeats, false );
}

$after_events = function_exists( 'ez_cron_count_events' )
	? ez_cron_count_events( $hook )
	: ( wp_next_scheduled( $hook ) ? 1 : 0 );

$still_paused = false;
$paused_after = get_option( 'wp_crontrol_paused', array() );
if ( is_array( $paused_after ) && array_key_exists( $hook, $paused_after ) ) {
	$still_paused = true;
}

get_header();
?>

<main class="container mx-auto px-4 py-8 max-w-xl" dir="rtl">
	<h1 class="text-2xl font-bold mb-4">حذف کرون SMS یک‌دقیقه‌ای</h1>

	<p class="text-sm text-gray-600 mb-4">
		Hook: <code dir="ltr"><?php echo esc_html( $hook ); ?></code>
	</p>

	<div class="text-sm space-y-2 border rounded p-4 bg-green-50 border-green-200">
		<p><strong>انجام شد.</strong></p>
		<ul class="list-disc pr-5 space-y-1" dir="ltr">
			<li>scheduled events before: <?php echo (int) $before_events; ?></li>
			<li>scheduled events after: <?php echo (int) $after_events; ?></li>
			<li>wp_clear_scheduled_hook: called</li>
			<li>wp_crontrol_paused entry: <?php echo $still_paused ? 'still present' : 'removed or was absent'; ?></li>
			<li>ez_cron_heartbeats entry: removed if existed</li>
		</ul>
	</div>

	<p class="text-sm text-amber-800 bg-amber-50 border border-amber-200 rounded p-3 mt-6">
		کد این کرون از <code dir="ltr">template/func/cron.php</code> هم حذف شده؛ با لود مجدد سایت دوباره schedule نمی‌شود.
		این برگه را بعد از تأیید حذف کنید.
	</p>

	<p class="text-sm mt-4">
		<a class="text-blue-600 underline" href="<?php echo esc_url( admin_url( 'tools.php?page=wp-crontrol' ) ); ?>">WP Crontrol</a>
		— مطمئن شوید event دیگر در لیست نیست.
	</p>
</main>

<?php
get_footer();
