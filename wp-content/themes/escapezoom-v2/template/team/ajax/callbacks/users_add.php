<?php
// Users Add Callback

// Get form data
$phone = sanitize_text_field($_POST['phone'] ?? '');
$first_name = sanitize_text_field($_POST['first_name'] ?? '');
$last_name = sanitize_text_field($_POST['last_name'] ?? '');
$role = sanitize_text_field($_POST['role'] ?? '');

// Remove leading zero from phone number for user_login
$phone_without_zero = ltrim($phone, '0');

// Validate required fields
if (empty($phone) || empty($first_name) || empty($last_name) || empty($role)) {
    wp_send_json_error('تمام فیلدها الزامی هستند.');
}

// Get current user and their role
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

// Check if current user can create users
$can_create_user = false;
$allowed_roles_for_creation = [];

if (in_array($current_user_role, ['administrator', 'poshtiban', 'supervisor', 'accounting'])) {
    $can_create_user = true;
    if ($current_user_role === 'accounting') {
        $allowed_roles_for_creation = ['customer', 'sans_manager', 'poshtiban', 'supervisor', 'compiler', 'accounting'];
    } else {
        $allowed_roles_for_creation = ['customer', 'sans_manager', 'poshtiban', 'supervisor', 'compiler'];
    }
}

if (!$can_create_user) {
    wp_send_json_error('شما دسترسی به ایجاد کاربر جدید ندارید.');
}

// Validate role is in allowed list
if (!in_array($role, $allowed_roles_for_creation)) {
    $role_names = [];
    $role_translations = [
        'customer' => 'مشتری',
        'sans_manager' => 'مدیر سانس',
        'poshtiban' => 'پشتیبان',
        'supervisor' => 'شاپ منیجر',
        'compiler' => 'مجموعه‌دار',
        'accounting' => 'حسابدار'
    ];
    foreach ($allowed_roles_for_creation as $allowed_role) {
        if (isset($role_translations[$allowed_role])) {
            $role_names[] = $role_translations[$allowed_role];
        }
    }
    wp_send_json_error('نقش انتخاب شده معتبر نیست. فقط نقش‌های زیر قابل انتخاب هستند: ' . implode('، ', $role_names));
}

// Validate phone number format (must start with 09 and be 11 digits)
if (!preg_match('/^09\d{9}$/', $phone)) {
    wp_send_json_error('شماره موبایل باید با 09 شروع شده و 11 رقم باشد.');
}


// Check if phone number already exists in userlogin
$existing_user = get_user_by('login', $phone_without_zero);
if ($existing_user) {
    wp_send_json_error('شماره موبایل قبلاً در سیستم ثبت شده است.');
}

// Create new user
$user_data = array(
    'user_login' => $phone_without_zero,
    'user_email' => $phone . '@info.com',
    'user_pass' => wp_generate_password(),
    'first_name' => $first_name,
    'last_name' => $last_name,
    'role' => $role
);

$user_id = wp_insert_user($user_data);

if (is_wp_error($user_id)) {
    wp_send_json_error('خطا در ایجاد کاربر: ' . $user_id->get_error_message());
}

// Update user meta
update_user_meta($user_id, 'billing_phone', $phone);
update_user_meta($user_id, 'billing_first_name', $first_name);
update_user_meta($user_id, 'billing_last_name', $last_name);

// Send success response
wp_send_json_success('کاربر با موفقیت ایجاد شد.');
