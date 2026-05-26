<?php
/**
 * Theme integration for signed /ajax booking gateway.
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/ez-ajax-boot-data.php';
require_once __DIR__ . '/ez-ajax-sub-secret-rules.php';
require_once __DIR__ . '/booking-reserve-week.php';

/**
 * Gateway available when shared secret + ez_core booted.
 */
function ez_booking_gateway_enabled(): bool {
	return defined( 'EZ_AJAX_SHARED_SECRET' )
		&& '' !== (string) EZ_AJAX_SHARED_SECRET
		&& class_exists( '\\EZ\\Ajax\\Auth\\SubKey' );
}

add_filter(
	'ez_booking_use_internal',
	static function ( bool $enabled ): bool {
		if ( $enabled ) {
			return true;
		}
		return ez_booking_gateway_enabled();
	}
);

add_action(
	'after_setup_theme',
	static function (): void {
		if ( ! ez_booking_gateway_enabled() ) {
			return;
		}
		if ( ! get_option( 'ez_ajax_rewrite_flushed_v1' ) ) {
			flush_rewrite_rules( false );
			update_option( 'ez_ajax_rewrite_flushed_v1', 1, false );
		}
	}
);

/**
 * @return bool
 */
function ez_booking_should_boot_ajax(): bool {
	if ( ! ez_booking_gateway_enabled() ) {
		return false;
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		return true;
	}
	if ( function_exists( 'get_query_var' ) && get_query_var( 'reserve' ) ) {
		return true;
	}
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'sans-manager' ) ) {
		return true;
	}

	return false;
}

add_action(
	'wp_head',
	static function (): void {
		if ( ez_booking_should_boot_ajax() ) {
			ez_ajax_boot_print_inline();
		}
	},
	0
);

add_action(
	'wp_body_open',
	static function (): void {
		if ( ! ez_booking_should_boot_ajax() || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		$product_id = (int) get_queried_object_id();
		if ( $product_id <= 0 ) {
			return;
		}
		$tz    = new DateTimeZone( 'Asia/Tehran' );
		$start = ( new DateTimeImmutable( 'today', $tz ) )->getTimestamp();
		printf(
			'<script>document.body.dataset.ezProductId="%d";document.body.dataset.ezInitialDay="%d";</script>',
			$product_id,
			$start
		);
	},
	5
);

