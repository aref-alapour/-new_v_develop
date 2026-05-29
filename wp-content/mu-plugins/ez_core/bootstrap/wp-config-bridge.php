<?php
/**
 * Load wp-config.php constants (DB, salts, DB_EXT_*) without bootstrapping WordPress.
 *
 * Used by ez-ajax-standalone.php when secrets.enc is absent (dev/Docker).
 */
declare(strict_types=1);

/**
 * Require repo wp-config.php with EZ_CORE_WP_CONFIG_BRIDGE so wp-settings.php is skipped.
 */
function ez_core_bridge_wp_config(): void {
	if ( defined( 'AUTH_KEY' ) && defined( 'DB_NAME' ) ) {
		return;
	}

	if ( ! defined( 'ABSPATH' ) ) {
		if ( ! defined( 'EZ_CORE_PATH' ) ) {
			return;
		}
		define( 'ABSPATH', dirname( EZ_CORE_PATH, 3 ) . '/' );
	}

	$config = ABSPATH . 'wp-config.php';
	if ( ! is_readable( $config ) ) {
		return;
	}

	if ( ! defined( 'EZ_CORE_WP_CONFIG_BRIDGE' ) ) {
		define( 'EZ_CORE_WP_CONFIG_BRIDGE', true );
	}

	require $config;
}
