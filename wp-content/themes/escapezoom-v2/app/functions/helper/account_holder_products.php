<?php
/**
 * Products linked to a user as sans_manager or collection owner (user_ebtal).
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * @param WP_User $user WordPress user object.
 * @param string  $role Role slug.
 */
function ez_user_has_role( $user, $role ) {
    if ( ! $user instanceof WP_User ) {
        return false;
    }
    return in_array( (string) $role, (array) $user->roles, true );
}

/**
 * Product IDs where this user is sans_manager or user_ebtal (compiler / مجموعه‌دار).
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function ez_account_get_managed_product_ids( $user_id ) {
    global $wpdb;
    $user_id = (int) $user_id;
    if ( $user_id <= 0 ) {
        return array();
    }
    $val = (string) $user_id;
    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key IN ('sans_manager', 'user_ebtal') AND meta_value = %s",
            $val
        )
    );
    if ( empty( $ids ) ) {
        return array();
    }
    $out = array();
    foreach ( array_unique( array_map( 'intval', $ids ) ) as $pid ) {
        if ( $pid > 0 && 'product' === get_post_type( $pid ) ) {
            $out[] = $pid;
        }
    }
    return array_values( array_unique( $out ) );
}

/**
 * Product IDs where this user is registered as collection owner (مجموعه‌دار) only — meta `user_ebtal`, not `sans_manager`.
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function ez_account_get_collection_owner_product_ids( $user_id ) {
	global $wpdb;
	$user_id = (int) $user_id;
	if ( $user_id <= 0 ) {
		return array();
	}
	$ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
			'user_ebtal',
			(string) $user_id
		)
	);
	if ( empty( $ids ) ) {
		return array();
	}
	$out = array();
	foreach ( array_unique( array_map( 'intval', $ids ) ) as $pid ) {
		if ( $pid > 0 && 'product' === get_post_type( $pid ) ) {
			$out[] = $pid;
		}
	}
	return array_values( array_unique( $out ) );
}

/**
 * Whether the user has at least one product as manager or collection owner.
 *
 * @param int $user_id User ID.
 */
function ez_account_user_has_managed_room( $user_id ) {
    return ! empty( ez_account_get_managed_product_ids( $user_id ) );
}

/**
 * Display styling for the «مجموعه دار» badge (active reservation on a managed product).
 *
 * @return array{color: string, background: string, text: string}
 */
function ez_get_mojavezedar_badge_display_parts(): array {
	return array(
		'color'      => '#6D28D9',
		'background' => 'rgba(109, 40, 217, 0.14)',
		'text'       => 'مجموعه دار',
	);
}

/**
 * Whether to show the «مجموعه دار» label instead of points-based level (UI only).
 * Requires compiler role, at least one product owned as collection owner (user_ebtal) with product_state active or updated.
 * Does not use sans_manager role or sans_manager postmeta.
 *
 * @param int $user_id WordPress user ID.
 */
function ez_user_should_show_mojavezedar_badge( int $user_id ): bool {
	if ( $user_id <= 0 ) {
		return false;
	}
	$user = get_userdata( $user_id );
	if ( ! $user || ! $user->exists() ) {
		return false;
	}
	if ( ! ez_user_has_role( $user, 'compiler' ) ) {
		return false;
	}
	$product_ids = ez_account_get_collection_owner_product_ids( $user_id );
	if ( empty( $product_ids ) ) {
		return false;
	}
	foreach ( $product_ids as $pid ) {
		$state = get_post_meta( (int) $pid, 'product_state', true );
		if ( 'active' === $state || 'updated' === $state ) {
			return true;
		}
	}
	return false;
}

/**
 * Hex color for customer name highlight (e.g. orders table) when «مجموعه دار» applies.
 */
function ez_get_mojavezedar_display_name_color_hex(): string {
	$parts = ez_get_mojavezedar_badge_display_parts();
	return $parts['color'];
}

/**
 * Points-based level, boosted to at least 3 when «مجموعه دار» badge applies (same caps as «با تجربه»).
 *
 * @param int $user_id WordPress user ID.
 * @return int Level 1–4 for use with access maps and comment power.
 */
function ez_user_effective_feature_level( int $user_id ): int {
	if ( ! function_exists( 'get_user_level' ) ) {
		return 0;
	}
	$base = (int) get_user_level( $user_id );
	if ( $user_id > 0 && function_exists( 'ez_user_should_show_mojavezedar_badge' ) && ez_user_should_show_mojavezedar_badge( $user_id ) ) {
		return max( $base, 3 );
	}
	return $base;
}

/**
 * Administrator fallback: products with postmeta administrator = user (legacy).
 *
 * @param int $user_id User ID.
 * @return int[]
 */
function ez_account_get_admin_legacy_product_ids( $user_id ) {
    global $wpdb;
    $user_id = (int) $user_id;
    if ( $user_id <= 0 ) {
        return array();
    }
    $ids = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value = %s",
            'administrator',
            (string) $user_id
        )
    );
    if ( empty( $ids ) ) {
        return array();
    }
    $out = array();
    foreach ( array_unique( array_map( 'intval', $ids ) ) as $pid ) {
        if ( $pid > 0 && 'product' === get_post_type( $pid ) ) {
            $out[] = $pid;
        }
    }
    return array_values( array_unique( $out ) );
}
