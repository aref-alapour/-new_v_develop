<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Domain;

use EscapeZoom\Core\Support\TehranTime;

/**
 * Build ordered day slots for one display day (legacy handler 1093–1118).
 */
final class DaySlotBuilder
{
	/**
	 * @param array<string, array<int, array<string, mixed>>> $scheduleDecoded
	 * @return array<int, array{ts: int, sans: array<string, mixed>, day_type: string}>
	 */
	public function buildForDay( int $timeRes, string $dayType, array $scheduleDecoded ): array {
		if ( 'closed' === $dayType || ! isset( $scheduleDecoded[ $dayType ] ) ) {
			return array();
		}

		$scheduleKey = $dayType;
		$sansRows      = $scheduleDecoded[ $scheduleKey ];
		if ( empty( $sansRows ) || ! is_array( $sansRows ) ) {
			return array();
		}

		$timeResNext = $timeRes + 86400;
		$daySlots    = array();

		foreach ( $sansRows as $sans ) {
			if ( ! is_array( $sans ) || ! isset( $sans['time'] ) ) {
				continue;
			}
			$t = (string) $sans['time'];
			$h = (int) substr( $t, 0, 2 );
			if ( $h >= 8 ) {
				$ts = TehranTime::slotTimestamp( $timeRes, $t );
				if ( false !== $ts ) {
					$daySlots[] = array(
						'ts'       => (int) $ts,
						'sans'     => $sans,
						'day_type' => $scheduleKey,
					);
				}
			}
		}

		foreach ( $sansRows as $sans ) {
			if ( ! is_array( $sans ) || ! isset( $sans['time'] ) ) {
				continue;
			}
			$t = (string) $sans['time'];
			$h = (int) substr( $t, 0, 2 );
			if ( $h < 8 ) {
				$ts = TehranTime::slotTimestamp( $timeResNext, $t );
				if ( false !== $ts ) {
					$daySlots[] = array(
						'ts'       => (int) $ts,
						'sans'     => $sans,
						'day_type' => $scheduleKey,
					);
				}
			}
		}

		usort(
			$daySlots,
			static function ( array $a, array $b ): int {
				return $a['ts'] <=> $b['ts'];
			}
		);

		return $daySlots;
	}
}
