<?php
$mobile   = sanitize_text_field($_POST['phone']);
$password = sanitize_text_field($_POST['password']);

if (empty($mobile)) {
    wp_send_json_error('شماره موبایل ضروری میباشد');
}
if (empty($password)) {
    wp_send_json_error('رمز عبور ضروری میباشد');
}
// Check mobile length
if (empty($mobile)) {
    wp_send_json_error('شماره موبایل ضروری میباشد');
}
// Check mobile is a number and doesn't have string or etc.
if (! ctype_digit($mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
// Check it's an iranian phone number
if (! preg_match('/^(\+98|0|0098)?9\d{9}$/', $mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

$user = get_user_by('login', $mobile);

// بررسی وجود کاربر
if (! $user) {
    wp_send_json_error('کاربری با این شماره یافت نشد');
}

// بررسی اینکه آیا کاربر رمز عبور دارد یا خیر (برای جلوگیری از ورود با رمز خالی)
if (empty($user->user_pass)) {
    wp_send_json_error('این حساب کاربری رمز عبور ندارد. لطفا با کد تایید وارد شوید.');
}

// بررسی صحت رمز عبور
if (! wp_check_password($password, $user->user_pass, $user->ID)) {
    wp_send_json_error('رمز عبور اشتباه است');
}

$firstname = get_user_meta($user->ID, 'first_name', true);
$lastname  = get_user_meta($user->ID, 'last_name', true);
$user_city = get_user_meta($user->ID, 'user_city', true);
$user_points = (int)get_user_points($user->ID);

if ($firstname !== '' && $lastname !== '' && $user_city !== '') {
    wp_set_current_user($user->ID, $mobile);
    wp_set_auth_cookie($user->ID, true);
    wp_send_json_success([
        'new' => false,
        'user_id' => $user->ID,
        'user_data' => [
            'firstname' => $firstname,
            'lastname'  => $lastname,
            'city'      => $user_city,
            'points'    => $user_points
        ]
    ]);
}

wp_send_json_success([
    'new' => true,
    'user_data' => [
        'firstname' => $firstname,
        'lastname'  => $lastname,
        'city'      => $user_city,
        'points'    => $user_points
    ]
]);