<?php
// Users Delete Callback

// Get user ID
$user_id = intval($_POST['user_id'] ?? 0);

// Validate user ID
if (empty($user_id)) {
    wp_send_json_error('شناسه کاربر الزامی است.');
}

// Check if user exists
$user = get_user_by('id', $user_id);
if (!$user) {
    wp_send_json_error('کاربر یافت نشد.');
}

// Get current user and their role
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

// Check if current user is administrator
if ($current_user_role !== 'administrator') {
    wp_send_json_error('شما دسترسی به حذف کاربر ندارید.');
}

// Check if user is trying to delete themselves
$current_user_id = get_current_user_id();
if ($user_id == $current_user_id) {
    wp_send_json_error('نمی‌توانید خودتان را حذف کنید.');
}

// Check if user is administrator
if (in_array('administrator', $user->roles)) {
    wp_send_json_error('نمی‌توانید کاربر مدیر را حذف کنید.');
}

// Delete user
$result = wp_delete_user($user_id);

if (!$result) {
    wp_send_json_error('خطا در حذف کاربر.');
}

// Send success response
wp_send_json_success('کاربر با موفقیت حذف شد.');
