<?php
/**
 * Loads theme PHP modules (hooks, enqueue, shop/marketing, integrations).
 * Paths rely on Theme_PATH / Theme_URL from functions.php.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once Theme_PATH . 'inc/theme/constants-uri.php';
require_once Theme_PATH . 'inc/core/products-snapshot/ProductsSnapshotRepository.php';
require_once Theme_PATH . 'inc/core/products-snapshot/ProductsSnapshotSwiperRenderer.php';
require_once Theme_PATH . 'inc/core/products-snapshot/ProductsSnapshotSwiperApi.php';
require_once Theme_PATH . 'inc/theme/ez-brand-related-products-snapshot.php';
require_once Theme_PATH . 'vendor/CMB2/cmb2-init.php';

require_once Theme_PATH . 'inc/core/brands/ProductBrandTaxonomyTweaks.php';
require_once Theme_PATH . 'template/stencil/components/brand-card/brand-card.php';
require_once Theme_PATH . 'inc/core/brands/ProductBrandTermAdmin.php';
require_once Theme_PATH . 'inc/core/brands/BrandsDirectoryPartial.php';
require_once Theme_PATH . 'inc/theme/ez-ajax-boot-data.php';

require_once Theme_PATH . 'inc/theme/core-hooks.php';
require_once Theme_PATH . 'inc/theme/account-nav-icons-registry.php';
require_once Theme_PATH . 'inc/theme/account-nav-icons.php';
if ( is_admin() ) {
	require_once Theme_PATH . 'inc/theme/account-nav-icons-admin.php';
}

require_once get_template_directory() . '/inc/wallet/wallet.php';
require_once Theme_PATH . 'inc/core/product-ratings/init.php';
require_once Theme_PATH . 'inc/core/product-ranking/init.php';
include_once Theme_PATH . 'app/functions/helper/product_review_eligibility.php';
include_once Theme_PATH . 'app/functions/helper/account_holder_products.php';
include_once Theme_PATH . 'app/functions/helper/order_satisfaction.php';
include get_template_directory() . '/inc/saeed-codes.php';
include get_template_directory() . '/inc/admin/admin-settings.php';
include get_template_directory() . '/inc/api-shortener.php';

require_once Theme_PATH . 'inc/theme/enqueue-and-hooks.php';
require_once Theme_PATH . 'inc/theme/woocommerce-theme.php';
require_once Theme_PATH . 'inc/theme/cities-data.php';
require_once Theme_PATH . 'inc/theme/theme-includes.php';
require_once Theme_PATH . 'inc/theme/media-avif.php';
require_once Theme_PATH . 'inc/shop/marketing/marketing-order-financial.php';
require_once Theme_PATH . 'inc/shop/marketing/booking-recovery.php';
require_once Theme_PATH . 'inc/theme/sms-payamak.php';
require_once Theme_PATH . 'inc/theme/http-debug-outbound.php';
require_once Theme_PATH . 'inc/theme/auth-login-duration.php';
require_once Theme_PATH . 'inc/theme/maintenance-mode.php';
