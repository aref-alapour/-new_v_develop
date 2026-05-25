<?php
/**
 * HTML partial: reservable sessions for one day (gateway booking.sans_day).
 *
 * @var int   $product_id
 * @var int   $day_start_time
 * @var array $sessions List of session rows (time, price, off_price, status).
 */
defined( 'ABSPATH' ) || exit;

$sessions = isset( $sessions ) && is_array( $sessions ) ? $sessions : array();
$reservable = array();
foreach ( $sessions as $item ) {
	if ( ! is_array( $item ) ) {
		continue;
	}
	if ( ( $item['status'] ?? '' ) === 'reservable' ) {
		$reservable[] = $item;
	}
}

if ( empty( $reservable ) ) : ?>
	<div class="w-full aspect-square bg-slate-100 shadow-13 rounded-2xl flex flex-col text-center items-center justify-center text-slate-350 leading-5 text-lg p-4">
		<p>در این روز سانس خالی نداریم!<br> روز دیگه‌ای انتخاب کن.</p>
	</div>
<?php
	return;
endif;

foreach ( $reservable as $item ) :
	$ts    = (int) ( $item['time'] ?? 0 );
	$price = (int) ( $item['price'] ?? 0 );
	$off   = (int) ( $item['off_price'] ?? 0 );
	$hour  = function_exists( 'wp_date' ) ? wp_date( 'H:i', $ts, new DateTimeZone( 'Asia/Tehran' ) ) : date( 'H:i', $ts );
	$h     = (int) substr( $hour, 0, 2 );
	$is_vip = ( $h >= 0 && $h < 8 );
	$border = $is_vip ? '#BF9A00' : '#5091FB';
	?>
	<div class="session-item" data-session-time="<?php echo esc_attr( (string) $ts ); ?>" data-session-price="<?php echo esc_attr( (string) ( $off > 0 ? $off : $price ) ); ?>">
		<div class="relative mb-2.5 flex cursor-pointer items-center justify-between rounded-[10px] transition-all overflow-hidden" style="border:1px solid <?php echo esc_attr( $border ); ?>;color:<?php echo esc_attr( $border ); ?>">
			<span class="text-right text-2xl px-4 py-3">
				<?php echo esc_html( $hour ); ?>
				<?php if ( $is_vip ) : ?><span class="mr-2 text-lg">بامداد VIP</span><?php endif; ?>
			</span>
			<span class="text-lg px-4 py-3 absolute left-0 top-0 flex items-center justify-center h-full flex-col leading-4">
				<?php if ( $off > 0 && $off !== $price ) : ?>
					<span class="text-[#5091FB] text-lg font-bold"><?php echo esc_html( number_format_i18n( $off ) ); ?></span>
					<span class="text-xs text-[#889BAD] line-through"><?php echo esc_html( number_format_i18n( $price ) ); ?> تومان</span>
				<?php else : ?>
					<span><?php echo esc_html( number_format_i18n( $price ) ); ?> <span class="mr-1.5 text-2xs opacity-70">تومان</span></span>
				<?php endif; ?>
			</span>
		</div>
	</div>
<?php endforeach; ?>
