<?php
// Users Create Password Callback
// Get form data
$user_id = intval($_POST['user_id'] ?? 0);
$phone = sanitize_text_field($_POST['phone'] ?? '');

// Validate required fields
if (empty($user_id) || empty($phone)) {
    wp_send_json_error('شناسه کاربر و شماره موبایل الزامی هستند.');
}

// Check if user exists
$user = get_user_by('id', $user_id);
if (!$user) {
    wp_send_json_error('کاربر یافت نشد.');
}

// Validate phone number format (Must be 11 digits starting with 09)
if (!preg_match('/^09\d{9}$/', $phone)) {
    wp_send_json_error('شماره موبایل باید با 09 شروع شده و 11 رقم باشد.');
}

// Logic: Remove the first '0' to create the password
// Example: 09123456789 -> 9123456789
$password = substr($phone, 1);

// Update user password
// wp_update_user handles the hashing automatically
$result = wp_update_user(array(
    'ID'        => $user_id,
    'user_pass' => $password
));

if (is_wp_error($result)) {
    wp_send_json_error('خطا در ایجاد رمز عبور: ' . $result->get_error_message());
}

// Send success response
wp_send_json_success('رمز ثابت با موفقیت ایجاد شد. رمز عبور: ' . $password);