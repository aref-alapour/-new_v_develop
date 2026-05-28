<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Models\BookingHistory;
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
				->get( array( 'status', 'booking_time', 'name', 'level', 'phone', 'quantity' ) );

			foreach ( $rows as $row ) {
				$orders[ (string) (int) $row->booking_time ] = (array) $row;
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
		self::assertExternalDb();

		$term = trim( $term );
		if ( '' === $term ) {
			return '';
		}

		$homeUrl = function_exists( 'home_url' ) ? home_url() : '';
		$parts   = preg_split( '/\s+/', $term ) ?: array();
		$products = array();

		if ( count( $parts ) === 2 && '' !== $parts[0] ) {
			$res1 = self::searchProducts( $parts[0] );
			$res2 = self::searchProducts( $parts[1] );
			$temp = array();
			foreach ( $res1 as $row ) {
				$temp[ (int) $row['product_id'] ] = $row;
			}
			$ids1 = array_keys( $temp );
			$ids2 = array();
			foreach ( $res2 as $row ) {
				$ids2[] = (int) $row['product_id'];
				$temp[ (int) $row['product_id'] ] = $row;
			}
			if ( '' !== $parts[1] ) {
				foreach ( array_intersect( $ids1, $ids2 ) as $pid ) {
					$products[] = $temp[ $pid ];
				}
			} else {
				$products = array_values( $temp );
			}
		} else {
			$products = self::searchProducts( $term );
		}

		$html = '';
		$slice = array_slice( $products, 0, 50 );
		foreach ( $slice as $product ) {
			$pid   = (int) $product['product_id'];
			$name  = esc_html( (string) ( $product['title'] ?? '' ) );
			$city  = esc_html( (string) ( $product['city_name'] ?? '' ) );
			$image = esc_url( $homeUrl . '/wp-content/uploads/' . ltrim( (string) ( $product['image'] ?? '' ), '/' ) );
			$html .= '<a href="javascript:;" data-id="' . esc_attr( (string) $pid ) . '" data-title="' . $name . '" class="team_sans_game_search_item flex items-center gap-x-2 py-2">';
			$html .= '<img src="' . $image . '" alt="" class="h-10 w-7.5 rounded">';
			$html .= '<span>' . $name . ' (' . $city . ')</span></a>';
		}

		return $html;
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

			$startGreg = jalali_to_gregorian( (int) $startParts[0], (int) $startParts[1], (int) $startParts[2] );
			$endGreg   = jalali_to_gregorian( (int) $endParts[0], (int) $endParts[1], (int) $endParts[2] );
			$startTs   = strtotime( sprintf( '%04d-%02d-%02d 00:00:00', $startGreg[0], $startGreg[1], $startGreg[2] ) );
			$endTs     = strtotime( sprintf( '%04d-%02d-%02d 00:00:00', $endGreg[0], $endGreg[1], $endGreg[2] ) );

			if ( false === $startTs || false === $endTs || $startTs > $endTs ) {
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
			$userId     = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;
			$now        = time();
			$processed  = 0;

			$currentDay = $startTs;
			while ( $currentDay <= $endTs ) {
				$dayType = self::getDayType( $currentDay );
				if ( ! isset( $sansesData[ $dayType ] ) || ! is_array( $sansesData[ $dayType ] ) ) {
					$currentDay = strtotime( '+1 day', $currentDay );
					continue;
				}

				$scheduleKey = $dayType;
				$daySlots    = self::buildDaySlots( $currentDay, $scheduleKey, $sansesData[ $dayType ] );

				foreach ( $daySlots as $slot ) {
					$sansTimeTs = (int) $slot['ts'];

					if ( 'close' === $action ) {
						$existing = BookingHistory::query()
							->where( 'room_id', $productId )
							->where( 'booking_time', $sansTimeTs )
							->first( array( 'status' ) );

						if ( null !== $existing ) {
							$st = (int) $existing->status;
							if ( 1 === $st || 2 === $st ) {
								continue;
							}
						}

						BookingHistory::query()->insert(
							array(
								'customer_id'  => $userId,
								'wc_order_id'  => null,
								'status'       => 2,
								'room_id'      => $productId,
								'booking_time' => $sansTimeTs,
								'booked_time'  => $now,
								'name'         => null,
								'phone'        => null,
								'quantity'     => 0,
							)
						);
						++$processed;
					} else {
						$deleted = BookingHistory::query()
							->where( 'room_id', $productId )
							->where( 'booking_time', $sansTimeTs )
							->where( 'status', 2 )
							->delete();
						$processed += (int) $deleted;
					}
				}

				$currentDay = strtotime( '+1 day', $currentDay );
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

		$row = Capsule::connection( 'external' )
			->table( 'products_data' )
			->where( 'product_id', $productId )
			->first( array( 'product_id', 'schedule', 'duration', 'image', 'title', 'city_name' ) );

		if ( null === $row ) {
			self::$productRowCache[ $productId ] = null;
			return null;
		}

		self::$productRowCache[ $productId ] = (array) $row;

		return self::$productRowCache[ $productId ];
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

		$row = Capsule::connection( 'external' )
			->table( 'calendar_data' )
			->value( 'data' );

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

		foreach ( explode( ',', (string) ( $calendarData['holidays'] ?? '' ) ) as $calendarDay ) {
			$calendarDay = trim( $calendarDay );
			if ( '' === $calendarDay || ! is_numeric( $calendarDay ) ) {
				continue;
			}
			if ( self::tehranMidnightUnix( (int) $calendarDay ) === $day ) {
				self::$dayTypeCache[ $day ] = 'holidays';
				return self::$dayTypeCache[ $day ];
			}
		}

		foreach ( explode( ',', (string) ( $calendarData['closed_days'] ?? '' ) ) as $calendarDay ) {
			$calendarDay = trim( $calendarDay );
			if ( '' === $calendarDay || ! is_numeric( $calendarDay ) ) {
				continue;
			}
			if ( self::tehranMidnightUnix( (int) $calendarDay ) === $day ) {
				self::$dayTypeCache[ $day ] = 'closed';
				return self::$dayTypeCache[ $day ];
			}
		}
		self::$dayTypeCache[ $day ] = 'normals';
		return self::$dayTypeCache[ $day ];
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

		$timeRes     = (int) $timeRes;
		$timeResNext = $timeRes + 86400;

		foreach ( $sansRows as $sans ) {
			if ( ! is_array( $sans ) || ! isset( $sans['time'] ) || '' === (string) $sans['time'] ) {
				continue;
			}
			$t = (string) $sans['time'];
			$h = (int) substr( $t, 0, 2 );
			if ( $h >= 8 ) {
				$ts = strtotime( date( 'Y-m-d', $timeRes ) . ' ' . $t );
			} else {
				$ts = strtotime( date( 'Y-m-d', $timeResNext ) . ' ' . $t );
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

	/**
	 * @return list<array<string, mixed>>
	 */
	private static function searchProducts( string $term ): array {
		$term = trim( $term );
		if ( '' === $term ) {
			return array();
		}

		$like = '%' . addcslashes( $term, '%_\\' ) . '%';

		$rows = Capsule::connection( 'external' )
			->table( 'products_data' )
			->where( 'title', 'LIKE', $like )
			->limit( 100 )
			->get();

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = (array) $row;
		}

		return $out;
	}

	public static function formatJalaliTime( int $timestamp ): string {
		if ( function_exists( 'jdate' ) ) {
			return (string) jdate( 'H:i', $timestamp );
		}

		return date( 'H:i', $timestamp );
	}

	private static function ensureJalaliConverter(): void {
		if ( function_exists( 'jalali_to_gregorian' ) ) {
			return;
		}

		$jdf = defined( 'ABSPATH' ) ? ABSPATH . 'web-service/jdf.php' : '';
		if ( '' !== $jdf && is_readable( $jdf ) ) {
			require_once $jdf;
		}
	}
}
