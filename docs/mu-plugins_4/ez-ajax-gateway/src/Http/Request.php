<?php
/**
 * Lightweight request value object — independent of WP.
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Http;

final class Request {

	/** @var array<string,string> Lowercased header name → first value. */
	private array $headers;

	/** @var array<string,string> */
	private array $query;

	/** @var array<string,mixed> Decoded body (parsed application/json or form-urlencoded). */
	private array $parsed_body;

	private string $raw_body;
	private string $method;
	private string $path;
	private string $client_ip;

	/**
	 * @param array<string,string> $headers
	 * @param array<string,string> $query
	 * @param array<string,mixed>  $parsed_body
	 */
	public function __construct(
		string $method,
		string $path,
		string $client_ip,
		array $headers,
		array $query,
		array $parsed_body,
		string $raw_body
	) {
		$this->method      = strtoupper( $method );
		$this->path        = $path;
		$this->client_ip   = $client_ip;
		$this->headers     = $headers;
		$this->query       = $query;
		$this->parsed_body = $parsed_body;
		$this->raw_body    = $raw_body;
	}

	public static function fromGlobals(): self {
		$headers = [];
		foreach ( $_SERVER as $k => $v ) {
			if ( ! is_string( $k ) ) {
				continue;
			}
			if ( strncmp( $k, 'HTTP_', 5 ) === 0 ) {
				$name             = strtolower( str_replace( '_', '-', substr( $k, 5 ) ) );
				$headers[ $name ] = is_string( $v ) ? $v : '';
			}
		}
		// Content-Type / Content-Length are not HTTP_ prefixed in CGI/FastCGI.
		if ( isset( $_SERVER['CONTENT_TYPE'] ) && is_string( $_SERVER['CONTENT_TYPE'] ) ) {
			$headers['content-type'] = $_SERVER['CONTENT_TYPE'];
		}
		if ( isset( $_SERVER['CONTENT_LENGTH'] ) && is_string( $_SERVER['CONTENT_LENGTH'] ) ) {
			$headers['content-length'] = $_SERVER['CONTENT_LENGTH'];
		}

		$method = isset( $_SERVER['REQUEST_METHOD'] ) && is_string( $_SERVER['REQUEST_METHOD'] )
			? $_SERVER['REQUEST_METHOD']
			: 'GET';

		$raw_path = isset( $_SERVER['REQUEST_URI'] ) && is_string( $_SERVER['REQUEST_URI'] )
			? $_SERVER['REQUEST_URI']
			: '/ajax';
		$path     = strtok( $raw_path, '?' );
		if ( ! is_string( $path ) || '' === $path ) {
			$path = '/ajax';
		}

		$raw_body = (string) file_get_contents( 'php://input' );
		$ctype    = strtolower( $headers['content-type'] ?? '' );

		$parsed = [];
		if ( str_contains( $ctype, 'application/json' ) && '' !== $raw_body ) {
			$decoded = json_decode( $raw_body, true );
			if ( is_array( $decoded ) ) {
				$parsed = $decoded;
			}
		} elseif ( str_contains( $ctype, 'application/x-www-form-urlencoded' ) ) {
			parse_str( $raw_body, $parsed );
			if ( ! is_array( $parsed ) ) {
				$parsed = [];
			}
		} elseif ( str_contains( $ctype, 'multipart/form-data' ) ) {
			// PHP already populated $_POST.
			$parsed = $_POST;
		}

		// Always merge query string (read-only) for lookups.
		$query = [];
		if ( isset( $_GET ) && is_array( $_GET ) ) {
			foreach ( $_GET as $k => $v ) {
				if ( is_string( $k ) ) {
					$query[ $k ] = is_string( $v ) ? $v : (string) ( is_scalar( $v ) ? $v : '' );
				}
			}
		}

		$ip = '0.0.0.0';
		if ( isset( $_SERVER['REMOTE_ADDR'] ) && is_string( $_SERVER['REMOTE_ADDR'] ) ) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return new self( $method, $path, $ip, $headers, $query, $parsed, $raw_body );
	}

	public function header( string $name ): string {
		return (string) ( $this->headers[ strtolower( $name ) ] ?? '' );
	}

	/**
	 * Action name lookup priority: X-EZ-Action header > parsed body 'action' > query 'action'.
	 */
	public function action(): string {
		$h = $this->header( 'X-EZ-Action' );
		if ( '' !== $h ) {
			return $h;
		}
		if ( isset( $this->parsed_body['action'] ) && is_string( $this->parsed_body['action'] ) ) {
			return $this->parsed_body['action'];
		}
		if ( isset( $this->query['action'] ) && is_string( $this->query['action'] ) ) {
			return $this->query['action'];
		}
		return '';
	}

	public function method(): string {
		return $this->method;
	}

	public function path(): string {
		return $this->path;
	}

	public function clientIp(): string {
		return $this->client_ip;
	}

	public function rawBody(): string {
		return $this->raw_body;
	}

	/** @return array<string,mixed> */
	public function parsedBody(): array {
		return $this->parsed_body;
	}

	/** @return array<string,string> */
	public function query(): array {
		return $this->query;
	}

	/**
	 * Single input value (parsed body > query).
	 *
	 * @return mixed
	 */
	public function input( string $key ) {
		if ( array_key_exists( $key, $this->parsed_body ) ) {
			return $this->parsed_body[ $key ];
		}
		if ( array_key_exists( $key, $this->query ) ) {
			return $this->query[ $key ];
		}
		return null;
	}
}
