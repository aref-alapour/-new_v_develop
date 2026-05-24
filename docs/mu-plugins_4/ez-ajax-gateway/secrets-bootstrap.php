<?php
/**
 * Loads secrets + DB credentials + table prefix from wp-config.php (or a dedicated secrets file)
 * WITHOUT booting WordPress.
 *
 * Resolution order:
 *  1. wp-content/mu-plugins/ez-ajax-secrets.php (if present) — runtime fast path, kept out of VCS.
 *  2. wp-config.php parsed with a strict whitelist regex.
 *
 * Whitelist (constants only — never reflect arbitrary defines):
 *  - EZ_AJAX_*           — our gateway secrets / TTLs.
 *  - EZ_BRANDS_USE_GATEWAY — phase 1 routing flag.
 *  - WP_DEBUG            — to gate dev/prod behavior.
 *  - DB_NAME / DB_USER / DB_PASSWORD / DB_HOST / DB_CHARSET / DB_COLLATE — needed by the lightweight DB layer.
 *
 * Plus `$table_prefix` (variable, parsed separately) → exposed as constant EZ_AJAX_TABLE_PREFIX.
 */

declare( strict_types = 1 );

if ( defined( 'EZ_AJAX_SECRETS_BOOTSTRAP_LOADED' ) ) {
	return;
}

define( 'EZ_AJAX_SECRETS_BOOTSTRAP_LOADED', true );

/**
 * Try the dedicated secrets file first (fast: no regex), then fall back to wp-config.php parsing.
 *
 * @throws \RuntimeException When neither source is readable.
 */
function ez_ajax_gateway_secrets_bootstrap( string $wp_config_path ): void {
	$dedicated = dirname( $wp_config_path ) . '/wp-content/mu-plugins/ez-ajax-secrets.php';
	if ( is_readable( $dedicated ) ) {
		// Dedicated file is plain PHP defines — fastest path, no parser cost.
		require_once $dedicated;
	}

	// Even if dedicated file ran, also parse wp-config.php for anything it did not provide.
	// This keeps dev (no dedicated file) working out-of-the-box.
	if ( is_readable( $wp_config_path ) ) {
		ez_ajax_gateway_parse_wp_config_constants( $wp_config_path );
	} elseif ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) ) {
		throw new \RuntimeException( 'EZ AJAX: wp-config.php not readable and no secrets file present.' );
	}

	ez_ajax_gateway_validate_secrets();
}

/**
 * Parse a small whitelist of constants + the table prefix variable from a wp-config-like PHP file.
 *
 * Uses non-greedy regex (no full PHP tokenizer) — kept intentionally simple.
 */
function ez_ajax_gateway_parse_wp_config_constants( string $wp_config_path ): void {
	$contents = (string) @file_get_contents( $wp_config_path );
	if ( '' === $contents ) {
		return;
	}

	$whitelist_pattern = '(EZ_AJAX_[A-Z0-9_]+|EZ_BRANDS_USE_GATEWAY|WP_DEBUG|DB_NAME|DB_USER|DB_PASSWORD|DB_HOST|DB_CHARSET|DB_COLLATE)';

	if ( preg_match_all(
		'/define\s*\(\s*[\'"]' . $whitelist_pattern . '[\'"]\s*,\s*([^)]+?)\s*\)\s*;/m',
		$contents,
		$matches,
		PREG_SET_ORDER
	) ) {
		foreach ( $matches as $m ) {
			$name = $m[1];
			if ( defined( $name ) ) {
				continue;
			}
			$value = ez_ajax_gateway_coerce_define_value( trim( $m[2] ) );
			define( $name, $value );
		}
	}

	if ( ! defined( 'EZ_AJAX_TABLE_PREFIX' ) ) {
		if ( preg_match( '/\$table_prefix\s*=\s*[\'"]([^\'"]+)[\'"]\s*;/', $contents, $m ) ) {
			define( 'EZ_AJAX_TABLE_PREFIX', $m[1] );
		} else {
			define( 'EZ_AJAX_TABLE_PREFIX', 'wp_' );
		}
	}
}

/**
 * Coerce a raw define() value literal to a PHP scalar.
 *
 *  - 'foo'       → string foo
 *  - "foo"       → string foo
 *  - true/false  → bool
 *  - 123         → int
 *  - other       → string (defensive)
 */
function ez_ajax_gateway_coerce_define_value( string $raw ) {
	if ( preg_match( '/^[\'"](.*)[\'"]$/s', $raw, $vm ) ) {
		return $vm[1];
	}
	$lc = strtolower( $raw );
	if ( 'true' === $lc ) {
		return true;
	}
	if ( 'false' === $lc ) {
		return false;
	}
	if ( 'null' === $lc ) {
		return null;
	}
	if ( preg_match( '/^-?\d+$/', $raw ) ) {
		return (int) $raw;
	}
	if ( preg_match( '/^-?\d+\.\d+$/', $raw ) ) {
		return (float) $raw;
	}
	return $raw;
}

/**
 * Enforce policy on loaded secrets.
 *
 *  - Missing EZ_AJAX_SHARED_SECRET: hard 500 always (cannot verify anything).
 *  - Placeholder EZ_AJAX_SHARED_SECRET: warn in dev, refuse in prod.
 */
function ez_ajax_gateway_validate_secrets(): void {
	if ( ! defined( 'EZ_AJAX_NONCE_TTL' ) ) {
		define( 'EZ_AJAX_NONCE_TTL', 60 );
	}
	if ( ! defined( 'EZ_AJAX_TIMESTAMP_SKEW' ) ) {
		define( 'EZ_AJAX_TIMESTAMP_SKEW', 30 );
	}
	if ( ! defined( 'EZ_AJAX_SUB_SECRET_TTL' ) ) {
		define( 'EZ_AJAX_SUB_SECRET_TTL', 900 );
	}
	if ( ! defined( 'EZ_AJAX_SUB_SECRET_TTL_MAX' ) ) {
		define( 'EZ_AJAX_SUB_SECRET_TTL_MAX', 86400 );
	}
	if ( ! defined( 'EZ_BRANDS_USE_GATEWAY' ) ) {
		define( 'EZ_BRANDS_USE_GATEWAY', false );
	}

	$is_dev = defined( 'WP_DEBUG' ) && WP_DEBUG;

	if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || ! is_string( EZ_AJAX_SHARED_SECRET ) || strlen( EZ_AJAX_SHARED_SECRET ) < 32 ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[EZ AJAX] FATAL: EZ_AJAX_SHARED_SECRET missing or too short (need 32+ chars).' );
		header( 'Content-Type: application/json; charset=utf-8' );
		http_response_code( 503 );
		echo '{"ok":false,"error":{"code":"INTERNAL"}}';
		exit;
	}

	if ( str_starts_with( (string) EZ_AJAX_SHARED_SECRET, 'CHANGE-ME' ) ) {
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[EZ AJAX] WARNING: EZ_AJAX_SHARED_SECRET is still the placeholder. Generate a real value (e.g. `openssl rand -base64 48`).' );
		if ( ! $is_dev ) {
			header( 'Content-Type: application/json; charset=utf-8' );
			http_response_code( 503 );
			echo '{"ok":false,"error":{"code":"INTERNAL"}}';
			exit;
		}
	}
}
