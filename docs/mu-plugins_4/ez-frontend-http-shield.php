<?php
/**
 * Plugin Name: EZ Frontend HTTP Shield
 * Description: Stops outbound plugin update/metadata HTTP requests on public (non-dashboard) URLs so storefront + HTMX are not blocked for seconds by timeouts.
 *
 * Typical culprit: Commercial plugins calling license/update servers synchronously during init.
 * Customize hosts with filter {@see ez_frontend_http_blocked_hosts} or disable with EZ_FRONTEND_HTTP_SHIELD_DISABLED.
 *
 * @package EscapeZoom
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'EZ_FRONTEND_HTTP_SHIELD_DISABLED' ) && EZ_FRONTEND_HTTP_SHIELD_DISABLED ) {
	return;
}

/**
 * Apply blocking only on storefront-like requests — not wp-admin, not cron/cli, not during install.
 */
function ez_frontend_http_shield_should_apply(): bool {
	if ( function_exists( 'wp_installing' ) && wp_installing() ) {
		return false;
	}
	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		return false;
	}
	if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
		return false;
	}

	// Covers wp-admin/, admin-ajax.php, admin-post.php — license checks belong there too.
	if ( function_exists( 'is_admin' ) && is_admin() ) {
		return false;
	}

	return true;
}

add_filter(
	'pre_http_request',
	static function ( $preempt, $args, $url ) {
		if ( '' !== $preempt && false !== $preempt && null !== $preempt ) {
			return $preempt;
		}

		if ( ! ez_frontend_http_shield_should_apply() ) {
			return $preempt;
		}

		if ( ! is_string( $url ) || '' === $url ) {
			return $preempt;
		}

		$parsed = wp_parse_url( $url );
		if ( ! is_array( $parsed ) || empty( $parsed['host'] ) ) {
			return $preempt;
		}

		$host = strtolower( preg_replace( '/^www\./i', '', $parsed['host'] ) );

		$blocked = apply_filters(
			'ez_frontend_http_blocked_hosts',
			array(
				// Seen timing out (~10s) on local dev; kills TTFB for every public request hitting the updater.
				'update.role-editor.com',
				// Third-party premium update/metadata endpoints commonly hit on unrelated front requests — extend via filter if needed.
				'connect.advancedcustomfields.com',
				'update.digerati.ir',
				'packages.translationspress.com',
			)
		);

		if ( ! is_array( $blocked ) ) {
			return $preempt;
		}

		foreach ( $blocked as $raw ) {
			if ( ! is_string( $raw ) ) {
				continue;
			}
			$rule = strtolower( trim( $raw ) );
			if ( '' === $rule ) {
				continue;
			}

			// Exact host or subdomain suffix.
			if ( $host === $rule ) {
				return new WP_Error(
					'ez_frontend_http_shield_blocked',
					'Outbound update/license request deferred on storefront',
					array( 'url' => $url )
				);
			}

			if ( strlen( $host ) > strlen( $rule ) && str_ends_with( $host, '.' . $rule ) ) {
				return new WP_Error(
					'ez_frontend_http_shield_blocked',
					'Outbound update/license request deferred on storefront',
					array( 'url' => $url )
				);
			}
		}

		return $preempt;
	},
	9,
	3
);
