<?php
// Get user's connected games (both as owner and sans_manager)

$user_id = intval($_POST['user_id'] ?? 0);

if (empty($user_id)) {
    wp_send_json_error('شناسه کاربر معتبر نیست.');
}

// Get current user and check permissions
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

if (!in_array($current_user_role, ['administrator', 'accounting'])) {
    wp_send_json_error('شما دسترسی به این عملیات را ندارید.');
}

// Include Medoo
$medoo = medoo();

// Get products where user is owner (user_ebtal) - using proper join
// First get post IDs from postmeta
$owner_meta = $medoo->select('wp_postmeta', [
    'post_id'
], [
    'meta_key' => 'user_ebtal',
    'meta_value' => $user_id
]);

$owner_games = [];
if (!empty($owner_meta)) {
    $owner_post_ids = array_column($owner_meta, 'post_id');
    if (!empty($owner_post_ids)) {
        $owner_products = $medoo->select('wp_posts', [
            'ID',
            'post_title'
        ], [
            'ID' => $owner_post_ids,
            'post_type' => 'product',
            'post_status' => 'publish'
        ]);
        
        foreach ($owner_products as $product) {
            $owner_games[] = [
                'id' => $product['ID'],
                'title' => $product['post_title']
            ];
        }
    }
}

// Get products where user is sans_manager - using proper join
$sans_meta = $medoo->select('wp_postmeta', [
    'post_id'
], [
    'meta_key' => 'sans_manager',
    'meta_value' => $user_id
]);

$sans_manager_games = [];
if (!empty($sans_meta)) {
    $sans_post_ids = array_column($sans_meta, 'post_id');
    if (!empty($sans_post_ids)) {
        $sans_manager_products = $medoo->select('wp_posts', [
            'ID',
            'post_title'
        ], [
            'ID' => $sans_post_ids,
            'post_type' => 'product',
            'post_status' => 'publish'
        ]);
        
        foreach ($sans_manager_products as $product) {
            $sans_manager_games[] = [
                'id' => $product['ID'],
                'title' => $product['post_title']
            ];
        }
    }
}


wp_send_json_success([
    'owner_games' => $owner_games,
    'sans_manager_games' => $sans_manager_games
]);
