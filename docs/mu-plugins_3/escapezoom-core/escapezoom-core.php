<?php

/**
 * Plugin Name: EscapeZoom Core
 * Description: Core logic engine: Eloquent, DDD modules (Games, etc.), EZ-Query API. No theme dependency.
 * Version: 1.0.0
 */

/**
 * پروداکشن و دیپلوی:
 * - روی سرور لازم: vendor/ (composer), dist/ (خروجی npm run build از assets/), assets/css/, assets/vendor/. node_modules روی سرور لازم نیست و آپلود نشود.
 * - امنیت: دسترسی به پوشه‌های سورس (مثل assets/stencil) را می‌توان با .htaccess یا Nginx محدود کرد؛ کاربر فقط به dist و فایل‌های استاتیک نیاز دارد.
 *
 * نگهداری (Maintenance):
 * - کش مرورگر: AssetManager از filemtime() برای نسخه‌گذاری استفاده می‌کند؛ با هر npm run build ورژن به‌روز شده و کش کاربران آپدیت می‌شود.
 * - پاکسازی گیت: اگر فایل‌های قدیمی با نام هش در ایندکس گیت مانده‌اند، یک‌بار اجرا کن: git rm -r --cached dist سپس فایل‌های ثابت جدید را add و commit کن.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!defined('EZ_CORE_PATH')) {
    define('EZ_CORE_PATH', __DIR__ . DIRECTORY_SEPARATOR);
}

require_once __DIR__ . '/src/Core/Bootstrap.php';

\EscapeZoom\Core\Core\Bootstrap::boot();

require_once __DIR__ . '/database/init.php';

if (!defined('EZ_CORE_BOOTED') || !EZ_CORE_BOOTED) {
    add_action('admin_notices', function (): void {
        $vendor = __DIR__ . '/vendor/autoload.php';
        if (!is_file($vendor)) {
            echo '<div class="notice notice-error"><p><strong>EscapeZoom Core:</strong> '
                . 'برای فعال بودن بلوک‌های گوتنبرگ و API باید در پوشه <code>wp-content/mu-plugins/escapezoom-core</code> دستور <code>composer install</code> را اجرا کنید.</p></div>';
        }
    });
}

if (defined('EZ_CORE_BOOTED') && EZ_CORE_BOOTED) {
    // Initialize centralized asset manager
    \EscapeZoom\Core\Modules\Core\AssetManager::init();
    
    \EscapeZoom\Core\AdminHeartbeatDisable::register();
    \EscapeZoom\Core\Core\AdminAppearance::register();
    \EscapeZoom\Core\Modules\Domain\EzDomainService::register();
    \EscapeZoom\Core\Modules\Games\LocationTaxonomy::register();
    \EscapeZoom\Core\Modules\Games\Admin\AdminBootstrap::register();
    \EscapeZoom\Core\Modules\Games\RewriteRules::register();
    add_action('wp', [\EscapeZoom\Core\Modules\Games\RewriteRules::class, 'maybeLoadProductTemplate'], 5);
    add_action('rest_api_init', function (): void {
        \EscapeZoom\Core\API\EzQueryRestController::create()->registerRoutes();
        \EscapeZoom\Core\Modules\Comments\API\CommentsRestController::create()->registerRoutes();
    });
    \EscapeZoom\Core\Blocks\BlocksBootstrap::boot();
    \EscapeZoom\Core\Scheduler\JobScheduler::register();

    // Brand Module Registration
    \EscapeZoom\Core\Modules\Brands\BrandBootstrap::register();

    // Archives (Dictionary + Archive Map) Module
    \EscapeZoom\Core\Modules\Archives\ArchiveBootstrap::register();

    // Redirects Module Registration
    \EscapeZoom\Core\Modules\Redirects\RedirectManager::register();
    \EscapeZoom\Core\Modules\Redirects\RedirectAdmin::register();
    \EscapeZoom\Core\Modules\Redirects\RedirectSuggestions::register();
    \EscapeZoom\Core\Modules\Redirects\RedirectImportExport::register();
}
