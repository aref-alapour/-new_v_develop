<?php
global $wpdb;

$user_id = get_current_user_id();

$user        = wp_get_current_user();
$product_id  = (int) $_POST['product_id'];
$comment_id  = 0;
$content     = sanitize_text_field( $_POST['content'] );
$author_name = $user->billing_phone;
$user_level  = get_user_level( $user_id );

if ( $user->user_firstname ) {
	$author_name = $user->user_firstname;

	if ( $user->user_lastname ) {
		$author_name .= ' ' . $user->user_lastname;
	}
}

$author_mail = $user->user_email;
$rate        = $_POST['rate'];

if ( ! isset( $product_id ) || empty( $product_id ) ) {
	wp_send_json_error( 'شماره محصول مشخص نیست.' );
}

if ( ! isset( $content ) || empty( $content ) ) {
	wp_send_json_error( 'پاسخ شما مشخص نیست.' );
}

/*--------------------------------------------------------------------------------*/
// بررسی اینکه آیا این پلیر این بازی را خریده است یا خیر

$statuses = array_map( 'esc_sql', [ 'wc-completed', 'wc-walletx', 'wc-partially-paid' ,'wc-completed-paid'  ] );
$statuses = "'" . implode( "', '", $statuses ) . "'";

//foreach ( $orders as $order ) {
//    $order_id = $order->order_id;
//
//    $args = [
//        "single_value" => true,
//        "query"        => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id",
//    ];
//    $true_orders = (array) json_decode( ez_reservation( [ 'type' => 'query_execution', 'data' => $args ] ) );
//
//    if ( empty( $true_orders ) )
//        continue;
//}

// این کوئری بررسی میکند که آیا این یوزر سرگروهی بوده است؟
$orders = $wpdb->get_results( $wpdb->prepare(
    "
    SELECT order_id
    FROM {$wpdb->prefix}woocommerce_order_items AS items
    INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS item_meta 
        ON items.order_item_id = item_meta.order_item_id
    INNER JOIN {$wpdb->prefix}posts AS orders 
        ON items.order_id = orders.ID
    INNER JOIN {$wpdb->prefix}postmeta AS postmeta 
        ON orders.ID = postmeta.post_id
    WHERE
        item_meta.meta_key = '_product_id'
        AND item_meta.meta_value = %d
        AND orders.post_status IN ( {$statuses} )
        AND postmeta.meta_key = '_customer_user'
        AND postmeta.meta_value = %d
    ",
    $product_id,
    $user_id
) );

$order_id       = 0;
$true_orders    = [];

$order_ids = [];
foreach ( $orders as $o )
    if ( isset($o->order_id) )
        $order_ids[] = intval( $o->order_id );

$orders_id_text = implode( ',', $order_ids );
$sans_args = [
    "single_value"  => false,
    "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` IN ($orders_id_text) ORDER BY `wc_order_id` DESC",
];
$true_orders_decoded = json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => $sans_args ) ) );

if ( is_array( $true_orders_decoded ) && count( $true_orders_decoded ) > 0 )
    $true_orders = reset( $true_orders_decoded );
elseif ( is_object( $true_orders_decoded ) )
    $true_orders = $true_orders_decoded;
else
    $true_orders = null;

if ( !empty( $true_orders ) ) { // این یوزر سرگروهی بوده است.
    $booking_time = (int) $true_orders->booking_time;
    $current_time = time();

    if ( $current_time - $booking_time < 60 * 60 )
        wp_send_json_error( 'برای ثبت کامنت باید حداقل 60 دقیقه پس از برگزاری سانس اقدام نمایید.' );

    if ( $current_time - $booking_time > 7 * 24 * 60 * 60 )
        wp_send_json_error( 'بیش از یک هفته از برگزاری این بازی گذشته است. دیگر نمی توانید برای این بازی کامنت ثبت کنید.' );

} else { // دست کم این یوزر سرگروهی این سفارش نبوده است حالا باید بررسی شود که آیا همگروهی بوده است؟

    $order_ids_obj = ez_cm_get_order_id( $product_id, $user->user_login );

    if ( ! empty( $order_ids_obj ) ) { // جز همگروهی ها بوده

        foreach ( $order_ids_obj as $order_id_obj ) {
            $user_qualified = true;

            $order_id = $order_id_obj->order_id;
            $order    = wc_get_order( $order_id );

            if ( !in_array( $order->get_status(), ['partially-paid', 'walletx', 'completed','completed-paid'] ) ) { // ممکنه به عنوان همگروهی ثبت شده باشه اما سفارش لغو شده باشه
                $user_qualified = false;
                continue;
            }

            $sans_args = [
                "single_value"  => false,
                "query"         => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` LIKE $order_id",
            ];
            $true_order = json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => $sans_args ) ) )[0];

            $booking_time = $true_order->booking_time;
            $current_time = time();

            if ( $current_time - $booking_time < 60 * 60 )
                wp_send_json_error( 'برای ثبت کامنت باید حداقل 60 دقیقه پس از برگزاری سانس اقدام نمایید.' );

            if ( $current_time - $booking_time > 7 * 24 * 60 * 60 )
                wp_send_json_error( 'بیش از یک هفته از برگزاری این بازی گذشته است. دیگر نمی توانید برای این بازی کامنت ثبت کنید.' );

            if ( $user_qualified )
                break;
        }

        if ( ! $user_qualified )
            wp_send_json_error( 'ثبت دیدگاه تنها برای خریداران این بازی امکان پذیر است!!' );

    } else // این یوزر جز همگروهی ها هم نبود
        wp_send_json_error( 'ثبت دیدگاه تنها برای خریداران این بازی امکان پذیر است!' );
}

//if ( get_current_user_id() == 89946 )
//    wp_send_json_error('karen');

/*--------------------------------------------------------------------------------*/

$blacklist     = get_post_meta( $product_id, 'comments_blacklist', true ) ?: [];
$blacklist_new = get_post_meta( $product_id, 'comments_blacklist_new', true ) ?: [];

if ( get_current_user_id() == 17735 ) {
    saeed_store([
        'user_login' => $user->user_login,
        'blacklist' => $blacklist,
        'user_id' => $user->ID,
        'blacklist_new' => $blacklist_new,
    ]);
}

if ( in_array( $user->user_login, $blacklist ) || in_array( $user->ID, $blacklist_new ) ) {
	wp_send_json_error( 'شما برای این بازی قبلا دیدگاه ارسال کرده اید.' );
}

$rate_average = ( intval( $rate[1098] ) + intval( $rate[1097] ) + intval( $rate[1096] ) + intval( $rate[1095] ) + intval( $rate[1094] ) ) / 5 / 20;
if ( ! isset( $rate[1097] ) ) {
	$rate_average = $rate_average * 5;
}

$com_meta = [
	"comment_rating" => $rate,
	"comment_offer"  => $product_id,
	"rating"         => $rate_average,
];

$words      = [
	"آشغال",
	"اشغال",
	"کیر",
	"کیری",
	"کص",
	"کس کش",
	"کص کش",
	"خایه",
	"تخم",
	"مادر",
	"ننه",
	"جنده",
	"حروم",
	"سگ",
	"ریدم",
	"قحبه",
	"گایید",
	"خواهر",
	"کون",
	"کصخل",
	"کسخل",
	"کسده",
	"بیضه",
	"کثافت",
	"لجن",
	"بیشرف",
	"شرف",
	"شاش",
	"چرت و پرت",
    "بدترین",
    "ریدیم",
    "عن",
    "گوه",
    "ادرار",
    "مالید",
    "مالید",
];
$swear_flag = false;
foreach ( $words as $word ) {
	if ( substr_count( $content, $word ) > 0 ) {
		$swear_flag = true;
		break;
	}
}

$com_data = [
	'user_id'              => $user_id,
	'comment_meta'         => $com_meta,
	'comment_post_ID'      => $product_id,
	'comment_author'       => $author_name,
	'comment_content'      => $content,
	'comment_type'         => 'review',
	'comment_author_email' => $author_mail,
	'comment_approved'     => $swear_flag ? 0 : 1,
	'comment_date'         => date( 'Y-m-d H:i:s' ),
	'comment_date_gmt'     => date( 'Y-m-d H:i:s' ),
];

$comment_id = wp_insert_comment( $com_data );

if ( is_wp_error( $comment_id ) ) {
	wp_send_json_error( 'کامنت شما ثبت نشد دوباره تلاش کنید', 400 );
}

add_point( 'submit-comment', $user_id, 'ثبت نظر برای ' . wc_get_product( $product_id )->get_title() );

$user_rating_power = get_user_rating_power( $user_id );
$keys              = [ 1098, 1097, 1096, 1095, 1094 ];

/***************************************/
// کامنت ها به صورت عادی و بی وزن محاسبه میشن

$temp          = get_post_meta( $product_id, 'product_rates', true );
$product_rates = ! empty( $temp ) ? $temp : [ 1098 => 0, 1097 => 0, 1096 => 0, 1095 => 0, 1094 => 0 ];
foreach ( $keys as $key ) {
	$product_rates[ $key ] += intval( $rate[ $key ] );
}

update_post_meta( $product_id, 'product_rates', $product_rates );

$temp2 = get_post_meta( $product_id, 'comments_count_new', true );
update_post_meta( $product_id, 'comments_count_new', ++ $temp2 );

/***************************************/
// کامنت ها به صورت وزن دار محاسبه میشن.

$temp_clone          = get_post_meta( $product_id, 'clone_product_rates', true );
$product_rates_clone = ! empty( $temp_clone ) ? $temp_clone : [ 1098 => 0, 1097 => 0, 1096 => 0, 1095 => 0, 1094 => 0 ];
foreach ( $keys as $key ) {
	$product_rates_clone[ $key ] += intval( $rate[ $key ] ) * $user_rating_power;
}

update_post_meta( $product_id, 'clone_product_rates', $product_rates_clone );

$temp2_clone = get_post_meta( $product_id, 'clone_comments_count_new', true );
update_post_meta( $product_id, 'clone_comments_count_new', (int)$temp2_clone + $user_rating_power );

update_comment_meta( $comment_id, 'user_level', $user_level );

/***************************************/
// اضافه کردن کامنت به جدول کامنت های 30 روز گذشته برای استفاده داغ ترین ها

$power_map = [
    1 => 1,
    2 => 2,
    3 => 7,
    4 => 20,
];

$wpdb->insert( 'hottest_products', array(
    'product_id'        => $product_id,
    'comment_id'        => $comment_id,
    'w_rate'            => (int)get_comment_meta($comment_id, 'rating', true),
    'w_comments_count'  => $power_map[$user_level] ?? 1,
    'time'              => time()
));

/***************************************/

// black list
$comments_blacklist_new = ! empty( $blacklist_new ) ? $blacklist_new : [];

$comments_blacklist_new[] = $user->ID;

update_post_meta( $product_id, 'comments_blacklist_new', $comments_blacklist_new );

$success_msg = ! $swear_flag ? 'با تشکر، کامنت شما ثبت گردید و هم اکنون قابل مشاهده است.' : 'با تشکر، کامنت شما ثبت گردید و بعد از تایید مدیریت در سایت درج خواهد شد.';

wp_send_json_success( $success_msg );