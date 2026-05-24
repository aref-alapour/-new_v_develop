<?php
global $wpdb;

$user_id    = get_current_user_id();
$product_id = sanitize_text_field($_POST['product_id']);
$type       = sanitize_text_field($_POST['type']);
$hour       = sanitize_text_field($_POST['hour']);
$percentage = sanitize_text_field($_POST['percentage']);
$user_role = get_user_role( $user_id );

if ( $user_role == 'sans_manager' )
    $user_products = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'sans_manager' AND `meta_value` LIKE {$user_id} ORDER BY `meta_value` DESC" ) );
else
    $user_products = $wpdb->get_results( $wpdb->prepare( "SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'user_ebtal' AND `meta_value` LIKE {$user_id} ORDER BY `meta_value` DESC" ) );

//if ( in_array($product_id, $user_products) )
//    wp_send_json_error( 'این بازی متعلق به شما نیست.', 400 );

$current = get_post_meta( $product_id, 'instant_off', true );
if ( ! is_array( $current ) ) 
    $current = [
        'normals'   => [
            'hour'          => -1,
            'percentage'    => -1,
        ],
        'holidays'  => [
            'hour'          => -1,
            'percentage'    => -1,
        ],
    ];

if ( $type === 'normals' ) {
    $current['normals']['hour']         = $hour;
    $current['normals']['percentage']   = $percentage;
} else {
    $current['holidays']['hour']        = $hour;
    $current['holidays']['percentage']  = $percentage;
}

update_post_meta( $product_id, 'instant_off', $current );

echo json_encode($user_products);