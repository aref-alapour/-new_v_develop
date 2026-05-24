<?php
/**
 * Checkout fields, heading renderer, and order meta for booking (sans + teammates + comment token).
 * Consolidates former ahmadreza/init.php checkout hooks to avoid duplicate filters.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		if ( ! is_checkout() ) {
			return $fields;
		}

		if ( isset( $_GET['quantity'] ) && (int) $_GET['quantity'] > 1 ) {
			$fields['billing']['billing_heading_name'] = array(
				'type'  => 'heading',
				'label' => 'پیشنهاد می کنیم شماره سایر اعضاء تیم را نیز وارد کنید تا امکان درج بازخورد و امتیاز دهی به اتاق فرار را داشته باشند. ما پیامک تبلیغاتی ارسال نمیکنیم',
			);
		}

		if ( isset( $_GET['quantity'] ) ) {
			$q = (int) $_GET['quantity'];
			for ( $i = 2; $i <= $q; $i++ ) {
				$fields['billing'][ 'players_name_' . $i ]  = array(
					'label'       => 'نام و نام خانوادگی',
					'placeholder' => 'نام و نام خانوادگی',
					'required'    => false,
					'class'       => array( 'form-row-wide' ),
					'clear'       => true,
				);
				$fields['billing'][ 'players_phone_' . $i ] = array(
					'label'       => 'تلفن همراه',
					'placeholder' => 'تلفن همراه',
					'required'    => false,
					'class'       => array( 'form-row-wide' ),
					'clear'       => true,
				);
			}
		}

		if ( isset( $_GET['book'] ) ) {
			$fields['billing']['book'] = array(
				'label'       => 'تایم رزرو',
				'placeholder' => 'تایم رزرو',
				'required'    => false,
				'class'       => array( 'form-row-wide' ),
				'clear'       => true,
				'default'     => sanitize_text_field( wp_unslash( $_GET['book'] ) ),
			);
		}

		return $fields;
	},
	20
);

add_filter( 'woocommerce_form_field_heading', 'sc_woocommerce_form_field_heading', 10, 4 );
function sc_woocommerce_form_field_heading( $field, $key, $args, $value ) {
	$output = '<h3 class="form-row chkout_players_phone_desc">' . __( $args['label'], 'woocommerce' ) . '</h3>';
	echo $output;
}

/**
 * Saves sans_time, structured teammates meta, and comment_token (merged from legacy send_sms_comment_url + ahmadreza).
 */
function ez_shop_save_checkout_booking_meta( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$items = $order->get_items();
	$item  = reset( $items );
	$qty   = $item ? (int) $item->get_quantity() : 0;

	$players = array();
	for ( $i = 2; $i <= $qty; $i++ ) {
		if ( ! empty( $_POST[ 'players_name_' . $i ] ) && ! empty( $_POST[ 'players_phone_' . $i ] ) ) {
			$players[] = array(
				'name'  => sanitize_text_field( wp_unslash( $_POST[ 'players_name_' . $i ] ) ),
				'phone' => sanitize_text_field( wp_unslash( $_POST[ 'players_phone_' . $i ] ) ),
			);
		}
	}

	if ( isset( $_POST['book'] ) ) {
		$order->update_meta_data( 'sans_time', sanitize_text_field( wp_unslash( $_POST['book'] ) ) );
	}

	$order->update_meta_data( 'players_phone', $players );

	$token = randString( 7 ) . base64_url_encode( (string) $order_id );
	$order->update_meta_data( 'comment_token', $token );

	$order->save();
}

add_action( 'woocommerce_checkout_update_order_meta', 'ez_shop_save_checkout_booking_meta', 10, 1 );
