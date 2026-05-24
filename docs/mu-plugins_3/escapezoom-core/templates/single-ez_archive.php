<?php
/**
 * Template: Single ez_archive (طرح آرشیو).
 * محتوای گوتنبرگ پست ez_archive؛ context در $GLOBALS['ez_archive_context'].
 * بلوک «لیست بازی‌های این آرشیو» می‌تواند از ez_archive_context (city_id, area_id, type_id, genre_id, mood_id, theme_id) برای فیلتر محصولات استفاده کند.
 * هر درخواست داده از فرانت (لیست بازی‌ها، فیلترها) باید از REST API (مثلاً escapezoom/v1/query) انجام شود، نه admin-ajax.php.
 *
 * @see EscapeZoom\Core\Modules\Archives\ArchiveRouter
 */

if (!defined('ABSPATH')) {
    exit;
}

$context = $GLOBALS[\EscapeZoom\Core\Modules\Archives\ArchiveRouter::CONTEXT_GLOBAL] ?? null;
$post_id = $context && isset($context->post_id) ? (int) $context->post_id : 0;

if ($post_id <= 0) {
    if (function_exists('get_header')) {
        get_header();
    }
    echo '<main class="ez-archive-main"><p>' . esc_html__('آرشیو یافت نشد.', 'escapezoom-core') . '</p></main>';
    if (function_exists('get_footer')) {
        get_footer();
    }
    return;
}

global $wp_query;
$wp_query = new \WP_Query(['p' => $post_id, 'post_type' => 'ez_archive', 'post_status' => 'publish']);

if (function_exists('get_header')) {
    get_header();
}

echo '<main class="ez-archive-main">';
if ($wp_query->have_posts()) {
    while ($wp_query->have_posts()) {
        $wp_query->the_post();
        the_content();
    }
    wp_reset_postdata();
}
echo '</main>';

if (function_exists('get_footer')) {
    get_footer();
}
