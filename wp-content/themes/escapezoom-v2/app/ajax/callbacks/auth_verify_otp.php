<?php
$mobile = sanitize_text_field($_POST['phone']);
$code = intval(sanitize_text_field($_POST['code']));
$type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : '';

if (strlen($mobile) == 11 && str_starts_with($mobile, "09")) {
    $mobile = substr($mobile, 1);
}

$user = get_user_by('login', $mobile);

if (!$user) {
    // بررسی برای کاربر جدید (Transient)
    $saved_code = intval(get_transient('otp_' . $mobile));
    if ($saved_code !== $code) {
        wp_send_json_error('کد تایید اشتباه است');
    }
    // کد صحیح است، ادامه می‌دهیم
} else {
    // بررسی برای کاربر قدیمی (User Meta)
    $saved_code = intval(get_user_meta($user->ID, 'otp', true));
    if ($saved_code !== $code) {
        wp_send_json_error('کد تایید اشتباه است');
    }
}

if ($type === 'login') {
    // اگر کاربر قدیمی بود و لاگین با OTP زده بود
    if ($user) {
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
                'redirect' => $redirect_url
            ]);
        } else {
            wp_send_json_success([
                'profile_incomplete' => true
            ]);
        }
    }

} elseif ($type === 'reset') {
    if ($user) {
        update_user_meta($user->ID, 'otp_verified_for_reset', true);
        wp_send_json_success([
            'message' => 'کد تایید شد. لطفا رمز عبور جدید را وارد کنید.'
        ]);
    }
} elseif ($type === 'register') {
    // برای کاربر جدید، فقط تایید می‌کنیم و در مرحله بعد ثبت نام می‌کنیم
    wp_send_json_success([
        'message' => 'کد تایید شد.'
    ]);
}