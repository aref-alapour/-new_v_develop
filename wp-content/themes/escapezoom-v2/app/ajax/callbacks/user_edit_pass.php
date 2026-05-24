<?php
// user_edit_pass.php
if (!defined('ABSPATH')) {
    die('Forbidden');
}

// Check if user is logged in
if (!is_user_logged_in()) {
    wp_send_json_error('User not logged in');
}

// Get user ID
$user_id = get_current_user_id();
$user = get_user_by('id', $user_id);

// Check if password is provided
if (!isset($_POST['new_password']) || empty($_POST['new_password'])) {
    wp_send_json_error('رمز عبور جدید الزامی است.');
}

// Check if old password is required (if user has a password set)
$has_password = !empty($user->user_pass);
$old_password = isset($_POST['old_password']) ? sanitize_text_field($_POST['old_password']) : '';

if ($has_password && empty($old_password)) {
    wp_send_json_error('رمز عبور قدیم الزامی است.');
}

// Verify old password if needed
if ($has_password && !wp_check_password($old_password, $user->user_pass, $user_id)) {
    wp_send_json_error('رمز عبور قدیم درست وارد نشده است.');
}

// Check new password matches confirmation
if (isset($_POST['confirm_password']) && $_POST['new_password'] !== $_POST['confirm_password']) {
    wp_send_json_error('رمز عبور جدید و تکرار رمز عبور یکسان نیست.');
}

// Update user password
wp_set_password($_POST['new_password'], $user_id);

wp_send_json_success('رمز عبور به درستی تغییر کرد.');