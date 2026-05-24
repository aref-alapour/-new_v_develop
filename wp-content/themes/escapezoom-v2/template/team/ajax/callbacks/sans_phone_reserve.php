<?php
/**
 * Team AJAX: phone reservation quote + submit.
 */

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'ez_team_user_can_phone_reservation' ) ) {
	wp_send_json_error( 'ماژول رزرو تلفنی بارگذاری نشده است.' );
}

if ( ! ez_team_user_can_phone_reservation() ) {
	wp_send_json_error( 'شما اجازه رزرو تلفنی را ندارید.' );
}

$operation    = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : 'reserve';
$product_id   = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
$sans_ts      = isset( $_POST['sans_time'] ) ? (int) $_POST['sans_time'] : 0;
$quantity     = isset( $_POST['quantity'] ) ? (int) $_POST['quantity'] : 1;
$payment_type = isset( $_POST['payment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_type'] ) ) : 'partial';
$user_id      = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
$phone        = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
$first_name   = isset( $_POST['first_name'] ) ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) ) : '';
$last_name    = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : '';
$note         = isset( $_POST['note'] ) ? sanitize_textarea_field( wp_unslash( $_POST['note'] ) ) : '';
$actor_id     = (int) get_current_user_id();

if ( $product_id <= 0 || $sans_ts <= 0 ) {
	wp_send_json_error( 'بازی یا زمان سانس نامعتبر است.' );
}

if ( $operation === 'quote' ) {
	$quote = ez_team_phone_reservation_quote( $product_id, $sans_ts, max( 1, $quantity ) );
	if ( is_wp_error( $quote ) ) {
		wp_send_json_error( $quote->get_error_message() );
	}
	wp_send_json_success( $quote );
}

$player_was_created = false;

if ( $user_id <= 0 ) {
	if ( $phone === '' ) {
		wp_send_json_error( 'لطفاً پلیر را از لیست انتخاب کنید یا شماره موبایل معتبر وارد کنید.' );
	}

	$resolved = ez_team_resolve_or_create_player_by_phone( $phone, $first_name, $last_name, $actor_id );
	if ( is_wp_error( $resolved ) ) {
		wp_send_json_error( $resolved->get_error_message() );
	}

	$user_id            = (int) $resolved['user_id'];
	$player_was_created = ! empty( $resolved['created'] );
}

$result = ez_team_create_phone_reservation(
	$product_id,
	$sans_ts,
	$user_id,
	max( 1, $quantity ),
	$payment_type,
	$actor_id,
	$note,
	$player_was_created
);

if ( is_wp_error( $result ) ) {
	wp_send_json_error( $result->get_error_message() );
}

if ( empty( $result['success'] ) ) {
	wp_send_json_error( $result['message'] ?? 'رزرو ناموفق بود.' );
}

$message = $result['message'] ?? 'رزرو ثبت شد.';
if ( $player_was_created ) {
	$message = 'حساب مشتری جدید ساخته شد. ' . $message;
}

wp_send_json_success(
	array(
		'order_id'      => (int) ( $result['order_id'] ?? 0 ),
		'user_id'       => $user_id,
		'user_created'  => $player_was_created,
		'code'          => $result['code'] ?? 'paid',
		'message'       => $message,
	)
);
