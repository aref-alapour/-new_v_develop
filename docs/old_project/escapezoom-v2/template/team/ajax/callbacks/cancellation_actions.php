<?php
$user_id = get_current_user_id();

$function = isset($_POST['function']) ? sanitize_text_field($_POST['function']) : '';

if ($function === 'create_cancellation_request') {

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
