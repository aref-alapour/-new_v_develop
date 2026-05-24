<?php

if (isset($_POST['text']) and isset($_POST['phone'])) {
    // بررسی دسترسی کاربر برای ارسال پیام دلخواه
    $current_user = wp_get_current_user();
    $allowed_custom_sms_roles = ['administrator', 'shopist'];
    $can_send_custom_sms = array_intersect($allowed_custom_sms_roles, $current_user->roles);

    // اگر پیام از قالب آماده نیست و کاربر مجاز نیست
    $is_template_message = isset($_POST['is_template']) && $_POST['is_template'] == 'true';
    if (!$is_template_message && empty($can_send_custom_sms)) {
        wp_send_json_error('شما مجاز به ارسال پیام دلخواه نیستید. فقط از قالب‌های آماده استفاده کنید.');
        return;
    }

    $allowed_tags = array('br' => array());
    $text = wp_kses($_POST['text'], $allowed_tags);
    try {
        ez_sendpayamak3( $_POST['phone'], $text, '2191307900' );
    } catch ( Exception $e ) {
        wp_send_json_error( $e->getMessage() );
    }
    wp_send_json_success('پیامک با موفقیت ارسال شد.');
} else
    wp_send_json_error('یکی از فیلدها خالی است.');
