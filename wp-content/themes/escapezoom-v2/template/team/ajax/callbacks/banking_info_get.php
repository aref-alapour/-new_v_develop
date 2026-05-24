<?php
// Banking Info Get Callback

// Check user permissions
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

// Only allow certain roles to access banking information
if (!in_array($current_user_role, ['administrator', 'accounting', 'poshtiban', 'shopist'])) {
    wp_send_json_error('شما دسترسی به این بخش ندارید.');
}

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

// Get banking information from user meta
$banking_data = array(
    'user_id' => $user_id,
    'identity_card' => get_user_meta($user_id, 'withdrawal_owner_identity_card', true) ?: '',
    'owner_name' => get_user_meta($user_id, 'withdrawal_owner_name', true) ?: '',
    'shaba' => get_user_meta($user_id, 'withdrawal_owner_shaba', true) ?: ''
);

// Remove IR prefix from shaba if present
if (!empty($banking_data['shaba']) && strpos($banking_data['shaba'], 'IR') === 0) {
    $banking_data['shaba'] = substr($banking_data['shaba'], 2);
}

// Send success response
wp_send_json_success($banking_data);
