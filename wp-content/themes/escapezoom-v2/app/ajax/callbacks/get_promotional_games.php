<?php
// دریافت بازی‌های تبلیغاتی بر اساس citySlug (بدون استفاده از web-service)
if (!defined('ABSPATH')) {
    exit;
}

$city_slug = isset($_POST['citySlug']) ? sanitize_text_field($_POST['citySlug']) : '';
if (empty($city_slug)) {
    wp_send_json_error(['message' => 'citySlug ارسال نشده است']);
}

// محصولات تبلیغاتی ذخیره شده در تنظیمات ادمین
$city_data   = get_option("promotional_products_{$city_slug}", []);
$product_ids = isset($city_data['products']) ? array_map('intval', (array)$city_data['products']) : [];

if (empty($product_ids)) {
    wp_send_json_error(['message' => 'هیچ محصول تبلیغاتی برای این شهر یافت نشد']);
}

wp_send_json_success([
    'product_ids' => $product_ids,
]);
