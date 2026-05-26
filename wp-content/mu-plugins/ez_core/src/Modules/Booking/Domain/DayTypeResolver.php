<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Domain;

use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\EloquentCalendarRepository;
use EscapeZoom\Core\Support\TehranTime;

/**
 * normals | holidays | closed (legacy get_day_type2).
 */
final class DayTypeResolver
{
	public function __construct(
		private ?EloquentCalendarRepository $calendar = null
	) {
		$this->calendar = $calendar ?? new EloquentCalendarRepository();
	}

	public function resolve( int $dayUnix ): string {
		$calendarData = $this->calendar->getCalendarData();
		$day          = TehranTime::tehranMidnightUnix( $dayUnix );

		foreach ( explode( ',', (string) ( $calendarData['holidays'] ?? '' ) ) as $calendarDay ) {
			$calendarDay = trim( $calendarDay );
			if ( '' === $calendarDay || ! is_numeric( $calendarDay ) ) {
				continue;
			}
			if ( TehranTime::tehranMidnightUnix( (int) $calendarDay ) === $day ) {
				return 'holidays';
			}
		}

		foreach ( explode( ',', (string) ( $calendarData['closed_days'] ?? '' ) ) as $calendarDay ) {
			$calendarDay = trim( $calendarDay );
			if ( '' === $calendarDay || ! is_numeric( $calendarDay ) ) {
				continue;
			}
			if ( TehranTime::tehranMidnightUnix( (int) $calendarDay ) === $day ) {
				return 'closed';
			}
		}

		return 'normals';
	}
}
