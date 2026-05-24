<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\Actions;

use EscapeZoom\Core\Modules\Brands\Services\BrandsDirectoryReadService;
use EZ\Ajax\Http\Request;
use EZ\Ajax\Http\Response;

/**
 * Reference action proving Eloquent works in the gateway WITHOUT wp-load.
 *
 * Registered in registry.php with `wp_level => 'none'`.
 */
final class BrandsCount
{
	/**
	 * @param array<string,mixed> $inputs
	 */
	public static function run(array $inputs, Request $req): Response
	{
		$count = (new BrandsDirectoryReadService())->countProductBrandTerms();

		return Response::json(
			[
				'count' => $count,
				'wp_loaded' => defined('EZ_AJAX_WP_LOADED'),
			]
		)->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate');
	}
}
