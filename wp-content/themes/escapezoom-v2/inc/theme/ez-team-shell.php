<?php
/**
 * Team CRM shell helpers (boot TTL rules + sans-management page detection).
 */
defined( 'ABSPATH' ) || exit;

/**
 * Virtual roles allowed to enter `/team/` routes.
 *
 * @return list<string>
 */
function ez_team_shell_allowed_roles(): array {
	return array( 'administrator', 'supervisor', 'poshtiban', 'accounting', 'sales', 'marketing', 'team_admin' );
}

/**
 * Logged-in user has at least one team-shell role.
 */
function ez_team_shell_user_has_access(): bool {
	if ( ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() ) {
		return false;
	}

	$user          = wp_get_current_user();
	$allowed_roles = ez_team_shell_allowed_roles();

	return ! empty( array_intersect( $allowed_roles, (array) $user->roles ) );
}

/**
 * Roles allowed to use team sans-management gateway actions (menu item roles).
 *
 * @return list<string>
 */
function ez_team_sans_management_roles(): array {
	$default = array( 'administrator', 'supervisor', 'poshtiban', 'team_admin' );

	return array_values(
		array_unique(
			array_filter(
				array_map( 'strval', (array) apply_filters( 'ez_team_sans_management_roles', $default ) )
			)
		)
	);
}

/**
 * Team pages that need signed /ajax boot (sans grid + game search on comments).
 */
function ez_team_is_booking_tools_page(): bool {
	if ( ! function_exists( 'get_query_var' ) || ! ez_team_shell_user_has_access() ) {
		return false;
	}

	$slug = (string) get_query_var( 'team_page' );

	return in_array( $slug, array( 'sans_management', 'comments' ), true );
}

/**
 * Current request is CRM team sans-management page (rewrite + menu access).
 */
function ez_team_is_sans_management_page(): bool {
	if ( ! function_exists( 'get_query_var' ) ) {
		return false;
	}

	$slug = (string) get_query_var( 'team_page' );
	if ( 'sans_management' !== $slug ) {
		return false;
	}

	if ( ! ez_team_shell_user_has_access() ) {
		return false;
	}

	if ( ! function_exists( 'get_accessible_team_menu_items' ) ) {
		return false;
	}

	$menu = get_accessible_team_menu_items();

	return isset( $menu['sans_management'] );
}

/**
 * Expose team sans-management screen for core boot client_kind filter.
 */
add_filter(
	'ez_ajax_team_sans_management_screen',
	static function ( bool $is_screen ): bool {
		if ( $is_screen ) {
			return true;
		}

		return ez_team_is_sans_management_page();
	}
);
