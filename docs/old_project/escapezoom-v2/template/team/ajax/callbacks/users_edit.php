<?php
// Users Edit Callback

// Get form data
$user_id = intval($_POST['user_id'] ?? 0);
$phone = sanitize_text_field($_POST['phone'] ?? '');
$first_name = sanitize_text_field($_POST['first_name'] ?? '');
$last_name = sanitize_text_field($_POST['last_name'] ?? '');
$role = sanitize_text_field($_POST['role'] ?? '');
$iban = sanitize_text_field($_POST['iban'] ?? '');

// Validate required fields
if (empty($user_id) || empty($phone) || empty($first_name) || empty($last_name) || empty($role)) {
    wp_send_json_error('تمام فیلدها الزامی هستند.');
}

// Check if user exists
$user = get_user_by('id', $user_id);
if (!$user) {
    wp_send_json_error('کاربر یافت نشد.');
}

// Validate phone number format
if (!preg_match('/^09\d{9}$/', $phone)) {
    wp_send_json_error('شماره موبایل باید با 09 شروع شده و 11 رقم باشد.');
}

// Validate IBAN format if provided
if (!empty($iban)) {
    // Remove any spaces and convert to uppercase
    $iban = strtoupper(str_replace(' ', '', $iban));

    // Check if it starts with IR
    if (!preg_match('/^IR\d{24}$/', $iban)) {
        wp_send_json_error('شماره شبا باید با IR شروع شده و 26 کاراکتر باشد (IR + 24 رقم).');
    }
}

// Get current user and their role
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

// Get target user's current role
$target_user_capabilities = get_user_meta($user_id, 'wp_capabilities', true);
$target_user_capabilities = maybe_unserialize($target_user_capabilities);
$target_user_current_role = !empty($target_user_capabilities) ? array_key_first($target_user_capabilities) : 'subscriber';

// Validate role assignment permissions
$can_assign_role = false;
$allowed_roles_for_current_user = [];

if ($current_user_role === 'administrator') {
    // Administrator can assign only the allowed roles
    $allowed_roles_for_current_user = array('customer', 'sans_manager', 'poshtiban', 'supervisor', 'compiler', 'accounting');
    $can_assign_role = in_array($role, $allowed_roles_for_current_user);
} elseif ($current_user_role === 'supervisor') {
    // Shopist can assign: customer, poshtiban, compiler, sans_manager
    $allowed_roles_for_current_user = array('customer', 'poshtiban', 'compiler', 'sans_manager');
    $can_assign_role = in_array($role, $allowed_roles_for_current_user);
} elseif ($current_user_role === 'poshtiban') {
    // Poshtiban can assign: customer, poshtiban, sans_manager
    $allowed_roles_for_current_user = array('customer', 'poshtiban', 'sans_manager');
    $can_assign_role = in_array($role, $allowed_roles_for_current_user);
} elseif ($current_user_role === 'accounting') {
    // Accounting can assign: customer, poshtiban, sans_manager, compiler, supervisor, accounting
    $allowed_roles_for_current_user = array('customer', 'poshtiban', 'sans_manager', 'compiler', 'supervisor', 'accounting');
    $can_assign_role = in_array($role, $allowed_roles_for_current_user);
}

if (!$can_assign_role) {
    wp_send_json_error('شما دسترسی به تخصیص این نقش را ندارید.');
}

// Validate role
if (!in_array($role, $allowed_roles_for_current_user)) {
    wp_send_json_error('نقش انتخاب شده معتبر نیست.');
}

// Check if phone number is already used by another user
$existing_user = get_user_by('login', $phone);
if ($existing_user && $existing_user->ID != $user_id) {
    wp_send_json_error('شماره موبایل قبلاً توسط کاربر دیگری استفاده شده است.');
}

// Update user data
$user_data = array(
    'ID' => $user_id,
    'user_login' => $phone,
    'user_email' => $phone . '@info.com',
    'first_name' => $first_name,
    'last_name' => $last_name,
    'role' => $role
);

$result = wp_update_user($user_data);

if (is_wp_error($result)) {
    wp_send_json_error('خطا در به‌روزرسانی کاربر: ' . $result->get_error_message());
}

// Update user meta
update_user_meta($user_id, 'billing_phone', $phone);
update_user_meta($user_id, 'billing_first_name', $first_name);
update_user_meta($user_id, 'billing_last_name', $last_name);

// Update IBAN if provided
if (!empty($iban)) {
    update_user_meta($user_id, 'withdrawal_owner_shaba', $iban);
} else {
    // If IBAN is empty, remove it
    delete_user_meta($user_id, 'withdrawal_owner_shaba');
}

// Send success response
wp_send_json_success('اطلاعات کاربر با موفقیت به‌روزرسانی شد.');
