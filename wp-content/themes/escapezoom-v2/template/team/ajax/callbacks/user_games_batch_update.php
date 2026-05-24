<?php
// Batch update user's game connections

$user_id = intval($_POST['user_id'] ?? 0);
$owner_games = isset($_POST['owner_games']) ? json_decode(stripslashes($_POST['owner_games']), true) : [];
$sans_games = isset($_POST['sans_games']) ? json_decode(stripslashes($_POST['sans_games']), true) : [];
$confirmed_games = isset($_POST['confirmed_games']) ? json_decode(stripslashes($_POST['confirmed_games']), true) : [];

if (empty($user_id)) {
    wp_send_json_error('شناسه کاربر معتبر نیست.');
}

// Get current user and check permissions
$current_user = wp_get_current_user();
$current_user_role = !empty($current_user->roles) ? $current_user->roles[0] : 'subscriber';

if (!in_array($current_user_role, ['administrator', 'accounting'])) {
    wp_send_json_error('شما دسترسی به این عملیات را ندارید.');
}

// Check if user exists
$user = get_user_by('id', $user_id);
if (!$user) {
    wp_send_json_error('کاربر یافت نشد.');
}

// Check if user has compiler role for owner games
if (!empty($owner_games)) {
    // Check if user has compiler role
    $user_capabilities = get_user_meta($user_id, 'wp_capabilities', true);
    $user_capabilities = maybe_unserialize($user_capabilities);
    $user_primary_role = !empty($user_capabilities) ? array_key_first($user_capabilities) : 'subscriber';
    
    if ($user_primary_role !== 'compiler') {
        wp_send_json_error('فقط کاربرانی با نقش مجموعه‌دار می‌توانند به عنوان مالک بازی انتخاب شوند.');
    }
}

// Check if user has sans_manager role for sans_manager games
if (!empty($sans_games)) {
    $user_capabilities = get_user_meta($user_id, 'wp_capabilities', true);
    $user_capabilities = maybe_unserialize($user_capabilities);
    $user_primary_role = !empty($user_capabilities) ? array_key_first($user_capabilities) : 'subscriber';
    
    // Sans manager can have sans_manager games, compiler can also have sans_manager games
    if (!in_array($user_primary_role, ['sans_manager', 'compiler'])) {
        wp_send_json_error('فقط کاربرانی با نقش مدیر سانس یا مجموعه‌دار می‌توانند به عنوان مدیر سانس بازی انتخاب شوند.');
    }
}

$medoo = medoo();
$errors = [];
$success_count = 0;

// Process owner games (user_ebtal)
foreach ($owner_games as $game_id) {
    $game_id = intval($game_id);
    
    // Check if game should be confirmed (has current owner)
    $needs_confirmation = false;
    foreach ($confirmed_games as $confirmed) {
        if ($confirmed['game_id'] == $game_id && $confirmed['type'] === 'owner') {
            $needs_confirmation = true;
            break;
        }
    }
    
    // If game has current owner and not confirmed, skip
    if (!$needs_confirmation) {
        $current_owner = get_post_meta($game_id, 'user_ebtal', true);
        if (!empty($current_owner) && $current_owner != $user_id) {
            continue; // Skip games that need confirmation but weren't confirmed
        }
    }
    
    // Update meta
    update_post_meta($game_id, 'user_ebtal', $user_id);
    
    // Remove from sans_manager if same user
    $sans_manager = get_post_meta($game_id, 'sans_manager', true);
    if ($sans_manager == $user_id) {
        delete_post_meta($game_id, 'sans_manager');
    }
    
    $success_count++;
}

// Process sans_manager games
foreach ($sans_games as $game_id) {
    $game_id = intval($game_id);
    
    // Check if game should be confirmed (has current sans_manager)
    $needs_confirmation = false;
    foreach ($confirmed_games as $confirmed) {
        if ($confirmed['game_id'] == $game_id && $confirmed['type'] === 'sans') {
            $needs_confirmation = true;
            break;
        }
    }
    
    // If game has current sans_manager and not confirmed, skip
    if (!$needs_confirmation) {
        $current_sans = get_post_meta($game_id, 'sans_manager', true);
        if (!empty($current_sans) && $current_sans != $user_id) {
            continue; // Skip games that need confirmation but weren't confirmed
        }
    }
    
    // Update meta
    update_post_meta($game_id, 'sans_manager', $user_id);
    
    // Remove from user_ebtal if same user
    $owner = get_post_meta($game_id, 'user_ebtal', true);
    if ($owner == $user_id) {
        delete_post_meta($game_id, 'user_ebtal');
    }
    
    $success_count++;
}

// Remove old connections that are not in new lists - using Medoo
$current_owner_metas = $medoo->select('wp_postmeta', [
    'post_id'
], [
    'meta_key' => 'user_ebtal',
    'meta_value' => $user_id
]);

$current_sans_metas = $medoo->select('wp_postmeta', [
    'post_id'
], [
    'meta_key' => 'sans_manager',
    'meta_value' => $user_id
]);

// Remove owner games that are not in new list
foreach ($current_owner_metas as $meta) {
    $game_id = intval($meta['post_id']);
    if (!in_array($game_id, $owner_games)) {
        delete_post_meta($game_id, 'user_ebtal');
    }
}

// Remove sans_manager games that are not in new list
foreach ($current_sans_metas as $meta) {
    $game_id = intval($meta['post_id']);
    if (!in_array($game_id, $sans_games)) {
        delete_post_meta($game_id, 'sans_manager');
    }
}

wp_send_json_success('تغییرات با موفقیت اعمال شد.');
