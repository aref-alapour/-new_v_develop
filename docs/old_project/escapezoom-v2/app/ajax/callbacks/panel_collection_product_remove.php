<?php
global $wpdb;

$user = wp_get_current_user();

$ID   = sanitize_title( $_POST['collection'] );
$item = (int) sanitize_text_field( $_POST['item'] );

if ( empty( $ID ) ) {
	wp_send_json_error( 'کالکشن نامعتبر است' );
}

$collection = $wpdb->get_results( "SELECT * FROM collections WHERE user_id LIKE $user->ID AND ID LIKE $ID" )[0];

if ( empty( $collection ) ) {
	wp_send_json_error( 'این کالکشن متعلق به شما نیست!' );
}

$items = unserialize( $collection->items );

if ( ! in_array( $item, $items ) ) {
	wp_send_json_error( 'این آیتم در کالکشن شما وجود ندارد.' );
}

if ( ( $key = array_search( $item, $items ) ) !== false ) {
	unset( $items[ $key ] );
}

$query = $wpdb->update( 'collections',
	[ 'items' => serialize( $items ) ],
	[ 'ID' => $collection->ID ],
);

if ( is_wp_error( $query ) ) {
	wp_send_json_error( 'خطایی پیش آمده لطفا دوباره امتحان کنید.' );
}

wp_send_json_success( "با موفقیت از کالکشن شما حذف شد." );