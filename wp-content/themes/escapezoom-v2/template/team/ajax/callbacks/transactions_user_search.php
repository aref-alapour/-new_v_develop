<?php

require_once __DIR__ . '/team_callback_bootstrap.php';

$medoo = medoo();

$phone_raw = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
$phone_raw = trim( $phone_raw );

if ( strlen( $phone_raw ) < 3 ) {
    echo '<div class="text-[#64748B] text-xs text-center py-3">حداقل ۳ رقم وارد کنید</div>';
    return;
}

$normalized = ltrim( $phone_raw, '0' );
$like_norm  = '%' . $normalized . '%';
$like_raw   = '%' . $phone_raw . '%';

$user_ids = array();

$login_users = $medoo->select(
    'wp_users',
    array( 'ID' ),
    array(
        'user_login[~]' => $like_norm,
        'LIMIT'           => 20,
    )
);
foreach ( (array) $login_users as $row ) {
    $user_ids[] = (int) $row['ID'];
}

$phone_meta_users = $medoo->select(
    'wp_usermeta',
    array( 'user_id' ),
    array(
        'meta_key'      => 'billing_phone',
        'meta_value[~]' => $like_raw,
        'LIMIT'         => 20,
    )
);
foreach ( (array) $phone_meta_users as $row ) {
    $user_ids[] = (int) $row['user_id'];
}

if ( $normalized !== $phone_raw && $phone_raw !== '' ) {
    $phone_meta_users2 = $medoo->select(
        'wp_usermeta',
        array( 'user_id' ),
        array(
            'meta_key'      => 'billing_phone',
            'meta_value[~]' => $like_norm,
            'LIMIT'         => 20,
        )
    );
    foreach ( (array) $phone_meta_users2 as $row ) {
        $user_ids[] = (int) $row['user_id'];
    }
}

$user_ids = array_values( array_unique( array_filter( $user_ids ) ) );
$user_ids = array_slice( $user_ids, 0, 20 );

if ( empty( $user_ids ) ) {
    ?>
    <a href="javascript:;" class="team_sans_game_search_item flex items-center gap-x-2 py-2">کاربری یافت نشد!</a>
    <?php
    return;
}

$users = $medoo->select(
    'wp_users',
    array( 'ID', 'user_login', 'display_name' ),
    array(
        'ID'    => $user_ids,
        'ORDER' => array( 'ID' => 'DESC' ),
    )
);

if ( empty( $users ) ) {
    ?>
    <a href="javascript:;" class="team_sans_game_search_item flex items-center gap-x-2 py-2">کاربری یافت نشد!</a>
    <?php
    return;
}

$meta_rows = $medoo->select(
    'wp_usermeta',
    array( 'user_id', 'meta_key', 'meta_value' ),
    array(
        'user_id'  => $user_ids,
        'meta_key' => array(
            'first_name',
            'last_name',
            'billing_first_name',
            'billing_last_name',
        ),
    )
);

$meta_by_user = array();
foreach ( (array) $meta_rows as $m ) {
    $uid = (int) $m['user_id'];
    if ( ! isset( $meta_by_user[ $uid ] ) ) {
        $meta_by_user[ $uid ] = array();
    }
    $meta_by_user[ $uid ][ (string) $m['meta_key'] ] = (string) $m['meta_value'];
}

foreach ( $users as $user ) {
    $uid = (int) $user['ID'];
    $meta = $meta_by_user[ $uid ] ?? array();

    $full_name_display = trim(
        ( $meta['first_name'] ?? '' ) . ' ' . ( $meta['last_name'] ?? '' )
    );
    if ( $full_name_display === '' ) {
        $full_name_display = trim(
            ( $meta['billing_first_name'] ?? '' ) . ' ' . ( $meta['billing_last_name'] ?? '' )
        );
    }
    if ( $full_name_display === '' ) {
        $full_name_display = (string) $user['display_name'];
    }
    ?>
    <a href="javascript:;" data-id="<?php echo (int) $uid; ?>" class="team_trans_user_search_item flex items-center gap-x-2 py-2">
        <span><?php echo esc_html( $full_name_display ); ?></span>
        <span><?php echo esc_html( (string) $user['user_login'] ); ?></span>
    </a>
    <?php
}
