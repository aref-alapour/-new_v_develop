<?php
/**
 * Plugin Name: 000 Test Load Order
 * Description: Test if mu-plugins are loading
 */

// لاگ کردن
error_log('=== 000-test-load.php loaded ===');

// تست در wp_head
add_action('wp_head', function() {
    echo "\n<!-- 000-test-load: MU-Plugin Active -->\n";
});

// تست در admin
add_action('admin_notices', function() {
    echo '<div class="notice notice-info"><p>✅ MU-Plugins در حال اجرا هستند</p></div>';
});
