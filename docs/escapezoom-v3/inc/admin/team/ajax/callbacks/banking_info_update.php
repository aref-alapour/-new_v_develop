<?php
// Banking Info Update Callback

// Check user permissions
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

// Only allow certain roles to update banking information
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

// Get and sanitize form data
$identity_card = sanitize_text_field($_POST['identity_card'] ?? '');
$owner_name = sanitize_text_field($_POST['owner_name'] ?? '');
$shaba = sanitize_text_field($_POST['shaba'] ?? '');

// Validate required fields
if (empty($identity_card)) {
    wp_send_json_error('کد ملی صاحب حساب الزامی است.');
}

if (empty($owner_name)) {
    wp_send_json_error('نام و نام خانوادگی صاحب حساب الزامی است.');
}

if (empty($shaba)) {
    wp_send_json_error('شماره شبا الزامی است.');
}

// Validate identity card format (10 digits)
if (!preg_match('/^[0-9]{10}$/', $identity_card)) {
    wp_send_json_error('کد ملی باید دقیقاً 10 رقم باشد.');
}

// Validate shaba format (24 digits)
if (!preg_match('/^[0-9]{24}$/', $shaba)) {
    wp_send_json_error('شماره شبا باید دقیقاً 24 رقم باشد.');
}

// Add IR prefix to shaba
$shaba_with_prefix = 'IR' . $shaba;

// Update user meta
$update_identity = update_user_meta($user_id, 'withdrawal_owner_identity_card', $identity_card);
$update_name = update_user_meta($user_id, 'withdrawal_owner_name', $owner_name);
$update_shaba = update_user_meta($user_id, 'withdrawal_owner_shaba', $shaba_with_prefix);

// Check if updates were successful
if ($update_identity === false && get_user_meta($user_id, 'withdrawal_owner_identity_card', true) !== $identity_card) {
    wp_send_json_error('خطا در به‌روزرسانی کد ملی.');
}

if ($update_name === false && get_user_meta($user_id, 'withdrawal_owner_name', true) !== $owner_name) {
    wp_send_json_error('خطا در به‌روزرسانی نام صاحب حساب.');
}

if ($update_shaba === false && get_user_meta($user_id, 'withdrawal_owner_shaba', true) !== $shaba_with_prefix) {
    wp_send_json_error('خطا در به‌روزرسانی شماره شبا.');
}

// Send success response
wp_send_json_success('اطلاعات بانکی با موفقیت به‌روزرسانی شد.');
