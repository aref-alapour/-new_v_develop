<?php
/**
 * Optional wp_checkout_intent table (CRM DB). DDL: escapezoom_ddl_manual_2026.sql section 4
 * (woo_customer_session + idx for guest supersede).
 * Upserts a lightweight row during checkout; removes the row when the order is placed (no converted state).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Physical table name on the CRM Medoo schema (default wp_checkout_intent).
 * Optional: define EZ_CHECKOUT_INTENT_TABLE in wp-config.php.
 *
 * @return string
 */
function ez_checkout_intent_table() {
	$default = 'wp_checkout_intent';
	if ( defined( 'EZ_CHECKOUT_INTENT_TABLE' ) && EZ_CHECKOUT_INTENT_TABLE !== '' ) {
		return apply_filters( 'ez_checkout_intent_table', (string) EZ_CHECKOUT_INTENT_TABLE );
	}
	return apply_filters( 'ez_checkout_intent_table', $default );
}

/**
 * Whether wp_checkout_intent exists on the CRM Medoo schema.
 */
function ez_checkout_intent_table_ready() {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$memo = false;
	if ( ! function_exists( 'medoo' ) ) {
		return $memo;
	}

	$table = ez_checkout_intent_table();
	if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) {
		return $memo;
	}

	try {
		$statement = medoo()->query( "SHOW TABLES LIKE '" . $table . "'" );
		if ( $statement ) {
			$row  = $statement->fetch( PDO::FETCH_NUM );
			$memo = ! empty( $row );
		}
	} catch ( Throwable $e ) {
		$memo = false;
	}

	return $memo;
}

/**
 * Whether CRM table has woo_customer_session (manual ALTER after deploy).
 */
function ez_checkout_intent_has_woo_customer_session_column() {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$memo = false;
	if ( ! ez_checkout_intent_table_ready() ) {
		return $memo;
	}
	$table = ez_checkout_intent_table();
	if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) {
		return $memo;
	}
	try {
		$statement = medoo()->query( 'SHOW COLUMNS FROM `' . $table . "` LIKE 'woo_customer_session'" );
		if ( $statement ) {
			$row  = $statement->fetch( PDO::FETCH_NUM );
			$memo = ! empty( $row );
		}
	} catch ( Throwable $e ) {
		$memo = false;
	}
	return $memo;
}

/**
 * Session-derived intent token (must match capture for the same checkout session).
 *
 * @param int    $user_id User ID (0 if guest).
 * @param int    $product_id Product ID.
 * @param int    $sans_ts Unix slot timestamp.
 * @param string $customer_session_id Woo customer/session id string.
 * @return string 64-char hex sha256
 */
function ez_checkout_intent_token( $user_id, $product_id, $sans_ts, $customer_session_id ) {
	return hash( 'sha256', (string) (int) $user_id . '|' . (int) $product_id . '|' . (int) $sans_ts . '|' . (string) $customer_session_id );
}

/**
 * Direct checkout URL matching ez_capture_checkout_intent query args (book, add-to-cart, quantity).
 *
 * @param int $product_id Woo product ID.
 * @param int $sans_ts    Unix slot timestamp.
 * @param int $qty        Party size / line quantity (min 1).
 * @return string
 */
function ez_checkout_intent_resume_url( $product_id, $sans_ts, $qty = 1 ) {
	$product_id = (int) $product_id;
	$sans_ts    = (int) $sans_ts;
	$qty        = max( 1, (int) $qty );
	if ( $product_id <= 0 || $sans_ts <= 0 || ! function_exists( 'wc_get_checkout_url' ) ) {
		return home_url( '/' );
	}
	return add_query_arg(
		array(
			'add-to-cart' => $product_id,
			'book'        => $sans_ts,
			'quantity'    => $qty,
		),
		wc_get_checkout_url()
	);
}

/**
 * Remove checkout intent row(s) for this order — table stores only pending carts.
 *
 * @param int $order_id WooCommerce order ID.
 */
function ez_remove_checkout_intents_for_order( $order_id ) {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! function_exists( 'medoo' ) ) {
		return;
	}
	if ( ! ez_checkout_intent_table_ready() ) {
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	$pid = null;
	foreach ( $order->get_items() as $item ) {
		if ( $item instanceof WC_Order_Item_Product ) {
			$pid = (int) $item->get_product_id();
			break;
		}
	}
	$sans = (int) get_post_meta( $order_id, 'sans_time', true );

	if ( ! $pid || ! $sans ) {
		return;
	}

	$t           = ez_checkout_intent_table();
	$uid         = (int) $order->get_customer_id();
	$cust_session = '';
	if ( function_exists( 'WC' ) && WC()->session ) {
		$cust_session = (string) WC()->session->get_customer_id();
	}

	$tokens = array_unique(
		array(
			ez_checkout_intent_token( $uid, $pid, $sans, $cust_session ),
			ez_checkout_intent_token( $uid, $pid, $sans, '' ),
		)
	);

	try {
		$medoo = medoo();

		foreach ( $tokens as $tok ) {
			if ( '' !== $tok ) {
				$medoo->delete( $t, array( 'intent_token' => $tok ) );
			}
		}

		if ( $uid > 0 ) {
			$medoo->delete(
				$t,
				array(
					'AND' => array(
						'user_id'    => $uid,
						'product_id' => $pid,
						'sans_ts'    => $sans,
					),
				)
			);
		}
	} catch ( Throwable $e ) {
		error_log( '[checkout_intent remove] ' . $e->getMessage() );
	}
}

/**
 * @param int $order_id WooCommerce order ID.
 * @deprecated Use ez_remove_checkout_intents_for_order().
 */
function ez_close_checkout_intent_for_order( $order_id ) {
	ez_remove_checkout_intents_for_order( $order_id );
}

/**
 * Capture or refresh a pending intent row when the user reaches checkout with book + add-to-cart query args.
 *
 * @return void
 */
function ez_capture_checkout_intent() {
	if ( ! is_checkout() || ! function_exists( 'medoo' ) ) {
		return;
	}
	if ( empty( $_GET['book'] ) || empty( $_GET['add-to-cart'] ) ) {
		return;
	}
	if ( ! ez_checkout_intent_table_ready() ) {
		return;
	}

	$product_id = isset( $_GET['add-to-cart'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['add-to-cart'] ) ) : 0;
	$book_raw   = isset( $_GET['book'] ) ? sanitize_text_field( wp_unslash( $_GET['book'] ) ) : '';
	$sans_ts    = is_numeric( $book_raw ) ? (int) $book_raw : 0;

	if ( $product_id <= 0 || $sans_ts <= 0 ) {
		return;
	}

	$user_id      = get_current_user_id();
	$cust_session = ( function_exists( 'WC' ) && WC()->session ) ? WC()->session->get_customer_id() : '';
	$intent_token = ez_checkout_intent_token( $user_id, $product_id, $sans_ts, $cust_session );

	$qty = isset( $_GET['quantity'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['quantity'] ) ) : 1;
	$qty = max( 1, $qty );

	$t       = ez_checkout_intent_table();
	$row_min = array(
		'intent_token' => $intent_token,
		'user_id'      => $user_id ? $user_id : null,
		'product_id'   => $product_id,
		'sans_ts'      => $sans_ts,
		'qty'          => $qty,
		'updated_at'   => gmdate( 'Y-m-d H:i:s' ),
	);
	$row_ins = array_merge(
		$row_min,
		array(
			'created_at' => gmdate( 'Y-m-d H:i:s' ),
		)
	);

	$sess_ok = ez_checkout_intent_has_woo_customer_session_column();
	if ( $sess_ok ) {
		$row_min['woo_customer_session'] = ( $cust_session !== '' ) ? (string) $cust_session : null;
		$row_ins['woo_customer_session'] = ( $cust_session !== '' ) ? (string) $cust_session : null;
	}

	try {
		$medoo = medoo();
		if ( $user_id > 0 ) {
			try {
				$medoo->delete(
					$t,
					array(
						'AND' => array(
							'user_id'         => $user_id,
							'intent_token[!]' => $intent_token,
						),
					)
				);
			} catch ( Throwable $del_e ) {
				error_log( '[checkout_intent supersede] ' . $del_e->getMessage() );
			}
		} elseif ( $sess_ok && $cust_session !== '' ) {
			try {
				$medoo->delete(
					$t,
					array(
						'AND' => array(
							'woo_customer_session' => (string) $cust_session,
							'intent_token[!]'      => $intent_token,
						),
					)
				);
			} catch ( Throwable $del_e ) {
				error_log( '[checkout_intent supersede guest] ' . $del_e->getMessage() );
			}
		}
		if ( $medoo->has( $t, array( 'intent_token' => $intent_token ) ) ) {
			$medoo->update( $t, $row_min, array( 'intent_token' => $intent_token ) );
			return;
		}
		$medoo->insert( $t, $row_ins );
	} catch ( Throwable $e ) {
		error_log( '[checkout_intent] ' . $e->getMessage() );
	}
}

add_action( 'woocommerce_before_checkout_form', 'ez_capture_checkout_intent', 20, 0 );
add_action( 'woocommerce_checkout_order_processed', 'ez_remove_checkout_intents_for_order', 50, 1 );
add_action( 'woocommerce_payment_complete', 'ez_remove_checkout_intents_for_order', 20, 1 );
