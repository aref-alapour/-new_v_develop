<?php
/**
 * Deprecated: forwards to ez-ajax-standalone.php (all POST /ajax actions).
 *
 * Prefer .htaccess rule in docs/project/ops/apache-ajax-light.conf routing directly
 * to wp-content/mu-plugins/ez_core/ez-ajax-standalone.php.
 */
declare(strict_types=1);

require __DIR__ . '/wp-content/mu-plugins/ez_core/ez-ajax-standalone.php';
