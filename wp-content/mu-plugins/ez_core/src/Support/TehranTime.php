<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Support;

/**
 * Asia/Tehran midnight normalization (legacy ez_ws_tehran_midnight_unix).
 */
final class TehranTime
{
	public static function tehranMidnightUnix( int $timestamp ): int {
		$timestamp = (int) $timestamp;
		if ( $timestamp <= 0 ) {
			return 0;
		}

		$tz   = new \DateTimeZone( 'Asia/Tehran' );
		$date = new \DateTime( '@' . $timestamp );
		$date->setTimezone( $tz );
		$midnight = new \DateTime( $date->format( 'Y-m-d' ) . ' 00:00:00', $tz );

		return (int) $midnight->getTimestamp();
	}

	/**
	 * Start of Gregorian calendar day in Asia/Tehran (00:00:00).
	 */
	public static function gregorianDayStartUnix( int $gy, int $gm, int $gd ): int {
		$tz = new \DateTimeZone( 'Asia/Tehran' );
		$dt = \DateTime::createFromFormat( 'Y-n-j H:i:s', sprintf( '%d-%d-%d 00:00:00', $gy, $gm, $gd ), $tz );
		if ( false === $dt ) {
			return 0;
		}

		return (int) $dt->getTimestamp();
	}

	/**
	 * End of Gregorian calendar day in Asia/Tehran (23:59:59).
	 */
	public static function gregorianDayEndUnix( int $gy, int $gm, int $gd ): int {
		$tz = new \DateTimeZone( 'Asia/Tehran' );
		$dt = \DateTime::createFromFormat( 'Y-n-j H:i:s', sprintf( '%d-%d-%d 23:59:59', $gy, $gm, $gd ), $tz );
		if ( false === $dt ) {
			return 0;
		}

		return (int) $dt->getTimestamp();
	}

	/**
	 * Add calendar days in Asia/Tehran and return midnight unix of result day.
	 */
	public static function addTehranDays( int $timestamp, int $days ): int {
		$tz = new \DateTimeZone( 'Asia/Tehran' );
		$dt = new \DateTime( '@' . self::tehranMidnightUnix( $timestamp ) );
		$dt->setTimezone( $tz );
		$modifier = ( $days >= 0 ? '+' : '' ) . $days . ' day';
		$dt->modify( $modifier );
		$dt->setTime( 0, 0, 0 );

		return (int) $dt->getTimestamp();
	}

	/**
	 * Format unix day anchor as Y-m-d in Asia/Tehran (legacy date() after bootstrap timezone).
	 */
	public static function formatDayYmd( int $timeRes ): string {
		$tz   = new \DateTimeZone( 'Asia/Tehran' );
		$date = new \DateTime( '@' . (int) $timeRes );
		$date->setTimezone( $tz );

		return $date->format( 'Y-m-d' );
	}

	/**
	 * Slot timestamp from day anchor + HH:MM (legacy strtotime + date Y-m-d).
	 */
	public static function slotTimestamp( int $timeRes, string $timeHm ): int|false {
		$ymd = self::formatDayYmd( $timeRes );
		$tz  = new \DateTimeZone( 'Asia/Tehran' );
		$dt  = \DateTime::createFromFormat( 'Y-m-d H:i', $ymd . ' ' . $timeHm, $tz );
		if ( false === $dt ) {
			return false;
		}

		return (int) $dt->getTimestamp();
	}
}
