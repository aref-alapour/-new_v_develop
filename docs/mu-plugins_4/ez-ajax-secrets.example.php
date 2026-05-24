<?php
/**
 * Optional dedicated secrets file — loaded by secrets-bootstrap.php BEFORE wp-config parsing.
 *
 * Why: parsing wp-config.php on every /ajax hit adds ~1ms of regex work and reads a much larger file
 * than necessary. When you care about sub-100ms TTFB on hot endpoints (e.g. ping), copy this file to
 * `wp-content/mu-plugins/ez-ajax-secrets.php` and add it to .gitignore.
 *
 *   cp wp-content/mu-plugins/ez-ajax-secrets.example.php \
 *      wp-content/mu-plugins/ez-ajax-secrets.php
 *
 *   echo 'wp-content/mu-plugins/ez-ajax-secrets.php' >> .gitignore
 *
 * Then edit ez-ajax-secrets.php and replace the placeholder.
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'EZ_AJAX_GATEWAY_DISPATCH' ) ) {
	exit;
}

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) ) {
	define( 'EZ_AJAX_SHARED_SECRET', 'REPLACE-ME-WITH-openssl-rand-base64-48-OUTPUT' );
}
if ( ! defined( 'EZ_AJAX_NONCE_TTL' ) ) {
	define( 'EZ_AJAX_NONCE_TTL', 60 );
}
if ( ! defined( 'EZ_AJAX_TIMESTAMP_SKEW' ) ) {
	define( 'EZ_AJAX_TIMESTAMP_SKEW', 30 );
}
if ( ! defined( 'EZ_AJAX_SUB_SECRET_TTL' ) ) {
	define( 'EZ_AJAX_SUB_SECRET_TTL', 900 );
}
if ( ! defined( 'EZ_AJAX_SUB_SECRET_TTL_MAX' ) ) {
	define( 'EZ_AJAX_SUB_SECRET_TTL_MAX', 86400 );
}
