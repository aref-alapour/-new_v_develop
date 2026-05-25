<?php
/**
 * Normalized request path for EZ AJAX TTL rules.
 */
defined( 'ABSPATH' ) || exit;

function ez_theme_normalized_request_path(): string {
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : '/';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	if ( '' === $path ) {
		$path = '/';
	}
	$home = (string) wp_parse_url( home_url( '/' ), PHP_URL_PATH );
	$home = rtrim( $home, '/' );
	if ( '' !== $home && str_starts_with( $path, $home ) ) {
		$path = substr( $path, strlen( $home ) );
	}
	$path = trim( $path, '/' );

	return '' === $path ? '/' : '/' . $path;
}
