<?php
/**
 * Example: place these defines in wp-config.php (gitignored values).
 *
 *  - EZ_AJAX_SHARED_SECRET   Master HMAC key. 32+ random bytes. Generate with:
 *                              openssl rand -base64 48
 *  - EZ_AJAX_NONCE_TTL        Per-nonce lifetime in seconds (60 is plenty — the timestamp window
 *                              is the primary defense; nonce just blocks replay within that window).
 *  - EZ_AJAX_TIMESTAMP_SKEW   Acceptable clock drift in seconds (30 = ±30).
 *  - EZ_AJAX_SUB_SECRET_TTL   Per-page sub-secret lifetime; rotate from JS via auth.bootstrap (phase 2).
 *  - EZ_AJAX_SUB_SECRET_TTL_MAX Optional ceiling (seconds) for theme-extended TTLs (default 86400).
 *  - EZ_BRANDS_USE_GATEWAY    Phase-1 flag — when true, /brands/ pagination posts to /ajax.
 *  - EZ_AJAX_REQUIRE_HTTPS    (optional) Force HTTPS even in dev. Defaults to "yes when WP_DEBUG=false".
 */

return;

define( 'EZ_AJAX_SHARED_SECRET', 'REPLACE-WITH-openssl-rand-base64-48-OUTPUT' );
define( 'EZ_AJAX_NONCE_TTL', 60 );
define( 'EZ_AJAX_TIMESTAMP_SKEW', 30 );
define( 'EZ_AJAX_SUB_SECRET_TTL', 900 );
// define( 'EZ_AJAX_SUB_SECRET_TTL_MAX', 86400 );
define( 'EZ_BRANDS_USE_GATEWAY', false );
// define( 'EZ_AJAX_REQUIRE_HTTPS', true );
