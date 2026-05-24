<?php

$medoo = medoo();

$compiler_user_ids_result = $medoo->select('wp_usermeta', 'user_id', [
    'meta_key' => 'wp_capabilities',
    'meta_value[~]' => '%compiler%'
]);

if (empty($compiler_user_ids_result)) {
    wp_send_json_error('مجموعه داری یافت نشد');
    wp_die();
}

$compiler_user_ids = array_map('intval', $compiler_user_ids_result);

// Get all users data using medoo for better performance
$compiler_users = $medoo->select('wp_users', ['ID', 'user_login', 'display_name'], [
    'ID' => $compiler_user_ids,
    'ORDER' => ['ID' => 'ASC']
]);

if (empty($compiler_users)) {
    wp_send_json_error('مجموعه داری یافت نشد');
    wp_die();
}

// Get latest transaction for each user in one optimized query
// Using subquery to get the latest transaction per user
if (!empty($compiler_user_ids)) {
    $user_ids_str = implode(',', array_map('intval', $compiler_user_ids));
    $latest_transactions = $medoo->query("
        SELECT t1.* 
        FROM wallet_transactions t1
        INNER JOIN (
            SELECT user_id, MAX(created_at) as max_created_at
            FROM wallet_transactions
            WHERE user_id IN (" . $user_ids_str . ")
            GROUP BY user_id
        ) t2 ON t1.user_id = t2.user_id AND t1.created_at = t2.max_created_at
    ")->fetchAll();
} else {
    $latest_transactions = [];
}

// Create a map of user_id => latest transaction for quick lookup
$transactions_map = [];
foreach ($latest_transactions as $trans) {
    $transactions_map[$trans['user_id']] = [
        'balance' => $trans['balance'] ?? 0,
        'created_at' => $trans['created_at'] ?? null
    ];
}

// Get balance for users who don't have transactions in the latest_transactions result
// (fallback for users with no transactions or edge cases)
$users_without_transactions = array_diff($compiler_user_ids, array_keys($transactions_map));
$fallback_balances_map = [];

if (!empty($users_without_transactions)) {
    $user_ids_str = implode(',', array_map('intval', $users_without_transactions));
    $fallback_transactions = $medoo->query("
        SELECT t1.user_id, t1.balance
        FROM wallet_transactions t1
        INNER JOIN (
            SELECT user_id, MAX(ID) as max_id
            FROM wallet_transactions
            WHERE user_id IN (" . $user_ids_str . ")
            GROUP BY user_id
        ) t2 ON t1.user_id = t2.user_id AND t1.ID = t2.max_id
    ")->fetchAll();
    
    foreach ($fallback_transactions as $trans) {
        $fallback_balances_map[$trans['user_id']] = (int)($trans['balance'] ?? 0);
    }
}

// Get all user meta for first name and last name
$user_meta_data = $medoo->select('wp_usermeta', ['user_id', 'meta_key', 'meta_value'], [
    'user_id' => $compiler_user_ids,
    'meta_key' => ['first_name', 'last_name', 'billing_first_name', 'billing_last_name']
]);

$user_names_map = [];
foreach ($user_meta_data as $meta) {
    if (!isset($user_names_map[$meta['user_id']])) {
        $user_names_map[$meta['user_id']] = [];
    }
    $user_names_map[$meta['user_id']][$meta['meta_key']] = $meta['meta_value'];
}

$owners_data = [];

foreach ($compiler_users as $user) {
    $user_id = (int)$user['ID'];

    // Get balance and last change date from transactions map
    $balance = 0;
    $last_change_date = null;

    if (isset($transactions_map[$user_id])) {
        $balance = (int)($transactions_map[$user_id]['balance'] ?? 0);
        $last_change_date = $transactions_map[$user_id]['created_at'] ?? null;
    } else {
        // If no transaction in main map, check fallback map (using medoo)
        if (isset($fallback_balances_map[$user_id])) {
            $balance = $fallback_balances_map[$user_id];
        } else {
            // No transaction at all, balance is 0
            $balance = 0;
        }
        $last_change_date = null;
    }
    
    // Get first name and last name
    $user_meta = $user_names_map[$user_id] ?? [];
    $first_name = $user_meta['first_name'] ?? $user_meta['billing_first_name'] ?? '';
    $last_name = $user_meta['last_name'] ?? $user_meta['billing_last_name'] ?? '';
    $full_name = trim($first_name . ' ' . $last_name);
    if (empty($full_name)) {
        $full_name = $user['display_name'] ?? 'نام و نام خانوادگی ثبت نشده است';
    }
    
    // Get phone number
    $phone = $user['user_login'] ?? '';
    $phone = strpos($phone, '0') !== 0 ? '0' . $phone : $phone;
    
    // Get user's products (games) and brand name
    // Query for user_ebtal products
    $user_ebtal_products = $medoo->select('wp_posts', [
        '[>]wp_postmeta' => ['ID' => 'post_id']
    ], [
        'wp_posts.ID',
        'wp_posts.post_title'
    ], [
        'wp_posts.post_type' => 'product',
        'wp_posts.post_status' => 'publish',
        'AND' => [
            'wp_postmeta.meta_key' => 'user_ebtal',
            'wp_postmeta.meta_value' => $user_id
        ],
        'LIMIT' => 50
    ]);
    
    // Query for sans_manager products
    $sans_manager_products = $medoo->select('wp_posts', [
        '[>]wp_postmeta' => ['ID' => 'post_id']
    ], [
        'wp_posts.ID',
        'wp_posts.post_title'
    ], [
        'wp_posts.post_type' => 'product',
        'wp_posts.post_status' => 'publish',
        'AND' => [
            'wp_postmeta.meta_key' => 'sans_manager',
            'wp_postmeta.meta_value' => $user_id
        ],
        'LIMIT' => 50
    ]);
    
    // Merge and deduplicate products
    $user_products = array_merge($user_ebtal_products, $sans_manager_products);
    $unique_products = [];
    $seen_ids = [];
    foreach ($user_products as $product) {
        if (!in_array($product['ID'], $seen_ids)) {
            $unique_products[] = $product;
            $seen_ids[] = $product['ID'];
        }
    }
    $user_products = $unique_products;
    
    // Get brand names and links from products
    $brand_links = [];
    $game_links = [];
    foreach ($user_products as $product) {
        $product_id = $product['ID'];
        $product_title = $product['post_title'];
        $product_permalink = get_permalink($product_id);
        
        // Create game link (آبی پررنگ)
        if ($product_permalink) {
            $game_links[] = '<a href="' . esc_url($product_permalink) . '" target="_blank" class="text-purple-700 hover:text-purple-900 hover:underline font-semibold">' . esc_html($product_title) . '</a>';
        } else {
            $game_links[] = esc_html($product_title);
        }
        
        // Get brand for this product
        $brand_terms = get_the_terms($product_id, 'product_brand');
        if ($brand_terms && !is_wp_error($brand_terms) && !empty($brand_terms)) {
            $brand_term = $brand_terms[0];
            $brand_name = $brand_term->name;
            $brand_link = get_term_link($brand_term);
            
            // Check if we already added this brand
            $brand_key = $brand_term->term_id;
            if (!isset($brand_links[$brand_key])) {
                if ($brand_link && !is_wp_error($brand_link)) {
                    $brand_links[$brand_key] = '<a href="' . esc_url($brand_link) . '" target="_blank" class="text-green-700 hover:text-green-900 hover:underline font-semibold">' . esc_html($brand_name) . '</a>';
                } else {
                    $brand_links[$brand_key] = esc_html($brand_name);
                }
            }
        }
    }
    
    $brand_name = !empty($brand_links) ? implode('، ', array_values($brand_links)) : '---';
    $games = !empty($game_links) ? implode('، ', $game_links) : '---';
    
    $owners_data[] = [
        'user_id' => $user_id,
        'full_name' => $full_name,
        'phone' => $phone,
        'collection_id' => $user_id,
        'balance' => $balance,
        'last_change_date' => $last_change_date,
        'brand_name' => $brand_name,
        'games' => $games
    ];
}

// Return JSON for AJAX
wp_send_json_success($owners_data);
wp_die();
