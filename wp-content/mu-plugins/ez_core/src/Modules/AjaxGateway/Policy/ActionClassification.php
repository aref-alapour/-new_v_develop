<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Policy;

/**
 * Gateway action sensitivity classes.
 */
final class ActionClassification
{
	public const CLASS_READ = 'read';

	public const CLASS_WRITE = 'write';

	public const CLASS_WRITE_HTML = 'write_html';

	/** HTML fragment reads — HMAC auth only, no AES on wire (large/slow when encrypted). */
	public const CLASS_READ_HTML = 'read_html';

	/** @var array<string, string> */
	private const MAP = array(
		'booking.product_set_view'   => self::CLASS_READ,
		'booking.sans_day_json'      => self::CLASS_READ,
		'booking.sans_day'           => self::CLASS_READ,
		'booking.sans_week'          => self::CLASS_READ,
		'booking.open_sans'           => self::CLASS_WRITE,
		'booking.close_sans'          => self::CLASS_WRITE,
		'booking.open_all_sanses'     => self::CLASS_WRITE,
		'booking.close_all_sanses'    => self::CLASS_WRITE,
		'booking.sans_management_web' => self::CLASS_READ_HTML,
		'booking.sans_management_data'=> self::CLASS_READ_HTML,
		'booking.check_playing'       => self::CLASS_READ_HTML,
		'booking.game_search'         => self::CLASS_READ_HTML,
		'booking.bulk_date_range'     => self::CLASS_WRITE,
	);

	public static function classify( string $action ): ?string {
		return self::MAP[ $action ] ?? null;
	}

	public static function isWrite( string $action ): bool {
		$class = self::classify( $action );

		return self::CLASS_WRITE === $class || self::CLASS_WRITE_HTML === $class;
	}

	public static function isRead( string $action ): bool {
		$class = self::classify( $action );

		return self::CLASS_READ === $class || self::CLASS_READ_HTML === $class;
	}

	public static function isReadHtml( string $action ): bool {
		return self::CLASS_READ_HTML === self::classify( $action );
	}

	public static function requiresSansPanelAuth( string $action ): bool {
		return self::isWrite( $action ) || self::isReadHtml( $action );
	}
}
