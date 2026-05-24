<?php
/**
 * Trim plugin/core assets on the public frontend. Theme JS/CSS stay in dist/front.js and dist/front.css.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', 'ez_theme_cleanup_front_assets', 999 );
add_action( 'wp_print_styles', 'ez_theme_cleanup_front_assets', 1 );
add_action( 'wp_print_scripts', 'ez_theme_cleanup_front_assets', 1 );
add_filter( 'woocommerce_enqueue_styles', 'ez_theme_filter_woocommerce_styles', 20 );

function ez_theme_is_public_frontend() {
	return ! is_admin() && ! wp_doing_ajax() && ! wp_doing_cron();
}

function ez_theme_should_load_mediaad() {
	if ( ! ez_theme_is_public_frontend() ) {
		return false;
	}

	$host = isset( $_SERVER['SERVER_NAME'] ) ? strtolower( (string) $_SERVER['SERVER_NAME'] ) : '';

	return in_array(
		$host,
		array(
			'escapezoom.co',
			'www.escapezoom.co',
			'escapezoom.ir',
			'www.escapezoom.ir',
		),
		true
	);
}

function ez_theme_needs_wc_frontend_assets() {
	if ( ! function_exists( 'is_woocommerce' ) ) {
		return false;
	}

	if ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) {
		return true;
	}

	return function_exists( 'is_order_received_page' ) && is_order_received_page();
}

function ez_theme_needs_brand_assets() {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return true;
	}

	return is_tax( 'product_brand' );
}

function ez_theme_needs_wc_order_attribution_assets() {
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return true;
	}

	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return true;
	}

	return function_exists( 'is_order_received_page' ) && is_order_received_page();
}

function ez_theme_needs_wallet_assets() {
	return function_exists( 'is_account_page' ) && is_account_page();
}

function ez_theme_get_front_plugin_script_handles() {
	$handles = array(
		'mega-menu-script',
		'wp-postviews-cache',
		'akismet-frontend',
		'cld-frontend',
		'rate-my-post',
		'rmp-recaptcha',
		'wordfenceAJAXjs',
		'wfi18njs',
	);

	if ( ! ez_theme_needs_wc_order_attribution_assets() ) {
		$handles[] = 'sourcebuster-js';
		$handles[] = 'wc-order-attribution';
	}

	if ( ! ez_theme_needs_wc_frontend_assets() ) {
		$handles = array_merge(
			$handles,
			array(
				'woocommerce',
				'jquery-blockui',
				'js-cookie',
				'wc-add-to-cart',
				'wc-cart',
				'wc-cart-fragments',
				'wc-checkout',
				'wc-country-select',
				'wc-address-i18n',
				'wc-single-product',
				'wc-account-i18n',
				'wc-password-strength-meter',
				'wc-lost-password',
				'wc-add-payment-method',
				'wc-geolocation',
				'selectWoo',
				'wc-city-select',
			)
		);
	}

	return $handles;
}

function ez_theme_get_front_plugin_style_handles() {
	$handles = array(
		'wc-blocks-style',
		'woocommerce-layout',
		'woocommerce-general',
		'woocommerce-smallscreen',
		'woocommerce-inline',
		'woocommerce-blocktheme',
		'select2',
		'cld-font-awesome',
		'cld-frontend',
		'rate-my-post',
		'woo-wallet-payment-jquery-ui',
		'jquery-datatables-style',
		'jquery-datatables-responsive-style',
	);

	if ( ! ez_theme_needs_brand_assets() ) {
		$handles[] = 'yith-wcbr';
		$handles[] = 'brands-styles';
	}

	if ( ! ez_theme_needs_wallet_assets() ) {
		$handles[] = 'woo-wallet-style';
	}

	return $handles;
}

function ez_theme_forget_matching_assets( $type, $prefixes ) {
	$registry = 'styles' === $type ? wp_styles() : wp_scripts();

	if ( ! $registry instanceof WP_Dependencies ) {
		return;
	}

	foreach ( array_keys( (array) $registry->registered ) as $handle ) {
		foreach ( $prefixes as $prefix ) {
			if ( 0 === strpos( $handle, $prefix ) ) {
				if ( 'styles' === $type ) {
					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
				} else {
					wp_dequeue_script( $handle );
					wp_deregister_script( $handle );
				}
				break;
			}
		}
	}
}

function ez_theme_forget_assets( $type, $handles ) {
	foreach ( $handles as $handle ) {
		if ( 'styles' === $type ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
			continue;
		}

		wp_dequeue_script( $handle );
		wp_deregister_script( $handle );
	}
}

function ez_theme_filter_woocommerce_styles( $styles ) {
	if ( ! ez_theme_is_public_frontend() ) {
		return $styles;
	}

	return array();
}

function ez_theme_cleanup_front_assets() {
	if ( ! ez_theme_is_public_frontend() ) {
		return;
	}

	ez_theme_forget_assets( 'scripts', ez_theme_get_front_plugin_script_handles() );
	ez_theme_forget_assets( 'styles', ez_theme_get_front_plugin_style_handles() );
	ez_theme_forget_matching_assets( 'styles', array( 'wc-blocks-style-' ) );
	ez_theme_forget_matching_assets( 'scripts', array( 'wc-blocks-' ) );
}
