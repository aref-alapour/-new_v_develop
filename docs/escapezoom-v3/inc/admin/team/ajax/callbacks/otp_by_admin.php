<?php
$mobile = sanitize_text_field($_POST['phone']);
$medoo = medoo();

// 2. اعتبارسنجی شماره موبایل
if (empty($mobile)) {
    wp_send_json_error('شماره موبایل ضروری میباشد');
}
if (!ctype_digit($mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}
if (!preg_match('/^(\+98|0|0098)?9\d{9}$/', $mobile)) {
    wp_send_json_error('شماره موبایل صحیح نیست');
}

// استانداردسازی شماره
if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

// 3. بررسی وجود کاربر (کاربر قدیمی یا جدید)
$user_id = $medoo->select("wp_users", "ID", ['user_login' => $mobile]);
$otp_code = null;

if ($user_id) {
    // --- کاربر قدیمی: خواندن از wp_usermeta ---
    $otp_code = $medoo->select("wp_usermeta", "meta_value", [
        'user_id' => $user_id,
        'meta_key' => 'otp'
    ]);
} else {
    // --- کاربر جدید: خواندن از Transient (wp_options) ---
    // نام کلید در دیتابیس به صورت _transient_otp_9123456789 ذخیره می‌شود
    $transient_key = '_transient_otp_' . $mobile;
    
    $otp_code = $medoo->select("wp_options", "option_value", [
        'option_name' => $transient_key
    ]);
}

// 4. بررسی وجود کد
if (empty($otp_code)) {
    wp_send_json_error('کدی برای این کاربر یافت نشد (منقضی یا ارسال نشده)');
}

// 5. ارسال پاسخ موفقیت آمیز
wp_send_json_success([
    'otp' => $otp_code,
    'message' => 'کد تایید بازیابی شد.'
]);