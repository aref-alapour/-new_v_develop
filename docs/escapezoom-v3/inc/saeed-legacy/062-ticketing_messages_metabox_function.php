<?php
/**
 * ticketing_messages_metabox_function
 *
 * توابع: ticketing_messages_metabox_function هوک‌ها: add_meta_boxes
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5516-5542)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'add_meta_boxes', 'ticketing_messages_metabox_function' ); // add meta box to specific post type
function ticketing_messages_metabox_function() {
    add_meta_box(
        'ticketing_messages_metabox_function',
        'پیام',
        'ticketing_messages_metabox_content_function',
        'ticketing',
        'normal',
        'high'
    );
    add_meta_box(
        'ticket_messages_metabox_function',
        'وضعیت تیکت',
        'ticket_status_function',
        'ticketing',
        'side',
        'high'
    );
    add_meta_box(
        'ticket_user_info_metabox_function',
        'اطلاعات کاربر',
        'user_info_function',
        'ticketing',
        'side',
        'high'
    );
}
