<?php
/**
 * Response value object — caller builds it, Gateway serializes it.
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Http;

final class Response {

	private int $status;
	private string $content_type;
	private string $body;

	/** @var array<string,string> */
	private array $headers;

	/**
	 * @param array<string,string> $headers
	 */
	private function __construct( int $status, string $content_type, string $body, array $headers ) {
		$this->status       = $status;
		$this->content_type = $content_type;
		$this->body         = $body;
		$this->headers      = $headers;
	}

	/**
	 * JSON success: `{"ok":true,"data":{...}}`.
	 *
	 * @param mixed                $data
	 * @param array<string,string> $headers
	 */
	public static function json( $data, int $status = 200, array $headers = [] ): self {
		$payload = wp_json_or_native_encode(
			[
				'ok'   => true,
				'data' => $data,
			]
		);
		return new self( $status, 'application/json; charset=utf-8', $payload, $headers );
	}

	/**
	 * JSON failure: `{"ok":false,"error":{"code":CODE}}` — never leak `detail` to clients.
	 *
	 * @param array<string,string> $headers
	 */
	public static function error( string $code, int $status, array $headers = [] ): self {
		$payload = wp_json_or_native_encode(
			[
				'ok'    => false,
				'error' => [ 'code' => $code ],
			]
		);
		return new self( $status, 'application/json; charset=utf-8', $payload, $headers );
	}

	/**
	 * HTML fragment (handler decides escaping).
	 *
	 * @param array<string,string> $headers
	 */
	public static function html( string $html, int $status = 200, array $headers = [] ): self {
		return new self( $status, 'text/html; charset=utf-8', $html, $headers );
	}

	public function withHeader( string $name, string $value ): self {
		$clone                     = clone $this;
		$clone->headers[ $name ] = $value;
		return $clone;
	}

	public function send(): void {
		if ( ! headers_sent() ) {
			http_response_code( $this->status );
			header( 'Content-Type: ' . $this->content_type );
			foreach ( $this->headers as $name => $value ) {
				header( $name . ': ' . $value, true );
			}
		}
		echo $this->body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped — caller handles escaping.
	}

	public function status(): int {
		return $this->status;
	}

	public function contentType(): string {
		return $this->content_type;
	}

	public function body(): string {
		return $this->body;
	}

	/** @return array<string,string> */
	public function headers(): array {
		return $this->headers;
	}
}

/**
 * JSON encode helper that does not depend on WP. We avoid wp_json_encode for wp_level=none.
 *
 * @param mixed $payload
 */
function wp_json_or_native_encode( $payload ): string {
	$out = json_encode( $payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	if ( ! is_string( $out ) ) {
		return '{"ok":false,"error":{"code":"INTERNAL"}}';
	}
	return $out;
}
