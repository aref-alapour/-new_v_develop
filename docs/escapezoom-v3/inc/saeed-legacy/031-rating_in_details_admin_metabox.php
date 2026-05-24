<?php
/**
 * rating_in_details_admin_metabox
 *
 * توابع: rating_in_details_admin_metabox هوک‌ها: add_meta_boxes
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3558-3561)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('add_meta_boxes', 'rating_in_details_admin_metabox');
function rating_in_details_admin_metabox() {
    add_meta_box('cld-count-info1', 'karen', 'rating_in_details_admin_html', 'comment', 'normal');
}
