<?php
/**
 * Plugin Name: EscapeZoom Core Loader
 * Description: Loads EscapeZoom Core mu-plugin
 * Version: 1.0.0
 * Author: EscapeZoom Team
 */

// بارگذاری mu-plugin اصلی
if (file_exists(__DIR__ . '/escapezoom-core/escapezoom-core.php')) {
    require_once __DIR__ . '/escapezoom-core/escapezoom-core.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo '<strong>EscapeZoom Core:</strong> فایل mu-plugin یافت نشد!';
        echo '</p></div>';
    });
}
