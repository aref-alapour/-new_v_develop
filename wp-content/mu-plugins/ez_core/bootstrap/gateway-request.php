<?php
/**
 * Early /ajax request metadata (before polyfills or wp-load).
 */
declare(strict_types=1);

use EscapeZoom\Core\Modules\AjaxGateway\Policy\ActionClassification;

/**
 * @return array{action: string, client_kind: string}
 */
function ez_core_gateway_request_meta(): array {
	$action = '';
	if ( isset( $_SERVER['HTTP_X_EZ_ACTION'] ) && is_string( $_SERVER['HTTP_X_EZ_ACTION'] ) ) {
		$action = trim( $_SERVER['HTTP_X_EZ_ACTION'] );
	} elseif ( isset( $_GET['action'] ) && is_string( $_GET['action'] ) ) {
		$action = trim( $_GET['action'] );
	}

	$clientKind = 'web-anon';
	if ( isset( $_SERVER['HTTP_X_EZ_CLIENT_KIND'] ) && is_string( $_SERVER['HTTP_X_EZ_CLIENT_KIND'] ) ) {
		$clientKind = trim( $_SERVER['HTTP_X_EZ_CLIENT_KIND'] );
	}
	if ( '' === $clientKind ) {
		$clientKind = 'web-anon';
	}

	return array(
		'action'      => $action,
		'client_kind' => $clientKind,
	);
}

/**
 * Gateway /ajax actions that only need Capsule (no full EZ module boot).
 *
 * @return list<string>
 */
function ez_core_gateway_data_layer_only_actions(): array {
	return array(
		'booking.game_search',
		'booking.sans_management_data',
		'booking.check_playing',
	);
}

function ez_core_gateway_uses_data_layer_only( string $action ): bool {
	return in_array( $action, ez_core_gateway_data_layer_only_actions(), true );
}

function ez_core_gateway_needs_session( string $action, string $clientKind ): bool {
	if ( ! in_array( $clientKind, array( 'web-user', 'web-team' ), true ) ) {
		return false;
	}

	if ( 'booking.game_search' === $action ) {
		return false;
	}

	if ( ActionClassification::requiresSansPanelAuth( $action ) ) {
		return true;
	}

	if ( ActionClassification::isWrite( $action ) ) {
		return true;
	}

	return false;
}

function ez_core_gateway_needs_wp_bootstrap( string $action, string $clientKind ): bool {
	if ( '' === $action ) {
		return false;
	}

	$sessionCacheFile = defined( 'EZ_CORE_PATH' ) ? EZ_CORE_PATH . '/bootstrap/gateway-session-cache.php' : '';
	if ( is_string( $sessionCacheFile ) && '' !== $sessionCacheFile && is_readable( $sessionCacheFile ) ) {
		require_once $sessionCacheFile;
	}

	if ( ! ez_core_gateway_needs_session( $action, $clientKind ) ) {
		return false;
	}

	return ! ez_core_gateway_try_restore_session( $clientKind );
}

/**
 * Full WordPress bootstrap for cookie auth (team/panel). Avoid after polyfills are loaded.
 */
function ez_core_gateway_bootstrap_wordpress(): void {
	if ( function_exists( 'is_user_logged_in' ) ) {
		return;
	}

	if ( ! defined( 'ABSPATH' ) ) {
		if ( ! defined( 'EZ_CORE_PATH' ) ) {
			return;
		}
		define( 'ABSPATH', dirname( EZ_CORE_PATH, 3 ) . '/' );
	}

	if ( ! defined( 'WP_USE_THEMES' ) ) {
		define( 'WP_USE_THEMES', false );
	}

	if ( ! defined( 'EZ_AJAX_GATEWAY_WP_BOOTSTRAP' ) ) {
		define( 'EZ_AJAX_GATEWAY_WP_BOOTSTRAP', true );
	}

	$wpLoad = ABSPATH . 'wp-load.php';
	if ( is_readable( $wpLoad ) ) {
		require_once $wpLoad;
	}
}
