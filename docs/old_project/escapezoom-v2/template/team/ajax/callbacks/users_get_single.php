<?php
// Users Get Single Callback

// Get user ID
$user_id = intval($_POST['user_id'] ?? 0);

// Validate user ID
if (empty($user_id)) {
    wp_send_json_error('شناسه کاربر الزامی است.');
}

// Get user
$user = get_user_by('id', $user_id);
if (!$user) {
    wp_send_json_error('کاربر یافت نشد.');
}

// Get user data
$user_data = array(
    'ID' => $user->ID,
    'phone' => get_user_meta($user_id, 'billing_phone', true) ?: $user->user_login,
    'first_name' => get_user_meta($user_id, 'first_name', true),
    'last_name' => get_user_meta($user_id, 'last_name', true),
    'role' => !empty($user->roles) ? $user->roles[0] : 'subscriber'
);

// Send success response
wp_send_json_success($user_data);
