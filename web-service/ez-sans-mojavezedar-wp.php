<?php
/**
 * Detect «مجموعه‌دار» for sans_management_web using WP DB only (same rules as
 * ez_user_should_show_mojavezedar_badge: compiler role + user_ebtal product with product_state active|updated).
 *
 * @package EscapeZoom Web Service
 */

use Medoo\Medoo;

if ( ! defined( 'EZ_SANS_WP_TABLE_PREFIX' ) ) {
	define( 'EZ_SANS_WP_TABLE_PREFIX', 'wp_' );
}

/**
 * Single label copy for sans cards / data attributes (aligned with ez_get_mojavezedar_badge_display_parts).
 */
function ez_sans_mojavezedar_label_text(): string {
	return 'مجموعه دار';
}

/**
 * Tailwind border token for card border-<?= token ?> (arbitrary color).
 */
function ez_sans_mojavezedar_border_color_token(): string {
	return '[#6D28D9]';
}

/**
 * Inline HTML for the mojavezedar badge (matches theme ez_get_mojavezedar_badge_display_parts).
 */
function ez_sans_mojavezedar_badge_inner_html(): string {
	$label = ez_sans_mojavezedar_label_text();
	return '<span class="inline-flex items-center leading-6 px-3 rounded-full gap-2 text-xs font-bold" style="color:#6D28D9;background:rgba(109,40,217,0.14);">' . htmlspecialchars( $label, ENT_QUOTES, 'UTF-8' ) . '</span>';
}

/**
 * @param Medoo  $db       Medoo instance connected to WordPress DB (e.g. escapezo_ez9920).
 * @param int[]  $user_ids Customer / WP user IDs.
 * @return array<int,bool> Same keys as input order not guaranteed; all requested ids present.
 */
function ez_sans_bulk_mojavezedar_flags( Medoo $db, array $user_ids ): array {
	$user_ids = array_values(
		array_unique(
			array_filter(
				array_map( 'intval', $user_ids ),
				static function ( $v ) {
					return $v > 0;
				}
			)
		)
	);

	$out = [];
	foreach ( $user_ids as $uid ) {
		$out[ $uid ] = false;
	}

	if ( empty( $user_ids ) || ! isset( $db->pdo ) || ! $db->pdo instanceof \PDO ) {
		return $out;
	}

	$pdo    = $db->pdo;
	$prefix = EZ_SANS_WP_TABLE_PREFIX;
	$cap_key = $prefix . 'capabilities';

	$place = implode( ',', array_fill( 0, count( $user_ids ), '?' ) );
	$sql   = "SELECT ID FROM {$prefix}users WHERE ID IN ($place)";
	$stmt  = $pdo->prepare( $sql );
	$stmt->execute( $user_ids );
	$existing = [];
	while ( $row = $stmt->fetch( \PDO::FETCH_ASSOC ) ) {
		$existing[] = (int) $row['ID'];
	}

	if ( empty( $existing ) ) {
		return $out;
	}

	$place2 = implode( ',', array_fill( 0, count( $existing ), '?' ) );
	$sql2   = "SELECT user_id, meta_value FROM {$prefix}usermeta WHERE meta_key = ? AND user_id IN ($place2)";
	$params = array_merge( array( $cap_key ), $existing );
	$stmt   = $pdo->prepare( $sql2 );
	$stmt->execute( $params );

	$compilers = array();
	while ( $row = $stmt->fetch( \PDO::FETCH_ASSOC ) ) {
		$caps = @unserialize( $row['meta_value'] );
		if ( is_array( $caps ) && ! empty( $caps['compiler'] ) ) {
			$compilers[] = (int) $row['user_id'];
		}
	}

	if ( empty( $compilers ) ) {
		return $out;
	}

	$str_ids = array_map( 'strval', $compilers );
	$place3  = implode( ',', array_fill( 0, count( $str_ids ), '?' ) );
	$sql3    = "
		SELECT DISTINCT pm_e.meta_value AS uid
		FROM {$prefix}postmeta pm_e
		INNER JOIN {$prefix}posts p ON p.ID = pm_e.post_id AND p.post_type = 'product'
		INNER JOIN {$prefix}postmeta pm_s ON pm_s.post_id = p.ID
			AND pm_s.meta_key = 'product_state'
			AND pm_s.meta_value IN ('active','updated')
		WHERE pm_e.meta_key = 'user_ebtal'
			AND pm_e.meta_value IN ($place3)
	";
	$stmt = $pdo->prepare( $sql3 );
	$stmt->execute( $str_ids );

	$with_collection = array();
	while ( $row = $stmt->fetch( \PDO::FETCH_ASSOC ) ) {
		$with_collection[] = (int) $row['uid'];
	}

	foreach ( $user_ids as $uid ) {
		if ( in_array( $uid, $compilers, true ) && in_array( $uid, $with_collection, true ) ) {
			$out[ $uid ] = true;
		}
	}

	return $out;
}
