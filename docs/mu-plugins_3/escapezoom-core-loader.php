<?php

/**
 * Loader for EscapeZoom Core MU-Plugin.
 * WordPress only auto-loads files directly in mu-plugins/; this file loads the core plugin.
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/escapezoom-core/escapezoom-core.php';
