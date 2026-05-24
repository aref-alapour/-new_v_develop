<?php
global $wpdb;

$user = wp_get_current_user();

$status = sanitize_text_field($_POST['status']);
$id     = (int) sanitize_text_field($_POST['id']);

if (empty($status) || !in_array($status, ['pending', 'approved', 'declined'])) {
	wp_send_json_error("وضعیت نامعتبر است.");
}

if (empty($id)) {
	wp_send_json_error("خطا در دریافت اطلاعات دعوت نامه.");
}

$invitation = $wpdb->get_results($wpdb->prepare("SELECT * FROM invitations WHERE invited_id LIKE %d AND ID LIKE %d", $user->ID, $id))[0];

if (empty($invitation)) {
	wp_send_json_error("این دعوت نامه برای شما نیست.");
}

// Get additional data for tracking before updating
$product_obj = wc_get_product($invitation->product_id);
$inviter_user = get_user_by('id', $invitation->inviter_id);

$query = $wpdb->update(
	'invitations',
	['status' => $status],
	['ID' => $id],
);

if (is_wp_error($query)) {
	wp_send_json_error('خطایی پیش آمده لطفا دوباره امتحان کنید.');
}

// Prepare tracking data for Zabalin
$tracking_data = [
	'invitation_id' => $id,
	'inviter_id' => $invitation->inviter_id,
	'inviter_phone' => $inviter_user ? $inviter_user->user_login : '',
	'invited_id' => $user->ID,
	'invited_phone' => $user->user_login,
	'product_id' => $invitation->product_id,
	'product_name' => $product_obj ? $product_obj->get_name() : '',
	'product_type' => $product_obj ? wp_get_post_terms($invitation->product_id, 'product_cat', ['fields' => 'names']) : [],
	'status' => $status,
	'response_date' => date('Y-m-d H:i:s'),
	'invitation_date' => date('Y-m-d H:i:s', $invitation->created_at),
	'current_page' => $_SERVER['HTTP_REFERER'] ?? '',
	'action' => $status == 'approved' ? 'accept' : 'decline'
];

wp_send_json_success([
	'message' => $status == 'approved' ? 'شما دعوت را پذیرفتید' : 'شما دعوت را نپذرفتید',
	'tracking_data' => $tracking_data
]);
