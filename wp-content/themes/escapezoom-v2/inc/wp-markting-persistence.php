<?php
/**
 * wp_markting persistence via $wpdb (WordPress DB). No Medoo required for CRM row writes.
 *
 * @package Escapezoom
 */

defined( 'ABSPATH' ) || exit;

/**
 * @return string
 */
function ez_markting_table_name(): string {
	return (string) apply_filters( 'ez_markting_table_name', 'wp_markting' );
}

/**
 * Whether wp_markting exists on the WordPress ($wpdb) connection.
 */
function ez_markting_wpdb_has_table(): bool {
	global $wpdb;
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$table = ez_markting_table_name();
	$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) );
	$memo  = ( $found === $table );
	return $memo;
}

/**
 * Whether wp_markting exists on the Medoo CRM connection.
 */
function ez_markting_medoo_crm_has_table(): bool {
	static $memo = null;
	if ( null !== $memo ) {
		return $memo;
	}
	$memo = false;
	if ( ! function_exists( 'medoo' ) ) {
		return false;
	}
	try {
		$medoo = medoo();
		if ( $medoo && method_exists( $medoo, 'count' ) ) {
			$medoo->count( ez_markting_table_name() );
			$memo = true;
		}
	} catch ( Throwable $e ) {
		$memo = false;
	}
	return $memo;
}

/**
 * Active storage: medoo_crm (preferred) or wpdb fallback.
 *
 * @return string|null wpdb|medoo_crm
 */
function ez_markting_storage_backend(): ?string {
	static $backend = '_unset';
	if ( '_unset' !== $backend ) {
		return 'none' === $backend ? null : $backend;
	}
	// Single wp_markting table: prefer Medoo CRM (panel/reports) when available, then $wpdb.
	if ( ez_markting_medoo_crm_has_table() ) {
		$backend = 'medoo_crm';
	} elseif ( ez_markting_wpdb_has_table() ) {
		$backend = 'wpdb';
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage( 0, 'markting_backend_wpdb', array( 'table' => ez_markting_table_name() ) );
		}
	} else {
		$backend = 'none';
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage( 0, 'markting_table_missing_on_wpdb', array( 'table' => ez_markting_table_name() ) );
		}
	}
	return 'none' === $backend ? null : $backend;
}

/**
 * @return bool
 */
function ez_markting_table_available(): bool {
	return null !== ez_markting_storage_backend();
}

/**
 * @param int $order_id Order ID.
 */
function ez_markting_row_exists( int $order_id ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! ez_markting_table_available() ) {
		return false;
	}
	$backend = ez_markting_storage_backend();
	if ( 'wpdb' === $backend ) {
		global $wpdb;
		$table = ez_markting_table_name();
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT order_id FROM `{$table}` WHERE order_id = %d LIMIT 1",
				$order_id
			)
		);
		return (int) $found === $order_id;
	}
	if ( 'medoo_crm' === $backend ) {
		try {
			$medoo = medoo();
			return $medoo && method_exists( $medoo, 'has' ) && $medoo->has( ez_markting_table_name(), array( 'order_id' => $order_id ) );
		} catch ( Throwable $e ) {
			return false;
		}
	}
	return false;
}

/**
 * @param int $order_id Order ID.
 * @return array<string,mixed>|null
 */
function ez_markting_get_row( int $order_id ): ?array {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || ! ez_markting_table_available() ) {
		return null;
	}
	$backend = ez_markting_storage_backend();
	if ( 'wpdb' === $backend ) {
		global $wpdb;
		$table = ez_markting_table_name();
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM `{$table}` WHERE order_id = %d LIMIT 1", $order_id ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}
	if ( 'medoo_crm' === $backend ) {
		try {
			$medoo = medoo();
			$row   = $medoo && method_exists( $medoo, 'get' )
				? $medoo->get( ez_markting_table_name(), '*', array( 'order_id' => $order_id ) )
				: null;
			return is_array( $row ) ? $row : null;
		} catch ( Throwable $e ) {
			return null;
		}
	}
	return null;
}

/**
 * @param int   $order_id Order ID.
 * @param array $data     Column => value.
 */
function ez_markting_update_fields( int $order_id, array $data ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 || empty( $data ) || ! ez_markting_table_available() ) {
		return false;
	}
	$backend = ez_markting_storage_backend();
	if ( 'wpdb' === $backend ) {
		global $wpdb;
		$table = ez_markting_table_name();
		$ok    = $wpdb->update( $table, $data, array( 'order_id' => $order_id ) );
		return false !== $ok;
	}
	if ( 'medoo_crm' === $backend ) {
		try {
			$medoo = medoo();
			if ( ! $medoo || ! method_exists( $medoo, 'update' ) ) {
				return false;
			}
			$updated = $medoo->update( ez_markting_table_name(), $data, array( 'order_id' => $order_id ) );
			return false !== $updated;
		} catch ( Throwable $e ) {
			error_log( '[ez_markting_update_fields] medoo_crm: ' . $e->getMessage() );
			return false;
		}
	}
	return false;
}

/**
 * @param array<string,mixed> $row Full row for insert.
 */
function ez_markting_insert_row( array $row ): bool {
	$order_id = isset( $row['order_id'] ) ? (int) $row['order_id'] : 0;
	$backend  = ez_markting_storage_backend();
	if ( 'wpdb' === $backend ) {
		global $wpdb;
		if ( false === $wpdb->insert( ez_markting_table_name(), $row ) ) {
			return false;
		}
		return $order_id > 0 && ez_markting_row_exists( $order_id );
	}
	if ( 'medoo_crm' === $backend ) {
		try {
			$medoo = medoo();
			if ( ! $medoo || ! method_exists( $medoo, 'insert' ) ) {
				return false;
			}
			$medoo->insert( ez_markting_table_name(), $row );
			return $order_id > 0 && ez_markting_row_exists( $order_id );
		} catch ( Throwable $e ) {
			error_log( '[ez_markting_insert_row] medoo_crm: ' . $e->getMessage() );
			return false;
		}
	}
	return false;
}

/**
 * Build wp_markting row from WooCommerce order (same fields as legacy save_to_markting_table).
 *
 * @return array{row:array<string,mixed>,product_id:int}|array{error:string}
 */
function ez_markting_build_row_from_order( WC_Order $order ) {
	$order_id = (int) $order->get_id();
	if ( $order_id <= 0 ) {
		return array( 'error' => 'invalid_order' );
	}

	$remove_prefixes = array(
		'اتاق فرار',
		'لیزرتگ',
		'سینما ترس',
		'اتاق خشم',
		'فوتبال حبابی',
		'کافه بازی',
		'بردگیم',
		'برد گیم',
		'پینت بال',
	);

	$customer_id = (int) $order->get_customer_id();
	global $wpdb;
	$customer_registered_at = null;
	if ( $customer_id > 0 ) {
		$customer_registered_at = $wpdb->get_var(
			$wpdb->prepare( "SELECT user_registered FROM {$wpdb->users} WHERE ID = %d LIMIT 1", $customer_id )
		);
	}

	$customer_level = null;
	if ( $customer_id && function_exists( 'get_user_level' ) ) {
		$customer_level = get_user_level( $customer_id );
	}

	$order_status     = function_exists( 'standardize_order_status' ) ? standardize_order_status( $order->get_status() ) : $order->get_status();
	$order_created_at = $order->get_date_created() ? $order->get_date_created()->date( 'Y-m-d H:i:s' ) : current_time( 'mysql' );
	$order_phones     = get_post_meta( $order_id, 'players_phone', true );
	if ( is_array( $order_phones ) ) {
		$order_phones = wp_json_encode( $order_phones, JSON_UNESCAPED_UNICODE );
	}

	$utm_source    = get_post_meta( $order_id, '_wc_order_attribution_utm_source', true ) ?: null;
	$session_entry = get_post_meta( $order_id, '_wc_order_attribution_session_entry', true ) ?: null;
	$referrer      = get_post_meta( $order_id, '_wc_order_attribution_referrer', true ) ?: null;
	$utm_medium    = get_post_meta( $order_id, '_wc_order_attribution_utm_medium', true ) ?: null;

	$order_refrerr = $utm_source;
	if (
		( is_string( $utm_source ) && strpos( $utm_source, 'escapezoom.co' ) !== false )
		|| ( is_string( $session_entry ) && strpos( $session_entry, 'escapezoom.co' ) !== false )
		|| ( is_string( $referrer ) && strpos( $referrer, 'escapezoom.co' ) !== false )
		|| ( is_string( $utm_medium ) && strpos( $utm_medium, 'cpc' ) !== false )
	) {
		$order_refrerr = 'escapezoom.co';
	}

	list( $product_id, $quantity ) = function_exists( 'ez_order_primary_bookable_line_item' )
		? ez_order_primary_bookable_line_item( $order )
		: array( null, 0 );
	$product_id = $product_id ? (int) $product_id : 0;
	$quantity   = (int) $quantity;

	if ( $product_id <= 0 ) {
		return array( 'error' => 'no_product_line' );
	}

	$product      = wc_get_product( $product_id );
	$product_post = get_post( $product_id );

	$game_brand = null;
	$brand_terms = get_the_terms( $product_id, 'yith_product_brand' );
	if ( $brand_terms && ! is_wp_error( $brand_terms ) && ! empty( $brand_terms ) ) {
		$game_brand = $brand_terms[0]->name;
	}

	$game_product_type = null;
	$game_city         = null;
	$terms             = get_the_terms( $product_id, 'product_cat' );
	if ( $terms && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( 0 === (int) $term->parent ) {
				$game_product_type = $term->name;
			} else {
				$game_city = $term->name;
			}
		}
		if ( 1 === count( $terms ) ) {
			$game_city = $terms[0]->name;
			$parent_term = get_term( $terms[0]->parent, 'product_cat' );
			$game_product_type = ( $parent_term && ! is_wp_error( $parent_term ) ) ? $parent_term->name : null;
		}
	}

	$game_city = is_string( $game_city ) ? $game_city : '';
	foreach ( $remove_prefixes as $prefix ) {
		if ( $game_city !== '' && mb_strpos( $game_city, $prefix . ' ' ) === 0 ) {
			$game_city = trim( mb_substr( $game_city, mb_strlen( $prefix ) ) );
			break;
		}
		if ( $game_city !== '' && mb_strpos( $game_city, $prefix ) === 0 ) {
			$game_city = trim( mb_substr( $game_city, mb_strlen( $prefix ) ) );
			break;
		}
	}

	$genres = array();
	$product_tags = get_the_terms( $product_id, 'product_tag' );
	if ( $product_tags && ! is_wp_error( $product_tags ) ) {
		foreach ( $product_tags as $product_tag ) {
			if ( strpos( $product_tag->name, '|||||' ) !== false ) {
				$genres[] = str_replace( '|||||', '', $product_tag->name );
			}
		}
	}

	$order_coupons       = $order->get_coupon_codes();
	$order_discount_code = ! empty( $order_coupons ) ? implode( ',', $order_coupons ) : null;
	$order_coupon_amount = null;
	$order_coupon_type   = null;
	if ( ! empty( $order_coupons ) ) {
		try {
			$coupon            = new WC_Coupon( $order_coupons[0] );
			$order_coupon_amount = $coupon->get_amount();
			$discount_type       = $coupon->get_discount_type();
			$order_coupon_type   = ( 'percent' === $discount_type || 'percent_product' === $discount_type ) ? 'percentage' : 'fixed';
		} catch ( Exception $e ) {
			error_log( 'ez_markting_build_row_from_order coupon: ' . $e->getMessage() );
		}
	}

	$pish_per_person = get_post_meta( $product_id, 'pish_pardakht_per_person', true );
	$pish_per_person = ! empty( $pish_per_person ) ? $pish_per_person : 1;

	$row = array(
		'customer_id'               => $customer_id,
		'customer_firstname'        => $order->get_billing_first_name(),
		'customer_lastname'         => $order->get_billing_last_name(),
		'customer_phone'            => $order->get_billing_phone(),
		'customer_registered_at'    => $customer_registered_at,
		'customer_level'            => $customer_level,
		'order_id'                  => $order_id,
		'order_status'              => $order_status,
		'order_phones'              => $order_phones,
		'order_prepaid_tickets'     => $pish_per_person,
		'order_tickets_quantity'    => max( 1, $quantity ),
		'order_refrerr'             => $order_refrerr,
		'order_coupon_used'         => $order_discount_code,
		'order_coupon_amount'       => $order_coupon_amount,
		'order_coupon_type'         => $order_coupon_type,
		'order_created_at'          => $order_created_at,
		'game_id'                   => $product_id,
		'game_name'                 => $product ? $product->get_title() : null,
		'game_city'                 => $game_city,
		'game_area'                 => function_exists( 'get_field' ) ? ( get_field( 'room_loc', $product_id ) ?: null ) : null,
		'game_product_type'         => $game_product_type,
		'game_genres'               => ! empty( $genres ) ? implode( ',', $genres ) : null,
		'game_duration'             => function_exists( 'get_field' ) ? ( get_field( 'room_duration', $product_id ) ?: null ) : null,
		'game_brand'                => $game_brand,
		'game_sans_manager_id'      => get_post_meta( $product_id, 'sans_manager', true ) ?: null,
		'game_user_ebtal_id'        => get_post_meta( $product_id, 'user_ebtal', true ) ?: null,
		'game_created_at'           => $product_post ? $product_post->post_date : null,
		'order_transaction_id'      => get_post_meta( $order_id, '_transaction_id', true ) ?: null,
		'order_happycall'           => get_post_meta( $order_id, 'supporting_happycall', true ) ?: 0,
		'order_paid'                => get_post_meta( $order_id, '_order_total_2', true ) ?: get_post_meta( $order_id, '_order_total', true ),
		'order_online_paid'         => get_post_meta( $order_id, '_order_total_2', true ) ?: null,
		'order_payment_gateway'     => $order->get_payment_method_title() ?: null,
		'order_payment_type'        => get_post_meta( $order_id, 'ez_payment_type', true ),
		'order_user_level_discount' => get_post_meta( $order_id, 'user_level_discount', true ) ?: null,
		'order_finall_price'        => null,
		'order_net_profit'          => null,
		'order_tax'                 => null,
		'order_sans_time'           => null,
		'order_sans_day'            => null,
		'order_sans_date'           => null,
	);

	if ( function_exists( 'ez_order_ticket_slug_from_order_key' ) ) {
		$slug = ez_order_ticket_slug_from_order_key( get_post_meta( $order_id, '_order_key', true ) );
		if ( $slug ) {
			$row['order_ticket_slug'] = $slug;
		}
	}

	return array(
		'row'        => $row,
		'product_id' => $product_id,
	);
}

/**
 * Ensure wp_markting row exists (no checkout abort unless requested).
 *
 * @param int           $order_id Order ID.
 * @param WC_Order|null $order    Order.
 * @param bool          $abort_on_fail_checkout Only true during web checkout insert.
 * @return array{ok:bool,reason?:string,inserted?:bool}
 */
function ez_markting_ensure_row_from_order( int $order_id, $order = null, bool $abort_on_fail_checkout = false ): array {
	$order_id = (int) $order_id;
	if ( ! $order instanceof WC_Order ) {
		$order = wc_get_order( $order_id );
	}
	if ( ! $order ) {
		return array( 'ok' => false, 'reason' => 'invalid_order' );
	}
	return ez_markting_upsert_from_order(
		$order,
		array( 'abort_on_fail_checkout' => $abort_on_fail_checkout )
	);
}

/**
 * @param int $booking_time Unix timestamp.
 * @return array{order_sans_date:string,order_sans_time:string,order_sans_day:string|null}|null
 */
function ez_markting_sans_fields_from_timestamp( int $booking_time ): ?array {
	if ( $booking_time <= 0 ) {
		return null;
	}
	$persian_days = array(
		0 => 'یکشنبه',
		1 => 'دوشنبه',
		2 => 'سه‌شنبه',
		3 => 'چهارشنبه',
		4 => 'پنج‌شنبه',
		5 => 'جمعه',
		6 => 'شنبه',
	);
	try {
		$date = new DateTime();
		$date->setTimestamp( $booking_time );
		return array(
			'order_sans_date' => $date->format( 'Y-m-d' ),
			'order_sans_time' => $date->format( 'H:i' ),
			'order_sans_day'  => $persian_days[ (int) $date->format( 'w' ) ] ?? null,
		);
	} catch ( Exception $e ) {
		error_log( '[ez_markting_sans_fields_from_timestamp] ' . $e->getMessage() );
		return null;
	}
}

/**
 * Insert or update wp_markting row.
 *
 * @param WC_Order $order Order.
 * @param array    $opts  abort_on_fail_checkout: bool, context: string.
 * @return array{ok:bool,reason?:string,inserted?:bool}
 */
function ez_markting_upsert_from_order( WC_Order $order, array $opts = array() ): array {
	$order_id = (int) $order->get_id();
	if ( $order_id <= 0 ) {
		return array( 'ok' => false, 'reason' => 'invalid_order' );
	}

	if ( ! ez_markting_table_available() ) {
		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage( $order_id, 'markting_upsert_no_table', array() );
		}
		if ( ! empty( $opts['abort_on_fail_checkout'] ) && function_exists( 'ez_abort_checkout_order_marketing_failure' ) ) {
			ez_abort_checkout_order_marketing_failure(
				$order,
				'table_unavailable',
				__( 'سیستم ثبت رزرو موقتاً در دسترس نیست. لطفاً چند دقیقه بعد دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2' )
			);
		}
		return array( 'ok' => false, 'reason' => 'table_unavailable' );
	}

	$built = ez_markting_build_row_from_order( $order );
	if ( isset( $built['error'] ) ) {
		if ( 'no_product_line' === $built['error'] ) {
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage( $order_id, 'markting_skip_no_product', array() );
			}
			if ( ! empty( $opts['abort_on_fail_checkout'] ) && function_exists( 'ez_abort_checkout_order_marketing_failure' ) ) {
				ez_abort_checkout_order_marketing_failure(
					$order,
					'no_product_line',
					__( 'خطا در ثبت رزرو: محصول سفارش شناسایی نشد. لطفاً دوباره از صفحهٔ بازی وارد چک‌اوت شوید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2' )
				);
			}
		}
		return array( 'ok' => false, 'reason' => (string) $built['error'] );
	}

	$row    = $built['row'];
	$exists = ez_markting_row_exists( $order_id );

	if ( ! $exists ) {
		$insert_ok = ez_markting_insert_row( $row );
		if ( ! $insert_ok ) {
			global $wpdb;
			$msg = 'insert failed backend=' . (string) ez_markting_storage_backend() . ' ' . $wpdb->last_error;
			if ( function_exists( 'log_order_error' ) ) {
				log_order_error( $order_id, 'ez_markting_upsert_from_order', $msg );
			}
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage( $order_id, 'markting_insert_failed', array( 'db' => $wpdb->last_error ) );
			}
			if ( ! empty( $opts['abort_on_fail_checkout'] ) && function_exists( 'ez_abort_checkout_order_marketing_failure' ) ) {
				ez_abort_checkout_order_marketing_failure(
					$order,
					'wp_markting_insert',
					__( 'ثبت اطلاعات رزرو در سیستم انجام نشد. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2' )
				);
			} elseif ( $order->is_paid() ) {
				$order->update_status( 'on-hold', 'EZ: ثبت مارکتینگ ناموفق پس از پرداخت — بررسی دستی.' );
			}
			return array( 'ok' => false, 'reason' => 'insert_failed' );
		}

		if ( ! ez_markting_row_exists( $order_id ) ) {
			if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
				ez_log_order_pipeline_stage( $order_id, 'markting_insert_verify_failed', array() );
			}
			if ( ! empty( $opts['abort_on_fail_checkout'] ) && function_exists( 'ez_abort_checkout_order_marketing_failure' ) ) {
				ez_abort_checkout_order_marketing_failure(
					$order,
					'wp_markting_insert_verify',
					__( 'ثبت اطلاعات رزرو در سیستم تأیید نشد. لطفاً دوباره تلاش کنید یا با پشتیبانی تماس بگیرید.', 'escapezoom-v2' )
				);
			}
			return array( 'ok' => false, 'reason' => 'insert_verify_failed' );
		}

		if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
			ez_log_order_pipeline_stage( $order_id, 'markting_upsert_ok', array( 'inserted' => 1 ) );
		}
		return array( 'ok' => true, 'inserted' => true );
	}

	$update_data = $row;
	unset( $update_data['order_finall_price'], $update_data['order_net_profit'], $update_data['order_tax'] );
	ez_markting_update_fields( $order_id, $update_data );

	if ( function_exists( 'ez_log_order_pipeline_stage' ) ) {
		ez_log_order_pipeline_stage( $order_id, 'markting_upsert_ok', array( 'inserted' => 0 ) );
	}
	return array( 'ok' => true, 'inserted' => false );
}

/**
 * @param WC_Order $order Order.
 */
function ez_markting_sync_status_from_order( WC_Order $order ): bool {
	$order_id = (int) $order->get_id();
	if ( $order_id <= 0 ) {
		return false;
	}
	if ( ! ez_markting_row_exists( $order_id ) ) {
		ez_markting_upsert_from_order( $order, array( 'abort_on_fail_checkout' => false ) );
	}
	if ( ! ez_markting_row_exists( $order_id ) ) {
		return false;
	}
	$status_std = function_exists( 'standardize_order_status' ) ? standardize_order_status( $order->get_status() ) : $order->get_status();
	$txn        = get_post_meta( $order_id, '_transaction_id', true );
	return ez_markting_update_fields(
		$order_id,
		array(
			'order_status'           => $status_std,
			'order_transaction_id' => $txn ? $txn : null,
		)
	);
}

/**
 * Delete marketing row for an order (unpaid resolver cancel, etc.).
 *
 * Uses $wpdb when the table is on the WordPress connection; Medoo CRM fallback otherwise.
 *
 * @param int $order_id Order ID.
 */
function ez_markting_delete_row( int $order_id ): bool {
	$order_id = (int) $order_id;
	if ( $order_id <= 0 ) {
		return true;
	}
	if ( function_exists( 'ez_markting_row_exists' ) && ! ez_markting_row_exists( $order_id ) ) {
		return true;
	}

	$backend = ez_markting_storage_backend();
	if ( 'wpdb' === $backend ) {
		global $wpdb;
		$table   = ez_markting_table_name();
		$deleted = $wpdb->delete( $table, array( 'order_id' => $order_id ), array( '%d' ) );
		if ( false !== $deleted ) {
			return true;
		}
	}
	if ( 'medoo_crm' === $backend ) {
		try {
			$medoo = medoo();
			if ( $medoo && method_exists( $medoo, 'delete' ) ) {
				$medoo->delete( ez_markting_table_name(), array( 'order_id' => $order_id ) );
				return true;
			}
		} catch ( Throwable $e ) {
			error_log( '[ez_markting_delete_row] medoo_crm: ' . $e->getMessage() );
		}
	}
	return false;
}

/**
 * Log order error via wpdb when Medoo path unavailable.
 */
function ez_markting_log_order_error_wpdb( int $order_id, string $function_name, string $log_message ): bool {
	global $wpdb;
	$table = 'wp_orders_log';
	$has_status = $wpdb->get_var( "SHOW COLUMNS FROM `{$table}` LIKE 'status'" );
	$data       = array(
		'order_id'       => $order_id,
		'order_function' => $function_name,
		'order_log'      => $log_message,
	);
	if ( $has_status ) {
		$data['status'] = 'active';
	}
	$ok = $wpdb->insert( $table, $data );
	return false !== $ok;
}
