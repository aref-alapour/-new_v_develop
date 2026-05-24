<?php
/**
 * Single source of truth for “path after site base” (no leading/trailing slashes).
 * Used by CRM routing and EZ AJAX boot TTL path rules — keep logic identical.
 */

defined( 'ABSPATH' ) || exit;

/**
 * Normalized request path relative to `home_url()` path prefix.
 *
 * @return string e.g. `team/orders` or empty for site root request path
 */
function ez_theme_normalized_request_path(): string {
	$uri_path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( (string) $_SERVER['REQUEST_URI'] ) : '';
	$parsed   = parse_url( $uri_path, PHP_URL_PATH );
	if ( ! is_string( $parsed ) ) {
		return '';
	}
	$base_path = parse_url( home_url(), PHP_URL_PATH );
	if ( ! is_string( $base_path ) ) {
		$base_path = '';
	}
	if ( $base_path && strpos( $parsed, $base_path ) === 0 ) {
		$parsed = substr( $parsed, strlen( $base_path ) );
	}
	return trim( $parsed, '/' );
}
