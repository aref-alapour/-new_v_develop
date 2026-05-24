<?php

/**
 * ثبت کلیک روی نتایج جستجو
 */

// بارگذاری WordPress
if (!defined('ABSPATH')) {
    $wp_load_path = $_SERVER['DOCUMENT_ROOT'];
    if (strpos($_SERVER['REQUEST_URI'], '/escapezoom_wp/') !== false) {
        $wp_load_path .= '/escapezoom_wp';
    }
    require_once($wp_load_path . '/wp-load.php');
}

// بارگذاری Medoo
require_once(get_template_directory() . '/inc/medoo/init.php');
$medoo = medoo();

// دریافت داده‌ها
$search_title = isset($_POST['search_value']) ? sanitize_text_field($_POST['search_value']) : '';
$search_url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : '';

// بررسی داده‌ها
if (empty($search_title) || empty($search_url)) {
    echo json_encode(['success' => false, 'message' => 'داده‌های ناقص']);
    exit;
}

try {
    // چک کردن آیا قبلاً وجود دارد (بر اساس search_title که unique است)
    $existing = $medoo->get('wp_popular_searches', '*', [
        'search_title' => $search_title
    ]);

    if ($existing) {
        // آپدیت کردن تعداد کلیک
        $medoo->update('wp_popular_searches', [
            'search_count' => $existing['search_count'] + 1,
            'search_url' => $search_url // آپدیت URL (اگر تغییر کرده باشد)
        ], [
            'search_title' => $search_title
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'کلیک ثبت شد',
            'click_count' => $existing['search_count'] + 1
        ]);
    } else {
        // درج جدید
        $medoo->insert('wp_popular_searches', [
            'search_title' => $search_title,
            'search_url' => $search_url,
            'search_count' => 1
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'جستجو ثبت شد',
            'click_count' => 1
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'خطا: ' . $e->getMessage()
    ]);
}

exit;
