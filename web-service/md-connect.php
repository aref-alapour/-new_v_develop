<?php
/**
 * Shared Medoo instances for standalone web-service scripts.
 * Loads WordPress first so EZ_MEDOO_CRM_DATABASE / wp-config apply; avoids redeclaring medoo() if theme already loaded it.
 */

$wp_load = dirname( __DIR__ ) . '/wp-load.php';
if ( ! is_file( $wp_load ) ) {
	throw new Exception( 'wp-load.php not found: ' . $wp_load );
}
require_once $wp_load;
$ezMedooInit = dirname( __DIR__ ) . '/wp-content/themes/escapezoom-v2/inc/medoo/init.php';

if ( ! function_exists( 'medoo' ) ) {
	if ( ! is_file( $ezMedooInit ) ) {
		throw new Exception( 'Medoo init not found: ' . $ezMedooInit );
	}
	require_once $ezMedooInit;
}

$ez_database = medoo();
$qr_database = medoo_queries();
