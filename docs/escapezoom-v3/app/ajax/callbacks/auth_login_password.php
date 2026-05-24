<?php
$mobile = sanitize_text_field($_POST['phone']);
$password = sanitize_text_field($_POST['password']);

if (empty($mobile)) {
    wp_send_json_error('شماره موبایل ضروری میباشد');
}
if (!ctype_digit($mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
if (!preg_match('/^(\+98|0|0098)?9\d{9}$/', $mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

$user = get_user_by('login', $mobile);

if (!$user) {
    wp_send_json_error('کاربری با این شماره یافت نشد');
}

if (!wp_check_password($password, $user->user_pass, $user->ID)) {
    wp_send_json_error('رمز عبور اشتباه است');
}

wp_set_current_user($user->ID, $mobile);
wp_set_auth_cookie($user->ID, true);

$firstname = get_user_meta($user->ID, 'first_name', true);
$lastname = get_user_meta($user->ID, 'last_name', true);
$user_city = get_user_meta($user->ID, 'user_city', true);

if ($firstname !== '' && $lastname !== '' && $user_city !== '') {
    $redirect_url = wc_get_account_endpoint_url('dashboard');
    wp_send_json_success([
        'redirect' => $redirect_url
    ]);
} else {
    wp_send_json_success([
        'profile_incomplete' => true
    ]);
}