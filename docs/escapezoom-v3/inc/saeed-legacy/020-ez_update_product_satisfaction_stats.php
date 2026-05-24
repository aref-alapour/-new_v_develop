<?php
/**
 * ez_update_product_satisfaction_stats
 *
 * توابع: ez_update_product_satisfaction_stats
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1095-1128)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_update_product_satisfaction_stats($product_id, $new_value) {
    if (empty($product_id) || !is_numeric($product_id))
        return false;

    $new_value = intval($new_value);

    // دریافت آمار فعلی از پست متا
    $satisfaction_count = get_post_meta($product_id, 'satisfaction_count', true);
    if ($satisfaction_count === '' || $satisfaction_count === false)
        $satisfaction_count = 0;
    else
        $satisfaction_count = intval($satisfaction_count);

    $satisfaction_positive_count = get_post_meta($product_id, 'satisfaction_positive_count', true);
    if ($satisfaction_positive_count === '' || $satisfaction_positive_count === false)
        $satisfaction_positive_count = 0;
    else
        $satisfaction_positive_count = intval($satisfaction_positive_count);

    if ($new_value === 1) {
        $satisfaction_count++;
        $satisfaction_positive_count++;

    } elseif ($new_value === 0)
        $satisfaction_count++;
    else
        return true; // برای -1 یا مقادیر نامعتبر، تغییری اعمال نکن

    // آپدیت پست متای محصول
    update_post_meta($product_id, 'satisfaction_count', $satisfaction_count);
    update_post_meta($product_id, 'satisfaction_positive_count', $satisfaction_positive_count);

    return true;
}
