<?php

declare(strict_types=1);

namespace EZ\Ajax\Store;

use EscapeZoom\Core\Modules\AjaxGateway\Repositories\EzAjaxNonceRepository;

final class EloquentNonceStore implements NonceStoreInterface {

	private EzAjaxNonceRepository $repository;

	public function __construct( ?EzAjaxNonceRepository $repository = null ) {
		$this->repository = $repository ?? new EzAjaxNonceRepository();
	}

	public function useOnce( string $nonce, int $ttl_seconds ): bool {
		return $this->repository->useOnce( $nonce, $ttl_seconds );
	}

	public function gc(): void {
		$this->repository->gc();
	}
}
