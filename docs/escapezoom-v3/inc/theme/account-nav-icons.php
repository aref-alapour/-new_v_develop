<?php
/**
 * My Account sidebar navigation icons — wraps registry paths as currentColor SVG.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Full icon markup for navigation links (inherits color from parent).
 *
 * @param string $endpoint WooCommerce account endpoint slug (menu array key).
 */
function ez_account_nav_icon_markup( string $endpoint ): string {
	$paths = ez_account_nav_icon_inner_paths( $endpoint );

	return sprintf(
		'<span class="account-nav-icon-svg inline-flex size-5 shrink-0 items-center justify-center text-current" aria-hidden="true">'
		. '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-5 pointer-events-none">%s</svg>'
		. '</span>',
		$paths // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted theme SVG paths from registry.
	);
}
