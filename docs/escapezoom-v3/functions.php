<?php
/**
 * Escapezoom v2 theme entry — constants only; logic lives under inc/.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'Theme_URL', get_template_directory_uri() . '/' );
define( 'Theme_PATH', get_template_directory() . DIRECTORY_SEPARATOR );
define( 'Theme_ASSET_URL', Theme_URL . 'assets' . DIRECTORY_SEPARATOR );
define( 'BASEURL', site_url() );

/**
 * آدرس مطلق برای فایل زیر پوشهٔ assets تم (تصویر، فونت و غیره).
 */
function ez_theme_asset_uri( string $relative_path ): string {
	$relative_path = str_replace( '\\', '/', $relative_path );
	return esc_url( Theme_URL . 'assets/' . ltrim( $relative_path, '/' ) );
}

require_once Theme_PATH . 'inc/bootstrap.php';
