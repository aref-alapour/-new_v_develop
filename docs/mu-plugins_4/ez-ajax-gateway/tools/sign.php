<?php
/**
 * CLI helper — prints a fully-formed curl command for a gateway action.
 *
 * Usage:
 *   php sign.php --action=ping
 *   php sign.php --action=brands.fragment --body='{"page":2}' --url=http://wo.escapezoom.local/ajax
 *   php sign.php --action=ping --url=http://wo.escapezoom.local/ajax --kid=v1 --client-kind=web-anon
 *
 * Reads EZ_AJAX_SHARED_SECRET from the wp-config.php two directories up (the project root)
 * — or from --secret=... on the command line.
 *
 * NEVER commit secrets via this tool. The shared secret stays in your shell scrollback;
 * use `history -d N` if needed.
 */

declare( strict_types = 1 );

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "sign.php is CLI-only.\n" );
	exit( 1 );
}

require_once dirname( __DIR__, 2 ) . '/ez_core/tests/Support/EzAjaxSigner.php';

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;

function ez_sign_parse_args( array $argv ): array {
	$out = [];
	foreach ( $argv as $arg ) {
		if ( substr( $arg, 0, 2 ) === '--' ) {
			$eq = strpos( $arg, '=' );
			if ( false !== $eq ) {
				$out[ substr( $arg, 2, $eq - 2 ) ] = substr( $arg, $eq + 1 );
			} else {
				$out[ substr( $arg, 2 ) ] = true;
			}
		}
	}
	return $out;
}

$args = ez_sign_parse_args( $argv );

$action      = (string) ( $args['action'] ?? '' );
$body        = (string) ( $args['body'] ?? '' );
$method      = strtoupper( (string) ( $args['method'] ?? 'POST' ) );
$url         = (string) ( $args['url'] ?? 'http://wo.escapezoom.local/ajax' );
$kid         = (string) ( $args['kid'] ?? 'v1' );
$client_kind = (string) ( $args['client-kind'] ?? 'web-anon' );
$client_id   = (string) ( $args['client-id'] ?? '' );
$ttl         = (int) ( $args['ttl'] ?? 300 );
$secret      = (string) ( $args['secret'] ?? '' );

if ( '' === $action ) {
	fwrite( STDERR, "missing --action=<name>\n" );
	exit( 1 );
}

if ( '' === $secret ) {
	$wp_config = dirname( __DIR__, 4 ) . '/wp-config.php';
	if ( ! is_readable( $wp_config ) ) {
		fwrite( STDERR, "wp-config.php not found at {$wp_config} — pass --secret=... instead.\n" );
		exit( 1 );
	}
	$contents = (string) file_get_contents( $wp_config );
	if ( preg_match( '/define\s*\(\s*[\'"]EZ_AJAX_SHARED_SECRET[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)\s*;/', $contents, $m ) ) {
		$secret = $m[1];
	}
	if ( '' === $secret ) {
		fwrite( STDERR, "could not parse EZ_AJAX_SHARED_SECRET from wp-config.php\n" );
		exit( 1 );
	}
}

if ( str_starts_with( $secret, 'CHANGE-ME' ) ) {
	fwrite( STDERR, "WARNING: EZ_AJAX_SHARED_SECRET is the placeholder. Generate one with `openssl rand -base64 48`.\n" );
}

$signer = ( new EzAjaxSigner(
	$action,
	$method,
	$url,
	$body,
	$secret,
	$kid,
	$client_kind,
	$client_id,
	$ttl
) )->sign();

echo "# canonical: {$signer->canonical}\n";
echo "# signature: {$signer->signature}\n";
echo "# expires_at: {$signer->expires_at} (in {$ttl}s)\n";
echo "\n{$signer->toCurlCommand()}\n";
