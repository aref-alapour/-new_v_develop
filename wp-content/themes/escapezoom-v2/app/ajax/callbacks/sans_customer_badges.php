<?php
/**
 * Batch HTML for user level / «مجموعه دار» badges — for sans management UI (no wp-load in web-service).
 */
if ( ! is_user_logged_in() ) {
	wp_send_json_error( [ 'message' => 'not_logged_in' ], 403 );
}

$data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : [];
if ( ! is_array( $data ) ) {
	wp_send_json_success( [ 'badges' => [] ] );
}

$user_ids = isset( $data['user_ids'] ) ? $data['user_ids'] : [];
if ( ! is_array( $user_ids ) ) {
	$user_ids = [];
}

$user_ids = array_values( array_unique( array_filter( array_map( 'intval', $user_ids ) ) ) );
$badges     = [];

foreach ( $user_ids as $uid ) {
	if ( $uid <= 0 ) {
		continue;
	}
	$key            = (string) $uid;
	$badges[ $key ] = function_exists( 'ez_user_level_badge_html' )
		? ez_user_level_badge_html( $uid )
		: '';
}

wp_send_json_success( [ 'badges' => $badges ] );
