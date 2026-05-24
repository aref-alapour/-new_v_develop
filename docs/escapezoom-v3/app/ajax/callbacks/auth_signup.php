<?php
$mobile = sanitize_text_field($_POST['phone']);
$firstname = sanitize_text_field($_POST['firstname']);
$lastname = sanitize_text_field($_POST['lastname']);
$user_city = sanitize_text_field($_POST['city']);

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
    wp_send_json_error('کاربر یافت نشد');
}

if (!empty($firstname)) {
    update_user_meta($user->ID, 'first_name', $firstname);
    update_user_meta($user->ID, 'billing_first_name', $firstname);
}
if (!empty($lastname)) {
    update_user_meta($user->ID, 'last_name', $lastname);
    update_user_meta($user->ID, 'billing_last_name', $lastname);
}
if (!empty($user_city)) {
    update_user_meta($user->ID, 'user_city', $user_city);
}

$current_firstname = get_user_meta($user->ID, 'first_name', true);
$current_lastname = get_user_meta($user->ID, 'last_name', true);

if (!empty($current_firstname) && !empty($current_lastname)) {
    wp_update_user([
        'ID' => $user->ID,
        'display_name' => $current_firstname . ' ' . $current_lastname,
    ]);
}

$redirect_url = wc_get_account_endpoint_url('dashboard');
wp_send_json_success([
    'redirect' => $redirect_url
]);