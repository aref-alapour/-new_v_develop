<?php
/**
 * Bootstrap for team panel callbacks hit directly (not via admin-ajax).
 */
if ( ! defined( 'ABSPATH' ) ) {
	$wp_load = $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php';
	if ( ! is_readable( $wp_load ) ) {
		http_response_code( 500 );
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo 'WordPress bootstrap not found.';
		exit;
	}
	require_once $wp_load;
}

if ( ! function_exists( 'medoo' ) ) {
	$medoo_init = $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/escapezoom-v2/inc/medoo/init.php';
	if ( is_readable( $medoo_init ) ) {
		require_once $medoo_init;
	}
}

if ( ! is_user_logged_in() ) {
	http_response_code( 403 );
	header( 'Content-Type: text/plain; charset=utf-8' );
	echo 'Unauthorized';
	exit;
}
