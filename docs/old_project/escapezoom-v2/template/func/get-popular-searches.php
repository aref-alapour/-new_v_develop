<?php

/**
 * دریافت محبوب‌ترین جستجوها
 * این فایل را در header فراخوانی کنید
 */

// بارگذاری Medoo (فقط اگر قبلاً لود نشده باشد)
if (!function_exists('medoo')) {
    require_once(get_template_directory() . '/inc/medoo/init.php');
}

/**
 * تابع دریافت محبوب‌ترین جستجوها
 * 
 * @param int $limit تعداد نتایج (پیش‌فرض: 6)
 * @param int $min_clicks حداقل تعداد کلیک (پیش‌فرض: 2)
 * @return array لیست محبوب‌ترین جستجوها
 */
function ez_get_popular_searches($limit = 6, $min_clicks = 1)
{
    $medoo = medoo();

    try {
        $popular = $medoo->select('wp_popular_searches', '*', [
            'search_count[>=]' => $min_clicks,
            'ORDER' => ['search_count' => 'DESC'],
            'LIMIT' => $limit
        ]);

        return $popular ?: [];
    } catch (Exception $e) {
        error_log('Error getting popular searches: ' . $e->getMessage());
        return [];
    }
}

/**
 * تابع ساخت HTML برای محبوب‌ترین جستجوها
 * 
 * @param int $limit تعداد نمایش
 * @return string HTML (خالی اگر جدول خالی باشد)
 */
function ez_render_popular_searches_html($limit = 6)
{
    $popular = ez_get_popular_searches($limit);

    // اگر جدول خالی بود، هیچی برنگردان
    if (empty($popular)) {
        return '';
    }

    $html = '<div class="popular-searches mb-4">';
    $html .= '<p class="text-sm font-bold text-[#62748E] mb-3 px-4">محبوب‌ترین جستجوها:</p>';
    $html .= '<ul class="space-y-3">';

    foreach ($popular as $item) {
        $html .= '<li>';
        $html .= '<a href="' . esc_url($item['search_url']) . '" 
                     class="flex items-center justify-between px-4 py-2 hover:bg-gray-50 rounded transition-colors">';
        $html .= '<span class="text-sm font-medium text-gray-700">' . esc_html($item['search_title']) . '</span>';
        $html .= '<span class="flex items-center gap-2">';
        $html .= '<span class="text-xs text-gray-400">' . number_format($item['search_count']) . '</span>';
        $html .= '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">';
        $html .= '<path d="M6 12L10 8L6 4" stroke="#90A1B9" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>';
        $html .= '</svg>';
        $html .= '</span>';
        $html .= '</a>';
        $html .= '</li>';
    }

    $html .= '</ul>';
    $html .= '</div>';

    return $html;
}
