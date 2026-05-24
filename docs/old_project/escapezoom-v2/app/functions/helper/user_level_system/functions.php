<?php

function user_badge_by_level( $user_data, $classes = '', $arg_type = 'user_id' ): void {

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

    echo "<span class='flex items-center leading-6 px-3 rounded-full gap-2 $classes' style='color: $color; background: {$background}'>$text</span>";
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
    $user_level = get_user_level($user_id);

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

    $user_id = get_current_user_id();

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

    $limitation = $access_map[get_user_level()][$source];

    if (is_bool($limitation) ) // اگر این فیچر برای این لول روشن بود یا خاموش بود.
        return $limitation;

    if ( $source == 'collection' ) {
        $collections_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM collections WHERE user_id LIKE %d", $user_id ) );

        if ( $collections_count >= $limitation )
            return false;
    }

    if ( $source == 'invitation' ) {

        $today_start = strtotime("today");
        $today_end   = strtotime("tomorrow") - 1;

        $invitation_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM invitations WHERE inviter_id = %d AND created_at BETWEEN %s AND %s", $user_id, $today_start, $today_end ) );

        if ( $invitation_count >= $limitation )
            return false;
    }

    if ( $source == 'collection_like' ) {
        $collections_count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM collections WHERE user_id LIKE %d", $user_id ) );

        if ( $collections_count >= $limitation )
            return false;
    }

    return true;
}

function add_new_point ($new_point) {
    global $wpdb;

    $new_point['created_at'] = time();
    $res = $wpdb->insert( 'points', $new_point);
}
