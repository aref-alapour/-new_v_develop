<?php

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\Services\Panel\PanelProductAuthorizationService;

$user_id    = get_current_user_id();
$product_id = isset( $_POST['product_id'] ) ? (int) $_POST['product_id'] : 0;
$type       = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['type'] ) ) : '';
$hour       = isset( $_POST['hour'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['hour'] ) ) : '';
$percentage = isset( $_POST['percentage'] ) ? sanitize_text_field( wp_unslash( (string) $_POST['percentage'] ) ) : '';

if ( $product_id <= 0 || ! get_post( $product_id ) ) {
	wp_send_json_error( array( 'message' => 'شناسه محصول نامعتبر است.' ), 400 );
}

if ( ! in_array( $type, array( 'normals', 'holidays' ), true ) ) {
	wp_send_json_error( array( 'message' => 'نوع تنظیم نامعتبر است.' ), 400 );
}

try {
	PanelProductAuthorizationService::assertCanManageProduct( $product_id );
} catch ( GatewayAuthException $e ) {
	wp_send_json_error( array( 'message' => 'این بازی متعلق به شما نیست.' ), 403 );
}

$current = get_post_meta( $product_id, 'instant_off', true );
if ( ! is_array( $current ) ) {
	$current = array(
		'normals'  => array(
			'hour'       => -1,
			'percentage' => -1,
		),
		'holidays' => array(
			'hour'       => -1,
			'percentage' => -1,
		),
	);
}

if ( 'normals' === $type ) {
	$current['normals']['hour']       = $hour;
	$current['normals']['percentage'] = $percentage;
} else {
	$current['holidays']['hour']       = $hour;
	$current['holidays']['percentage'] = $percentage;
}

update_post_meta( $product_id, 'instant_off', $current );

wp_send_json_success(
	array(
		'product_id' => $product_id,
		'instant_off' => $current,
	)
);
