<?php
$mobile = sanitize_text_field($_POST['phone']);
$type = sanitize_text_field($_POST['type']);

if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

$code = wp_rand(1000, 9999);
$user = get_user_by('login', $mobile);

if ($user) {
    update_user_meta($user->ID, 'otp', $code);
    update_user_meta($user->ID, 'otp_send_time', time());
    update_user_meta($user->ID, 'otp_type', $type);
} else {
    // برای کاربر جدید که هنوز کاربر ساخته نشده، از Transient استفاده می‌کنیم
    set_transient('otp_' . $mobile, $code, 5 * MINUTE_IN_SECONDS);
    set_transient('otp_type_' . $mobile, $type, 5 * MINUTE_IN_SECONDS);
}

try {
   //   ez_sendpayamak3( $mobile, 'کد تایید شما: ' . $code, '90006491' );
   ez_otp_new($mobile, $code);
} catch ( Exception $e ) {
   // Silent fail
}

wp_send_json_success(['message' => 'کد تایید ارسال شد']);