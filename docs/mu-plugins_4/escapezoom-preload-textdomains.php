<?php
/**
 * Early WooCommerce textdomain preload and WooCommerce Blocks pattern mitigation.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'ez_escapezoom_preload_plugin_textdomains' ) ) {
	function ez_escapezoom_preload_plugin_textdomains() {
		if ( defined( 'WC_PLUGIN_FILE' ) && ! is_textdomain_loaded( 'woocommerce' ) ) {
			load_plugin_textdomain( 'woocommerce', false, dirname( plugin_basename( WC_PLUGIN_FILE ) ) . '/i18n/languages' );
		}

		if ( defined( 'WOO_WALLET_PLUGIN_FILE' ) && ! is_textdomain_loaded( 'woo-wallet' ) ) {
			load_plugin_textdomain( 'woo-wallet', false, dirname( plugin_basename( WOO_WALLET_PLUGIN_FILE ) ) . '/languages' );
		}
	}
}

add_action( 'plugins_loaded', 'ez_escapezoom_preload_plugin_textdomains', 1 );
add_action( 'woocommerce_loaded', 'ez_escapezoom_preload_plugin_textdomains', 0 );
add_action( 'after_setup_theme', 'ez_escapezoom_preload_plugin_textdomains', 0 );
add_action( 'init', 'ez_escapezoom_preload_plugin_textdomains', 0 );

add_filter(
	'woocommerce_admin_get_feature_config',
	static function ( $config ) {
		if ( is_array( $config ) && array_key_exists( 'pattern-toolkit-full-composability', $config ) ) {
			$config['pattern-toolkit-full-composability'] = false;
		}

		return $config;
	}
);

if ( ! function_exists( 'ez_escapezoom_remove_wc_block_pattern_hooks' ) ) {
	function ez_escapezoom_remove_wc_block_pattern_hooks( $hook, $method ) {
		global $wp_filter;

		if ( ! isset( $wp_filter[ $hook ] ) || ! $wp_filter[ $hook ] instanceof WP_Hook ) {
			return 0;
		}

		$removed = 0;

		foreach ( $wp_filter[ $hook ]->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				$function = $callback['function'];

				if ( ! is_array( $function ) || ! is_object( $function[0] ) || $method !== $function[1] ) {
					continue;
				}

				$class = get_class( $function[0] );
				if ( 0 !== strpos( $class, 'Automattic\\WooCommerce\\Blocks\\' ) ) {
					continue;
				}

				remove_action( $hook, array( $function[0], $method ), $priority );
				$removed++;
			}
		}

		return $removed;
	}
}

if ( ! function_exists( 'ez_escapezoom_disable_woocommerce_block_patterns' ) ) {
	function ez_escapezoom_disable_woocommerce_block_patterns() {
		if ( ! class_exists( '\Automattic\WooCommerce\Blocks\Package' ) ) {
			return;
		}

		$container = \Automattic\WooCommerce\Blocks\Package::container();

		try {
			$patterns = $container->get( \Automattic\WooCommerce\Blocks\BlockPatterns::class );
			remove_action( 'init', array( $patterns, 'register_block_patterns' ) );
			remove_action( 'init', array( $patterns, 'register_ptk_patterns' ) );
		} catch ( Exception $e ) {
			// Block pattern service not registered yet.
		}

		try {
			$controller = $container->get( \Automattic\WooCommerce\Blocks\BlockTypesController::class );
			remove_action( 'wp_loaded', array( $controller, 'register_block_patterns' ) );
		} catch ( Exception $e ) {
			// Block types controller not registered yet.
		}

		foreach ( array( 'register_block_patterns', 'register_ptk_patterns' ) as $method ) {
			ez_escapezoom_remove_wc_block_pattern_hooks( 'init', $method );
		}
		foreach ( array( 'register_patterns', 'register_block_patterns' ) as $method ) {
			ez_escapezoom_remove_wc_block_pattern_hooks( 'wp_loaded', $method );
		}
	}
}

add_action( 'woocommerce_blocks_loaded', 'ez_escapezoom_disable_woocommerce_block_patterns', 999 );
add_action( 'init', 'ez_escapezoom_disable_woocommerce_block_patterns', 11 );
add_action( 'wp_loaded', 'ez_escapezoom_disable_woocommerce_block_patterns', 0 );
