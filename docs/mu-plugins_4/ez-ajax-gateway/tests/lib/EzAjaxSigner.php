<?php

declare(strict_types=1);

/**
 * @deprecated Use EscapeZoom\Core\Tests\Support\EzAjaxSigner via ez_core/tests/Support/.
 */
require_once dirname( __DIR__, 3 ) . '/ez_core/tests/Support/EzAjaxSigner.php';

class_alias( \EscapeZoom\Core\Tests\Support\EzAjaxSigner::class, 'EzAjaxSigner' );
