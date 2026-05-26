<?php
/**
 * Boot data for the AJAX gateway, emitted as an inline `<script>` immediately before `wp_head()`.
 *
 * Why inline (rule 00-modularity says no inline JS in PHP):
 *   The sub-secret must be available BEFORE any other script runs (so the first HTMX call can sign).
 *   It is per-request and per-page-load, so it cannot live in a static asset.
 *   Rendering it via a localized script enqueued by wp_enqueue_script() would also push it to be
 *   inline anyway — using `wp_print_inline_script_tag()` keeps it CSP-friendly + WP-managed.
 *
 * TTL resolution:
 *   - Base: filter `ez_ajax_sub_secret_ttl_base` (default EZ_AJAX_SUB_SECRET_TTL or 900).
 *   - First matching rule from filter `ez_ajax_sub_secret_ttl_rules` (see ez-ajax-sub-secret-rules.php).
 *   - Clamp: filter `ez_ajax_sub_secret_ttl_max` (default EZ_AJAX_SUB_SECRET_TTL_MAX or 86400), minimum 60.
 */

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/ez-ajax-sub-secret-rules.php';

/**
 * Apply rule walk + global TTL ceiling/floor.
 */
function ez_ajax_resolve_sub_secret_ttl( int $base_ttl ): int {
	$rules = apply_filters( 'ez_ajax_sub_secret_ttl_rules', ez_ajax_default_sub_secret_ttl_rules() );
	$resolved = $base_ttl;
	foreach ( $rules as $rule ) {
		if ( ! is_array( $rule ) ) {
			continue;
		}
		if ( ez_ajax_sub_secret_ttl_rule_matches( $rule ) ) {
			$resolved = (int) $rule['ttl'];
			break;
		}
	}

	$max_default = defined( 'EZ_AJAX_SUB_SECRET_TTL_MAX' ) ? (int) EZ_AJAX_SUB_SECRET_TTL_MAX : 86400;
	$max         = (int) apply_filters( 'ez_ajax_sub_secret_ttl_max', $max_default );
	if ( $max < 60 ) {
		$max = 86400;
	}

	return max( 60, min( $resolved, $max ) );
}

/**
 * Build the boot data array for the current request.
 *
 * @return array{kid:string,client_id:string,client_kind:string,sub_secret:string,expires_at:int,ajax_url:string,timestamp:int}
 */
function ez_ajax_boot_data(): array {
	$kid         = 'v1';
	$client_kind = 'web-anon';
	$client_id   = ez_ajax_boot_client_id();

	$base_ttl = defined( 'EZ_AJAX_SUB_SECRET_TTL' ) ? (int) EZ_AJAX_SUB_SECRET_TTL : 900;
	$base_ttl = (int) apply_filters( 'ez_ajax_sub_secret_ttl_base', $base_ttl );
	$ttl      = ez_ajax_resolve_sub_secret_ttl( $base_ttl );
	$expires_at = time() + $ttl;

	$sub_secret = '';
	if ( defined( 'EZ_AJAX_SHARED_SECRET' ) && class_exists( '\\EZ\\Ajax\\Auth\\SubKey' ) ) {
		$sub_secret = \EZ\Ajax\Auth\SubKey::deriveBase64Url(
			(string) EZ_AJAX_SHARED_SECRET,
			$kid,
			$client_id,
			$expires_at
		);
	}

	$boot = [
		'kid'            => $kid,
		'client_id'      => $client_id,
		'client_kind'    => $client_kind,
		'sub_secret'     => $sub_secret,
		'expires_at'     => $expires_at,
		'ajax_url'       => home_url( '/ajax' ),
		'timestamp'      => time(),
		'encrypt_writes' => defined( 'EZ_GATEWAY_ENCRYPT_WRITES' ) && EZ_GATEWAY_ENCRYPT_WRITES,
		'encrypt_reads'  => defined( 'EZ_GATEWAY_ENCRYPT_READS' ) && EZ_GATEWAY_ENCRYPT_READS,
	];

	return apply_filters( 'ez_ajax_boot_data', $boot );
}

/**
 * UUIDv4 minted per page-load. We use SubKey::uuidV4() when available; fallback to wp_generate_uuid4().
 */
function ez_ajax_boot_client_id(): string {
	if ( class_exists( '\\EZ\\Ajax\\Auth\\SubKey' ) ) {
		return \EZ\Ajax\Auth\SubKey::uuidV4();
	}
	if ( function_exists( 'wp_generate_uuid4' ) ) {
		return (string) wp_generate_uuid4();
	}
	return bin2hex( random_bytes( 16 ) );
}

/**
 * Print `window.__EZ_BOOT__ = {...};` as an inline `<script>`.
 *
 * Called directly from header.php or team CRM layout, BEFORE wp_head(), so the boot data is available before
 * any other JS in <head> runs (HTMX hook depends on it).
 *
 * No-op on core admin / login screens — skip wp-admin; storefront + `/team/` shell include boot here.
 */
function ez_ajax_boot_print_inline(): void {
	if ( is_admin() ) {
		return;
	}
	ez_ajax_boot_print_script( ez_ajax_boot_data() );
}

/**
 * Boot script for wp-admin screens that use the EZ AJAX gateway (e.g. product penalties).
 */
function ez_ajax_boot_print_for_admin_screen( string $hook_suffix ): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$allowed = apply_filters(
		'ez_ajax_admin_boot_hook_suffixes',
		[
			'escapezoom_page_ez-product-penalties',
		]
	);

	if ( ! in_array( $hook_suffix, $allowed, true ) ) {
		return;
	}

	$boot = ez_ajax_boot_data();
	$boot['client_kind'] = 'web-user';
	ez_ajax_boot_print_script( $boot );
}

/**
 * @param array<string, mixed> $boot
 */
function ez_ajax_boot_print_script( array $boot ): void {
	$json = wp_json_encode( $boot, JSON_UNESCAPED_SLASHES );
	if ( ! is_string( $json ) ) {
		return;
	}
	echo '<script id="ez-ajax-boot">window.__EZ_BOOT__=' . $json . ';</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}
