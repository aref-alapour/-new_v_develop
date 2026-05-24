<?php
add_action( 'wp_ajax_v2_ajax_handler',        'v2_ajax_handler_callback' );
add_action( 'wp_ajax_nopriv_v2_ajax_handler', 'v2_ajax_handler_callback' );
function v2_ajax_handler_callback() {
    global $wpdb;
    
    // تنظیم headers برای جلوگیری از کش شدن AJAX
    if (!headers_sent()) {
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
    
    // غیرفعال کردن LiteSpeed Cache برای AJAX
    if (defined('LSCACHE_NO_CACHE')) {
        do_action('litespeed_control_set_nocache', 'AJAX request');
    }
    
    // check_ajax_referer('v2-ajax-nonce', 'nonce');

    $callback_file = Theme_PATH . "app/ajax/callbacks/" . $_POST['callback'] . '.php';
    
    if (file_exists($callback_file)) {
        require_once $callback_file;
    }

    wp_die();
}