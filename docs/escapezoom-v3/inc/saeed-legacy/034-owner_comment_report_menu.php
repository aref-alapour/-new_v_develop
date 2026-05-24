<?php
/**
 * owner_comment_report_menu
 *
 * توابع: owner_comment_report_menu هوک‌ها: admin_menu
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3659-3670)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

//add_action('admin_menu', 'owner_comment_report_menu');
function owner_comment_report_menu() {
    $notification_count = 2; // <- here you should get correct count of forms submitted since last visit

    add_menu_page(
        'ریپورت نظرات',
        $notification_count ? sprintf('ریپورت نظرات<span class="awaiting-mod">%d</span>', $notification_count) : 'ریپورت نظرات',
        'administrator',
        'owner-comment-report',
        'owner_comment_report_page_handler'
    );
}
