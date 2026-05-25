<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway;

/**
 * Gateway HTTP response helpers.
 */
final class GatewayResponse
{
	public static function json( bool $ok, array $data = array(), ?array $error = null, int $status = 200 ): void {
		status_header( $status );
		header( 'Content-Type: application/json; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		$payload = array( 'ok' => $ok );
		if ( $ok ) {
			$payload['data'] = $data;
		} else {
			$payload['error'] = $error ?? array( 'code' => 'ERROR', 'message' => 'Request failed' );
		}
		echo wp_json_encode( $payload, JSON_UNESCAPED_UNICODE );
		exit;
	}

	/** Legacy reservation.php JSON body (no {ok,data} envelope). */
	public static function raw( string $body, string $contentType = 'application/json', int $status = 200 ): void {
		status_header( $status );
		header( 'Content-Type: ' . $contentType . '; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo $body;
		exit;
	}

	public static function html( string $html, int $status = 200 ): void {
		status_header( $status );
		header( 'Content-Type: text/html; charset=utf-8' );
		header( 'X-Robots-Tag: noindex' );
		echo $html;
		exit;
	}
}
