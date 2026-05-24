<?php

declare(strict_types=1);

/**
 * @deprecated Use EscapeZoom\Core\Tests\Support\GatewayHttpClient via ez_core/tests/Support/.
 */
require_once dirname( __DIR__, 3 ) . '/ez_core/tests/Support/GatewayHttpClient.php';

class_alias( \EscapeZoom\Core\Tests\Support\GatewayHttpClient::class, 'GatewayHttpClient' );
