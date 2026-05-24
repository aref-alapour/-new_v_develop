<?php
// Games Search Callback - Search products and check current owner/sans_manager

$search_term = sanitize_text_field($_POST['search'] ?? '');
$limit = intval($_POST['limit'] ?? 20);

if (empty($search_term)) {
    wp_send_json_error('لطفاً نام بازی را وارد کنید.');
}

// Get current user and check permissions
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

if (!in_array($current_user_role, ['administrator', 'accounting'])) {
    wp_send_json_error('شما دسترسی به این عملیات را ندارید.');
}

// Include Medoo
$medoo = medoo();

// Search products from wp_products_search table (much faster)
$products = $medoo->select('wp_products_search', [
    'product_id',
    'product_name'
], [
    'product_name[~]' => $search_term,
    'LIMIT' => $limit,
    'ORDER' => ['product_name' => 'ASC']
]);

if (empty($products)) {
    wp_send_json_success([]);
}

// Get all product IDs
$product_ids = array_column($products, 'product_id');

// Get all owner and sans_manager meta in one query
$owner_metas = $medoo->select('wp_postmeta', [
    'post_id',
    'meta_value'
], [
    'post_id' => $product_ids,
    'meta_key' => 'user_ebtal'
]);

$sans_metas = $medoo->select('wp_postmeta', [
    'post_id',
    'meta_value'
], [
    'post_id' => $product_ids,
    'meta_key' => 'sans_manager'
]);

// Create lookup arrays
$owner_map = [];
foreach ($owner_metas as $meta) {
    $owner_map[$meta['post_id']] = intval($meta['meta_value']);
}

$sans_map = [];
foreach ($sans_metas as $meta) {
    $sans_map[$meta['post_id']] = intval($meta['meta_value']);
}

// Get unique user IDs
$all_user_ids = array_unique(array_merge(array_values($owner_map), array_values($sans_map)));

// Get user names in batch - simpler approach
$user_names = [];
if (!empty($all_user_ids)) {
    // Get basic user info
    $user_data = $medoo->select('wp_users', [
        'ID',
        'display_name',
        'user_login'
    ], [
        'ID' => $all_user_ids
    ]);
    
    // Get user meta in batch
    $user_meta = $medoo->select('wp_usermeta', [
        'user_id',
        'meta_key',
        'meta_value'
    ], [
        'user_id' => $all_user_ids,
        'meta_key' => ['first_name', 'last_name', 'billing_first_name', 'billing_last_name']
    ]);
    
    // Organize meta by user_id
    $user_meta_map = [];
    foreach ($user_meta as $meta) {
        $user_id = $meta['user_id'];
        if (!isset($user_meta_map[$user_id])) {
            $user_meta_map[$user_id] = [];
        }
        $user_meta_map[$user_id][$meta['meta_key']] = $meta['meta_value'];
    }
    
    // Build user names
    foreach ($user_data as $user) {
        $user_id = $user['ID'];
        $meta = $user_meta_map[$user_id] ?? [];
        $first_name = $meta['first_name'] ?? $meta['billing_first_name'] ?? '';
        $last_name = $meta['last_name'] ?? $meta['billing_last_name'] ?? '';
        $full_name = trim($first_name . ' ' . $last_name);
        if (empty($full_name)) {
            $full_name = $user['display_name'] ?: $user['user_login'];
        }
        $user_names[$user_id] = $full_name;
    }
}

// Build results
$results = [];
foreach ($products as $product) {
    $product_id = $product['product_id'];
    
    $current_owner_id = $owner_map[$product_id] ?? null;
    $current_owner_name = $current_owner_id ? ($user_names[$current_owner_id] ?? '') : '';
    
    $current_sans_manager_id = $sans_map[$product_id] ?? null;
    $current_sans_manager_name = $current_sans_manager_id ? ($user_names[$current_sans_manager_id] ?? '') : '';
    
    $results[] = [
        'id' => $product_id,
        'title' => $product['product_name'],
        'current_owner_id' => $current_owner_id,
        'current_owner_name' => $current_owner_name,
        'current_sans_manager_id' => $current_sans_manager_id,
        'current_sans_manager_name' => $current_sans_manager_name
    ];
}

wp_send_json_success($results);
