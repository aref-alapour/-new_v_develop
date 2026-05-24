<?php

function user_badge_by_level( $user_data, $classes = '', $arg_type = 'user_id' ): void {

	if ( 'user_id' === $arg_type && (int) $user_data > 0 && function_exists( 'ez_user_should_show_mojavezedar_badge' ) && ez_user_should_show_mojavezedar_badge( (int) $user_data ) ) {
		$parts        = function_exists( 'ez_get_mojavezedar_badge_display_parts' ) ? ez_get_mojavezedar_badge_display_parts() : array(
			'color'      => '#6D28D9',
			'background' => 'rgba(109, 40, 217, 0.14)',
			'text'       => 'مجموعه دار',
		);
		$color        = $parts['color'];
		$background = $parts['background'];
		$text         = $parts['text'];
		echo "<span class='flex items-center leading-6 px-3 rounded-full gap-2 " . esc_attr( $classes ) . "' style='color: " . esc_attr( $color ) . '; background: ' . esc_attr( $background ) . "'>" . esc_html( $text ) . '</span>';
		return;
	}

    if ($arg_type == 'user_id')
        $user_level = get_user_level( $user_data ) ?: 0;
    elseif ($arg_type == 'user_level')
        $user_level = $user_data;

    if ( $user_level == 1 ) {
        $color      = '#959798';
        $background = '#2527281A';
        $text       = 'تازه وارد';
    } elseif ( $user_level == 2 ) {
        $color      = '#049654';
        $background = '#02C96F4D';
        $text       = 'نوپا';
    } elseif ( $user_level == 3 ) {
        $color      = '#3F7FF5';
        $background = '#5091FB4D';
        $text       = 'با تجربه';
    } else {
        $color      = '#FD7013';
        $background = '#FD701338';
        $text       = 'کارکشته';
    }

    echo "<span class='flex items-center leading-6 px-3 rounded-full gap-2 " . esc_attr( $classes ) . "' style='color: " . esc_attr( $color ) . '; background: ' . esc_attr( $background ) . "'>" . esc_html( $text ) . '</span>';
}

/**
 * Product review card: badge from comment_meta `user_level` only (10 = مجموعه‌دار at submit time).
 * Does not use live points or mojavezedar status.
 *
 * @param int    $user_id           Comment author ID (kept for call compatibility; unused for display).
 * @param string $classes           Extra span classes.
 * @param int    $stored_user_level comment_meta `user_level`.
 */
function ez_comment_badge_by_stored_level( int $user_id, string $classes, int $stored_user_level ): void {
	$moj    = defined( 'EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR' ) ? (int) EZ_COMMENT_USER_LEVEL_MOJAVEZEDAR : 10;
	$stored = (int) $stored_user_level;

	if ( $stored === $moj ) {
		$parts = function_exists( 'ez_get_mojavezedar_badge_display_parts' ) ? ez_get_mojavezedar_badge_display_parts() : array(
			'color'      => '#6D28D9',
			'background' => 'rgba(109, 40, 217, 0.14)',
			'text'       => 'مجموعه دار',
		);
		$color       = $parts['color'];
		$background = $parts['background'];
		$text        = $parts['text'];
		echo "<span class='flex items-center leading-6 px-3 rounded-full gap-2 " . esc_attr( $classes ) . "' style='color: " . esc_attr( $color ) . '; background: ' . esc_attr( $background ) . "'>" . esc_html( $text ) . '</span>';
		return;
	}

	$display_level = $stored;
	if ( $stored < 1 || $stored > 4 ) {
		$display_level = 1;
	}
	user_badge_by_level( $display_level, $classes, 'user_level' );
}

/**
 * HTML span for user level badge (points-based or «مجموعه دار»). For AJAX/CRM tables.
 *
 * @param int $user_id WordPress user ID.
 */
function ez_user_level_badge_html( int $user_id ): string {
	if ( $user_id <= 0 ) {
		return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #959798; background: #2527281A;">نامشخص</span>';
	}
	if ( function_exists( 'ez_user_should_show_mojavezedar_badge' ) && ez_user_should_show_mojavezedar_badge( $user_id ) ) {
		$p = function_exists( 'ez_get_mojavezedar_badge_display_parts' ) ? ez_get_mojavezedar_badge_display_parts() : array(
			'color'      => '#6D28D9',
			'background' => 'rgba(109, 40, 217, 0.14)',
			'text'       => 'مجموعه دار',
		);
		return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: ' . esc_attr( $p['color'] ) . '; background: ' . esc_attr( $p['background'] ) . ';">' . esc_html( $p['text'] ) . '</span>';
	}
	$user_level = get_user_level( $user_id );
	switch ( $user_level ) {
		case 1:
			return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #959798; background: #2527281A;">تازه وارد</span>';
		case 2:
			return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #049654; background: #02C96F4D;">نوپا</span>';
		case 3:
			return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #3F7FF5; background: #5091FB4D;">با تجربه</span>';
		case 4:
			return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #FD7013; background: #FD701338;">کارکشته</span>';
		default:
			return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold" style="color: #959798; background: #2527281A;">نامشخص</span>';
	}
}

function get_user_level( $user_id = null ): int {

    if (is_null($user_id))
        $user_id = get_current_user_id();

    $points = (int) get_user_points( $user_id ) ?: 0;

    if ( $points <= 150 ) {
        $user_level = 1;
    } elseif ( $points <= 700 ) {
        $user_level = 2;
    } elseif ( $points <= 7000 ) {
        $user_level = 3;
    } else {
        $user_level = 4;
    }

    return $user_level;
}

function get_user_discount( int $order_id = 0, $user_id = null ): array {

    if (is_null($user_id))
        $user_id = get_current_user_id();

    // اگر قبلاً ذخیره شده، همان را برگردان
    $saved_discount = get_post_meta($order_id, 'user_level_discount', true);

    if (!empty($saved_discount))
        return [
            'percentage' => (int) $saved_discount,
            'label'      => '(' . (int) $saved_discount . '%)',
        ];

    $user_level = get_user_level($user_id);

    if ($user_level == 3) {
        $discount_percentage = 5;
        $discount_label      = '(5%)';

    } elseif ($user_level == 4) {
        $discount_percentage = 10;
        $discount_label      = '(10%)';
    }

    // ذخیره در متای سفارش (حافظه‌دار)
    if ( $order_id )
        update_post_meta($order_id, 'user_level_discount', $discount_percentage);

    return [
        'percentage' => $discount_percentage,
        'label'      => $discount_label,
    ];
}

function get_user_rating_power( $user_id = null ): int {
	if ( is_null( $user_id ) ) {
		$user_id = get_current_user_id();
	}
	$user_level = function_exists( 'ez_user_effective_feature_level' )
		? ez_user_effective_feature_level( (int) $user_id )
		: (int) get_user_level( $user_id );

    $power_map = [
        1 => 1,
        2 => 2,
        3 => 7,
        4 => 20,
    ];

    return $power_map[$user_level] ?? 1;
}

function user_features_access ($source): bool {
    global $wpdb;

    $user_id   = get_current_user_id();
    $raw_limit = false;

    $base_access = [
        'collection'        => false,
        'invitation'        => false,
        'collection_like'   => false,
        'bio'               => false,
        'avatar'            => false,
    ];

    $access_map = [
        1 => $base_access,
        2 => array_merge($base_access, [
            'collection'        => 1,
            'invitation'        => 3,
            'collection_like'   => true,
        ]),
        3 => array_merge($base_access, [
            'collection'        => 3,
            'invitation'        => 10,
            'collection_like'   => true,
            'bio'               => true,
        ]),
        4 => array_merge($base_access, [
            'collection'        => 7,
            'invitation'        => true,
            'collection_like'   => true,
            'bio'               => true,
            'avatar'            => true,
        ]),
    ];

    $effective_level = function_exists( 'ez_user_effective_feature_level' )
        ? ez_user_effective_feature_level( (int) $user_id )
        : (int) get_user_level();
    if ( ! isset( $access_map[ $effective_level ] ) ) {
        $effective_level = (int) get_user_level();
    }
    $level_row = $access_map[ $effective_level ] ?? $access_map[1];
    if ( is_array( $level_row ) && array_key_exists( $source, $level_row ) ) {
        $raw_limit = $level_row[ $source ];
    }

    if ( is_bool( $raw_limit ) ) { // bool: feature on/off for this level
        return $raw_limit;
    }

    $numeric_limit = (int) ( is_array( $level_row ) && array_key_exists( $source, $level_row ) ? $level_row[ $source ] : 0 );

    if ( $source == 'collection' ) { // numeric caps: collection / invitation daily limits
        $collections_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM collections WHERE user_id LIKE %d", $user_id ) );

        if ( $collections_count >= $numeric_limit ) {
            return false;
        }
    }

    if ( $source == 'invitation' ) {

        $today_start = strtotime( "today" );
        $today_end   = strtotime( "tomorrow" ) - 1;

        $invitation_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM invitations WHERE inviter_id = %d AND created_at BETWEEN %s AND %s", $user_id, $today_start, $today_end ) );

        if ( $invitation_count >= $numeric_limit ) {
            return false;
        }
    }

    if ( $source == 'collection_like' ) {
        $collections_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM collections WHERE user_id LIKE %d", $user_id ) );

        if ( $collections_count >= $numeric_limit ) {
            return false;
        }
    }

    return true;
}

function add_new_point ($new_point) {
    global $wpdb;

    $new_point['created_at'] = time();
    $res = $wpdb->insert( 'points', $new_point);
}
