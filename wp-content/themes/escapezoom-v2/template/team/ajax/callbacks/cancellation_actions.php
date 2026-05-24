<?php
$user_id = get_current_user_id();

$function = isset($_POST['function']) ? sanitize_text_field($_POST['function']) : '';

if ($function === 'direct_cancellation_refund') {

    if (!function_exists('ez_team_user_can_direct_cancellation_refund') || !ez_team_user_can_direct_cancellation_refund($user_id)) {
        wp_send_json_error('شما اجازه این عملیات را ندارید.');
    }

    $order_id       = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $requester_type = isset($_POST['requester_type']) ? sanitize_text_field($_POST['requester_type']) : '';
    $reason_id      = isset($_POST['reason_id']) && $_POST['reason_id'] !== '' ? intval($_POST['reason_id']) : null;

    if (!$order_id || !$requester_type) {
        wp_send_json_error('ورودی‌های نامعتبر.');
    }

    $result = ez_team_direct_cancellation_refund($order_id, $requester_type, $user_id, $reason_id);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    }

    wp_send_json_success('کنسلی و استرداد با موفقیت ثبت شد.');

} elseif ( $function === 'master_refund' ) {

    if ( ! function_exists( 'ez_team_user_can_direct_cancellation_refund' ) || ! ez_team_user_can_direct_cancellation_refund( $user_id ) ) {
        wp_send_json_error( 'شما اجازه این عملیات را ندارید.' );
    }

    $order_id = isset( $_POST['order_id'] ) ? intval( $_POST['order_id'] ) : 0;
    if ( ! $order_id ) {
        wp_send_json_error( 'شناسه سفارش نامعتبر است.' );
    }

    if ( ! function_exists( 'ez_team_master_refund' ) ) {
        wp_send_json_error( 'تابع مسترد در دسترس نیست.' );
    }

    $result = ez_team_master_refund( $order_id, $user_id );

    if ( is_wp_error( $result ) ) {
        wp_send_json_error( $result->get_error_message() );
    }

    wp_send_json_success( 'سفارش مسترد شد؛ سانس باز و مبلغ به کیف پول پلیر برگشت (بدون پیامک).' );

} elseif ($function === 'create_cancellation_request') {

    $order_id       = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $reason_id      = isset($_POST['reason_id']) ? intval($_POST['reason_id']) : null;
    $requester_type = isset($_POST['requester_type']) ? sanitize_text_field($_POST['requester_type']) : '';
    $requester_id   = $user_id;

    if (!$order_id || !$requester_id || ($requester_type === 'owner' && !$reason_id))
        wp_send_json_error('ورودی‌های نامعتبر.');

    $result = create_cancellation_request($order_id, $requester_id, $requester_type, $reason_id);

    if (is_wp_error($result))
        wp_send_json_error($result->get_error_message());
    else
        wp_send_json_success('درخواست کنسلی با موفقیت ثبت شد.');

} elseif ($function === 'update_cancellation_status') {

    $reqid = isset($_POST['reqid']) ? intval($_POST['reqid']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

    if (!$reqid || !$status) {
        wp_send_json_error('ورودی‌های نامعتبر.');
    }

    // Convert status to action format expected by process_cancellation_request
    $action = ($status === 'approved') ? 'approve' : 'reject';

    // Use the existing process_cancellation_request function
    $result = process_cancellation_request($reqid, $user_id, $action);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
    } else {
        if ($status === 'approved') {
            wp_send_json_success('درخواست لغو با موفقیت تایید شد و سفارش بازگردانده شد.');
        } else {
            wp_send_json_success('درخواست لغو با موفقیت رد شد.');
        }
    }

} else {
    wp_send_json_error('عمل نامعتبر.');
}
