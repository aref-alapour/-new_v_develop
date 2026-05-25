<?php
// Allow CORS for all origins
header( 'Access-Control-Allow-Origin: *' );
header( 'Access-Control-Allow-Headers: Authorization, Accept, Origin, DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Content-Range, Range' );
header( 'Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH' );
header( 'Access-Control-Max-Age: 1728000' );

if ( $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) {
	http_response_code( 204 );
	exit;
}

error_reporting( E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED );
date_default_timezone_set( 'Asia/Tehran' );

$data = null;

if ( $_SERVER['REQUEST_METHOD'] === 'POST' ) {
	$content_type = isset( $_SERVER['CONTENT_TYPE'] ) ? trim( $_SERVER['CONTENT_TYPE'] ) : '';

	if ( str_contains( $content_type, 'application/json' ) ) {
		$data = json_decode( file_get_contents( 'php://input' ) );
	} elseif ( str_contains( $content_type, 'application/x-www-form-urlencoded' ) ) {
		$data = json_decode( json_encode( $_POST ) );
	} else {
		http_response_code( 415 );
		echo json_encode( array( 'error' => 'Unsupported Media Type' ) );
		exit;
	}
} else {
	http_response_code( 405 );
	echo json_encode( array( 'error' => 'Invalid Request Method' ) );
	exit;
}

require_once __DIR__ . '/includes/reservation-dispatch.php';

if ( ! isset( $data ) || ! is_object( $data ) ) {
	http_response_code( 400 );
	echo json_encode( array( 'error' => 'Invalid payload' ) );
	exit;
}

echo ez_reservation_dispatch( $data );
