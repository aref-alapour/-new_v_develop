<?php
// دریافت بازی‌های تبلیغاتی بر اساس citySlug
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// دریافت citySlug از درخواست AJAX
$city_slug = isset($_POST['citySlug']) ? sanitize_text_field($_POST['citySlug']) : '';

if (empty($city_slug)) {
    wp_send_json_error([
        'message' => 'citySlug ارسال نشده است'
    ]);
}

// دریافت term_id از URL یا session (فرض می‌کنیم از query string می‌آید)
global $wp_query;
$term_id = null;

// اگر در صفحه taxonomy هستیم
if (is_tax('product_tag')) {
    $term_id = get_queried_object_id();
} else {
    // اگر term_id از جای دیگری می‌آید، می‌توانید اینجا تنظیم کنید
    // برای مثال از $_POST یا $_GET
    $term_id = isset($_POST['term_id']) ? intval($_POST['term_id']) : null;
}

if (!$term_id) {
    wp_send_json_error([
        'message' => 'term_id یافت نشد'
    ]);
}

// اجرای کدهای PHP که در درخواست شما بود
$city_data = get_option("promotional_products_{$city_slug}", []);
$product_ids = isset($city_data['products']) ? $city_data['products'] : [];
$city_genre_ids = [];

if ($product_ids && !empty($product_ids)) {
    $args = [
        'tag' => [get_term($term_id)->slug],
        'limit' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'status' => 'publish',
        'include' => $product_ids // فقط محصولاتی که ID آن‌ها در product_ids است
    ];
    $products = wc_get_products($args);

    foreach ($products as $product) {
        $city_genre_ids[] = $product->get_id();
    }
}

// بررسی وجود محصولات فیلتر شده
if (empty($city_genre_ids)) {
    wp_send_json_error([
        'message' => 'هیچ محصول تبلیغاتی برای این شهر و ژانر یافت نشد',
        'city_slug' => $city_slug,
        'term_id' => $term_id
    ]);
}

// ارسال پاسخ موفق شامل ID های محصولات فیلتر شده
wp_send_json_success([
    'product_ids' => $city_genre_ids,
    'city_slug' => $city_slug,
    'term_id' => $term_id
]);
