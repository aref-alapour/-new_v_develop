<?php
if (!defined('ABSPATH')) {
	exit;
}

/* =======================================================
    include file [start]
========================================================= */

include_once Theme_PATH . 'inc/medoo/init.php';
include_once Theme_PATH . 'inc/checkout-intent.php';
include_once Theme_PATH . 'app/init.php';
include_once Theme_PATH . 'aref-jan/init.php';
include_once Theme_PATH . 'saeed/init.php';
include_once Theme_PATH . 'inc/theme/ahmadreza/init.php';
include_once Theme_PATH . 'inc/admin/team/init.php';
include_once Theme_PATH . 'inc/http/func/auto-sync-products.php';
include_once Theme_PATH . 'inc/http/func/product-shortlink-column.php';
include_once Theme_PATH . 'inc/http/func/cron.php';
include_once Theme_PATH . 'inc/url-shortener/url-shortener.php';
include_once Theme_PATH . 'template/layout/categories-menu.php';
include_once Theme_PATH . 'template/layout/mega-menu-display.php';
include_once Theme_PATH . 'inc/admin/metabox/single-product.php';
include_once Theme_PATH . 'inc/admin/metabox/product-category-faq.php';
include_once Theme_PATH . 'inc/admin/options/cities-id.php';
include_once Theme_PATH . 'inc/admin/options/offer-games.php';
include_once Theme_PATH . 'inc/admin/options/promotional-games.php';
include_once Theme_PATH . 'inc/admin/options/ads-landing.php';
include_once Theme_PATH . 'inc/admin/options/sms-templates.php';
include_once Theme_PATH . 'inc/admin/options/mega-menu.php';
include_once Theme_PATH . 'inc/admin/options/bootcamp.php';
include_once Theme_PATH . 'inc/admin/options/orders-log.php';
include_once Theme_PATH . 'inc/admin/options/order-status-log.php';
include_once Theme_PATH . 'inc/admin/metabox/promotional-page.php';
include_once Theme_PATH . 'inc/admin/metabox/discounts-page.php';
include_once Theme_PATH . 'inc/admin/metabox/tandis-z.php';
include_once Theme_PATH . 'inc/admin/metabox/user-profile.php';
include_once Theme_PATH . 'inc/admin/call-me-notify.php';
include_once Theme_PATH . 'inc/admin/checkout-intent-admin.php';
include_once Theme_PATH . 'inc/admin/shortcodes/call-me-notify.php';

add_action(
	'wp_footer',
	function () {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
			return;
		}
		if ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) {
			return;
		}
		?>
		<script>
		jQuery(function ($) {
			var $btn = $('#checkout_btn_process');
			if (!$btn.length) {
				return;
			}
			$(document.body).on('checkout_error', function () {
				$btn.prop('disabled', false);
			});
			$('form.checkout').on('submit', function () {
				if ($btn.prop('disabled')) {
					return false;
				}
				$btn.prop('disabled', true);
			});
		});
		</script>
		<?php
	},
	99
);
