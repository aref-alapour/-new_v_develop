<?php
/**
 * save_owner_comment_report_message
 *
 * توابع: save_owner_comment_report_message هوک‌ها: wp
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3671-3683)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('wp', 'save_owner_comment_report_message');
function save_owner_comment_report_message() {
/**
 * POST: cm_report_subject
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/035-save_owner_comment_report_message.php:16
 */
    if ( isset( $_POST['cm_report_subject'] ) && !empty( $_POST['cm_report_subject'] ) && isset( $_POST['cm_report_text'] ) && !empty( $_POST['cm_report_text'] ) ) {

/**
 * POST: cm_id
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/035-save_owner_comment_report_message.php:18
 */
        if ( isset( $_POST['cm_id'] ) && isset( $_POST['cm_roomid'] ) )

            $comment_id = htmlspecialchars( $_POST['cm_id'] );
        $subject    = htmlspecialchars( $_POST['cm_report_subject'] );
        $content    = htmlspecialchars( $_POST['cm_report_text'] );

        add_comment_meta( $comment_id, 'owner_report', [$subject, $content], true );
    }
}
