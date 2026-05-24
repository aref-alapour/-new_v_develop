<?php
/**
 * Output a single payment method
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment-method.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.5.0
 * @var $gateway
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="rounded-lg py-2 px-3 bg-gray-20 content-center">
    <label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" class="flex items-center gap-x-4 cursor-pointer peer-checked:text-white">
        <input class="hidden peer" type="radio" name="payment_method" id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>">

        <span class="bg-white flex items-center justify-center w-7.5 h-7.5 rounded-full border border-border-1 peer-checked:bg-payment-title peer-checked:border-payment-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="11" viewBox="0 0 14 11" fill="none">
                <path d="M6.05505 10.5388L13.5946 2.65308C13.8612 2.35416 14.0065 1.95862 13.9998 1.54984C13.993 1.14107 13.8348 0.751005 13.5584 0.461906C13.282 0.172821 12.909 0.00729693 12.5182 0.000235535C12.1274 -0.00682586 11.7492 0.145127 11.4634 0.424054L4.98946 7.19523L2.53661 4.62975C2.25082 4.35083 1.87264 4.19887 1.48181 4.20594C1.09097 4.213 0.718037 4.37852 0.441629 4.6676C0.165235 4.9567 0.00697661 5.34676 0.000225195 5.75554C-0.00652622 6.16432 0.138756 6.55986 0.405439 6.85877L3.92388 10.5388C4.20661 10.8341 4.58987 11 4.98946 11C5.38906 11 5.77232 10.8341 6.05505 10.5388Z" fill="white"/>
            </svg>
        </span>

        <span class="w-9 h-9 object-cover rounded drop-shadow-md flex items-center">
	        <?php echo $gateway->get_icon(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?>
        </span>

        <span><?php echo $gateway->get_title(); /* phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped */ ?></span>

    </label>
</div>