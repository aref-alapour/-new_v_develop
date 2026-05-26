<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;

/**
 * calendar_data row (escapezo_queries) for day type resolution.
 */
final class EloquentCalendarRepository
{
	/** @var array<string, mixed>|null */
	private static ?array $cached = null;

	/**
	 * Unserialized calendar map (holidays, closed_days, …).
	 *
	 * @return array<string, mixed>
	 */
	public function getCalendarData(): array {
		if ( null !== self::$cached ) {
			return self::$cached;
		}

		if ( ! CapsuleManager::isBooted() ) {
			CapsuleManager::boot();
		}

		try {
			$row = CapsuleManager::connection( 'external' )
				->table( 'calendar_data' )
				->select( 'data' )
				->first();
		} catch ( \Throwable $e ) {
			error_log( '[EZ Booking] calendar_data read failed: ' . $e->getMessage() );
			self::$cached = array();

			return array();
		}

		if ( null === $row || empty( $row->data ) ) {
			self::$cached = array();

			return array();
		}

		$decoded = @unserialize( (string) $row->data );
		if ( ! is_array( $decoded ) ) {
			self::$cached = array();

			return array();
		}

		// Legacy json_decode(json_encode(unserialize(...)), true).
		self::$cached = json_decode( json_encode( $decoded ), true );
		if ( ! is_array( self::$cached ) ) {
			self::$cached = array();
		}

		return self::$cached;
	}

	public static function clearRequestCache(): void {
		self::$cached = null;
	}
}
