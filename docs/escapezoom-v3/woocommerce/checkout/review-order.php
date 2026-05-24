<?php
/**
 * Review order table
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/review-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 5.2.0
 */

defined( 'ABSPATH' ) || exit;

global $wldb;

$user_id = get_current_user_id();
$balance = $wldb->get_balance( $user_id );

?>

<div class="shop_table woocommerce-checkout-review-order-table bg-breserve border border-edge font-semibold p-4 md:p-6 rounded-xl space-y-4 child:flex child:items-center child:justify-between shadow-13">

	<?php do_action( 'woocommerce_review_order_before_cart_contents' ); ?>

	<?php do_action( 'woocommerce_review_order_after_cart_contents' ); ?>

	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

</div>
