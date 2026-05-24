<?php
$mobile = sanitize_text_field($_POST['phone']);
$new_password = sanitize_text_field($_POST['password']);

if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

$user = get_user_by('login', $mobile);

if (!$user) {
    wp_send_json_error('کاربری با این شماره یافت نشد');
}

$is_verified = get_user_meta($user->ID, 'otp_verified_for_reset', true);

if (!$is_verified) {
    wp_send_json_error('دسترسی غیرمجاز. لطفا مراحل فراموشی رمز را از ابتدا طی کنید.');
}

// --- اعتبارسنجی بک‌اند ---
// ۱. حداقل ۱۰ کاراکتر
if (strlen($new_password) < 10) {
    wp_send_json_error('رمز عبور باید حداقل ۱۰ کاراکتر باشد');
}

// ۲. شامل حروف بزرگ، کوچک و عدد
if (!preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
    wp_send_json_error('رمز عبور باید شامل حروف بزرگ، کوچک و عدد باشد');
}
// ------------------------------

wp_set_password($new_password, $user->ID);

delete_user_meta($user->ID, 'otp_verified_for_reset');
delete_user_meta($user->ID, 'otp');
delete_user_meta($user->ID, 'otp_send_time');

wp_set_current_user($user->ID, $mobile);
wp_set_auth_cookie($user->ID, true);

$firstname = get_user_meta($user->ID, 'first_name', true);
$lastname = get_user_meta($user->ID, 'last_name', true);
$user_city = get_user_meta($user->ID, 'user_city', true);

if ($firstname !== '' && $lastname !== '' && $user_city !== '') {
    $redirect_url = wc_get_account_endpoint_url('dashboard');
    wp_send_json_success([
        'redirect' => $redirect_url,
        'message' => 'رمز عبور با موفقیت تغییر کرد و وارد شدید.'
    ]);
} else {
    wp_send_json_success([
        'profile_incomplete' => true,
        'message' => 'رمز عبور تغییر کرد. لطفا اطلاعات خود را تکمیل کنید.'
    ]);
}