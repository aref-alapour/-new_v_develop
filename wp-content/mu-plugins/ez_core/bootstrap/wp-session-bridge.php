<?php
/**
 * @deprecated Use ez_core_gateway_bootstrap_wordpress() in gateway-request.php (wp-load, not wp-settings after polyfills).
 */
declare(strict_types=1);

function ez_core_bridge_wp_session(): void {
	require_once __DIR__ . '/gateway-request.php';
	ez_core_gateway_bootstrap_wordpress();
}
