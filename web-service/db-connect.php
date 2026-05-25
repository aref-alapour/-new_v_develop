<?php

/**
 * External queries DB (escapezo_queries). Graceful failure for internal WP dispatch.
 *
 * @return mysqli|null
 */
function ez_reservation_db_connect(): ?mysqli {
	$servername = getenv( 'WORDPRESS_DB_EXT_HOST' ) ?: ( getenv( 'WORDPRESS_DB_HOST' ) ?: 'mysql' );
	$dbname     = getenv( 'WORDPRESS_DB_EXT_NAME' ) ?: 'escapezo_queries';
	$username   = getenv( 'WORDPRESS_DB_EXT_USER' ) ?: ( getenv( 'WORDPRESS_DB_USER' ) ?: 'root' );
	$password   = getenv( 'WORDPRESS_DB_EXT_PASSWORD' ) ?: ( getenv( 'WORDPRESS_DB_PASSWORD' ) ?: 'arefpassword' );

	$conn = @new mysqli( $servername, $username, $password, $dbname );
	if ( $conn->connect_error ) {
		if ( defined( 'EZ_BOOKING_INTERNAL_CALL' ) && EZ_BOOKING_INTERNAL_CALL ) {
			error_log( '[EZ Booking] External DB connection failed in internal call: ' . $conn->connect_error );
			return null;
		}
		die( 'Connection failed: ' . $conn->connect_error );
	}

	$conn->set_charset( 'utf8' );

	return $conn;
}

$conn = ez_reservation_db_connect();
