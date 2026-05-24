<?php
/**
 * init: upload_file_test, get_duplicate_transactions, 1402_topsales, bak_test, update_product_brand
 *
 * هوک init با چند شرط GET برای ابزارهای ادمین/مهاجرت.
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6756-8110)
 * نوع: هوک init (چند ابزار GET)
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * GET: upload_file_test
 *
 * هدف: فرم HTML تست آپلود API
 * استفاده: توسعه
 * وابستگی: فرم POST به API
 * امنیت: بدون احراز هویت
 * وضعیت: حذف در production
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:14
 */
if ( isset( $_GET['upload_file_test'] ) ) { ?>

    <form method="post" action="https://escapezoom.ir/api/v1/user/upload_self_destruct/" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload" multiple>
        <input type="submit" value="Upload Image" name="submit">
    </form>
    <form method="post" action="https://escapezoom.ir/api/v1/user/upload/" enctype="multipart/form-data">
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Upload Image" name="submit">
    </form>

    <?php
}

//add_filter('get_the_terms', 'custom_modify_tag_title', 10, 3);
function custom_modify_tag_title($terms, $post_id, $taxonomy) {
    if ($taxonomy === 'product_tag' && is_array($terms))
        foreach ($terms as $key => $term)
            if (str_contains($term->name, '|||||'))
                $terms[$key]->name = str_replace('|||||', '', $term->name);

    return $terms;
}

/**
 * GET: get_duplicate_transactions
 *
 * هدف: یافتن تراکنش‌های تکراری کیف پول
 * استفاده: تحلیل یک‌بار
 * وابستگی: $wpdb, $wldb, saeed_store
 * امنیت: بدون احراز هویت
 * وضعیت: حذف یا guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:38
 */
if ( isset( $_GET['get_duplicate_transactions'] ) ) {
    global $wpdb, $wldb;

    $transactions = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `wallet_transactions` WHERE `created_at` > '1760719999';" ) );

    foreach ( $transactions as $transaction )
        if ( str_contains($transaction->description, 'سفارش:') )
            $duplicates[$transaction->user_id][] = $transaction->description;

    $new = [];
    foreach ( $duplicates as $user_id => $orders ) {

        if ( !empty( $duplicated_orders = array_unique(array_diff_assoc($orders, array_unique($orders))) ) ) {

            $amendment_orders = [];
            foreach ($duplicated_orders as $desc) {
                $order_id = explode('سفارش: ', $desc)[1];

                foreach ( $transactions as $transaction )
                    if ( str_contains($transaction->description, $order_id) && str_contains($transaction->description, 'اصلاحیه') )
                        $amendment_orders[] = $order_id;

            }

            // حذف اصلاحیه خورده ها از لیست تکراری ها
            foreach ($amendment_orders as $amendment_order)
                foreach ($duplicated_orders as $key => $value)
                    if (strpos($value, (string)$amendment_order) !== false)
                        unset($duplicated_orders[$key]);

            if ( !empty( $duplicated_orders ) )
                $new[$user_id] = array_values($duplicated_orders);
        }
    }

    saeed_store($new);
}

/**
 * GET: 1402_topsales
 *
 * هدف: گزارش topsale سال ۱۴۰۲ روی سفارش‌های خاص
 * استفاده: تحلیل یک‌بار
 * وابستگی: ez_reservation, wc_get_order
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:76
 */
if ( isset( $_GET['1402_topsales'] ) ) {

    add_action('woocommerce_after_register_post_type', function () {

        global $wpdb;

        $temp      = $wpdb->get_results( "SELECT wp_posts.ID FROM wp_posts WHERE post_status = 'wc-partially-paid' OR post_status = 'wc-held' OR post_status = 'wc-completed' OR post_status = 'wc-walletx'", ARRAY_A );
        $ids_int   = array_values( array_unique( array_filter( array_map( 'absint', wp_list_pluck( $temp, 'ID' ) ) ) ) );
        $in_orders = ! empty( $ids_int ) ? implode( ',', $ids_int ) : '';
        $rows      = array();
        if ( $in_orders !== '' ) {
            $rows = json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => array( 'query' => "SELECT wc_order_id AS ID, booking_time AS booking_time FROM wp_zb_booking_history WHERE `wc_order_id` IN ({$in_orders}) AND `room_id` IN (403881,333821,309692,335012,145024,2762,4134,334025,154030,38163,6292,40875,300206,21755)" ) ) ), true );
        }
        $rows = is_array( $rows ) ? $rows : array();
        $all  = array();

        foreach ( $rows as $row ) {

            if ( $row['booking_time'] < time() - 4 * 3600 && time() - 365 * 24 * 3600 < $row['booking_time']  ) {
                $order_id = $row['ID'];

                $order = wc_get_order($order_id);
                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $quantity   = $item->get_quantity();
                }

                $all[] = array(
                    'room_id'   => $product_id,
                    'order_id'  => $order_id,
                    'count'     => $quantity,
                    'held_time' => $row['booking_time']
                );
            }
        }

        $topsale = [];
        foreach ( $all as $order ) {
            $topsale[$order['room_id']] += $order['count'];
        }
        saeed_store($topsale);

    });
}

/**
 * GET: bak_test
 *
 * هدف: تست sort_products_get از web-service
 * استفاده: توسعه
 * وابستگی: wp_remote_post, saeed_print
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:121
 */
if ( isset( $_GET['bak_test'] ) ) {
    $posts_per_page = 1;
    $args = [
        'image_type'        => 'url',
        'limit'             => $posts_per_page,
        'page'              => 1,
        'max_num_pages'     => true,
        "format"            => 'html_slider',
        'is_mobile'         => wp_is_mobile(),
        'sort_type'         => 'trend',
        'only_ads'          => false,
        'show_more'         => 0,
        'badge_ads'         => false,
        'random'            => true
    ];
//    $products = json_decode ( ez_webservice( array('type' => 'sort_products_get', 'data' => $args) ) );

    $data = array('type' => 'sort_products_get', 'data' => $args);
    $base_url = ($_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    $response = wp_remote_post( $base_url, array(
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => array(),
        'body'          => $data,
        'cookies'       => array()
    ) );

    saeed_print($response);
}

/**
 * GET: update_product_brand
 *
 * هدف: کپی term برند به post meta محصول
 * استفاده: مهاجرت
 * وابستگی: WP_Query, product_brand
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:154
 */
if ( isset( $_GET['update_product_brand'] ) ) {

    add_action('init', function() {

        $args = array (
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'meta_query'        => array (
                array(
                    'key'     => 'product_state',
                    'value'   => 'active',
                    'compare' => 'LIKE',
                ),
            ),
        );
        $query = new WP_Query($args);

        while ($query->have_posts()) : $query->the_post();
            global $product;

            $brand_data = get_the_terms(get_the_ID(), 'product_brand')[0];

            update_post_meta(get_the_ID(), 'product_brand', $brand_data->term_id);

        endwhile;
        wp_reset_postdata();

    });
}

/**
 * GET: get_list_of_all_products
 *
 * هدف: لیست همه محصولات در لاگ
 * استفاده: مهاجرت
 * وابستگی: WP_Query, saeed_store
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:185
 */
if ( isset( $_GET['get_list_of_all_products'] ) ) {

    add_action('woocommerce_after_register_post_type', function() {

        $args = array (
            'post_type'         => 'product',
            'posts_per_page'    => -1,
        );
        $query = new WP_Query($args);

        while ($query->have_posts()) : $query->the_post();
            $id = get_the_ID();

            $data[] = [
                'id'        => $id,
                'title'     => get_the_title(),
                'date'      => get_the_date('Y - m - d'),
                'statue'    => get_post_status(),
                'city'      => (get_the_terms($id, 'product_cat')[0])->name,
            ];

        endwhile;
        wp_reset_postdata();

        saeed_store($data);

    });
}

/**
 * GET: redirect_to_payment_url
 *
 * هدف: ریدایرکت به URL پرداخت ثابت
 * استفاده: تست
 * وابستگی: header Location
 * امنیت: بدون احراز هویت
 * وضعیت: حذف فوری
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:214
 */
if ( isset( $_GET['redirect_to_payment_url'] ) ) {
    header('Location: ' . 'https://escapezoom.ir/checkout/order-pay/474181/?key=wc_order_O06V2Rlrnda5G');
    exit;
}

/**
 * GET: reservation_webservice_test
 *
 * هدف: INSERT تست در booking_history
 * استفاده: تست
 * وابستگی: wp_remote_post reservation.php
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:219
 */
if ( isset( $_GET['reservation_webservice_test'] ) ) {

    $query = "INSERT INTO `wp_zb_booking_history` (`booking_id`, `customer_id`, `wc_order_id`, `status`, `room_id`, `booking_time`, `booked_time`, `name`, `phone`, `quantity`) 
                    VALUES (NULL, 1, 1, 1, 1, 1, 1, 1, 1, 1);";
    $data = [
        'query'         => $query,
        'single_value'  => false,
    ];

    $response = wp_remote_post( 'https://escapezoom.ir/web-service/reservation.php', array(
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => array(),
        'body'          => array('type' => 'query_execution2', 'data' => $data),
        'cookies'       => array()
    ) );

//    $res = $response['body'];
    $res = $response;

    saeed_store($res);
}

/**
 * GET: get_jwt_token_by_user
 *
 * هدف: تست API نشان (نه JWT)
 * استفاده: تست
 * وابستگی: curl, api-key در کد
 * امنیت: کلید در کد
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:245
 */
if ( isset( $_GET['get_jwt_token_by_user'] ) ) {

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_URL, "https://api.neshan.org/v4/direction?type=car&destination=35.72056383837348,51.43225866699049&avoidTrafficZone=false&avoidOddEvenZone=false&alternative=false&bearing=");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "api-key: service.b745e9cdd9ec4f2f9d65acdcedff3ca6"
    ));

    $response = curl_exec($curl);
    curl_close($curl);

    echo $response;
}

/**
 * GET: lat
 *
 * هدف: لینک geo تست
 * استفاده: تست
 * وابستگی: HTML
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:260
 */
if ( isset( $_GET['lat'] ) ) { ?>

    <a href="geo:35.745775,51.212623">35.745775,51.212623</a>

    <?php
}

/**
 * GET: ashoora
 *
 * هدف: close_all_sanses کامنت‌شده
 * استفاده: غیرفعال
 * وابستگی: ez_reservation
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:267
 */
if ( isset( $_GET['ashoora'] ) ) {
//    ez_reservation( array('type' => 'close_all_sanses_of_all_products') );
}

/**
 * GET: data_products_set2
 *
 * هدف: POST به dev-api data_products_set
 * استفاده: تست
 * وابستگی: wp_remote_post
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:271
 */
if ( isset( $_GET['data_products_set2'] ) ) {

    $response = wp_remote_post( 'https://dev-api.escapezoom.ir/web-service/web-service.php', array(
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => array(),
        'body'          => array('type' => 'data_products_set', 'data' => $product_data),
        'cookies'       => array()
    ) );

    $res = $response['body'];

//    echo '<pre>'; print_r($res); echo '</pre>';
}

/**
 * GET: update_user_shaba
 *
 * هدف: به‌روزرسانی شبا مالک از متن ثابت
 * استفاده: مهاجرت
 * وابستگی: update_user_meta
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:289
 */
if ( isset( $_GET['update_user_shaba'] ) ) {

    $text = "491,3370104598,IR930620000000301907391007,البرز آقایی
        478,,IR890560083488800577817001,سینا غلامیان
    ";

    $lines = explode("\n", $text);
    $desired_array = [];

    foreach ($lines as $line) {
        $parts = explode(',', $line);
        if (count($parts) >= 4) {
            $user_id = trim($parts[0]);
            $withdrawal_owner_shaba = trim($parts[2]);
            $withdrawal_owner_identity_card = trim($parts[1]);
            $withdrawal_owner_name = trim($parts[3]);

            update_user_meta($user_id, 'withdrawal_owner_name', sanitize_text_field( $withdrawal_owner_name));
            update_user_meta($user_id, 'withdrawal_owner_shaba', sanitize_text_field($withdrawal_owner_shaba));
            update_user_meta($user_id, 'withdrawal_owner_identity_card', sanitize_text_field($withdrawal_owner_identity_card ));
        }
    }
}

/**
 * GET: get_single_product
 *
 * هدف: تست API get product
 * استفاده: تست
 * وابستگی: wp_remote_post
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:313
 */
if ( isset( $_GET['get_single_product'] ) ) {

    $response = wp_remote_post( 'https://escapezoom.ir/api/v1/product/get/395598', array(
        'method'        => 'POST',
        'timeout'       => 45,
        'redirection'   => 5,
        'httpversion'   => '1.0',
        'blocking'      => true,
        'headers'       => array(),
        'body'          => $data,
        'cookies'       => array()
    ) );

    saeed_print( $response['body'] );

}


add_action('init', function (){
    global $wpdb;

/**
 * GET: brands_list
 *
 * هدف: خروجی CSV برندها در لاگ
 * استفاده: مهاجرت/گزارش
 * وابستگی: get_terms, saeed_store
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:334
 */
    if( isset($_GET['brands_list']) ) {
        $brands = get_terms([
            'taxonomy'      => 'product_brand',
            'hide_empty'    => true,
        ]);

        foreach ( $brands as $brand ) {

            $brand_id = $brand->term_id;

            $items[] = [
                'id'    => $brand_id,
                'title' => $brand->name,
                'url'   => get_term_link($brand),
                'count' => $brand->count
            ];
        }

        $output = "id, title, url, count\n"; // Initial line for the header

        foreach ($items as $item) {
            $output .= "{$item['id']}, {$item['title']}, {$item['url']}, {$item['count']}\n";
        }

        saeed_store($output);
    }

/**
 * GET: customer_user
 *
 * هدف: پردازش سفارش‌های partially-paid و مالی مالک
 * استفاده: عملیاتی خطرناک
 * وابستگی: ez_reservation, wc_get_order, $wldb
 * امنیت: بدون احراز هویت
 * وضعیت: guard یا حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:361
 */
    if( isset($_GET['customer_user']) ) {
        global $wpdb, $wldb;

        $temp      = $wpdb->get_results( "SELECT ID FROM wp_posts WHERE post_status = 'wc-partially-paid' ORDER BY wp_posts.ID", ARRAY_A );
        $ids_int   = array_values( array_unique( array_filter( array_map( 'absint', wp_list_pluck( $temp, 'ID' ) ) ) ) );
        $in_orders = ! empty( $ids_int ) ? implode( ',', $ids_int ) : '';
        $rows      = array();
        if ( $in_orders !== '' ) {
            $rows = json_decode( ez_reservation( array( 'type' => 'query_execution', 'data' => array( 'query' => "SELECT wc_order_id AS ID, booking_time AS booking_time FROM wp_zb_booking_history WHERE `wc_order_id` IN ({$in_orders})" ) ) ), true );
        }
        $rows = is_array( $rows ) ? $rows : array();
        foreach ($rows as $row) {
            if ($row['booking_time'] < time() - 24 * 3600 || 1) { // اگر 24 ساعت از زمان برگزار سانس گذشته است.
                $order_id = $row['ID'];

                $commission = 10;
                $tax        = 10;

                $order = wc_get_order($order_id);
                foreach ($order->get_items() as $item) {
                    $product_id     = $item->get_product_id();
                    $item_quantity  = $item->get_quantity();
                }

                $tax_free = [2762, 21755, 353952, 87471, 145024];
                if ( in_array($product_id, $tax_free) )
                    $tax = 0;

                $description = 'فروش تیکت بازی ' . get_the_title($product_id) . ' - سفارش: ' . $order_id;

                $if_exists = $wpdb->get_results("SELECT *  FROM `wallet_transactions` WHERE `description` LIKE '{$description}'", ARRAY_A);
                if ( !empty( $if_exists ) ) // this transaction is already added.
                    continue;

                $pish_per_person    = get_post_meta( $order_id, 'ticket_tedad', true );
                $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : get_post_meta( $product_id, 'pish_pardakht_per_person', true );
                $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : 1;

                $prepaid    = get_post_meta( $order_id, "prepaid", true );
                $pish_final = $prepaid ? : (get_post_meta( $order_id, "_order_total_2", true ) ? : get_post_meta( $order_id, "_order_total", true ));

                $item_total         = $pish_final / $pish_per_person * $item_quantity;
                $porsant            = $item_total * ($commission / 100);

                /*===========================*/
                // owner transaction adding

                $owner_id = get_owner_id_by_product_id($product_id);

                $current_balance    = $wldb->get_balance($owner_id);

                $amount             = $pish_final - ($porsant * (1 + $tax / 100) );
                $balance            = $current_balance + $amount;

                $new_transaction = array(
                    'user_id'       => $owner_id,
                    'amount'        => $amount,
                    'balance'       => $balance,
                    'description'   => $description,
                    'type'          => 'transaction',
                );

                $wldb->insert($new_transaction);
                $wpdb->update('wp_posts', array('post_status' => 'wc-walletx'), array('ID' => $order_id));

                update_post_meta($product_id, 'total_income', (int)get_post_meta($product_id, 'total_income', true) + $item_total); // آپدیت فروش کل این محصول
            }
        }
    }

/**
 * GET: tickets_sold
 *
 * هدف: آمار بلیت فروخته‌شده برند
 * استفاده: گزارش
 * وابستگی: wpdb, terms
 * امنیت: بدون احراز هویت
 * وضعیت: بررسی
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:431
 */
    if( isset($_GET['tickets_sold']) ) {

        $products_id = [538135, 1635, 538155, 537803, 490170, 542937, 542432, 541302, 539626, 539608, 539564, 539512, 539509, 1897, 539299, 533504, 532638, 531587, 530424, 530358, 529528, 529497, 529174, 529069, 527026, 526417, 2150, 522070, 521994, 521030, 520909, 519646, 517286, 516040, 514476, 514467, 514059, 512498, 511359, 511033, 510400, 510362, 508099, 507937, 443595, 500030, 499528, 499441, 498283, 496053, 490096, 489211, 488731, 488633, 488213, 488078, 488019, 486773, 485312, 483708, 482200, 482354, 482367, 481623, 481097, 479745, 1649, 474755, 472428, 472130, 472094, 471992, 70653, 3720, 468476, 464817, 466829, 465606, 465393, 464782, 35043, 463454, 462946, 13260, 52537, 457309, 455070, 447890, 447851, 447595, 440887, 446144, 445620, 444939, 443258, 442825, 441662, 440644, 440306, 440282, 440264, 440233, 439951, 438986, 436461, 436455, 434183, 434173, 434106, 431286, 427014, 425891, 423249, 420707, 73114, 418306, 416772, 415200, 2063, 415202, 415193, 409393, 408583, 344615, 407225, 405670, 91070, 404211, 403881, 403561, 395598, 393056, 2048, 390782, 388357, 387917, 387776, 166363, 385758, 383915, 382454, 380897, 380811, 380685, 378387, 373449, 371273, 368764, 2108, 368021, 366766, 365808, 365195, 342044, 355123, 354862, 354307, 353935, 336417, 346356, 346334, 345947, 7878, 343501, 343362, 343323, 334025, 337994, 336796, 336907, 335012, 335003, 333821, 4898, 330722, 328026, 327706, 325572, 322346, 317434, 1781, 1689, 2054, 309692, 300206, 295150, 295093, 287040, 272235, 267698, 263318, 261541, 261569, 261593, 260739, 256317, 245273, 237601, 227820, 207847, 40926, 196406, 191792, 191056, 187826, 196527, 174267, 173684, 173382, 169326, 10356, 169159, 166675, 163961, 145024, 154030, 134833, 129327, 127015, 118711, 110215, 97593, 88510, 87447, 1770, 79708, 58776, 52833, 50308, 46785, 52635, 43204, 29633, 28325, 25616, 24720, 24194, 52594, 40800, 21755, 17527, 16097, 15249, 9933, 40875, 7865, 7874, 7683, 5104, 4675, 5054, 4134, 5042, 2043, 2029, 2026, 1997, 1908, 1488, 843, 1065, 941, 922, 173420, 63797, 128417, 4195, 555000, 554746, 554726, 553972, 553932, 553892, 553555, 553178, 559461, 559446, 562523, 562085, 561281, 561173, 561118, 533541, 555640, 559480, 543509, 557147, 560596, 559903, 537519, 550798, 550362, 550336, 537433, 536881, 545575, 547917, 545907, 545857, 553078, 552832, 552282, 558538, 557627, 557202, 560245, 560621, 561000, 561050, 561020, 560230, 560327, 562624, 562579, 562546, 562825, 563170, 567154, 565661, 565647, 565611, 565593, 565432, 565355, 565108, 563319, 567760, 568692, 568356, 568026, 567828, 570352, 570305, 570278, 569898, 569861, 569649, 568760, 571931, 571909, 571892, 571867, 2100, 572565, 573395, 572670, 572644, 573437, 573404, 574752, 573916, 573665, 575579, 575552, 575521, 582240, 565128, 581563, 581511, 578902, 578872, 578855, 576159, 576092, 576080, 583358, 585639, 581887, 585992, 586469, 583218, 588667, 588603, 588584, 587887, 589337, 588770, 589774, 589363, 589802, 582356, 591018, 589831, 593639, 593596, 593583, 593564, 594081, 596196, 4687, 594641, 596731, 596706, 596538, 596820, 596799, 596767, 7674, 596839, 599912, 599775, 599527, 598390, 600718, 600687, 601878, 601688, 601016, 600757, 601918, 601914, 601910, 601904, 601930, 601924, 602304, 602292, 602514, 602507, 602492, 602314, 603022, 602591, 602535, 603077, 603035, 603576, 603157, 603814, 603800, 603749, 603725, 603659, 603707, 603674, 603641, 441023, 643082, 643055, 643037, 676763, 676755, 526944, 675982, 674711, 673819, 671724, 671568, 678843, 678492, 678487, 678484, 472421, 678860, 679548, 679531, 678902, 681108, 679889, 685436, 682251, 685447, 685844, 685486, 686095, 686075, 689015, 689008, 687853, 689477, 689040, 689034,];

        foreach ( $products_id as $product_id ) {

            $query = $wpdb->prepare("
                SELECT COUNT(DISTINCT posts.ID) AS order_count
                FROM wp_posts AS posts
                INNER JOIN wp_woocommerce_order_items AS order_items ON posts.ID = order_items.order_id
                INNER JOIN wp_woocommerce_order_itemmeta AS item_meta ON order_items.order_item_id = item_meta.order_item_id
                WHERE posts.post_type = 'shop_order'
                  AND posts.post_status = 'wc-walletx'
                  AND posts.post_date BETWEEN %s AND %s
                  AND item_meta.meta_key = '_product_id'
                  AND item_meta.meta_value = %d
            ", '2024-03-21 00:00:00', '2025-03-21 23:59:59', $product_id);

            $tickets_sold = (int)($wpdb->get_col($query))[0];

//            update_post_meta($product_id, 'tickets_sold', $tickets_sold);

            $detail[$product_id] = $tickets_sold;
        }

        saeed_store($detail);

        die();
    }

/**
 * GET: get_owners_info
 *
 * هدف: خروجی اطلاعات مالکان
 * استفاده: گزارش
 * وابستگی: users, meta
 * امنیت: بدون احراز هویت
 * وضعیت: بررسی
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:461
 */
    if( isset($_GET['get_owners_info']) ) {

//        $owners_data = 'a:577:{i:0;a:5:{s:2:"id";s:4:"3206";s:15:"registered_date";s:10:"1401/07/20";s:18:"total_tickets_sold";s:5:"11086";s:6:"points";d:6452;s:12:"days_elapsed";i:919;}i:1;a:5:{s:2:"id";s:4:"5164";s:15:"registered_date";s:10:"1402/03/10";s:18:"total_tickets_sold";s:5:"10575";s:6:"points";d:5583;s:12:"days_elapsed";i:686;}i:2;a:5:{s:2:"id";s:4:"5143";s:15:"registered_date";s:10:"1402/03/09";s:18:"total_tickets_sold";s:5:"10128";s:6:"points";d:5437;s:12:"days_elapsed";i:687;}i:3;a:5:{s:2:"id";s:4:"7543";s:15:"registered_date";s:10:"1402/09/05";s:18:"total_tickets_sold";s:4:"9975";s:6:"points";d:4846;s:12:"days_elapsed";i:507;}i:4;a:5:{s:2:"id";s:4:"2272";s:15:"registered_date";s:10:"1400/08/24";s:18:"total_tickets_sold";s:4:"8051";s:6:"points";d:6431;s:12:"days_elapsed";i:1249;}i:5;a:5:{s:2:"id";s:4:"7527";s:15:"registered_date";s:10:"1402/08/14";s:18:"total_tickets_sold";s:4:"7680";s:6:"points";d:4147;s:12:"days_elapsed";i:529;}i:6;a:5:{s:2:"id";s:4:"5206";s:15:"registered_date";s:10:"1402/03/13";s:18:"total_tickets_sold";s:4:"7273";s:6:"points";d:4473;s:12:"days_elapsed";i:683;}i:7;a:5:{s:2:"id";s:4:"3350";s:15:"registered_date";s:10:"1401/10/19";s:18:"total_tickets_sold";s:4:"7050";s:6:"points";d:4834;s:12:"days_elapsed";i:828;}i:8;a:5:{s:2:"id";s:4:"3082";s:15:"registered_date";s:10:"1401/02/28";s:18:"total_tickets_sold";s:4:"6883";s:6:"points";d:5477;s:12:"days_elapsed";i:1061;}i:9;a:5:{s:2:"id";s:4:"7766";s:15:"registered_date";s:10:"1403/03/12";s:18:"total_tickets_sold";s:4:"6290";s:6:"points";d:3054;s:12:"days_elapsed";i:319;}i:10;a:5:{s:2:"id";s:4:"1620";s:15:"registered_date";s:10:"1400/07/22";s:18:"total_tickets_sold";s:4:"6053";s:6:"points";d:5864;s:12:"days_elapsed";i:1282;}i:11;a:5:{s:2:"id";s:4:"3878";s:15:"registered_date";s:10:"1402/01/23";s:18:"total_tickets_sold";s:4:"6005";s:6:"points";d:4198;s:12:"days_elapsed";i:732;}i:12;a:5:{s:2:"id";s:4:"7683";s:15:"registered_date";s:10:"1402/12/24";s:18:"total_tickets_sold";s:4:"5028";s:6:"points";d:2867;s:12:"days_elapsed";i:397;}i:13;a:5:{s:2:"id";s:4:"7454";s:15:"registered_date";s:10:"1402/05/31";s:18:"total_tickets_sold";s:4:"4965";s:6:"points";d:3467;s:12:"days_elapsed";i:604;}i:14;a:5:{s:2:"id";s:4:"3086";s:15:"registered_date";s:10:"1401/02/31";s:18:"total_tickets_sold";s:4:"4785";s:6:"points";d:4769;s:12:"days_elapsed";i:1058;}i:15;a:5:{s:2:"id";s:4:"8044";s:15:"registered_date";s:10:"1403/09/26";s:18:"total_tickets_sold";s:4:"4755";s:6:"points";d:1948;s:12:"days_elapsed";i:121;}i:16;a:5:{s:2:"id";s:4:"7460";s:15:"registered_date";s:10:"1402/06/05";s:18:"total_tickets_sold";s:4:"4607";s:6:"points";d:3333;s:12:"days_elapsed";i:599;}i:17;a:5:{s:2:"id";s:4:"7760";s:15:"registered_date";s:10:"1403/03/06";s:18:"total_tickets_sold";s:4:"4411";s:6:"points";d:2445;s:12:"days_elapsed";i:325;}i:18;a:5:{s:2:"id";s:4:"3290";s:15:"registered_date";s:10:"1401/09/02";s:18:"total_tickets_sold";s:4:"4408";s:6:"points";d:4094;s:12:"days_elapsed";i:875;}i:19;a:5:{s:2:"id";s:4:"7723";s:15:"registered_date";s:10:"1403/02/05";s:18:"total_tickets_sold";s:4:"4401";s:6:"points";d:2529;s:12:"days_elapsed";i:354;}i:20;a:5:{s:2:"id";s:4:"3709";s:15:"registered_date";s:10:"1401/12/24";s:18:"total_tickets_sold";s:4:"4367";s:6:"points";d:3742;s:12:"days_elapsed";i:762;}i:21;a:5:{s:2:"id";s:4:"7672";s:15:"registered_date";s:10:"1402/12/17";s:18:"total_tickets_sold";s:4:"4220";s:6:"points";d:2619;s:12:"days_elapsed";i:404;}i:22;a:5:{s:2:"id";s:4:"3393";s:15:"registered_date";s:10:"1401/11/20";s:18:"total_tickets_sold";s:4:"4163";s:6:"points";d:3776;s:12:"days_elapsed";i:796;}i:23;a:5:{s:2:"id";s:4:"2783";s:15:"registered_date";s:10:"1400/10/18";s:18:"total_tickets_sold";s:4:"4098";s:6:"points";d:4948;s:12:"days_elapsed";i:1194;}i:24;a:5:{s:2:"id";s:4:"7514";s:15:"registered_date";s:10:"1402/07/29";s:18:"total_tickets_sold";s:4:"3988";s:6:"points";d:2964;s:12:"days_elapsed";i:545;}i:25;a:5:{s:2:"id";s:4:"3019";s:15:"registered_date";s:10:"1400/12/26";s:18:"total_tickets_sold";s:4:"3984";s:6:"points";d:4703;s:12:"days_elapsed";i:1125;}i:26;a:5:{s:2:"id";s:4:"7793";s:15:"registered_date";s:10:"1403/04/10";s:18:"total_tickets_sold";s:4:"3928";s:6:"points";d:2179;s:12:"days_elapsed";i:290;}i:27;a:5:{s:2:"id";s:4:"7455";s:15:"registered_date";s:10:"1402/06/01";s:18:"total_tickets_sold";s:4:"3864";s:6:"points";d:3097;s:12:"days_elapsed";i:603;}i:28;a:5:{s:2:"id";s:4:"8039";s:15:"registered_date";s:10:"1403/09/21";s:18:"total_tickets_sold";s:4:"3858";s:6:"points";d:1664;s:12:"days_elapsed";i:126;}i:29;a:5:{s:2:"id";s:4:"4187";s:15:"registered_date";s:10:"1402/02/06";s:18:"total_tickets_sold";s:4:"3831";s:6:"points";d:3431;s:12:"days_elapsed";i:718;}i:30;a:5:{s:2:"id";s:4:"1404";s:15:"registered_date";s:10:"1400/07/10";s:18:"total_tickets_sold";s:4:"3817";s:6:"points";d:5154;s:12:"days_elapsed";i:1294;}i:31;a:5:{s:2:"id";s:4:"7588";s:15:"registered_date";s:10:"1402/10/21";s:18:"total_tickets_sold";s:4:"3767";s:6:"points";d:2639;s:12:"days_elapsed";i:461;}i:32;a:5:{s:2:"id";s:4:"7568";s:15:"registered_date";s:10:"1402/10/03";s:18:"total_tickets_sold";s:4:"3758";s:6:"points";d:2690;s:12:"days_elapsed";i:479;}i:33;a:5:{s:2:"id";s:4:"3182";s:15:"registered_date";s:10:"1401/06/20";s:18:"total_tickets_sold";s:4:"3732";s:6:"points";d:4091;s:12:"days_elapsed";i:949;}i:34;a:5:{s:2:"id";s:4:"2758";s:15:"registered_date";s:10:"1400/09/20";s:18:"total_tickets_sold";s:4:"3365";s:6:"points";d:4788;s:12:"days_elapsed";i:1222;}i:35;a:5:{s:2:"id";s:4:"7465";s:15:"registered_date";s:10:"1402/06/11";s:18:"total_tickets_sold";s:4:"3258";s:6:"points";d:2865;s:12:"days_elapsed";i:593;}i:36;a:5:{s:2:"id";s:4:"7449";s:15:"registered_date";s:10:"1402/05/29";s:18:"total_tickets_sold";s:4:"3181";s:6:"points";d:2878;s:12:"days_elapsed";i:606;}i:37;a:5:{s:2:"id";s:4:"7689";s:15:"registered_date";s:10:"1402/12/28";s:18:"total_tickets_sold";s:4:"3118";s:6:"points";d:2218;s:12:"days_elapsed";i:393;}i:38;a:5:{s:2:"id";s:4:"7976";s:15:"registered_date";s:10:"1403/08/19";s:18:"total_tickets_sold";s:4:"3005";s:6:"points";d:1479;s:12:"days_elapsed";i:159;}i:39;a:5:{s:2:"id";s:4:"1551";s:15:"registered_date";s:10:"1400/07/18";s:18:"total_tickets_sold";s:4:"2993";s:6:"points";d:4856;s:12:"days_elapsed";i:1286;}i:40;a:5:{s:2:"id";s:4:"8037";s:15:"registered_date";s:10:"1403/09/19";s:18:"total_tickets_sold";s:4:"2893";s:6:"points";d:1348;s:12:"days_elapsed";i:128;}i:41;a:5:{s:2:"id";s:4:"7631";s:15:"registered_date";s:10:"1402/12/08";s:18:"total_tickets_sold";s:4:"2830";s:6:"points";d:2182;s:12:"days_elapsed";i:413;}i:42;a:5:{s:2:"id";s:4:"7846";s:15:"registered_date";s:10:"1403/05/14";s:18:"total_tickets_sold";s:4:"2769";s:6:"points";d:1691;s:12:"days_elapsed";i:256;}i:43;a:5:{s:2:"id";s:4:"7492";s:15:"registered_date";s:10:"1402/07/03";s:18:"total_tickets_sold";s:4:"2766";s:6:"points";d:2635;s:12:"days_elapsed";i:571;}i:44;a:5:{s:2:"id";s:4:"7659";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:4:"2756";s:6:"points";d:2152;s:12:"days_elapsed";i:411;}i:45;a:5:{s:2:"id";s:4:"7575";s:15:"registered_date";s:10:"1402/10/07";s:18:"total_tickets_sold";s:4:"2735";s:6:"points";d:2337;s:12:"days_elapsed";i:475;}i:46;a:5:{s:2:"id";s:4:"7542";s:15:"registered_date";s:10:"1402/09/04";s:18:"total_tickets_sold";s:4:"2725";s:6:"points";d:2432;s:12:"days_elapsed";i:508;}i:47;a:5:{s:2:"id";s:4:"3107";s:15:"registered_date";s:10:"1401/03/30";s:18:"total_tickets_sold";s:4:"2703";s:6:"points";d:3994;s:12:"days_elapsed";i:1031;}i:48;a:5:{s:2:"id";s:4:"7627";s:15:"registered_date";s:10:"1402/12/05";s:18:"total_tickets_sold";s:4:"2643";s:6:"points";d:2129;s:12:"days_elapsed";i:416;}i:49;a:5:{s:2:"id";s:4:"1497";s:15:"registered_date";s:10:"1400/07/15";s:18:"total_tickets_sold";s:4:"2640";s:6:"points";d:4747;s:12:"days_elapsed";i:1289;}i:50;a:5:{s:2:"id";s:4:"3117";s:15:"registered_date";s:10:"1401/04/15";s:18:"total_tickets_sold";s:4:"2543";s:6:"points";d:3893;s:12:"days_elapsed";i:1015;}i:51;a:5:{s:2:"id";s:4:"7421";s:15:"registered_date";s:10:"1402/04/31";s:18:"total_tickets_sold";s:4:"2510";s:6:"points";d:2739;s:12:"days_elapsed";i:634;}i:52;a:5:{s:2:"id";s:4:"7764";s:15:"registered_date";s:10:"1403/03/11";s:18:"total_tickets_sold";s:4:"2460";s:6:"points";d:1780;s:12:"days_elapsed";i:320;}i:53;a:5:{s:2:"id";s:4:"6962";s:15:"registered_date";s:10:"1402/04/18";s:18:"total_tickets_sold";s:4:"2458";s:6:"points";d:2760;s:12:"days_elapsed";i:647;}i:54;a:5:{s:2:"id";s:4:"3128";s:15:"registered_date";s:10:"1401/05/05";s:18:"total_tickets_sold";s:4:"2430";s:6:"points";d:3795;s:12:"days_elapsed";i:995;}i:55;a:5:{s:2:"id";s:4:"8081";s:15:"registered_date";s:10:"1403/11/11";s:18:"total_tickets_sold";s:4:"2422";s:6:"points";d:1032;s:12:"days_elapsed";i:75;}i:56;a:5:{s:2:"id";s:4:"7502";s:15:"registered_date";s:10:"1402/07/17";s:18:"total_tickets_sold";s:4:"2420";s:6:"points";d:2478;s:12:"days_elapsed";i:557;}i:57;a:5:{s:2:"id";s:4:"3020";s:15:"registered_date";s:10:"1400/12/26";s:18:"total_tickets_sold";s:4:"2415";s:6:"points";d:4180;s:12:"days_elapsed";i:1125;}i:58;a:5:{s:2:"id";s:4:"7552";s:15:"registered_date";s:10:"1402/09/16";s:18:"total_tickets_sold";s:4:"2373";s:6:"points";d:2279;s:12:"days_elapsed";i:496;}i:59;a:5:{s:2:"id";s:4:"6274";s:15:"registered_date";s:10:"1402/04/05";s:18:"total_tickets_sold";s:4:"2353";s:6:"points";d:2764;s:12:"days_elapsed";i:660;}i:60;a:5:{s:2:"id";s:4:"7519";s:15:"registered_date";s:10:"1402/08/07";s:18:"total_tickets_sold";s:4:"2299";s:6:"points";d:2374;s:12:"days_elapsed";i:536;}i:61;a:5:{s:2:"id";s:4:"3114";s:15:"registered_date";s:10:"1401/04/12";s:18:"total_tickets_sold";s:4:"2288";s:6:"points";d:3817;s:12:"days_elapsed";i:1018;}i:62;a:5:{s:2:"id";s:4:"7576";s:15:"registered_date";s:10:"1402/10/14";s:18:"total_tickets_sold";s:4:"2284";s:6:"points";d:2165;s:12:"days_elapsed";i:468;}i:63;a:5:{s:2:"id";s:4:"7701";s:15:"registered_date";s:10:"1403/01/12";s:18:"total_tickets_sold";s:4:"2252";s:6:"points";d:1885;s:12:"days_elapsed";i:378;}i:64;a:5:{s:2:"id";s:4:"7879";s:15:"registered_date";s:10:"1403/06/06";s:18:"total_tickets_sold";s:4:"2252";s:6:"points";d:1450;s:12:"days_elapsed";i:233;}i:65;a:5:{s:2:"id";s:4:"8068";s:15:"registered_date";s:10:"1403/11/03";s:18:"total_tickets_sold";s:4:"2202";s:6:"points";d:983;s:12:"days_elapsed";i:83;}i:66;a:5:{s:2:"id";s:4:"7870";s:15:"registered_date";s:10:"1403/05/27";s:18:"total_tickets_sold";s:4:"2197";s:6:"points";d:1461;s:12:"days_elapsed";i:243;}i:67;a:5:{s:2:"id";s:4:"7734";s:15:"registered_date";s:10:"1403/02/19";s:18:"total_tickets_sold";s:4:"2195";s:6:"points";d:1752;s:12:"days_elapsed";i:340;}i:68;a:5:{s:2:"id";s:4:"7607";s:15:"registered_date";s:10:"1402/11/14";s:18:"total_tickets_sold";s:4:"2189";s:6:"points";d:2041;s:12:"days_elapsed";i:437;}i:69;a:5:{s:2:"id";s:4:"8063";s:15:"registered_date";s:10:"1403/10/20";s:18:"total_tickets_sold";s:4:"2187";s:6:"points";d:1020;s:12:"days_elapsed";i:97;}i:70;a:5:{s:2:"id";s:4:"7562";s:15:"registered_date";s:10:"1402/09/28";s:18:"total_tickets_sold";s:4:"2166";s:6:"points";d:2174;s:12:"days_elapsed";i:484;}i:71;a:5:{s:2:"id";s:4:"3405";s:15:"registered_date";s:10:"1401/11/26";s:18:"total_tickets_sold";s:4:"2144";s:6:"points";d:3085;s:12:"days_elapsed";i:790;}i:72;a:5:{s:2:"id";s:4:"3042";s:15:"registered_date";s:10:"1401/01/30";s:18:"total_tickets_sold";s:4:"2086";s:6:"points";d:3965;s:12:"days_elapsed";i:1090;}i:73;a:5:{s:2:"id";s:4:"7439";s:15:"registered_date";s:10:"1402/05/21";s:18:"total_tickets_sold";s:4:"2083";s:6:"points";d:2536;s:12:"days_elapsed";i:614;}i:74;a:5:{s:2:"id";s:4:"3080";s:15:"registered_date";s:10:"1401/02/27";s:18:"total_tickets_sold";s:4:"2080";s:6:"points";d:3879;s:12:"days_elapsed";i:1062;}i:75;a:5:{s:2:"id";s:4:"7780";s:15:"registered_date";s:10:"1403/03/26";s:18:"total_tickets_sold";s:4:"2064";s:6:"points";d:1603;s:12:"days_elapsed";i:305;}i:76;a:5:{s:2:"id";s:4:"7488";s:15:"registered_date";s:10:"1402/07/02";s:18:"total_tickets_sold";s:4:"2058";s:6:"points";d:2402;s:12:"days_elapsed";i:572;}i:77;a:5:{s:2:"id";s:4:"6276";s:15:"registered_date";s:10:"1402/04/05";s:18:"total_tickets_sold";s:4:"2045";s:6:"points";d:2662;s:12:"days_elapsed";i:660;}i:78;a:5:{s:2:"id";s:4:"7666";s:15:"registered_date";s:10:"1402/12/15";s:18:"total_tickets_sold";s:4:"2025";s:6:"points";d:1893;s:12:"days_elapsed";i:406;}i:79;a:5:{s:2:"id";s:4:"4374";s:15:"registered_date";s:10:"1402/02/13";s:18:"total_tickets_sold";s:4:"2021";s:6:"points";d:2807;s:12:"days_elapsed";i:711;}i:80;a:5:{s:2:"id";s:4:"7726";s:15:"registered_date";s:10:"1403/02/08";s:18:"total_tickets_sold";s:4:"2019";s:6:"points";d:1726;s:12:"days_elapsed";i:351;}i:81;a:5:{s:2:"id";s:4:"2849";s:15:"registered_date";s:10:"1400/11/21";s:18:"total_tickets_sold";s:4:"2010";s:6:"points";d:4150;s:12:"days_elapsed";i:1160;}i:82;a:5:{s:2:"id";s:4:"3320";s:15:"registered_date";s:10:"1401/10/04";s:18:"total_tickets_sold";s:4:"2008";s:6:"points";d:3198;s:12:"days_elapsed";i:843;}i:83;a:5:{s:2:"id";s:4:"7497";s:15:"registered_date";s:10:"1402/07/08";s:18:"total_tickets_sold";s:4:"2008";s:6:"points";d:2367;s:12:"days_elapsed";i:566;}i:84;a:5:{s:2:"id";s:4:"3322";s:15:"registered_date";s:10:"1401/10/05";s:18:"total_tickets_sold";s:4:"1998";s:6:"points";d:3192;s:12:"days_elapsed";i:842;}i:85;a:5:{s:2:"id";s:3:"491";s:15:"registered_date";s:10:"1400/04/08";s:18:"total_tickets_sold";s:4:"1970";s:6:"points";d:4818;s:12:"days_elapsed";i:1387;}i:86;a:5:{s:2:"id";s:4:"7875";s:15:"registered_date";s:10:"1403/06/01";s:18:"total_tickets_sold";s:4:"1936";s:6:"points";d:1359;s:12:"days_elapsed";i:238;}i:87;a:5:{s:2:"id";s:4:"3708";s:15:"registered_date";s:10:"1401/12/24";s:18:"total_tickets_sold";s:4:"1931";s:6:"points";d:2930;s:12:"days_elapsed";i:762;}i:88;a:5:{s:2:"id";s:4:"2773";s:15:"registered_date";s:10:"1400/09/30";s:18:"total_tickets_sold";s:4:"1916";s:6:"points";d:4275;s:12:"days_elapsed";i:1212;}i:89;a:5:{s:2:"id";s:4:"8029";s:15:"registered_date";s:10:"1403/09/14";s:18:"total_tickets_sold";s:4:"1883";s:6:"points";d:1027;s:12:"days_elapsed";i:133;}i:90;a:5:{s:2:"id";s:4:"3445";s:15:"registered_date";s:10:"1401/12/07";s:18:"total_tickets_sold";s:4:"1880";s:6:"points";d:2964;s:12:"days_elapsed";i:779;}i:91;a:5:{s:2:"id";s:4:"3395";s:15:"registered_date";s:10:"1401/11/20";s:18:"total_tickets_sold";s:4:"1876";s:6:"points";d:3013;s:12:"days_elapsed";i:796;}i:92;a:5:{s:2:"id";s:4:"3147";s:15:"registered_date";s:10:"1401/06/05";s:18:"total_tickets_sold";s:4:"1872";s:6:"points";d:3516;s:12:"days_elapsed";i:964;}i:93;a:5:{s:2:"id";s:4:"7596";s:15:"registered_date";s:10:"1402/11/02";s:18:"total_tickets_sold";s:4:"1857";s:6:"points";d:1966;s:12:"days_elapsed";i:449;}i:94;a:5:{s:2:"id";s:4:"3338";s:15:"registered_date";s:10:"1401/10/12";s:18:"total_tickets_sold";s:4:"1854";s:6:"points";d:3123;s:12:"days_elapsed";i:835;}i:95;a:5:{s:2:"id";s:4:"3502";s:15:"registered_date";s:10:"1401/12/16";s:18:"total_tickets_sold";s:4:"1852";s:6:"points";d:2927;s:12:"days_elapsed";i:770;}i:96;a:5:{s:2:"id";s:4:"7658";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:4:"1851";s:6:"points";d:1850;s:12:"days_elapsed";i:411;}i:97;a:5:{s:2:"id";s:4:"7506";s:15:"registered_date";s:10:"1402/07/23";s:18:"total_tickets_sold";s:4:"1816";s:6:"points";d:2258;s:12:"days_elapsed";i:551;}i:98;a:5:{s:2:"id";s:4:"3293";s:15:"registered_date";s:10:"1401/09/06";s:18:"total_tickets_sold";s:4:"1799";s:6:"points";d:3213;s:12:"days_elapsed";i:871;}i:99;a:5:{s:2:"id";s:4:"7660";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:4:"1791";s:6:"points";d:1830;s:12:"days_elapsed";i:411;}i:100;a:5:{s:2:"id";s:4:"7776";s:15:"registered_date";s:10:"1403/03/20";s:18:"total_tickets_sold";s:4:"1782";s:6:"points";d:1527;s:12:"days_elapsed";i:311;}i:101;a:5:{s:2:"id";s:4:"7536";s:15:"registered_date";s:10:"1402/08/30";s:18:"total_tickets_sold";s:4:"1774";s:6:"points";d:2130;s:12:"days_elapsed";i:513;}i:102;a:5:{s:2:"id";s:4:"7854";s:15:"registered_date";s:10:"1403/05/18";s:18:"total_tickets_sold";s:4:"1772";s:6:"points";d:1347;s:12:"days_elapsed";i:252;}i:103;a:5:{s:2:"id";s:4:"2636";s:15:"registered_date";s:10:"1400/09/11";s:18:"total_tickets_sold";s:4:"1770";s:6:"points";d:4283;s:12:"days_elapsed";i:1231;}i:104;a:5:{s:2:"id";s:4:"4667";s:15:"registered_date";s:10:"1402/02/23";s:18:"total_tickets_sold";s:4:"1758";s:6:"points";d:2689;s:12:"days_elapsed";i:701;}i:105;a:5:{s:2:"id";s:4:"7612";s:15:"registered_date";s:10:"1402/11/21";s:18:"total_tickets_sold";s:4:"1757";s:6:"points";d:1876;s:12:"days_elapsed";i:430;}i:106;a:5:{s:2:"id";s:4:"5208";s:15:"registered_date";s:10:"1402/03/13";s:18:"total_tickets_sold";s:4:"1722";s:6:"points";d:2623;s:12:"days_elapsed";i:683;}i:107;a:5:{s:2:"id";s:4:"4137";s:15:"registered_date";s:10:"1402/02/04";s:18:"total_tickets_sold";s:4:"1710";s:6:"points";d:2730;s:12:"days_elapsed";i:720;}i:108;a:5:{s:2:"id";s:4:"6237";s:15:"registered_date";s:10:"1402/04/04";s:18:"total_tickets_sold";s:4:"1706";s:6:"points";d:2552;s:12:"days_elapsed";i:661;}i:109;a:5:{s:2:"id";s:4:"7770";s:15:"registered_date";s:10:"1403/03/14";s:18:"total_tickets_sold";s:4:"1689";s:6:"points";d:1514;s:12:"days_elapsed";i:317;}i:110;a:5:{s:2:"id";s:4:"6242";s:15:"registered_date";s:10:"1402/04/04";s:18:"total_tickets_sold";s:4:"1674";s:6:"points";d:2541;s:12:"days_elapsed";i:661;}i:111;a:5:{s:2:"id";s:4:"4354";s:15:"registered_date";s:10:"1402/02/12";s:18:"total_tickets_sold";s:4:"1662";s:6:"points";d:2690;s:12:"days_elapsed";i:712;}i:112;a:5:{s:2:"id";s:4:"7887";s:15:"registered_date";s:10:"1403/06/15";s:18:"total_tickets_sold";s:4:"1652";s:6:"points";d:1223;s:12:"days_elapsed";i:224;}i:113;a:5:{s:2:"id";s:4:"7919";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:4:"1651";s:6:"points";d:1150;s:12:"days_elapsed";i:200;}i:114;a:5:{s:2:"id";s:4:"7489";s:15:"registered_date";s:10:"1402/07/03";s:18:"total_tickets_sold";s:4:"1637";s:6:"points";d:2259;s:12:"days_elapsed";i:571;}i:115;a:5:{s:2:"id";s:4:"7902";s:15:"registered_date";s:10:"1403/07/03";s:18:"total_tickets_sold";s:4:"1613";s:6:"points";d:1156;s:12:"days_elapsed";i:206;}i:116;a:5:{s:2:"id";s:4:"7765";s:15:"registered_date";s:10:"1403/03/12";s:18:"total_tickets_sold";s:4:"1608";s:6:"points";d:1493;s:12:"days_elapsed";i:319;}i:117;a:5:{s:2:"id";s:4:"7908";s:15:"registered_date";s:10:"1403/07/05";s:18:"total_tickets_sold";s:4:"1608";s:6:"points";d:1148;s:12:"days_elapsed";i:204;}i:118;a:5:{s:2:"id";s:4:"7936";s:15:"registered_date";s:10:"1403/07/14";s:18:"total_tickets_sold";s:4:"1561";s:6:"points";d:1105;s:12:"days_elapsed";i:195;}i:119;a:5:{s:2:"id";s:4:"7538";s:15:"registered_date";s:10:"1402/09/01";s:18:"total_tickets_sold";s:4:"1514";s:6:"points";d:2038;s:12:"days_elapsed";i:511;}i:120;a:5:{s:2:"id";s:4:"8097";s:15:"registered_date";s:10:"1403/11/23";s:18:"total_tickets_sold";s:4:"1510";s:6:"points";d:692;s:12:"days_elapsed";i:63;}i:121;a:5:{s:2:"id";s:4:"7722";s:15:"registered_date";s:10:"1403/02/05";s:18:"total_tickets_sold";s:4:"1508";s:6:"points";d:1565;s:12:"days_elapsed";i:354;}i:122;a:5:{s:2:"id";s:4:"3403";s:15:"registered_date";s:10:"1401/11/24";s:18:"total_tickets_sold";s:4:"1489";s:6:"points";d:2872;s:12:"days_elapsed";i:792;}i:123;a:5:{s:2:"id";s:4:"4460";s:15:"registered_date";s:10:"1402/02/16";s:18:"total_tickets_sold";s:4:"1486";s:6:"points";d:2619;s:12:"days_elapsed";i:708;}i:124;a:5:{s:2:"id";s:4:"3184";s:15:"registered_date";s:10:"1401/06/21";s:18:"total_tickets_sold";s:4:"1485";s:6:"points";d:3339;s:12:"days_elapsed";i:948;}i:125;a:5:{s:2:"id";s:4:"7990";s:15:"registered_date";s:10:"1403/08/27";s:18:"total_tickets_sold";s:4:"1481";s:6:"points";d:947;s:12:"days_elapsed";i:151;}i:126;a:5:{s:2:"id";s:5:"18313";s:15:"registered_date";s:10:"1403/12/27";s:18:"total_tickets_sold";s:4:"1479";s:6:"points";d:580;s:12:"days_elapsed";i:29;}i:127;a:5:{s:2:"id";s:4:"3818";s:15:"registered_date";s:10:"1402/01/20";s:18:"total_tickets_sold";s:4:"1456";s:6:"points";d:2690;s:12:"days_elapsed";i:735;}i:128;a:5:{s:2:"id";s:4:"7787";s:15:"registered_date";s:10:"1403/04/03";s:18:"total_tickets_sold";s:4:"1428";s:6:"points";d:1367;s:12:"days_elapsed";i:297;}i:129;a:5:{s:2:"id";s:4:"7740";s:15:"registered_date";s:10:"1403/02/20";s:18:"total_tickets_sold";s:4:"1418";s:6:"points";d:1490;s:12:"days_elapsed";i:339;}i:130;a:5:{s:2:"id";s:4:"3238";s:15:"registered_date";s:10:"1401/08/15";s:18:"total_tickets_sold";s:4:"1416";s:6:"points";d:3151;s:12:"days_elapsed";i:893;}i:131;a:5:{s:2:"id";s:4:"7595";s:15:"registered_date";s:10:"1402/11/01";s:18:"total_tickets_sold";s:4:"1406";s:6:"points";d:1819;s:12:"days_elapsed";i:450;}i:132;a:5:{s:2:"id";s:4:"4191";s:15:"registered_date";s:10:"1402/02/06";s:18:"total_tickets_sold";s:4:"1402";s:6:"points";d:2621;s:12:"days_elapsed";i:718;}i:133;a:5:{s:2:"id";s:4:"7580";s:15:"registered_date";s:10:"1402/10/17";s:18:"total_tickets_sold";s:4:"1394";s:6:"points";d:1860;s:12:"days_elapsed";i:465;}i:134;a:5:{s:2:"id";s:4:"7628";s:15:"registered_date";s:10:"1402/12/06";s:18:"total_tickets_sold";s:4:"1385";s:6:"points";d:1707;s:12:"days_elapsed";i:415;}i:135;a:5:{s:2:"id";s:4:"7625";s:15:"registered_date";s:10:"1402/12/03";s:18:"total_tickets_sold";s:4:"1381";s:6:"points";d:1714;s:12:"days_elapsed";i:418;}i:136;a:5:{s:2:"id";s:4:"5144";s:15:"registered_date";s:10:"1402/03/09";s:18:"total_tickets_sold";s:4:"1377";s:6:"points";d:2520;s:12:"days_elapsed";i:687;}i:137;a:5:{s:2:"id";s:4:"7604";s:15:"registered_date";s:10:"1402/11/14";s:18:"total_tickets_sold";s:4:"1354";s:6:"points";d:1762;s:12:"days_elapsed";i:437;}i:138;a:5:{s:2:"id";s:3:"497";s:15:"registered_date";s:10:"1400/04/08";s:18:"total_tickets_sold";s:4:"1314";s:6:"points";d:4599;s:12:"days_elapsed";i:1387;}i:139;a:5:{s:2:"id";s:4:"7956";s:15:"registered_date";s:10:"1403/08/05";s:18:"total_tickets_sold";s:4:"1304";s:6:"points";d:954;s:12:"days_elapsed";i:173;}i:140;a:5:{s:2:"id";s:4:"7456";s:15:"registered_date";s:10:"1402/06/03";s:18:"total_tickets_sold";s:4:"1293";s:6:"points";d:2234;s:12:"days_elapsed";i:601;}i:141;a:5:{s:2:"id";s:4:"7556";s:15:"registered_date";s:10:"1402/09/18";s:18:"total_tickets_sold";s:4:"1281";s:6:"points";d:1909;s:12:"days_elapsed";i:494;}i:142;a:5:{s:2:"id";s:4:"7855";s:15:"registered_date";s:10:"1403/05/18";s:18:"total_tickets_sold";s:4:"1278";s:6:"points";d:1182;s:12:"days_elapsed";i:252;}i:143;a:5:{s:2:"id";s:4:"7970";s:15:"registered_date";s:10:"1403/08/15";s:18:"total_tickets_sold";s:4:"1277";s:6:"points";d:915;s:12:"days_elapsed";i:163;}i:144;a:5:{s:2:"id";s:4:"7801";s:15:"registered_date";s:10:"1403/04/18";s:18:"total_tickets_sold";s:4:"1274";s:6:"points";d:1271;s:12:"days_elapsed";i:282;}i:145;a:5:{s:2:"id";s:4:"2719";s:15:"registered_date";s:10:"1400/09/15";s:18:"total_tickets_sold";s:4:"1256";s:6:"points";d:4100;s:12:"days_elapsed";i:1227;}i:146;a:5:{s:2:"id";s:4:"7445";s:15:"registered_date";s:10:"1402/05/26";s:18:"total_tickets_sold";s:4:"1247";s:6:"points";d:2243;s:12:"days_elapsed";i:609;}i:147;a:5:{s:2:"id";s:4:"2506";s:15:"registered_date";s:10:"1400/09/05";s:18:"total_tickets_sold";s:4:"1236";s:6:"points";d:4123;s:12:"days_elapsed";i:1237;}i:148;a:5:{s:2:"id";s:4:"7699";s:15:"registered_date";s:10:"1403/01/11";s:18:"total_tickets_sold";s:4:"1219";s:6:"points";d:1543;s:12:"days_elapsed";i:379;}i:149;a:5:{s:2:"id";s:4:"3721";s:15:"registered_date";s:10:"1402/01/01";s:18:"total_tickets_sold";s:4:"1212";s:6:"points";d:2666;s:12:"days_elapsed";i:754;}i:150;a:5:{s:2:"id";s:4:"7850";s:15:"registered_date";s:10:"1403/05/16";s:18:"total_tickets_sold";s:4:"1204";s:6:"points";d:1163;s:12:"days_elapsed";i:254;}i:151;a:5:{s:2:"id";s:5:"15681";s:15:"registered_date";s:10:"1403/12/19";s:18:"total_tickets_sold";s:4:"1201";s:6:"points";d:511;s:12:"days_elapsed";i:37;}i:152;a:5:{s:2:"id";s:4:"3436";s:15:"registered_date";s:10:"1401/12/04";s:18:"total_tickets_sold";s:4:"1193";s:6:"points";d:2744;s:12:"days_elapsed";i:782;}i:153;a:5:{s:2:"id";s:4:"3230";s:15:"registered_date";s:10:"1401/08/08";s:18:"total_tickets_sold";s:4:"1174";s:6:"points";d:3091;s:12:"days_elapsed";i:900;}i:154;a:5:{s:2:"id";s:4:"7476";s:15:"registered_date";s:10:"1402/06/22";s:18:"total_tickets_sold";s:4:"1132";s:6:"points";d:2123;s:12:"days_elapsed";i:582;}i:155;a:5:{s:2:"id";s:4:"7957";s:15:"registered_date";s:10:"1403/08/06";s:18:"total_tickets_sold";s:4:"1127";s:6:"points";d:892;s:12:"days_elapsed";i:172;}i:156;a:5:{s:2:"id";s:4:"3183";s:15:"registered_date";s:10:"1401/06/21";s:18:"total_tickets_sold";s:4:"1118";s:6:"points";d:3217;s:12:"days_elapsed";i:948;}i:157;a:5:{s:2:"id";s:4:"3113";s:15:"registered_date";s:10:"1401/04/12";s:18:"total_tickets_sold";s:4:"1104";s:6:"points";d:3422;s:12:"days_elapsed";i:1018;}i:158;a:5:{s:2:"id";s:4:"7981";s:15:"registered_date";s:10:"1403/08/22";s:18:"total_tickets_sold";s:4:"1101";s:6:"points";d:835;s:12:"days_elapsed";i:156;}i:159;a:5:{s:2:"id";s:5:"16322";s:15:"registered_date";s:10:"1403/12/21";s:18:"total_tickets_sold";s:4:"1098";s:6:"points";d:471;s:12:"days_elapsed";i:35;}i:160;a:5:{s:2:"id";s:4:"7938";s:15:"registered_date";s:10:"1403/07/16";s:18:"total_tickets_sold";s:4:"1097";s:6:"points";d:945;s:12:"days_elapsed";i:193;}i:161;a:5:{s:2:"id";s:4:"7757";s:15:"registered_date";s:10:"1403/03/03";s:18:"total_tickets_sold";s:4:"1094";s:6:"points";d:1349;s:12:"days_elapsed";i:328;}i:162;a:5:{s:2:"id";s:4:"7590";s:15:"registered_date";s:10:"1402/10/24";s:18:"total_tickets_sold";s:4:"1092";s:6:"points";d:1738;s:12:"days_elapsed";i:458;}i:163;a:5:{s:2:"id";s:4:"3141";s:15:"registered_date";s:10:"1401/05/27";s:18:"total_tickets_sold";s:4:"1083";s:6:"points";d:3280;s:12:"days_elapsed";i:973;}i:164;a:5:{s:2:"id";s:4:"3321";s:15:"registered_date";s:10:"1401/10/05";s:18:"total_tickets_sold";s:4:"1079";s:6:"points";d:2886;s:12:"days_elapsed";i:842;}i:165;a:5:{s:2:"id";s:4:"7882";s:15:"registered_date";s:10:"1403/06/11";s:18:"total_tickets_sold";s:4:"1063";s:6:"points";d:1038;s:12:"days_elapsed";i:228;}i:166;a:5:{s:2:"id";s:4:"6706";s:15:"registered_date";s:10:"1402/04/13";s:18:"total_tickets_sold";s:4:"1043";s:6:"points";d:2304;s:12:"days_elapsed";i:652;}i:167;a:5:{s:2:"id";s:4:"7851";s:15:"registered_date";s:10:"1403/05/16";s:18:"total_tickets_sold";s:4:"1027";s:6:"points";d:1104;s:12:"days_elapsed";i:254;}i:168;a:5:{s:2:"id";s:4:"7805";s:15:"registered_date";s:10:"1403/04/19";s:18:"total_tickets_sold";s:4:"1023";s:6:"points";d:1184;s:12:"days_elapsed";i:281;}i:169;a:5:{s:2:"id";s:4:"7773";s:15:"registered_date";s:10:"1403/03/19";s:18:"total_tickets_sold";s:4:"1017";s:6:"points";d:1275;s:12:"days_elapsed";i:312;}i:170;a:5:{s:2:"id";s:4:"2775";s:15:"registered_date";s:10:"1400/10/02";s:18:"total_tickets_sold";s:4:"1016";s:6:"points";d:3969;s:12:"days_elapsed";i:1210;}i:171;a:5:{s:2:"id";s:4:"3045";s:15:"registered_date";s:10:"1401/02/04";s:18:"total_tickets_sold";s:4:"1009";s:6:"points";d:3591;s:12:"days_elapsed";i:1085;}i:172;a:5:{s:2:"id";s:4:"2716";s:15:"registered_date";s:10:"1400/09/15";s:18:"total_tickets_sold";s:3:"997";s:6:"points";d:4013;s:12:"days_elapsed";i:1227;}i:173;a:5:{s:2:"id";s:4:"5173";s:15:"registered_date";s:10:"1402/03/10";s:18:"total_tickets_sold";s:3:"997";s:6:"points";d:2390;s:12:"days_elapsed";i:686;}i:174;a:5:{s:2:"id";s:4:"7485";s:15:"registered_date";s:10:"1402/06/29";s:18:"total_tickets_sold";s:3:"996";s:6:"points";d:2057;s:12:"days_elapsed";i:575;}i:175;a:5:{s:2:"id";s:4:"7503";s:15:"registered_date";s:10:"1402/07/17";s:18:"total_tickets_sold";s:3:"990";s:6:"points";d:2001;s:12:"days_elapsed";i:557;}i:176;a:5:{s:2:"id";s:4:"2721";s:15:"registered_date";s:10:"1400/09/15";s:18:"total_tickets_sold";s:3:"987";s:6:"points";d:4010;s:12:"days_elapsed";i:1227;}i:177;a:5:{s:2:"id";s:4:"3298";s:15:"registered_date";s:10:"1401/09/08";s:18:"total_tickets_sold";s:3:"986";s:6:"points";d:2936;s:12:"days_elapsed";i:869;}i:178;a:5:{s:2:"id";s:4:"7598";s:15:"registered_date";s:10:"1402/11/02";s:18:"total_tickets_sold";s:3:"986";s:6:"points";d:1676;s:12:"days_elapsed";i:449;}i:179;a:5:{s:2:"id";s:4:"7690";s:15:"registered_date";s:10:"1402/12/29";s:18:"total_tickets_sold";s:3:"957";s:6:"points";d:1495;s:12:"days_elapsed";i:392;}i:180;a:5:{s:2:"id";s:4:"8047";s:15:"registered_date";s:10:"1403/09/28";s:18:"total_tickets_sold";s:3:"948";s:6:"points";d:673;s:12:"days_elapsed";i:119;}i:181;a:5:{s:2:"id";s:4:"7858";s:15:"registered_date";s:10:"1403/05/21";s:18:"total_tickets_sold";s:3:"946";s:6:"points";d:1062;s:12:"days_elapsed";i:249;}i:182;a:5:{s:2:"id";s:5:"17834";s:15:"registered_date";s:10:"1403/12/25";s:18:"total_tickets_sold";s:3:"944";s:6:"points";d:408;s:12:"days_elapsed";i:31;}i:183;a:5:{s:2:"id";s:4:"7611";s:15:"registered_date";s:10:"1402/11/21";s:18:"total_tickets_sold";s:3:"943";s:6:"points";d:1604;s:12:"days_elapsed";i:430;}i:184;a:5:{s:2:"id";s:4:"7808";s:15:"registered_date";s:10:"1403/04/20";s:18:"total_tickets_sold";s:3:"939";s:6:"points";d:1153;s:12:"days_elapsed";i:280;}i:185;a:5:{s:2:"id";s:4:"3564";s:15:"registered_date";s:10:"1401/12/18";s:18:"total_tickets_sold";s:3:"933";s:6:"points";d:2615;s:12:"days_elapsed";i:768;}i:186;a:5:{s:2:"id";s:4:"7511";s:15:"registered_date";s:10:"1402/07/27";s:18:"total_tickets_sold";s:3:"933";s:6:"points";d:1952;s:12:"days_elapsed";i:547;}i:187;a:5:{s:2:"id";s:4:"7763";s:15:"registered_date";s:10:"1403/03/10";s:18:"total_tickets_sold";s:3:"931";s:6:"points";d:1273;s:12:"days_elapsed";i:321;}i:188;a:5:{s:2:"id";s:4:"8102";s:15:"registered_date";s:10:"1403/11/27";s:18:"total_tickets_sold";s:3:"930";s:6:"points";d:487;s:12:"days_elapsed";i:59;}i:189;a:5:{s:2:"id";s:4:"7470";s:15:"registered_date";s:10:"1402/06/16";s:18:"total_tickets_sold";s:3:"905";s:6:"points";d:2066;s:12:"days_elapsed";i:588;}i:190;a:5:{s:2:"id";s:4:"7752";s:15:"registered_date";s:10:"1403/03/01";s:18:"total_tickets_sold";s:3:"897";s:6:"points";d:1289;s:12:"days_elapsed";i:330;}i:191;a:5:{s:2:"id";s:4:"3371";s:15:"registered_date";s:10:"1401/11/09";s:18:"total_tickets_sold";s:3:"860";s:6:"points";d:2708;s:12:"days_elapsed";i:807;}i:192;a:5:{s:2:"id";s:4:"8038";s:15:"registered_date";s:10:"1403/09/19";s:18:"total_tickets_sold";s:3:"851";s:6:"points";d:668;s:12:"days_elapsed";i:128;}i:193;a:5:{s:2:"id";s:4:"7686";s:15:"registered_date";s:10:"1402/12/24";s:18:"total_tickets_sold";s:3:"842";s:6:"points";d:1472;s:12:"days_elapsed";i:397;}i:194;a:5:{s:2:"id";s:4:"7558";s:15:"registered_date";s:10:"1402/09/22";s:18:"total_tickets_sold";s:3:"832";s:6:"points";d:1747;s:12:"days_elapsed";i:490;}i:195;a:5:{s:2:"id";s:4:"7484";s:15:"registered_date";s:10:"1402/06/28";s:18:"total_tickets_sold";s:3:"829";s:6:"points";d:2004;s:12:"days_elapsed";i:576;}i:196;a:5:{s:2:"id";s:4:"4582";s:15:"registered_date";s:10:"1402/02/20";s:18:"total_tickets_sold";s:3:"811";s:6:"points";d:2382;s:12:"days_elapsed";i:704;}i:197;a:5:{s:2:"id";s:4:"3360";s:15:"registered_date";s:10:"1401/11/02";s:18:"total_tickets_sold";s:3:"810";s:6:"points";d:2712;s:12:"days_elapsed";i:814;}i:198;a:5:{s:2:"id";s:4:"8053";s:15:"registered_date";s:10:"1403/10/10";s:18:"total_tickets_sold";s:3:"804";s:6:"points";d:589;s:12:"days_elapsed";i:107;}i:199;a:5:{s:2:"id";s:4:"7812";s:15:"registered_date";s:10:"1403/04/25";s:18:"total_tickets_sold";s:3:"785";s:6:"points";d:1087;s:12:"days_elapsed";i:275;}i:200;a:5:{s:2:"id";s:4:"3746";s:15:"registered_date";s:10:"1402/01/11";s:18:"total_tickets_sold";s:3:"767";s:6:"points";d:2488;s:12:"days_elapsed";i:744;}i:201;a:5:{s:2:"id";s:4:"4298";s:15:"registered_date";s:10:"1402/02/10";s:18:"total_tickets_sold";s:3:"767";s:6:"points";d:2398;s:12:"days_elapsed";i:714;}i:202;a:5:{s:2:"id";s:4:"3159";s:15:"registered_date";s:10:"1401/06/08";s:18:"total_tickets_sold";s:3:"761";s:6:"points";d:3137;s:12:"days_elapsed";i:961;}i:203;a:5:{s:2:"id";s:4:"8107";s:15:"registered_date";s:10:"1403/12/04";s:18:"total_tickets_sold";s:3:"758";s:6:"points";d:409;s:12:"days_elapsed";i:52;}i:204;a:5:{s:2:"id";s:4:"7822";s:15:"registered_date";s:10:"1403/05/03";s:18:"total_tickets_sold";s:3:"757";s:6:"points";d:1053;s:12:"days_elapsed";i:267;}i:205;a:5:{s:2:"id";s:4:"3157";s:15:"registered_date";s:10:"1401/06/07";s:18:"total_tickets_sold";s:3:"748";s:6:"points";d:3135;s:12:"days_elapsed";i:962;}i:206;a:5:{s:2:"id";s:4:"7602";s:15:"registered_date";s:10:"1402/11/14";s:18:"total_tickets_sold";s:3:"733";s:6:"points";d:1555;s:12:"days_elapsed";i:437;}i:207;a:5:{s:2:"id";s:4:"3742";s:15:"registered_date";s:10:"1402/01/07";s:18:"total_tickets_sold";s:3:"732";s:6:"points";d:2488;s:12:"days_elapsed";i:748;}i:208;a:5:{s:2:"id";s:4:"7498";s:15:"registered_date";s:10:"1402/07/08";s:18:"total_tickets_sold";s:3:"731";s:6:"points";d:1942;s:12:"days_elapsed";i:566;}i:209;a:5:{s:2:"id";s:4:"4610";s:15:"registered_date";s:10:"1402/02/21";s:18:"total_tickets_sold";s:3:"726";s:6:"points";d:2351;s:12:"days_elapsed";i:703;}i:210;a:5:{s:2:"id";s:4:"8048";s:15:"registered_date";s:10:"1403/09/29";s:18:"total_tickets_sold";s:3:"718";s:6:"points";d:593;s:12:"days_elapsed";i:118;}i:211;a:5:{s:2:"id";s:4:"8112";s:15:"registered_date";s:10:"1403/12/13";s:18:"total_tickets_sold";s:3:"709";s:6:"points";d:365;s:12:"days_elapsed";i:43;}i:212;a:5:{s:2:"id";s:4:"8058";s:15:"registered_date";s:10:"1403/10/16";s:18:"total_tickets_sold";s:3:"707";s:6:"points";d:539;s:12:"days_elapsed";i:101;}i:213;a:5:{s:2:"id";s:4:"3455";s:15:"registered_date";s:10:"1401/12/10";s:18:"total_tickets_sold";s:3:"700";s:6:"points";d:2561;s:12:"days_elapsed";i:776;}i:214;a:5:{s:2:"id";s:4:"7698";s:15:"registered_date";s:10:"1403/01/11";s:18:"total_tickets_sold";s:3:"698";s:6:"points";d:1370;s:12:"days_elapsed";i:379;}i:215;a:5:{s:2:"id";s:4:"7859";s:15:"registered_date";s:10:"1403/05/21";s:18:"total_tickets_sold";s:3:"698";s:6:"points";d:980;s:12:"days_elapsed";i:249;}i:216;a:5:{s:2:"id";s:4:"7996";s:15:"registered_date";s:10:"1403/08/28";s:18:"total_tickets_sold";s:3:"697";s:6:"points";d:682;s:12:"days_elapsed";i:150;}i:217;a:5:{s:2:"id";s:4:"7877";s:15:"registered_date";s:10:"1403/06/05";s:18:"total_tickets_sold";s:3:"694";s:6:"points";d:933;s:12:"days_elapsed";i:234;}i:218;a:5:{s:2:"id";s:4:"7667";s:15:"registered_date";s:10:"1402/12/16";s:18:"total_tickets_sold";s:3:"693";s:6:"points";d:1446;s:12:"days_elapsed";i:405;}i:219;a:5:{s:2:"id";s:4:"7577";s:15:"registered_date";s:10:"1402/10/14";s:18:"total_tickets_sold";s:3:"692";s:6:"points";d:1635;s:12:"days_elapsed";i:468;}i:220;a:5:{s:2:"id";s:4:"7533";s:15:"registered_date";s:10:"1402/08/21";s:18:"total_tickets_sold";s:3:"681";s:6:"points";d:1793;s:12:"days_elapsed";i:522;}i:221;a:5:{s:2:"id";s:4:"3432";s:15:"registered_date";s:10:"1401/12/04";s:18:"total_tickets_sold";s:3:"667";s:6:"points";d:2568;s:12:"days_elapsed";i:782;}i:222;a:5:{s:2:"id";s:4:"7847";s:15:"registered_date";s:10:"1403/05/14";s:18:"total_tickets_sold";s:3:"665";s:6:"points";d:990;s:12:"days_elapsed";i:256;}i:223;a:5:{s:2:"id";s:3:"817";s:15:"registered_date";s:10:"1400/05/31";s:18:"total_tickets_sold";s:3:"660";s:6:"points";d:4222;s:12:"days_elapsed";i:1334;}i:224;a:5:{s:2:"id";s:4:"7571";s:15:"registered_date";s:10:"1402/10/03";s:18:"total_tickets_sold";s:3:"646";s:6:"points";d:1652;s:12:"days_elapsed";i:479;}i:225;a:5:{s:2:"id";s:4:"8056";s:15:"registered_date";s:10:"1403/10/13";s:18:"total_tickets_sold";s:3:"645";s:6:"points";d:527;s:12:"days_elapsed";i:104;}i:226;a:5:{s:2:"id";s:4:"3007";s:15:"registered_date";s:10:"1400/12/15";s:18:"total_tickets_sold";s:3:"643";s:6:"points";d:3622;s:12:"days_elapsed";i:1136;}i:227;a:5:{s:2:"id";s:4:"7671";s:15:"registered_date";s:10:"1402/12/17";s:18:"total_tickets_sold";s:3:"640";s:6:"points";d:1425;s:12:"days_elapsed";i:404;}i:228;a:5:{s:2:"id";s:4:"7545";s:15:"registered_date";s:10:"1402/09/06";s:18:"total_tickets_sold";s:3:"636";s:6:"points";d:1730;s:12:"days_elapsed";i:506;}i:229;a:5:{s:2:"id";s:4:"3089";s:15:"registered_date";s:10:"1401/03/04";s:18:"total_tickets_sold";s:3:"635";s:6:"points";d:3383;s:12:"days_elapsed";i:1057;}i:230;a:5:{s:2:"id";s:4:"6453";s:15:"registered_date";s:10:"1402/04/08";s:18:"total_tickets_sold";s:3:"629";s:6:"points";d:2181;s:12:"days_elapsed";i:657;}i:231;a:5:{s:2:"id";s:4:"7461";s:15:"registered_date";s:10:"1402/06/05";s:18:"total_tickets_sold";s:3:"624";s:6:"points";d:2005;s:12:"days_elapsed";i:599;}i:232;a:5:{s:2:"id";s:4:"7823";s:15:"registered_date";s:10:"1403/05/03";s:18:"total_tickets_sold";s:3:"603";s:6:"points";d:1002;s:12:"days_elapsed";i:267;}i:233;a:5:{s:2:"id";s:4:"4931";s:15:"registered_date";s:10:"1402/03/03";s:18:"total_tickets_sold";s:3:"601";s:6:"points";d:2279;s:12:"days_elapsed";i:693;}i:234;a:5:{s:2:"id";s:4:"7555";s:15:"registered_date";s:10:"1402/09/16";s:18:"total_tickets_sold";s:3:"589";s:6:"points";d:1684;s:12:"days_elapsed";i:496;}i:235;a:5:{s:2:"id";s:4:"7732";s:15:"registered_date";s:10:"1403/02/19";s:18:"total_tickets_sold";s:3:"589";s:6:"points";d:1216;s:12:"days_elapsed";i:340;}i:236;a:5:{s:2:"id";s:4:"7806";s:15:"registered_date";s:10:"1403/04/19";s:18:"total_tickets_sold";s:3:"571";s:6:"points";d:1033;s:12:"days_elapsed";i:281;}i:237;a:5:{s:2:"id";s:4:"7585";s:15:"registered_date";s:10:"1402/10/19";s:18:"total_tickets_sold";s:3:"568";s:6:"points";d:1578;s:12:"days_elapsed";i:463;}i:238;a:5:{s:2:"id";s:4:"3362";s:15:"registered_date";s:10:"1401/11/03";s:18:"total_tickets_sold";s:3:"567";s:6:"points";d:2628;s:12:"days_elapsed";i:813;}i:239;a:5:{s:2:"id";s:4:"7937";s:15:"registered_date";s:10:"1403/07/16";s:18:"total_tickets_sold";s:3:"566";s:6:"points";d:768;s:12:"days_elapsed";i:193;}i:240;a:5:{s:2:"id";s:4:"7935";s:15:"registered_date";s:10:"1403/07/14";s:18:"total_tickets_sold";s:3:"554";s:6:"points";d:770;s:12:"days_elapsed";i:195;}i:241;a:5:{s:2:"id";s:3:"478";s:15:"registered_date";s:10:"1400/04/07";s:18:"total_tickets_sold";s:3:"542";s:6:"points";d:4345;s:12:"days_elapsed";i:1388;}i:242;a:5:{s:2:"id";s:4:"2912";s:15:"registered_date";s:10:"1400/11/30";s:18:"total_tickets_sold";s:3:"542";s:6:"points";d:3634;s:12:"days_elapsed";i:1151;}i:243;a:5:{s:2:"id";s:4:"7657";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:3:"542";s:6:"points";d:1414;s:12:"days_elapsed";i:411;}i:244;a:5:{s:2:"id";s:4:"7696";s:15:"registered_date";s:10:"1403/01/07";s:18:"total_tickets_sold";s:3:"540";s:6:"points";d:1329;s:12:"days_elapsed";i:383;}i:245;a:5:{s:2:"id";s:4:"7888";s:15:"registered_date";s:10:"1403/06/17";s:18:"total_tickets_sold";s:3:"533";s:6:"points";d:844;s:12:"days_elapsed";i:222;}i:246;a:5:{s:2:"id";s:5:"14045";s:15:"registered_date";s:10:"1403/12/14";s:18:"total_tickets_sold";s:3:"532";s:6:"points";d:303;s:12:"days_elapsed";i:42;}i:247;a:5:{s:2:"id";s:4:"1003";s:15:"registered_date";s:10:"1400/06/19";s:18:"total_tickets_sold";s:3:"531";s:6:"points";d:4122;s:12:"days_elapsed";i:1315;}i:248;a:5:{s:2:"id";s:4:"2616";s:15:"registered_date";s:10:"1400/09/10";s:18:"total_tickets_sold";s:3:"509";s:6:"points";d:3866;s:12:"days_elapsed";i:1232;}i:249;a:5:{s:2:"id";s:4:"7771";s:15:"registered_date";s:10:"1403/03/16";s:18:"total_tickets_sold";s:3:"503";s:6:"points";d:1113;s:12:"days_elapsed";i:315;}i:250;a:5:{s:2:"id";s:4:"1864";s:15:"registered_date";s:10:"1400/08/05";s:18:"total_tickets_sold";s:3:"502";s:6:"points";d:3971;s:12:"days_elapsed";i:1268;}i:251;a:5:{s:2:"id";s:4:"7731";s:15:"registered_date";s:10:"1403/02/13";s:18:"total_tickets_sold";s:3:"501";s:6:"points";d:1205;s:12:"days_elapsed";i:346;}i:252;a:5:{s:2:"id";s:4:"7992";s:15:"registered_date";s:10:"1403/08/28";s:18:"total_tickets_sold";s:3:"500";s:6:"points";d:617;s:12:"days_elapsed";i:150;}i:253;a:5:{s:2:"id";s:4:"7711";s:15:"registered_date";s:10:"1403/01/30";s:18:"total_tickets_sold";s:3:"499";s:6:"points";d:1246;s:12:"days_elapsed";i:360;}i:254;a:5:{s:2:"id";s:4:"7890";s:15:"registered_date";s:10:"1403/06/18";s:18:"total_tickets_sold";s:3:"498";s:6:"points";d:829;s:12:"days_elapsed";i:221;}i:255;a:5:{s:2:"id";s:4:"3434";s:15:"registered_date";s:10:"1401/12/04";s:18:"total_tickets_sold";s:3:"495";s:6:"points";d:2511;s:12:"days_elapsed";i:782;}i:256;a:5:{s:2:"id";s:4:"7720";s:15:"registered_date";s:10:"1403/02/04";s:18:"total_tickets_sold";s:3:"490";s:6:"points";d:1228;s:12:"days_elapsed";i:355;}i:257;a:5:{s:2:"id";s:4:"7524";s:15:"registered_date";s:10:"1402/08/08";s:18:"total_tickets_sold";s:3:"488";s:6:"points";d:1768;s:12:"days_elapsed";i:535;}i:258;a:5:{s:2:"id";s:4:"3299";s:15:"registered_date";s:10:"1401/09/08";s:18:"total_tickets_sold";s:3:"476";s:6:"points";d:2766;s:12:"days_elapsed";i:869;}i:259;a:5:{s:2:"id";s:4:"7682";s:15:"registered_date";s:10:"1402/12/23";s:18:"total_tickets_sold";s:3:"470";s:6:"points";d:1351;s:12:"days_elapsed";i:398;}i:260;a:5:{s:2:"id";s:4:"8106";s:15:"registered_date";s:10:"1403/12/01";s:18:"total_tickets_sold";s:3:"470";s:6:"points";d:322;s:12:"days_elapsed";i:55;}i:261;a:5:{s:2:"id";s:4:"3054";s:15:"registered_date";s:10:"1401/02/10";s:18:"total_tickets_sold";s:3:"464";s:6:"points";d:3392;s:12:"days_elapsed";i:1079;}i:262;a:5:{s:2:"id";s:4:"7572";s:15:"registered_date";s:10:"1402/10/04";s:18:"total_tickets_sold";s:3:"460";s:6:"points";d:1587;s:12:"days_elapsed";i:478;}i:263;a:5:{s:2:"id";s:4:"7713";s:15:"registered_date";s:10:"1403/01/30";s:18:"total_tickets_sold";s:3:"459";s:6:"points";d:1233;s:12:"days_elapsed";i:360;}i:264;a:5:{s:2:"id";s:4:"7447";s:15:"registered_date";s:10:"1402/05/27";s:18:"total_tickets_sold";s:3:"456";s:6:"points";d:1976;s:12:"days_elapsed";i:608;}i:265;a:5:{s:2:"id";s:4:"7523";s:15:"registered_date";s:10:"1402/08/08";s:18:"total_tickets_sold";s:3:"455";s:6:"points";d:1757;s:12:"days_elapsed";i:535;}i:266;a:5:{s:2:"id";s:4:"2873";s:15:"registered_date";s:10:"1400/11/24";s:18:"total_tickets_sold";s:3:"453";s:6:"points";d:3622;s:12:"days_elapsed";i:1157;}i:267;a:5:{s:2:"id";s:4:"7772";s:15:"registered_date";s:10:"1403/03/16";s:18:"total_tickets_sold";s:3:"453";s:6:"points";d:1096;s:12:"days_elapsed";i:315;}i:268;a:5:{s:2:"id";s:4:"8046";s:15:"registered_date";s:10:"1403/09/27";s:18:"total_tickets_sold";s:3:"453";s:6:"points";d:511;s:12:"days_elapsed";i:120;}i:269;a:5:{s:2:"id";s:4:"7597";s:15:"registered_date";s:10:"1402/11/02";s:18:"total_tickets_sold";s:3:"451";s:6:"points";d:1497;s:12:"days_elapsed";i:449;}i:270;a:5:{s:2:"id";s:4:"7969";s:15:"registered_date";s:10:"1403/08/15";s:18:"total_tickets_sold";s:3:"445";s:6:"points";d:637;s:12:"days_elapsed";i:163;}i:271;a:5:{s:2:"id";s:4:"1164";s:15:"registered_date";s:10:"1400/06/31";s:18:"total_tickets_sold";s:3:"443";s:6:"points";d:4057;s:12:"days_elapsed";i:1303;}i:272;a:5:{s:2:"id";s:4:"7891";s:15:"registered_date";s:10:"1403/06/20";s:18:"total_tickets_sold";s:3:"435";s:6:"points";d:802;s:12:"days_elapsed";i:219;}i:273;a:5:{s:2:"id";s:4:"7662";s:15:"registered_date";s:10:"1402/12/13";s:18:"total_tickets_sold";s:3:"431";s:6:"points";d:1368;s:12:"days_elapsed";i:408;}i:274;a:5:{s:2:"id";s:4:"7952";s:15:"registered_date";s:10:"1403/08/01";s:18:"total_tickets_sold";s:3:"431";s:6:"points";d:675;s:12:"days_elapsed";i:177;}i:275;a:5:{s:2:"id";s:4:"7778";s:15:"registered_date";s:10:"1403/03/23";s:18:"total_tickets_sold";s:3:"430";s:6:"points";d:1067;s:12:"days_elapsed";i:308;}i:276;a:5:{s:2:"id";s:4:"7984";s:15:"registered_date";s:10:"1403/08/23";s:18:"total_tickets_sold";s:3:"429";s:6:"points";d:608;s:12:"days_elapsed";i:155;}i:277;a:5:{s:2:"id";s:4:"7881";s:15:"registered_date";s:10:"1403/06/11";s:18:"total_tickets_sold";s:3:"422";s:6:"points";d:825;s:12:"days_elapsed";i:228;}i:278;a:5:{s:2:"id";s:3:"239";s:15:"registered_date";s:10:"1400/02/18";s:18:"total_tickets_sold";s:3:"420";s:6:"points";d:4448;s:12:"days_elapsed";i:1436;}i:279;a:5:{s:2:"id";s:4:"7844";s:15:"registered_date";s:10:"1403/05/14";s:18:"total_tickets_sold";s:3:"413";s:6:"points";d:906;s:12:"days_elapsed";i:256;}i:280;a:5:{s:2:"id";s:4:"7687";s:15:"registered_date";s:10:"1402/12/26";s:18:"total_tickets_sold";s:3:"411";s:6:"points";d:1322;s:12:"days_elapsed";i:395;}i:281;a:5:{s:2:"id";s:4:"7718";s:15:"registered_date";s:10:"1403/02/02";s:18:"total_tickets_sold";s:3:"406";s:6:"points";d:1206;s:12:"days_elapsed";i:357;}i:282;a:5:{s:2:"id";s:4:"7814";s:15:"registered_date";s:10:"1403/04/31";s:18:"total_tickets_sold";s:3:"406";s:6:"points";d:942;s:12:"days_elapsed";i:269;}i:283;a:5:{s:2:"id";s:5:"15624";s:15:"registered_date";s:10:"1403/12/19";s:18:"total_tickets_sold";s:3:"405";s:6:"points";d:246;s:12:"days_elapsed";i:37;}i:284;a:5:{s:2:"id";s:4:"7748";s:15:"registered_date";s:10:"1403/02/27";s:18:"total_tickets_sold";s:3:"402";s:6:"points";d:1130;s:12:"days_elapsed";i:332;}i:285;a:5:{s:2:"id";s:4:"7782";s:15:"registered_date";s:10:"1403/03/31";s:18:"total_tickets_sold";s:3:"396";s:6:"points";d:1032;s:12:"days_elapsed";i:300;}i:286;a:5:{s:2:"id";s:4:"7977";s:15:"registered_date";s:10:"1403/08/19";s:18:"total_tickets_sold";s:3:"395";s:6:"points";d:609;s:12:"days_elapsed";i:159;}i:287;a:5:{s:2:"id";s:4:"3176";s:15:"registered_date";s:10:"1401/06/16";s:18:"total_tickets_sold";s:3:"394";s:6:"points";d:2990;s:12:"days_elapsed";i:953;}i:288;a:5:{s:2:"id";s:4:"7495";s:15:"registered_date";s:10:"1402/07/06";s:18:"total_tickets_sold";s:3:"391";s:6:"points";d:1834;s:12:"days_elapsed";i:568;}i:289;a:5:{s:2:"id";s:4:"7483";s:15:"registered_date";s:10:"1402/06/28";s:18:"total_tickets_sold";s:3:"375";s:6:"points";d:1853;s:12:"days_elapsed";i:576;}i:290;a:5:{s:2:"id";s:4:"7521";s:15:"registered_date";s:10:"1402/08/08";s:18:"total_tickets_sold";s:3:"375";s:6:"points";d:1730;s:12:"days_elapsed";i:535;}i:291;a:5:{s:2:"id";s:4:"7716";s:15:"registered_date";s:10:"1403/02/02";s:18:"total_tickets_sold";s:3:"374";s:6:"points";d:1196;s:12:"days_elapsed";i:357;}i:292;a:5:{s:2:"id";s:4:"3177";s:15:"registered_date";s:10:"1401/06/19";s:18:"total_tickets_sold";s:3:"373";s:6:"points";d:2974;s:12:"days_elapsed";i:950;}i:293;a:5:{s:2:"id";s:4:"7811";s:15:"registered_date";s:10:"1403/04/23";s:18:"total_tickets_sold";s:3:"367";s:6:"points";d:953;s:12:"days_elapsed";i:277;}i:294;a:5:{s:2:"id";s:4:"4131";s:15:"registered_date";s:10:"1402/02/04";s:18:"total_tickets_sold";s:3:"366";s:6:"points";d:2282;s:12:"days_elapsed";i:720;}i:295;a:5:{s:2:"id";s:4:"7803";s:15:"registered_date";s:10:"1403/04/18";s:18:"total_tickets_sold";s:3:"364";s:6:"points";d:967;s:12:"days_elapsed";i:282;}i:296;a:5:{s:2:"id";s:4:"8101";s:15:"registered_date";s:10:"1403/11/25";s:18:"total_tickets_sold";s:3:"362";s:6:"points";d:304;s:12:"days_elapsed";i:61;}i:297;a:5:{s:2:"id";s:4:"7656";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:3:"358";s:6:"points";d:1352;s:12:"days_elapsed";i:411;}i:298;a:5:{s:2:"id";s:4:"7974";s:15:"registered_date";s:10:"1403/08/17";s:18:"total_tickets_sold";s:3:"357";s:6:"points";d:602;s:12:"days_elapsed";i:161;}i:299;a:5:{s:2:"id";s:4:"3296";s:15:"registered_date";s:10:"1401/09/07";s:18:"total_tickets_sold";s:3:"355";s:6:"points";d:2728;s:12:"days_elapsed";i:870;}i:300;a:5:{s:2:"id";s:4:"7504";s:15:"registered_date";s:10:"1402/07/23";s:18:"total_tickets_sold";s:3:"352";s:6:"points";d:1770;s:12:"days_elapsed";i:551;}i:301;a:5:{s:2:"id";s:4:"5055";s:15:"registered_date";s:10:"1402/03/06";s:18:"total_tickets_sold";s:3:"347";s:6:"points";d:2186;s:12:"days_elapsed";i:690;}i:302;a:5:{s:2:"id";s:4:"1149";s:15:"registered_date";s:10:"1400/06/30";s:18:"total_tickets_sold";s:3:"341";s:6:"points";d:4026;s:12:"days_elapsed";i:1304;}i:303;a:5:{s:2:"id";s:4:"3297";s:15:"registered_date";s:10:"1401/09/07";s:18:"total_tickets_sold";s:3:"335";s:6:"points";d:2722;s:12:"days_elapsed";i:870;}i:304;a:5:{s:2:"id";s:4:"7910";s:15:"registered_date";s:10:"1403/07/05";s:18:"total_tickets_sold";s:3:"331";s:6:"points";d:722;s:12:"days_elapsed";i:204;}i:305;a:5:{s:2:"id";s:4:"7995";s:15:"registered_date";s:10:"1403/08/28";s:18:"total_tickets_sold";s:3:"329";s:6:"points";d:560;s:12:"days_elapsed";i:150;}i:306;a:5:{s:2:"id";s:4:"8019";s:15:"registered_date";s:10:"1403/09/08";s:18:"total_tickets_sold";s:3:"328";s:6:"points";d:526;s:12:"days_elapsed";i:139;}i:307;a:5:{s:2:"id";s:4:"7435";s:15:"registered_date";s:10:"1402/05/14";s:18:"total_tickets_sold";s:3:"327";s:6:"points";d:1972;s:12:"days_elapsed";i:621;}i:308;a:5:{s:2:"id";s:4:"5882";s:15:"registered_date";s:10:"1402/03/28";s:18:"total_tickets_sold";s:3:"322";s:6:"points";d:2111;s:12:"days_elapsed";i:668;}i:309;a:5:{s:2:"id";s:4:"3140";s:15:"registered_date";s:10:"1401/05/27";s:18:"total_tickets_sold";s:3:"321";s:6:"points";d:3026;s:12:"days_elapsed";i:973;}i:310;a:5:{s:2:"id";s:4:"8030";s:15:"registered_date";s:10:"1403/09/14";s:18:"total_tickets_sold";s:3:"319";s:6:"points";d:505;s:12:"days_elapsed";i:133;}i:311;a:5:{s:2:"id";s:4:"7479";s:15:"registered_date";s:10:"1402/06/23";s:18:"total_tickets_sold";s:3:"318";s:6:"points";d:1849;s:12:"days_elapsed";i:581;}i:312;a:5:{s:2:"id";s:4:"7529";s:15:"registered_date";s:10:"1402/08/15";s:18:"total_tickets_sold";s:3:"312";s:6:"points";d:1688;s:12:"days_elapsed";i:528;}i:313;a:5:{s:2:"id";s:4:"7733";s:15:"registered_date";s:10:"1403/02/19";s:18:"total_tickets_sold";s:3:"311";s:6:"points";d:1124;s:12:"days_elapsed";i:340;}i:314;a:5:{s:2:"id";s:4:"7810";s:15:"registered_date";s:10:"1403/04/23";s:18:"total_tickets_sold";s:3:"311";s:6:"points";d:935;s:12:"days_elapsed";i:277;}i:315;a:5:{s:2:"id";s:4:"7876";s:15:"registered_date";s:10:"1403/06/03";s:18:"total_tickets_sold";s:3:"310";s:6:"points";d:811;s:12:"days_elapsed";i:236;}i:316;a:5:{s:2:"id";s:4:"8008";s:15:"registered_date";s:10:"1403/09/05";s:18:"total_tickets_sold";s:3:"309";s:6:"points";d:529;s:12:"days_elapsed";i:142;}i:317;a:5:{s:2:"id";s:4:"4525";s:15:"registered_date";s:10:"1402/02/18";s:18:"total_tickets_sold";s:3:"306";s:6:"points";d:2220;s:12:"days_elapsed";i:706;}i:318;a:5:{s:2:"id";s:4:"7750";s:15:"registered_date";s:10:"1403/02/30";s:18:"total_tickets_sold";s:3:"304";s:6:"points";d:1088;s:12:"days_elapsed";i:329;}i:319;a:5:{s:2:"id";s:4:"7725";s:15:"registered_date";s:10:"1403/02/06";s:18:"total_tickets_sold";s:3:"303";s:6:"points";d:1160;s:12:"days_elapsed";i:353;}i:320;a:5:{s:2:"id";s:4:"3115";s:15:"registered_date";s:10:"1401/04/14";s:18:"total_tickets_sold";s:3:"302";s:6:"points";d:3149;s:12:"days_elapsed";i:1016;}i:321;a:5:{s:2:"id";s:4:"7428";s:15:"registered_date";s:10:"1402/05/05";s:18:"total_tickets_sold";s:3:"302";s:6:"points";d:1991;s:12:"days_elapsed";i:630;}i:322;a:5:{s:2:"id";s:4:"8065";s:15:"registered_date";s:10:"1403/10/24";s:18:"total_tickets_sold";s:3:"301";s:6:"points";d:379;s:12:"days_elapsed";i:93;}i:323;a:5:{s:2:"id";s:4:"7959";s:15:"registered_date";s:10:"1403/08/09";s:18:"total_tickets_sold";s:3:"299";s:6:"points";d:607;s:12:"days_elapsed";i:169;}i:324;a:5:{s:2:"id";s:4:"8066";s:15:"registered_date";s:10:"1403/11/01";s:18:"total_tickets_sold";s:3:"298";s:6:"points";d:354;s:12:"days_elapsed";i:85;}i:325;a:5:{s:2:"id";s:4:"8095";s:15:"registered_date";s:10:"1403/11/22";s:18:"total_tickets_sold";s:3:"298";s:6:"points";d:291;s:12:"days_elapsed";i:64;}i:326;a:5:{s:2:"id";s:4:"8114";s:15:"registered_date";s:10:"1403/12/13";s:18:"total_tickets_sold";s:3:"294";s:6:"points";d:227;s:12:"days_elapsed";i:43;}i:327;a:5:{s:2:"id";s:4:"7586";s:15:"registered_date";s:10:"1402/10/19";s:18:"total_tickets_sold";s:3:"287";s:6:"points";d:1485;s:12:"days_elapsed";i:463;}i:328;a:5:{s:2:"id";s:4:"8110";s:15:"registered_date";s:10:"1403/12/07";s:18:"total_tickets_sold";s:3:"283";s:6:"points";d:241;s:12:"days_elapsed";i:49;}i:329;a:5:{s:2:"id";s:4:"3143";s:15:"registered_date";s:10:"1401/05/31";s:18:"total_tickets_sold";s:3:"280";s:6:"points";d:3000;s:12:"days_elapsed";i:969;}i:330;a:5:{s:2:"id";s:4:"7884";s:15:"registered_date";s:10:"1403/06/12";s:18:"total_tickets_sold";s:3:"278";s:6:"points";d:774;s:12:"days_elapsed";i:227;}i:331;a:5:{s:2:"id";s:4:"3181";s:15:"registered_date";s:10:"1401/06/20";s:18:"total_tickets_sold";s:3:"274";s:6:"points";d:2938;s:12:"days_elapsed";i:949;}i:332;a:5:{s:2:"id";s:4:"3110";s:15:"registered_date";s:10:"1401/04/06";s:18:"total_tickets_sold";s:3:"270";s:6:"points";d:3162;s:12:"days_elapsed";i:1024;}i:333;a:5:{s:2:"id";s:4:"7804";s:15:"registered_date";s:10:"1403/04/19";s:18:"total_tickets_sold";s:3:"269";s:6:"points";d:933;s:12:"days_elapsed";i:281;}i:334;a:5:{s:2:"id";s:4:"7599";s:15:"registered_date";s:10:"1402/11/02";s:18:"total_tickets_sold";s:3:"265";s:6:"points";d:1435;s:12:"days_elapsed";i:449;}i:335;a:5:{s:2:"id";s:4:"2812";s:15:"registered_date";s:10:"1400/11/11";s:18:"total_tickets_sold";s:3:"263";s:6:"points";d:3598;s:12:"days_elapsed";i:1170;}i:336;a:5:{s:2:"id";s:4:"7781";s:15:"registered_date";s:10:"1403/03/30";s:18:"total_tickets_sold";s:3:"260";s:6:"points";d:990;s:12:"days_elapsed";i:301;}i:337;a:5:{s:2:"id";s:4:"7826";s:15:"registered_date";s:10:"1403/05/03";s:18:"total_tickets_sold";s:3:"252";s:6:"points";d:885;s:12:"days_elapsed";i:267;}i:338;a:5:{s:2:"id";s:4:"1389";s:15:"registered_date";s:10:"1400/07/09";s:18:"total_tickets_sold";s:3:"249";s:6:"points";d:3968;s:12:"days_elapsed";i:1295;}i:339;a:5:{s:2:"id";s:4:"3735";s:15:"registered_date";s:10:"1402/01/06";s:18:"total_tickets_sold";s:3:"245";s:6:"points";d:2329;s:12:"days_elapsed";i:749;}i:340;a:5:{s:2:"id";s:4:"5879";s:15:"registered_date";s:10:"1402/03/28";s:18:"total_tickets_sold";s:3:"245";s:6:"points";d:2086;s:12:"days_elapsed";i:668;}i:341;a:5:{s:2:"id";s:4:"7513";s:15:"registered_date";s:10:"1402/07/27";s:18:"total_tickets_sold";s:3:"243";s:6:"points";d:1722;s:12:"days_elapsed";i:547;}i:342;a:5:{s:2:"id";s:4:"7487";s:15:"registered_date";s:10:"1402/06/30";s:18:"total_tickets_sold";s:3:"241";s:6:"points";d:1802;s:12:"days_elapsed";i:574;}i:343;a:5:{s:2:"id";s:4:"4495";s:15:"registered_date";s:10:"1402/02/17";s:18:"total_tickets_sold";s:3:"239";s:6:"points";d:2201;s:12:"days_elapsed";i:707;}i:344;a:5:{s:2:"id";s:4:"7547";s:15:"registered_date";s:10:"1402/09/09";s:18:"total_tickets_sold";s:3:"239";s:6:"points";d:1589;s:12:"days_elapsed";i:503;}i:345;a:5:{s:2:"id";s:4:"7640";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:3:"238";s:6:"points";d:1312;s:12:"days_elapsed";i:411;}i:346;a:5:{s:2:"id";s:4:"8005";s:15:"registered_date";s:10:"1403/09/03";s:18:"total_tickets_sold";s:3:"233";s:6:"points";d:510;s:12:"days_elapsed";i:144;}i:347;a:5:{s:2:"id";s:4:"8103";s:15:"registered_date";s:10:"1403/11/27";s:18:"total_tickets_sold";s:3:"233";s:6:"points";d:255;s:12:"days_elapsed";i:59;}i:348;a:5:{s:2:"id";s:4:"7621";s:15:"registered_date";s:10:"1402/11/28";s:18:"total_tickets_sold";s:3:"231";s:6:"points";d:1346;s:12:"days_elapsed";i:423;}i:349;a:5:{s:2:"id";s:4:"2269";s:15:"registered_date";s:10:"1400/08/24";s:18:"total_tickets_sold";s:3:"229";s:6:"points";d:3823;s:12:"days_elapsed";i:1249;}i:350;a:5:{s:2:"id";s:3:"961";s:15:"registered_date";s:10:"1400/06/16";s:18:"total_tickets_sold";s:3:"225";s:6:"points";d:4029;s:12:"days_elapsed";i:1318;}i:351;a:5:{s:2:"id";s:4:"2271";s:15:"registered_date";s:10:"1400/08/24";s:18:"total_tickets_sold";s:3:"222";s:6:"points";d:3821;s:12:"days_elapsed";i:1249;}i:352;a:5:{s:2:"id";s:4:"7510";s:15:"registered_date";s:10:"1402/07/27";s:18:"total_tickets_sold";s:3:"222";s:6:"points";d:1715;s:12:"days_elapsed";i:547;}i:353;a:5:{s:2:"id";s:4:"7486";s:15:"registered_date";s:10:"1402/06/29";s:18:"total_tickets_sold";s:3:"220";s:6:"points";d:1798;s:12:"days_elapsed";i:575;}i:354;a:5:{s:2:"id";s:4:"3312";s:15:"registered_date";s:10:"1401/09/28";s:18:"total_tickets_sold";s:3:"219";s:6:"points";d:2620;s:12:"days_elapsed";i:849;}i:355;a:5:{s:2:"id";s:4:"7796";s:15:"registered_date";s:10:"1403/04/11";s:18:"total_tickets_sold";s:3:"219";s:6:"points";d:940;s:12:"days_elapsed";i:289;}i:356;a:5:{s:2:"id";s:4:"3161";s:15:"registered_date";s:10:"1401/06/09";s:18:"total_tickets_sold";s:3:"216";s:6:"points";d:2952;s:12:"days_elapsed";i:960;}i:357;a:5:{s:2:"id";s:4:"7548";s:15:"registered_date";s:10:"1402/09/09";s:18:"total_tickets_sold";s:3:"214";s:6:"points";d:1580;s:12:"days_elapsed";i:503;}i:358;a:5:{s:2:"id";s:4:"1345";s:15:"registered_date";s:10:"1400/07/08";s:18:"total_tickets_sold";s:3:"213";s:6:"points";d:3959;s:12:"days_elapsed";i:1296;}i:359;a:5:{s:2:"id";s:4:"7584";s:15:"registered_date";s:10:"1402/10/19";s:18:"total_tickets_sold";s:3:"209";s:6:"points";d:1459;s:12:"days_elapsed";i:463;}i:360;a:5:{s:2:"id";s:4:"8099";s:15:"registered_date";s:10:"1403/11/24";s:18:"total_tickets_sold";s:3:"209";s:6:"points";d:256;s:12:"days_elapsed";i:62;}i:361;a:5:{s:2:"id";s:4:"7918";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:3:"207";s:6:"points";d:669;s:12:"days_elapsed";i:200;}i:362;a:5:{s:2:"id";s:4:"8074";s:15:"registered_date";s:10:"1403/11/07";s:18:"total_tickets_sold";s:3:"206";s:6:"points";d:306;s:12:"days_elapsed";i:79;}i:363;a:5:{s:2:"id";s:4:"3346";s:15:"registered_date";s:10:"1401/10/18";s:18:"total_tickets_sold";s:3:"200";s:6:"points";d:2554;s:12:"days_elapsed";i:829;}i:364;a:5:{s:2:"id";s:4:"7517";s:15:"registered_date";s:10:"1402/08/02";s:18:"total_tickets_sold";s:3:"199";s:6:"points";d:1689;s:12:"days_elapsed";i:541;}i:365;a:5:{s:2:"id";s:4:"7933";s:15:"registered_date";s:10:"1403/07/12";s:18:"total_tickets_sold";s:3:"191";s:6:"points";d:655;s:12:"days_elapsed";i:197;}i:366;a:5:{s:2:"id";s:4:"7679";s:15:"registered_date";s:10:"1402/12/22";s:18:"total_tickets_sold";s:3:"190";s:6:"points";d:1260;s:12:"days_elapsed";i:399;}i:367;a:5:{s:2:"id";s:4:"7646";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:3:"187";s:6:"points";d:1295;s:12:"days_elapsed";i:411;}i:368;a:5:{s:2:"id";s:4:"7648";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:3:"187";s:6:"points";d:1295;s:12:"days_elapsed";i:411;}i:369;a:5:{s:2:"id";s:4:"7436";s:15:"registered_date";s:10:"1402/05/15";s:18:"total_tickets_sold";s:3:"179";s:6:"points";d:1920;s:12:"days_elapsed";i:620;}i:370;a:5:{s:2:"id";s:4:"7747";s:15:"registered_date";s:10:"1403/02/27";s:18:"total_tickets_sold";s:3:"171";s:6:"points";d:1053;s:12:"days_elapsed";i:332;}i:371;a:5:{s:2:"id";s:4:"3355";s:15:"registered_date";s:10:"1401/10/27";s:18:"total_tickets_sold";s:3:"170";s:6:"points";d:2517;s:12:"days_elapsed";i:820;}i:372;a:5:{s:2:"id";s:4:"7856";s:15:"registered_date";s:10:"1403/05/20";s:18:"total_tickets_sold";s:3:"170";s:6:"points";d:807;s:12:"days_elapsed";i:250;}i:373;a:5:{s:2:"id";s:4:"8098";s:15:"registered_date";s:10:"1403/11/24";s:18:"total_tickets_sold";s:3:"169";s:6:"points";d:242;s:12:"days_elapsed";i:62;}i:374;a:5:{s:2:"id";s:4:"7583";s:15:"registered_date";s:10:"1402/10/19";s:18:"total_tickets_sold";s:3:"164";s:6:"points";d:1444;s:12:"days_elapsed";i:463;}i:375;a:5:{s:2:"id";s:4:"3435";s:15:"registered_date";s:10:"1401/12/04";s:18:"total_tickets_sold";s:3:"161";s:6:"points";d:2400;s:12:"days_elapsed";i:782;}i:376;a:5:{s:2:"id";s:5:"12394";s:15:"registered_date";s:10:"1403/12/14";s:18:"total_tickets_sold";s:3:"161";s:6:"points";d:180;s:12:"days_elapsed";i:42;}i:377;a:5:{s:2:"id";s:4:"6031";s:15:"registered_date";s:10:"1402/03/31";s:18:"total_tickets_sold";s:3:"159";s:6:"points";d:2048;s:12:"days_elapsed";i:665;}i:378;a:5:{s:2:"id";s:4:"3038";s:15:"registered_date";s:10:"1401/01/23";s:18:"total_tickets_sold";s:3:"154";s:6:"points";d:3342;s:12:"days_elapsed";i:1097;}i:379;a:5:{s:2:"id";s:4:"8105";s:15:"registered_date";s:10:"1403/11/29";s:18:"total_tickets_sold";s:3:"153";s:6:"points";d:222;s:12:"days_elapsed";i:57;}i:380;a:5:{s:2:"id";s:4:"7544";s:15:"registered_date";s:10:"1402/09/05";s:18:"total_tickets_sold";s:3:"149";s:6:"points";d:1571;s:12:"days_elapsed";i:507;}i:381;a:5:{s:2:"id";s:4:"7591";s:15:"registered_date";s:10:"1402/10/26";s:18:"total_tickets_sold";s:3:"149";s:6:"points";d:1418;s:12:"days_elapsed";i:456;}i:382;a:5:{s:2:"id";s:4:"7446";s:15:"registered_date";s:10:"1402/05/27";s:18:"total_tickets_sold";s:3:"147";s:6:"points";d:1873;s:12:"days_elapsed";i:608;}i:383;a:5:{s:2:"id";s:4:"3359";s:15:"registered_date";s:10:"1401/10/30";s:18:"total_tickets_sold";s:3:"146";s:6:"points";d:2500;s:12:"days_elapsed";i:817;}i:384;a:5:{s:2:"id";s:4:"7418";s:15:"registered_date";s:10:"1402/04/27";s:18:"total_tickets_sold";s:3:"143";s:6:"points";d:1962;s:12:"days_elapsed";i:638;}i:385;a:5:{s:2:"id";s:4:"7567";s:15:"registered_date";s:10:"1402/10/03";s:18:"total_tickets_sold";s:3:"142";s:6:"points";d:1484;s:12:"days_elapsed";i:479;}i:386;a:5:{s:2:"id";s:4:"7582";s:15:"registered_date";s:10:"1402/10/19";s:18:"total_tickets_sold";s:3:"141";s:6:"points";d:1436;s:12:"days_elapsed";i:463;}i:387;a:5:{s:2:"id";s:4:"7913";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:3:"140";s:6:"points";d:647;s:12:"days_elapsed";i:200;}i:388;a:5:{s:2:"id";s:4:"7920";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:3:"137";s:6:"points";d:646;s:12:"days_elapsed";i:200;}i:389;a:5:{s:2:"id";s:5:"14211";s:15:"registered_date";s:10:"1403/12/14";s:18:"total_tickets_sold";s:3:"136";s:6:"points";d:171;s:12:"days_elapsed";i:42;}i:390;a:5:{s:2:"id";s:4:"7746";s:15:"registered_date";s:10:"1403/02/26";s:18:"total_tickets_sold";s:3:"135";s:6:"points";d:1044;s:12:"days_elapsed";i:333;}i:391;a:5:{s:2:"id";s:4:"8080";s:15:"registered_date";s:10:"1403/11/10";s:18:"total_tickets_sold";s:3:"135";s:6:"points";d:273;s:12:"days_elapsed";i:76;}i:392;a:5:{s:2:"id";s:4:"7705";s:15:"registered_date";s:10:"1403/01/22";s:18:"total_tickets_sold";s:3:"134";s:6:"points";d:1149;s:12:"days_elapsed";i:368;}i:393;a:5:{s:2:"id";s:4:"7785";s:15:"registered_date";s:10:"1403/04/03";s:18:"total_tickets_sold";s:3:"134";s:6:"points";d:936;s:12:"days_elapsed";i:297;}i:394;a:5:{s:2:"id";s:4:"1135";s:15:"registered_date";s:10:"1400/06/29";s:18:"total_tickets_sold";s:3:"133";s:6:"points";d:3959;s:12:"days_elapsed";i:1305;}i:395;a:5:{s:2:"id";s:4:"3385";s:15:"registered_date";s:10:"1401/11/17";s:18:"total_tickets_sold";s:3:"131";s:6:"points";d:2441;s:12:"days_elapsed";i:799;}i:396;a:5:{s:2:"id";s:4:"4278";s:15:"registered_date";s:10:"1402/02/09";s:18:"total_tickets_sold";s:3:"131";s:6:"points";d:2189;s:12:"days_elapsed";i:715;}i:397;a:5:{s:2:"id";s:4:"7709";s:15:"registered_date";s:10:"1403/01/29";s:18:"total_tickets_sold";s:3:"131";s:6:"points";d:1127;s:12:"days_elapsed";i:361;}i:398;a:5:{s:2:"id";s:4:"7892";s:15:"registered_date";s:10:"1403/06/20";s:18:"total_tickets_sold";s:3:"131";s:6:"points";d:701;s:12:"days_elapsed";i:219;}i:399;a:5:{s:2:"id";s:4:"8051";s:15:"registered_date";s:10:"1403/10/04";s:18:"total_tickets_sold";s:3:"129";s:6:"points";d:382;s:12:"days_elapsed";i:113;}i:400;a:5:{s:2:"id";s:4:"8052";s:15:"registered_date";s:10:"1403/10/10";s:18:"total_tickets_sold";s:3:"128";s:6:"points";d:364;s:12:"days_elapsed";i:107;}i:401;a:5:{s:2:"id";s:4:"8031";s:15:"registered_date";s:10:"1403/09/14";s:18:"total_tickets_sold";s:3:"125";s:6:"points";d:441;s:12:"days_elapsed";i:133;}i:402;a:5:{s:2:"id";s:4:"8071";s:15:"registered_date";s:10:"1403/11/06";s:18:"total_tickets_sold";s:3:"125";s:6:"points";d:282;s:12:"days_elapsed";i:80;}i:403;a:5:{s:2:"id";s:4:"7724";s:15:"registered_date";s:10:"1403/02/06";s:18:"total_tickets_sold";s:3:"124";s:6:"points";d:1100;s:12:"days_elapsed";i:353;}i:404;a:5:{s:2:"id";s:4:"8018";s:15:"registered_date";s:10:"1403/09/08";s:18:"total_tickets_sold";s:3:"124";s:6:"points";d:458;s:12:"days_elapsed";i:139;}i:405;a:5:{s:2:"id";s:4:"7968";s:15:"registered_date";s:10:"1403/08/14";s:18:"total_tickets_sold";s:3:"123";s:6:"points";d:533;s:12:"days_elapsed";i:164;}i:406;a:5:{s:2:"id";s:4:"7988";s:15:"registered_date";s:10:"1403/08/26";s:18:"total_tickets_sold";s:3:"123";s:6:"points";d:497;s:12:"days_elapsed";i:152;}i:407;a:5:{s:2:"id";s:4:"7911";s:15:"registered_date";s:10:"1403/07/08";s:18:"total_tickets_sold";s:3:"121";s:6:"points";d:643;s:12:"days_elapsed";i:201;}i:408;a:5:{s:2:"id";s:4:"3249";s:15:"registered_date";s:10:"1401/08/24";s:18:"total_tickets_sold";s:3:"118";s:6:"points";d:2691;s:12:"days_elapsed";i:884;}i:409;a:5:{s:2:"id";s:4:"7708";s:15:"registered_date";s:10:"1403/01/29";s:18:"total_tickets_sold";s:3:"118";s:6:"points";d:1122;s:12:"days_elapsed";i:361;}i:410;a:5:{s:2:"id";s:4:"7736";s:15:"registered_date";s:10:"1403/02/19";s:18:"total_tickets_sold";s:3:"117";s:6:"points";d:1059;s:12:"days_elapsed";i:340;}i:411;a:5:{s:2:"id";s:4:"7691";s:15:"registered_date";s:10:"1402/12/29";s:18:"total_tickets_sold";s:3:"116";s:6:"points";d:1215;s:12:"days_elapsed";i:392;}i:412;a:5:{s:2:"id";s:4:"5383";s:15:"registered_date";s:10:"1402/03/17";s:18:"total_tickets_sold";s:3:"114";s:6:"points";d:2075;s:12:"days_elapsed";i:679;}i:413;a:5:{s:2:"id";s:4:"7973";s:15:"registered_date";s:10:"1403/08/17";s:18:"total_tickets_sold";s:3:"114";s:6:"points";d:521;s:12:"days_elapsed";i:161;}i:414;a:5:{s:2:"id";s:4:"3153";s:15:"registered_date";s:10:"1401/06/07";s:18:"total_tickets_sold";s:3:"113";s:6:"points";d:2924;s:12:"days_elapsed";i:962;}i:415;a:5:{s:2:"id";s:4:"7649";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:3:"108";s:6:"points";d:1269;s:12:"days_elapsed";i:411;}i:416;a:5:{s:2:"id";s:4:"7535";s:15:"registered_date";s:10:"1402/08/22";s:18:"total_tickets_sold";s:3:"107";s:6:"points";d:1599;s:12:"days_elapsed";i:521;}i:417;a:5:{s:2:"id";s:4:"8001";s:15:"registered_date";s:10:"1403/09/01";s:18:"total_tickets_sold";s:3:"106";s:6:"points";d:473;s:12:"days_elapsed";i:146;}i:418;a:5:{s:2:"id";s:4:"8034";s:15:"registered_date";s:10:"1403/09/17";s:18:"total_tickets_sold";s:3:"101";s:6:"points";d:424;s:12:"days_elapsed";i:130;}i:419;a:5:{s:2:"id";s:4:"7921";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:3:"100";s:6:"points";d:633;s:12:"days_elapsed";i:200;}i:420;a:5:{s:2:"id";s:4:"3331";s:15:"registered_date";s:10:"1401/10/11";s:18:"total_tickets_sold";s:2:"99";s:6:"points";d:2541;s:12:"days_elapsed";i:836;}i:421;a:5:{s:2:"id";s:4:"7431";s:15:"registered_date";s:10:"1402/05/10";s:18:"total_tickets_sold";s:2:"99";s:6:"points";d:1908;s:12:"days_elapsed";i:625;}i:422;a:5:{s:2:"id";s:4:"7613";s:15:"registered_date";s:10:"1402/11/24";s:18:"total_tickets_sold";s:2:"98";s:6:"points";d:1314;s:12:"days_elapsed";i:427;}i:423;a:5:{s:2:"id";s:4:"8076";s:15:"registered_date";s:10:"1403/11/08";s:18:"total_tickets_sold";s:2:"97";s:6:"points";d:266;s:12:"days_elapsed";i:78;}i:424;a:5:{s:2:"id";s:4:"7774";s:15:"registered_date";s:10:"1403/03/19";s:18:"total_tickets_sold";s:2:"94";s:6:"points";d:967;s:12:"days_elapsed";i:312;}i:425;a:5:{s:2:"id";s:4:"7835";s:15:"registered_date";s:10:"1403/05/07";s:18:"total_tickets_sold";s:2:"94";s:6:"points";d:820;s:12:"days_elapsed";i:263;}i:426;a:5:{s:2:"id";s:4:"7950";s:15:"registered_date";s:10:"1403/07/28";s:18:"total_tickets_sold";s:2:"93";s:6:"points";d:574;s:12:"days_elapsed";i:181;}i:427;a:5:{s:2:"id";s:4:"8017";s:15:"registered_date";s:10:"1403/09/08";s:18:"total_tickets_sold";s:2:"92";s:6:"points";d:448;s:12:"days_elapsed";i:139;}i:428;a:5:{s:2:"id";s:4:"7941";s:15:"registered_date";s:10:"1403/07/18";s:18:"total_tickets_sold";s:2:"91";s:6:"points";d:603;s:12:"days_elapsed";i:191;}i:429;a:5:{s:2:"id";s:4:"8050";s:15:"registered_date";s:10:"1403/10/03";s:18:"total_tickets_sold";s:2:"91";s:6:"points";d:372;s:12:"days_elapsed";i:114;}i:430;a:5:{s:2:"id";s:4:"1184";s:15:"registered_date";s:10:"1400/06/31";s:18:"total_tickets_sold";s:2:"90";s:6:"points";d:3939;s:12:"days_elapsed";i:1303;}i:431;a:5:{s:2:"id";s:4:"3192";s:15:"registered_date";s:10:"1401/06/27";s:18:"total_tickets_sold";s:2:"90";s:6:"points";d:2856;s:12:"days_elapsed";i:942;}i:432;a:5:{s:2:"id";s:4:"7534";s:15:"registered_date";s:10:"1402/08/21";s:18:"total_tickets_sold";s:2:"90";s:6:"points";d:1596;s:12:"days_elapsed";i:522;}i:433;a:5:{s:2:"id";s:4:"7717";s:15:"registered_date";s:10:"1403/02/02";s:18:"total_tickets_sold";s:2:"88";s:6:"points";d:1100;s:12:"days_elapsed";i:357;}i:434;a:5:{s:2:"id";s:4:"7633";s:15:"registered_date";s:10:"1402/12/09";s:18:"total_tickets_sold";s:2:"87";s:6:"points";d:1265;s:12:"days_elapsed";i:412;}i:435;a:5:{s:2:"id";s:4:"7695";s:15:"registered_date";s:10:"1403/01/07";s:18:"total_tickets_sold";s:2:"87";s:6:"points";d:1178;s:12:"days_elapsed";i:383;}i:436;a:5:{s:2:"id";s:4:"7469";s:15:"registered_date";s:10:"1402/06/14";s:18:"total_tickets_sold";s:2:"86";s:6:"points";d:1799;s:12:"days_elapsed";i:590;}i:437;a:5:{s:2:"id";s:4:"7561";s:15:"registered_date";s:10:"1402/09/22";s:18:"total_tickets_sold";s:2:"86";s:6:"points";d:1499;s:12:"days_elapsed";i:490;}i:438;a:5:{s:2:"id";s:4:"7906";s:15:"registered_date";s:10:"1403/07/04";s:18:"total_tickets_sold";s:2:"86";s:6:"points";d:644;s:12:"days_elapsed";i:205;}i:439;a:5:{s:2:"id";s:4:"7987";s:15:"registered_date";s:10:"1403/08/26";s:18:"total_tickets_sold";s:2:"86";s:6:"points";d:485;s:12:"days_elapsed";i:152;}i:440;a:5:{s:2:"id";s:4:"5333";s:15:"registered_date";s:10:"1402/03/16";s:18:"total_tickets_sold";s:2:"85";s:6:"points";d:2068;s:12:"days_elapsed";i:680;}i:441;a:5:{s:2:"id";s:4:"3016";s:15:"registered_date";s:10:"1400/12/24";s:18:"total_tickets_sold";s:2:"84";s:6:"points";d:3409;s:12:"days_elapsed";i:1127;}i:442;a:5:{s:2:"id";s:4:"8088";s:15:"registered_date";s:10:"1403/11/18";s:18:"total_tickets_sold";s:2:"84";s:6:"points";d:232;s:12:"days_elapsed";i:68;}i:443;a:5:{s:2:"id";s:4:"6284";s:15:"registered_date";s:10:"1402/04/05";s:18:"total_tickets_sold";s:2:"83";s:6:"points";d:2008;s:12:"days_elapsed";i:660;}i:444;a:5:{s:2:"id";s:4:"8011";s:15:"registered_date";s:10:"1403/09/06";s:18:"total_tickets_sold";s:2:"83";s:6:"points";d:451;s:12:"days_elapsed";i:141;}i:445;a:5:{s:2:"id";s:4:"8025";s:15:"registered_date";s:10:"1403/09/12";s:18:"total_tickets_sold";s:2:"83";s:6:"points";d:433;s:12:"days_elapsed";i:135;}i:446;a:5:{s:2:"id";s:4:"4501";s:15:"registered_date";s:10:"1402/02/17";s:18:"total_tickets_sold";s:2:"82";s:6:"points";d:2148;s:12:"days_elapsed";i:707;}i:447;a:5:{s:2:"id";s:4:"7574";s:15:"registered_date";s:10:"1402/10/07";s:18:"total_tickets_sold";s:2:"81";s:6:"points";d:1452;s:12:"days_elapsed";i:475;}i:448;a:5:{s:2:"id";s:4:"7830";s:15:"registered_date";s:10:"1403/05/06";s:18:"total_tickets_sold";s:2:"81";s:6:"points";d:819;s:12:"days_elapsed";i:264;}i:449;a:5:{s:2:"id";s:4:"7790";s:15:"registered_date";s:10:"1403/04/09";s:18:"total_tickets_sold";s:2:"78";s:6:"points";d:899;s:12:"days_elapsed";i:291;}i:450;a:5:{s:2:"id";s:4:"7951";s:15:"registered_date";s:10:"1403/07/30";s:18:"total_tickets_sold";s:2:"77";s:6:"points";d:563;s:12:"days_elapsed";i:179;}i:451;a:5:{s:2:"id";s:4:"3205";s:15:"registered_date";s:10:"1401/07/16";s:18:"total_tickets_sold";s:2:"76";s:6:"points";d:2794;s:12:"days_elapsed";i:923;}i:452;a:5:{s:2:"id";s:4:"7680";s:15:"registered_date";s:10:"1402/12/22";s:18:"total_tickets_sold";s:2:"76";s:6:"points";d:1222;s:12:"days_elapsed";i:399;}i:453;a:5:{s:2:"id";s:4:"7802";s:15:"registered_date";s:10:"1403/04/18";s:18:"total_tickets_sold";s:2:"75";s:6:"points";d:871;s:12:"days_elapsed";i:282;}i:454;a:5:{s:2:"id";s:4:"8062";s:15:"registered_date";s:10:"1403/10/20";s:18:"total_tickets_sold";s:2:"75";s:6:"points";d:316;s:12:"days_elapsed";i:97;}i:455;a:5:{s:2:"id";s:4:"7512";s:15:"registered_date";s:10:"1402/07/27";s:18:"total_tickets_sold";s:2:"72";s:6:"points";d:1665;s:12:"days_elapsed";i:547;}i:456;a:5:{s:2:"id";s:4:"7614";s:15:"registered_date";s:10:"1402/11/24";s:18:"total_tickets_sold";s:2:"72";s:6:"points";d:1305;s:12:"days_elapsed";i:427;}i:457;a:5:{s:2:"id";s:4:"7644";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:2:"71";s:6:"points";d:1257;s:12:"days_elapsed";i:411;}i:458;a:5:{s:2:"id";s:4:"7647";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:2:"71";s:6:"points";d:1257;s:12:"days_elapsed";i:411;}i:459;a:5:{s:2:"id";s:4:"7751";s:15:"registered_date";s:10:"1403/02/30";s:18:"total_tickets_sold";s:2:"71";s:6:"points";d:1011;s:12:"days_elapsed";i:329;}i:460;a:5:{s:2:"id";s:4:"8040";s:15:"registered_date";s:10:"1403/09/21";s:18:"total_tickets_sold";s:2:"70";s:6:"points";d:401;s:12:"days_elapsed";i:126;}i:461;a:5:{s:2:"id";s:5:"23229";s:15:"registered_date";s:10:"1404/01/07";s:18:"total_tickets_sold";s:2:"70";s:6:"points";d:77;s:12:"days_elapsed";i:18;}i:462;a:5:{s:2:"id";s:4:"7508";s:15:"registered_date";s:10:"1402/07/25";s:18:"total_tickets_sold";s:2:"69";s:6:"points";d:1670;s:12:"days_elapsed";i:549;}i:463;a:5:{s:2:"id";s:4:"8093";s:15:"registered_date";s:10:"1403/11/22";s:18:"total_tickets_sold";s:2:"69";s:6:"points";d:215;s:12:"days_elapsed";i:64;}i:464;a:5:{s:2:"id";s:4:"7784";s:15:"registered_date";s:10:"1403/04/02";s:18:"total_tickets_sold";s:2:"68";s:6:"points";d:917;s:12:"days_elapsed";i:298;}i:465;a:5:{s:2:"id";s:4:"4221";s:15:"registered_date";s:10:"1402/02/07";s:18:"total_tickets_sold";s:2:"65";s:6:"points";d:2173;s:12:"days_elapsed";i:717;}i:466;a:5:{s:2:"id";s:4:"7525";s:15:"registered_date";s:10:"1402/08/08";s:18:"total_tickets_sold";s:2:"64";s:6:"points";d:1626;s:12:"days_elapsed";i:535;}i:467;a:5:{s:2:"id";s:5:"26521";s:15:"registered_date";s:10:"1404/01/13";s:18:"total_tickets_sold";s:2:"63";s:6:"points";d:57;s:12:"days_elapsed";i:12;}i:468;a:5:{s:2:"id";s:4:"7813";s:15:"registered_date";s:10:"1403/04/30";s:18:"total_tickets_sold";s:2:"62";s:6:"points";d:831;s:12:"days_elapsed";i:270;}i:469;a:5:{s:2:"id";s:4:"3813";s:15:"registered_date";s:10:"1402/01/18";s:18:"total_tickets_sold";s:2:"60";s:6:"points";d:2231;s:12:"days_elapsed";i:737;}i:470;a:5:{s:2:"id";s:4:"7615";s:15:"registered_date";s:10:"1402/11/25";s:18:"total_tickets_sold";s:2:"60";s:6:"points";d:1298;s:12:"days_elapsed";i:426;}i:471;a:5:{s:2:"id";s:5:"29811";s:15:"registered_date";s:10:"1404/01/21";s:18:"total_tickets_sold";s:2:"59";s:6:"points";d:32;s:12:"days_elapsed";i:4;}i:472;a:5:{s:2:"id";s:4:"8049";s:15:"registered_date";s:10:"1403/10/01";s:18:"total_tickets_sold";s:2:"56";s:6:"points";d:367;s:12:"days_elapsed";i:116;}i:473;a:5:{s:2:"id";s:5:"16398";s:15:"registered_date";s:10:"1403/12/21";s:18:"total_tickets_sold";s:2:"54";s:6:"points";d:123;s:12:"days_elapsed";i:35;}i:474;a:5:{s:2:"id";s:4:"7474";s:15:"registered_date";s:10:"1402/06/21";s:18:"total_tickets_sold";s:2:"53";s:6:"points";d:1767;s:12:"days_elapsed";i:583;}i:475;a:5:{s:2:"id";s:4:"8055";s:15:"registered_date";s:10:"1403/10/13";s:18:"total_tickets_sold";s:2:"53";s:6:"points";d:330;s:12:"days_elapsed";i:104;}i:476;a:5:{s:2:"id";s:5:"18993";s:15:"registered_date";s:10:"1403/12/30";s:18:"total_tickets_sold";s:2:"53";s:6:"points";d:96;s:12:"days_elapsed";i:26;}i:477;a:5:{s:2:"id";s:4:"4770";s:15:"registered_date";s:10:"1402/02/27";s:18:"total_tickets_sold";s:2:"52";s:6:"points";d:2108;s:12:"days_elapsed";i:697;}i:478;a:5:{s:2:"id";s:4:"3152";s:15:"registered_date";s:10:"1401/06/06";s:18:"total_tickets_sold";s:2:"50";s:6:"points";d:2906;s:12:"days_elapsed";i:963;}i:479;a:5:{s:2:"id";s:4:"7949";s:15:"registered_date";s:10:"1403/07/28";s:18:"total_tickets_sold";s:2:"50";s:6:"points";d:560;s:12:"days_elapsed";i:181;}i:480;a:5:{s:2:"id";s:4:"4322";s:15:"registered_date";s:10:"1402/02/11";s:18:"total_tickets_sold";s:2:"49";s:6:"points";d:2155;s:12:"days_elapsed";i:713;}i:481;a:5:{s:2:"id";s:4:"7559";s:15:"registered_date";s:10:"1402/09/22";s:18:"total_tickets_sold";s:2:"49";s:6:"points";d:1486;s:12:"days_elapsed";i:490;}i:482;a:5:{s:2:"id";s:4:"7603";s:15:"registered_date";s:10:"1402/11/14";s:18:"total_tickets_sold";s:2:"49";s:6:"points";d:1327;s:12:"days_elapsed";i:437;}i:483;a:5:{s:2:"id";s:4:"3074";s:15:"registered_date";s:10:"1401/02/21";s:18:"total_tickets_sold";s:2:"48";s:6:"points";d:3220;s:12:"days_elapsed";i:1068;}i:484;a:5:{s:2:"id";s:4:"3342";s:15:"registered_date";s:10:"1401/10/15";s:18:"total_tickets_sold";s:2:"47";s:6:"points";d:2512;s:12:"days_elapsed";i:832;}i:485;a:5:{s:2:"id";s:4:"7472";s:15:"registered_date";s:10:"1402/06/17";s:18:"total_tickets_sold";s:2:"47";s:6:"points";d:1777;s:12:"days_elapsed";i:587;}i:486;a:5:{s:2:"id";s:4:"7706";s:15:"registered_date";s:10:"1403/01/25";s:18:"total_tickets_sold";s:2:"47";s:6:"points";d:1111;s:12:"days_elapsed";i:365;}i:487;a:5:{s:2:"id";s:4:"2809";s:15:"registered_date";s:10:"1400/11/10";s:18:"total_tickets_sold";s:2:"45";s:6:"points";d:3528;s:12:"days_elapsed";i:1171;}i:488;a:5:{s:2:"id";s:4:"7518";s:15:"registered_date";s:10:"1402/08/05";s:18:"total_tickets_sold";s:2:"45";s:6:"points";d:1629;s:12:"days_elapsed";i:538;}i:489;a:5:{s:2:"id";s:4:"7972";s:15:"registered_date";s:10:"1403/08/16";s:18:"total_tickets_sold";s:2:"45";s:6:"points";d:501;s:12:"days_elapsed";i:162;}i:490;a:5:{s:2:"id";s:4:"7605";s:15:"registered_date";s:10:"1402/11/14";s:18:"total_tickets_sold";s:2:"43";s:6:"points";d:1325;s:12:"days_elapsed";i:437;}i:491;a:5:{s:2:"id";s:4:"8057";s:15:"registered_date";s:10:"1403/10/16";s:18:"total_tickets_sold";s:2:"43";s:6:"points";d:317;s:12:"days_elapsed";i:101;}i:492;a:5:{s:2:"id";s:4:"8064";s:15:"registered_date";s:10:"1403/10/24";s:18:"total_tickets_sold";s:2:"43";s:6:"points";d:293;s:12:"days_elapsed";i:93;}i:493;a:5:{s:2:"id";s:4:"7849";s:15:"registered_date";s:10:"1403/05/14";s:18:"total_tickets_sold";s:2:"42";s:6:"points";d:782;s:12:"days_elapsed";i:256;}i:494;a:5:{s:2:"id";s:4:"8090";s:15:"registered_date";s:10:"1403/11/22";s:18:"total_tickets_sold";s:2:"42";s:6:"points";d:206;s:12:"days_elapsed";i:64;}i:495;a:5:{s:2:"id";s:3:"458";s:15:"registered_date";s:10:"1400/04/05";s:18:"total_tickets_sold";s:2:"40";s:6:"points";d:4183;s:12:"days_elapsed";i:1390;}i:496;a:5:{s:2:"id";s:4:"7898";s:15:"registered_date";s:10:"1403/06/27";s:18:"total_tickets_sold";s:2:"40";s:6:"points";d:649;s:12:"days_elapsed";i:212;}i:497;a:5:{s:2:"id";s:4:"7507";s:15:"registered_date";s:10:"1402/07/25";s:18:"total_tickets_sold";s:2:"37";s:6:"points";d:1659;s:12:"days_elapsed";i:549;}i:498;a:5:{s:2:"id";s:4:"7901";s:15:"registered_date";s:10:"1403/07/02";s:18:"total_tickets_sold";s:2:"37";s:6:"points";d:633;s:12:"days_elapsed";i:207;}i:499;a:5:{s:2:"id";s:4:"8009";s:15:"registered_date";s:10:"1403/09/06";s:18:"total_tickets_sold";s:2:"37";s:6:"points";d:435;s:12:"days_elapsed";i:141;}i:500;a:5:{s:2:"id";s:4:"7729";s:15:"registered_date";s:10:"1403/02/13";s:18:"total_tickets_sold";s:2:"36";s:6:"points";d:1050;s:12:"days_elapsed";i:346;}i:501;a:5:{s:2:"id";s:4:"7795";s:15:"registered_date";s:10:"1403/04/10";s:18:"total_tickets_sold";s:2:"36";s:6:"points";d:882;s:12:"days_elapsed";i:290;}i:502;a:5:{s:2:"id";s:4:"8045";s:15:"registered_date";s:10:"1403/09/26";s:18:"total_tickets_sold";s:2:"36";s:6:"points";d:375;s:12:"days_elapsed";i:121;}i:503;a:5:{s:2:"id";s:4:"8061";s:15:"registered_date";s:10:"1403/10/20";s:18:"total_tickets_sold";s:2:"34";s:6:"points";d:302;s:12:"days_elapsed";i:97;}i:504;a:5:{s:2:"id";s:4:"7779";s:15:"registered_date";s:10:"1403/03/24";s:18:"total_tickets_sold";s:2:"33";s:6:"points";d:932;s:12:"days_elapsed";i:307;}i:505;a:5:{s:2:"id";s:4:"7566";s:15:"registered_date";s:10:"1402/10/03";s:18:"total_tickets_sold";s:2:"31";s:6:"points";d:1447;s:12:"days_elapsed";i:479;}i:506;a:5:{s:2:"id";s:4:"7618";s:15:"registered_date";s:10:"1402/11/26";s:18:"total_tickets_sold";s:2:"31";s:6:"points";d:1285;s:12:"days_elapsed";i:425;}i:507;a:5:{s:2:"id";s:4:"7934";s:15:"registered_date";s:10:"1403/07/12";s:18:"total_tickets_sold";s:2:"31";s:6:"points";d:601;s:12:"days_elapsed";i:197;}i:508;a:5:{s:2:"id";s:4:"7807";s:15:"registered_date";s:10:"1403/04/19";s:18:"total_tickets_sold";s:2:"30";s:6:"points";d:853;s:12:"days_elapsed";i:281;}i:509;a:5:{s:2:"id";s:4:"7997";s:15:"registered_date";s:10:"1403/08/28";s:18:"total_tickets_sold";s:2:"30";s:6:"points";d:460;s:12:"days_elapsed";i:150;}i:510;a:5:{s:2:"id";s:4:"8004";s:15:"registered_date";s:10:"1403/09/03";s:18:"total_tickets_sold";s:2:"29";s:6:"points";d:442;s:12:"days_elapsed";i:144;}i:511;a:5:{s:2:"id";s:4:"8022";s:15:"registered_date";s:10:"1403/09/11";s:18:"total_tickets_sold";s:2:"29";s:6:"points";d:418;s:12:"days_elapsed";i:136;}i:512;a:5:{s:2:"id";s:4:"3229";s:15:"registered_date";s:10:"1401/08/08";s:18:"total_tickets_sold";s:2:"28";s:6:"points";d:2709;s:12:"days_elapsed";i:900;}i:513;a:5:{s:2:"id";s:4:"7623";s:15:"registered_date";s:10:"1402/12/01";s:18:"total_tickets_sold";s:2:"28";s:6:"points";d:1269;s:12:"days_elapsed";i:420;}i:514;a:5:{s:2:"id";s:4:"8109";s:15:"registered_date";s:10:"1403/12/06";s:18:"total_tickets_sold";s:2:"28";s:6:"points";d:159;s:12:"days_elapsed";i:50;}i:515;a:5:{s:2:"id";s:5:"15927";s:15:"registered_date";s:10:"1403/12/20";s:18:"total_tickets_sold";s:2:"27";s:6:"points";d:117;s:12:"days_elapsed";i:36;}i:516;a:5:{s:2:"id";s:5:"19000";s:15:"registered_date";s:10:"1403/12/30";s:18:"total_tickets_sold";s:2:"27";s:6:"points";d:87;s:12:"days_elapsed";i:26;}i:517;a:5:{s:2:"id";s:4:"7837";s:15:"registered_date";s:10:"1403/05/10";s:18:"total_tickets_sold";s:2:"25";s:6:"points";d:788;s:12:"days_elapsed";i:260;}i:518;a:5:{s:2:"id";s:4:"7967";s:15:"registered_date";s:10:"1403/08/14";s:18:"total_tickets_sold";s:2:"25";s:6:"points";d:500;s:12:"days_elapsed";i:164;}i:519;a:5:{s:2:"id";s:4:"7541";s:15:"registered_date";s:10:"1402/09/04";s:18:"total_tickets_sold";s:2:"24";s:6:"points";d:1532;s:12:"days_elapsed";i:508;}i:520;a:5:{s:2:"id";s:4:"8036";s:15:"registered_date";s:10:"1403/09/18";s:18:"total_tickets_sold";s:2:"24";s:6:"points";d:395;s:12:"days_elapsed";i:129;}i:521;a:5:{s:2:"id";s:4:"7712";s:15:"registered_date";s:10:"1403/01/30";s:18:"total_tickets_sold";s:2:"23";s:6:"points";d:1088;s:12:"days_elapsed";i:360;}i:522;a:5:{s:2:"id";s:4:"7719";s:15:"registered_date";s:10:"1403/02/02";s:18:"total_tickets_sold";s:2:"22";s:6:"points";d:1078;s:12:"days_elapsed";i:357;}i:523;a:5:{s:2:"id";s:5:"26488";s:15:"registered_date";s:10:"1404/01/13";s:18:"total_tickets_sold";s:2:"22";s:6:"points";d:43;s:12:"days_elapsed";i:12;}i:524;a:5:{s:2:"id";s:4:"7904";s:15:"registered_date";s:10:"1403/07/04";s:18:"total_tickets_sold";s:2:"21";s:6:"points";d:622;s:12:"days_elapsed";i:205;}i:525;a:5:{s:2:"id";s:4:"8092";s:15:"registered_date";s:10:"1403/11/22";s:18:"total_tickets_sold";s:2:"21";s:6:"points";d:199;s:12:"days_elapsed";i:64;}i:526;a:5:{s:2:"id";s:4:"7676";s:15:"registered_date";s:10:"1402/12/22";s:18:"total_tickets_sold";s:2:"20";s:6:"points";d:1204;s:12:"days_elapsed";i:399;}i:527;a:5:{s:2:"id";s:4:"7697";s:15:"registered_date";s:10:"1403/01/10";s:18:"total_tickets_sold";s:2:"20";s:6:"points";d:1147;s:12:"days_elapsed";i:380;}i:528;a:5:{s:2:"id";s:4:"7675";s:15:"registered_date";s:10:"1402/12/20";s:18:"total_tickets_sold";s:2:"19";s:6:"points";d:1209;s:12:"days_elapsed";i:401;}i:529;a:5:{s:2:"id";s:4:"7739";s:15:"registered_date";s:10:"1403/02/20";s:18:"total_tickets_sold";s:2:"19";s:6:"points";d:1023;s:12:"days_elapsed";i:339;}i:530;a:5:{s:2:"id";s:4:"7978";s:15:"registered_date";s:10:"1403/08/20";s:18:"total_tickets_sold";s:2:"19";s:6:"points";d:480;s:12:"days_elapsed";i:158;}i:531;a:5:{s:2:"id";s:4:"7989";s:15:"registered_date";s:10:"1403/08/26";s:18:"total_tickets_sold";s:2:"19";s:6:"points";d:462;s:12:"days_elapsed";i:152;}i:532;a:5:{s:2:"id";s:4:"7452";s:15:"registered_date";s:10:"1402/05/31";s:18:"total_tickets_sold";s:2:"18";s:6:"points";d:1818;s:12:"days_elapsed";i:604;}i:533;a:5:{s:2:"id";s:4:"7475";s:15:"registered_date";s:10:"1402/06/22";s:18:"total_tickets_sold";s:2:"18";s:6:"points";d:1752;s:12:"days_elapsed";i:582;}i:534;a:5:{s:2:"id";s:4:"7861";s:15:"registered_date";s:10:"1403/05/22";s:18:"total_tickets_sold";s:2:"18";s:6:"points";d:750;s:12:"days_elapsed";i:248;}i:535;a:5:{s:2:"id";s:4:"8089";s:15:"registered_date";s:10:"1403/11/20";s:18:"total_tickets_sold";s:2:"18";s:6:"points";d:204;s:12:"days_elapsed";i:66;}i:536;a:5:{s:2:"id";s:4:"7426";s:15:"registered_date";s:10:"1402/05/03";s:18:"total_tickets_sold";s:2:"17";s:6:"points";d:1902;s:12:"days_elapsed";i:632;}i:537;a:5:{s:2:"id";s:4:"7833";s:15:"registered_date";s:10:"1403/05/07";s:18:"total_tickets_sold";s:2:"16";s:6:"points";d:794;s:12:"days_elapsed";i:263;}i:538;a:5:{s:2:"id";s:4:"7914";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:2:"16";s:6:"points";d:605;s:12:"days_elapsed";i:200;}i:539;a:5:{s:2:"id";s:4:"8033";s:15:"registered_date";s:10:"1403/09/17";s:18:"total_tickets_sold";s:2:"16";s:6:"points";d:395;s:12:"days_elapsed";i:130;}i:540;a:5:{s:2:"id";s:4:"7963";s:15:"registered_date";s:10:"1403/08/12";s:18:"total_tickets_sold";s:2:"15";s:6:"points";d:503;s:12:"days_elapsed";i:166;}i:541;a:5:{s:2:"id";s:4:"8028";s:15:"registered_date";s:10:"1403/09/13";s:18:"total_tickets_sold";s:2:"14";s:6:"points";d:407;s:12:"days_elapsed";i:134;}i:542;a:5:{s:2:"id";s:3:"397";s:15:"registered_date";s:10:"1400/04/01";s:18:"total_tickets_sold";s:2:"13";s:6:"points";d:4186;s:12:"days_elapsed";i:1394;}i:543;a:5:{s:2:"id";s:4:"7626";s:15:"registered_date";s:10:"1402/12/05";s:18:"total_tickets_sold";s:2:"13";s:6:"points";d:1252;s:12:"days_elapsed";i:416;}i:544;a:5:{s:2:"id";s:4:"8091";s:15:"registered_date";s:10:"1403/11/22";s:18:"total_tickets_sold";s:2:"13";s:6:"points";d:196;s:12:"days_elapsed";i:64;}i:545;a:5:{s:2:"id";s:4:"3306";s:15:"registered_date";s:10:"1401/09/22";s:18:"total_tickets_sold";s:2:"12";s:6:"points";d:2569;s:12:"days_elapsed";i:855;}i:546;a:5:{s:2:"id";s:4:"7953";s:15:"registered_date";s:10:"1403/08/01";s:18:"total_tickets_sold";s:2:"12";s:6:"points";d:535;s:12:"days_elapsed";i:177;}i:547;a:5:{s:2:"id";s:4:"7994";s:15:"registered_date";s:10:"1403/08/28";s:18:"total_tickets_sold";s:2:"11";s:6:"points";d:454;s:12:"days_elapsed";i:150;}i:548;a:5:{s:2:"id";s:4:"8015";s:15:"registered_date";s:10:"1403/09/07";s:18:"total_tickets_sold";s:2:"11";s:6:"points";d:424;s:12:"days_elapsed";i:140;}i:549;a:5:{s:2:"id";s:4:"3207";s:15:"registered_date";s:10:"1401/07/23";s:18:"total_tickets_sold";s:2:"10";s:6:"points";d:2751;s:12:"days_elapsed";i:916;}i:550;a:5:{s:2:"id";s:4:"7653";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:2:"10";s:6:"points";d:1236;s:12:"days_elapsed";i:411;}i:551;a:5:{s:2:"id";s:4:"7946";s:15:"registered_date";s:10:"1403/07/26";s:18:"total_tickets_sold";s:2:"10";s:6:"points";d:552;s:12:"days_elapsed";i:183;}i:552;a:5:{s:2:"id";s:5:"18484";s:15:"registered_date";s:10:"1403/12/27";s:18:"total_tickets_sold";s:2:"10";s:6:"points";d:90;s:12:"days_elapsed";i:29;}i:553;a:5:{s:2:"id";s:5:"18668";s:15:"registered_date";s:10:"1403/12/29";s:18:"total_tickets_sold";s:2:"10";s:6:"points";d:84;s:12:"days_elapsed";i:27;}i:554;a:5:{s:2:"id";s:5:"18755";s:15:"registered_date";s:10:"1403/12/29";s:18:"total_tickets_sold";s:2:"10";s:6:"points";d:84;s:12:"days_elapsed";i:27;}i:555;a:5:{s:2:"id";s:4:"8054";s:15:"registered_date";s:10:"1403/10/13";s:18:"total_tickets_sold";s:1:"9";s:6:"points";d:315;s:12:"days_elapsed";i:104;}i:556;a:5:{s:2:"id";s:4:"8003";s:15:"registered_date";s:10:"1403/09/03";s:18:"total_tickets_sold";s:1:"8";s:6:"points";d:435;s:12:"days_elapsed";i:144;}i:557;a:5:{s:2:"id";s:4:"8010";s:15:"registered_date";s:10:"1403/09/06";s:18:"total_tickets_sold";s:1:"8";s:6:"points";d:426;s:12:"days_elapsed";i:141;}i:558;a:5:{s:2:"id";s:4:"8100";s:15:"registered_date";s:10:"1403/11/24";s:18:"total_tickets_sold";s:1:"8";s:6:"points";d:189;s:12:"days_elapsed";i:62;}i:559;a:5:{s:2:"id";s:4:"3013";s:15:"registered_date";s:10:"1400/12/24";s:18:"total_tickets_sold";s:1:"7";s:6:"points";d:3383;s:12:"days_elapsed";i:1127;}i:560;a:5:{s:2:"id";s:5:"23279";s:15:"registered_date";s:10:"1404/01/07";s:18:"total_tickets_sold";s:1:"7";s:6:"points";d:56;s:12:"days_elapsed";i:18;}i:561;a:5:{s:2:"id";s:4:"4608";s:15:"registered_date";s:10:"1402/02/21";s:18:"total_tickets_sold";s:1:"6";s:6:"points";d:2111;s:12:"days_elapsed";i:703;}i:562;a:5:{s:2:"id";s:4:"7983";s:15:"registered_date";s:10:"1403/08/22";s:18:"total_tickets_sold";s:1:"6";s:6:"points";d:470;s:12:"days_elapsed";i:156;}i:563;a:5:{s:2:"id";s:4:"8042";s:15:"registered_date";s:10:"1403/09/21";s:18:"total_tickets_sold";s:1:"6";s:6:"points";d:380;s:12:"days_elapsed";i:126;}i:564;a:5:{s:2:"id";s:4:"8079";s:15:"registered_date";s:10:"1403/11/10";s:18:"total_tickets_sold";s:1:"6";s:6:"points";d:230;s:12:"days_elapsed";i:76;}i:565;a:5:{s:2:"id";s:4:"5207";s:15:"registered_date";s:10:"1402/03/13";s:18:"total_tickets_sold";s:1:"5";s:6:"points";d:2051;s:12:"days_elapsed";i:683;}i:566;a:5:{s:2:"id";s:4:"7553";s:15:"registered_date";s:10:"1402/09/16";s:18:"total_tickets_sold";s:1:"5";s:6:"points";d:1490;s:12:"days_elapsed";i:496;}i:567;a:5:{s:2:"id";s:4:"7654";s:15:"registered_date";s:10:"1402/12/10";s:18:"total_tickets_sold";s:1:"5";s:6:"points";d:1235;s:12:"days_elapsed";i:411;}i:568;a:5:{s:2:"id";s:4:"7872";s:15:"registered_date";s:10:"1403/05/29";s:18:"total_tickets_sold";s:1:"5";s:6:"points";d:725;s:12:"days_elapsed";i:241;}i:569;a:5:{s:2:"id";s:4:"7980";s:15:"registered_date";s:10:"1403/08/21";s:18:"total_tickets_sold";s:1:"5";s:6:"points";d:473;s:12:"days_elapsed";i:157;}i:570;a:5:{s:2:"id";s:4:"8041";s:15:"registered_date";s:10:"1403/09/21";s:18:"total_tickets_sold";s:1:"5";s:6:"points";d:380;s:12:"days_elapsed";i:126;}i:571;a:5:{s:2:"id";s:4:"3386";s:15:"registered_date";s:10:"1401/11/17";s:18:"total_tickets_sold";s:1:"4";s:6:"points";d:2398;s:12:"days_elapsed";i:799;}i:572;a:5:{s:2:"id";s:4:"4840";s:15:"registered_date";s:10:"1402/02/30";s:18:"total_tickets_sold";s:1:"4";s:6:"points";d:2083;s:12:"days_elapsed";i:694;}i:573;a:5:{s:2:"id";s:4:"7834";s:15:"registered_date";s:10:"1403/05/07";s:18:"total_tickets_sold";s:1:"4";s:6:"points";d:790;s:12:"days_elapsed";i:263;}i:574;a:5:{s:2:"id";s:4:"7860";s:15:"registered_date";s:10:"1403/05/21";s:18:"total_tickets_sold";s:1:"4";s:6:"points";d:748;s:12:"days_elapsed";i:249;}i:575;a:5:{s:2:"id";s:4:"7899";s:15:"registered_date";s:10:"1403/07/01";s:18:"total_tickets_sold";s:1:"4";s:6:"points";d:625;s:12:"days_elapsed";i:208;}i:576;a:5:{s:2:"id";s:4:"7917";s:15:"registered_date";s:10:"1403/07/09";s:18:"total_tickets_sold";s:1:"4";s:6:"points";d:601;s:12:"days_elapsed";i:200;}}';
//        foreach ( unserialize( $owners_data ) as $owner_data ) {
//            $new_point = [
//                'user_id'       => $owner_data['id'],
//                'point'         => $owner_data['points'],
//                'action'        => 'سوابق',
//                'description'   => 'امتیاز سوابق مجموعه داری',
//            ];
//
//            saeed_print($new_point);
//
//            add_new_point($new_point);
//        }

//        INSERT INTO `points` ( `user_id`, `point`, `action`, `description`, `created_at`) VALUES
//        (89164, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(96522, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(96995, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(81749, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(15558, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(8028, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(96689, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(26521, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(97767, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(101302, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(101293, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(54742, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(77025, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(7933, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(3155, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(2467, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(100132, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(36132, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874'),
//(80637, 1000, 'سوابق', 'فعالیت مجموعه داری', '1764508874');

        $brands = get_terms(array(
            'taxonomy'      => 'product_brand',
            'hide_empty'    => true,
        ));

        foreach ($brands as $brand) {
            $brand_id = $brand->term_id;

            $args = array(
                'post_type'  => 'product',
                'meta_query' => array(
                    array(
                        'key'     => 'product_brand',
                        'value'   => $brand_id,
                        'compare' => '=',
                    ),
                ),
            );
            $products = get_posts($args);

            $total_tickets_sold = 0;
            foreach ($products as $product)
                $total_tickets_sold += (int)get_post_meta($product->ID, 'tickets_sold', true);

            if ( $total_tickets_sold )
                update_term_meta($brand_id, 'brand_reputation', $total_tickets_sold);
        }

        die();
    }

/**
 * GET: rate_power_user
 *
 * هدف: کپی متای امتیاز محصول
 * استفاده: یک‌بار
 * وابستگی: update_post_meta
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:529
 */
    if( isset($_GET['rate_power_user']) ) {

        $products_id = [5104];

        foreach ( $products_id as $product_id ) :

            update_post_meta( $product_id, 'clone_product_rates', get_post_meta($product_id, 'product_rates', true) );
            update_post_meta( $product_id, 'clone_comments_count_new', get_post_meta($product_id, 'comments_count_new', true));

        endforeach;

        die();
    }

/**
 * GET: telegramx
 *
 * هدف: تست لاگ/تلگرام
 * استفاده: تست
 * وابستگی: saeed_store
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:543
 */
    if( isset($_GET['telegramx']) ) {

        echo current_time('H:i:s');

        saeed_store('saeed');

//        $ch = curl_init("https://impec.ir/?chat_id=97720589&message=salam");
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_exec($ch);
//        curl_close($ch);
//
//        file_get_contents("https://impec.ir/?chat_id=97720589&message=salam");

//        $options = [
//            "http" => [
//                "method" => "GET",
//                "header" => "User-Agent: Mozilla/5.0 (compatible; MyBot/1.0)\r\n"
//            ]
//        ];
//        $context = stream_context_create($options);
//        $response = file_get_contents("https://impec.ir/?chat_id=97720589&message=salam", false, $context);
//        echo $response;
        die();
    }

/**
 * GET: cm_300
 *
 * هدف: بازسازی امتیاز از کامنت ۳۰۰+ محصول
 * استفاده: تحلیل سنگین
 * وابستگی: get_comments, saeed_print
 * امنیت: بدون احراز هویت
 * وضعیت: guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:568
 */
    if( isset($_GET['cm_300']) ) {

        $product_ids = array(538135, 1635, 538155, 537803, 490170, 542937, 542432, 541302, 539626, 539608, 539564, 539512, 539509, 1897, 539299, 533504, 532638, 531587, 530424, 530358, 529528, 529497, 529174,
            529069, 527026, 526417, 2150, 522070, 521994, 521030, 520909, 519646, 517286, 516040, 514476, 514467, 514059, 512498, 511359, 511033, 510400, 510362, 508099, 507937, 443595, 500030, 499528, 499441,
            498283, 496053, 490096, 489211, 488731, 488633, 488213, 488078, 488019, 486773, 485312, 483708, 482200, 482354, 482367, 481623, 481097, 479745, 1649, 474755, 472428, 472130, 472094, 471992, 70653,
            3720, 468476, 464817, 466829, 465606, 465393, 464782, 35043, 463454, 462946, 13260, 52537, 457309, 455070, 447890, 447851, 447595, 440887, 446144, 445620, 444939, 443258, 442825, 441662, 440644,
            440306, 440282, 440264, 440233, 439951, 438986, 436461, 436455, 434183, 434173, 434106, 431286, 427014, 425891, 423249, 420707, 73114, 418306, 416772, 415200, 2063, 415202, 415193, 409393, 408583,
            344615, 407225, 405670, 91070, 404211, 403881, 403561, 395598, 393056, 2048, 390782, 388357, 387917, 387776, 166363, 385758, 383915, 382454, 380897, 380811, 380685, 378387, 373449, 371273, 368764,
            2108, 368021, 366766, 365808, 365195, 342044, 355123, 354862, 354307, 353935, 336417, 346356, 346334, 345947, 7878, 343501, 343362, 343323, 334025, 337994, 336796, 336907, 335012, 335003, 333821,
            4898, 330722, 328026, 327706, 325572, 322346, 317434, 1781, 1689, 2054, 309692, 300206, 295150, 295093, 287040, 272235, 267698, 263318, 261541, 261569, 261593, 260739, 256317, 245273, 237601, 227820,
            207847, 40926, 196406, 191792, 191056, 187826, 196527, 174267, 173684, 173382, 169326, 10356, 169159, 166675, 163961, 145024, 154030, 134833, 129327, 127015, 118711, 110215, 97593, 88510, 87447, 1770,
            79708, 58776, 52833, 50308, 46785, 52635, 43204, 29633, 28325, 25616, 24720, 24194, 52594, 40800, 21755, 17527, 16097, 15249, 9933, 40875, 7865, 7874, 7683, 5104, 4675, 5054, 4134, 5042, 2043, 2029,
            2026, 1997, 1908, 1488, 843, 1065, 941, 922, 173420, 63797, 128417, 4195, 555000, 554746, 554726, 553972, 553932, 553892, 553555, 553178, 559461, 559446, 562523, 562085, 561281, 561173, 561118,
            533541, 555640, 559480, 543509, 557147, 560596, 559903, 537519, 550798, 550362, 550336, 537433, 536881, 545575, 547917, 545907, 545857, 553078, 552832, 552282, 558538, 557627, 557202, 560245,
            560621, 561000, 561050, 561020, 560230, 560327, 562624, 562579, 562546, 562825, 563170, 567154, 565661, 565647, 565611, 565593, 565432, 565355, 565108, 563319, 567760, 568692, 568356, 568026, 567828,
            570352, 570305, 570278, 569898, 569861, 569649, 568760, 571931, 571909, 571892, 571867, 2100, 572565, 573395, 572670, 572644, 573437, 573404, 574752, 573916, 573665, 575579, 575552, 575521, 582240,
            565128, 581563, 581511, 578902, 578872, 578855, 576159, 576092, 576080, 583358, 585639, 581887, 585992, 586469, 583218, 588667, 588603, 588584, 587887, 589337, 588770, 589774, 589363, 589802, 582356,
            591018, 589831, 593639, 593596, 593583, 593564, 594081, 596196, 4687, 594641, 596731, 596706, 596538, 596820, 596799, 596767, 7674, 596839, 599912, 599775, 599527, 598390, 600718, 600687, 601878, 601688,
            601016, 600757, 601918, 601914, 601910, 601904, 601930, 601924, 602304, 602292, 602514, 602507, 602492, 602314, 603022, 602591, 602535, 603077, 603035, 603576, 603157, 603814, 603800, 603749, 603725,
            603659, 603707, 603674, 603641, 441023, 643082, 643055, 643037, 676763, 676755, 526944, 675982, 674711, 673819, 671724, 671568, 678843, 678492, 678487, 678484, 472421, 678860, 679548, 679531, 678902,
            681108, 679889, 685436, 682251, 685447, 685844, 685486, 686095, 686075, 689015, 689008, 687853, 689477, 689040, 689034,);
//        $product_ids = array(538135, 1635, 538155, 537803,);

        $today = current_time('Y-m-d H:i:s');
        $thirty_days_ago = date('Y-m-d H:i:s', strtotime('-365 days', strtotime($today)));

        foreach ($product_ids as $product_id) {
            $args = array(
                'post_id' => $product_id,
                'date_query' => array(
                    array(
                        'after'     => $thirty_days_ago,
                        'before'    => $today,
                        'inclusive' => true,
                    ),
                ),
                'status' => 'approve',
                'type' => 'review',
            );
            $comments = get_comments($args);

            $detail[$product_id] = [
                'rating'    => 0,
                'count'     => 0,
                'rate'      => 0,
            ];

            foreach ( $comments as $comment ) {

                $detail[$product_id]['rating']    += (int)get_comment_meta($comment->comment_ID, 'rating', true);
                $detail[$product_id]['count']     ++;
                $detail[$product_id]['rate']      = $detail[$product_id]['rating'] / $detail[$product_id]['count'];

//                $user_power = get_user_rating_power($comment->user_id);
//                $wpdb->insert( 'hottest_products', [
//                    'product_id'        => $comment->comment_post_ID,
//                    'comment_id'        => $comment->comment_ID,
//                    'w_rate'            => (int)get_comment_meta($comment->comment_ID, 'rating', true),
//                    'w_comments_count'  => $user_power,
//                    'time'              => time()
//                ]);
            }
        }

        saeed_print(serialize($detail));

        die();
    }

/**
 * GET: sms_send
 *
 * هدف: هم‌تراز کردن product_state روی لیست ID
 * استفاده: مهاجرت
 * وابستگی: get_post_meta, add_post_meta
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:637
 */
    if( isset($_GET['sms_send']) ) {
        $args = array(
            'post_type'      => 'product',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        );

        $product_ids = get_posts($args);

        $product_ids = [343294,
            347275,
            356501,
            483409,
            550815,
            347061,
            498706,
            351099,
            365977,
            465643,
            351107,
            9820,
            431335,
            476422,
            304656,
            499415,];


        foreach ($product_ids as $product_id) {
            if (metadata_exists('post', $product_id, 'product_state') && metadata_exists('post', $product_id, '_product_state'))
                continue;

            $product_state = get_post_meta($product_id, 'product_state', true) == 'active' ? 1 : 0;

            $product_state = ($product_state == '1') ? 'active' : 'deactivated';

            if (!metadata_exists('post', $product_id, 'product_state'))
                add_post_meta($product_id, 'product_state', $product_state, true);

            if (!metadata_exists('post', $product_id, '_product_state'))
                add_post_meta($product_id, '_product_state', 'field_684aed6b29aea', true);
        }
    }

/**
 * GET: get_hottest
 *
 * هدف: محاسبه hot_score و hottest_products_set
 * استفاده: دستی
 * وابستگی: ez_webservice, get_bayesian_score
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری / guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:680
 */
    if( isset($_GET['get_hottest']) ) {
        global $wpdb;

        $wpdb->get_results( "DELETE FROM hottest_products WHERE time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))");

        $C          = 4.3; // مثلاً میانگین همه $w_rate ها
        $m          = 15;  // مثلاً میانگین همه w_cm30 ها یا یک عدد ثابت
        $max_views  = 4000; // ماکزیمم بازدیدهای ماهانه یک بازی

        $rows = $wpdb->get_results( "SELECT * FROM hottest_products", ARRAY_A );

        $hottest = [];
        foreach ( $rows as $row )
            if ( !isset( $hottest[$row['product_id']] ) ) {
                $hottest[$row['product_id']]['w_rate']              = $row['w_rate'] * $row['w_comments_count']; // مجموع rate ها و نه میانگین آنها
                $hottest[$row['product_id']]['w_comments_count']    = $row['w_comments_count'];
            } else {
                $hottest[$row['product_id']]['w_rate']              += $row['w_rate'] * $row['w_comments_count'];
                $hottest[$row['product_id']]['w_comments_count']    += $row['w_comments_count'];
            }

        $product_ids    = implode(',', array_keys($hottest));
        $views          = (array)(json_decode( ez_webservice( array ('type' => 'get_products_30days_views_count', 'data' => ['product_ids' => $product_ids]) ) ));

        foreach ( $hottest as $product_id => $hottest_item ) :

            $w_rate             = $hottest_item['w_rate'] / $hottest_item['w_comments_count']; // به دست آوردن میانیگن rate ها
            $w_comments_count   = $hottest_item['w_comments_count'];
            $view               = $views[$product_id];

            $bayesian_score = get_bayesian_score($w_rate, $w_comments_count, $C, $m);

            $normalized_bayesian_score = 0.6 * $bayesian_score + 0.4 * log($w_comments_count + 1);

            $normalized_views = log($view + 1) / log($max_views + 1) * 5;

            $hot_score[$product_id] = 0.67 * $normalized_bayesian_score + 0.33 * $normalized_views;

            saeed_print([
                'product_id'                => $product_id,
                'w_rate'                    => $w_rate,
                'w_comments_count'          => $w_comments_count,
                'views'                     => $view,
                'bayesian_score'            => $bayesian_score,
                'normalized_bayesian_score' => $normalized_bayesian_score,
                'normalized_views'          => $normalized_views,
                'hot_score'                 => $hot_score[$product_id],
            ]);

        endforeach;

        asort($hot_score);

        saeed_print(serialize($hot_score));

        $product_data = [];
        foreach ( $hot_score as $product_id => $count )
            $product_data[] = $product_id;

        ez_webservice( array('type' => 'hottest_products_set', 'data' => array_reverse($product_data)) );

        die();
    }

/**
 * GET: shrt
 *
 * هدف: تست generate_shortlink
 * استفاده: توسعه
 * وابستگی: generate_shortlink
 * امنیت: بدون احراز هویت
 * وضعیت: انتقال shortlink
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:744
 */
    if( isset($_GET['shrt']) ) {

        $res = generate_shortlink('https://escapezoom.ir/wp-admin/options-permalink.php');

        saeed_print($res);
        die();
    }

/**
 * GET: brands_of_products_update
 *
 * هدف: برند روی محصولات deactivated
 * استفاده: مهاجرت
 * وابستگی: WP_Query
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:752
 */
    if ( isset( $_GET['brands_of_products_update'] ) ) {
        add_action('init', function() {

            $args = array(
                'post_type'      => 'product',
                'post_status'    => 'publish',
                'posts_per_page' => 500,
                'meta_query'     => array(
                    array(
                        'key'     => 'product_state',
                        'value'   => 'deactivated',
                        'compare' => 'LIKE',
                    ),
                ),
                'paged'          => 5,
            );
            $query = new WP_Query($args);

            while ($query->have_posts()) : $query->the_post();
                global $product;

                $brand_data = get_the_terms(get_the_ID(), 'product_brand')[0];

                update_post_meta(get_the_ID(), 'product_brand', $brand_data->term_id);

            endwhile;
            wp_reset_postdata();

        });
    }

/**
 * GET: get_brands_name
 *
 * هدف: CSV نام برندها
 * استفاده: مهاجرت
 * وابستگی: get_term, fputcsv
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:783
 */
    if ( isset( $_GET['get_brands_name'] ) ) {

        $brand_ids = [142,192,417,422,257,300,140,318,419,193,167,462,232,672,290,572,186,288,574,165,287,521,391,755,461,297,1153,268,699,204,314,120,1002,1191,829,480,337,700,601,679,173,199,365,545,286,655,1085,1129,350,696,842,745,166,370,639,653,576,432,195,226,242,404,786,1280,481,999,316,747,1079,1181,146,844,658,923,214,758,514,262,732,719,403,398,1001,336,229,358,1047,549,207,930,295,636,674,291,476,695,308,159,340,294,347,196,157,143,243,540,448,427,691,887,821,675,577,1053,873,1092,1071,998,840,759,763,299,865,536,437,774,729,579,1166,1269,1222,751,959,961,400,171,282,784,997,702,744,312,414,374,1018,1016,667,440,715,735,388,760,478,311,525,562,657,533,338,472,399,206,163,373,330,276,154,625,203,511,1067,356,1010,402,767,1132,1261,556,814,578,940,804,1155,728,1170,1188,1174,1159,565,948,1229,1125,1095,766,678,820,676,466,1064,791,750,526,1021,1005,703,988,1238,980,659,917,670,559,912,801,881,830,738,929,575,895,1136,452,643,1022,809,853,1299,1028,1285,1099,519,1137,851,1135,1244,1032,835,477,938,823,1089,267,993,606,467,323,600,1312,1283,1114,771,331,231,455,1206,952,1172,164,908,724,585,1029,762,736,947,808,915,944,780,894,718,650,858,646,775,389,812,383,697,656,765,651,683,219,581,212,598,349,573,357,329,604,351,233,454,360,426,283,155,434,222,198,266,280,278,235,354,450,210,215,292,261,150,145,189,188,530,946,627,621,622,610,561,543,522,446,864,443,420,641,1228,1264,486,1207,1204,1202,1198,1197,1195,1193,1189,1182,1173,1168,1167,1164,1163,1165,1157,1154,1144,1131,1124,1123,1307,1118,1117,1116,1113,1213,1110,1098,1093,1091,1088,1080,1077,1075,1070,1068,1065,1063,1058,1057,1054,1052,1041,1034,1020,1019,1012,1006,995,987,983,1104,962,950,1119,910,1184,890,888,1200,866,863,237,860,848,845,819,914,807,782,849,770,943,710,704,693,689,666,654,642,1313,1311,1310,1309,1308,1305,1298,1297,1294,1292,1290,1289,1284,1279,415,1278,1277,1027,1276,1275,989,1274,1272,1270,1268,1267,1265,376,1271,1263,1262,1259,1257,772,1247,1240,1243,1242,1241,1239,1234,1233,1227,1225,1216,1215,1210,546,520,557,509,1306,1295,1296,1291,1273,1260,1256,1211,1232,1226,1220,1046,1194,1190,1187,1185,1183,1171,1161,1162,1152,1145,1128,1127,1112,1120,1115,1109,1107,1105,1102,1101,1103,1096,1083,1082,1081,752,1076,1069,1066,1061,1060,1059,1055,1051,1050,1049,1048,1045,1087,1043,1042,1038,1040,1035,1037,1033,1031,1025,1014,1003,996,994,990,986,985,984,1011,963,971,960,951,942,939,936,935,934,933,932,931,924,922,916,907,896,903,889,1013,882,880,876,874,879,869,868,867,878,862,861,859,877,857,847,846,839,838,837,834,833,831,828,827,825,818,1078,813,991,870,810,806,802,937,796,795,793,787,785,783,779,799,773,768,727,756,757,753,891,743,737,733,725,722,720,798,797,788,790,721,711,764,800,731,717,740,692,684,682,681,680,677,927,669,668,558,662,663,626,911,645,644,826,635,634,629,628,623,353,624,608,605,603,602,701,594,593,590,850,586,587,583,560,552,551,566,548,568,582,661,817,687,537,1130,524,517,516,515,456,220,513,501,532,534,503,474,469,464,463,510,460,457,445,620,223,441,438,431,409,418,412,408,405,156,429,381,378,377,371,369,368,361,539,352,334,333,328,326,324,322,319,315,306,433,468,236,475,274,271,265,264,263,252,256,247,255,254,251,246,238,234,230,152,227,225,221,458,218,217,213,209,205,202,197,194,444,183,179,175,168,172,161,160,151,726,149,147,1282,327,527,451,296,281,275,449,439,410,407,396,382,375,567,372,367,542,541,364,348,341,201,505,241,535,313,307,531,303,301,];

        $csv_file = fopen('brands.csv', 'w');
        fputcsv($csv_file, ['brand_id', 'brand_name']);

        foreach ($brand_ids as $brand_id) {
            $term = get_term($brand_id, 'product_brand');
            if (!is_wp_error($term) && $term) {
                $brand_name = $term->name;
            } else {
                $brand_name = 'نامشخص';
            }

            fputcsv($csv_file, [$brand_id, $brand_name]);
        }

        fclose($csv_file);
        echo 'فایل CSV ساخته شد.';

    }

/**
 * GET: update_min_price
 *
 * هدف: محاسبه min_price از سانس‌ها
 * استفاده: batch دستی
 * وابستگی: get_sanses, post meta
 * امنیت: بدون احراز هویت
 * وضعیت: guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:806
 */
    if ( isset( $_GET['update_min_price'] ) ) {

        $products_id = [678,721,725,731,738,830,833,840,843,847,850,853,857,863,867,870,919,922,926,929,932,941,944,951,953,958,961,963,967,976,996,1005,1017,1036,1037,1050,1058,1062,1065,1072,1076,1129,1134,1145,1170,1174,1177,1182,1191,1220,1292,1299,1308,1316,1322,1326,1331,1346,1348,1352,1360,1367,1400,1429,1431,1434,1441,1448,1452,1455,1459,1461,1463,1465,1467,1471,1478,1506,1508,1510,1512,1516,1525,1530,1532,1535,1537,1539,1544,1546,1548,1594,1597,1599,1604,1606,1608,1616,1618,1622,1624,1630,1632,1635,1637,1640,1642,1644,1649,1652,1655,1689,1693,1699,1700,1704,1708,1715,1719,1721,1731,1734,1736,1739,1745,1768,1770,1773,1776,1781,1785,1796,1800,1802,1804,1809,1812,1819,1822,1836,1838,1841,1848,1850,1858,1869,1874,1878,1889,1893,1897,1900,1903,1906,1908,1997,1999,2003,2005,2007,2009,2012,2017,2019,2022,2024,2026,2029,2031,2034,2036,2038,2040,2043,2045,2047,2048,2050,2052,2054,2059,2061,2063,2065,2067,2072,2075,2077,2079,2081,2083,2090,2092,2094,2096,2098,2100,2108,2112,2118,2120,2122,2135,2150,2152,2155,2158,2161,2433,2435,2440,2453,2455,2458,2618,2621,2623,2696,2762,2764,2766,2826,3002,3041,3046,3068,3085,3105,3107,3117,3120,3123,3128,3131,3134,3136,3139,3141,3153,3203,3214,3304,3336,3367,3443,3471,3552,3658,3661,3678,3709,3720,3760,3763,3766,3778,4132,4134,4141,4155,4195,4373,4376,4529,4564,4595,4607,4609,4612,4614,4618,4626,4629,4632,4649,4658,4662,4664,4668,4672,4675,4680,4685,4687,4690,4693,4696,4702,4705,4708,4728,4731,4752,4755,4756,4768,4770,4842,4877,4879,4883,4885,4888,4891,4898,4948,4962,4983,5025,5029,5031,5033,5036,5042,5054,5056,5059,5061,5066,5070,5077,5104,5186,5198,5283,5285,5387,5435,5438,5440,5497,5506,5593,5690,5694,5696,5712,5738,5764,5830,6079,6085,6095,6118,6122,6234,6292,6630,6646,6709,6724,6733,6830,6944,6954,7011,7204,7219,7524,7648,7674,7683,7861,7862,7865,7871,7874,7875,7878,8810,8845,8983,8987,8990,9029,9134,9140,9390,9462,9470,9852,9933,10229,10356,10600,11256,11419,11537,12267,12279,12282,12942,12957,12988,13241,13242,13254,13260,13262,13266,13274,13306,13442,13467,13480,13487,13540,14403,14412,14434,14438,14441,14584,14710,14732,15216,15249,15484,16084,16097,16776,17149,17527,17965,18417,18879,18909,19036,19234,19254,19287,19348,19480,20002,20439,20452,20725,20755,21755,21764,23775,24194,24208,24218,24720,25505,25616,25766,25833,27717,28325,28579,29371,29456,29619,29633,30125,30456,31053,31783,32643,32709,32931,33314,33473,33481,33529,33631,33657,34871,34895,34914,35043,35052,35072,36050,38163,38212,38487,39102,39531,40453,40792,40800,40875,40884,40910,40913,40926,40928,41742,41790,43204,44593,46785,46799,46801,49008,49047,49882,49989,50284,50308,52063,52070,52437,52537,52547,52576,52594,52635,52833,55019,55249,58322,58343,58394,58599,58776,60733,62575,63797,63838,64478,67184,67247,67304,67639,67646,67695,70010,70078,70321,70591,70633,70653,70950,70954,72392,72721,72729,73114,76441,76948,78428,78433,78439,78447,79708,80753,81638,83869,83898,83915,84094,84146,84202,87281,87431,87447,87458,87471,88510,88730,88788,89763,91070,91392,91662,94604,97593,98789,98814,98961,99322,101651,102443,104214,104229,104243,106468,106498,106515,109379,110215,110223,111288,114125,114137,114160,118668,118697,118711,120959,120968,121624,123887,123944,123957,127015,127202,127645,127709,127860,127927,128307,128391,128417,128559,128648,129002,129039,129068,129327,129642,129708,131915,131939,131947,131968,132489,133289,133956,134094,134777,134833,137266,137406,137858,137866,138514,138747,138826,139202,140147,141076,143451,143690,143786,145024,145482,146539,148220,148236,149806,150084,150115,150240,153086,154030,155762,155776,156221,156558,156987,158782,158809,158818,158823,158837,159425,161296,161317,161610,161714,162312,162358,163684,163961,164798,166363,166391,166675,166747,166799,167195,167331,167371,167681,167715,169159,169184,169326,169939,171057,171710,173382,173420,173422,173684,173692,174145,174267,177197,181212,181275,181574,182949,183673,186308,186723,187826,188506,191056,191427,191792,193183,196339,196377,196406,196527,196860,199297,202850,203833,204090,204098,207847,208511,208968,209613,212658,212998,213378,219803,220152,220783,221612,222740,222853,224359,227313,227820,227985,228594,228989,230799,230928,231394,231415,234663,235168,236378,237601,241354,243149,244969,244987,245273,245449,245490,247859,248088,248132,248629,248680,249528,250379,250446,250650,251249,251299,254268,254281,255411,256133,256317,258626,260739,260962,261459,261541,261569,261593,262141,263318,263353,263385,263772,267438,267684,267698,267901,267912,267929,267955,272235,272743,276275,278137,278212,285343,287035,287040,287042,293298,293336,295088,295093,295150,296460,300206,306575,307782,309692,315537,315737,315749,317361,317434,320582,321278,321483,321542,322043,322346,322898,322906,324043,325572,326572,326595,326855,326925,326946,327706,328019,328026,328818,329087,329233,330431,330510,330527,330722,331274,331308,331721,333232,333472,333816,333821,333842,334025,334127,334139,335003,335012,335017,336417,336446,336459,336796,336832,336907,337630,337634,337994,338025,338112,339222,339346,340053,340744,340829,342041,342044,342220,342238,342310,343315,343323,343340,343362,343438,343501,343564,344595,344615,344642,344679,345947,346334,346356,346431,348127,348409,348787,349943,351142,351188,351217,351593,352358,352640,353110,353935,353952,354307,354754,354812,354862,355123,355127,361119,362084,362436,363603,364109,364134,364402,365072,365195,365808,365910,365945,365965,365972,366620,366708,366766,367022,368021,368421,368764,368777,371273,371325,371337,371343,371672,372358,372913,373449,375539,375947,378294,378340,378353,378387,378769,378816,378885,379351,380685,380811,380818,380882,380897,381813,381857,381906,382452,382454,383862,383915,385757,385758,385770,385783,387776,387917,388333,388349,388357,389393,389419,389427,389432,389992,390007,390144,390782,390791,390802,391675,392887,393052,393056,393284,395598,395605,395609,395627,395636,398443,398506,398725,401740,401797,403527,403534,403561,403881,403900,403910,404202,404211,405619,405631,405670,407225,407422,408583,408649,408701,409375,409393,410740,410786,410795,415180,415193,415200,415202,415209,415244,416772,416795,416954,416974,417225,418306,419211,420707,420725,420731,420742,420748,420750,423249,423334,423346,423655,425865,425879,425891,425904,427014,431239,431286,431323,431381,431397,434106,434173,434183,434275,434639,436455,436461,437732,437743,438880,438905,438939,438986,439036,439860,439899,439951,439993,440233,440264,440282,440306,440644,440850,440887,440966,441008,441023,441662,441697,442825,443258,443265,443595,443952,443992,444939,445613,445620,445626,445904,446144,446260,446297,446874,447595,447851,447862,447890,452201,453410,453895,454181,454203,455070,455564,457309,461559,462900,462946,463010,463454,463465,463494,464782,464800,464817,465361,465393,465606,465881,466813,466829,467680,467694,468476,469271,469478,471512,471992,472070,472094,472130,472421,472428,472441,474463,474742,474755,475732,476317,476332,476338,476351,479745,480335,481064,481097,481623,482084,482102,482132,482150,482176,482200,482354,482367,482381,482465,482606,483708,483816,485159,485188,485312,486149,486171,486194,486235,486280,486513,486525,486543,486773,488019,488078,488213,488633,488731,489206,489211,490096,490170,491755,491763,491931,491990,492392,492784,492837,492865,493271,493803,496053,496153,496461,496526,496586,497391,498283,498398,499441,499528,500030,501658,501677,503500,503669,505551,505702,506048,506141,506338,506693,507895,507937,508099,509760,510362,510400,511033,511359,512498,514059,514198,514256,514467,514476,516022,516040,517286,518207,519646,520886,520909,521030,521994,522070,523765,525580,526417,526462,526533,526944,527026,529037,529069,529114,529174,529403,529497,529528,530286,530358,530424,530474,530513,531587,532638,533504,533541,533611,534041,536794,536800,536881,536897,537164,537433,537519,537717,537803,538135,538155,539299,539509,539512,539517,539521,539522,539528,539564,539608,539626,539644,540523,541302,542432,542937,543509,543526,545551,545575,545857,545907,547044,547098,547107,547651,547858,547884,547917,549467,549492,550112,550336,550362,550798,552205,552234,552263,552282,552818,552832,553048,553064,553078,553178,553456,553555,553892,553932,553972,554002,554726,554746,555000,555557,555582,555629,555640,555929,557147,557202,557545,557556,557614,557627,557806,558538,558564,559430,559446,559461,559480,559903,560230,560245,560272,560327,560596,560621,561000,561020,561050,561118,561173,561265,561281,562039,562085,562523,562546,562579,562624,562796,562825,563170,563303,563319,564585,565108,565128,565330,565355,565432,565593,565611,565647,565661,565684,565834,567154,567760,567776,567828,568026,568356,568692,568760,569212,569550,569649,569861,569898,570278,570305,570316,570352,570740,570822,571867,571892,571909,571931,572565,572644,572670,572681,573395,573404,573437,573455,573629,573665,573916,574752,575521,575552,575579,576055,576080,576092,576159,578855,578872,578902,581511,581563,581887,582240,582356,583218,583358,583405,583799,585447,585639,585992,586469,586972,587887,587942,588584,588603,588667,588699,588770,589337,589363,589379,589408,589431,589774,589802,589831,591018,592202,593555,593564,593583,593596,593639,594081,594641,596147,596196,596538,596706,596731,596767,596799,596820,596839,598390,599022,599527,599775,599912,600687,600718,600757,600782,601016,601688,601735,601878,601904,601910,601914,601918,601924,601930,602065,602292,602304,602314,602492,602507,602514,602535,602591,603022,603035,603077,603157,603576,603641,603659,603674,603707,603725,603749,603800,603814,621707,643037,643055,643082,671568,671676,671724,673819,673865,674711,674725,675982,676449,676755,676763,678484,678487,678492,678843,678860,678902,679531,679548,679889,681108,681622,682251,685436,685447,685486,685844,686075,686095,686483,686495,687853,689008,689015,689034,689040,689477,690763,690781,690811,691003,691477,692762,692783,693067,693278,693493,693850,693879,694349,694782,694805,695091,696202,696225,696296,696313,697040,697042,698372,698387,699607,701697,701736,701771,701935,701956,701991,702050,702071,702796,703276,703394,703599,704618,704679,704730,704994,705035,705282,705283,705871,705893,706418,706464,706503,710179,710189,710636,711068,711083,711091,711336,711349,711358,711362,711470,711490,711510,711528,711529,711547,711849,711869,711875,711879,711901,711913,711926,711938,711965,711985,711997,712034,712042,712548,712562,712575,712591,712635,713705,713731,713956,713982,713999,714011,714038,714086,714116,714319,715310,715322,715325,715342,715346,715357,715368,715451,716634,716639,716686,716996,717017,717031,717109,717112,717131,717140,717149,717167,717394,717441,717482,717800,717872,717898,717980,717994,718206,718216,718231,718239,718271,718292,718365,718413,718500,718508,718708,718728,718749,718761,718797,718824,718845,718863,718913,718925,718937,721073,721087,721102,721382,721392,721485,721819,721835,721882,721933,721997,722023,722294,722423,723425,723440,723468,723851,723948,723969,724304,724317,724720,725228,727417,727449,727458,727470,727481,729994,730957,731429,733654,733694,733713,733813,733836,733859,736099,736109,736284,736375,736385,736388,736390,736394,736396,736398,736497,736499,736500,736501,736504,736506,736507,736508,736592,736598,736603,736610,736769,736772,736774,736776,736785,736788,736796,736799,736808,736820,736829,736839,736862,736865,736869,736885,736909,736915,736918,736930,736933,736952,736972,736983,737221,737231,737243,737248,737255,737263,737266,737268,737282,737286,737290,737294,737315,737390,737410,738676,741155,741175,741178,741186,741187,741235,741248,741384,741435,741453,744910,744926,745025,747261,747280,747539,747560,749118,749183,750446,750528,750822,751087,752976,752995,753015,753029,753047,753260,753268,753292,753572,753993,755745,755754,755787,755792,755972,755995,756603,758424,758468,758501,758706,758717,758724];

        foreach ( $products_id as $product_id ) {

            $last_price = [];
            $sanses     = get_sanses($product_id);

            $special_discount = 1;

            if ( get_post_meta($product_id, 'special_discount_enable', true) ) {
                if ( get_post_meta($product_id, 'special_discount_date', true) > time() ) {

                    $percentage = floatval(get_post_meta($product_id, 'special_discount_percentage', true));
                    $special_discount = 1 - ($percentage / 100);
                }
            }

            $special_discount = floatval($special_discount);

            foreach ( $sanses as $sans_by_type ) {
                foreach ( $sans_by_type as $sans ) {

                    $base_price = $sans['off_price'] ?: $sans['price'];
                    $base_price = floatval($base_price);

                    $last_price[] = $base_price * $special_discount;
                }
            }

            if ( !empty($last_price) ) {
                update_post_meta($product_id, 'min_price', min($last_price));
            }
        }

    }

/**
 * GET: medoo
 *
 * هدف: تست زمان‌بندی SMS payamak
 * استفاده: تست
 * وابستگی: curl, credential در کد
 * امنیت: رمز در کد
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:844
 */
    if ( isset( $_GET['medoo'] ) ) {

        $sms_c_d                = strtotime("+1 seconds", time());
        $sms_c_myDate           = date("Y-m-d H:i:s", $sms_c_d);

//        ini_set("soap.wsdl_cache_enabled", "0");
//        $sms_client = new SoapClient('http://api.payamak-panel.com/post/schedule.asmx?wsdl', array('encoding'=>'UTF-8'));
//        $parameters['username']         = "xescape";
//        $parameters['password']         = "2kkh7Gm36%#X91h";
//        $parameters['to']               = '9353316152';
//        $parameters['from']             = "2191307900";
//        $parameters['text']             = "سلام";
//        $parameters['isflash']          = false;
//        $parameters['scheduleDateTime'] = "$sms_c_my_date_and_time";
//        $parameters['period']           = "Once";
//        $sms_client->AddSchedule($parameters)->AddScheduleResult;

        $text = 'سعید';
        $text .= "\n\nلغو 11";

        $data = [
            'username'      => "xescape",
            'password'      => "2kkh7Gm36%#X91h",
            'text'          => $text,
            'to'            => "9353316152",
            "from"          => '2191307900',
            'scheduleDate'  => $sms_c_myDate,
            'period'        => 0
        ];

        $post_data = http_build_query($data);
        $handle = curl_init('https://rest.payamak-panel.com/api/SendSMS/SendSchedule');
        curl_setopt($handle, CURLOPT_HTTPHEADER, array(
            'content-type' => 'application/x-www-form-urlencoded'
        ));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        $response = curl_exec($handle);
    }

/**
 * GET: auto_dis_proc
 *
 * هدف: نرمال‌سازی meta auto_disable
 * استفاده: نگهداری
 * وابستگی: $wpdb postmeta
 * امنیت: بدون احراز هویت
 * وضعیت: guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:887
 */
    if ( isset( $_GET['auto_dis_proc'] ) ) {

//        $ids = array(28325,25616,9933,5104,5054,5042,922);
//        $placeholders = implode( ', ', array_fill( 0, count($ids), '%d' ) );

        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s",
                'auto_disable'
            ),
            ARRAY_A
        );

        $allowed = array(15, 30, 60, 120, 180);
        foreach ( $rows as $row ) {
            $post_id    = intval( $row['post_id'] );
            $raw        = trim( $row['meta_value'] );

            $value = (int) $raw;

            if ( in_array( $value, $allowed, true ) )
                $final = $value;
            else
                if ( $value <= 30 )
                    $final = 30;
                else
                    $final = 60;

            if ( $value !== $final )
                update_post_meta( $post_id, 'auto_disable', $final );
        }
    }

/**
 * GET: refunded_points
 *
 * هدف: بررسی امتیاز استرداد
 * استفاده: تحلیل
 * وابستگی: $wpdb orders
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:920
 */
    if ( isset( $_GET['refunded_points'] ) ) {
        global $wpdb;

        $list = [
            [50525, 600718, 19],
            [17735, 5104, 18],
            [7620, 5104, 13],
            [16005, 600718, 11],
            [78211, 5104, 8],
            [27544, 542937, 8],
            [30065, 603077, 8],
            [50509, 5104, 7],
            [42460, 553178, 7],
            [53388, 600718, 7],
            [16814, 334025, 6],
            [21573, 582240, 6],
            [70215, 565128, 6],
            [14636, 46785, 5],
            [16814, 2100, 5],
            [14636, 678860, 5],
            [49886, 403881, 5],
            [17819, 388357, 5],
            [27544, 568026, 5],
            [66743, 713999, 5],
            [18195, 602314, 5],
            [52034, 589337, 5],
            [13903, 589831, 5],
            [51966, 425891, 5],
            [18059, 261541, 4],
            [18888, 2100, 4],
            [21355, 334025, 4],
            [18698, 40875, 4],
            [55283, 4885, 4],
            [67694, 35043, 4],
            [16526, 472130, 4],
            [7822, 600718, 4],
            [42739, 563319, 4],
            [11242, 558538, 4],
            [62980, 565355, 4],
            [29936, 603077, 4],
            [9953, 603077, 4],
            [15657, 565661, 4],
            [55283, 552282, 4],
            [64385, 479745, 4],
            [80380, 346356, 4],
            [31421, 474755, 4],
            [64619, 711470, 4],
            [39855, 596767, 4],
            [27544, 488731, 4],
            [9720, 729941, 4],
            [9720, 568356, 4],
            [8047, 729941, 4],
            [8047, 568356, 4],
            [82588, 589831, 4],
            [41519, 425891, 4],
            [13806, 425891, 4],
            [16876, 154030, 3],
            [17883, 166363, 3],
            [14248, 334025, 3],
        ];

        foreach ( $list as $key => $lis ) {

            $query = $wpdb->prepare("
                SELECT COUNT(*) AS correct_points, pm_user.meta_value AS customer_id
                FROM wp_posts p
                JOIN wp_postmeta pm_user ON pm_user.post_id = p.ID AND pm_user.meta_key = '_customer_user'
                JOIN wp_postmeta pm_code ON pm_code.post_id = p.ID AND pm_code.meta_key = 'code_otagh'
                WHERE p.post_type = 'shop_order'
                AND pm_user.meta_value = %d
                  AND pm_code.meta_value = %d
                  AND p.post_status = 'wc-walletx';
            ", $lis[0], $lis[1]);

            $count = $wpdb->get_var($query);
            if ( $count < $lis[2] )
                $list[$key][] = $count;
        }
        saeed_print($list, 1);

        exit();
    }

/**
 * GET: points_purge
 *
 * هدف: پاکسازی امتیاز کالکشن کاربران
 * استفاده: عملیاتی خطرناک
 * وابستگی: $wpdb points
 * امنیت: بدون احراز هویت
 * وضعیت: guard یا حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1003
 */
    if ( isset( $_GET['points_purge'] ) ) {
        global $wpdb;

        $user_ids = [80,497,1184,1404,2716,2758,3042,3054,3113,3181,3206,3230,3238,3290,3346,3564,3708,3811,4187,4931,5055,5143,5879,6274,6453,6962,7418,7460,7489,7497,7503,7543,7556,7575,7588,7607,7618,7628,7683,7691,7701,7778,7787,7791,7846,7847,7858,7859,7870,7879,7902,7919,7953,7956,7969,7990,7996,8000,8034,8044,8046,8048,8056,8060,8074,8075,8097,8098,8099,8101,8106,8110,8112,8114,8313,8319,8329,8330,8331,8343,8353,8359,8362,8370,8371,8386,8393,8396,8433,8532,9122,9169,9323,9516,9721,9749,9845,9867,9968,9990,10014,10070,10246,10258,10273,10370,10564,10771,10855,10990,11042,11086,11118,11136,11228,11237,11271,11541,11577,11604,11654,11671,11864,11979,12022,12029,12036,12055,12060,12158,12218,12264,12308,12394,12413,12507,12590,12592,12600,12654,12662,12670,12726,12730,12992,13003,13091,13109,13151,13157,13173,13174,13239,13263,13279,13321,13337,13351,13537,13554,13560,13570,13573,13638,13672,13701,13826,13856,13891,13916,13930,13935,13967,13995,14014,14106,14118,14138,14148,14160,14168,14174,14182,14195,14198,14275,14276,14291,14355,14442,14451,14466,14467,14500,14575,14579,14593,14688,14708,14732,14742,14797,14801,14808,14816,14817,14833,14880,14882,14910,14939,14952,14953,14954,14956,14962,14968,14975,15027,15031,15032,15047,15070,15072,15096,15109,15113,15130,15162,15216,15219,15221,15223,15224,15228,15230,15236,15261,15275,15285,15293,15326,15330,15354,15368,15372,15375,15383,15387,15396,15419,15423,15427,15429,15493,15495,15530,15542,15581,15644,15657,15727,15897,15916,16014,16056,16081,16248,16336,16370,16398,16592,16984,17122,17512,17606,17819,17939,17942,18299,18787,18898,19244,19798,20155,20327,21449,21754,22235,22283,22316,22394,22859,22918,23522,23652,25799,27010,27347,27544,28238,28319,28627,29936,30864,30991,31256,32280,32715,32858,33015,33355,34122,34143,34858,35210,35581,36000,36364,36838,37217,37845,38290,38668,38951,39310,40059,40090,41248,41947,42113,43610,43619,43867,46411,46990,47795,47892,49060,49886,50509,50997,51978,54778,55609,55611,56511,57019,57196,60433,60628,61179,62143,62995,65523,66073,66376,67125,68433,69058,69506,70939,73465,75406,75923,77377,77556,77731,82588,82621,83883];

        $user_ids_sql = implode(',', $user_ids);

        $collections_count = $wpdb->get_results("
            SELECT user_id, COUNT(*) AS collection_count
            FROM collections
            WHERE user_id IN ($user_ids_sql)
            GROUP BY user_id
        ", OBJECT_K);

        foreach ($user_ids as $user_id) {
            $collection_count   = isset($collections_count[$user_id]) ? $collections_count[$user_id]->collection_count : 0;

            update_user_meta($user_id, 'collections_count_valid_points', $collection_count);

            $last_some = $wpdb->get_col( $wpdb->prepare("
                SELECT ID
                FROM points
                WHERE user_id = %d AND action = %s
                ORDER BY ID DESC
                LIMIT %d
            ", $user_id, 'ایجاد کالکشن', $collection_count) );

            $ids_to_keep = implode(',', $last_some);

            if ( $ids_to_keep ) {
                $wpdb->query( $wpdb->prepare("
                    DELETE FROM points
                    WHERE user_id = %d 
                      AND action = %s
                      AND ID NOT IN ($ids_to_keep)
                ", $user_id, 'ایجاد کالکشن') );
            } else {
                $wpdb->query( $wpdb->prepare("
                    DELETE FROM points
                    WHERE user_id = %d 
                      AND action = %s
                ", $user_id, 'ایجاد کالکشن') );
            }
        }

        exit();
    }

/**
 * GET: collection_likes
 *
 * هدف: شمارش لایک کالکشن
 * استفاده: گزارش
 * وابستگی: $wpdb collections
 * امنیت: بدون احراز هویت
 * وضعیت: بررسی
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1051
 */
    if ( isset( $_GET['collection_likes'] ) ) {
        global $wpdb;

        $user_ids = [14952,13351,15047,15228,12507,15581,14953,15223,15221,5143,80,10564,8331,8396,17819,3811,9169,13239,10070,32280,7701,15031,16336,22235,28627,14275,15495,19798,9516,16014,17512,77377,7575,8386,12992,14451,19244,9990,13554,15423,56511,14168,15130,15727,55611,7859,7870,14808,14939,15219,15427,15493,37845,2716,7955,12590,30864,37217,4187,8044,8362,10855,13537,13967,15072,15429,15530,16592,21754,23652,497,3054,3230,3708,6274,7503,7607,7618,7847,7879,8097,8313,8353,8532,11136,12264,13173,13174,13701,14138,14575,14732,14742,14801,14962,15096,15275,15293,15326,15383,15657,17606,17939,18787,22918,31256,33355,43619,];

        foreach ( $user_ids as $user_id ) {
            $collections = $wpdb->get_results("
                SELECT users FROM `collections` WHERE `user_id` = $user_id
                
            ");

            $count = 0;
            foreach ($collections as $collection) {
                $liked_users = @unserialize($collection->users);
                if ( $liked_users )
                    $count += count($liked_users);
            }

            saeed_print($user_id . ' : ' . $count);
        }

        exit();
    }

/**
 * GET: points_points_purying
 *
 * هدف: dedupe امتیاز رزرو بازی
 * استفاده: نگهداری
 * وابستگی: $wpdb points
 * امنیت: بدون احراز هویت
 * وضعیت: guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1075
 */
    if ( isset( $_GET['points_points_purying'] ) ) {
        global $wpdb;

        $to_keep = $wpdb->get_results("
            SELECT MIN(ID) AS keep_id, user_id, description
            FROM {$wpdb->prefix}points
            WHERE action = 'رزرو بازی'
            GROUP BY user_id, description
            HAVING COUNT(*) > 2
        ");

        if ( ! empty( $to_keep ) ) {
            foreach ( $to_keep as $row ) {
                $user_id     = (int) $row->user_id;
                $description = esc_sql( $row->description );
                $keep_id     = (int) $row->keep_id;

                $wpdb->query("
                    DELETE FROM {$wpdb->prefix}points
                    WHERE user_id = {$user_id}
                      AND description = '{$description}'
                      AND action = 'رزرو بازی'
                      AND ID <> {$keep_id}
                ");
            }
        }

        $user_ids = [50525, 17735, 7620, 16005, 78211];

        foreach ( $user_ids as $user_id ) {

            $collections = $wpdb->get_results( $wpdb->prepare("
                SELECT users 
                FROM {$wpdb->prefix}collections
                WHERE user_id = %d
            ", $user_id) );

            $count = 0;

            foreach ( $collections as $collection ) {

                $liked_users = @unserialize( $collection->users );

                if ( is_array($liked_users) && ! empty($liked_users) ) {
                    $count += count($liked_users);
                }
            }
        }

        exit;
    }

/**
 * GET: satis_rebuild
 *
 * هدف: بازسازی آمار رضایت محصول
 * استفاده: batch
 * وابستگی: ez_rebuild_product_satisfaction_stats
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1127
 */
    if ( isset( $_GET['satis_rebuild'] ) ) {

        $products_id = [678,721,725,731,738,830,833,840,843,847,850,853,857,863,867,870,919,922,926,929,932,941,944,951,953,958,961,963,967,976,996,1005,1017,1036,1037,1050,1058,1062,1065,1072,1076,1129,1134,1145,1170,1174,1177,1182,1191,1220,1292,1299,1308,1316,1322,1326,1331,1346,1348,1352,1360,1367,1400,1429,1431,1434,1441,1448,1452,1455,1459,1461,1463,1465,1467,1471,1478,1506,1508,1510,1512,1516,1525,1530,1532,1535,1537,1539,1544,1546,1548,1594,1597,1599,1604,1606,1608,1616,1618,1622,1624,1630,1632,1635,1637,1640,1642,1644,1649,1652,1655,1689,1693,1699,1700,1704,1708,1715,1719,1721,1731,1734,1736,1739,1745,1768,1770,1773,1776,1781,1785,1796,1800,1802,1804,1809,1812,1819,1822,1836,1838,1841,1848,1850,1858,1869,1874,1878,1889,1893,1897,1900,1903,1906,1908,1997,1999,2003,2005,2007,2009,2012,2017,2019,2022,2024,2026,2029,2031,2034,2036,2038,2040,2043,2045,2047,2048,2050,2052,2054,2059,2061,2063,2065,2067,2072,2075,2077,2079,2081,2083,2090,2092,2094,2096,2098,2100,2108,2112,2118,2120,2122,2135,2150,2152,2155,2158,2161,2433,2435,2440,2453,2455,2458,2618,2621,2623,2696,2762,2764,2766,2826,3002,3041,3046,3068,3085,3105,3107,3117,3120,3123,3128,3131,3134,3136,3139,3141,3153,3203,3214,3304,3336,3367,3443,3471,3552,3658,3661,3678,3709,3720,3760,3763,3766,3778,4132,4134,4141,4155,4195,4373,4376,4529,4564,4595,4607,4609,4612,4614,4618,4626,4629,4632,4649,4658,4662,4664,4668,4672,4675,4680,4685,4687,4690,4693,4696,4702,4705,4708,4728,4731,4752,4755,4756,4768,4770,4842,4877,4879,4883,4885,4888,4891,4898,4948,4962,4983,5025,5029,5031,5033,5036,5042,5054,5056,5059,5061,5066,5070,5077,5104,5186,5198,5283,5285,5387,5435,5438,5440,5497,5506,5593,5690,5694,5696,5712,5738,5764,5830,6079,6085,6095,6118,6122,6234,6292,6630,6646,6709,6724,6733,6830,6944,6954,7011,7204,7219,7524,7648,7674,7683,7861,7862,7865,7871,7874,7875,7878,8810,8845,8983,8987,8990,9029,9134,9140,9390,9462,9470,9852,9933,10229,10356,10600,11256,11419,11537,12267,12279,12282,12942,12957,12988,13241,13242,13254,13260,13262,13266,13274,13306,13442,13467,13480,13487,13540,14403,14412,14434,14438,14441,14584,14710,14732,15216,15249,15484,16084,16097,16776,17149,17527,17965,18417,18879,18909,19036,19234,19254,19287,19348,19480,20002,20439,20452,20725,20755,21755,21764,23775,24194,24208,24218,24720,25505,25616,25766,25833,27717,28325,28579,29371,29456,29619,29633,30125,30456,31053,31783,32643,32709,32931,33314,33473,33481,33529,33631,33657,34871,34895,34914,35043,35052,35072,36050,38163,38212,38487,39102,39531,40453,40792,40800,40875,40884,40910,40913,40926,40928,41742,41790,43204,44593,46785,46799,46801,49008,49047,49882,49989,50284,50308,52063,52070,52437,52537,52547,52576,52594,52635,52833,55019,55249,58322,58343,58394,58599,58776,60733,62575,63797,63838,64478,67184,67247,67304,67639,67646,67695,70010,70078,70321,70591,70633,70653,70950,70954,72392,72721,72729,73114,76441,76948,78428,78433,78439,78447,79708,80753,81638,83869,83898,83915,84094,84146,84202,87281,87431,87447,87458,87471,88510,88730,88788,89763,91070,91392,91662,94604,97593,98789,98814,98961,99322,101651,102443,104214,104229,104243,106468,106498,106515,109379,110215,110223,111288,114125,114137,114160,118668,118697,118711,120959,120968,121624,123887,123944,123957,127015,127202,127645,127709,127860,127927,128307,128391,128417,128559,128648,129002,129039,129068,129327,129642,129708,131915,131939,131947,131968,132489,133289,133956,134094,134777,134833,137266,137406,137858,137866,138514,138747,138826,139202,140147,141076,143451,143690,143786,145024,145482,146539,148220,148236,149806,150084,150115,150240,153086,154030,155762,155776,156221,156558,156987,158782,158809,158818,158823,158837,159425,161296,161317,161610,161714,162312,162358,163684,163961,164798,166363,166391,166675,166747,166799,167195,167331,167371,167681,167715,169159,169184,169326,169939,171057,171710,173382,173420,173422,173684,173692,174145,174267,177197,181212,181275,181574,182949,183673,186308,186723,187826,188506,191056,191427,191792,193183,196339,196377,196406,196527,196860,199297,202850,203833,204090,204098,207847,208511,208968,209613,212658,212998,213378,219803,220152,220783,221612,222740,222853,224359,227313,227820,227985,228594,228989,230799,230928,231394,231415,234663,235168,236378,237601,241354,243149,244969,244987,245273,245449,245490,247859,248088,248132,248629,248680,249528,250379,250446,250650,251249,251299,254268,254281,255411,256133,256317,258626,260739,260962,261459,261541,261569,261593,262141,263318,263353,263385,263772,267438,267684,267698,267901,267912,267929,267955,272235,272743,276275,278137,278212,285343,287035,287040,287042,293298,293336,295088,295093,295150,296460,300206,306575,307782,309692,315537,315737,315749,317361,317434,320582,321278,321483,321542,322043,322346,322898,322906,324043,325572,326572,326595,326855,326925,326946,327706,328019,328026,328818,329087,329233,330431,330510,330527,330722,331274,331308,331721,333232,333472,333816,333821,333842,334025,334127,334139,335003,335012,335017,336417,336446,336459,336796,336832,336907,337630,337634,337994,338025,338112,339222,339346,340053,340744,340829,342041,342044,342220,342238,342310,343315,343323,343340,343362,343438,343501,343564,344595,344615,344642,344679,345947,346334,346356,346431,348127,348409,348787,349943,351142,351188,351217,351593,352358,352640,353110,353935,353952,354307,354754,354812,354862,355123,355127,361119,362084,362436,363603,364109,364134,364402,365072,365195,365808,365910,365945,365965,365972,366620,366708,366766,367022,368021,368421,368764,368777,371273,371325,371337,371343,371672,372358,372913,373449,375539,375947,378294,378340,378353,378387,378769,378816,378885,379351,380685,380811,380818,380882,380897,381813,381857,381906,382452,382454,383862,383915,385757,385758,385770,385783,387776,387917,388333,388349,388357,389393,389419,389427,389432,389992,390007,390144,390782,390791,390802,391675,392887,393052,393056,393284,395598,395605,395609,395627,395636,398443,398506,398725,401740,401797,403527,403534,403561,403881,403900,403910,404202,404211,405619,405631,405670,407225,407422,408583,408649,408701,409375,409393,410740,410786,410795,415180,415193,415200,415202,415209,415244,416772,416795,416954,416974,417225,418306,419211,420707,420725,420731,420742,420748,420750,423249,423334,423346,423655,425865,425879,425891,425904,427014,431239,431286,431323,431381,431397,434106,434173,434183,434275,434639,436455,436461,437732,437743,438880,438905,438939,438986,439036,439860,439899,439951,439993,440233,440264,440282,440306,440644,440850,440887,440966,441008,441023,441662,441697,442825,443258,443265,443595,443952,443992,444939,445613,445620,445626,445904,446144,446260,446297,446874,447595,447851,447862,447890,452201,453410,453895,454181,454203,455070,455564,457309,461559,462900,462946,463010,463454,463465,463494,464782,464800,464817,465361,465393,465606,465881,466813,466829,467680,467694,468476,469271,469478,471512,471992,472070,472094,472130,472421,472428,472441,474463,474742,474755,475732,476317,476332,476338,476351,479745,480335,481064,481097,481623,482084,482102,482132,482150,482176,482200,482354,482367,482381,482465,482606,483708,483816,485159,485188,485312,486149,486171,486194,486235,486280,486513,486543,486773,488019,488078,488213,488633,488731,489206,489211,490096,490170,491755,491763,491931,491990,492392,492784,492837,492865,493271,493803,496053,496153,496461,496526,496586,497391,498283,498398,499441,499528,500030,501658,501677,503500,503669,505551,505702,506048,506141,506338,506693,507895,507937,508099,509760,510362,510400,511033,511359,512498,514059,514198,514256,514467,514476,516022,516040,517286,518207,519646,520886,520909,521030,521994,522070,523765,525580,526417,526462,526533,526944,527026,529037,529069,529114,529174,529403,529497,529528,530286,530358,530424,530474,530513,531587,532638,533504,533541,533611,534041,536794,536800,536881,536897,537164,537433,537519,537717,537803,538135,538155,539299,539509,539512,539517,539521,539522,539528,539564,539608,539626,539644,540523,541302,542432,542937,543509,543526,545551,545575,545857,545907,547044,547098,547107,547651,547858,547884,547917,549467,549492,550112,550336,550362,550798,552205,552234,552263,552282,552818,552832,553048,553064,553078,553178,553456,553555,553892,553932,553972,554002,554726,554746,555000,555557,555582,555629,555640,555929,557147,557202,557545,557556,557614,557627,557806,558538,558564,559430,559446,559461,559480,559903,560230,560245,560272,560327,560596,560621,561000,561020,561050,561118,561173,561265,561281,562039,562085,562523,562546,562579,562624,562796,562825,563170,563303,563319,564585,565108,565128,565330,565355,565432,565593,565611,565647,565661,565684,565834,567154,567760,567776,567828,568026,568356,568692,568760,569212,569550,569649,569861,569898,570278,570305,570316,570352,570740,570822,571867,571892,571909,571931,572565,572644,572670,572681,573395,573404,573437,573455,573629,573665,573916,574752,575521,575552,575579,576055,576080,576092,576159,578855,578872,578902,581511,581563,581887,582240,582356,583218,583358,583405,583799,585447,585639,585992,586469,586972,587887,587942,588584,588603,588667,588699,588770,589337,589363,589379,589408,589431,589774,589802,589831,591018,592202,593555,593564,593583,593596,593639,594081,594641,596147,596196,596538,596706,596731,596767,596799,596820,596839,598390,599022,599527,599775,599912,600687,600718,600757,600782,601016,601688,601735,601878,601904,601910,601914,601918,601924,601930,602065,602292,602304,602314,602492,602507,602514,602535,602591,603022,603035,603077,603157,603576,603641,603659,603674,603707,603725,603749,603800,603814,621707,643037,643055,643082,671568,671676,671724,673819,673865,674711,674725,675982,676449,676755,676763,678484,678487,678492,678843,678860,678902,679531,679548,679889,681108,681622,682251,685436,685447,685486,685844,686075,686095,686483,686495,687853,689008,689015,689034,689040,689477,690763,690781,690811,691003,691477,692762,692783,693067,693278,693493,693850,693879,694349,694782,694805,695091,696202,696225,696296,696313,697040,697042,698372,698387,699607,701697,701736,701771,701935,701956,701991,702050,702071,702796,703276,703394,703599,704618,704679,704730,704994,705035,705282,705283,705871,705893,706418,706464,706503,710179,710189,710636,711068,711083,711091,711336,711349,711358,711362,711470,711490,711510,711528,711529,711547,711849,711869,711875,711879,711901,711913,711926,711938,711965,711985,711997,712034,712042,712548,712562,712575,712591,712635,713705,713731,713956,713982,713999,714011,714038,714086,714116,714319,715310,715322,715325,715342,715346,715357,715368,715451,716634,716639,716686,716996,717017,717031,717109,717112,717131,717140,717149,717167,717394,717441,717482,717800,717872,717898,717980,717994,718206,718216,718231,718239,718271,718292,718365,718413,718500,718508,718708,718728,718749,718761,718797,718824,718845,718863,718913,718925,718937,721073,721087,721102,721382,721392,721485,721819,721835,721882,721933,721997,722023,722294,722423,723425,723440,723468,723851,723948,723969,724304,724317,724720,725228,727417,727449,727458,727470,727481,729994,730957,731429,733654,733694,733713,733813,733836,733859,736099,736109,736284,736375,736385,736388,736390,736394,736396,736398,736497,736499,736500,736501,736504,736506,736507,736508,736592,736598,736603,736610,736769,736772,736774,736776,736785,736788,736796,736799,736808,736820,736829,736839,736862,736865,736869,736885,736909,736915,736918,736930,736933,736952,736972,736983,737221,737231,737243,737248,737255,737263,737266,737268,737282,737286,737290,737294,737315,737390,737410,738676,741155,741175,741178,741186,741187,741235,741248,741384,741435,741453,744910,744926,745025,747261,747280,747539,747560,749118,749183,750446,750528,750822,751087,751873,752976,752995,753015,753029,753047,753260,753268,753292,753572];

        foreach ( $products_id as $product_id ) {
            if( empty(get_post_meta($product_id, 'satisfaction_positive_count', true)) )
                ez_rebuild_product_satisfaction_stats($product_id);
        }
    }

/**
 * GET: get_unverified_list
 *
 * هدف: اجرای zarinpal_co_paid_transactions_process
 * استفاده: دستی
 * وابستگی: shop/payment zarinpal-cron
 * امنیت: بدون احراز هویت
 * وضعیت: guard
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1137
 */
    if ( isset( $_GET['get_unverified_list'] ) ) {
        zarinpal_co_paid_transactions_process();
        die();
    }

/**
 * GET: if_user_commented
 *
 * هدف: اجرای comment_reminder_sms_process
 * استفاده: دستی/cron
 * وابستگی: comment_reminder_sms_process
 * امنیت: بدون احراز هویت
 * وضعیت: نگهداری
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1142
 */
    if ( isset( $_GET['if_user_commented'] ) ) {
        comment_reminder_sms_process();
        die();
    }

/**
 * GET: team_reservation
 *
 * هدف: پردازش/ UI رزرو هم‌تیمی
 * استفاده: عملیاتی
 * وابستگی: process_team_reservation_*
 * امنیت: بدون احراز هویت
 * وضعیت: انتقال shop/team
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1147
 */
    if ( isset( $_GET['team_reservation'] ) ) {

        process_team_reservation_rewards(5000, 0);

        die();
        ?>

        <button id="start-team-reservation">شروع پردازش</button>
        <div id="progress" style="margin-top:10px;"></div>

        <script>
            jQuery(function($){
                var limit = 5000;
                var offset = 180000;
                var processing = false;

                function processBatch() {
                    if (!processing) return;

                    $.ajax({
                        url: window.location.href.split('?')[0],
                        method: 'GET',
                        data: {
                            team_reservation: 1,
                            limit: limit,
                            offset: offset
                        },
                        success: function(data) {
                            console.log(data);
                            $('#progress').html('Batch ' + (offset/limit + 1) + ' پردازش شد');

                            var processed = parseInt(data);

                            if (processed < limit) {
                                processing = false;
                                return;
                            }

                            offset += limit;
                            processBatch(); // بدون setTimeout → بلافاصله بعد از پاسخ اجرا می‌شود
                        },
                        error: function(xhr, status, error) {
                            console.error(error);
                            processing = false;
                        }
                    });
                }

                $('#start-team-reservation').on('click', function(){
                    if (!processing) {
                        processing = true;
                        processBatch();
                    }
                });
            });

        </script>

        <?php
/**
 * GET: limit
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: دستی از URL
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1206
 */
        if ( isset($_GET['limit']) )
            process_team_reservations_batch($_GET['limit'], $_GET['offset']);

        die();
    }

/**
 * GET: update_comment_list_table
 *
 * هدف: تست ez_cm_add_phone روی سفارش
 * استفاده: تست
 * وابستگی: ez_cm_add_phone
 * امنیت: بدون احراز هویت
 * وضعیت: حذف
 * منبع: saeed-legacy/109-init-upload_file_test-get_duplicate_transactions.php:1212
 */
    if ( isset( $_GET['update_comment_list_table'] ) ) {

        $order_id = 759498;
        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item)
            $product_id = $item->get_product_id();

        $players_data = get_post_meta($order_id, 'players_phone', true);

        foreach ($players_data as $player_data)
            $res = ez_cm_add_phone($product_id, $order_id, $player_data['phone']);

        die();
    }

});

function process_team_reservations_batch($limit, $offset) {
    global $wpdb;

    $all_users = $wpdb->get_results("
        SELECT user_id, meta_value
        FROM wp_usermeta
        WHERE meta_key = 'billing_phone'
    ", ARRAY_A);

    $user_lookup = [];
    foreach ($all_users as $u) {
        $phone_normalized               = ltrim(preg_replace('/[^0-9]/', '', $u['meta_value']), '0');
        $user_lookup[$phone_normalized] = $u['user_id'];
    }

    $orders = $wpdb->get_col($wpdb->prepare("
                SELECT post_id 
                FROM {$wpdb->postmeta}
                WHERE meta_key = 'players_phone'
                ORDER BY `wp_postmeta`.`post_id` ASC
                LIMIT %d OFFSET %d
            ", $limit, $offset));

    foreach ($orders as $order_id) {

        $billing_phone = get_post_meta($order_id, '_billing_phone', true);
        $players       = get_post_meta($order_id, 'players_phone', true);
        $product_id    = get_post_meta($order_id, 'code_otagh', true);

        if (empty($players) || !is_array($players)) continue;

        $clean_players = [];

        foreach ($players as $p)
            if (is_string($p))
                $clean_players[] = $p;
            elseif (is_array($p) && isset($p['phone']))
                $clean_players[] = $p['phone'];

        // حذف شماره سرگروهی
        foreach ($clean_players as $k => $phone)
            if ($phone == $billing_phone)
                unset($clean_players[$k]);

        if (empty($clean_players)) continue;

        // بررسی کاربران
        foreach ($clean_players as $phone) {

            try {
                $phone_normalized = ltrim(preg_replace('/[^0-9]/', '', $phone), '0');
            } catch (Throwable $e) {
                continue;
            }

            if (!isset($user_lookup[$phone_normalized])) continue; // بدون اکانت

            $user_id = $user_lookup[$phone_normalized];

            // ذخیره رکورد در جدول team_reservations بدون تکرار
            $wpdb->query($wpdb->prepare("
                INSERT IGNORE INTO team_reservations (user_id, product_id, order_id, phone)
                VALUES (%d, %d, %d, %s)
            ", $user_id, $product_id, $order_id, $phone_normalized));
        }
    }

    return count($orders);
}

function process_team_reservation_rewards($limit = 200, $offset = 0) {
    global $wpdb;

    // رکوردهای پردازش‌نشده
    $rows = $wpdb->get_results($wpdb->prepare("
        SELECT id, user_id, product_id
        FROM team_reservations
        WHERE processed = 0
        ORDER BY id ASC
        LIMIT %d OFFSET %d
    ", $limit, $offset));

//    $rows = $wpdb->get_results("SELECT * FROM `team_reservations` WHERE `user_id` = 15223");

//    saeed_print($rows, 1);

    if (!$rows) return 0;

    foreach ($rows as $row) {

        $user_id    = intval($row->user_id);
        $product_id = intval($row->product_id);

        $products = get_user_meta($user_id, 'teammate_products', true);
        if (!is_array($products)) $products = [];

        if (!in_array($product_id, $products)) {
            $products[] = $product_id;
            update_user_meta($user_id, 'teammate_products', $products);
        }

        $product_title  = get_the_title($product_id);
        $point_desc     = 'رزرو بازی ' . $product_title . ' - همگروهی';
        $already_exists = $wpdb->get_var( $wpdb->prepare("
            SELECT COUNT(*) FROM points
            WHERE user_id = %d
            AND description = %s
        ", $user_id, $point_desc) );

        if ( ! $already_exists )
            add_point('place-order-teammate', $user_id, $point_desc);

        // --- 3) علامت زدن رکورد به عنوان پردازش‌شده ---
        $wpdb->update(
            'team_reservations',
            ['processed' => 1],
            ['id' => $row->id],
            ['%d'],
            ['%d']
        );
    }

    return count($rows);
}


function get_bayesian_score($R, $v, $C, $m) {
    return $v + $m ? (($v / ($v + $m)) * $R) + (($m / ($v + $m)) * $C) : 0;
}

function encrypt_data($plaintext, $key) {
    $ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
    return bin2hex($iv . $ciphertext_raw);
}



