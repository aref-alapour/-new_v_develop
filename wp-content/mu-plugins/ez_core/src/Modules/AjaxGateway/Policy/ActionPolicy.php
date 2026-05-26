<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\AjaxGateway\Policy;

/**
 * Authorize gateway actions by client_kind and light vs full bootstrap.
 */
final class ActionPolicy
{
	public const ERR_FORBIDDEN_ACTION = 'FORBIDDEN_ACTION';

	public const ERR_AUTH_REQUIRED = 'AUTH_REQUIRED';

	/**
	 * @return string|null Error code or null when allowed.
	 */
	public static function authorize( string $action, string $clientKind ): ?string {
		if ( null === ActionClassification::classify( $action ) ) {
			return self::ERR_FORBIDDEN_ACTION;
		}

		$isLight = defined( 'EZ_AJAX_LIGHT_GATEWAY' ) && EZ_AJAX_LIGHT_GATEWAY;
		$isWrite = ActionClassification::isWrite( $action );

		if ( $isLight && $isWrite ) {
			return self::ERR_FORBIDDEN_ACTION;
		}

		if ( ! $isWrite ) {
			return null;
		}

		if ( 'web-anon' === $clientKind ) {
			return self::ERR_FORBIDDEN_ACTION;
		}

		if ( ! self::isAuthenticatedClientKind( $clientKind ) ) {
			return self::ERR_FORBIDDEN_ACTION;
		}

		if ( ! self::isUserLoggedIn() ) {
			return self::ERR_AUTH_REQUIRED;
		}

		return null;
	}

	private static function isAuthenticatedClientKind( string $clientKind ): bool {
		return in_array( $clientKind, array( 'web-user', 'web-team' ), true );
	}

	private static function isUserLoggedIn(): bool {
		if ( ! function_exists( 'is_user_logged_in' ) ) {
			return false;
		}

		return is_user_logged_in();
	}
}
