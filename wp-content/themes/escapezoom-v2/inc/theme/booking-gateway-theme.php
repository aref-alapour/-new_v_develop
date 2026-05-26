<?php
/**
 * Theme integration for signed /ajax booking gateway.
 */
defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/ez-ajax-boot-data.php';
require_once __DIR__ . '/ez-ajax-sub-secret-rules.php';
require_once __DIR__ . '/booking-reserve-week.php';
require_once __DIR__ . '/ez-team-shell.php';

use EscapeZoom\Core\Modules\AjaxGateway\GatewayBootDiagnostics;

/**
 * Gateway available when shared secret + ez_core booted.
 */
function ez_booking_gateway_enabled(): bool {
	$enabled = defined( 'EZ_AJAX_SHARED_SECRET' )
		&& '' !== (string) EZ_AJAX_SHARED_SECRET
		&& class_exists( '\\EZ\\Ajax\\Auth\\SubKey' );

	GatewayBootDiagnostics::log(
		'gateway_enabled_check',
		array(
			'gateway_enabled' => $enabled,
			'secret_defined'  => defined( 'EZ_AJAX_SHARED_SECRET' ),
			'subkey_class'    => class_exists( '\\EZ\\Ajax\\Auth\\SubKey' ),
		)
	);

	return $enabled;
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
 * Owner sans-manager (WooCommerce endpoint on /panel/sans-manager/ or my-account).
 *
 * @return bool
 */
function ez_booking_is_sans_manager_endpoint(): bool {
	if ( function_exists( 'is_wc_endpoint_url' ) && is_wc_endpoint_url( 'sans-manager' ) ) {
		return true;
	}
	if ( function_exists( 'get_query_var' ) ) {
		$qv = get_query_var( 'sans-manager', false );
		if ( false !== $qv ) {
			return true;
		}
	}

	return false;
}

/**
 * WooCommerce product single (room) — broader than is_product() alone (custom /room/ URLs).
 *
 * @return bool
 */
function ez_booking_is_product_context(): bool {
	if ( function_exists( 'is_product' ) && is_product() ) {
		return true;
	}
	if ( function_exists( 'is_singular' ) && is_singular( 'product' ) ) {
		return true;
	}
	$queried = get_queried_object();
	if ( $queried instanceof WP_Post && 'product' === $queried->post_type ) {
		return true;
	}
	global $post;
	if ( $post instanceof WP_Post && 'product' === $post->post_type ) {
		return true;
	}

	return false;
}

/**
 * Pages that need gateway boot data (product, reserve, sans-manager, team sans-management).
 *
 * @return bool
 */
function ez_ajax_should_boot(): bool {
	if ( defined( 'EZ_BOOKING_SANS_PANELS_GATEWAY' ) && ! EZ_BOOKING_SANS_PANELS_GATEWAY ) {
		return false;
	}

	if ( ez_booking_is_product_context() ) {
		return true;
	}
	if ( function_exists( 'get_query_var' ) && get_query_var( 'reserve' ) ) {
		return true;
	}
	if ( function_exists( 'ez_booking_is_sans_manager_endpoint' ) && ez_booking_is_sans_manager_endpoint() ) {
		return true;
	}
	if ( function_exists( 'ez_team_is_sans_management_page' ) && ez_team_is_sans_management_page() ) {
		return true;
	}

	return false;
}

/**
 * @return bool
 */
function ez_booking_should_boot_ajax(): bool {
	return ez_ajax_should_boot();
}

add_action(
	'wp',
	static function (): void {
		if ( ez_booking_should_boot_ajax() && ez_booking_gateway_enabled() ) {
			ez_booking_print_ajax_boot();
		}
	},
	5
);

/**
 * Emit boot when gateway secrets are loaded (sub_secret non-empty). Once per request.
 */
function ez_booking_print_ajax_boot(): void {
	static $printed = false;
	if ( $printed ) {
		GatewayBootDiagnostics::log(
			'boot_skip',
			array( 'reason' => 'already_printed' )
		);

		return;
	}
	if ( ! ez_booking_should_boot_ajax() ) {
		GatewayBootDiagnostics::log(
			'boot_skip',
			array(
				'reason'          => 'should_boot_ajax_false',
				'is_product'      => function_exists( 'is_product' ) && is_product(),
				'is_product_ctx'  => ez_booking_is_product_context(),
			)
		);

		return;
	}
	if ( ! ez_booking_gateway_enabled() ) {
		GatewayBootDiagnostics::log(
			'boot_skip',
			array( 'reason' => 'gateway_enabled_false' )
		);

		return;
	}
	$printed = true;
	ez_ajax_boot_print_inline();
	$boot = ez_ajax_boot_data();
	GatewayBootDiagnostics::log(
		'boot_script_printed',
		array(
			'sub_secret_nonempty' => '' !== (string) ( $boot['sub_secret'] ?? '' ),
			'product_id'          => isset( $boot['product_id'] ) ? (int) $boot['product_id'] : 0,
		)
	);
}

add_action( 'wp_head', 'ez_booking_print_ajax_boot', 0 );

add_action(
	'wp_footer',
	static function (): void {
		if ( ! ez_booking_should_boot_ajax() || ! ez_booking_gateway_enabled() ) {
			return;
		}
		if ( ! did_action( 'wp_head' ) ) {
			ez_booking_print_ajax_boot();
		}
	},
	0
);

add_action(
	'wp_enqueue_scripts',
	static function (): void {
		if ( ! ez_booking_should_boot_ajax() || ! ez_booking_gateway_enabled() ) {
			return;
		}
		wp_localize_script( 'main-js', 'ezAjaxBoot', ez_ajax_boot_data() );
	},
	20
);

add_filter(
	'ez_ajax_boot_data',
	static function ( array $boot ): array {
		if ( function_exists( 'is_product' ) && is_product() ) {
			$product_id = (int) get_queried_object_id();
			if ( $product_id > 0 ) {
				$boot['product_id'] = $product_id;
			}
		}

		return $boot;
	}
);

/**
 * Visible hint when booking pages need the gateway but secrets.enc is missing (dev / team / owners).
 */
add_action(
	'wp_footer',
	static function (): void {
		if ( ! function_exists( 'ez_ajax_should_boot' ) || ! ez_ajax_should_boot() ) {
			return;
		}
		if ( function_exists( 'ez_booking_gateway_enabled' ) && ez_booking_gateway_enabled() ) {
			return;
		}
		if ( ! function_exists( 'is_user_logged_in' ) || ! is_user_logged_in() ) {
			return;
		}

		$may_see = current_user_can( 'manage_options' );
		if ( ! $may_see && function_exists( 'ez_team_shell_user_has_access' ) ) {
			$may_see = ez_team_shell_user_has_access();
		}
		if ( ! $may_see && function_exists( 'is_account_page' ) && is_account_page() ) {
			$may_see = true;
		}
		if ( ! $may_see ) {
			return;
		}

		$err = class_exists( '\\EscapeZoom\\Core\\Infrastructure\\Config\\SecretsLoader' )
			? \EscapeZoom\Core\Infrastructure\Config\SecretsLoader::getBootError()
			: null;

		echo '<div id="ez-gateway-boot-warning" style="position:fixed;bottom:0;left:0;right:0;z-index:99999;background:#7f1d1d;color:#fff;padding:12px 16px;font:14px/1.5 Tahoma,sans-serif;text-align:center;">';
		echo esc_html__( 'رزرو آنلاین غیرفعال است: secrets.enc یا EZ_CORE_SECRETS_KEY در دسترس نیست.', 'escapezoom' );
		echo ' <code style="background:rgba(0,0,0,.25);padding:2px 6px;">php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php</code>';
		if ( $err ) {
			echo ' — ' . esc_html( (string) $err );
		}
		echo '</div>';
	},
	5
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

