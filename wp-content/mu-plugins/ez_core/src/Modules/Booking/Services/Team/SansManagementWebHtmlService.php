<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking\Services\Team;

use EscapeZoom\Core\Infrastructure\Database\CapsuleManager;
use EscapeZoom\Core\Models\BookingHistory;
use EscapeZoom\Core\Modules\Booking\Infrastructure\Eloquent\EloquentBookingLockRepository;

/**
 * CRM / owner sans grid HTML (ported from web-service/team/sans_management.php sans_management_web).
 */
final class SansManagementWebHtmlService
{
	private const LOCK_TTL_SECONDS = 300;

	public static function render( int $productId, int $dayStartTime ): string {
		if ( ! CapsuleManager::hasExternalConnection() ) {
			throw new \RuntimeException( 'External DB unavailable' );
		}

		self::ensureMojavezedarHelpers();

		$product = TeamSansBridge::getProductRow( $productId );
		if ( null === $product ) {
			return '';
		}

		$dayType     = TeamSansBridge::getDayType( $dayStartTime );
		$sanses      = TeamSansBridge::getSansesFromRow( $product );
		$scheduleKey = ( 'closed' === $dayType || ! isset( $sanses[ $dayType ] ) ) ? null : $dayType;
		$sansForDay  = ( null !== $scheduleKey && isset( $sanses[ $scheduleKey ] ) ) ? $sanses[ $scheduleKey ] : array();
		$daySlots    = TeamSansBridge::buildDaySlots( $dayStartTime, $scheduleKey, $sansForDay );

		$tsList = array_column( $daySlots, 'ts' );
		$orders = array();
		if ( ! empty( $tsList ) ) {
			$rows = BookingHistory::query()
				->where( 'room_id', $productId )
				->whereIn( 'booking_time', $tsList )
				->whereIn( 'status', array( 1, 2 ) )
				->get(
					array(
						'customer_id',
						'wc_order_id',
						'status',
						'booking_time',
						'booked_time',
						'name',
						'level',
						'phone',
						'quantity',
					)
				);

			foreach ( $rows as $row ) {
				$bookingTime = (int) ( $row->booking_time ?? 0 );
				if ( $bookingTime <= 0 ) {
					continue;
				}
				$key       = (string) $bookingTime;
				$candidate = method_exists( $row, 'toArray' ) ? $row->toArray() : (array) $row;
				$current   = $orders[ $key ] ?? null;
				$orders[ $key ] = self::resolveEffectiveSlotRow( is_array( $current ) ? $current : null, $candidate );
			}
		}

		$activeLocks = self::activeLockTimes( $productId, $tsList );

		$reservationData = array();
		foreach ( $daySlots as $slot ) {
			$firstTimeTs = (int) $slot['ts'];
			$orderObj    = $orders[ (string) $firstTimeTs ] ?? null;
			$reserved    = null;
			$status      = 'closeable';

			if ( is_array( $orderObj ) && isset( $orderObj['status'] ) && 1 === (int) $orderObj['status'] ) {
				$status   = 'reserved';
				$reserved = array(
					'customer_id' => (int) ( $orderObj['customer_id'] ?? 0 ),
					'name'        => (string) ( $orderObj['name'] ?? '' ),
					'level'       => (int) ( $orderObj['level'] ?? 0 ),
					'phone'       => (string) ( $orderObj['phone'] ?? '' ),
					'quantity'    => (int) ( $orderObj['quantity'] ?? 0 ),
					'order_id'    => (int) ( $orderObj['wc_order_id'] ?? 0 ),
				);
			} elseif ( is_array( $orderObj ) && isset( $orderObj['status'] ) && 2 === (int) $orderObj['status'] ) {
				$status = 'openable';
			} elseif ( isset( $activeLocks[ $firstTimeTs ] ) ) {
				$status = 'reserving';
			}

			$reservationData[] = array(
				'time'          => $firstTimeTs,
				'status'        => $status,
				'reserved_data' => $reserved,
			);
		}

		$mojMap = self::buildMojavezedarMap( $reservationData );

		$totalChangeable = 0;
		$totalClosed     = 0;
		foreach ( $reservationData as $row ) {
			if ( 'closeable' === $row['status'] || 'openable' === $row['status'] ) {
				++$totalChangeable;
				if ( 'openable' === $row['status'] ) {
					++$totalClosed;
				}
			}
		}

		$isAllClosed  = $totalChangeable > 0 && $totalChangeable === $totalClosed;
		$closeChecked = $isAllClosed ? 'checked' : '';
		$openChecked  = ! $isAllClosed ? 'checked' : '';

		$html  = self::renderBulkRadioTemplate( $isAllClosed, $openChecked, $closeChecked );
		$html .= self::renderSlots( $reservationData, $productId, $dayStartTime, $mojMap );

		return $html;
	}

	/**
	 * @param list<array{time: int, status: string, reserved_data: array<string, mixed>|null}> $reservationData
	 * @return array<int, bool>
	 */
	private static function buildMojavezedarMap( array $reservationData ): array {
		$ids = array();
		foreach ( $reservationData as $row ) {
			if ( 'reserved' !== $row['status'] || empty( $row['reserved_data']['customer_id'] ) ) {
				continue;
			}
			$cid = (int) $row['reserved_data']['customer_id'];
			if ( $cid > 0 ) {
				$ids[] = $cid;
			}
		}

		$ids = array_values( array_unique( $ids ) );
		if ( array() === $ids ) {
			return array();
		}

		return self::resolveMojavezedarFlags( $ids );
	}

	private static function renderBulkRadioTemplate( bool $isAllClosed, string $openChecked, string $closeChecked ): string {
		$openLabelClass  = ! $isAllClosed ? 'text-gray-900' : 'text-[#90A1B9]';
		$closeLabelClass = $isAllClosed ? 'text-gray-900' : 'text-[#90A1B9]';

		return '<div id="radio-toggle-template" style="display: none;">'
			. '<div class="flex items-center justify-center gap-6 w-full mb-4">'
			. '<label class="flex items-center gap-2 cursor-pointer text-sm font-bold ' . esc_attr( $openLabelClass ) . '">'
			. '<input type="radio" name="bulk_action" value="open_all" class="form-radio text-[#f97316] focus:ring-[#f97316] w-5 h-5" ' . $openChecked . '>'
			. '<span>باز کردن همه سانس ها</span></label>'
			. '<label class="flex items-center gap-2 cursor-pointer text-sm font-bold ' . esc_attr( $closeLabelClass ) . '">'
			. '<input type="radio" name="bulk_action" value="close_all" class="form-radio text-[#9ca3af] focus:ring-[#9ca3af] w-5 h-5" ' . $closeChecked . '>'
			. '<span>بستن همه سانس ها</span></label>'
			. '</div></div>';
	}

	/**
	 * @param list<array{time: int, status: string, reserved_data: array<string, mixed>|null}> $reservationData
	 * @param array<int, bool> $mojMap
	 */
	private static function renderSlots( array $reservationData, int $productId, int $dayStartTime, array $mojMap ): string {
		$html = '';

		foreach ( $reservationData as $data ) {
			$time   = (int) $data['time'];
			$status = (string) $data['status'];
			$timeLbl = esc_html( TeamSansBridge::formatJalaliTime( $time ) );

			if ( 'reserved' === $status && is_array( $data['reserved_data'] ) ) {
				$rd          = $data['reserved_data'];
				$ezSansCid   = (int) ( $rd['customer_id'] ?? 0 );
				$ezSansMoj   = $ezSansCid > 0 && ! empty( $mojMap[ $ezSansCid ] );
				$theme       = self::resolveTheme( $rd, $ezSansMoj );
				$userInfoJson = wp_json_encode(
					array(
						'customer_id' => $rd['customer_id'] ?? 0,
						'name'        => $rd['name'] ?? '',
						'level_title' => $theme['text'],
						'level_color' => $theme['color'],
						'phone'       => $rd['phone'] ?? '',
						'order_id'    => $rd['order_id'] ?? 0,
						'date'        => $rd['name'] ?? '',
						'quantity'    => $rd['quantity'] ?? 0,
					),
					JSON_UNESCAPED_UNICODE
				);
				$userInfoAttr = htmlspecialchars( (string) $userInfoJson, ENT_QUOTES, 'UTF-8' );
				$slotPre     = $ezSansMoj ? self::mojavezedarBadgeInnerHtml() : '';
				$slotAttr    = $ezSansMoj ? ' data-ez-mojavezedar="1"' : '';
				$name        = esc_html( (string) ( $rd['name'] ?? '' ) );

				$html .= '<div class="rounded-xl border border-orangee bg-[#F1F5F9] px-4 py-2.5 shadow-13 openModalInfo cursor-pointer" style="box-shadow: 0px 1px 0px 0px #FF6900;" data-user-info=\'' . $userInfoAttr . '\'>';
				$html .= '<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold"> ' . $timeLbl . ' </bdo>';
				$html .= '<div class="space-y-2.5 mt-3"><div class="flex items-center justify-between gap-7 bg-white h-[39px] rounded-lg px-3 py-2">';
				$html .= '<div class="flex items-center gap-2 min-w-0 flex-wrap">';
				$html .= '<span class="text-xs font-bold text-navyBlue">' . $name . '</span>';
				$html .= '<span class="ez-sans-badge-slot inline-flex flex-wrap shrink-0" data-ez-customer="' . esc_attr( (string) $ezSansCid ) . '"' . $slotAttr . '>' . $slotPre . '</span>';
				$html .= '</div>';
				$html .= self::reservedEyeSvg();
				$html .= '</div></div></div>';
				continue;
			}

			if ( 'closeable' === $status ) {
				$html .= '<div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">';
				$html .= '<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold">' . $timeLbl . '</bdo>';
				$html .= '<button type="button" data-room-action="close" data-product="' . esc_attr( (string) $productId ) . '" data-timestamp="' . esc_attr( $time . '.' . $dayStartTime ) . '" class="toggle-btn h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#04B968] text-white">باز</button>';
				$html .= '</div>';
				continue;
			}

			if ( 'openable' === $status ) {
				$html .= '<div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13">';
				$html .= '<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold">' . $timeLbl . '</bdo>';
				$html .= '<button type="button" data-room-action="open" data-product="' . esc_attr( (string) $productId ) . '" data-timestamp="' . esc_attr( $time . '.' . $dayStartTime ) . '" class="toggle-btn h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#E2E8F0] text-black">بسته</button>';
				$html .= '</div>';
				continue;
			}

			if ( 'reserving' === $status ) {
				$html .= '<div class="rounded-xl border border-[#DBE2EA] bg-white p-2.5 shadow-13 opacity-90">';
				$html .= '<bdo dir="ltr" class="text-2xl block text-center font-yekan-bold">' . $timeLbl . '</bdo>';
				$html .= '<button type="button" disabled class="h-10 w-full rounded-lg font-yekan-bold mt-3 bg-[#EDA10D] text-white cursor-wait">در حال رزرو</button>';
				$html .= '</div>';
			}
		}

		return $html;
	}

	/**
	 * @param array<string, mixed> $reservedData
	 * @return array{color: string, text: string}
	 */
	private static function resolveTheme( array $reservedData, bool $isMoj ): array {
		if ( $isMoj ) {
			return array(
				'color' => self::mojavezedarBorderColorToken(),
				'text'  => self::mojavezedarLabelText(),
			);
		}

		$level = (int) ( $reservedData['level'] ?? 4 );
		if ( 1 === $level ) {
			return array( 'color' => '[#858585]', 'text' => 'تازه وارد' );
		}
		if ( 2 === $level ) {
			return array( 'color' => '[#252728]', 'text' => 'نوپا' );
		}
		if ( 3 === $level ) {
			return array( 'color' => '[#00B2FF]', 'text' => 'با تجربه' );
		}

		return array( 'color' => 'primary-500', 'text' => 'کارکشته' );
	}

	private static function reservedEyeSvg(): string {
		return '<svg xmlns="http://www.w3.org/2000/svg" class="mx-0" width="19" height="19" viewBox="0 0 19 19" fill="none">'
			. '<rect x="0.5" y="0.5" width="18" height="18" rx="4" fill="#FF6900" />'
			. '<path d="M14.9397 8.95573C15.113 9.19853 15.1996 9.3205 15.1996 9.50003C15.1996 9.68013 15.113 9.80153 14.9397 10.0443C14.1612 11.1363 12.1727 13.4896 9.5002 13.4896C6.82717 13.4896 4.83921 11.1358 4.06067 10.0443C3.88741 9.80153 3.80078 9.67956 3.80078 9.50003C3.80078 9.31993 3.88741 9.19853 4.06067 8.95573C4.83921 7.86373 6.82774 5.51044 9.5002 5.51044C12.1732 5.51044 14.1612 7.8643 14.9397 8.95573Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />'
			. '<path d="M11.2107 9.49999C11.2107 9.04651 11.0305 8.61161 10.7099 8.29096C10.3892 7.9703 9.95431 7.79016 9.50084 7.79016C9.04737 7.79016 8.61247 7.9703 8.29181 8.29096C7.97116 8.61161 7.79102 9.04651 7.79102 9.49999C7.79102 9.95346 7.97116 10.3884 8.29181 10.709C8.61247 11.0297 9.04737 11.2098 9.50084 11.2098C9.95431 11.2098 10.3892 11.0297 10.7099 10.709C11.0305 10.3884 11.2107 9.95346 11.2107 9.49999Z" stroke="white" stroke-linecap="round" stroke-linejoin="round" />'
			. '</svg>';
	}

	/**
	 * @param list<int> $tsList
	 * @return array<int, true>
	 */
	private static function activeLockTimes( int $productId, array $tsList ): array {
		if ( $productId <= 0 || array() === $tsList ) {
			return array();
		}

		$repo  = new EloquentBookingLockRepository();
		$locks = $repo->forProductTimes( $productId, $tsList );
		$now   = time();
		$out   = array();
		$tsSet = array_fill_keys( array_map( 'intval', $tsList ), true );

		foreach ( $locks as $lock ) {
			$bookingTime = (int) ( $lock->booking_time ?? 0 );
			if ( $bookingTime <= 0 || ! isset( $tsSet[ $bookingTime ] ) ) {
				continue;
			}

			$lockTime = (int) ( $lock->lock_time ?? 0 );
			if ( $lockTime > 0 && $now < $lockTime + self::LOCK_TTL_SECONDS ) {
				$out[ $bookingTime ] = true;
			}
		}

		return $out;
	}

	private static function ensureMojavezedarHelpers(): void {
		// Legacy helper include removed; logic is internalized in this service.
	}

	/**
	 * @param array<string,mixed>|null $current
	 * @param array<string,mixed> $candidate
	 * @return array<string,mixed>
	 */
	private static function resolveEffectiveSlotRow( ?array $current, array $candidate ): array {
		if ( null === $current ) {
			return $candidate;
		}

		$currentStatus   = (int) ( $current['status'] ?? 0 );
		$candidateStatus = (int) ( $candidate['status'] ?? 0 );

		// Reserved should always dominate closed for the same slot.
		if ( $candidateStatus !== $currentStatus ) {
			if ( 1 === $candidateStatus ) {
				return $candidate;
			}
			if ( 1 === $currentStatus ) {
				return $current;
			}
		}

		$currentBooked   = (int) ( $current['booked_time'] ?? 0 );
		$candidateBooked = (int) ( $candidate['booked_time'] ?? 0 );
		if ( $candidateBooked > $currentBooked ) {
			return $candidate;
		}
		if ( $candidateBooked < $currentBooked ) {
			return $current;
		}

		// Stable tie-breaker.
		$currentOrder   = (int) ( $current['wc_order_id'] ?? 0 );
		$candidateOrder = (int) ( $candidate['wc_order_id'] ?? 0 );

		return $candidateOrder >= $currentOrder ? $candidate : $current;
	}

	private static function mojavezedarLabelText(): string {
		return 'مجموعه دار';
	}

	private static function mojavezedarBorderColorToken(): string {
		return '[#6D28D9]';
	}

	private static function mojavezedarBadgeInnerHtml(): string {
		$label = self::mojavezedarLabelText();

		return '<span class="inline-flex items-center leading-6 px-3 rounded-full gap-2 text-xs font-bold" style="color:#6D28D9;background:rgba(109,40,217,0.14);">'
			. htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' )
			. '</span>';
	}

	/**
	 * @param list<int> $userIds
	 * @return array<int, bool>
	 */
	private static function resolveMojavezedarFlags( array $userIds ): array {
		$userIds = array_values(
			array_unique(
				array_filter(
					array_map( 'intval', $userIds ),
					static fn( int $v ): bool => $v > 0
				)
			)
		);
		$out = array();
		foreach ( $userIds as $uid ) {
			$out[ $uid ] = false;
		}
		if ( array() === $userIds ) {
			return $out;
		}

		global $wpdb;
		if ( ! isset( $wpdb ) || ! is_object( $wpdb ) || ! isset( $wpdb->users, $wpdb->usermeta, $wpdb->postmeta, $wpdb->posts, $wpdb->prefix ) ) {
			return $out;
		}

		$idsSql   = implode( ',', array_map( 'intval', $userIds ) );
		$capKey   = $wpdb->prefix . 'capabilities';
		$capRows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT user_id, meta_value FROM {$wpdb->usermeta} WHERE meta_key = %s AND user_id IN ({$idsSql})",
				$capKey
			),
			ARRAY_A
		);
		if ( ! is_array( $capRows ) || array() === $capRows ) {
			return $out;
		}

		$compilers = array();
		foreach ( $capRows as $row ) {
			$caps = isset( $row['meta_value'] ) ? @unserialize( (string) $row['meta_value'] ) : array();
			if ( is_array( $caps ) && ! empty( $caps['compiler'] ) ) {
				$compilers[] = (int) ( $row['user_id'] ?? 0 );
			}
		}
		$compilers = array_values( array_unique( array_filter( $compilers ) ) );
		if ( array() === $compilers ) {
			return $out;
		}

		$compilersSql = "'" . implode( "','", array_map( 'esc_sql', array_map( 'strval', $compilers ) ) ) . "'";
		$collectionRows = $wpdb->get_col(
			"SELECT DISTINCT pm_e.meta_value AS uid
			FROM {$wpdb->postmeta} pm_e
			INNER JOIN {$wpdb->posts} p ON p.ID = pm_e.post_id AND p.post_type = 'product'
			INNER JOIN {$wpdb->postmeta} pm_s ON pm_s.post_id = p.ID
				AND pm_s.meta_key = 'product_state'
				AND pm_s.meta_value IN ('active','updated')
			WHERE pm_e.meta_key = 'user_ebtal'
				AND pm_e.meta_value IN ({$compilersSql})"
		);
		$withCollection = array_map( 'intval', is_array( $collectionRows ) ? $collectionRows : array() );

		foreach ( $userIds as $uid ) {
			if ( in_array( $uid, $compilers, true ) && in_array( $uid, $withCollection, true ) ) {
				$out[ $uid ] = true;
			}
		}

		return $out;
	}
}
