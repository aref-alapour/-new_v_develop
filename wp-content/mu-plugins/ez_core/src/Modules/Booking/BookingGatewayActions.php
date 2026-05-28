<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Modules\AjaxGateway\ActionRegistry;
use EscapeZoom\Core\Modules\AjaxGateway\GatewayResponse;
use EscapeZoom\Core\Modules\Booking\Actions\GetSansesJsonAction;
use EscapeZoom\Core\Modules\Booking\BookingReadContext;
use EscapeZoom\Core\Modules\Booking\Services\BookingCacheInvalidator;
use EscapeZoom\Core\Modules\Booking\Services\Team\SansManagementWebHtmlService;
use EscapeZoom\Core\Modules\Booking\Services\Team\TeamSansBridge;
use EscapeZoom\Core\Modules\Booking\Services\Team\TeamSansWriteService;

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
		$startedAt = microtime( true );
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			self::emitTelemetry( $startedAt );
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

		self::emitTelemetry( $startedAt );
		GatewayResponse::html( $html );
	}

	/**
	 * Legacy flat JSON for single-product BuildSans (no HTML partial).
	 *
	 * @param array<string,mixed> $body
	 */
	public static function sansDayJson( array $body ): void {
		$startedAt = microtime( true );
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		BookingReadContext::reset();

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			BookingReadContext::setReason( 'invalid_input' );
			BookingReadContext::applyDevHeaders();
			self::emitTelemetry( $startedAt );
			GatewayResponse::raw( '[]' );
		}

		$result = ( new GetSansesJsonAction() )->handle( $body );

		BookingReadContext::applyDevHeaders();
		self::emitTelemetry( $startedAt );
		GatewayResponse::raw( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) ?: '[]' );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function sansWeek( array $body ): void {
		$startedAt = microtime( true );
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			self::emitTelemetry( $startedAt );
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

		self::emitTelemetry( $startedAt );
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function sansManagementWeb( array $body ): void {
		$startedAt = microtime( true );
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;
		$format       = isset( $body['format'] ) ? (string) $body['format'] : 'html';

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			self::emitTelemetry( $startedAt );
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		try {
			if ( 'json' === $format ) {
				$data = SansManagementWebHtmlService::getData( $productId, $dayStartTime );
				self::emitTelemetry( $startedAt );
				GatewayResponse::raw( wp_json_encode( $data, JSON_UNESCAPED_UNICODE ) ?: '{}' );
				return;
			}
			$html = SansManagementWebHtmlService::render( $productId, $dayStartTime );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::emitTelemetry( $startedAt );
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
		$startedAt = microtime( true );
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			self::emitTelemetry( $startedAt );
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		try {
			$html = TeamSansBridge::checkPlayingHtml( $productId, $dayStartTime );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::emitTelemetry( $startedAt );
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function gameSearch( array $body ): void {
		$startedAt = microtime( true );
		$term = isset( $body['term'] ) ? trim( (string) $body['term'] ) : '';

		try {
			$html = TeamSansBridge::gameSearchHtml( $term );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		self::emitTelemetry( $startedAt );
		GatewayResponse::html( $html );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	public static function bulkDateRange( array $body ): void {
		$startedAt = microtime( true );
		$productId  = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$startDate  = isset( $body['start_date'] ) ? trim( (string) $body['start_date'] ) : '';
		$endDate    = isset( $body['end_date'] ) ? trim( (string) $body['end_date'] ) : '';
		$action     = isset( $body['action'] ) ? trim( (string) $body['action'] ) : '';

		if ( $productId <= 0 ) {
			self::emitTelemetry( $startedAt );
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product' ), 400 );
		}

		try {
			$result = TeamSansBridge::bulkDateRange( $productId, $startDate, $endDate, $action );
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		BookingCacheInvalidator::invalidateProduct( $productId );

		self::emitTelemetry( $startedAt );
		GatewayResponse::raw( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) ?: '{}' );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	private static function dispatchBulkDayAction( string $type, array $body ): void {
		$startedAt = microtime( true );
		$productId    = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$dayStartTime = isset( $body['day_start_time'] ) ? (int) $body['day_start_time'] : 0;

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			self::emitTelemetry( $startedAt );
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid product or day' ), 400 );
		}

		$userId = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;

		try {
			if ( 'open_all_sanses' === $type ) {
				$result = TeamSansWriteService::openAllSanses( $productId, $dayStartTime );
			} else {
				$result = TeamSansWriteService::closeAllSanses( $productId, $dayStartTime, $userId );
			}
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		BookingCacheInvalidator::invalidateSansDay( $productId, $dayStartTime );

		self::emitTelemetry( $startedAt );
		GatewayResponse::raw( wp_json_encode( $result, JSON_UNESCAPED_UNICODE ) ?: '{}' );
	}

	/**
	 * @param array<string,mixed> $body
	 */
	private static function dispatchJsonSansAction( string $type, array $body ): void {
		$startedAt = microtime( true );
		$productId = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$sansTime  = isset( $body['sans_time'] ) ? (int) $body['sans_time'] : 0;

		if ( $productId <= 0 || $sansTime <= 0 ) {
			self::emitTelemetry( $startedAt );
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid sans' ), 400 );
		}

		$userId = function_exists( 'get_current_user_id' ) ? (int) get_current_user_id() : 0;

		try {
			if ( 'open_sans' === $type ) {
				$payload = TeamSansWriteService::openSans( $productId, $sansTime );
			} else {
				$payload = TeamSansWriteService::closeSans( $productId, $sansTime, $userId );
			}
		} catch ( \Throwable $e ) {
			GatewayResponse::json( false, array(), array( 'code' => 'SERVER', 'message' => $e->getMessage() ), 500 );
		}

		BookingCacheInvalidator::invalidateSansDay(
			$productId,
			TeamSansBridge::tehranMidnightUnix( $sansTime )
		);

		self::emitTelemetry( $startedAt );
		GatewayResponse::raw( wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ) ?: '{}' );
	}

	private static function emitTelemetry( float $startedAt ): void {
		self::syncResponseCrypto();
		if ( ! headers_sent() ) {
			header( 'X-EZ-Booking-Elapsed-Ms: ' . (string) (int) round( ( microtime( true ) - $startedAt ) * 1000 ) );
		}
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
