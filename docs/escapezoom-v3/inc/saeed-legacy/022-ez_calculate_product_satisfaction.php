<?php
/**
 * ez_calculate_product_satisfaction
 *
 * توابع: ez_calculate_product_satisfaction
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 1156-1186)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_calculate_product_satisfaction($product_id) {
    $medoo = medoo();

    $post_ids = $medoo->select('wp_postmeta', 'post_id', [
        'meta_key'      => 'code_otagh',
        'meta_value'    => $product_id
    ]);

    if (empty($post_ids))
        return;

    $is_satisfied_values = $medoo->select('wp_postmeta', 'meta_value', [
        'meta_key'      => 'is_satisfied',
        'post_id'       => $post_ids,
        'meta_value[!]' => -1
    ]);

    $total      = count($is_satisfied_values);
    $positive   = 0;

    foreach ($is_satisfied_values as $val)
        if ((int)$val === 1)
            $positive++;

//    saeed_store([$post_ids, $total, $positive]);

    return [
        'satisfied_count'   => $positive,
        'total_rated_count' => $total,
    ];
}
