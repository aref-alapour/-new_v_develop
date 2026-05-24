<?php

global $wpdb;

$user          = wp_get_current_user();
$collection_id = (int) sanitize_text_field( $_POST['collection'] );

$collection = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM collections WHERE ID LIKE %d", $collection_id ) )[0];

$users = $collection->users ? unserialize( $collection->users ) : [];

$liked_collections = get_user_meta( $user->ID, 'liked_collections', true ) ?: [];

if ( ! $collection ) {
    wp_send_json_error( [
        'text'   => "کالکشن وجود ندارد.",
        'button' => count( $users ) . ( ( count( $users ) > 1 ) ? ' نفر پسندیدند' : ' نفر پسندید' ) . '<button type="button" data-collection="' . $collection_id . '" data-action="like" class="flex gap-x-2 py-1 px-4 rounded-xl border shadow-12 items-center bg-slate-100">
	        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
	            <path d="M7.50186 2.45658L8.00195 2.90447L8.50229 2.45686C9.21653 1.8179 10.1483 1.47663 11.1063 1.50313C12.0642 1.52963 12.9756 1.92185 13.6534 2.59926C14.3305 3.27626 14.723 4.1866 14.7504 5.14369C14.7777 6.09404 14.4429 7.01899 13.8144 7.73163L8.0008 13.5534L2.18854 7.73156C1.55916 7.01853 1.22406 6.09274 1.25157 5.14157C1.27928 4.18371 1.67263 3.2728 2.35086 2.59585C3.02909 1.9189 3.94074 1.52727 4.89865 1.50137C5.85656 1.47547 6.78804 1.81727 7.50186 2.45658Z" stroke="#889BAD" stroke-width="1.5"/>
	        </svg>
	        پسندیدن
	    </button>',
    ] );
}

if ( ! user_features_access( 'collection_like' ) ) {
    wp_send_json_error( [
        'text'   => 'برای استفاده از این ویژگی نیاز به سطح کاربری بالاتر دارید',
        'button' => count( $users ) . ( ( count( $users ) > 1 ) ? ' نفر پسندیدند' : ' نفر پسندید' ) . ' <button type = "button" data-collection="' . $collection_id . '" data-action="like" class="flex gap-x-2 py-1 px-4 rounded-xl border shadow-12 items-center bg-slate-100">
	        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
	            <path d="M7.50186 2.45658L8.00195 2.90447L8.50229 2.45686C9.21653 1.8179 10.1483 1.47663 11.1063 1.50313C12.0642 1.52963 12.9756 1.92185 13.6534 2.59926C14.3305 3.27626 14.723 4.1866 14.7504 5.14369C14.7777 6.09404 14.4429 7.01899 13.8144 7.73163L8.0008 13.5534L2.18854 7.73156C1.55916 7.01853 1.22406 6.09274 1.25157 5.14157C1.27928 4.18371 1.67263 3.2728 2.35086 2.59585C3.02909 1.9189 3.94074 1.52727 4.89865 1.50137C5.85656 1.47547 6.78804 1.81727 7.50186 2.45658Z" stroke="#889BAD" stroke-width="1.5"/>
	        </svg>
	        پسندیدن
	    </button>',
    ] );
}

if ( empty( $liked_collections ) ) {
    $liked_collections = [];
}

if ( $collection->user_id == $user->ID ) {
    wp_send_json_error( [
        'text'   => 'شما نمیتوانید کالکشن خود را لایک کنید.',
        'button' => count( $users ) . ( ( count( $users ) > 1 ) ? ' نفر پسندیدند' : ' نفر پسندید' ) . '<button type="button" data-collection="' . $collection_id . '" data-action="like" class="flex gap-x-2 py-1 px-4 rounded-xl border shadow-12 items-center bg-slate-100">
	        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="15" viewBox="0 0 16 15" fill="none">
	            <path d="M7.50186 2.45658L8.00195 2.90447L8.50229 2.45686C9.21653 1.8179 10.1483 1.47663 11.1063 1.50313C12.0642 1.52963 12.9756 1.92185 13.6534 2.59926C14.3305 3.27626 14.723 4.1866 14.7504 5.14369C14.7777 6.09404 14.4429 7.01899 13.8144 7.73163L8.0008 13.5534L2.18854 7.73156C1.55916 7.01853 1.22406 6.09274 1.25157 5.14157C1.27928 4.18371 1.67263 3.2728 2.35086 2.59585C3.02909 1.9189 3.94074 1.52727 4.89865 1.50137C5.85656 1.47547 6.78804 1.81727 7.50186 2.45658Z" stroke="#889BAD" stroke-width="1.5"/>
	        </svg>
	        پسندیدن
	    </button>',
    ] );
}

if ( in_array( $collection_id, $liked_collections ) || in_array( $user->ID, $users ) ) {
    wp_send_json_error( [
        'text'   => 'شما قبلا این کالکشن را لایک کردید',
        'button' => count( $users ) . ( ( count( $users ) > 1 ) ? ' نفر پسندیدند' : ' نفر پسندید' ) . '<button type="button" data-collection="' . $collection_id . '" data-action="like" class="flex gap-x-2 py-1 px-4 rounded-xl border shadow-12 items-center bg-slate-100">
	        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
	            <path d="M13.6837 2.0689C14.4962 2.88129 14.9672 3.9737 15.0001 5.12221C15.033 6.27072 14.6254 7.38831 13.8607 8.2459L7.50073 14.6149L1.14223 8.2459C0.376673 7.38786 -0.0313664 6.26931 0.00188346 5.11988C0.0351333 3.97045 0.50715 2.87736 1.32103 2.06502C2.13491 1.25268 3.22889 0.782725 4.37838 0.751646C5.52787 0.720567 6.64565 1.13072 7.50223 1.8979C8.35932 1.13114 9.47744 0.721615 10.627 0.753416C11.7766 0.785216 12.8703 1.25593 13.6837 2.0689Z" fill="#F21543"/>
	        </svg>
	        می پسندم
	    </button>',
    ] );
}

$liked_collections[] = $collection_id;
$users[]             = $user->ID;

update_user_meta( $user->ID, 'liked_collections', $liked_collections );

$wpdb->update( 'collections', [ 'users' => serialize( $users ), ], [ 'ID' => $collection_id ] );

// دریافت لیست آیدی نویسندگانی که این کاربر (لایک‌کننده) قبلاً کالکشن آن‌ها را لایک کرده است
$liked_authors = get_user_meta( $user->ID, 'liked_collection_authors', true ) ?: [];

// بررسی اینکه آیا نویسنده این کالکشن قبلاً توسط این کاربر لایک شده است یا خیر؟
if ( ! in_array( $collection->user_id, $liked_authors ) ) {
    
    // اگر بار اول است، امتیاز را به صاحب کالکشن بده
    add_point(
        'collection-liked',
        $collection->user_id,
        "لایک گرفتن کالکشن \"{$collection->title}\""
    );

    // حالا آیدی صاحب کالکشن را به لیست لایک‌شده‌های این کاربر اضافه کن تا دفعات بعدی امتیاز نگیرد
    $liked_authors[] = $collection->user_id;
    update_user_meta( $user->ID, 'liked_collection_authors', $liked_authors );
}


wp_send_json_success( [
    'text'   => 'کالکشن با موفقیت لایک شد',
    'button' => count( $users ) . ( ( count( $users ) > 1 ) ? ' نفر پسندیدند' : ' نفر پسندید' ) . '<button disabled type="button" data-collection="' . $collection_id . '" data-action="unlike" class="flex gap-x-2 py-1 px-4 rounded-xl border shadow-12 items-center bg-slate-100 opacity-60 ">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15" fill="none">
        <path d="M13.6837 2.0689C14.4962 2.88129 14.9672 3.9737 15.0001 5.12221C15.033 6.27072 14.6254 7.38831 13.8607 8.2459L7.50073 14.6149L1.14223 8.2459C0.376673 7.38786 -0.0313664 6.26931 0.00188346 5.11988C0.0351333 3.97045 0.50715 2.87736 1.32103 2.06502C2.13491 1.25268 3.22889 0.782725 4.37838 0.751646C5.52787 0.720567 6.64565 1.13072 7.50223 1.8979C8.35932 1.13114 9.47744 0.721615 10.627 0.753416C11.7766 0.785216 12.8703 1.25593 13.6837 2.0689Z" fill="#F21543"/>
    </svg>
    می پسندم
</button>',
] );
