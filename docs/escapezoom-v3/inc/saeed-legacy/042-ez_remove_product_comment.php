<?php
/**
 * ez_remove_product_comment
 *
 * توابع: ez_remove_product_comment هوک‌ها: trash_comment
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3868-3916)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'trash_comment', 'ez_remove_product_comment' );

function ez_remove_product_comment( $comment_id ) {

	$comment = get_comment( $comment_id );
	if ( ! $comment ) {
		return;
	}

	if ( (string) $comment->comment_approved !== '1' ) {
		return;
	}

	if ( ! ez_product_review_comment_applies_to_totals( $comment ) ) {
		return;
	}

	ez_product_review_remove_totals_for_comment( (int) $comment_id );
}
