<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * EZ ARCHITECT TOOL: Outbound HTTP Tracker
 * Logs all server-to-server requests to identify required hosts.
 */
add_action('http_api_debug', function($response, $context, $class, $args, $url) {
    // جلوگیری از ثبت درخواست‌هایی که به خودِ سایت (localhost) زده می‌شود
    $site_url = parse_url(get_site_url(), PHP_URL_HOST);
    $target_host = parse_url($url, PHP_URL_HOST);
    
    if ($target_host === $site_url) return;

    $log_file = WP_CONTENT_DIR . '/ez-http-requests.log';
    
    // جمع‌آوری اطلاعات دیباگ برای فهمیدن اینکه کدام فایل این درخواست را ارسال کرده
    $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
    $source = 'unknown';
    foreach ($trace as $step) {
        if (isset($step['file']) && strpos($step['file'], 'themes') !== false) {
            $source = basename($step['file']) . ' (Line: ' . $step['line'] . ')';
            break;
        } elseif (isset($step['file']) && strpos($step['file'], 'plugins') !== false) {
            $source = 'Plugin: ' . explode('/', str_replace(WP_PLUGIN_DIR . '/', '', $step['file']))[0];
            break;
        }
    }

    $entry = sprintf(
        "[%s] HOST: %s | METHOD: %s | SOURCE: %s | URL: %s\n",
        date('Y-m-d H:i:s'),
        $target_host,
        $args['method'] ?? 'GET',
        $source,
        $url
    );

    file_put_contents($log_file, $entry, FILE_APPEND);
}, 10, 5);
