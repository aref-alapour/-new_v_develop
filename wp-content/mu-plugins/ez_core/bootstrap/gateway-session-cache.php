<?php
/**
 * Short-lived gateway session cache (cookie-bound) to skip wp-load on repeat team /ajax reads.
 */
declare(strict_types=1);

function ez_core_gateway_session_cookie_fingerprint(): string {
	foreach ( $_COOKIE as $name => $value ) {
		if ( ! is_string( $name ) || ! is_string( $value ) ) {
			continue;
		}
		if ( str_starts_with( $name, 'wordpress_logged_in_' ) ) {
			return hash( 'sha256', $name . "\0" . $value );
		}
	}

	if ( isset( $_SERVER['HTTP_COOKIE'] ) && is_string( $_SERVER['HTTP_COOKIE'] ) ) {
		if ( preg_match( '/(wordpress_logged_in_[^=;]+)=([^;]*)/', $_SERVER['HTTP_COOKIE'], $matches ) ) {
			return hash( 'sha256', $matches[1] . "\0" . rawurldecode( $matches[2] ) );
		}
	}

	return '';
}

function ez_core_gateway_session_cache_dir(): string {
	if ( defined( 'EZ_CORE_PATH' ) ) {
		$dir = dirname( EZ_CORE_PATH, 2 ) . '/cache/ez-gateway-sess';
	} else {
		$dir = sys_get_temp_dir() . '/ez-gateway-sess';
	}
	if ( ! is_dir( $dir ) ) {
		@mkdir( $dir, 0755, true );
	}

	return $dir;
}

function ez_core_gateway_session_cache_path( string $fingerprint ): string {
	return ez_core_gateway_session_cache_dir() . '/sess_' . substr( $fingerprint, 0, 40 ) . '.json';
}

/**
 * Restore team/user gateway auth from a recent wp-load (same WP login cookie).
 */
function ez_core_gateway_try_restore_session( string $clientKind ): bool {
	if ( defined( 'EZ_GATEWAY_SESSION_CACHED' ) && EZ_GATEWAY_SESSION_CACHED ) {
		return true;
	}

	$fingerprint = ez_core_gateway_session_cookie_fingerprint();
	if ( '' === $fingerprint ) {
		return false;
	}

	$path = ez_core_gateway_session_cache_path( $fingerprint );
	if ( ! is_readable( $path ) ) {
		return false;
	}

	$raw  = file_get_contents( $path );
	$data = is_string( $raw ) ? json_decode( $raw, true ) : null;
	if ( ! is_array( $data ) ) {
		return false;
	}

	$expires = isset( $data['exp'] ) ? (int) $data['exp'] : 0;
	if ( $expires <= time() ) {
		@unlink( $path );

		return false;
	}

	if ( (string) ( $data['client_kind'] ?? '' ) !== $clientKind ) {
		return false;
	}

	$userId = isset( $data['user_id'] ) ? (int) $data['user_id'] : 0;
	if ( $userId <= 0 ) {
		return false;
	}

	if ( 'web-team' === $clientKind && empty( $data['team_ok'] ) ) {
		return false;
	}

	$GLOBALS['ez_gateway_cached_user_id'] = $userId;
	$GLOBALS['ez_gateway_cached_team_ok']   = ! empty( $data['team_ok'] );

	if ( ! defined( 'EZ_GATEWAY_SESSION_CACHED' ) ) {
		define( 'EZ_GATEWAY_SESSION_CACHED', true );
	}

	return true;
}

function ez_core_gateway_remember_session( string $clientKind ): void {
	if ( ! function_exists( 'get_current_user_id' ) ) {
		return;
	}

	$userId = (int) get_current_user_id();
	if ( $userId <= 0 ) {
		return;
	}

	$fingerprint = ez_core_gateway_session_cookie_fingerprint();
	if ( '' === $fingerprint ) {
		return;
	}

	$teamOk = false;
	if ( 'web-team' === $clientKind ) {
		if ( function_exists( 'current_user_can' ) && current_user_can( 'manage_options' ) ) {
			$teamOk = true;
		} elseif ( class_exists( \EscapeZoom\Core\Modules\Booking\SansManagementAuthorizationService::class ) ) {
			$teamOk = \EscapeZoom\Core\Modules\Booking\SansManagementAuthorizationService::userHasTeamSansRole( $userId );
		}
	}

	$payload = json_encode(
		array(
			'user_id'     => $userId,
			'team_ok'     => $teamOk,
			'client_kind' => $clientKind,
			'exp'         => time() + 300,
		),
		JSON_UNESCAPED_UNICODE
	);

	if ( ! is_string( $payload ) ) {
		return;
	}

	$path = ez_core_gateway_session_cache_path( $fingerprint );
	@file_put_contents( $path, $payload, LOCK_EX );
}

function ez_core_gateway_cached_user_id(): int {
	return isset( $GLOBALS['ez_gateway_cached_user_id'] ) ? (int) $GLOBALS['ez_gateway_cached_user_id'] : 0;
}

function ez_core_gateway_cached_team_ok(): bool {
	return ! empty( $GLOBALS['ez_gateway_cached_team_ok'] );
}
