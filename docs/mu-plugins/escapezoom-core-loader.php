<?php
/**
 * Plugin Name: EscapeZoom Core Loader Bridge
 * Description: Ensures EscapeZoom Core MU plugin is loaded from subdirectory.
 */

if (!defined('ABSPATH')) {
    exit;
}

$coreLoader = __DIR__ . '/escapezoom-core/escapezoom-core.php';
if (is_file($coreLoader)) {
    require_once $coreLoader;
}
