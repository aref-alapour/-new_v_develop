<?php
/**
 * remove_footer_admin
 *
 * توابع: remove_footer_admin هوک‌ها: admin_footer_text
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6106-6109)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('admin_footer_text', 'remove_footer_admin');
function remove_footer_admin () {
    echo '<span id="footer-thankyou"></a></span>';
}
