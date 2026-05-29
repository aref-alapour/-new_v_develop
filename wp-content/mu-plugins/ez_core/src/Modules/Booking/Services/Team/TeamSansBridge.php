<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Models\BookingHistory;
use EscapeZoom\Core\Support\TehranTime;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Team sans-management HTML/JSON (ported from web-service/team/sans_management.php).
 */
final class TeamSansBridge
{
	/** @var array<int, array<string,mixed>|null> */
	private static array $productRowCache = array();

	/** @var array<int, string> */
	private static array $dayTypeCache = array();

	/** @var array<string, array<string,mixed>> */
	private static array $scheduleCache = array();

	public static function checkPlayingHtml( int $productId, int $dayStartTime ): string {
		self::assertExternalDb();

		$product = self::getProductRow( $productId );
		if ( null === $product ) {
			return '';
		}

		$dayType     = self::getDayType( $dayStartTime );
		$sanses      = self::getSansesFromRow( $product );
		$scheduleKey = ( 'closed' === $dayType || ! isset( $sanses[ $dayType ] ) ) ? null : $dayType;
		$sansForDay  = ( null !== $scheduleKey && isset( $sanses[ $scheduleKey ] ) ) ? $sanses[ $scheduleKey ] : array();
		$daySlots    = self::buildDaySlots( $dayStartTime, $scheduleKey, $sansForDay );

		$tsList = array_column( $daySlots, 'ts' );
		$orders = array();
		if ( ! empty( $tsList ) ) {
			$rows = BookingHistory::query()
				->where( 'room_id', $productId )
				->whereIn( 'booking_time', $tsList )
				->where( 'status', 1 )
				->get( array( 'status', 'booking_time', 'booked_time', 'name', 'level', 'phone', 'quantity' ) );

			foreach ( $rows as $row ) {
				$bookingTime = (int) ( $row->booking_time ?? 0 );
				if ( $bookingTime <= 0 ) {
					continue;
				}
				$key       = (string) $bookingTime;
				$candidate = method_exists( $row, 'toArray' ) ? $row->toArray() : (array) $row;
				$current   = $orders[ $key ] ?? null;
				if ( ! is_array( $current ) || (int) ( $candidate['booked_time'] ?? 0 ) >= (int) ( $current['booked_time'] ?? 0 ) ) {
					$orders[ $key ] = $candidate;
				}
			}
		}

		$duration = isset( $product['duration'] ) ? (int) $product['duration'] : 0;
		$now      = time();
		$html     = '';

		foreach ( $daySlots as $slot ) {
			$ts        = (int) $slot['ts'];
			$orderObj  = $orders[ (string) $ts ] ?? null;
			$status    = isset( $orderObj['status'] ) ? (int) $orderObj['status'] : 0;

			if ( 1 !== $status || ! is_array( $orderObj ) ) {
				continue;
			}

			if ( $ts >= $now || $now >= $ts + $duration * 60 ) {
				continue;
			}

			$level = isset( $orderObj['level'] ) ? (int) $orderObj['level'] : 0;
			if ( 1 === $level ) {
				$themeColor = '[#858585]';
				$themeText  = 'تازه وارد';
			} elseif ( 2 === $level ) {
				$themeColor = '[#252728]';
				$themeText  = 'نوپا';
			} elseif ( 3 === $level ) {
				$themeColor = '[#00B2FF]';
				$themeText  = 'با تجربه';
			} else {
				$themeColor = 'primary-500';
				$themeText  = 'کارکشته';
			}

			$name     = esc_html( (string) ( $orderObj['name'] ?? '' ) );
			$quantity = (int) ( $orderObj['quantity'] ?? 0 );
			$timeLbl  = self::formatJalaliTime( $ts );

			$html .= '<div class="flex justify-between items-center border border-[#E8EDF1] rounded-lg p-4">';
			$html .= '<img src="./assets/images/picture-game.svg" alt="">';
			$html .= '<p class="text-base font-yekan-bold text-grayy">سانس <span class="text-base font-yekan-bold text-navyBlue">' . esc_html( $timeLbl ) . '</span></p>';
			$html .= '<div class="flex gap-2">';
			$html .= '<p class="text-base font-yekan-bold text-grayy">توسط <span class="text-base font-yekan-bold text-navyBlue">' . $name . '</span></p>';
			$html .= '<span class="rounded-3xl text-xs font-yekan-heavy text-[#FF6900] p-1" style="background-color: ' . esc_attr( $themeColor ) . '"> ' . esc_html( $themeText ) . ' </span>';
			$html .= '</div>';
			$html .= '<p class="text-base font-yekan-bold text-navyBlue">' . esc_html( (string) $quantity ) . ' بلیت</p>';
			$html .= '<button class="text-lg font-yekan-heavy text-[#02C96F] py-2 px-3 rounded-lg" style="background: rgba(2, 201, 111, 0.10);">در حال بازی</button>';
			$html .= '</div>';
		}

		return $html;
	}

	public static function gameSearchHtml( string $term ): string {
		return ( new GameSearchService() )->searchHtml( $term );
	}

	/**
	 * @return array{success: bool, data: list<string|array<string, string>>}
	 */
	public static function bulkDateRange( int $productId, string $startDate, string $endDate, string $action ): array {
		self::assertExternalDb();
		self::ensureJalaliConverter();

		if ( $productId <= 0 || '' === trim( $startDate ) || '' === trim( $endDate ) ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'اطلاعات تاریخ ناقص است.' ) ),
			);
		}

		if ( ! in_array( $action, array( 'open', 'close' ), true ) ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'عملیات نامعتبر است.' ) ),
			);
		}

		try {
			$startParts = explode( '/', trim( $startDate ) );
			$endParts   = explode( '/', trim( $endDate ) );
			if ( count( $startParts ) < 3 || count( $endParts ) < 3 ) {
				return array(
					'success' => false,
					'data'    => array( array( 'error' => 'فرمت تاریخ نامعتبر است.' ) ),
				);
			}

			$startGreg = \jalali_to_gregorian( (int) $startParts[0], (int) $startParts[1], (int) $startParts[2] );
			$endGreg   = \jalali_to_gregorian( (int) $endParts[0], (int) $endParts[1], (int) $endParts[2] );
			$startDay  = TehranTime::gregorianDayStartUnix( (int) $startGreg[0], (int) $startGreg[1], (int) $startGreg[2] );
			$endDay    = TehranTime::gregorianDayStartUnix( (int) $endGreg[0], (int) $endGreg[1], (int) $endGreg[2] );

			if ( $startDay <= 0 || $endDay <= 0 || $startDay > $endDay ) {
				return array(
					'success' => false,
					'data'    => array( array( 'error' => 'تاریخ شروع نمی‌تواند بزرگتر از تاریخ پایان باشد.' ) ),
				);
			}

			$productRow = self::getProductRow( $productId );
			if ( null === $productRow ) {
				return array(
					'success' => false,
					'data'    => array( array( 'error' => 'محصول یافت نشد.' ) ),
				);
			}

			$sansesData = self::getSansesFromRow( $productRow );
			$userId     = 0;
			if ( function_exists( 'ez_core_gateway_cached_user_id' ) ) {
				$userId = ez_core_gateway_cached_user_id();
			}
			if ( $userId <= 0 && function_exists( 'get_current_user_id' ) ) {
				$userId = (int) get_current_user_id();
			}
			$now        = time();
			$processed  = 0;

			$allSlotTimes = array();
			$currentDay   = $startDay;
			while ( $currentDay <= $endDay ) {
				$dayAnchor = TehranTime::tehranMidnightUnix( $currentDay );
				$dayType   = self::getDayType( $dayAnchor );
				if ( ! isset( $sansesData[ $dayType ] ) || ! is_array( $sansesData[ $dayType ] ) ) {
					$currentDay = TehranTime::addTehranDays( $dayAnchor, 1 );
					continue;
				}

				$daySlots = self::buildDaySlots( $dayAnchor, $dayType, $sansesData[ $dayType ] );
				foreach ( $daySlots as $slot ) {
					$allSlotTimes[] = (int) $slot['ts'];
				}

				$currentDay = TehranTime::addTehranDays( $dayAnchor, 1 );
			}

			$allSlotTimes = array_values( array_unique( array_filter( $allSlotTimes ) ) );
			if ( array() === $allSlotTimes ) {
				return array(
					'success' => true,
					'data'    => array( 'هیچ سانسی برای این بازه یافت نشد.' ),
				);
			}

			if ( 'close' === $action ) {
				$managed = BookingHistory::query()
					->where( 'room_id', $productId )
					->whereIn( 'booking_time', $allSlotTimes )
					->whereIn( 'status', array( 1, 2 ) )
					->pluck( 'booking_time' )
					->map( static fn( $t ): int => (int) $t )
					->all();
				$managedSet = array_fill_keys( $managed, true );
				$toClose    = array_values(
					array_filter(
						$allSlotTimes,
						static fn( int $ts ): bool => ! isset( $managedSet[ $ts ] )
					)
				);

				if ( array() !== $toClose ) {
					BookingHistory::query()
						->where( 'room_id', $productId )
						->whereIn( 'booking_time', $toClose )
						->where( 'status', 2 )
						->delete();

					$rows = array();
					foreach ( $toClose as $sansTimeTs ) {
						$rows[] = array(
							'customer_id'  => $userId,
							'wc_order_id'  => null,
							'status'       => 2,
							'room_id'      => $productId,
							'booking_time' => $sansTimeTs,
							'booked_time'  => $now,
							'name'         => null,
							'phone'        => null,
							'quantity'     => 0,
						);
					}
					foreach ( array_chunk( $rows, 500 ) as $chunk ) {
						BookingHistory::query()->insert( $chunk );
					}
					$processed = count( $toClose );
				}
			} else {
				$processed = BookingHistory::query()
					->where( 'room_id', $productId )
					->whereIn( 'booking_time', $allSlotTimes )
					->where( 'status', 2 )
					->delete();
			}

			$msg = ( 'close' === $action )
				? "تعداد {$processed} سانس با موفقیت بسته شد."
				: "تعداد {$processed} سانس با موفقیت باز شد.";

			return array(
				'success' => true,
				'data'    => array( $msg ),
			);
		} catch ( \Throwable $e ) {
			return array(
				'success' => false,
				'data'    => array( array( 'error' => 'خطای سرور: ' . $e->getMessage() ) ),
			);
		}
	}

	private static function assertExternalDb(): void {
		if ( ! CapsuleManager::hasExternalConnection() ) {
			throw new \RuntimeException( 'External DB unavailable' );
		}
	}

	/**
	 * @return array<string, mixed>|null
	 */
	public static function getProductRow( int $productId ): ?array {
		if ( array_key_exists( $productId, self::$productRowCache ) ) {
			return self::$productRowCache[ $productId ];
		}

		$cacheKey = "ez_product_row_{$productId}";
		$cached   = function_exists( 'wp_cache_get' ) ? wp_cache_get( $cacheKey, 'ez_booking' ) : false;
		if ( is_array( $cached ) ) {
			self::$productRowCache[ $productId ] = $cached;

			return $cached;
		}

		$row = Capsule::connection( 'external' )
			->table( 'products_data' )
			->where( 'product_id', $productId )
			->first( array( 'product_id', 'schedule', 'duration', 'image', 'title', 'city_name', 'auto_disable' ) );

		if ( null === $row ) {
			self::$productRowCache[ $productId ] = null;

			return null;
		}

		$data = (array) $row;
		self::$productRowCache[ $productId ] = $data;

		if ( function_exists( 'wp_cache_set' ) ) {
			wp_cache_set( $cacheKey, $data, 'ez_booking', 3600 );
		}

		return $data;
	}

	/**
	 * @param array<string, mixed> $product
	 * @return array<string, mixed>
	 */
	public static function getSansesFromRow( array $product ): array {
		$raw = $product['schedule'] ?? '';
		if ( ! is_string( $raw ) || '' === $raw ) {
			return array();
		}
		if ( isset( self::$scheduleCache[ $raw ] ) ) {
			return self::$scheduleCache[ $raw ];
		}

		$decoded = @unserialize( $raw, array( 'allowed_classes' => false ) );
		if ( false === $decoded ) {
			return array();
		}

		$json = json_decode( json_encode( $decoded ), true );
		$result = is_array( $json ) ? $json : array();
		self::$scheduleCache[ $raw ] = $result;

		return $result;
	}

	public static function getDayType( int $day ): string {
		$day = self::tehranMidnightUnix( $day );
		if ( isset( self::$dayTypeCache[ $day ] ) ) {
			return self::$dayTypeCache[ $day ];
		}

		$cacheKey = "ez_day_type_{$day}";
		$cached   = function_exists( 'wp_cache_get' ) ? wp_cache_get( $cacheKey, 'ez_booking' ) : false;
		if ( is_string( $cached ) ) {
			self::$dayTypeCache[ $day ] = $cached;

			return $cached;
		}

		$row = false;
		if ( function_exists( 'wp_cache_get' ) ) {
			$row = wp_cache_get( 'ez_calendar_data_raw', 'ez_booking' );
		}

		if ( false === $row ) {
			$row = Capsule::connection( 'external' )
				->table( 'calendar_data' )
				->value( 'data' );
			if ( function_exists( 'wp_cache_set' ) && is_string( $row ) ) {
				wp_cache_set( 'ez_calendar_data_raw', $row, 'ez_booking', 3600 );
			}
		}

		if ( ! is_string( $row ) || '' === $row ) {
			self::$dayTypeCache[ $day ] = 'normals';

			return self::$dayTypeCache[ $day ];
		}

		$calendar = @unserialize( $row, array( 'allowed_classes' => false ) );
		if ( false === $calendar ) {
			self::$dayTypeCache[ $day ] = 'normals';

			return self::$dayTypeCache[ $day ];
		}

		$calendarData = json_decode( json_encode( $calendar ), true );
		if ( ! is_array( $calendarData ) ) {
			self::$dayTypeCache[ $day ] = 'normals';

			return self::$dayTypeCache[ $day ];
		}

		$result = 'normals';
		foreach ( explode( ',', (string) ( $calendarData['holidays'] ?? '' ) ) as $calendarDay ) {
			$calendarDay = trim( $calendarDay );
			if ( '' === $calendarDay || ! is_numeric( $calendarDay ) ) {
				continue;
			}
			if ( self::tehranMidnightUnix( (int) $calendarDay ) === $day ) {
				$result = 'holidays';
				break;
			}
		}

		if ( 'normals' === $result ) {
			foreach ( explode( ',', (string) ( $calendarData['closed_days'] ?? '' ) ) as $calendarDay ) {
				$calendarDay = trim( $calendarDay );
				if ( '' === $calendarDay || ! is_numeric( $calendarDay ) ) {
					continue;
				}
				if ( self::tehranMidnightUnix( (int) $calendarDay ) === $day ) {
					$result = 'closed';
					break;
				}
			}
		}

		self::$dayTypeCache[ $day ] = $result;
		if ( function_exists( 'wp_cache_set' ) ) {
			wp_cache_set( $cacheKey, $result, 'ez_booking', 3600 );
		}

		return $result;
	}

	public static function tehranMidnightUnix( int $timestamp ): int {
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
	 * @param array<int, array<string, mixed>> $sansRows
	 * @return list<array{ts: int, sans: array<string, mixed>}>
	 */
	public static function buildDaySlots( int $timeRes, ?string $scheduleKey, array $sansRows ): array {
		$daySlots = array();
		if ( null === $scheduleKey || empty( $sansRows ) ) {
			return $daySlots;
		}

		$timeRes     = TehranTime::tehranMidnightUnix( $timeRes );
		$timeResNext = TehranTime::addTehranDays( $timeRes, 1 );

		foreach ( $sansRows as $sans ) {
			if ( ! is_array( $sans ) || ! isset( $sans['time'] ) || '' === (string) $sans['time'] ) {
				continue;
			}
			$t = (string) $sans['time'];
			$h = (int) substr( $t, 0, 2 );
			$hm = substr( $t, 0, 5 );
			if ( $h >= 8 ) {
				$ts = TehranTime::slotTimestamp( $timeRes, $hm );
			} else {
				$ts = TehranTime::slotTimestamp( $timeResNext, $hm );
			}
			if ( false === $ts ) {
				continue;
			}
			$daySlots[] = array(
				'ts'   => (int) $ts,
				'sans' => $sans,
			);
		}

		usort(
			$daySlots,
			static function ( array $a, array $b ): int {
				return $a['ts'] - $b['ts'];
			}
		);

		return $daySlots;
	}

	private static function normalizeProductImageUrl( string $imagePath ): string {
		$imagePath = trim( $imagePath );
		if ( '' === $imagePath ) {
			return '';
		}

		if ( preg_match( '#^https?://#i', $imagePath ) ) {
			return $imagePath;
		}

		$normalized = str_replace( '\\', '/', $imagePath );
		$normalized = ltrim( $normalized, '/' );
		$needle     = 'wp-content/uploads/';
		$pos        = stripos( $normalized, $needle );
		if ( false !== $pos ) {
			$normalized = substr( $normalized, $pos + strlen( $needle ) );
		}

		$homeUrl = function_exists( 'home_url' ) ? home_url() : '';
		if ( '' === $homeUrl ) {
			return '/wp-content/uploads/' . ltrim( $normalized, '/' );
		}

		return rtrim( $homeUrl, '/' ) . '/wp-content/uploads/' . ltrim( $normalized, '/' );
	}

	public static function formatJalaliTime( int $timestamp ): string {
		if ( function_exists( 'jdate' ) ) {
			return (string) jdate( 'H:i', $timestamp );
		}

		return date( 'H:i', $timestamp );
	}

	private static function ensureJalaliConverter(): void {
		if ( \function_exists( 'jalali_to_gregorian' ) ) {
			return;
		}

		$candidates = array();

		if ( function_exists( 'get_template_directory' ) ) {
			$candidates[] = get_template_directory() . '/ahmadreza/jdate.php';
		}

		$root = defined( 'ABSPATH' ) ? ABSPATH : ( defined( 'EZ_CORE_PATH' ) ? dirname( EZ_CORE_PATH, 3 ) . '/' : '' );
		if ( '' !== $root ) {
			$candidates[] = $root . 'wp-content/themes/escapezoom-v2/ahmadreza/jdate.php';
			$candidates[] = $root . 'web-service/jdf.php';
		}

		foreach ( $candidates as $path ) {
			if ( '' === $path || ! is_readable( $path ) ) {
				continue;
			}
			require_once $path;
			if ( \function_exists( 'jalali_to_gregorian' ) ) {
				return;
			}
		}

		throw new \RuntimeException( 'Jalali date converter unavailable' );
	}
}
