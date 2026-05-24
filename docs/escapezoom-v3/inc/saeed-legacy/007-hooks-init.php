<?php
/**
 * hooks: init
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 191-201)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'init',
	static function () {
		if ( get_option( 'ez_order_satisfaction_cron_cleared_v1', '' ) === '1' ) {
			return;
		}
		wp_clear_scheduled_hook( 'ez_satisfaction_on_comments_cron' );
		update_option( 'ez_order_satisfaction_cron_cleared_v1', '1', false );
	},
	5
);
