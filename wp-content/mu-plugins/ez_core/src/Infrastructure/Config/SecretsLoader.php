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

	/**
	 * @return array{host: string, database: string, username: string, password: string, table_prefix: string}|null
	 */
	public static function wordpressDatabase(): ?array {
		if ( ! self::boot() ) {
			return null;
		}

		$host     = (string) self::get( 'wordpress.host', '' );
		$database = (string) self::get( 'wordpress.database', '' );
		$username = (string) self::get( 'wordpress.username', '' );
		$password = (string) self::get( 'wordpress.password', '' );
		$prefix   = (string) self::get( 'wordpress.table_prefix', '' );

		if ( '' === $host || '' === $database || '' === $username || '' === $prefix ) {
			return null;
		}

		return array(
			'host'          => $host,
			'database'      => $database,
			'username'      => $username,
			'password'      => $password,
			'table_prefix'  => $prefix,
		);
	}

	public static function tablePrefix(): string {
		$wp = self::wordpressDatabase();
		if ( null !== $wp ) {
			return $wp['table_prefix'];
		}

		if ( isset( $GLOBALS['table_prefix'] ) && is_string( $GLOBALS['table_prefix'] ) && '' !== $GLOBALS['table_prefix'] ) {
			return $GLOBALS['table_prefix'];
		}

		return 'wp_';
	}

	public static function ajaxSharedSecret(): string {
		return (string) self::get( 'gateway.ajax_shared_secret', '' );
	}

	public static function encFilePath(): string {
		$corePath = defined( 'EZ_CORE_PATH' ) ? EZ_CORE_PATH : dirname( __DIR__, 3 );

		return $corePath . self::ENC_FILE;
	}

	/**
	 * Master AJAX signing secret from secrets.enc (empty if not configured).
	 */
	public static function resolveAjaxSharedSecret(): string {
		if ( ! self::boot() ) {
			self::debugLog(
				'resolveAjaxSharedSecret: boot failed',
				array(
					'boot_error' => self::getBootError(),
					'enc_path'   => self::encFilePath(),
					'is_loaded'  => self::isLoaded(),
				)
			);

			return '';
		}

		$secret = self::ajaxSharedSecret();
		if ( '' === $secret ) {
			self::debugLog(
				'resolveAjaxSharedSecret: gateway.ajax_shared_secret empty',
				array(
					'enc_path'  => self::encFilePath(),
					'is_loaded' => self::isLoaded(),
				)
			);
		}

		return $secret;
	}

	public static function bookingUseInternal(): bool {
		$val = self::get( 'gateway.booking_use_internal', true );

		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	public static function bookingNativeSanses(): bool {
		$val = self::get( 'gateway.booking_native_sanses', true );

		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	public static function payloadEncryptWrites(): bool {
		$val = self::get( 'gateway.payload_encrypt_writes', false );

		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	public static function payloadEncryptReads(): bool {
		$val = self::get( 'gateway.payload_encrypt_reads', false );

		return filter_var( $val, FILTER_VALIDATE_BOOLEAN );
	}

	/**
	 * Rate limit config for a gateway action (secrets override + defaults).
	 *
	 * @return array{per_ip: int, per_client: int, window_seconds: int}
	 */
	public static function rateLimitFor( string $action ): array {
		$defaults = array(
			'booking.sans_day_json' => array(
				'per_ip'          => 120,
				'per_client'      => 60,
				'window_seconds'  => 60,
			),
			'booking.game_search' => array(
				'per_ip'          => 30,
				'per_client'      => 20,
				'window_seconds'  => 60,
			),
			'booking.check_playing' => array(
				'per_ip'          => 60,
				'per_client'      => 40,
				'window_seconds'  => 60,
			),
			'booking.bulk_date_range' => array(
				'per_ip'          => 10,
				'per_client'      => 5,
				'window_seconds'  => 60,
			),
			'default'             => array(
				'per_ip'          => 60,
				'per_client'      => 30,
				'window_seconds'  => 60,
			),
		);

		$base = $defaults[ $action ] ?? $defaults['default'];

		$fromSecrets = self::get( 'gateway.rate_limits.' . $action );
		if ( ! is_array( $fromSecrets ) ) {
			$fromSecrets = self::get( 'gateway.rate_limits.default' );
		}
		if ( is_array( $fromSecrets ) ) {
			$base = array_merge( $base, $fromSecrets );
		}

		$merged = array(
			'per_ip'         => max( 1, (int) ( $base['per_ip'] ?? 60 ) ),
			'per_client'     => max( 1, (int) ( $base['per_client'] ?? 30 ) ),
			'window_seconds' => max( 1, (int) ( $base['window_seconds'] ?? 60 ) ),
		);

		if ( function_exists( 'apply_filters' ) ) {
			$filtered = apply_filters( 'ez_gateway_rate_limit', $merged, $action );
			if ( is_array( $filtered ) ) {
				$merged = array(
					'per_ip'         => max( 1, (int) ( $filtered['per_ip'] ?? $merged['per_ip'] ) ),
					'per_client'     => max( 1, (int) ( $filtered['per_client'] ?? $merged['per_client'] ) ),
					'window_seconds' => max( 1, (int) ( $filtered['window_seconds'] ?? $merged['window_seconds'] ) ),
				);
			}
		}

		return $merged;
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

		self::bootstrapKeyEnv();

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

	/**
	 * Resolve EZ_CORE_SECRETS_KEY from constant, *_FILE, or repo-root .env (dev).
	 */
	private static function bootstrapKeyEnv(): void {
		$keyEnv = getenv( 'EZ_CORE_SECRETS_KEY' );
		if ( false !== $keyEnv && '' !== $keyEnv ) {
			return;
		}

		if ( defined( 'EZ_CORE_SECRETS_KEY' ) && '' !== (string) EZ_CORE_SECRETS_KEY ) {
			putenv( 'EZ_CORE_SECRETS_KEY=' . (string) EZ_CORE_SECRETS_KEY );

			return;
		}

		$fileVar = getenv( 'EZ_CORE_SECRETS_KEY_FILE' );
		if ( false !== $fileVar && '' !== $fileVar && is_readable( $fileVar ) ) {
			$key = trim( (string) file_get_contents( $fileVar ) );
			if ( '' !== $key ) {
				putenv( 'EZ_CORE_SECRETS_KEY=' . $key );

				return;
			}
		}

		foreach ( self::dotEnvPaths() as $path ) {
			if ( ! is_readable( $path ) ) {
				continue;
			}
			$key = self::readEnvFileValue( $path, 'EZ_CORE_SECRETS_KEY' );
			if ( '' !== $key ) {
				putenv( 'EZ_CORE_SECRETS_KEY=' . $key );

				return;
			}
		}
	}

	/**
	 * @return list<string>
	 */
	private static function dotEnvPaths(): array {
		$paths = array();
		if ( defined( 'ABSPATH' ) ) {
			$paths[] = ABSPATH . '.env';
		}
		if ( defined( 'EZ_CORE_PATH' ) ) {
			$paths[] = dirname( EZ_CORE_PATH, 3 ) . '/.env';
		}

		return array_values( array_unique( $paths ) );
	}

	private static function readEnvFileValue( string $path, string $name ): string {
		$lines = file( $path, FILE_IGNORE_NEW_LINES );
		if ( false === $lines ) {
			return '';
		}

		$prefix = $name . '=';
		foreach ( $lines as $line ) {
			$line = trim( $line );
			if ( '' === $line || '#' === $line[0] ) {
				continue;
			}
			if ( ! str_starts_with( $line, $prefix ) ) {
				continue;
			}

			$value = trim( substr( $line, strlen( $prefix ) ) );
			if ( str_starts_with( $value, '"' ) && str_ends_with( $value, '"' ) && strlen( $value ) >= 2 ) {
				$value = substr( $value, 1, -1 );
			} elseif ( str_starts_with( $value, "'" ) && str_ends_with( $value, "'" ) && strlen( $value ) >= 2 ) {
				$value = substr( $value, 1, -1 );
			}

			return $value;
		}

		return '';
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

	private static function debugLoggingEnabled(): bool {
		if ( defined( 'EZ_DEBUG_GATEWAY_BOOT' ) && EZ_DEBUG_GATEWAY_BOOT ) {
			return true;
		}

		return defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;
	}

	/**
	 * @param array<string, mixed> $context
	 */
	private static function debugLog( string $message, array $context = array() ): void {
		if ( ! self::debugLoggingEnabled() ) {
			return;
		}

		$line = '[EZ Core DEBUG] ' . $message;
		if ( array() !== $context ) {
			$encoded = function_exists( 'wp_json_encode' )
				? wp_json_encode( $context, JSON_UNESCAPED_SLASHES )
				: json_encode( $context, JSON_UNESCAPED_SLASHES );
			if ( is_string( $encoded ) && '' !== $encoded ) {
				$line .= ' ' . $encoded;
			}
		}

		// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
		error_log( $line );
	}
}
