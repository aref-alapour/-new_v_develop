<?php
/**
 * Admin reference page: endpoint → label → preview → copyable SVG snippet.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action(
	'admin_menu',
	static function () {
		add_theme_page(
			__( 'آیکن منوی حساب', 'escapezoom-v2' ),
			__( 'آیکن منوی حساب', 'escapezoom-v2' ),
			'manage_options',
			'ez-account-nav-icons',
			'ez_render_account_nav_icons_admin_page'
		);
	}
);

/**
 * @return void
 */
function ez_render_account_nav_icons_admin_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'مجوز دسترسی ندارید.', 'escapezoom-v2' ) );
	}

	$registry = ez_account_nav_icons_registry();
	$menu     = function_exists( 'wc_get_account_menu_items' ) ? wc_get_account_menu_items() : array();

	wp_enqueue_style( 'wp-admin' );

	echo '<div class="wrap" dir="rtl" style="font-family: inherit;">';
	echo '<h1>' . esc_html__( 'آیکن‌های منوی پنل کاربر (WooCommerce My Account)', 'escapezoom-v2' ) . '</h1>';
	echo '<p style="max-width: 720px;">' . esc_html__( 'همهٔ آیکن‌ها از stroke با currentColor استفاده می‌کنند و رنگ را از لینک والد می‌گیرند. برای عوض کردن شکل هر آیتم، فایل زیر را ویرایش کنید:', 'escapezoom-v2' ) . ' <code>inc/theme/account-nav-icons-registry.php</code></p>';

	echo '<h2>' . esc_html__( 'پیش‌نمایش از رجیستری', 'escapezoom-v2' ) . '</h2>';
	echo '<table class="widefat striped" style="max-width: 960px;"><thead><tr>';
	echo '<th>' . esc_html__( 'اندپوینت', 'escapezoom-v2' ) . '</th>';
	echo '<th>' . esc_html__( 'برچسب ثابت در قالب', 'escapezoom-v2' ) . '</th>';
	echo '<th>' . esc_html__( 'در منوی فعلی کاربر', 'escapezoom-v2' ) . '</th>';
	echo '<th>' . esc_html__( 'پیش‌نمایش', 'escapezoom-v2' ) . '</th>';
	echo '<th style="width:40%">' . esc_html__( 'کد HTML آیکن (کپی)', 'escapezoom-v2' ) . '</th>';
	echo '</tr></thead><tbody>';

	foreach ( $registry as $slug => $row ) {
		$label_fixed = isset( $row['label'] ) ? $row['label'] : '';
		$in_menu     = isset( $menu[ $slug ] ) ? $menu[ $slug ] : '—';
		$markup      = ez_account_nav_icon_markup( $slug );

		echo '<tr>';
		echo '<td><code>' . esc_html( $slug ) . '</code></td>';
		echo '<td>' . esc_html( $label_fixed ) . '</td>';
		echo '<td>' . esc_html( $in_menu ) . '</td>';
		echo '<td style="color:#09192d;"><span style="display:inline-flex;padding:6px;border:1px solid #ccd0d4;border-radius:6px;background:#fff;">';
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- controlled theme markup.
		echo $markup;
		echo '</span></td>';
		echo '<td><textarea readonly rows="4" style="width:100%;font-size:11px;direction:ltr;text-align:left;">';
		echo esc_textarea( $markup );
		echo '</textarea></td>';
		echo '</tr>';
	}

	echo '</tbody></table>';

	if ( ! empty( $menu ) ) {
		echo '<h2 style="margin-top:2rem;">' . esc_html__( 'کلیدهای منوی WooCommerce (فعلی)', 'escapezoom-v2' ) . '</h2>';
		echo '<ul style="columns:2;max-width:720px;">';
		foreach ( $menu as $slug => $title ) {
			$has = isset( $registry[ $slug ] ) ? '✓' : '✗';
			echo '<li><code>' . esc_html( $slug ) . '</code> — ' . esc_html( $title ) . ' — ' . esc_html( $has ) . '</li>';
		}
		echo '</ul>';
		echo '<p>' . esc_html__( '✓ یعنی در رجیستری آیکن دارد؛ ✗ یعنی به آیکن پیش‌فرض پیشخوان برمی‌گردد تا در رجیستری اضافه شود.', 'escapezoom-v2' ) . '</p>';
	}

	echo '</div>';
}
