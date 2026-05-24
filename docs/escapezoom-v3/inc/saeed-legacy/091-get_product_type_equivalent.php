<?php
/**
 * get_product_type_equivalent
 *
 * توابع: get_product_type_equivalent
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6461-6477)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function get_product_type_equivalent($product_type) {
    $product_types = [
        'cafegame'      => 'کافه بازی',
        'escaperoom'    => 'اتاق فرار',
        'cinema'        => 'سینما ترس',
        'lasertag'      => 'لیزرتگ',
        'rageroom'      => 'اتاق خشم',
        'bubblefootball'=> 'فوتبال حبابی',
        'paintball'     => 'پینت بال',
        'haunted_house' => 'هانتد هاوس',
    ];

    if (array_key_exists($product_type, $product_types)) // if parameter is English
        return $product_types[$product_type];

    return array_search($product_type, $product_types);
}
