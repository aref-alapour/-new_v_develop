<?php
/**
 * Plugin Name: EZ Reading settings (visibility only)
 * Description: Settings → Reading shows only search-engine visibility; saves only blog_public from this form.
 *
 * @package Escapezoom
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Core options.php saves every key in the reading group; missing POST fields become null.
 * Only narrow allowed keys when this screen actually POSTs to options.php (avoid breaking other reading integrations).
 */
add_filter(
	'allowed_options',
	static function ( array $allowed ): array {
		$action = isset( $_POST['action'] ) ? sanitize_text_field( wp_unslash( $_POST['action'] ) ) : '';
		$page = isset( $_POST['option_page'] ) ? sanitize_text_field( wp_unslash( $_POST['option_page'] ) ) : '';
		if ( 'update' !== $action || 'reading' !== $page ) {
			return $allowed;
		}
		if ( isset( $allowed['reading'] ) ) {
			$allowed['reading'] = array( 'blog_public' );
		}
		return $allowed;
	},
	99999
);

add_action(
	'load-options-reading.php',
	static function (): void {
		add_action(
			'admin_head',
			static function (): void {
				$screen = get_current_screen();
				if ( ! $screen || 'options-reading' !== $screen->id ) {
					return;
				}
				$screen->remove_help_tabs();
				echo '<style id="ez-reading-settings-admin">table.form-table tbody > tr:not(.option-site-visibility){display:none !important;}</style>';
			},
			1
		);
	}
);
