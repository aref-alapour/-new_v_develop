<?php

declare(strict_types=1);

namespace EZ\Ajax\Store;

use EscapeZoom\Core\Modules\AjaxGateway\Repositories\EzAjaxRateLimiterRepository;

final class EloquentRateLimiter implements RateLimiterInterface {

	private EzAjaxRateLimiterRepository $repository;

	public function __construct( ?EzAjaxRateLimiterRepository $repository = null ) {
		$this->repository = $repository ?? new EzAjaxRateLimiterRepository();
	}

	public function consume( string $bucket, int $capacity, int $window_s ): bool {
		return $this->repository->consume( $bucket, $capacity, $window_s );
	}
}
