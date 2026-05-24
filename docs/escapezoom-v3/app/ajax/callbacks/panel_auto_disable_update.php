<?php
global $wpdb;

$user_id = get_current_user_id();

$product_id   = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$auto_disable = isset($_POST['auto_disable']) ? intval($_POST['auto_disable']) : 0;

if ($product_id <= 0 || !get_post($product_id))
    wp_send_json_error(['message' => 'شناسه محصول نامعتبر است.'], 400);

// only allow predefined minutes
$allowed = [15, 30, 60, 120, 180];
if (!in_array($auto_disable, $allowed, true))
    wp_send_json_error(['message' => 'مقدار زمان نامعتبر است.'], 400);

// access control similar to other panel callbacks
if (!function_exists('get_user_role')) {
    function get_user_role($uid) {
        $u = get_userdata($uid);
        if (!$u || empty($u->roles)) return '';
        return $u->roles[0];
    }
}

$user_role = get_user_role($user_id);
$meta_key = ($user_role === 'sans_manager') ? 'sans_manager' : 'user_ebtal';

$has_access = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(1) FROM `wp_postmeta` WHERE `post_id` = %d AND `meta_key` = %s AND `meta_value` = %s",
        $product_id,
        $meta_key,
        (string) $user_id
    )
);

if (!$has_access && !current_user_can('manage_options'))
    wp_send_json_error(['message' => 'دسترسی به این محصول ندارید.'], 403);

update_post_meta($product_id, 'auto_disable', $auto_disable);

wp_send_json_success([
    'product_id'   => $product_id,
    'auto_disable' => $auto_disable,
]);

