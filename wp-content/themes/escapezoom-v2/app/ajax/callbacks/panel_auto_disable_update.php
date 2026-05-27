<?php

use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\Booking\Services\Panel\PanelProductAuthorizationService;

$user_id = get_current_user_id();

$product_id   = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$auto_disable = isset($_POST['auto_disable']) ? intval($_POST['auto_disable']) : 0;

if ($product_id <= 0 || !get_post($product_id))
    wp_send_json_error(['message' => 'شناسه محصول نامعتبر است.'], 400);

// only allow predefined minutes
$allowed = [15, 30, 60, 120, 180];
if (!in_array($auto_disable, $allowed, true))
    wp_send_json_error(['message' => 'مقدار زمان نامعتبر است.'], 400);

try {
	PanelProductAuthorizationService::assertCanManageProduct( $product_id );
} catch ( GatewayAuthException $e ) {
	wp_send_json_error( array( 'message' => 'دسترسی به این محصول ندارید.' ), 403 );
}

update_post_meta($product_id, 'auto_disable', $auto_disable);

wp_send_json_success([
    'product_id'   => $product_id,
    'auto_disable' => $auto_disable,
]);

