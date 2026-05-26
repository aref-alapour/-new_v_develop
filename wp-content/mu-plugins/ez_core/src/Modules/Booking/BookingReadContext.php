<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Modules\AjaxGateway\GatewayResponse;

/**
 * Per-request booking read path metadata (native vs legacy) for dev headers + logs.
 */
final class BookingReadContext
{
	private static ?string $path = null;

	private static ?string $reason = null;

	private static int $count = 0;

	public static function reset(): void {
		self::$path   = null;
		self::$reason = null;
		self::$count  = 0;
	}

	public static function setPath( string $path ): void {
		self::$path = $path;
	}

	public static function setReason( string $reason ): void {
		self::$reason = $reason;
	}

	public static function setCount( int $count ): void {
		self::$count = max( 0, $count );
	}

	public static function getPath(): ?string {
		return self::$path;
	}

	public static function getReason(): ?string {
		return self::$reason;
	}

	public static function getCount(): int {
		return self::$count;
	}

	public static function applyDevHeaders(): void {
		if ( ! ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ) {
			return;
		}

		if ( null !== self::$path && '' !== self::$path ) {
			GatewayResponse::setResponseHeader( 'X-EZ-Booking-Path', self::$path );
		}

		GatewayResponse::setResponseHeader( 'X-EZ-Booking-Sans-Count', (string) self::$count );

		if ( null !== self::$reason && '' !== self::$reason ) {
			GatewayResponse::setResponseHeader( 'X-EZ-Booking-Reason', self::$reason );
		}
	}
}
