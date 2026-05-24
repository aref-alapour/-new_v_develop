<?php
/**
 * hooks: wpseo_sitemap_exclude_empty_terms, wpseo_sitemap_entry
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6439-6448)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'wpseo_sitemap_exclude_empty_terms', '__return_false' );
add_filter('wpseo_sitemap_entry', function($url, $type, $term) {
    if ($type === 'term' && empty($url)) {
        $url['loc'] = get_term_link($term, $term->taxonomy);
        $url['mod'] = current_time('mysql');
        $url['chf'] = 'daily';
        $url['pri'] = 1;
    }
    return $url;
}, 99, 3);
