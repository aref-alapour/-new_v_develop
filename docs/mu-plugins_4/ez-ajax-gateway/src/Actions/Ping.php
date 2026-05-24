<?php
/**
 * `ping` action — wp_level=none.
 *
 * Smallest possible response: confirms gateway is reachable and signature flow works.
 * No DB queries beyond the mandatory nonce write (already done by SignatureVerifier).
 */

declare( strict_types = 1 );

namespace EZ\Ajax\Actions;

use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

final class Ping {

	/**
	 * @param array<string,mixed> $inputs
	 */
	public static function run( array $inputs, Request $req ): Response {
		$start = defined( 'EZ_AJAX_GATEWAY_START' ) ? (float) EZ_AJAX_GATEWAY_START : microtime( true );
		$took  = (int) round( ( microtime( true ) - $start ) * 1000 );

		return Response::json(
			[
				'pong'       => true,
				'server_now' => time(),
				'took_ms'    => $took,
			]
		)
			->withHeader( 'Cache-Control', 'no-store, no-cache, must-revalidate' );
	}
}
