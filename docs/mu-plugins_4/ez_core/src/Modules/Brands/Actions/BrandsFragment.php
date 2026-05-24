<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Actions;

use EscapeZoom\Core\Modules\Brands\Services\BrandsDirectoryReadService;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

/**
 * `brands.fragment` — HTML grid + pagination without {@see wp-load.php}.
 *
 * Security: same gateway pre-HMAC stack as other actions; delegates to {@see BrandsDirectoryReadService}.
 */
final class BrandsFragment
{
	/** @param array<string, mixed> $inputs */
	public static function run(array $inputs, Request $req): Response
	{
		$page = isset($inputs['page']) ? max(1, (int) $inputs['page']) : 1;
		$result = (new BrandsDirectoryReadService())->buildFragment($page);

		$response = Response::html($result->html, $result->status)
			->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

		if ($result->withVary) {
			$response = $response->withHeader('Vary', 'HX-Request, Accept');
		}

		if ($result->hxPushUrl !== null && $result->hxPushUrl !== '') {
			$response = $response->withHeader('HX-Push-Url', $result->hxPushUrl);
		}

		return $response;
	}
}
