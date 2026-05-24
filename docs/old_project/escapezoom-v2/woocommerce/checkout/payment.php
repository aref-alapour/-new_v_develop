<?php
/**
 * Checkout Payment Section
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 8.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_before_payment' );
}
?>
    <div id="payment" class="woocommerce-checkout-payment">

        <h3 class="my-4 font-semibold">انتخاب روش پرداخت</h3>

		<?php if ( WC()->cart->needs_payment() ) : ?>
            <ul class="wc_payment_methods payment_methods methods">
				<?php
				if ( ! empty( $available_gateways ) ) {
					foreach ( $available_gateways as $gateway ) {
						wc_get_template( 'checkout/payment-method.php', [ 'gateway' => $gateway ] );
					}
				} else {
					echo '<li>';
					wc_print_notice( apply_filters( 'woocommerce_no_available_payment_methods_message', WC()->customer->get_billing_country() ? esc_html__( 'Sorry, it seems that there are no available payment methods. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' ) ), 'notice' ); // phpcs:ignore WooCommerce.Commenting.CommentHooks.MissingHookComment
					echo '</li>';
				}
				?>
            </ul>
		<?php endif; ?>
        <div class="form-row place-order">
            <noscript>
				<?php
				/* translators: $1 and $2 opening and closing emphasis tags respectively */
				printf( esc_html__( 'Since your browser does not support JavaScript, or it is disabled, please ensure you click the %1$sUpdate Totals%2$s button before placing your order. You may be charged more than the amount stated above if you fail to do so.', 'woocommerce' ), '<em>', '</em>' );
				?>
                <br/>

                <button type="submit" class="button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>" name="woocommerce_checkout_update_totals" value="<?php esc_attr_e( 'Update totals', 'woocommerce' ); ?>"><?php esc_html_e( 'Update totals', 'woocommerce' ); ?></button>
            </noscript>

			<?php wc_get_template( 'checkout/terms.php' ); ?>

			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>

			<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" id="checkout_btn_process" class="button alt' . esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ( ' ' . wc_wp_theme_get_element_class_name( 'button' ) ) : '' ) . 'my-4.5 flex items-center h-15 rounded-xl font-semibold shadow-13 text-white text-lg w-full overflow-hidden" name="woocommerce_checkout_update_totals" value="' . esc_attr( $order_button_text ) . '" data-value="' . esc_attr( $order_button_text ) . '">
                <span class="payment-title">
                    <span class="font-bold lg:text-lg">پرداخت و ثبت رزرو</span>
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="21" viewBox="0 0 22 21" fill="none">
                            <g clip-path="url(#clip0_5890_283)">
                                <path d="M3.00759 9.57055C2.7501 9.81664 2.60547 10.1502 2.60547 10.498C2.60547 10.8459 2.7501 11.1795 3.00759 11.4255L8.19226 16.3772C8.4502 16.6234 8.80005 16.7617 9.16484 16.7617C9.52963 16.7617 9.87948 16.6234 10.1374 16.3772C10.3954 16.131 10.5403 15.797 10.5403 15.4488C10.5403 15.1006 10.3954 14.7666 10.1374 14.5204L7.29943 11.8105L16.4065 11.8105C16.7712 11.8105 17.1209 11.6723 17.3788 11.4261C17.6366 11.18 17.7815 10.8461 17.7815 10.498C17.7815 10.15 17.6366 9.81611 17.3788 9.56997C17.1209 9.32383 16.7712 9.18555 16.4065 9.18555L7.29943 9.18555L10.1374 6.47655C10.2651 6.35463 10.3665 6.2099 10.4356 6.05061C10.5047 5.89132 10.5403 5.72059 10.5403 5.54817C10.5403 5.37576 10.5047 5.20503 10.4356 5.04574C10.3665 4.88645 10.2651 4.74171 10.1374 4.6198C10.0097 4.49788 9.85808 4.40117 9.6912 4.33519C9.52433 4.26921 9.34547 4.23525 9.16484 4.23525C8.98422 4.23525 8.80536 4.26921 8.63848 4.33519C8.47161 4.40117 8.31998 4.49788 8.19226 4.6198L3.00759 9.57055Z" fill="currentColor"></path>
                            </g>
                            <defs>
                                <clipPath id="clip0_5890_283">
                                    <rect width="21" height="22" fill="white" transform="translate(0 21) rotate(-90)"></rect>
                                </clipPath>
                            </defs>
                        </svg>
                    </span>
                </span>
                <span class="payment-amount">
                    <span class="font-extrabold text-lg lg:text-2xl">' . WC()->cart->get_total() . '</span>
                </span>
            </button>' ); // @codingStandardsIgnoreLine ?>

			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>

			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
        </div>
    </div>
<?php
if ( ! wp_doing_ajax() ) {
	do_action( 'woocommerce_review_order_after_payment' );
}
