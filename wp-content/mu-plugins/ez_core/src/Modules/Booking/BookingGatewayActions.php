<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Booking;

use EscapeZoom\Core\Modules\AjaxGateway\ActionRegistry;
use EscapeZoom\Core\Modules\AjaxGateway\GatewayResponse;
use EscapeZoom\Core\Modules\Booking\Actions\GetSansesJsonAction;

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

		if ( $productId <= 0 || $dayStartTime <= 0 ) {
			GatewayResponse::raw( '[]' );
		}

		$result = ( new GetSansesJsonAction() )->handle( $body );

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

		$days = BookingAvailabilityService::getSanses( $productId, $dayStartTime, 7 );

		$html = self::renderPartial(
			'sans-week',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
				'days'           => is_array( $days ) ? $days : array(),
			)
		);

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

		$html = BookingDispatchService::dispatchType(
			'sans_management_web',
			array(
				'product_id'     => $productId,
				'day_start_time' => $dayStartTime,
			)
		);

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
	private static function dispatchJsonSansAction( string $type, array $body ): void {
		$productId = isset( $body['product_id'] ) ? (int) $body['product_id'] : 0;
		$sansTime  = isset( $body['sans_time'] ) ? (int) $body['sans_time'] : 0;

		if ( $productId <= 0 || $sansTime <= 0 ) {
			GatewayResponse::json( false, array(), array( 'code' => 'VALIDATION', 'message' => 'Invalid sans' ), 400 );
		}

		$raw = BookingDispatchService::dispatchType(
			$type,
			array(
				'product_id' => $productId,
				'sans_time'  => $sansTime,
				'user_id'    => isset( $body['user_id'] ) ? (int) $body['user_id'] : 0,
			)
		);

		if ( '' === trim( (string) $raw ) ) {
			GatewayResponse::json( false, array(), array( 'code' => 'DISPATCH', 'message' => 'Empty response' ), 500 );
		}

		GatewayResponse::raw( (string) $raw );
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
