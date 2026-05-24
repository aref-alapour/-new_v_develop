<?php
global $wpdb;

$user    = sanitize_text_field($_POST['user']);
$product = sanitize_text_field($_POST['product']);

if (! user_features_access('invitation')) {
	wp_send_json_error('شما به سقف دعوت روزانه خود رسیده اید.');
}

if (empty($user)) {
	wp_send_json_error('آی دی کاربر نامعتبر است.');
}

if (empty($product)) {
	wp_send_json_error('محصول نامعتبر است.');
}

// Get product and user details for tracking before insertion
$product_obj = wc_get_product($product);
$invited_user = get_user_by('id', $user);
$inviter_user = wp_get_current_user();

$query = $wpdb->insert('invitations', [
	'inviter_id' => get_current_user_id(),
	'invited_id' => $user,
	'product_id' => (int) $product,
	'status'     => 'pending',
	'created_at' => time(),
]);

if (is_wp_error($query)) {
	wp_send_json_error('خطایی پیش آمده لطفا دوباره امتحان کنید.');
}

// Get the inserted invitation ID
$invitation_id = $wpdb->insert_id;

// Prepare tracking data for Zabalin
$tracking_data = [
	'invitation_id' => $invitation_id,
	'inviter_id' => get_current_user_id(),
	'inviter_phone' => $inviter_user->user_login,
	'invited_id' => $user,
	'invited_phone' => $invited_user ? $invited_user->user_login : '',
	'product_id' => (int) $product,
	'product_name' => $product_obj ? $product_obj->get_name() : '',
	'product_type' => $product_obj ? wp_get_post_terms($product, 'product_cat', ['fields' => 'names']) : [],
	'invitation_date' => date('Y-m-d H:i:s'),
	'current_page' => $_SERVER['HTTP_REFERER'] ?? '',
	'action' => 'send'
];

wp_send_json_success([
	'message' => 'دعوتنامه با موفقیت ارسال شد، منتظر تایید از طرف کاربر باشید.',
	'tracking_data' => $tracking_data
]);
