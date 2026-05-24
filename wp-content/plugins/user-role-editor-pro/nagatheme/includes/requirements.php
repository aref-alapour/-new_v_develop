<?php

namespace NagaTheme\UserRoleEditorPro\Includes;

final class Requirements {
	private static $instance;

	private $errors         = [];
	private $warnings       = [];
	private $loader_version = 0;
	private $translations   = [];

	const PHP_VERSION_LOW     = 'php_version_too_low';
	const PHP_VERSION_HIGH    = 'php_version_too_high';
	const LOADER_MISSING      = 'loader_not_installed';
	const LOADER_VERSION_LOW  = 'loader_version_too_low';
	const LOADER_VERSION_HIGH = 'loader_version_too_high';

	const MIN_PHP = '7.4';
	const MAX_PHP = '8.4';

	const MIN_LOADER = '12';
	const MAX_LOADER = false;

	const NAME = 'User Role Editor Pro';

	private function __construct() {
		$this->set_translations();
		$this->check_requirements();
	}

	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public static function run_compatibility_check() {
		$instance = self::get_instance();

		if ( $instance->has_errors() ) {
			add_action( 'admin_notices', [ $instance, 'display_errors' ] );
			return false;
		}

		if ( $instance->has_warnings() ) {
			add_action( 'admin_notices', [ $instance, 'display_warnings' ] );
		}

		return true;
	}

	public function has_errors() {
		return ! empty( $this->errors );
	}

	public function has_warnings() {
		return ! empty( $this->warnings );
	}

	public function display_errors() {
		foreach ( $this->errors as $error ) {
			$this->render_notice( $this->get_message( $error ), 'error' );
		}
	}

	public function display_warnings() {
		foreach ( $this->warnings as $warning ) {
			$this->render_notice( $this->get_message( $warning ), 'warning' );
		}
	}

	private function check_requirements() {
		$this->check_php_version();
		$this->check_loader_version();
	}

	private function check_php_version() {
		if ( version_compare( PHP_VERSION, self::MIN_PHP, '<' ) ) {
			$this->errors[] = self::PHP_VERSION_LOW;
		} elseif ( version_compare( PHP_VERSION, self::MAX_PHP, '>' ) ) {
			$this->errors[] = self::PHP_VERSION_HIGH;
		}
	}

	private function check_loader_version() {
		if ( ! extension_loaded( 'ionCube Loader' ) ) {
			$this->errors[] = self::LOADER_MISSING;
		} else {
			/** @disregard P1010 Undefined function */
			$this->loader_version = ioncube_loader_version();

			if ( version_compare( $this->loader_version, self::MIN_LOADER, '<' ) ) {
				$this->errors[] = self::LOADER_VERSION_LOW;
			} elseif ( self::MAX_LOADER && version_compare( $this->loader_version, self::MAX_LOADER, '>' ) ) {
				$this->errors[] = self::LOADER_VERSION_HIGH;
			}
		}
	}

	private function render_notice( $message, $type = 'error' ) {
		echo '<div class="notice notice-' . esc_attr( $type ) . '"><p>' . $message . '</p></div>';
	}

	private function get_message( $key ) {
		$messages = [
			self::PHP_VERSION_LOW     => sprintf(
				$this->get_text( self::PHP_VERSION_LOW ),
				self::NAME,
				self::MIN_PHP,
				PHP_VERSION
			),

			self::PHP_VERSION_HIGH    => sprintf(
				$this->get_text( self::PHP_VERSION_HIGH ),
				self::NAME,
				self::MAX_PHP,
				PHP_VERSION
			),

			self::LOADER_MISSING      => sprintf(
				wp_kses(
					$this->get_text( self::LOADER_MISSING ),
					[
						'a' => [
							'href'   => [],
							'target' => [],
						],
					]
				),
				self::NAME,
				'https://nagatheme.com/how-to-install-ioncube-and-sourceguardian-loader/'
			),

			self::LOADER_VERSION_LOW  => sprintf(
				$this->get_text( self::LOADER_VERSION_LOW ),
				self::NAME,
				self::MIN_LOADER,
				$this->loader_version
			),

			self::LOADER_VERSION_HIGH => sprintf(
				$this->get_text( self::LOADER_VERSION_HIGH ),
				self::NAME,
				self::MAX_LOADER,
				$this->loader_version
			),

		];

		return $messages[ $key ] ?? '';
	}

	private function get_text( $key, $locale = null ) {
		$locale = $locale ?? get_locale();
		$lang   = substr( $locale, 0, 2 );
		return isset( $this->translations[ $lang ][ $key ] ) ? $this->translations[ $lang ][ $key ] : $this->translations['en'][ $key ];
	}

	private function set_translations() {
		$this->translations = [
			'en' => [
				/* translators: %1$s plugin name, %2$s required php version, %3$s current php version. */
				self::PHP_VERSION_LOW     => '%1$s: PHP version %2$s or higher is required. You are currently using PHP %3$s. Please upgrade to a supported PHP version!',
				/* translators: %1$s plugin name, %2$s max supported php version, %3$s current php version. */
				self::PHP_VERSION_HIGH    => '%1$s: This product is not compatible with PHP versions above %2$s. You are currently using version %3$s. Please downgrade your PHP to a version less than %2$s.',
				/* translators: %1$s plugin name, %2$s ioncube guide URI */
				self::LOADER_MISSING      => '%1$s: ionCube loader is not installed. For installation guide and more info, please refer to our <a href="%2$s" target="_blank">documentation</a>.',
				/* translators: %1$s plugin name, %2$s required ioncube loader version, %3$s current ioncube loader version. */
				self::LOADER_VERSION_LOW  => '%1$s: ionCube Loader version %2$s or higher is required. You are currently using version %3$s. Please upgrade to a supported ionCube Loader version!',
				/* translators: %1$s plugin name, %2$s max supported ioncube loader version, %3$s current ioncube loader version. */
				self::LOADER_VERSION_HIGH => '%1$s: This product is not compatible with ionCube Loader versions above %2$s. You are currently using version %3$s. Please downgrade your ionCube Loader to a version less than %2$s.',
			],
			'fa' => [
				self::PHP_VERSION_LOW     => '%1$s: این محصول جهت اجرا شدن به PHP نسخه %2$s یا بالاتر نیاز دارد. شما در حال حاضر از نسخه %3$s PHP استفاده می‌کنید. لطفاً نسخه PHP سرویس میزبانی خود را به یک نسخه پشتیبانی شده ارتقاء دهید!',
				self::PHP_VERSION_HIGH    => '%1$s: این محصول با PHP نسخه %2$s به بعد سازگار نمی‌باشد. شما در حال حاضر از نسخه %3$s استفاده می‌کنید. لطفاً PHP سرویس میزبانی خود را به یک نسخه پایین‌تر از %2$s کاهش دهید.',
				self::LOADER_MISSING      => '%1$s: ماژول آیون‌کیوب لودر بر روی سرویس میزبانی شما نصب نشده است و این محصول جهت اجرا شدن به آن نیاز دارد. لطفاً جهت آموزش نصب و دریافت اطلاعات بیشتر، به <a href="%2$s" target="_blank">مقاله اختصاصی</a> ما مراجعه کنید.',
				self::LOADER_VERSION_LOW  => '%1$s: این محصول جهت اجرا شدن به ماژول آیون‌کیوب لودر نسخه %2$s یا بالاتر نیاز دارد. شما در حال حاضر از نسخه %3$s استفاده می‌کنید. لطفاً ماژول آیون‌کیوب لودر سرویس میزبانی خود را به یک نسخه پشتیبانی شده ارتقاء دهید!',
				self::LOADER_VERSION_HIGH => '%1$s: این محصول با ماژول آیون‌کیوب لودر نسخه %2$s به بعد سازگار نمی‌باشد. شما در حال حاضر از نسخه %3$s استفاده می‌کنید. لطفاً ماژول آیون‌کیوب لودر سرویس میزبانی خود را به یک نسخه پایین‌تر از %2$s کاهش دهید.',
			],
		];
	}
}
