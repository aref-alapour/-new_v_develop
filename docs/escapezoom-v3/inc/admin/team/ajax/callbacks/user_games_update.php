<?php
// Update user's game connections

$user_id = intval($_POST['user_id'] ?? 0);
$game_id = intval($_POST['game_id'] ?? 0);
$connection_type = sanitize_text_field($_POST['connection_type'] ?? ''); // 'user_ebtal' or 'sans_manager'
$action = sanitize_text_field($_POST['action'] ?? ''); // 'add' or 'remove'

if (empty($user_id) || empty($game_id) || empty($connection_type) || empty($action)) {
    wp_send_json_error('تمام پارامترها الزامی هستند.');
}

if (!in_array($connection_type, ['user_ebtal', 'sans_manager'])) {
    wp_send_json_error('نوع اتصال معتبر نیست.');
}

if (!in_array($action, ['add', 'remove'])) {
    wp_send_json_error('عملیات معتبر نیست.');
}

// Get current user and check permissions
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

if (!in_array($current_user_role, ['administrator', 'accounting'])) {
    wp_send_json_error('شما دسترسی به این عملیات را ندارید.');
}

// Check if product exists
$product = get_post($game_id);
if (!$product || $product->post_type !== 'product') {
    wp_send_json_error('بازی یافت نشد.');
}

// Check if user exists
$user = get_user_by('id', $user_id);
if (!$user) {
    wp_send_json_error('کاربر یافت نشد.');
}

if ($action === 'add') {
    $force = isset($_POST['force']) && $_POST['force'] === 'true';
    
    // Check current owner/sans_manager before adding (unless force is true)
    if (!$force) {
        $current_value = get_post_meta($game_id, $connection_type, true);
        
        if (!empty($current_value) && $current_value != $user_id) {
            // Get current user info
            $current_user_obj = get_user_by('id', $current_value);
            $current_user_name = '';
            if ($current_user_obj) {
                $current_first_name = get_user_meta($current_value, 'first_name', true) ?: get_user_meta($current_value, 'billing_first_name', true);
                $current_last_name = get_user_meta($current_value, 'last_name', true) ?: get_user_meta($current_value, 'billing_last_name', true);
                $current_user_name = trim(($current_first_name ?? '') . ' ' . ($current_last_name ?? ''));
                if (empty($current_user_name)) {
                    $current_user_name = $current_user_obj->display_name ?: $current_user_obj->user_login;
                }
            }
            
            $role_name = $connection_type === 'user_ebtal' ? 'مجموعه‌دار' : 'مدیر سانس';
            
            // Return warning info (client will show confirmation)
            wp_send_json_success([
                'warning' => true,
                'current_user_id' => $current_value,
                'current_user_name' => $current_user_name,
                'role_name' => $role_name,
                'game_title' => $product->post_title
            ]);
        }
    }
    
    // Update meta
    update_post_meta($game_id, $connection_type, $user_id);
    
    // If adding as owner, remove from sans_manager (and vice versa if needed)
    if ($connection_type === 'user_ebtal') {
        // Remove from sans_manager if exists
        $sans_manager = get_post_meta($game_id, 'sans_manager', true);
        if ($sans_manager == $user_id) {
            delete_post_meta($game_id, 'sans_manager');
        }
    } else {
        // Remove from user_ebtal if exists
        $owner = get_post_meta($game_id, 'user_ebtal', true);
        if ($owner == $user_id) {
            delete_post_meta($game_id, 'user_ebtal');
        }
    }
    
    wp_send_json_success('بازی با موفقیت به کاربر متصل شد.');
    
} else if ($action === 'remove') {
    // Remove connection
    delete_post_meta($game_id, $connection_type);
    
    wp_send_json_success('اتصال بازی با موفقیت حذف شد.');
}

wp_send_json_error('خطایی رخ داده است.');
