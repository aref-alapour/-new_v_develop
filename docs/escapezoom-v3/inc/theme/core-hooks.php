<?php
if (!defined('ABSPATH')) {
	exit;
}

/* =======================================================
    unset wordpress widget [start]
========================================================= */

add_action('wp_dashboard_setup', function () {
    global $wp_meta_boxes;

    // حذف ویجت‌های هسته وردپرس
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_site_health']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);

    // حذف ویجت‌های پلاگین‌ها
    // foreach ($wp_meta_boxes['dashboard'] as $position) {
    //     foreach ($position as $priority) {
    //         foreach ($priority as $id => $widget) {
    //             if (strpos($id, 'yoast') !== false || strpos($id, 'elementor') !== false || strpos($id, 'jetpack') !== false || strpos($id, 'rank_math') !== false) {
    //                 unset($wp_meta_boxes['dashboard'][$widget['context']][$widget['priority']][$id]);
    //             }
    //         }
    //     }
    // }
});


function ez_fix_menu_urls($menu_items)
{
    if (empty($menu_items) || !is_array($menu_items)) {
        return $menu_items;
    }

    $main_domain_url = 'https://escapezoom.ir/';
    $main_domain_url_without_slash = 'https://escapezoom.ir';

    foreach ($menu_items as &$item) {
        if (!empty($item['url'])) {
            $item['url'] = ez_replace_domain_url($item['url'], $main_domain_url, $main_domain_url_without_slash);
        }
        if (!empty($item['children']) && is_array($item['children'])) {
            foreach ($item['children'] as &$child) {
                if (!empty($child['url'])) {
                    $child['url'] = ez_replace_domain_url($child['url'], $main_domain_url, $main_domain_url_without_slash);
                }
            }
        }
    }

    return $menu_items;
}

function ez_replace_domain_url($url, $main_domain_url, $main_domain_url_without_slash)
{
    if (strpos($url, $main_domain_url) === 0) {
        $path = str_replace($main_domain_url, '', $url);
        return home_url('/' . ltrim($path, '/'));
    } elseif (strpos($url, $main_domain_url_without_slash) === 0) {
        $path = str_replace($main_domain_url_without_slash, '', $url);
        return home_url('/' . ltrim($path, '/'));
    }

    return $url;
}

add_filter('option_ez_mega_menu_header', 'ez_fix_menu_urls');
add_filter('option_ez_mega_menu_footer', 'ez_fix_menu_urls');

add_filter('heartbeat_send', '__return_false');
add_filter('heartbeat_tick', '__return_false');

// غیرفعال کردن سایت‌مپ هسته وردپرس (wp-sitemap.xml)
add_filter('wp_sitemaps_enabled', '__return_false');

// مسیرهای wp-sitemap*.xml هسته → ۴۰۴ | فقط sitemap.xml به ایندکس Yoast ریدایرکت می‌شود
add_action('template_redirect', function () {
    if (empty($_SERVER['REQUEST_URI'])) {
        return;
    }
    $path = wp_parse_url(wp_unslash($_SERVER['REQUEST_URI']), PHP_URL_PATH);
    if ($path === false || $path === '') {
        return;
    }
    $base = basename($path);

    if (strpos($base, 'wp-sitemap') === 0) {
        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        nocache_headers();
        $tpl = get_404_template();
        if ($tpl) {
            include $tpl;
        } else {
            wp_die('', '', array('response' => 404));
        }
        exit;
    }

    if ($base === 'sitemap.xml') {
        wp_safe_redirect(home_url('/sitemap_index.xml'), 301, 'EscapeZoom');
        exit;
    }
}, 0);

add_action('init', function () {
    if (is_admin()) {
        return;
    }

    wp_deregister_script('heartbeat');
});
