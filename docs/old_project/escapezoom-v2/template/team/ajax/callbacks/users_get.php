<?php
// Users Get Callback with Medoo for better performance
// Include Medoo
$medoo = medoo();
// Get search and filter parameters
$search = sanitize_text_field($_POST['search'] ?? '');
$role = sanitize_text_field($_POST['role'] ?? '');
$level = sanitize_text_field($_POST['level'] ?? '');
$page = intval($_POST['page'] ?? 1);
$items_per_page = intval($_POST['items_per_page'] ?? 50);
$offset = ($page - 1) * $items_per_page;
// Function to get user level with colors
function get_user_level_display($user_id)
{
    $user_level = get_user_level($user_id);
    switch ($user_level) {
        case 1:
            return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #959798; background: #2527281A;">تازه وارد</span>';
        case 2:
            return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #049654; background: #02C96F4D;">نوپا</span>';
        case 3:
            return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #3F7FF5; background: #5091FB4D;">با تجربه</span>';
        case 4:
            return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #FD7013; background: #FD701338;">کارکشته</span>';
        default:
            return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #959798; background: #2527281A;">نامشخص</span>';
    }
}
// Function to get user role in Persian
function get_user_role_persian($role)
{
    $role_translations = array(
        'administrator' => 'مدیر',
        'wpseo_editor' => 'ویرایشگر سئو',
        'wpseo_manager' => 'مدیر سئو',
        'accounting' => 'حسابدار',
        'sans_manager' => 'مدیر سانس',
        'contentist' => 'محتواگذار',
        'shopist' => 'شاپ منیجر',
        'commentchi' => 'کامنتچی',
        'poshtiban' => 'پشتیبان',
        'compiler' => 'مجموعه‌دار',
        'translator' => 'مترجم',
        'seller' => 'فروشنده',
        'shop_manager' => 'مدیر فروشگاه',
        'customer' => 'مشتری',
        'subscriber' => 'مشترک',
        'contributor' => 'مشارکت‌کننده',
        'author' => 'نویسنده',
        'editor' => 'ویرایشگر',
        'support' => 'پشتیبان',
        'manager' => 'مدیر',
        'staff' => 'کارمند',
        'employee' => 'کارمند'
    );
    return isset($role_translations[$role]) ? $role_translations[$role] : $role;
}
// Function to get user phone number
function get_user_phone($user_data)
{
    $billing_phone = $user_data['billing_phone'] ?? '';
    $user_login = $user_data['user_login'] ?? '';
    // Check if billing_phone is a valid phone number
    if (!empty($billing_phone) && preg_match('/^09\d{9}$/', $billing_phone)) {
        return $billing_phone;
    }
    // Check if user_login is a valid phone number
    if (preg_match('/^09\d{9}$/', $user_login)) {
        return $user_login;
    }
    return 'شماره تلفن معتبر ثبت نشده است';
}
// Function to get user full name
function get_user_full_name($user_data)
{
    $first_name = $user_data['first_name'] ?? '';
    $last_name = $user_data['last_name'] ?? '';
    if (!empty($first_name) || !empty($last_name)) {
        return trim($first_name . ' ' . $last_name);
    }
    $billing_first_name = $user_data['billing_first_name'] ?? '';
    $billing_last_name = $user_data['billing_last_name'] ?? '';
    if (!empty($billing_first_name) || !empty($billing_last_name)) {
        return trim($billing_first_name . ' ' . $billing_last_name);
    }
    return 'نام و نام خانوادگی ثبت نشده است';
}
// Function to get user's managed games using ACF fields
function get_user_managed_games($user_id, $medoo)
{
    // Get products where user is in user_ebtal ACF field
    $products = $medoo->select('wp_posts', [
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
        'LIMIT' => 5
    ]);
    // Also check sans_manager
    $sans_products = $medoo->select('wp_posts', [
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
        'LIMIT' => 5
    ]);
    $all_products = array_merge($products, $sans_products);
    $all_products = array_unique($all_products, SORT_REGULAR);
    if (empty($all_products)) {
        return '<span class="text-gray-500 text-sm">هیچ بازی مدیریت نمی‌کند</span>';
    }
    $games_html = '<div class="space-y-1">';
    foreach ($all_products as $product) {
        $games_html .= '<div class="text-sm">';
        $games_html .= '<a href="' . get_permalink($product['ID']) . '" class="text-blue-600 hover:text-blue-800" target="_blank">';
        $games_html .= esc_html($product['post_title']);
        $games_html .= '</a>';
        $games_html .= '</div>';
    }
    if (count($all_products) >= 5) {
        $games_html .= '<div class="text-xs text-gray-500">...</div>';
    }
    $games_html .= '</div>';
    return $games_html;
}
// Function to get user meta data
function get_user_meta_data($user_id, $medoo)
{
    $meta_data = $medoo->select('wp_usermeta', [
        'meta_key',
        'meta_value'
    ], [
        'user_id' => $user_id,
        'meta_key' => ['first_name', 'last_name', 'billing_phone', 'billing_first_name', 'billing_last_name', 'wp_capabilities']
    ]);
    $result = [];
    foreach ($meta_data as $meta) {
        $result[$meta['meta_key']] = $meta['meta_value'];
    }
    return $result;
}
// Build search conditions for Medoo
$search_conditions = [];
// If search term is provided, find all matching user IDs
if (!empty($search)) {
    // 1. Search in basic user fields
    $basic_search_users = $medoo->select('wp_users', [
        'ID'
    ], [
        'OR' => [
            'user_login[~]' => $search,
            'user_email[~]' => $search,
            'display_name[~]' => $search
        ]
    ]);
    // 2. Search in user meta fields
    $meta_search_users = $medoo->select('wp_usermeta', [
        'user_id'
    ], [
        'meta_key' => ['first_name', 'last_name', 'billing_first_name', 'billing_last_name', 'billing_phone'],
        'meta_value[~]' => $search
    ]);
    // 3. Search in managed games (user_ebtal) - using raw SQL for better compatibility
    $game_search_users_ebtal = $medoo->query("
        SELECT pm.meta_value 
        FROM wp_postmeta pm 
        INNER JOIN wp_posts p ON pm.post_id = p.ID 
        WHERE pm.meta_key = 'user_ebtal' 
        AND pm.meta_value != '' 
        AND p.post_type = 'product' 
        AND p.post_status = 'publish' 
        AND p.post_title LIKE " . $medoo->quote('%' . $search . '%') . "
    ")->fetchAll();
    // 4. Search in managed games (sans_manager) - using raw SQL for better compatibility
    $game_search_users_sans = $medoo->query("
        SELECT pm.meta_value 
        FROM wp_postmeta pm 
        INNER JOIN wp_posts p ON pm.post_id = p.ID 
        WHERE pm.meta_key = 'sans_manager' 
        AND pm.meta_value != '' 
        AND p.post_type = 'product' 
        AND p.post_status = 'publish' 
        AND p.post_title LIKE " . $medoo->quote('%' . $search . '%') . "
    ")->fetchAll();
    // 5. Check if search is numeric (user ID)
    $numeric_search_users = [];
    if (is_numeric($search)) {
        $numeric_search_users = $medoo->select('wp_users', [
            'ID'
        ], [
            'ID' => intval($search)
        ]);
    }
    // Combine all user IDs
    $all_matching_user_ids = [];
    // Add basic search results
    foreach ($basic_search_users as $user) {
        $all_matching_user_ids[] = $user['ID'];
    }
    // Add meta search results
    foreach ($meta_search_users as $user) {
        $all_matching_user_ids[] = $user['user_id'];
    }
    // Add game search results
    if (is_array($game_search_users_ebtal)) {
        foreach ($game_search_users_ebtal as $user) {
            $all_matching_user_ids[] = $user['meta_value'];
        }
    }
    if (is_array($game_search_users_sans)) {
        foreach ($game_search_users_sans as $user) {
            $all_matching_user_ids[] = $user['meta_value'];
        }
    }
    // Add numeric search results
    foreach ($numeric_search_users as $user) {
        $all_matching_user_ids[] = $user['ID'];
    }
    // Remove duplicates and filter out empty values
    $all_matching_user_ids = array_unique(array_filter($all_matching_user_ids));
    if (!empty($all_matching_user_ids)) {
        $search_conditions['ID'] = $all_matching_user_ids;
    } else {
        // If no matches found, return empty result
        $search_conditions['ID'] = [0]; // This will return no results
    }
}
// Add role filter if specified
if (!empty($role)) {
    // Get all users with capabilities
    $all_users_with_caps = $medoo->select('wp_usermeta', [
        'user_id',
        'meta_value'
    ], [
        'meta_key' => 'wp_capabilities'
    ]);
    $role_user_ids = [];
    foreach ($all_users_with_caps as $user_cap) {
        $capabilities = maybe_unserialize($user_cap['meta_value']);
        if (is_array($capabilities) && isset($capabilities[$role])) {
            $role_user_ids[] = $user_cap['user_id'];
        }
    }
    if (!empty($role_user_ids)) {
        if (isset($search_conditions['ID'])) {
            $search_conditions['ID'] = array_intersect($search_conditions['ID'], $role_user_ids);
        } else {
            $search_conditions['ID'] = $role_user_ids;
        }
    } else {
        $search_conditions['ID'] = [0]; // No results
    }
}
// Get all users with search and role filters applied
$all_users = $medoo->select('wp_users', [
    'wp_users.ID',
    'wp_users.user_login',
    'wp_users.user_email',
    'wp_users.display_name',
    'wp_users.user_registered'
], array_merge($search_conditions, [
    'ORDER' => ['wp_users.user_registered' => 'DESC']
]));
// Apply level filter if specified
$filtered_users = [];
if (is_array($all_users)) {
    foreach ($all_users as $user) {
        $include_user = true;
        // Level filter
        if (!empty($level)) {
            $user_level = get_user_level($user['ID']);
            if ($user_level != $level) {
                $include_user = false;
            }
        }
        if ($include_user) {
            $filtered_users[] = $user;
        }
    }
}
// Get total count after filtering
$total_users = count($filtered_users);
// Apply pagination
$total_pages = ceil($total_users / $items_per_page);
$users = array_slice($filtered_users, $offset, $items_per_page);
// Generate HTML
$html = '';
foreach ($users as $user) {
    $user_id = $user['ID'];
    // Get user meta data
    $user_meta = get_user_meta_data($user_id, $medoo);
    // Create user data array for compatibility with existing functions
    $user_data = array_merge($user, $user_meta);
    $user_phone = get_user_phone($user_data);
    $user_full_name = get_user_full_name($user_data);
    $user_first_name = $user_data['first_name'] ?? $user_data['billing_first_name'] ?? '';
    $user_last_name = $user_data['last_name'] ?? $user_data['billing_last_name'] ?? '';
    $user_level_display = get_user_level_display($user_id);
    // Get user role
    $user_capabilities = maybe_unserialize($user_meta['wp_capabilities'] ?? '');
    $user_role = !empty($user_capabilities) ? array_key_first($user_capabilities) : 'subscriber';
    $user_role_persian = get_user_role_persian($user_role);
    $user_managed_games = get_user_managed_games($user_id, $medoo);
    // Get user IBAN (شماره شبا)
    $user_iban = get_user_meta($user_id, 'withdrawal_owner_shaba', true);
    $user_iban_display = !empty($user_iban) ? esc_html($user_iban) : '---';
    // Get current user and their role
    $current_user = wp_get_current_user();
    $current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';
    // Check permissions
    $can_delete = ($current_user_role === 'administrator' && $user_role !== 'administrator');
    $can_edit = false;
    $can_manage_banking = false;
    // Role management rules - only allow editing of the restricted roles
    if ($current_user_role === 'administrator') {
        // Administrator can edit only the allowed roles
        $can_edit = in_array($user_role, ['customer', 'sans_manager', 'poshtiban', 'supervisor', 'compiler']);
    } elseif ($current_user_role === 'supervisor') {
        // Shopist can edit: customer, poshtiban, compiler, sans_manager
        $can_edit = in_array($user_role, ['customer', 'poshtiban', 'compiler', 'sans_manager']);
    } elseif ($current_user_role === 'poshtiban') {
        // Poshtiban can edit: customer, poshtiban, sans_manager
        $can_edit = in_array($user_role, ['customer', 'poshtiban', 'sans_manager', 'compiler']);
    } elseif ($current_user_role === 'accounting') {
        // Accounting can edit: customer, poshtiban, sans_manager, compiler, supervisor, accounting
        $can_edit = in_array($user_role, ['customer', 'sans_manager', 'poshtiban', 'supervisor', 'compiler', 'accounting']);
    }
    // Banking management permissions
    if (in_array($current_user_role, ['administrator', 'accounting', 'poshtiban', 'supervisor'])) {
        $can_manage_banking = true;
    }
    // Permission for creating password (Same as edit permission)
    $can_create_password = $can_edit;

    $html .= '<tr class="border-b border-[#E4EBF0] hover:bg-gray-50">';
    $html .= '<td class="px-6 py-4 text-sm font-yekan-bold text-navyBlue">' . esc_html($user_phone) . '</td>';
    $html .= '<td class="px-6 py-4 text-sm font-yekan-bold text-navyBlue">' . esc_html($user_full_name) . '</td>';
    $html .= '<td class="px-6 py-4">' . $user_level_display . '</td>';
    $html .= '<td class="px-6 py-4 text-sm font-yekan-bold text-navyBlue">' . esc_html($user_role_persian) . '</td>';
    $html .= '<td class="px-6 py-4">';
    $html .= '<a href="' . home_url('/profile/' . $user_id) . '" class="text-sm text-white bg-primary-500 hover:bg-primary-600 transition-all duration-300 rounded-lg px-3 py-1 shadow-[0_2px_2px_1px_#00000042]" target="_blank">';
    $html .= 'ID: ' . $user_id;
    $html .= '</a>';
    $html .= '</td>';
    $html .= '<td class="px-6 py-4 text-sm font-yekan-bold text-navyBlue">';
    $html .= '<div class="flex items-center gap-2">';
    $html .= '<span>' . $user_iban_display . '</span>';
    if ($can_manage_banking) {
        if (!empty($user_iban)) {
            $html .= '<button class="view-banking-btn text-[#137cb1] hover:text-[#0F5A8A] transition-colors duration-200" data-user-id="' . $user_id . '" title="مشاهده اطلاعات بانکی">';
            $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>';
            $html .= '</svg>';
            $html .= '</button>';
        } else {
            $html .= '<button class="add-banking-btn text-[#FD7013] hover:text-[#CA5608] transition-colors duration-200" data-user-id="' . $user_id . '" title="افزودن اطلاعات بانکی">';
            $html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">';
            $html .= '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>';
            $html .= '</svg>';
            $html .= '</button>';
        }
    }
    $html .= '</div>';
    $html .= '</td>';
    $html .= '<td class="px-6 py-4">' . $user_managed_games . '</td>';
    $html .= '<td class="px-6 py-4">';
    $html .= '<div class="flex gap-2">';
    // Edit button
    if ($can_edit) {
        $html .= '<button class="w-12 h-6 flex items-center justify-center edit-user-btn text-[#137cb1] text-sm font-yekan-bold" data-user-id="' . $user_id . '" data-user-phone="' . esc_attr($user_phone) . '" data-user-first-name="' . esc_attr($user_first_name) . '" data-user-last-name="' . esc_attr($user_last_name) . '" data-user-role="' . esc_attr($user_role) . '">ویرایش</button>';
    } else {
        $html .= '<button class="w-12 h-6 flex items-center justify-center edit-user-btn-disabled text-gray-400 text-sm font-yekan-bold cursor-not-allowed" data-user-id="' . $user_id . '" title="شما دسترسی به ویرایش این کاربر ندارید">ویرایش</button>';
    }
    // Delete button
    if ($can_delete) {
        $html .= '<button class="w-12 h-6 flex items-center justify-center delete-user-btn text-red-600 hover:text-red-800 text-sm font-yekan-bold" data-user-id="' . $user_id . '">حذف</button>';
    } else {
        $html .= '<button class="w-12 h-6 flex items-center justify-center delete-user-btn-disabled text-gray-400 text-sm font-yekan-bold cursor-not-allowed" data-user-id="' . $user_id . '" title="شما دسترسی به حذف کاربر ندارید">حذف</button>';
    }
    // Create Password Button
    if ($can_create_password) {
        $html .= '<button class="w-12 h-6 flex items-center justify-center create-password-btn text-[#FD7013] text-sm font-yekan-bold" data-user-id="' . $user_id . '" data-user-phone="' . esc_attr($user_phone) . '">رمز ثابت</button>';
    } else {
        $html .= '<button class="w-12 h-6 flex items-center justify-center create-password-btn-disabled text-gray-400 text-sm font-yekan-bold cursor-not-allowed" data-user-id="' . $user_id . '" title="شما دسترسی به ایجاد رمز ندارید">رمز ثابت</button>';
    }
    $html .= '</div>';
    $html .= '</td>';
    $html .= '</tr>';
}
// Generate pagination
$pagination_html = '';
if ($total_pages > 1) {
    $pagination_html .= '<div class="flex justify-center items-center gap-2 mt-6">';
    // Previous page
    if ($page > 1) {
        $pagination_html .= '<a href="?page=' . ($page - 1) . '" class="flex items-center gap-2 px-3 py-2 text-sm font-yekan-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 pagination-link">';
        $pagination_html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>';
        $pagination_html .= '</a>';
    }
    // Page numbers
    $start_page = max(1, $page - 2);
    $end_page = min($total_pages, $page + 2);
    for ($i = $start_page; $i <= $end_page; $i++) {
        $active_class = ($i == $page) ? 'bg-[#FD7013] text-white' : 'text-gray-700 bg-white border border-gray-300 hover:bg-gray-50';
        $pagination_html .= '<a href="?page=' . $i . '" class="px-3 py-2 text-sm font-yekan-bold rounded-lg pagination-link ' . $active_class . '">' . $i . '</a>';
    }
    // Next page
    if ($page < $total_pages) {
        $pagination_html .= '<a href="?page=' . ($page + 1) . '" class="flex items-center gap-2 px-3 py-2 text-sm font-yekan-bold text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 pagination-link">';
        $pagination_html .= '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>';
        $pagination_html .= '</a>';
    }
    $pagination_html .= '</div>';
}
// Send response
wp_send_json_success(array(
    'html' => $html,
    'pagination' => $pagination_html,
    'total_users' => $total_users,
    'total_pages' => $total_pages,
    'current_page' => $page,
    'items_per_page' => $items_per_page
));