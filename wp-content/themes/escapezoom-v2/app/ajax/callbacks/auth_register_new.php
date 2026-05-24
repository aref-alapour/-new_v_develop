<?php
$mobile = sanitize_text_field($_POST['phone']);
$password = sanitize_text_field($_POST['password']);
$confirm_password = sanitize_text_field($_POST['confirm_password']);
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

if (empty($password)) {
    wp_send_json_error('رمز عبور ضروری میباشد');
}
if ($password !== $confirm_password) {
    wp_send_json_error('تکرار رمز عبور مطابقت ندارد');
}

if (get_user_by('login', $mobile)) {
    wp_send_json_error('این شماره قبلاً ثبت شده است');
}

$email = $mobile . '@' . str_replace(['https://', 'http://'], '', site_url());
$user_id = wp_create_user($mobile, $password, $email);

if (is_wp_error($user_id)) {
    wp_send_json_error('خطا در ایجاد کاربر. لطفا مجددا تلاش کنید.');
}

// --- حذف Transient های OTP بعد از ثبت نام موفق ---
delete_transient('otp_' . $mobile);
delete_transient('otp_type_' . $mobile);
// --------------------------------------------------

$user_obj = new WP_User($user_id);
$user_obj->set_role('customer');
update_user_meta($user_id, 'billing_phone', '0' . $mobile);
update_user_meta($user_id, 'first_name', $firstname);
update_user_meta($user_id, 'billing_first_name', $firstname);
update_user_meta($user_id, 'last_name', $lastname);
update_user_meta($user_id, 'billing_last_name', $lastname);
update_user_meta($user_id, 'user_city', $user_city);

wp_update_user([
        'ID' => $user_id,
        'display_name' => $firstname . ' ' . $lastname,
]);

add_point('register', $user_id, 'ثبت نام در سایت');

wp_set_current_user($user_id, $mobile);
wp_set_auth_cookie($user_id, true);

$redirect_url = wc_get_account_endpoint_url('dashboard');

wp_send_json_success([
    'redirect' => $redirect_url,
    'message' => 'ثبت نام موفقیت آمیز بود.'
]);