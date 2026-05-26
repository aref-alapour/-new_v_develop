<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Infrastructure\Config;

/**
 * Decrypt and load ez_core secrets from config/secrets.enc (sodium secretbox).
 */
final class SecretsLoader
{
	private const ENC_FILE = '/config/secrets.enc';

	/** @var array<string, mixed>|null */
	private static ?array $secrets = null;

	private static bool $bootAttempted = false;

	private static ?string $bootError = null;

	public static function boot(): bool {
		if ( self::$bootAttempted ) {
			return null === self::$bootError;
		}

		self::$bootAttempted = true;
		self::$bootError     = null;

		try {
			self::$secrets = self::decryptFile();
		} catch ( \Throwable $e ) {
			self::$bootError = $e->getMessage();
			self::$secrets   = null;
			// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
			error_log( '[EZ Core] Secrets decrypt failed: ' . self::$bootError );
		}

		return null === self::$bootError;
	}

	public static function isLoaded(): bool {
		return is_array( self::$secrets );
	}

	public static function getBootError(): ?string {
		return self::$bootError;
	}

	/**
	 * Dot-path lookup, e.g. external.database.
	 *
	 * @param mixed $default
	 * @return mixed
	 */
	public static function get( string $key, $default = null ) {
		if ( ! self::boot() || ! is_array( self::$secrets ) ) {
			return $default;
		}

		$parts = explode( '.', $key );
		$node  = self::$secrets;
		foreach ( $parts as $part ) {
			if ( ! is_array( $node ) || ! array_key_exists( $part, $node ) ) {
				return $default;
			}
			$node = $node[ $part ];
		}

		return $node;
	}

	/**
	 * @return array{host: string, database: string, username: string, password: string}|null
	 */
	public static function externalDatabase(): ?array {
		if ( ! self::boot() ) {
			return null;
		}

		$host     = (string) self::get( 'external.host', '' );
		$database = (string) self::get( 'external.database', '' );
		$username = (string) self::get( 'external.username', '' );
		$password = (string) self::get( 'external.password', '' );

		if ( '' === $host || '' === $database || '' === $username ) {
			return null;
		}

		return array(
			'host'     => $host,
			'database' => $database,
			'username' => $username,
			'password' => $password,
		);
	}

	public static function ajaxSharedSecret(): string {
		return (string) self::get( 'gateway.ajax_shared_secret', '' );
	}

	public static function bookingUseInternal(): bool {
		$val = self::get( 'gateway.booking_use_internal', true );

		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	public static function bookingNativeSanses(): bool {
		$val = self::get( 'gateway.booking_native_sanses', true );

		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Encrypt plaintext JSON for storage (CLI / tests).
	 */
	public static function encrypt( string $plainJson, string $keyBase64 ): string {
		$key = self::decodeKey( $keyBase64 );
		$nonce      = random_bytes( SODIUM_CRYPTO_SECRETBOX_NONCEBYTES );
		$ciphertext = sodium_crypto_secretbox( $plainJson, $nonce, $key );

		return base64_encode( $nonce . $ciphertext );
	}

	/**
	 * Decrypt blob (tests).
	 */
	public static function decrypt( string $blobBase64, string $keyBase64 ): string {
		$key  = self::decodeKey( $keyBase64 );
		$raw  = base64_decode( $blobBase64, true );
		if ( false === $raw ) {
			throw new \InvalidArgumentException( 'Invalid base64 secrets blob.' );
		}

		return self::openBlob( $raw, $key );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function decryptFile(): array {
		$corePath = defined( 'EZ_CORE_PATH' ) ? EZ_CORE_PATH : dirname( __DIR__, 3 );
		$encPath  = $corePath . self::ENC_FILE;

		if ( ! is_readable( $encPath ) ) {
			throw new \RuntimeException( 'secrets.enc not found at ' . $encPath );
		}

		$keyEnv = getenv( 'EZ_CORE_SECRETS_KEY' );
		if ( ( false === $keyEnv || '' === $keyEnv ) && defined( 'EZ_CORE_SECRETS_KEY' ) ) {
			$keyEnv = (string) EZ_CORE_SECRETS_KEY;
		}
		if ( false === $keyEnv || '' === $keyEnv ) {
			throw new \RuntimeException( 'EZ_CORE_SECRETS_KEY is not set.' );
		}

		$key = self::decodeKey( (string) $keyEnv );
		$raw = base64_decode( (string) file_get_contents( $encPath ), true );
		if ( false === $raw ) {
			throw new \RuntimeException( 'secrets.enc is not valid base64.' );
		}

		$plain = self::openBlob( $raw, $key );
		$data  = json_decode( $plain, true, 512, JSON_THROW_ON_ERROR );
		if ( ! is_array( $data ) ) {
			throw new \RuntimeException( 'secrets payload must be a JSON object.' );
		}

		return $data;
	}

	private static function openBlob( string $raw, string $key ): string {
		$nonceLen = SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;
		if ( strlen( $raw ) < $nonceLen + SODIUM_CRYPTO_SECRETBOX_MACBYTES ) {
			throw new \RuntimeException( 'secrets blob too short.' );
		}

		$nonce      = substr( $raw, 0, $nonceLen );
		$ciphertext = substr( $raw, $nonceLen );
		$plain      = sodium_crypto_secretbox_open( $ciphertext, $nonce, $key );
		if ( false === $plain ) {
			throw new \RuntimeException( 'Invalid key or corrupted secrets.enc.' );
		}

		return $plain;
	}

	private static function decodeKey( string $keyBase64 ): string {
		$key = base64_decode( $keyBase64, true );
		if ( false === $key || SODIUM_CRYPTO_SECRETBOX_KEYBYTES !== strlen( $key ) ) {
			throw new \InvalidArgumentException(
				'EZ_CORE_SECRETS_KEY must be base64 of ' . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ' bytes.'
			);
		}

		return $key;
	}
}
