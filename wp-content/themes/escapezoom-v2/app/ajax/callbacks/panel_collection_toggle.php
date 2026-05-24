<?php

global $wpdb;

$user = wp_get_current_user();

$ID     = sanitize_text_field( $_POST['collection'] );
$active = sanitize_text_field( $_POST['active'] );
$active = $active == "unchecked" ? 1 : 0;

if ( empty( $ID ) ) {
	wp_send_json_error( 'کالکشن نامعتبر است' );
}

$collection = $wpdb->get_results( "SELECT * FROM collections WHERE user_id LIKE $user->ID AND ID LIKE $ID" )[0];
if ( empty( $collection ) ) {
	wp_send_json_error( 'این کالکشن متعلق به شما نیست!' );
}

if ( empty( unserialize( $collection->items ) ) ) {
	wp_send_json_error( 'این کالکشن خالی است و نمیتواند فعال شود.' );
}

$query = $wpdb->update(
	'collections',
	[ 'active' => $active ],
	[ 'ID' => $ID ],
);

if ( is_wp_error( $query ) ) {
	wp_send_json_error( 'خطایی پیش آمده لطفا دوباره امتحان کنید.' );
}

wp_send_json_success( 'وضعیت کالکشن با موفقیت تغییر کرد.' );