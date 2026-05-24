<?php
/**
 * بارگذاری فوق‌سبک: فقط DB + Eloquent (بدون WordPress)
 * برای استفاده مجدد در همه callbackها
 */

// فقط یک بار اجرا شود
if (defined('EZ_BOOTSTRAP_LOADED')) {
    return;
}
define('EZ_BOOTSTRAP_LOADED', true);

// ۱. فقط DB constants (بدون لود wp-settings)
if (!defined('ABSPATH')) {
    define('EZ_AJAX_ONLY', true);
    $wp_config = __DIR__ . '/../../../wp-config.php';
    if (!file_exists($wp_config)) {
        die(json_encode(['success' => false, 'error' => 'wp-config not found'], JSON_UNESCAPED_UNICODE));
    }
    require_once $wp_config;
}

// تعریف WP_CONTENT_DIR اگر وجود نداشت
if (!defined('WP_CONTENT_DIR')) {
    define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
}

// ۲. Composer Autoload (یک بار)
static $autoload_loaded = false;
if (!$autoload_loaded) {
    require_once __DIR__ . '/vendor/autoload.php';
    $autoload_loaded = true;
}

// ۳. Eloquent Boot (یک بار)
if (!class_exists('\EscapeZoom\Core\Database') || !\EscapeZoom\Core\Database::getCapsule()) {
    \EscapeZoom\Core\Database::boot();
}

// ۴. Helper functions
if (!function_exists('ez_db')) {
    function ez_db($name = 'default') {
        return \EscapeZoom\Core\Database::connection($name);
    }
}

if (!function_exists('ez_table')) {
    function ez_table($table) {
        global $table_prefix;
        $prefix = $table_prefix ?? 'wp_';
        return \EscapeZoom\Core\Database::connection('default')->table($prefix . $table);
    }
}

// ۵. تابع کمکی برای user level
if (!function_exists('ez_user_level_from_points')) {
    function ez_user_level_from_points($points) {
        return ($points <= 150) ? 1 : (($points <= 700) ? 2 : (($points <= 7000) ? 3 : 4));
    }
}

// ۶. نقشه نقش‌ها به فارسی
if (!function_exists('ez_role_persian')) {
    function ez_role_persian($role) {
        static $map = [
            'administrator' => 'مدیر', 'accounting' => 'حسابدار', 'sans_manager' => 'مدیر سانس',
            'shopist' => 'شاپ منیجر', 'poshtiban' => 'پشتیبان', 'compiler' => 'مجموعه‌دار',
            'supervisor' => 'شاپ منیجر', 'customer' => 'مشتری', 'subscriber' => 'مشترک',
            'contentist' => 'محتواگذار', 'commentchi' => 'کامنتچی', 'translator' => 'مترجم'
        ];
        return $map[$role] ?? $role;
    }
}

// ۷. تبدیل اعداد فارسی به انگلیسی
if (!function_exists('ez_persian_to_english')) {
    function ez_persian_to_english($str) {
        return str_replace(
            ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $str
        );
    }
}
