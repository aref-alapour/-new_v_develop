<?php
/**
 * my_approve_comment_callback
 *
 * توابع: my_approve_comment_callback هوک‌ها: transition_comment_status
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 3917-3992)
 * نوع: توابع/هوک‌های دائمی
 *
 * تجمع امتیاز از ez_product_review_* برای هم‌ترازی با وزن مجموعه‌دار (سطح ۱۰) و rollup رابطه‌ای.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'transition_comment_status', 'my_approve_comment_callback', 10, 3 );

function my_approve_comment_callback( $new_status, $old_status, $comment ) {

	if ( $old_status === $new_status ) {
		return;
	}

	if ( ! $comment instanceof WP_Comment ) {
		return;
	}

	if ( ! ez_product_review_comment_applies_to_totals( $comment ) ) {
		return;
	}

	$comment_id = (int) $comment->comment_ID;

	if ( $new_status === 'approved' ) {
		ez_product_review_add_totals_for_comment( $comment_id );

		return;
	}

	if ( $new_status === 'unapproved' ) {
		ez_product_review_remove_totals_for_comment( $comment_id );
	}
}
