<?php
/**
 * Plugin Name: EZ AJAX Gateway
 * Description: Central gateway for signed AJAX requests (POST /ajax → dispatch.php). Phase 0+1.
 * Version: 0.1.0
 *
 * Auto-load shim: WordPress only auto-loads files directly inside mu-plugins/.
 * The actual bootstrap (autoload, registry, hooks) lives in the matching folder.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/ez-ajax-gateway/bootstrap.php';
