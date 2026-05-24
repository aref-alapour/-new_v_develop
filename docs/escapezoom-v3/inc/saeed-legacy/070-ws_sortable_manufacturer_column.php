<?php
/**
 * ws_sortable_manufacturer_column
 *
 * توابع: ws_sortable_manufacturer_column هوک‌ها: manage_edit-ticketing_sortable_columns
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 5913-5920)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'manage_edit-ticketing_sortable_columns', 'ws_sortable_manufacturer_column' );
function ws_sortable_manufacturer_column( $columns )    {

    $columns['last_message']    = 'last_message';
    $columns['type']            = 'type';

    return $columns;
}
