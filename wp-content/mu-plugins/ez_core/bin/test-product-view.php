<?php
declare(strict_types=1);

define( 'EZ_AJAX_LIGHT_GATEWAY', true );
define( 'EZ_CORE_PATH', dirname( __DIR__ ) );
require EZ_CORE_PATH . '/vendor/autoload.php';
require EZ_CORE_PATH . '/bootstrap/load-secrets.php';

use EscapeZoom\Core\Core\Bootstrap;
use EscapeZoom\Core\Modules\Booking\Services\ProductViewService;

Bootstrap::bootMinimal( 'booking.product_set_view' );

try {
	$result = ProductViewService::record( 5104, '10.0.0.1' );
	echo 'record=' . ( $result ? 'true' : 'false' ) . PHP_EOL;
} catch ( Throwable $e ) {
	echo 'ERR: ' . $e->getMessage() . PHP_EOL;
	echo $e->getTraceAsString() . PHP_EOL;
}
