<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $wpdb;

$user_id    = get_current_user_id();
$user       = wp_get_current_user();
$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
$content    = isset( $_POST['content'] ) ? sanitize_text_field( wp_unslash( $_POST['content'] ) ) : '';
$rate_raw   = isset( $_POST['rate'] ) ? wp_unslash( $_POST['rate'] ) : [];

if ( $user_id < 1 ) {
	ez_send_product_review_error( 'auth', 'برای ثبت دیدگاه وارد حساب کاربری شوید.' );
}

$author_name = (string) $user->billing_phone;
if ( $user->user_firstname ) {
	$author_name = trim( $user->user_firstname . ' ' . ( $user->user_lastname ?: '' ) );
}
$author_mail = $user->user_email;

$user_phone_10 = ez_user_billing_phone_10( $user );

if ( empty( $product_id ) ) {
	ez_send_product_review_error( 'no_product', 'شماره محصول مشخص نیست.' );
}
if ( $content === '' ) {
	ez_send_product_review_error( 'no_content', 'پاسخ شما مشخص نیست.' );
}
if ( empty( $rate_raw ) || ! is_array( $rate_raw ) ) {
	ez_send_product_review_error( 'no_rate', 'امتیازات مشخص نیست.' );
}

$row = ez_resolve_user_latest_valid_markting_row( $user_id, $product_id, $user_phone_10 );
if ( is_wp_error( $row ) ) {
	ez_send_product_review_error( 'not_buyer', $row->get_error_message() );
}

$booking_ts = ez_markting_row_booking_timestamp( $row );
$tw         = ez_product_review_time_window_ok( (int) $booking_ts );
if ( is_wp_error( $tw ) ) {
	$code = $tw->get_error_code();
	$map  = [
		'bad_booking'     => 'bad_booking',
		'comment_too_soon' => 'too_soon',
		'comment_expired'  => 'expired',
	];
	ez_send_product_review_error( $map[ $code ] ?? 'time_window', $tw->get_error_message() );
}

$existing_review_id = ez_get_user_product_review_comment_id( $user_id, $product_id );
if ( $existing_review_id > 0 ) {
	ez_send_product_review_error(
		'already_reviewed',
		'شما قبلاً برای این بازی دیدگاه ثبت کرده‌اید. می‌توانید دیدگاه قبلی خود را از بخش «دیدگاه‌های من» در پنل کاربری یا همین صفحه ویرایش کنید.',
		[ 'comment_id' => $existing_review_id ]
	);
}

$missing_1097 = ez_review_rate_missing_1097( $rate_raw );
$rate         = ez_normalize_review_rate_array( $rate_raw );
$rate_average = ez_compute_review_rate_average( $rate, $missing_1097 );

$swear     = ez_apply_swear_moderation( $content );
$swear_flag = $swear['flag'];

$approved = $swear_flag ? '0' : '1';

$comment_id = wp_insert_comment(
	[
		'comment_post_ID'      => $product_id,
		'comment_author'       => $author_name,
		'comment_author_email' => $author_mail,
		'comment_content'      => $content,
		'comment_type'         => 'review',
		'comment_parent'       => 0,
		'user_id'              => $user_id,
		'comment_approved'     => $approved,
		'comment_date'         => current_time( 'mysql' ),
		'comment_date_gmt'     => current_time( 'mysql', 1 ),
	]
);

if ( ! $comment_id || is_wp_error( $comment_id ) ) {
	ez_send_product_review_error( 'insert_failed', 'کامنت شما ثبت نشد دوباره تلاش کنید' );
}

if ( ! function_exists( 'ez_crm_comment_audit_insert' ) && defined( 'Theme_PATH' ) ) {
	$audit_file = Theme_PATH . 'inc/admin/team/functions/crm_comment_audit_log.php';
	if ( file_exists( $audit_file ) ) {
		require_once $audit_file;
	}
}

if ( (string) $approved !== '1' && function_exists( 'ez_crm_comment_audit_insert' ) && function_exists( 'ez_crm_comment_audit_row_from_comment_system' ) ) {
	$comment_for_audit = get_comment( (int) $comment_id );
	if ( $comment_for_audit instanceof WP_Comment ) {
		$swear_snapshot = array(
			'swear_flag' => (bool) $swear_flag,
			'moderation' => is_array( $swear ) ? $swear : array(),
		);
		$details = function_exists( 'ez_crm_comment_audit_build_status_transition_text' )
			? ez_crm_comment_audit_build_status_transition_text( 'new', (string) $comment_for_audit->comment_approved )
			: '';
		$details .= "\nمبدا: سیستم تشخیص محتوای نامناسب\nخلاصه بررسی: " . wp_json_encode( $swear_snapshot, JSON_UNESCAPED_UNICODE );

		ez_crm_comment_audit_insert(
			array_merge(
				ez_crm_comment_audit_row_from_comment_system( $comment_for_audit ),
				array(
					'action'          => 'auto_hold',
					'approve_subtype' => 'hold',
					'reason'          => 'تشخیص خودکار محتوای نامناسب',
					'details'         => $details,
				)
			)
		);
	}
}

$user_level = function_exists( 'ez_comment_stored_user_level_for_new_review' )
	? ez_comment_stored_user_level_for_new_review( (int) $user_id )
	: ( function_exists( 'ez_user_effective_feature_level' ) ? ez_user_effective_feature_level( (int) $user_id ) : (int) get_user_level( $user_id ) );

add_comment_meta( $comment_id, 'comment_rating', $rate, true );
add_comment_meta( $comment_id, 'rating', $rate_average, true );
add_comment_meta( $comment_id, 'comment_offer', $product_id, true );
add_comment_meta( $comment_id, 'user_level', $user_level, true );

// wp_insert_comment does not fire transition_comment_status; approved reviews must update aggregates here.
if ( '1' === (string) $approved ) {
	ez_product_review_add_totals_for_comment( (int) $comment_id );
	if ( function_exists( 'ez_order_satisfaction_sync_comment_effect' ) ) {
		ez_order_satisfaction_sync_comment_effect( (int) $comment_id, 'product_add_comment' );
	}
}

$w_comments = function_exists( 'ez_comment_stored_user_level_to_rating_power' )
	? ez_comment_stored_user_level_to_rating_power( (int) $user_level )
	: (int) ( ez_product_review_power_map()[ $user_level ] ?? 1 );
$wpdb->insert(
	'hottest_products',
	[
		'product_id'       => $product_id,
		'comment_id'       => (int) $comment_id,
		'w_rate'           => (int) round( (float) $rate_average ),
		'w_comments_count' => (int) $w_comments,
		'time'             => time(),
	],
	[ '%d', '%d', '%d', '%d', '%d' ]
);

do_action( 'ez_ranking_recalculate', $product_id, [ 'hottest' ] );

$blacklist     = get_post_meta( $product_id, 'comments_blacklist', true ) ?: [];
$blacklist_new = get_post_meta( $product_id, 'comments_blacklist_new', true ) ?: [];
if ( ! in_array( $user->user_login, $blacklist, true ) ) {
	$blacklist[] = $user->user_login;
}
if ( ! in_array( $user->ID, $blacklist_new, true ) ) {
	$blacklist_new[] = $user->ID;
}
update_post_meta( $product_id, 'comments_blacklist', $blacklist );
update_post_meta( $product_id, 'comments_blacklist_new', $blacklist_new );

if ( function_exists( 'add_point' ) ) {
	$game_name = ! empty( $row->game_name ) ? $row->game_name : get_the_title( $product_id );
	add_point( 'submit-comment', $user_id, 'ثبت نظر برای ' . $game_name );
}

$msg = $swear_flag
	? 'دیدگاه شما با موفقیت ثبت شد و پس از تایید مدیریت نمایش داده خواهد شد.'
	: 'دیدگاه شما با موفقیت ثبت شد.';

wp_send_json_success(
	[
		'message'    => $msg,
		'comment_id' => (int) $comment_id,
	]
);
