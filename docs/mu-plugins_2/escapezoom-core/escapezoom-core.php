<?php
/**
 * Plugin Name: EscapeZoom Core
 * Plugin URI: https://escapezoom.com
 * Description: EscapeZoom Core - Eloquent ORM, Illuminate packages & Corcel for WordPress integration
 * Version: 1.0.0
 * Author: EscapeZoom Team
 * Author URI: https://escapezoom.com
 * Requires at least: 6.0
 * Requires PHP: 8.2
 * License: Proprietary
 * Text Domain: escapezoom-core
 */

// اگر مستقیم دسترسی شود، خروج
if (!defined('ABSPATH')) {
    exit;
}

// تعریف ثابت‌های پلاگین
define('EZCORE_VERSION', '1.0.0');
define('EZCORE_PATH', __DIR__);
define('EZCORE_URL', plugins_url('', __FILE__));

// بارگذاری Composer Autoloader
if (file_exists(EZCORE_PATH . '/vendor/autoload.php')) {
    require_once EZCORE_PATH . '/vendor/autoload.php';
} else {
    // نمایش خطا اگر Composer نصب نشده
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>EscapeZoom Core:</strong> لطفاً ابتدا <code>composer install</code> را در پوشه mu-plugin اجرا کنید.';
        echo '</p></div>';
    });
    return;
}

// بارگذاری Helper Functions
require_once EZCORE_PATH . '/src/helpers.php';
// توکن AJAX اختصاصی Team
if (file_exists(EZCORE_PATH . '/api/auth.php')) {
    require_once EZCORE_PATH . '/api/auth.php';
}

// بوت کردن Database بعد از Corcel (اولویت 10) تا resolver ما با اتصال wordpress استفاده شود
add_action('plugins_loaded', function() {
    \EscapeZoom\Core\Database::boot();
}, 10);

// درگاه واحد AJAX – قالب‌ها با فیلتر ez_ajax_register_files فایل ثبت هندلر خود را اضافه می‌کنند
$ez_ajax_callback = function() {
    $register_files = apply_filters('ez_ajax_register_files', []);
    foreach (is_array($register_files) ? $register_files : [] as $file) {
        if (is_string($file) && is_file($file)) {
            require_once $file;
        }
    }
    require_once EZCORE_PATH . '/ez-ajax.php';
};
add_action('wp_ajax_' . \EscapeZoom\Core\Ajax::GATEWAY_ACTION, $ez_ajax_callback, 1);
add_action('wp_ajax_nopriv_' . \EscapeZoom\Core\Ajax::GATEWAY_ACTION, $ez_ajax_callback, 1);

// AJAX امن حالا با .htaccess مستقیم به ez-ajax.php می‌ره
// دیگه نیازی به WordPress rewrite نیست

// راه‌اندازی Corcel برای استفاده از مدل‌های WordPress
add_action('plugins_loaded', function() {
    // تنظیم Corcel برای استفاده از connection پیش‌فرض Eloquent
    if (class_exists('\Corcel\Database')) {
        try {
            \Corcel\Database::connect([
                'database'  => DB_NAME,
                'username'  => DB_USER,
                'password'  => DB_PASSWORD,
                'host'      => DB_HOST,
                'prefix'    => $GLOBALS['table_prefix'] ?? 'wp_',
            ]);
        } catch (\Exception $e) {
            error_log('EscapeZoom Core - Corcel connection error: ' . $e->getMessage());
        }
    }
}, 5);

// Hook برای اطمینان از بوت شدن در WP-CLI
if (defined('WP_CLI') && WP_CLI) {
    \EscapeZoom\Core\Database::boot();
}

/**
 * دریافت instance از مدل Marketing
 * 
 * @return \EscapeZoom\Core\Models\Marketing
 */
function ez_marketing() {
    return new \EscapeZoom\Core\Models\Marketing();
}

/**
 * دریافت instance از مدل ProductData
 * 
 * @return \EscapeZoom\Core\Models\ProductData
 */
function ez_products() {
    return new \EscapeZoom\Core\Models\ProductData();
}

/**
 * دریافت instance از مدل BookingHistory
 * 
 * @return \EscapeZoom\Core\Models\BookingHistory
 */
function ez_bookings() {
    return new \EscapeZoom\Core\Models\BookingHistory();
}

/**
 * دریافت یک اتصال دیتابیس
 * 
 * @param string|null $name نام اتصال (default یا external)
 * @return \Illuminate\Database\Connection
 */
function ez_db(?string $name = null) {
    return \EscapeZoom\Core\Database::connection($name);
}

/**
 * دریافت Query Builder برای یک جدول (connection default)
 * 
 * @param string $table
 * @return \Illuminate\Database\Query\Builder
 */
function ez_table(string $table) {
    return \EscapeZoom\Core\Database::connection('default')->table($table);
}

/**
 * دریافت Query Builder برای یک جدول (connection external)
 * 
 * @param string $table
 * @return \Illuminate\Database\Query\Builder
 */
function ez_external_table(string $table) {
    return \EscapeZoom\Core\Database::connection('external')->table($table);
}

// ─── مدل‌های جدول‌های اصلی وردپرس (با relations) ─────────────────────────────
// استفاده: \EscapeZoom\Core\Models\WordPress\Post::find(1)->author; یا ez_wp_posts()->where(...)->get();

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\WordPress\Post */
function ez_wp_posts() {
    return \EscapeZoom\Core\Models\WordPress\Post::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\WordPress\User */
function ez_wp_users() {
    return \EscapeZoom\Core\Models\WordPress\User::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\WordPress\Term */
function ez_wp_terms() {
    return \EscapeZoom\Core\Models\WordPress\Term::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\WordPress\Taxonomy */
function ez_wp_taxonomies() {
    return \EscapeZoom\Core\Models\WordPress\Taxonomy::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\WordPress\Comment */
function ez_wp_comments() {
    return \EscapeZoom\Core\Models\WordPress\Comment::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\WordPress\Option */
function ez_wp_options() {
    return \EscapeZoom\Core\Models\WordPress\Option::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\Brand */
function ez_brands() {
    return \EscapeZoom\Core\Models\Brand::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\Product */
function ez_product_cache() {
    return \EscapeZoom\Core\Models\Product::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\City */
function ez_cities() {
    return \EscapeZoom\Core\Models\City::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\Area */
function ez_areas() {
    return \EscapeZoom\Core\Models\Area::query();
}

/** @return \Illuminate\Database\Eloquent\Builder|\EscapeZoom\Core\Models\EzUser */
function ez_users_ez() {
    return \EscapeZoom\Core\Models\EzUser::query();
}
