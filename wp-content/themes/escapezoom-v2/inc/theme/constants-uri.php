<?php
/**
 * Theme URI helpers (dist bundle paths).
 */
defined( 'ABSPATH' ) || exit;

/**
 * @param string $relative Path under theme dist/ (e.g. front.js).
 */
function ez_theme_dist_uri( string $relative ): string {
	return Theme_URL . 'dist/' . ltrim( $relative, '/' );
}

/**
 * Whether the Vite front bundle (dist/front.js + front.css) is active.
 */
function ez_theme_use_vite_front(): bool {
	if ( defined( 'EZ_USE_VITE_FRONT' ) ) {
		return (bool) EZ_USE_VITE_FRONT;
	}

	return (bool) apply_filters(
		'ez_use_vite_front',
		is_readable( Theme_PATH . 'dist/front.js' ) && is_readable( Theme_PATH . 'dist/front.css' )
	);
}
