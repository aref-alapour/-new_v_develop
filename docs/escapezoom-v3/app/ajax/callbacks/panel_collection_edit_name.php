<?php

global $wpdb;

$user = wp_get_current_user();

$title         = sanitize_text_field( $_POST['title'] );
$collection_ID = sanitize_text_field( $_POST['collection'] );

$query      = $wpdb->prepare( "SELECT * FROM collections WHERE user_id LIKE $user->ID AND ID LIKE $collection_ID" );
$collection = $wpdb->get_results( $query );

if ( ! $collection ) {
    wp_send_json_error( 'این کالکشن متعلق به شما نیست!' );
}

if ( strlen( $title ) < 3 ) {
    wp_send_json_error( 'نام کالکشن باید بیشتر از 3 حرف باشد.' );
}

$collection = $collection[0];

$update = $wpdb->update(
    'collections',
    [ 'title' => $title, ],
    [ 'ID' => $collection_ID, ],
);

if ( is_wp_error( $update ) ) {
    wp_send_json_error( 'خطایی در هنگام تغییر نام بوجود آمده لطفا دوباره امتحان کنید.' );
}

wp_send_json_success( "نام کالکشن با موفقیت تغییر کرد." );