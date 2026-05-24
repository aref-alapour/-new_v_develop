<?php
/**
 * saeed_store
 *
 * توابع: saeed_store
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 30-43)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function saeed_store ( $val='', $die=false ) {
    $line  = '[' . date('Y-m-d H:i:s') . '] ';
    $line .= is_scalar($val) ? (string) $val : wp_json_encode($val, JSON_UNESCAPED_UNICODE);
    $line .= "\n";

    $log_dir = WP_CONTENT_DIR . '/ez-logs';
    if (!is_dir($log_dir)) {
        @mkdir($log_dir, 0755, true);
    }
    @file_put_contents($log_dir . '/saeed-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);

    if ( $die )
        die();
}
