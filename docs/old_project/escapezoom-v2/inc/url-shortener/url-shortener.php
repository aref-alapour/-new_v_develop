<?php

add_action('init', 'add_shortlink_rewrite_rule');
function add_shortlink_rewrite_rule() {
    add_rewrite_rule('^s/([^/]+)/?', 'index.php?shortcode=$matches[1]', 'top');
}

add_filter('query_vars', 'register_shortlink_query_var');
function register_shortlink_query_var($vars) {
    $vars[] = 'shortcode';
    return $vars;
}

function generate_shortlink($long_url) {
    global $wpdb;

    $long_url = esc_url_raw($long_url);

    // بررسی وجود لینک قبلی
    $row = $wpdb->get_row($wpdb->prepare("SELECT shortcode FROM shortlinks WHERE long_url = %s", $long_url));
    if ($row)
        return home_url('/s/' . $row->shortcode);

    // ساخت کد یونیک
    do {

        $length     = 4;
        $chars      = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $shortcode  = '';

        for ($i = 0; $i < $length; $i++)
            $shortcode .= $chars[random_int(0, strlen($chars) - 1)];

        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM shortlinks WHERE shortcode = %s", $shortcode));
    } while ($exists > 0);

    // ذخیره
    $wpdb->insert('shortlinks', [
        'shortcode' => $shortcode,
        'long_url' => $long_url,
    ]);

    return home_url('/s/' . $shortcode);
}

add_action('init', 'shortlink_redirect_handler');
function shortlink_redirect_handler() {
    global $wpdb;

    $request_uri = trim($_SERVER['REQUEST_URI'], '/');

    // فقط اگه شروع با s/ بود
    if (strpos($request_uri, 's/') !== 0) return;

    $shortcode = sanitize_title(str_replace('s/', '', $request_uri));

    $target = $wpdb->get_var($wpdb->prepare("SELECT long_url FROM shortlinks WHERE shortcode = %s", $shortcode));

    if ($target) {
        wp_redirect($target, 301);
        exit;

    } else {
        // اگر پیدا نشد، 404 بده
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        include get_404_template();
        exit;
    }
}