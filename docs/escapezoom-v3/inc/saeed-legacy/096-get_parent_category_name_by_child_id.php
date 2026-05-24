<?php
/**
 * get_parent_category_name_by_child_id
 *
 * توابع: get_parent_category_name_by_child_id
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6558-6569)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_parent_category_name_by_child_id($child_term_id) {
    $child_term = get_term($child_term_id, 'product_cat');

    if ($child_term && !is_wp_error($child_term) && $child_term->parent) {
        $parent_term = get_term($child_term->parent, 'product_cat');

        if ($parent_term && !is_wp_error($parent_term))
            return  $parent_term->name;
    }

    return '';
}
