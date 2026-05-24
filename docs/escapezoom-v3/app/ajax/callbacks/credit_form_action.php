<?php
/**
 * فرم درگاه پرداخت اعتباری – ثبت دیده شدن / غیرفعالسازی (فقط برای مجموعه‌داران)
 * callback: credit_form_action
 * POST: action = mark_view | mark_canceled
 */
global $wpdb;

if (!is_user_logged_in()) {
    wp_send_json_error('لطفاً وارد شوید.');
}

if (!function_exists('has_role') || !has_role('compiler')) {
    wp_send_json_error('دسترسی مجاز نیست.');
}

$action = isset($_POST['action_param']) ? sanitize_text_field($_POST['action_param']) : '';

if (!in_array($action, ['mark_view', 'mark_canceled'], true)) {
    wp_send_json_error('عمل نامعتبر.');
}

$user_id = get_current_user_id();
$first_name = get_user_meta($user_id, 'first_name', true);
$last_name  = get_user_meta($user_id, 'last_name', true);
$full_name_fa = trim($first_name . ' ' . $last_name);
if ($full_name_fa === '') {
    $user = get_userdata($user_id);
    $full_name_fa = $user ? $user->display_name : '';
}
$phone = get_user_meta($user_id, 'billing_phone', true);
if ($phone === '') {
    $user = get_userdata($user_id);
    $phone = $user ? $user->user_login : '';
}

$table = $wpdb->prefix . 'creadit_form';

if ($action === 'mark_view') {
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id, is_view FROM `{$table}` WHERE owner_id = %d",
        $user_id
    ));
    if ($existing) {
        $wpdb->update(
            $table,
            [
                'full_name_fa' => $full_name_fa,
                'phone'        => $phone,
                'is_view'      => 1,
                'updated_at'   => current_time('mysql'),
            ],
            ['owner_id' => $user_id],
            ['%s', '%s', '%d', '%s'],
            ['%d']
        );
    } else {
        $wpdb->insert(
            $table,
            [
                'owner_id'    => $user_id,
                'full_name_fa'=> $full_name_fa,
                'phone'       => $phone,
                'is_view'     => 1,
                'canceled'    => 0,
                'created_at'  => current_time('mysql'),
                'updated_at'  => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%d', '%s', '%s']
        );
    }
    wp_send_json_success(['message' => 'ثبت شد.']);
}

if ($action === 'mark_canceled') {
    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM `{$table}` WHERE owner_id = %d",
        $user_id
    ));
    if ($existing) {
        $wpdb->update(
            $table,
            [
                'full_name_fa' => $full_name_fa,
                'phone'        => $phone,
                'canceled'     => 1,
                'is_view'      => 1,
                'updated_at'   => current_time('mysql'),
            ],
            ['owner_id' => $user_id],
            ['%s', '%s', '%d', '%d', '%s'],
            ['%d']
        );
    } else {
        $wpdb->insert(
            $table,
            [
                'owner_id'    => $user_id,
                'full_name_fa'=> $full_name_fa,
                'phone'       => $phone,
                'is_view'     => 1,
                'canceled'    => 1,
                'created_at'  => current_time('mysql'),
                'updated_at'  => current_time('mysql'),
            ],
            ['%d', '%s', '%s', '%d', '%d', '%s', '%s']
        );
    }
    wp_send_json_success(['message' => 'درخواست غیرفعالسازی ثبت شد.']);
}

wp_send_json_error('خطا.');
wp_die();
