<?php
if ( ! function_exists( 'ez_checkout_intent_table' ) || ! function_exists( 'ez_checkout_intent_table_ready' ) ) {
	wp_send_json_error( 'checkout intent helper بارگذاری نشده است.' );
}

if ( ! ez_checkout_intent_table_ready() ) {
	wp_send_json_error( 'جدول wp_checkout_intent یافت نشد.' );
}

$table = ez_checkout_intent_table();
if ( ! preg_match( '/^[a-zA-Z0-9_]+$/', $table ) ) {
	wp_send_json_error( 'نام جدول نامعتبر است.' );
}

$medoo = medoo();
$page   = isset( $_POST['page'] ) ? max( 1, absint( $_POST['page'] ) ) : 1;
$per_pg = isset( $_POST['per_page'] ) ? max( 1, min( 200, absint( $_POST['per_page'] ) ) ) : 80;
$offset = ( $page - 1 ) * $per_pg;

try {
	$rows  = $medoo->select(
		$table,
		'*',
		array(
			'ORDER' => array( 'updated_at' => 'DESC' ),
			'LIMIT' => array( $offset, $per_pg ),
		)
	);
	$total = (int) $medoo->count( $table );
} catch ( Throwable $e ) {
	wp_send_json_error( $e->getMessage() );
}

if ( ! is_array( $rows ) ) {
	$rows = array();
}

foreach ( $rows as &$r ) {
	$pid = isset( $r['product_id'] ) ? absint( $r['product_id'] ) : 0;
	$uid = isset( $r['user_id'] ) && $r['user_id'] !== null ? absint( $r['user_id'] ) : 0;

	$r['product_title']  = $pid ? get_the_title( $pid ) : '';
	$r['user_display']   = '';
	$r['user_phone']     = '';
	if ( $uid > 0 ) {
		$u = get_userdata( $uid );
		if ( $u ) {
			$r['user_display'] = $u->display_name;
			$r['user_phone']   = (string) get_user_meta( $uid, 'billing_phone', true );
			if ( $r['user_phone'] === '' && preg_match( '/^\d{10,11}$/', (string) $u->user_login ) ) {
				$r['user_phone'] = (string) $u->user_login;
			}
		}
	}
}
unset( $r );

wp_send_json_success(
	array(
		'rows'        => $rows,
		'total'       => $total,
		'page'        => $page,
		'per_page'    => $per_pg,
		'total_pages' => max( 1, (int) ceil( $total / $per_pg ) ),
	)
);
