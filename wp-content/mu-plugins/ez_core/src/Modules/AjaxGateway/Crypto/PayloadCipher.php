<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Crypto;

use EscapeZoom\Core\Infrastructure\Config\SecretsLoader;
use EscapeZoom\Core\Modules\AjaxGateway\Policy\ActionClassification;

/**
 * AES-256-GCM payload envelope (wire body signed as-is).
 */
final class PayloadCipher
{
	public const HEADER = 'v1';

	/**
	 * @throws \RuntimeException
	 */
	public static function encrypt( string $plaintext, string $subSecretBase64Url ): string {
		if ( ! function_exists( 'sodium_crypto_aead_aes256gcm_encrypt' ) ) {
			throw new \RuntimeException( 'AES-GCM not available (ext-sodium).' );
		}

		$key   = self::keyFromSubSecret( $subSecretBase64Url );
		$nonce = random_bytes( SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES );
		$ct    = sodium_crypto_aead_aes256gcm_encrypt( $plaintext, '', $nonce, $key );

		$json = json_encode(
			array(
				'ez_enc' => self::HEADER,
				'iv'     => self::bytesToBase64Url( $nonce ),
				'ct'     => self::bytesToBase64Url( $ct ),
			),
			JSON_UNESCAPED_SLASHES
		);

		if ( ! is_string( $json ) ) {
			throw new \RuntimeException( 'Failed to encode encrypted envelope.' );
		}

		return $json;
	}

	/**
	 * @throws \RuntimeException
	 */
	public static function decrypt( string $wireBody, string $subSecretBase64Url ): string {
		if ( ! self::isEnvelope( $wireBody ) ) {
			return $wireBody;
		}

		if ( ! function_exists( 'sodium_crypto_aead_aes256gcm_decrypt' ) ) {
			throw new \RuntimeException( 'AES-GCM not available (ext-sodium).' );
		}

		/** @var array{ez_enc?: string, iv?: string, ct?: string} $env */
		$env = json_decode( $wireBody, true );
		if ( ! is_array( $env ) || self::HEADER !== ( $env['ez_enc'] ?? '' ) ) {
			throw new \RuntimeException( 'Invalid encrypted envelope.' );
		}

		$key   = self::keyFromSubSecret( $subSecretBase64Url );
		$nonce = self::base64UrlDecode( (string) ( $env['iv'] ?? '' ) );
		$ct    = self::base64UrlDecode( (string) ( $env['ct'] ?? '' ) );

		$plain = sodium_crypto_aead_aes256gcm_decrypt( $ct, '', $nonce, $key );
		if ( false === $plain ) {
			throw new \RuntimeException( 'Payload decryption failed.' );
		}

		return $plain;
	}

	public static function isEnvelope( string $body ): bool {
		if ( '' === $body || '{' !== $body[0] ) {
			return false;
		}

		$env = json_decode( $body, true );

		return is_array( $env ) && self::HEADER === ( $env['ez_enc'] ?? '' );
	}

	public static function encryptionRequiredFor( string $action ): bool {
		if ( ActionClassification::isWrite( $action ) ) {
			return SecretsLoader::payloadEncryptWrites();
		}

		if ( ActionClassification::isRead( $action ) ) {
			return SecretsLoader::payloadEncryptReads();
		}

		return false;
	}

	private static function keyFromSubSecret( string $subSecretBase64Url ): string {
		$key = self::base64UrlDecode( $subSecretBase64Url );
		if ( SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES !== strlen( $key ) ) {
			throw new \RuntimeException( 'Invalid sub-secret key length.' );
		}

		return $key;
	}

	private static function base64UrlDecode( string $value ): string {
		$b64 = strtr( $value, '-_', '+/' );
		$pad = strlen( $b64 ) % 4;
		if ( $pad > 0 ) {
			$b64 .= str_repeat( '=', 4 - $pad );
		}
		$raw = base64_decode( $b64, true );
		if ( false === $raw ) {
			throw new \RuntimeException( 'Invalid base64url value.' );
		}

		return $raw;
	}

	private static function bytesToBase64Url( string $bytes ): string {
		return rtrim( strtr( base64_encode( $bytes ), '+/', '-_' ), '=' );
	}
}
