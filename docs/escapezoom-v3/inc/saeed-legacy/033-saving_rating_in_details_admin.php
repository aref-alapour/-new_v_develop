<?php
/**
 * saving_rating_in_details_admin
 *
 * توابع: saving_rating_in_details_admin هوک‌ها: edit_comment
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3637-3658)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('edit_comment', 'saving_rating_in_details_admin');
function saving_rating_in_details_admin($comment_id) {
    $nonce_name     = isset($_POST['cld_metabox_nonce1_field']) ? $_POST['cld_metabox_nonce1_field'] : '';
    $nonce_action   = 'cld_metabox_nonce1';

    if (!wp_verify_nonce($nonce_name, $nonce_action))
        return;

/**
 * POST: fazasazi
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/033-saving_rating_in_details_admin.php:22
 */
    if (isset($_POST['fazasazi'], $_POST['moama'])) {

        update_comment_meta($comment_id, 'comment_rating', [
            '1094' => $_POST['fazasazi'] * 20,
            '1095' => $_POST['moama'] * 20,
            '1098' => $_POST['tazegi'] * 20,
            '1096' => $_POST['act'] * 20,
            '1097' => $_POST['personel'] * 20,
        ]);

        $comment = get_comment($comment_id);
        if (
            $comment
            && $comment->comment_type === 'review'
            && (string) $comment->comment_approved === '1'
            && function_exists('ez_product_ranking_sync_after_review_change')
        ) {
            ez_product_ranking_sync_after_review_change((int) $comment->comment_post_ID);
        }

        return $comment_id;
    } else
        return $comment_id;
}
