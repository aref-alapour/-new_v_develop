<?php
/**
 * Full automated test suite for EZ AJAX Gateway (preflight, store, HTTP smoke, parity).
 *
 * Usage:
 *   php wp-content/mu-plugins/ez-ajax-gateway/tests/run-full-gateway-tests.php
 *   php .../run-full-gateway-tests.php --skip-http
 *   php .../run-full-gateway-tests.php --base-url=http://wo.escapezoom.local/ajax --verbose
 *   php .../run-full-gateway-tests.php --ignore-php-platform   # WSL/host PHP 8.4 only (warn)
 */

declare( strict_types=1 );

const EZ_GATEWAY_TEST_EZ_CORE_MIN_PHP = '8.5.0';

if ( PHP_SAPI !== 'cli' ) {
	fwrite( STDERR, "run-full-gateway-tests.php is CLI-only.\n" );
	exit( 2 );
}

require_once dirname( __DIR__, 2 ) . '/ez_core/tests/Support/EzAjaxSigner.php';
require_once dirname( __DIR__, 2 ) . '/ez_core/tests/Support/GatewayHttpClient.php';
require_once __DIR__ . '/lib/TestReporter.php';

use EscapeZoom\Core\Tests\Support\EzAjaxSigner;
use EscapeZoom\Core\Tests\Support\GatewayHttpClient;

ini_set( 'default_socket_timeout', '8' );

/**
 * @return array<string, mixed>
 */
function ez_gateway_test_parse_args( array $argv ): array {
	$out = [
		'base-url'       => 'http://wo.escapezoom.local/ajax',
		'skip-http'      => false,
		'skip-rate-store' => false,
		'json-only'            => false,
		'verbose'              => false,
		'ignore-php-platform'  => false,
		'connect-timeout'      => 3,
		'http-timeout'         => 8,
	];
	foreach ( $argv as $arg ) {
		if ( ! str_starts_with( $arg, '--' ) ) {
			continue;
		}
		$eq = strpos( $arg, '=' );
		if ( false !== $eq ) {
			$key           = substr( $arg, 2, $eq - 2 );
			$out[ $key ] = substr( $arg, $eq + 1 );
		} else {
			$out[ substr( $arg, 2 ) ] = true;
		}
	}

	return $out;
}

function ez_gateway_test_note( string $message, bool $json_only ): void
{
	if ( $json_only ) {
		return;
	}
	fwrite( STDERR, '[ez-gateway-test] ' . $message . PHP_EOL );
}

function ez_gateway_test_php_meets_ez_core( string $min = EZ_GATEWAY_TEST_EZ_CORE_MIN_PHP ): bool
{
	return version_compare( PHP_VERSION, $min, '>=' );
}

/**
 * @return array{host:string, port:int}
 */
function ez_gateway_test_parse_db_endpoint(): array
{
	$host = defined( 'DB_HOST' ) ? (string) DB_HOST : '127.0.0.1';
	$port = 3306;
	if ( str_contains( $host, ':' ) ) {
		[ $host, $port_str ] = explode( ':', $host, 2 );
		$port = (int) $port_str;
	} elseif ( str_contains( $host, '/' ) ) {
		$host = explode( '/', $host, 2 )[0];
	}

	return [
		'host' => $host,
		'port' => $port > 0 ? $port : 3306,
	];
}

function ez_gateway_test_db_host_reachable( float $timeout_seconds ): bool
{
	if ( ! defined( 'DB_HOST' ) ) {
		return false;
	}

	$endpoint = ez_gateway_test_parse_db_endpoint();
	$errno    = 0;
	$errstr   = '';
	$fp       = @fsockopen( $endpoint['host'], $endpoint['port'], $errno, $errstr, $timeout_seconds );

	if ( is_resource( $fp ) ) {
		fclose( $fp );

		return true;
	}

	return false;
}

function ez_gateway_test_http_base_reachable( string $base_url, float $timeout_seconds ): bool
{
	$parts = parse_url( $base_url );
	if ( ! is_array( $parts ) ) {
		return false;
	}
	$scheme = $parts['scheme'] ?? 'http';
	$host   = $parts['host'] ?? '';
	$port   = (int) ( $parts['port'] ?? ( 'https' === $scheme ? 443 : 80 ) );
	if ( '' === $host ) {
		return false;
	}

	$target = ( 'https' === $scheme ? 'ssl://' : '' ) . $host;
	$fp     = @fsockopen( $target, $port, $errno, $errstr, $timeout_seconds );

	if ( is_resource( $fp ) ) {
		fclose( $fp );

		return true;
	}

	return false;
}

function ez_gateway_test_load_composer_bypass_platform_check( string $autoload_path ): void
{
	$vendor_dir = dirname( $autoload_path );
	require_once $vendor_dir . '/composer/ClassLoader.php';
	require_once $vendor_dir . '/composer/autoload_static.php';

	$static_src = (string) file_get_contents( $vendor_dir . '/composer/autoload_static.php' );
	if ( ! preg_match( '/class (ComposerStaticInit[a-f0-9]+)/', $static_src, $m ) ) {
		throw new RuntimeException( 'Could not detect Composer static init class name.' );
	}

	/** @var class-string $static_class */
	$static_class = '\\Composer\\Autoload\\' . $m[1];
	$loader       = new \Composer\Autoload\ClassLoader( $vendor_dir );
	call_user_func( $static_class::getInitializer( $loader ) );
	$loader->register( true );

	/** @var array<string, string> $files */
	$files = require $vendor_dir . '/composer/autoload_files.php';
	foreach ( $files as $file ) {
		require $file;
	}
}

function ez_gateway_test_require_ez_core_autoload(
	string $autoload_path,
	TestReporter $reporter,
	bool $ignore_platform
): bool {
	if ( ! is_readable( $autoload_path ) ) {
		$reporter->fail( 'ez_core_autoload', 'Missing ' . $autoload_path, 'preflight' );

		return false;
	}

	$reporter->pass( 'ez_core_autoload', 'ez_core vendor autoload found', 'preflight' );

	$meets_php = ez_gateway_test_php_meets_ez_core();
	if ( $meets_php ) {
		$reporter->pass(
			'php_version',
			'PHP ' . PHP_VERSION . ' meets ez_core requirement',
			'preflight'
		);
	} elseif ( $ignore_platform ) {
		$reporter->warn(
			'php_version',
			'PHP ' . PHP_VERSION . ' < ' . EZ_GATEWAY_TEST_EZ_CORE_MIN_PHP
				. ' — bypassing Composer platform_check (dev/WSL only; prefer Docker PHP 8.5+)',
			'preflight'
		);
	} else {
		$reporter->fail(
			'php_version',
			'PHP ' . PHP_VERSION . ' is below ez_core requirement '
				. EZ_GATEWAY_TEST_EZ_CORE_MIN_PHP . '. Use Docker PHP 8.5+ or pass --ignore-php-platform',
			'preflight'
		);

		return false;
	}

	try {
		if ( $meets_php ) {
			require_once $autoload_path;
		} else {
			ez_gateway_test_load_composer_bypass_platform_check( $autoload_path );
		}

		return true;
	} catch ( Throwable $e ) {
		$reporter->fail( 'ez_core_autoload', 'Autoload failed: ' . $e->getMessage(), 'preflight' );

		return false;
	}
}

function ez_gateway_test_load_secret_from_config( string $wp_config ): string {
	if ( ! is_readable( $wp_config ) ) {
		return '';
	}
	$contents = (string) file_get_contents( $wp_config );
	if ( preg_match(
		'/define\s*\(\s*[\'"]EZ_AJAX_SHARED_SECRET[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)\s*;/',
		$contents,
		$m
	) ) {
		return $m[1];
	}

	return '';
}

/**
 * @return array{ok:bool, code:?string}
 */
function ez_gateway_test_json_error_code( string $body ): array {
	$data = json_decode( $body, true );
	if ( ! is_array( $data ) ) {
		return [ 'ok' => false, 'code' => null ];
	}
	if ( ! empty( $data['ok'] ) ) {
		return [ 'ok' => true, 'code' => null ];
	}
	$code = $data['error']['code'] ?? null;

	return [
		'ok'   => false,
		'code' => is_string( $code ) ? $code : null,
	];
}

/**
 * @param array<string, string> $headers
 */
function ez_gateway_test_assert_fragment_structure( TestReporter $reporter, string $html, string $group ): void {
	if ( ! str_contains( $html, 'id="brands-directory-swap"' ) ) {
		$reporter->fail( 'parity_swap_root', 'Missing #brands-directory-swap', $group );

		return;
	}
	$reporter->pass( 'parity_swap_root', 'Found #brands-directory-swap', $group );

	if ( str_contains( $html, 'class="relative"' ) || str_contains( $html, "class='relative'" ) ) {
		$reporter->pass( 'parity_swap_relative', 'Found relative wrapper class', $group );
	} else {
		$reporter->warn( 'parity_swap_relative', 'No class="relative" on swap root (error/taxonomy states omit it)', $group );
	}

	if ( str_contains( $html, 'grid grid-cols-2' ) && str_contains( $html, '2xl:grid-cols-8' ) ) {
		$reporter->pass( 'parity_grid', 'Grid classes present', $group );
	} else {
		$reporter->warn( 'parity_grid', 'Grid classes missing (empty taxonomy page?)', $group );
	}

	$has_pagination_url = str_contains( $html, '/ajax?action=brands.fragment&page=' )
		|| str_contains( $html, '/ajax?action=brands.fragment&amp;page=' );
	if ( $has_pagination_url && str_contains( $html, 'hx-indicator="#ez-brands-htmx-skeleton"' ) ) {
		$reporter->pass( 'parity_pagination', 'Pagination hx-get + indicator present', $group );
	} else {
		$reporter->warn( 'parity_pagination', 'Pagination markers missing (single page?)', $group );
	}

	if ( str_contains( $html, 'aria-label="صفحه‌بندی برندها"' ) ) {
		$reporter->pass( 'parity_persian_nav', 'Persian pagination aria-label present', $group );
	} else {
		$reporter->warn( 'parity_persian_nav', 'Persian nav aria-label missing', $group );
	}
}

$args       = ez_gateway_test_parse_args( $argv );
$reporter   = new TestReporter();
$verbose    = ! empty( $args['verbose'] );
$json_only  = ! empty( $args['json-only'] );
$skip_http  = ! empty( $args['skip-http'] );
$skip_rate  = ! empty( $args['skip-rate-store'] );
$ignore_php      = ! empty( $args['ignore-php-platform'] );
$base_url        = is_string( $args['base-url'] ?? null ) ? (string) $args['base-url'] : 'http://wo.escapezoom.local/ajax';
$connect_timeout = max( 1, min( 30, (int) ( $args['connect-timeout'] ?? 3 ) ) );
$http_timeout    = max( 1, min( 60, (int) ( $args['http-timeout'] ?? 8 ) ) );

$gateway_dir = dirname( __DIR__ );
$wp_root     = dirname( $gateway_dir, 3 );
$wp_config   = $wp_root . '/wp-config.php';
$secret      = '';
$db_reachable = false;

ez_gateway_test_note( 'Starting preflight…', $json_only );

// --- Group 1: Preflight ---
try {
	require_once $gateway_dir . '/secrets-bootstrap.php';
	if ( is_readable( $wp_config ) ) {
		ez_ajax_gateway_secrets_bootstrap( $wp_config );
		$reporter->pass( 'config_readable', 'Loaded secrets from ' . $wp_config, 'preflight' );
	} else {
		$reporter->fail( 'config_readable', 'wp-config.php not readable at ' . $wp_config, 'preflight' );
	}
} catch ( Throwable $e ) {
	$reporter->fail( 'config_readable', 'secrets-bootstrap failed: ' . $e->getMessage(), 'preflight' );
}

if ( defined( 'EZ_AJAX_SHARED_SECRET' ) && is_string( EZ_AJAX_SHARED_SECRET ) ) {
	$secret = EZ_AJAX_SHARED_SECRET;
	if ( strlen( $secret ) >= 32 ) {
		$reporter->pass( 'secret_defined', 'EZ_AJAX_SHARED_SECRET length OK', 'preflight' );
	} else {
		$reporter->fail( 'secret_defined', 'Secret shorter than 32 chars', 'preflight' );
	}
	if ( str_starts_with( $secret, 'CHANGE-ME' ) ) {
		$is_dev = defined( 'WP_DEBUG' ) && WP_DEBUG;
		if ( $is_dev ) {
			$reporter->warn(
				'secret_not_placeholder',
				'Placeholder secret (allowed in WP_DEBUG dev)',
				'preflight'
			);
		} else {
			$reporter->fail(
				'secret_not_placeholder',
				'Placeholder secret not allowed when WP_DEBUG is false',
				'preflight'
			);
		}
	} else {
		$reporter->pass( 'secret_not_placeholder', 'Secret is not CHANGE-ME placeholder', 'preflight' );
	}
} else {
	$reporter->fail( 'secret_defined', 'EZ_AJAX_SHARED_SECRET not defined after bootstrap', 'preflight' );
	$secret = ez_gateway_test_load_secret_from_config( $wp_config );
}

$db_ok = defined( 'DB_NAME' ) && defined( 'DB_USER' ) && defined( 'DB_PASSWORD' ) && defined( 'DB_HOST' );
if ( $db_ok ) {
	$reporter->pass( 'db_constants', 'DB_* constants defined', 'preflight' );
} else {
	$reporter->fail( 'db_constants', 'Missing DB_* constants', 'preflight' );
}

$ez_autoload = $wp_root . '/wp-content/mu-plugins/ez_core/vendor/autoload.php';
ez_gateway_test_note( 'Loading ez_core autoload…', $json_only );
$ez_autoloaded = ez_gateway_test_require_ez_core_autoload( $ez_autoload, $reporter, $ignore_php );
ez_gateway_test_note( $ez_autoloaded ? 'Autoload OK' : 'Autoload failed', $json_only );

if ( ! extension_loaded( 'pdo_mysql' ) ) {
	$reporter->fail(
		'pdo_mysql_extension',
		'pdo_mysql extension not loaded — run tests inside Docker PHP or enable the extension',
		'preflight'
	);
}

if ( $db_ok ) {
	$endpoint = ez_gateway_test_parse_db_endpoint();
	ez_gateway_test_note(
		'Probing DB ' . $endpoint['host'] . ':' . $endpoint['port'] . ' (timeout ' . $connect_timeout . 's)…',
		$json_only
	);
	$db_reachable = ez_gateway_test_db_host_reachable( (float) $connect_timeout );
	if ( $db_reachable ) {
		$reporter->pass(
			'db_tcp_reachable',
			'TCP connect OK to ' . $endpoint['host'] . ':' . $endpoint['port'],
			'preflight'
		);
	} else {
		$reporter->fail(
			'db_tcp_reachable',
			'Cannot reach DB at ' . $endpoint['host'] . ':' . $endpoint['port']
				. ' from this host (Docker service name?). Run: docker compose exec wordpress php …/run-full-gateway-tests.php',
			'preflight'
		);
	}
}

if ( $ez_autoloaded && $db_reachable && class_exists( \EscapeZoom\Core\Core\Bootstrap::class ) ) {
	try {
		ez_gateway_test_note( 'Booting Capsule…', $json_only );
		\EscapeZoom\Core\Core\Bootstrap::bootDataLayerOnly();
		if ( \EscapeZoom\Core\Database\CapsuleBoot::isBooted() ) {
			$reporter->pass( 'capsule_boot', 'Capsule data layer booted', 'preflight' );
		} else {
			$reporter->fail( 'capsule_boot', 'CapsuleBoot::isBooted() false after boot', 'preflight' );
		}
	} catch ( Throwable $e ) {
		$reporter->fail( 'capsule_boot', 'Boot failed: ' . $e->getMessage(), 'preflight' );
	}
} elseif ( $ez_autoloaded && ! $db_reachable ) {
	$reporter->fail( 'capsule_boot', 'Skipped — DB not reachable from CLI', 'preflight' );
} elseif ( $ez_autoloaded ) {
	$reporter->fail( 'capsule_boot', 'EscapeZoom Bootstrap class missing', 'preflight' );
} else {
	$reporter->fail( 'capsule_boot', 'Skipped — ez_core autoload not loaded', 'preflight' );
}

if ( defined( 'EZ_BRANDS_USE_GATEWAY' ) && EZ_BRANDS_USE_GATEWAY ) {
	$reporter->info( 'brands_gateway_flag', 'EZ_BRANDS_USE_GATEWAY is true', 'preflight' );
} else {
	$reporter->info(
		'brands_gateway_flag',
		'EZ_BRANDS_USE_GATEWAY is false (browser still uses theme HTMX path)',
		'preflight'
	);
}

if ( $ez_autoloaded ) {
	$prefix     = \EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema::prefix();
	$nonces_tbl = \EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema::noncesTable();
	$rate_tbl   = \EscapeZoom\Core\Modules\AjaxGateway\AjaxGatewaySchema::rateTable();
} else {
	$prefix     = defined( 'EZ_AJAX_TABLE_PREFIX' ) ? (string) EZ_AJAX_TABLE_PREFIX : 'wp_';
	$nonces_tbl = $prefix . 'ez_ajax_nonces';
	$rate_tbl   = $prefix . 'ez_ajax_rate';
}

if ( $ez_autoloaded && $db_reachable && \EscapeZoom\Core\Database\CapsuleBoot::isBooted() && extension_loaded( 'pdo_mysql' ) ) {
	try {
		$schema     = \Illuminate\Database\Capsule\Manager::connection(
			\EscapeZoom\Core\Database\CapsuleBoot::CONNECTION_WP
		)->getSchemaBuilder();
		$has_nonces = $schema->hasTable( $nonces_tbl );
		$has_rate   = $schema->hasTable( $rate_tbl );
		if ( $has_nonces ) {
			$reporter->pass( 'ddl_nonces_table', 'Table exists: ' . $nonces_tbl, 'preflight' );
		} else {
			$reporter->fail(
				'ddl_nonces_table',
				'Missing table ' . $nonces_tbl . ' — run ajax DDL in ez_bootstrap_custom_tables.sql',
				'preflight'
			);
		}
		if ( $has_rate ) {
			$reporter->pass( 'ddl_rate_table', 'Table exists: ' . $rate_tbl, 'preflight' );
		} else {
			$reporter->fail(
				'ddl_rate_table',
				'Missing table ' . $rate_tbl . ' — run ajax DDL in ez_bootstrap_custom_tables.sql',
				'preflight'
			);
		}
	} catch ( Throwable $e ) {
		$reporter->fail(
			'ddl_connection',
			'DB unreachable from CLI: ' . $e->getMessage() . ' — run inside Docker?',
			'preflight'
		);
	}
}

// --- Group 2: In-process store ---
if ( $ez_autoloaded && $db_reachable && '' !== $secret && \EscapeZoom\Core\Database\CapsuleBoot::isBooted() && extension_loaded( 'pdo_mysql' ) ) {
	try {
		$nonce_repo = new \EscapeZoom\Core\Modules\AjaxGateway\Repositories\EzAjaxNonceRepository();
		$test_nonce = bin2hex( random_bytes( 16 ) );
		if ( $nonce_repo->useOnce( $test_nonce, 60 ) ) {
			$reporter->pass( 'nonce_use_once', 'First useOnce returned true', 'store' );
		} else {
			$reporter->fail( 'nonce_use_once', 'First useOnce returned false', 'store' );
		}
		if ( ! $nonce_repo->useOnce( $test_nonce, 60 ) ) {
			$reporter->pass( 'nonce_replay_repo', 'Replay useOnce returned false', 'store' );
		} else {
			$reporter->fail( 'nonce_replay_repo', 'Replay useOnce should return false', 'store' );
		}

		if ( ! $skip_rate ) {
			$rate_repo = new \EscapeZoom\Core\Modules\AjaxGateway\Repositories\EzAjaxRateLimiterRepository();
			$bucket    = 'ez-test:' . bin2hex( random_bytes( 8 ) );
			$all_ok    = true;
			for ( $i = 0; $i < 5; $i++ ) {
				if ( ! $rate_repo->consume( $bucket, 5, 60 ) ) {
					$all_ok = false;
					break;
				}
			}
			if ( $all_ok ) {
				$reporter->pass( 'rate_consume_ok', '5/5 consumes succeeded', 'store' );
			} else {
				$reporter->fail( 'rate_consume_ok', 'Expected 5 successful consumes', 'store' );
			}
			if ( ! $rate_repo->consume( $bucket, 5, 60 ) ) {
				$reporter->pass( 'rate_exhausted', '6th consume correctly rejected', 'store' );
			} else {
				$reporter->fail( 'rate_exhausted', '6th consume should return false', 'store' );
			}
		}
	} catch ( Throwable $e ) {
		$reporter->fail( 'store_connection', 'Store tests failed: ' . $e->getMessage(), 'store' );
	}
}

// --- Group 3: HTTP smoke ---
$http_client = new GatewayHttpClient();
if ( $skip_http ) {
	$reporter->info( 'http_skipped', 'HTTP tests skipped (--skip-http)', 'http' );
} elseif ( '' === $secret ) {
	$reporter->fail( 'http_skipped', 'No secret available for HTTP tests', 'http' );
} elseif ( ! ez_gateway_test_http_base_reachable( $base_url, (float) $connect_timeout ) ) {
	$reporter->fail(
		'http_unreachable',
		'Cannot reach ' . $base_url . ' from this host — start Docker / check hosts file',
		'http'
	);
} else {
	ez_gateway_test_note( 'Running HTTP smoke tests…', $json_only );
	$ping_signer = ( new EzAjaxSigner( 'ping', 'POST', $base_url, '', $secret ) )->sign();
	$ping_resp   = $http_client->post( $base_url, '', $ping_signer->headers, $http_timeout );
	if ( null !== $ping_resp['error'] ) {
		$reporter->fail(
			'http_ping',
			'Server unreachable: ' . $ping_resp['error'] . ' — is Docker/up at ' . $base_url . '?',
			'http'
		);
	} else {
		$parsed = ez_gateway_test_json_error_code( $ping_resp['body'] );
		$data   = json_decode( $ping_resp['body'], true );
		if ( 200 === $ping_resp['status'] && $parsed['ok'] && ! empty( $data['data']['pong'] ) ) {
			$reporter->pass( 'http_ping', 'HTTP 200 pong=true', 'http' );
		} else {
			$msg = 'HTTP ' . $ping_resp['status'];
			if ( $verbose ) {
				$msg .= ' body=' . substr( $ping_resp['body'], 0, 200 );
			}
			$reporter->fail( 'http_ping', $msg, 'http' );
		}

		$count_signer = ( new EzAjaxSigner( 'brands.count', 'POST', $base_url, '', $secret ) )->sign();
		$count_resp   = $http_client->post( $base_url, '', $count_signer->headers, $http_timeout );
		if ( null !== $count_resp['error'] ) {
			$reporter->fail( 'http_brands_count', $count_resp['error'], 'http' );
		} else {
			$data = json_decode( $count_resp['body'], true );
			$ok   = 200 === $count_resp['status']
				&& is_array( $data )
				&& ! empty( $data['ok'] )
				&& isset( $data['data']['count'] )
				&& array_key_exists( 'wp_loaded', $data['data'] )
				&& false === $data['data']['wp_loaded'];
			if ( $ok ) {
				$reporter->pass(
					'http_brands_count',
					'count=' . (int) $data['data']['count'] . ', wp_loaded=false',
					'http'
				);
			} else {
				$reporter->fail( 'http_brands_count', 'HTTP ' . $count_resp['status'], 'http' );
			}
		}

		$frag_body    = '{"page":1}';
		$frag_signer  = ( new EzAjaxSigner( 'brands.fragment', 'POST', $base_url, $frag_body, $secret ) )->sign();
		$frag_resp    = $http_client->post( $base_url, $frag_body, $frag_signer->headers, $http_timeout );
		if ( null !== $frag_resp['error'] ) {
			$reporter->fail( 'http_brands_fragment', $frag_resp['error'], 'http' );
		} else {
			if ( 200 === $frag_resp['status']
				&& str_contains( $frag_resp['body'], 'id="brands-directory-swap"' )
				&& str_contains( $frag_resp['body'], 'grid grid-cols-2' ) ) {
				$reporter->pass( 'http_brands_fragment', 'HTTP 200 HTML fragment OK', 'http' );
			} else {
				$reporter->fail(
					'http_brands_fragment',
					'HTTP ' . $frag_resp['status'] . ' or missing swap/grid markup',
					'http'
				);
			}

			$vary = $frag_resp['headers']['vary'] ?? '';
			if ( str_contains( strtolower( $vary ), 'hx-request' ) ) {
				$reporter->pass( 'http_fragment_headers', 'Vary header includes HX-Request', 'http' );
			} else {
				$reporter->warn(
					'http_fragment_headers',
					'Vary header missing or taxonomy-only response (status ' . $frag_resp['status'] . ')',
					'http'
				);
			}

			ez_gateway_test_assert_fragment_structure( $reporter, $frag_resp['body'], 'http_parity' );

			$replay_resp = $http_client->post( $base_url, $frag_body, $frag_signer->headers, $http_timeout );
			if ( null !== $replay_resp['error'] ) {
				$reporter->fail( 'http_nonce_replay', $replay_resp['error'], 'http' );
			} else {
				$parsed = ez_gateway_test_json_error_code( $replay_resp['body'] );
				if ( 409 === $replay_resp['status'] && 'NONCE_REPLAY' === $parsed['code'] ) {
					$reporter->pass( 'http_nonce_replay', 'HTTP 409 NONCE_REPLAY', 'http' );
				} else {
					$reporter->fail(
						'http_nonce_replay',
						'Expected 409 NONCE_REPLAY, got HTTP ' . $replay_resp['status']
							. ( $parsed['code'] ? ' code=' . $parsed['code'] : '' ),
						'http'
					);
				}
			}
		}

		$bad_signer = ( new EzAjaxSigner( 'ping', 'POST', $base_url, '', $secret ) )->sign()->withBadSignature();
		$bad_resp   = $http_client->post( $base_url, '', $bad_signer->headers, $http_timeout );
		if ( null !== $bad_resp['error'] ) {
			$reporter->fail( 'http_bad_signature', $bad_resp['error'], 'http' );
		} else {
			$parsed = ez_gateway_test_json_error_code( $bad_resp['body'] );
			if ( 401 === $bad_resp['status'] && 'BAD_SIGNATURE' === $parsed['code'] ) {
				$reporter->pass( 'http_bad_signature', 'HTTP 401 BAD_SIGNATURE', 'http' );
			} else {
				$reporter->fail(
					'http_bad_signature',
					'Expected 401 BAD_SIGNATURE, got HTTP ' . $bad_resp['status'],
					'http'
				);
			}
		}
	}
}

// --- Group 4: In-process parity ---
if ( ! extension_loaded( 'pdo_mysql' ) ) {
	$reporter->warn(
		'parity_inprocess_service',
		'Skipped — pdo_mysql not available in this PHP CLI',
		'parity'
	);
} elseif ( $ez_autoloaded && $db_reachable && class_exists( \EscapeZoom\Core\Modules\Brands\Services\BrandsDirectoryReadService::class )
	&& \EscapeZoom\Core\Database\CapsuleBoot::isBooted() ) {
	try {
		$result = ( new \EscapeZoom\Core\Modules\Brands\Services\BrandsDirectoryReadService() )
			->buildFragment( 1 );
		if ( 200 === $result->status ) {
			$reporter->pass(
				'parity_inprocess_service',
				'buildFragment(1) status 200, html length ' . strlen( $result->html ),
				'parity'
			);
			ez_gateway_test_assert_fragment_structure( $reporter, $result->html, 'parity' );
		} elseif ( 500 === $result->status ) {
			$reporter->fail(
				'parity_inprocess_service',
				'buildFragment returned 500 — DB unreachable or data layer error',
				'parity'
			);
		} else {
			$reporter->warn(
				'parity_inprocess_service',
				'buildFragment returned status ' . $result->status . ' (taxonomy missing?)',
				'parity'
			);
		}
	} catch ( Throwable $e ) {
		$reporter->fail( 'parity_inprocess_service', $e->getMessage(), 'parity' );
	}
} else {
	$reporter->fail( 'parity_inprocess_service', 'BrandsDirectoryReadService or Capsule unavailable', 'parity' );
}

ez_gateway_test_note( 'Done.', $json_only );
$reporter->printTable( $json_only );
$reporter->printJson();

if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || ! is_readable( $wp_config ) ) {
	exit( 2 );
}

exit( $reporter->hasFailures() ? 1 : 0 );
