<?php
/**
 * Per-context TTL rules for EZ AJAX gateway boot (`expires_at` / sub-secret lifetime).
 *
 * Resolution order:
 *   1. `ez_ajax_sub_secret_ttl_base` filter → storefront default (usually EZ_AJAX_SUB_SECRET_TTL).
 *   2. Walk `ez_ajax_sub_secret_ttl_rules` (filtered); first matching rule wins.
 *   3. Clamp to [60, ez_ajax_sub_secret_ttl_max] (filter + optional EZ_AJAX_SUB_SECRET_TTL_MAX).
 *
 * Extend by editing {@see ez_ajax_default_sub_secret_ttl_rules()} or filtering `ez_ajax_sub_secret_ttl_rules`.
 *
 * Rule `team_shell`: same gate as CRM routing — logged-in user with a role from
 * {@see ez_team_shell_allowed_roles()} plus URL under `/team/` or non-empty `team_page` query var
 * only when that slug exists in {@see get_accessible_team_menu_items()}.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/ez-request-path.php';

/**
 * Request path relative to the WordPress site path (no leading/trailing slashes).
 */
function ez_ajax_boot_normalized_request_path(): string {
	return ez_theme_normalized_request_path();
}

/**
 * Default rules (first match wins). Add `uri_prefix` or `page_slug` entries as needed.
 *
 * @return list<array{type:string, ttl:int, prefix?:string, slug?:string}>
 */
function ez_ajax_default_sub_secret_ttl_rules(): array {
	return [
		[
			'type' => 'team_shell',
			'ttl'  => 4 * HOUR_IN_SECONDS,
		],
		[
			'type'   => 'admin_hook',
			'hook'   => 'escapezoom_page_ez-product-penalties',
			'ttl'    => 4 * HOUR_IN_SECONDS,
		],
	];
}

/**
 * @param array<string, mixed> $rule
 */
function ez_ajax_sub_secret_ttl_rule_matches( array $rule ): bool {
	$type = isset( $rule['type'] ) ? (string) $rule['type'] : '';
	if ( '' === $type || ! isset( $rule['ttl'] ) ) {
		return false;
	}

	switch ( $type ) {
		case 'team_shell':
			if ( ! function_exists( 'ez_team_shell_user_has_access' ) || ! ez_team_shell_user_has_access() ) {
				return false;
			}
			$slug = (string) get_query_var( 'team_page' );
			if ( '' !== $slug ) {
				if ( ! function_exists( 'get_accessible_team_menu_items' ) ) {
					return false;
				}
				$menu = get_accessible_team_menu_items();
				return isset( $menu[ $slug ] );
			}
			$p = ez_ajax_boot_normalized_request_path();
			return 'team' === $p || str_starts_with( $p . '/', 'team/' );

		case 'uri_prefix':
			$prefix = isset( $rule['prefix'] ) ? trim( (string) $rule['prefix'], '/' ) : '';
			if ( '' === $prefix ) {
				return false;
			}
			$p = ez_ajax_boot_normalized_request_path();
			return $prefix === $p || str_starts_with( $p . '/', $prefix . '/' );

		case 'page_slug':
			return isset( $rule['slug'] ) && is_string( $rule['slug'] )
				&& function_exists( 'is_page' )
				&& is_page( $rule['slug'] );

		case 'admin_hook':
			if ( ! is_admin() || ! function_exists( 'get_current_screen' ) ) {
				return false;
			}
			$hook = isset( $rule['hook'] ) ? (string) $rule['hook'] : '';
			if ( '' === $hook ) {
				return false;
			}
			$screen = get_current_screen();

			return $screen instanceof WP_Screen && $hook === $screen->id;

		default:
			return false;
	}
}
