<?php

// بارگذاری WordPress
if (!defined('ABSPATH')) {
    $wp_load_path = $_SERVER['DOCUMENT_ROOT'];
    if (strpos($_SERVER['REQUEST_URI'], '/escapezoom_wp/') !== false) {
        $wp_load_path .= '/escapezoom_wp';
    }
    require_once($wp_load_path . '/wp-load.php');
}

require_once(get_template_directory() . '/inc/medoo/init.php');
$medoo = medoo();

$user_id = get_current_user_id();
$is_logged_in = ($user_id > 0);
$sent_user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if ($sent_user_id > 0 && $is_logged_in && $sent_user_id != $user_id) {
    echo json_encode([
        'success' => false,
        'message' => 'خطای امنیتی'
    ]);
    exit;
}

try {
    $deleted_from = [];

    setcookie('ez_user_searches', '', time() - 3600, '/');
    $deleted_from[] = 'cookie';

    if ($is_logged_in) {
        $medoo->delete('wp_user_search_history', ['user_id' => $user_id]);
        $deleted_from[] = 'database';
    }

    echo json_encode([
        'success' => true,
        'message' => 'جستجوهای اخیر شما با موفقیت حذف شد',
        'user_id' => $user_id,
        'deleted_from' => implode('+', $deleted_from)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطا: ' . $e->getMessage()
    ]);
}

exit;
