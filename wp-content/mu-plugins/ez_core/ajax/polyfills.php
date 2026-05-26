<?php
/**
 * WordPress shims for light /ajax gateway.
 */
declare(strict_types=1);

if ( ! function_exists( 'status_header' ) ) {
	function status_header( int $code, $description = '' ): void {
		if ( headers_sent() ) {
			return;
		}
		$text = '';
		switch ( $code ) {
			case 200:
				$text = 'OK';
				break;
			case 400:
				$text = 'Bad Request';
				break;
			case 401:
				$text = 'Unauthorized';
				break;
			case 404:
				$text = 'Not Found';
				break;
			case 405:
				$text = 'Method Not Allowed';
				break;
			case 503:
				$text = 'Service Unavailable';
				break;
			default:
				$text = '';
		}
		header( 'HTTP/1.1 ' . $code . ( $text ? ' ' . $text : '' ), true, $code );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	/**
	 * @param mixed $data
	 */
	function wp_json_encode( $data, int $options = 0, int $depth = 512 ) {
		return json_encode( $data, $options | JSON_UNESCAPED_UNICODE, $depth );
	}
}
