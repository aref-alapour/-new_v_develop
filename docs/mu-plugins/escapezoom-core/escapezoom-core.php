<?php
/**
 * Plugin Name: EscapeZoom Core Loader
 * Description: Safe MU loader for EscapeZoom Core package.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

$ezCoreBasePath = __DIR__;

if (!defined('EZ_CORE_PATH')) {
    define('EZ_CORE_PATH', $ezCoreBasePath . '/');
}

if (!defined('EZ_CORE_FILE')) {
    define('EZ_CORE_FILE', __FILE__);
}

if (!defined('EZ_CORE_VERSION')) {
    define('EZ_CORE_VERSION', '1.0.0');
}

$ezAutoload = $ezCoreBasePath . '/vendor/autoload.php';

if (!is_file($ezAutoload)) {
    add_action('admin_notices', static function (): void {
        if (!current_user_can('manage_options')) {
            return;
        }
        echo '<div class="notice notice-warning"><p><strong>EscapeZoom Core:</strong> '
            . 'vendor/autoload.php not found. Run <code>composer install</code> in '
            . '<code>wp-content/mu-plugins/escapezoom-core</code>.</p></div>';
    });
    return;
}

require_once $ezAutoload;

if (!class_exists('\EscapeZoom\Core\Core\Bootstrap')) {
    error_log('[EZ_CORE] Bootstrap class not found after autoload.');
    return;
}

try {
    \EscapeZoom\Core\Core\Bootstrap::boot();
} catch (\Throwable $e) {
    error_log('[EZ_CORE] Boot failed: ' . $e->getMessage());
}
