<?php
/**
 * ez_get_product_meta
 *
 * توابع: ez_get_product_meta
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6403-6431)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_get_product_meta($product_id) {

    $terms = get_the_terms($product_id, 'product_cat') ? : [];
    if ( count( $terms ) > 1 ) {

        foreach ( $terms as $term ) {
            if ( $term->parent == 0 )
                $product_type = $term->name;

            else {
                $city_name  = $term->name;
                $city_id    = $term->term_id;
            }
        }

    } else {
        $product_type   = get_term($terms[0]->parent)->name;
        $city_name      = $terms[0]->name;
        $city_id        = $terms[0]->term_id;
    }

    $data = new stdClass();

    $data->product_type = $product_type;
    $data->city_name    = $city_name;
    $data->city_id      = $city_id;

    return $data;
}
