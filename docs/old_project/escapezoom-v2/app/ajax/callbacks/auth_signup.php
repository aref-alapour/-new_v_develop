<?php

$mobile = sanitize_text_field($_POST['phone']);

$firstname = sanitize_text_field($_POST['firstname']);
$lastname  = sanitize_text_field($_POST['lastname']);
$user_city = sanitize_text_field($_POST['city']);

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

// Only update fields that are provided (not empty)
if (!empty($firstname)) {
    update_user_meta($user->ID, 'first_name', $firstname);
    update_user_meta($user->ID, 'billing_first_name', $firstname);
    update_user_meta($user->ID, 'shipping_first_name', $firstname);
}

if (!empty($lastname)) {
    update_user_meta($user->ID, 'last_name', $lastname);
    update_user_meta($user->ID, 'billing_last_name', $lastname);
    update_user_meta($user->ID, 'shipping_last_name', $lastname);
}

if (!empty($user_city)) {
    update_user_meta($user->ID, 'user_city', $user_city);
}

// Update display name only if both first and last names are available
$current_firstname = get_user_meta($user->ID, 'first_name', true);
$current_lastname = get_user_meta($user->ID, 'last_name', true);

if (!empty($current_firstname) && !empty($current_lastname)) {
    wp_update_user([
        'ID'           => $user->ID,
        'display_name' => $current_firstname . ' ' . $current_lastname,
    ]);
}

delete_user_meta($user->ID, 'otp_send_time');
delete_user_meta($user->ID, 'otp');

wp_set_current_user($user->ID, $mobile);
wp_set_auth_cookie($user->ID, true);

wp_send_json_success(['msg' => 'ثبت نام شما با موفقیت انجام شد.', 'user_id' => $user->ID]);
