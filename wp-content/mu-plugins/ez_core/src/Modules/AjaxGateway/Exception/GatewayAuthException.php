<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Exception;

/**
 * Thrown when product-level authorization fails for write actions.
 */
final class GatewayAuthException extends \RuntimeException
{
	public function __construct(
		private readonly string $errorCode = 'FORBIDDEN',
		string $message = 'Forbidden',
	) {
		parent::__construct( $message );
	}

	public function errorCode(): string {
		return $this->errorCode;
	}
}
