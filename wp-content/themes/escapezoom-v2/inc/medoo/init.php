<?php
/**
 * Medoo connections (singleton per request).
 *
 * Topology (verify in phpMyAdmin):
 * - medoo()      → EZ_MEDOO_CRM_DATABASE (wp_markting, wp_orders_log, …). Often same as DB_NAME.
 * - medoo_queries() → DB_EXT_NAME (escapezo_queries: wp_zb_booking_history, products_data, …).
 * - $wpdb        → DB_NAME (WordPress core; notifications, collections, booking_lock_schedule).
 *
 * Standalone scripts that only include this file (no wp-load) use the same fallbacks as before.
 */
$ezMedooInit = dirname( __DIR__ ) . '/medoo/Medoo.php';

if ( ! is_file( $ezMedooInit ) ) {
	throw new Exception( 'Medoo init not found: ' . $ezMedooInit );
}
require_once $ezMedooInit;
use Medoo\Medoo;

/**
 * @return array{type:string,host:string,database:string,username:string,password:string,charset:string,option:array}
 */
function ez_medoo_crm_config(): array {
	$db   = ( defined( 'EZ_MEDOO_CRM_DATABASE' ) && EZ_MEDOO_CRM_DATABASE ) ? EZ_MEDOO_CRM_DATABASE : 'escapezo_ez9920';
	$host = defined( 'DB_HOST' ) ? DB_HOST : 'localhost';
	$user = defined( 'DB_USER' ) ? DB_USER : 'escapezo_ez9920';
	$pass = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '+)BxI4K)9bc!WUn#';

	return [
		'type'     => 'mysql',
		'host'     => $host,
		'database' => $db,
		'username' => $user,
		'password' => $pass,
		'charset'  => 'utf8mb4',
		'option'   => [
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
		],
	];
}

/**
 * @return array{type:string,host:string,database:string,username:string,password:string,charset:string,option:array}
 */
function ez_medoo_queries_config(): array {
	$db   = defined( 'DB_EXT_NAME' ) ? DB_EXT_NAME : 'escapezo_queries';
	$host = defined( 'DB_EXT_HOST' ) ? DB_EXT_HOST : ( defined( 'DB_HOST' ) ? DB_HOST : 'localhost' );
	$user = defined( 'DB_EXT_USER' ) ? DB_EXT_USER : ( defined( 'DB_USER' ) ? DB_USER : 'escapezo_escapezoom' );
	$pass = defined( 'DB_EXT_PASSWORD' ) ? DB_EXT_PASSWORD : ( defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '}lg#0#SaA}0%$zQn' );

	return [
		'type'     => 'mysql',
		'host'     => $host,
		'database' => $db,
		'username' => $user,
		'password' => $pass,
		'charset'  => 'utf8mb4',
		'option'   => [
			\PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
		],
	];
}

function medoo() {
	static $medoo = null;
	if ( $medoo === null ) {
		$medoo = new Medoo( ez_medoo_crm_config() );
	}
	return $medoo;
}

function medoo_queries() {
	static $medoo_queries = null;
	if ( $medoo_queries === null ) {
		$medoo_queries = new Medoo( ez_medoo_queries_config() );
	}
	return $medoo_queries;
}
