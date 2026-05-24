<?php
/**
 * ez_rebuild_product_satisfaction_stats
 *
 * توابع: ez_rebuild_product_satisfaction_stats
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1187-1200)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_rebuild_product_satisfaction_stats($product_id) {
    if (empty($product_id) || !is_numeric($product_id))
        return false;

    $stats = ez_calculate_product_satisfaction($product_id);

    if ($stats === false)
        return false;

    update_post_meta($product_id, 'satisfaction_count', $stats['total_rated_count']);
    update_post_meta($product_id, 'satisfaction_positive_count', $stats['satisfied_count']);

    return true;
}
