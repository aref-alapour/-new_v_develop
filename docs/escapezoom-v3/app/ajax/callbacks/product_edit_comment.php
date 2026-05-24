<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$user_id = get_current_user_id();
if ( $user_id < 1 ) {
	ez_send_product_review_error( 'auth', 'برای ویرایش دیدگاه وارد حساب کاربری شوید.' );
}

$comment_id = isset( $_POST['comment_id'] ) ? (int) $_POST['comment_id'] : 0;
$content    = isset( $_POST['content'] ) ? sanitize_text_field( wp_unslash( $_POST['content'] ) ) : '';
$rate_raw   = isset( $_POST['rate'] ) ? wp_unslash( $_POST['rate'] ) : [];

if ( $comment_id < 1 ) {
	ez_send_product_review_error( 'no_comment', 'شناسه دیدگاه مشخص نیست.' );
}
if ( $content === '' ) {
	ez_send_product_review_error( 'no_content', 'پاسخ شما مشخص نیست.' );
}
if ( empty( $rate_raw ) || ! is_array( $rate_raw ) ) {
	ez_send_product_review_error( 'no_rate', 'امتیازات مشخص نیست.' );
}

$comment = get_comment( $comment_id );
if ( ! $comment ) {
	ez_send_product_review_error( 'not_found', 'دیدگاه یافت نشد.' );
}
if ( (int) $comment->user_id !== $user_id ) {
	ez_send_product_review_error( 'forbidden', 'اجازه ویرایش این دیدگاه را ندارید.' );
}
if ( $comment->comment_type !== 'review' ) {
	ez_send_product_review_error( 'invalid_type', 'این مورد قابل ویرایش نیست.' );
}
$product_id = (int) $comment->comment_post_ID;
if ( get_post_type( $product_id ) !== 'product' ) {
	ez_send_product_review_error( 'invalid_product', 'محصول نامعتبر است.' );
}

$user = wp_get_current_user();
$gate = ez_user_may_review_product_in_window( $user_id, $product_id, $user );
if ( is_wp_error( $gate ) ) {
	$code = $gate->get_error_code();
	$map  = [
		'not_buyer'        => 'not_buyer',
		'bad_booking'      => 'bad_booking',
		'comment_too_soon' => 'too_soon',
		'comment_expired'  => 'expired',
	];
	ez_send_product_review_error( $map[ $code ] ?? 'time_window', $gate->get_error_message() );
}

$missing_1097   = ez_review_rate_missing_1097( $rate_raw );
$rate           = ez_normalize_review_rate_array( $rate_raw );
$rate_average   = ez_compute_review_rate_average( $rate, $missing_1097 );
$swear          = ez_apply_swear_moderation( $content );
$new_swear_flag = $swear['flag'];

if ( ! function_exists( 'ez_crm_comment_audit_insert' ) && defined( 'Theme_PATH' ) ) {
	$audit_file = Theme_PATH . 'inc/admin/team/functions/crm_comment_audit_log.php';
	if ( file_exists( $audit_file ) ) {
		require_once $audit_file;
	}
}

$was_approved = ( $comment->comment_approved === '1' );

$old_rating = get_comment_meta( $comment_id, 'comment_rating', true );
if ( ! is_array( $old_rating ) ) {
	$old_rating = array_fill_keys( ez_get_product_review_rate_keys(), 0 );
}
$old_level = (int) get_comment_meta( $comment_id, 'user_level', true );
$old_power = function_exists( 'ez_comment_stored_user_level_to_rating_power' )
	? ez_comment_stored_user_level_to_rating_power( $old_level )
	: ( ez_product_review_power_map()[ $old_level ] ?? 1 );

$new_level = function_exists( 'ez_comment_stored_user_level_for_new_review' )
	? ez_comment_stored_user_level_for_new_review( (int) $user_id )
	: ( function_exists( 'ez_user_effective_feature_level' ) ? ez_user_effective_feature_level( (int) $user_id ) : (int) get_user_level( $user_id ) );
$new_power = function_exists( 'ez_comment_stored_user_level_to_rating_power' )
	? ez_comment_stored_user_level_to_rating_power( (int) $new_level )
	: (int) get_user_rating_power( $user_id );

if ( $was_approved && $new_swear_flag ) {
	wp_set_comment_status( $comment_id, 'hold' );
	wp_update_comment(
		[
			'comment_ID'      => $comment_id,
			'comment_content' => $content,
		]
	);
	update_comment_meta( $comment_id, 'comment_rating', $rate );
	update_comment_meta( $comment_id, 'rating', $rate_average );
	update_comment_meta( $comment_id, 'user_level', $new_level );

	$comment_hold_after = get_comment( (int) $comment_id );
	if ( $comment_hold_after instanceof WP_Comment && (string) $comment_hold_after->comment_approved !== '1' && function_exists( 'ez_crm_comment_audit_insert' ) && function_exists( 'ez_crm_comment_audit_row_from_comment_system' ) ) {
		$swear_snapshot = array(
			'swear_flag' => (bool) $new_swear_flag,
			'moderation' => is_array( $swear ) ? $swear : array(),
		);
		$details = function_exists( 'ez_crm_comment_audit_build_status_transition_text' )
			? ez_crm_comment_audit_build_status_transition_text( '1', (string) $comment_hold_after->comment_approved )
			: '';
		$details .= "\nمبدا: سیستم تشخیص محتوای نامناسب (ویرایش)\nخلاصه بررسی: " . wp_json_encode( $swear_snapshot, JSON_UNESCAPED_UNICODE );

		ez_crm_comment_audit_insert(
			array_merge(
				ez_crm_comment_audit_row_from_comment_system( $comment_hold_after ),
				array(
					'action'          => 'auto_hold',
					'approve_subtype' => 'hold',
					'reason'          => 'تشخیص خودکار محتوای نامناسب',
					'details'         => $details,
				)
			)
		);
	}
} elseif ( $was_approved && ! $new_swear_flag ) {
	ez_product_review_apply_rating_delta_approved( $product_id, $old_rating, $old_power, $rate, $new_power );
	wp_update_comment(
		[
			'comment_ID'      => $comment_id,
			'comment_content' => $content,
		]
	);
	update_comment_meta( $comment_id, 'comment_rating', $rate );
	update_comment_meta( $comment_id, 'rating', $rate_average );
	update_comment_meta( $comment_id, 'user_level', $new_level );
} elseif ( ! $was_approved && $new_swear_flag ) {
	wp_update_comment(
		[
			'comment_ID'      => $comment_id,
			'comment_content' => $content,
		]
	);
	update_comment_meta( $comment_id, 'comment_rating', $rate );
	update_comment_meta( $comment_id, 'rating', $rate_average );
	update_comment_meta( $comment_id, 'user_level', $new_level );
} else {
	wp_update_comment(
		[
			'comment_ID'      => $comment_id,
			'comment_content' => $content,
		]
	);
	update_comment_meta( $comment_id, 'comment_rating', $rate );
	update_comment_meta( $comment_id, 'rating', $rate_average );
	update_comment_meta( $comment_id, 'user_level', $new_level );
	wp_set_comment_status( $comment_id, 'approve' );
}

$w_comments = function_exists( 'ez_comment_stored_user_level_to_rating_power' )
	? ez_comment_stored_user_level_to_rating_power( (int) $new_level )
	: ( ez_product_review_power_map()[ $new_level ] ?? 1 );

$hottest_row = $wpdb->get_var( $wpdb->prepare( 'SELECT comment_id FROM hottest_products WHERE comment_id = %d LIMIT 1', $comment_id ) );
if ( $hottest_row ) {
	$wpdb->update(
		'hottest_products',
		[
			'w_rate'           => (int) round( (float) $rate_average ),
			'w_comments_count' => (int) $w_comments,
			'time'             => time(),
		],
		[ 'comment_id' => $comment_id ],
		[ '%d', '%d', '%d' ],
		[ '%d' ]
	);
} else {
	$wpdb->insert(
		'hottest_products',
		[
			'product_id'       => $product_id,
			'comment_id'       => $comment_id,
			'w_rate'           => (int) round( (float) $rate_average ),
			'w_comments_count' => (int) $w_comments,
			'time'             => time(),
		],
		[ '%d', '%d', '%d', '%d', '%d' ]
	);
}

do_action( 'ez_ranking_recalculate', $product_id, [ 'hottest' ] );

	if ( function_exists( 'ez_order_satisfaction_sync_comment_effect' ) ) {
		ez_order_satisfaction_sync_comment_effect( (int) $comment_id, 'product_edit_comment' );
	}

	if ( class_exists( Ez_Product_Rating_Rollup_Service::class ) ) {
		Ez_Product_Rating_Rollup_Service::instance()->sync_storage_from_comment_meta( (int) $comment_id );
	}

	$msg = $new_swear_flag
	? 'دیدگاه شما به‌روزرسانی شد و پس از تایید مدیریت نمایش داده خواهد شد.'
	: 'دیدگاه شما با موفقیت به‌روزرسانی شد.';

wp_send_json_success(
	[
		'message'    => $msg,
		'comment_id' => (int) $comment_id,
	]
);
