<?php
/**
 * @deprecated Use bootstrap/load-secrets.php. Thin wrapper for backward compatibility.
 */
declare(strict_types=1);

if ( ! defined( 'EZ_CORE_PATH' ) ) {
	define( 'EZ_CORE_PATH', dirname( __DIR__ ) );
}

require dirname( __DIR__ ) . '/bootstrap/load-secrets.php';
