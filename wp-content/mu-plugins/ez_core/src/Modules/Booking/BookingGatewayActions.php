<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Modules\AjaxGateway\ActionRegistry;
use EscapeZoom\Core\Modules\AjaxGateway\GatewayResponse;
use EscapeZoom\Core\Modules\Booking\Actions\GetSansesJsonAction;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;
use EscapeZoom\Core\Modules\Booking\Services\Team\TeamSansBridge;

/**
 * booking.* gateway actions (HTML partials).
 */
final class BookingGatewayActions
{
	public static function register(): void {
		ActionRegistry::register( 'booking.sans_day', array( self::class, 'sansDay' ) );
		ActionRegistry::register( 'booking.sans_day_json', array( self::class, 'sansDayJson' ) );
		ActionRegistry::register( 'booking.sans_week', array( self::class, 'sansWeek' ) );
		ActionRegistry::register( 'booking.sans_management_web', array( self::class, 'sansManagementWeb' ) );
		ActionRegistry::register( 'booking.open_sans', array( self::class, 'openSans' ) );
		ActionRegistry::register( 'booking.close_sans', array( self::class, 'closeSans' ) );
		ActionRegistry::register( 'booking.open_all_sanses', array( self::class, 'openAllSanses' ) );
		ActionRegistry::register( 'booking.close_all_sanses', array( self::class, 'closeAllSanses' ) );
		ActionRegistry::register( 'booking.check_playing', array( self::class, 'checkPlaying' ) );
		ActionRegistry::register( 'booking.game_search', array( self::class, 'gameSearch' ) );
		ActionRegistry::register( 'booking.bulk_date_range', array( self::class, 'bulkDateRange' ) );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function sansDay( array $body ): void {
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		$sessions = BookingAvailabilityService::getSanses( $productId, $dayStartTime, 1 );
		$day      = is_array( $sessions ) ? $sessions : array();
		// Flat list for days=1; if nested (legacy mistake), use first day bucket.
		if ( isset( $day[0] ) && is_array( $day[0] ) && isset( $day[0]['time'] ) ) {
			$dayList = $day;
		} elseif ( isset( $day[0] ) && is_array( $day[0] ) && ! isset( $day[0]['time'] ) ) {
			$dayList = $day[0];
		} else {
			$dayList = $day;
		}

		$html = self::renderPartial(
			'sans-day',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'sessions'       => is_array( $dayList ) ? $dayList : array(),
			)
		);

		self::syncResponseCrypto();
		GatewayResponse::html( $html );
	}

	/**
	 * Legacy flat JSON for single-product BuildSans (no HTML partial).
	 *
	 * @param array<string,mixed> $body
	 */
	public static function sansDayJson( array $body ): void {
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		BookingReadContext::reset();

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			BookingReadContext::setReason( 'invalid_input' );
			BookingReadContext::applyDevHeaders();
			self::syncResponseCrypto();
			GatewayResponse::raw( '[]' );
		}

		$result = ( new GetSansesJsonAction() )->handle( $body );

		BookingReadContext::applyDevHeaders();
		self::syncResponseCrypto();
		GatewayResponse::raw( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) ?: '[]' );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function sansWeek( array $body ): void {
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		if ( ! function_exists( 'ez_render_reserve_week_table' ) ) {
			$weekHelper = function_exists( 'get_template_directory' )
				? get_template_directory() . '/inc/theme/booking-reserve-week.php'
				: '';
			if ( '' !== $weekHelper && is_readable( $weekHelper ) ) {
				require_once $weekHelper;
			}
		}

		$html = function_exists( 'ez_render_reserve_week_table' )
			? ez_render_reserve_week_table( $productId, $dayStartTime )
			: '';

		$trim = ltrim( (string) $html );
		if ( '' === $trim || str_starts_with( $trim, '[' ) || str_starts_with( $trim, '{' ) ) {
			GatewayResponse::html(
				'<p class="text-red-600 p-4">خطا در نمایش سانس‌ها. لطفاً صفحه را رفرش کنید.</p>'
			);
		}

		self::syncResponseCrypto();
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function sansManagementWeb( array $body ): void {
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		try {
			$html = BookingDispatchService::dispatchType(
				'sans_management_web',
				array(
					'product_id'     => $productId,
					'day_start_time' => $dayStartTime,
				)
			);
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::syncResponseCrypto();
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function openSans( array $body ): void {
		self::dispatchJsonSansAction( 'open_sans', $body );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function closeSans( array $body ): void {
		self::dispatchJsonSansAction( 'close_sans', $body );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function openAllSanses( array $body ): void {
		self::dispatchBulkDayAction( 'open_all_sanses', $body );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function closeAllSanses( array $body ): void {
		self::dispatchBulkDayAction( 'close_all_sanses', $body );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function checkPlaying( array $body ): void {
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		try {
			$html = TeamSansBridge::checkPlayingHtml( $productId, $dayStartTime );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::syncResponseCrypto();
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function gameSearch( array $body ): void {
		$term = isset( $body['term'] ) ? trim( (string) $body['term'] ) : '';

		try {
			$html = TeamSansBridge::gameSearchHtml( $term );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::syncResponseCrypto();
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function bulkDateRange( array $body ): void {
		$productId  = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$startDate  = isset( $body['start_date'] ) ? trim( (string) $body['start_date'] ) : '';
		$endDate    = isset( $body['end_date'] ) ? trim( (string) $body['end_date'] ) : '';
		$action     = isset( $body['action'] ) ? trim( (string) $body['action'] ) : '';

		if ( $productId <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product' ), 400 );
		}

		try {
			$result = TeamSansBridge::bulkDateRange( $productId, $startDate, $endDate, $action );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::syncResponseCrypto();
		GatewayResponse::raw( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) ?: '{}' );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	private static function dispatchBulkDayAction( string $type, array $body ): void {
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		$userId = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;

		$raw = BookingDispatchService::dispatchType(
			$type,
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'user_id'        => $userId,
			)
		);

		if ( '' === trim( (string) $raw ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'DISPATCH', 'message' => 'Empty response' ), 500 );
		}

		self::syncResponseCrypto();
		GatewayResponse::raw( (string) $raw );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	private static function dispatchJsonSansAction( string $type, array $body ): void {
		$productId = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$sansTime  = isset( $body['sans_time'] ) ? (int) $body['sans_time'] : 0;

		if ( $productId <= 0 || $sansTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid sans' ), 400 );
		}

		$userId = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;

		$raw = BookingDispatchService::dispatchType(
			$type,
			array(
				'product_id' => $productId,
				'sans_time'  => $sansTime,
				'user_id'    => $userId,
			)
		);

		if ( '' === trim( (string) $raw ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'DISPATCH', 'message' => 'Empty response' ), 500 );
		}

		self::syncResponseCrypto();
		GatewayResponse::raw( (string) $raw );
	}

	private static function syncResponseCrypto(): void {
		GatewayResponse::syncCryptoContextFromGlobals();
	}

	/**
	 * @param array<string,mixed> $vars
	 */
	private static function renderPartial( string $slug, array $vars ): string {
		$themeDir = function_exists( 'get_template_directory' ) ? get_template_directory() : '';
		$path     = $themeDir . '/template/parts/booking/' . $slug . '.php';
		if ( ! is_readable( $path ) ) {
			return '<!-- booking partial missing: ' . esc_html( $slug ) . ' -->';
		}

		ob_start();
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		extract( $vars, EXTR_SKIP );
		include $path;

		return (string) ob_get_clean();
	}
}
