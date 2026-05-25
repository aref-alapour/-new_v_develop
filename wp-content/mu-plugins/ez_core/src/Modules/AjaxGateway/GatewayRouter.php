<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

use EscapeZoom\Core\Modules\AjaxGateway\Auth\NonceStore;
use EscapeZoom\Core\Modules\AjaxGateway\Auth\SignatureVerifier;
use EZ\Ajax\Auth\SubKey;

/**
 * POST /ajax signed gateway entry.
 */
final class GatewayRouter
{
	public static function registerRewrite(): void {
		add_rewrite_rule( '^ajax/?$', 'index.php?ez_ajax_gateway=1', 'top' );
		add_filter( 'query_vars', static function ( array $vars ): array {
			$vars[] = 'ez_ajax_gateway';
			return $vars;
		} );
	}

	public static function maybeHandle(): void {
		if ( ! get_query_var( 'ez_ajax_gateway' ) ) {
			return;
		}

		if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
			GatewayResponse::json( false, array(), array( 'code' => 'METHOD_NOT_ALLOWED', 'message' => 'POST only' ), 405 );
		}

		if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
			GatewayResponse::json( false, array(), array( 'code' => 'GATEWAY_DISABLED', 'message' => 'Gateway not configured' ), 503 );
		}

		$raw   = file_get_contents( 'php://input' );
		$body  = is_string( $raw ) ? $raw : '';
		$json  = json_decode( $body, true );
		$payload = is_array( $json ) ? $json : array();

		$action = '';
		if ( isset( $_GET['action'] ) ) {
			$action = sanitize_text_field( wp_unslash( (string) $_GET['action'] ) );
		}
		if ( '' === $action && isset( $payload['action'] ) ) {
			$action = sanitize_text_field( (string) $payload['action'] );
		}
		unset( $payload['action'] );

		$headers = self::normalizeHeaders();
		$path    = self::gatewayPath();

		$kid         = $headers['x-ez-kid'] ?? 'v1';
		$clientId    = $headers['x-ez-client-id'] ?? '';
		$clientKind  = $headers['x-ez-client-kind'] ?? 'web-anon';
		$expires     = isset( $headers['x-ez-sub-expires'] ) ? (int) $headers['x-ez-sub-expires'] : 0;
		$subSecret   = SubKey::deriveBase64Url( (string) EZ_AJAX_SHARED_SECRET, $kid, $clientId, $expires );
		$headers['x-ez-sub-secret'] = $subSecret;

		$verifyErr = SignatureVerifier::verify( 'POST', $path, $action, $body, $headers );
		if ( null !== $verifyErr ) {
			GatewayResponse::json( false, array(), array( 'code' => $verifyErr, 'message' => 'Unauthorized' ), 401 );
		}

		$nonce = $headers['x-ez-nonce'] ?? '';
		if ( ! NonceStore::consume( $clientId, $nonce ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'REPLAY', 'message' => 'Nonce already used' ), 401 );
		}

		if ( '' === $action || ! ActionRegistry::has( $action ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'UNKNOWN_ACTION', 'message' => 'Unknown action' ), 404 );
		}

		ActionRegistry::dispatch( $action, $payload );
	}

	private static function gatewayPath(): string {
		$home = wp_parse_url( home_url( '/ajax' ), PHP_URL_PATH );
		if ( ! is_string( $home ) || '' === $home ) {
			return '/ajax';
		}
		$home = rtrim( $home, '/' ) ?: '/';

		return $home;
	}

	/**
	 * @return array<string,string>
	 */
	private static function normalizeHeaders(): array {
		$out = array();
		foreach ( $_SERVER as $key => $value ) {
			if ( ! is_string( $key ) || ! str_starts_with( $key, 'HTTP_' ) ) {
				continue;
			}
			$name = strtolower( str_replace( '_', '-', substr( $key, 5 ) ) );
			if ( is_string( $value ) ) {
				$out[ $name ] = $value;
			}
		}
		if ( isset( $_SERVER['HTTP_X_EZ_ACTION'] ) ) {
			$out['x-ez-action'] = sanitize_text_field( wp_unslash( (string) $_SERVER['HTTP_X_EZ_ACTION'] ) );
		}

		return $out;
	}
}
