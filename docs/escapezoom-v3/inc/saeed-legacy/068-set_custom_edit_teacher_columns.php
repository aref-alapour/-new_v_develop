<?php
/**
 * set_custom_edit_teacher_columns
 *
 * توابع: set_custom_edit_teacher_columns هوک‌ها: manage_ticketing_posts_columns
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5849-5860)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'manage_ticketing_posts_columns', 'set_custom_edit_teacher_columns' );
function set_custom_edit_teacher_columns($columns) {
    unset($columns['date']);

    $columns['type']                = 'نوع';
    $columns['status']              = 'وضعیت';
    $columns['last_message']        = 'آخرین پیام';
    $columns['last_message_date']   = 'تاریخ آخرین پیام';
    $columns['date']                = 'تاریخ ایجاد';

    return $columns;
}
