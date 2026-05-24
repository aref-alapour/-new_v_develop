<?php
/**
 * Registry of My Account nav icons: semantic endpoints → stroke SVG paths (currentColor).
 * Edit labels/paths here; optional filter: ez_account_nav_icons_registry.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @return array<string, array{label: string, paths: string}>
 */
function ez_account_nav_icons_registry(): array {
	static $cache = null;
	if ( null !== $cache ) {
		return $cache;
	}

	$icons = array(
		'dashboard'             => array(
			'label' => 'پیشخوان',
			'paths' => '<rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>',
		),
		'sans-manager'          => array(
			'label' => 'مدیریت سانس',
			'paths' => '<path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/><path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>',
		),
		'sells'                 => array(
			'label' => 'فروش‌های من',
			'paths' => '<path d="M16 10a4 4 0 0 1-8 0"/><path d="M3.103 6.034h17.794"/><path d="M3.4 5.467a2 2 0 0 0-.4 1.6V20a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7.067a2 2 0 0 0-.4-1.6l-2-2.667A2 2 0 0 0 17.333 2H6.667a2 2 0 0 0-1.6.8L3.4 5.467"/>',
		),
		'credit'                => array(
			'label' => 'اعتبار تخفیف',
			'paths' => '<line x1="19" x2="5" y1="5" y2="19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
		),
		'wallet'                => array(
			'label' => 'کیف پول',
			'paths' => '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>',
		),
		'notices'               => array(
			'label' => 'اطلاعیه‌ها',
			'paths' => '<path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a2 2 0 0 0 3.4 0"/>',
		),
		'offers'                => array(
			'label' => 'پیشنهادها',
			'paths' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/>',
		),
		'products'              => array(
			'label' => 'بازی‌های من',
			'paths' => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.27 6.96 12 5.01 12-5.01"/><path d="M12 22.08V12"/>',
		),
		'orders'                => array(
			'label' => 'رزروهای من',
			'paths' => '<path d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z"/><path d="M13 5v2"/><path d="M13 17v2"/><path d="M13 11v2"/>',
		),
		'comments'              => array(
			'label' => 'کامنت‌ها',
			'paths' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/>',
		),
		'my-reviews'            => array(
			'label' => 'کامنت‌های من',
			'paths' => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
		),
		'invitation'            => array(
			'label' => 'دعوت‌ها',
			'paths' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/>',
		),
		'my-collections'        => array(
			'label' => 'کالکشن‌های من',
			'paths' => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
		),
		'tickets'               => array(
			'label' => 'تیکت‌ها',
			'paths' => '<path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/>',
		),
		'cancellation-requests' => array(
			'label' => 'درخواست‌ها',
			'paths' => '<rect width="8" height="4" x="8" y="2" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/>',
		),
		'settings'              => array(
			'label' => 'تنظیمات حساب',
			'paths' => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
		),
		'sans-settings'         => array(
			'label' => 'تنظیمات سانس',
			'paths' => '<line x1="21" x2="14" y1="4" y2="4"/><line x1="10" x2="3" y1="4" y2="4"/><line x1="21" x2="12" y1="12" y2="12"/><line x1="8" x2="3" y1="12" y2="12"/><line x1="21" x2="16" y1="20" y2="20"/><line x1="12" x2="3" y1="20" y2="20"/><line x1="14" x2="14" y1="2" y2="6"/><line x1="8" x2="8" y1="10" y2="14"/><line x1="16" x2="16" y1="18" y2="22"/>',
		),
		'customer-logout'       => array(
			'label' => 'خروج',
			'paths' => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>',
		),
		'downloads'             => array(
			'label' => 'دانلودها',
			'paths' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>',
		),
		'edit-address'          => array(
			'label' => 'آدرس',
			'paths' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
		),
		'points'                => array(
			'label' => 'امتیاز من',
			'paths' => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
		),
	);

	$cache = apply_filters( 'ez_account_nav_icons_registry', $icons );

	return $cache;
}

/**
 * Raw inner SVG paths for an endpoint (fallback: dashboard).
 */
function ez_account_nav_icon_inner_paths( string $endpoint ): string {
	$r = ez_account_nav_icons_registry();
	if ( isset( $r[ $endpoint ]['paths'] ) ) {
		return $r[ $endpoint ]['paths'];
	}
	return $r['dashboard']['paths'];
}
