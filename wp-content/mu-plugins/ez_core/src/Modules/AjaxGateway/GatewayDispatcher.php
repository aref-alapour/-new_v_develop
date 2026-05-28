<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

use EscapeZoom\Core\Modules\AjaxGateway\Auth\NonceStore;
use EscapeZoom\Core\Modules\AjaxGateway\Auth\RateLimiter;
use EscapeZoom\Core\Modules\AjaxGateway\Auth\SignatureVerifier;
use EscapeZoom\Core\Modules\AjaxGateway\Crypto\PayloadCipher;
use EscapeZoom\Core\Modules\AjaxGateway\Exception\GatewayAuthException;
use EscapeZoom\Core\Modules\AjaxGateway\Policy\ActionClassification;
use EscapeZoom\Core\Modules\AjaxGateway\Policy\ActionPolicy;
use EscapeZoom\Core\Modules\Booking\BookingGatewayActions;
use EscapeZoom\Core\Modules\Booking\SansManagementAuthorizationService;
use EZ\Ajax\Auth\SubKey;

/**
 * Shared POST /ajax handler (WordPress router + light gateway).
 */
final class GatewayDispatcher
{
	public static function handle( string $gatewayPath ): void {
		$t0 = microtime( true );
		$tAfterRate = $tAfterAuth = $tAfterCrypto = $tAfterPolicy = $tAfterOwner = 0.0;

		while ( ob_get_level() > 0 ) {
			ob_end_clean();
		}

		if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
			GatewayResponse::json( false, array(), array( 'code' => 'METHOD_NOT_ALLOWED', 'message' => 'POST only' ), 405 );
		}

		if ( ! defined( 'EZ_AJAX_SHARED_SECRET' ) || '' === (string) EZ_AJAX_SHARED_SECRET ) {
			GatewayResponse::json( false, array(), array( 'code' => 'GATEWAY_DISABLED', 'message' => 'Gateway not configured' ), 503 );
		}

		$raw     = file_get_contents( 'php://input' );
		$body    = is_string( $raw ) ? $raw : '';
		$payload = array();
		if ( '' !== $body && ! PayloadCipher::isEnvelope( $body ) ) {
			$json = json_decode( $body, true );
			if ( is_array( $json ) ) {
				$payload = $json;
			}
		}

		$action = '';
		if ( isset( $_GET['action'] ) ) {
			$action = self::sanitizeTextField( self::wp_unslash_if_available( (string) $_GET['action'] ) );
		}
		if ( '' === $action && isset( $payload['action'] ) ) {
			$action = self::sanitizeTextField( (string) $payload['action'] );
		}
		unset( $payload['action'] );

		$headers = self::normalizeHeaders();
		if ( isset( $headers['x-ez-action'] ) && '' !== $headers['x-ez-action'] ) {
			$action = self::sanitizeTextField( $headers['x-ez-action'] );
		}

		BookingGatewayActions::ensureRegistered( $action );

		$kid        = $headers['x-ez-kid'] ?? 'v1';
		$clientId   = $headers['x-ez-client-id'] ?? '';
		$clientKind = $headers['x-ez-client-kind'] ?? 'web-anon';
		$expires    = isset( $headers['x-ez-sub-expires'] ) ? (int) $headers['x-ez-sub-expires'] : 0;
		$subSecret  = SubKey::deriveBase64Url( (string) EZ_AJAX_SHARED_SECRET, $kid, $clientId, $expires );
		$headers['x-ez-sub-secret'] = $subSecret;

		if ( '' === $action || ! ActionRegistry::has( $action ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'UNKNOWN_ACTION', 'message' => 'Unknown action' ), 404 );
		}

		$rate = RateLimiter::check( $action, $clientId );
		if ( $rate['limited'] ) {
			self::logRateLimit( $action );
			GatewayResponse::json(
				false,
				array(),
				array( 'code' => 'RATE_LIMITED', 'message' => 'Too many requests' ),
				429,
				array( 'Retry-After' => (string) $rate['retry_after'] )
			);
		}
		$tAfterRate = microtime( true );

		$verifyErr = SignatureVerifier::verify( 'POST', $gatewayPath, $action, $body, $headers );
		if ( null !== $verifyErr ) {
			GatewayResponse::json( false, array(), array( 'code' => $verifyErr, 'message' => 'Unauthorized' ), 401 );
		}

		$nonce = $headers['x-ez-nonce'] ?? '';
		if ( ! NonceStore::consume( $clientId, $nonce ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'REPLAY', 'message' => 'Nonce already used' ), 401 );
		}
		$tAfterAuth = microtime( true );

		if ( PayloadCipher::encryptionRequiredFor( $action ) ) {
			if ( ! PayloadCipher::isEnvelope( $body ) ) {
				GatewayResponse::json( false, array(), array( 'code' => 'ENCRYPTION_REQUIRED', 'message' => 'Encrypted body required' ), 400 );
			}
			try {
				$plain   = PayloadCipher::decrypt( $body, $subSecret );
				$decoded = json_decode( $plain, true );
				$payload = is_array( $decoded ) ? $decoded : array();
			} catch ( \Throwable $e ) {
				GatewayResponse::json( false, array(), array( 'code' => 'BAD_PAYLOAD', 'message' => 'Decrypt failed' ), 400 );
			}
		} elseif ( PayloadCipher::isEnvelope( $body ) ) {
			try {
				$plain   = PayloadCipher::decrypt( $body, $subSecret );
				$decoded = json_decode( $plain, true );
				$payload = is_array( $decoded ) ? $decoded : array();
			} catch ( \Throwable $e ) {
				GatewayResponse::json( false, array(), array( 'code' => 'BAD_PAYLOAD', 'message' => 'Decrypt failed' ), 400 );
			}
		}
		$tAfterCrypto = microtime( true );

		$policyErr = ActionPolicy::authorize( $action, $clientKind );
		if ( null !== $policyErr ) {
			GatewayResponse::json(
				false,
				array(),
				array( 'code' => $policyErr, 'message' => 'Forbidden' ),
				403
			);
		}
		$tAfterPolicy = microtime( true );

		if ( ActionClassification::requiresSansPanelAuth( $action ) ) {
			try {
				if ( 'booking.game_search' === $action ) {
					SansManagementAuthorizationService::assertTeamSansToolsAccess( $clientKind );
				} else {
					$productId = isset( $payload['product_id'] ) ? (int) $payload['product_id'] : 0;
					SansManagementAuthorizationService::assertCanManageProduct( $productId, $clientKind );
				}
			} catch ( GatewayAuthException $e ) {
				GatewayResponse::json(
					false,
					array(),
					array( 'code' => $e->errorCode(), 'message' => $e->getMessage() ),
					403
				);
			}
		}
		$tAfterOwner = microtime( true );

		$GLOBALS['ez_gateway_crypto'] = array(
			'action'     => $action,
			'sub_secret' => $subSecret,
		);
		GatewayResponse::setCryptoContext( $action, $subSecret );

		if ( ! headers_sent() ) {
			header( 'X-EZ-Gateway-Phase-Rate-Ms: ' . (string) (int) round( ( $tAfterRate - $t0 ) * 1000 ) );
			header( 'X-EZ-Gateway-Phase-Auth-Ms: ' . (string) (int) round( ( $tAfterAuth - $tAfterRate ) * 1000 ) );
			header( 'X-EZ-Gateway-Phase-Crypto-Ms: ' . (string) (int) round( ( $tAfterCrypto - $tAfterAuth ) * 1000 ) );
			header( 'X-EZ-Gateway-Phase-Policy-Ms: ' . (string) (int) round( ( $tAfterPolicy - $tAfterCrypto ) * 1000 ) );
			header( 'X-EZ-Gateway-Phase-Owner-Ms: ' . (string) (int) round( ( $tAfterOwner - $tAfterPolicy ) * 1000 ) );
			header( 'X-EZ-Gateway-PreDispatch-Ms: ' . (string) (int) round( ( $tAfterOwner - $t0 ) * 1000 ) );
		}

		ob_start();
		ActionRegistry::dispatch( $action, $payload );
		$stray = ob_get_clean();
		if ( is_string( $stray ) && '' !== $stray && defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log(
				'[EZ Gateway] stray output during dispatch action=' . $action . ': '
				. substr( preg_replace( '/\s+/', ' ', $stray ), 0, 200 )
			);
		}

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
			$out['x-ez-action'] = self::sanitizeTextField( self::wp_unslash_if_available( (string) $_SERVER['HTTP_X_EZ_ACTION'] ) );
		}

		return $out;
	}

	private static function sanitizeTextField( string $str ): string {
		if ( function_exists( 'sanitize_text_field' ) ) {
			return sanitize_text_field( $str );
		}

		return trim( strip_tags( $str ) );
	}

	private static function logRateLimit( string $action ): void {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			return;
		}
		$ipHash = hash( 'sha256', RateLimiter::clientIp() );
		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( '[EZ Gateway] rate_limit ip_hash=' . substr( $ipHash, 0, 12 ) . ' action=' . $action );
	}

	private static function wp_unslash_if_available( string $str ): string {
		if ( function_exists( 'wp_unslash' ) ) {
			return (string) wp_unslash( $str );
		}

		return stripslashes( $str );
	}
}
