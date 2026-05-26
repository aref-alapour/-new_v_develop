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

	/** @var array<string, string> */
	private const MAP = array(
		'booking.sans_day_json'      => self::CLASS_READ,
		'booking.sans_day'           => self::CLASS_READ,
		'booking.sans_week'          => self::CLASS_READ,
		'booking.open_sans'          => self::CLASS_WRITE,
		'booking.close_sans'         => self::CLASS_WRITE,
		'booking.sans_management_web' => self::CLASS_WRITE_HTML,
	);

	public static function classify( string $action ): ?string {
		return self::MAP[ $action ] ?? null;
	}

	public static function isWrite( string $action ): bool {
		$class = self::classify( $action );

		return self::CLASS_WRITE === $class || self::CLASS_WRITE_HTML === $class;
	}

	public static function isRead( string $action ): bool {
		return self::CLASS_READ === self::classify( $action );
	}
}
