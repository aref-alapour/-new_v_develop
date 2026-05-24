<?php
/**
 * hooks: wpseo_canonical
 *
 * ثبت هوک/فیلتر بدون تابع نام‌دار در همین بلوک.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6595-6603)
 * نوع: هوک وردپرس
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter('wpseo_canonical', function($canonical) {
    if (is_page('collections') && is_paged()) {
        $paged = get_query_var('paged') ?: 1;
        $canonical = get_permalink(get_page_by_path('collections')) . 'page/' . $paged . '/';
    } elseif (is_page('collections'))
        $canonical = get_permalink(get_page_by_path('collections'));

    return $canonical;
});
