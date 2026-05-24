<?php
/**
 * ez_queryable_set_marketing_data2 (+1 more)
 *
 * توابع: ez_queryable_set_marketing_data2, ez_queryable_set_marketing_data هوک‌ها: woocommerce_after_register_post_type, init
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 868-1002)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// function ez_owner_wallet_held_24hrs2() {
//     add_action('woocommerce_after_register_post_type', 'ez_owner_wallet_held_24hrs');
// }
/*===============================*/
function ez_queryable_set_marketing_data2() {
    add_action('init', 'ez_queryable_set_marketing_data');
}
/*===============================*/
function ez_queryable_set_marketing_data () {
    global $wpdb;

    $wpdb->query("TRUNCATE `marketing`");

    $args = array(
        'post_type'         => array ('shop_order'),
        'order'             => 'ASC',
        'post_status'       => array ('trash', 'wc-admin-cancelled', 'wc-cancelled', 'wc-completed', 'wc-conflict', 'wc-partially-paid', 'wc-pending', 'wc-refunded', 'wc-walletx'),
        'posts_per_page'    => 5000,
        'paged'             => 1,
        'date_query'        => array (
            array (
                'after'     => date('Y-m-d 00:00:00.000000', strtotime('-1 day')),
                'before'    => date('Y-m-d 23:59:59.000000'),
                'inclusive' => true,
            ),
        ),
    );
    $the_query = new WP_Query($args);

    if ($the_query->have_posts()) :
        while ($the_query->have_posts()) : $the_query->the_post();
            $order_id = get_the_ID();

            $order = wc_get_order($order_id);

            $order_date = get_the_date('U');

            $order_time         = date('H:i', $order_date);
            $reserve_day        = wp_date('l', $order_date);
            $order_date_only    = persianToEnglish(jdate('Y/m/d', $order_date));
            $order_date_only_g  = date('Y/m/d', $order_date);

            $order_status = $order->get_status();
            if ( $order_status == 'partially-paid' || $order_status == 'completed' || $order_status == 'walletx'  )
                $order_status = 'موفق';
            else
                $order_status = 'ناموفق';

            $order = wc_get_order($order_id);
            foreach ($order->get_items() as $item) {
                $product_id = $item->get_product_id();
                $quantity   = $item->get_quantity();
            }

            $terms = get_the_terms($product_id, 'product_cat');
            if ( count( $terms ) > 1 ) {
                foreach ( $terms as $term )
                    if ( $term->parent != 0 )
                        $city_name = $term->name;
            }  else
                $city_name = $terms[0]->name;

            $duration   = get_field("room_duration", $product_id);
            $hood       = get_field("room_loc", $product_id);

            $genre1 = $genre2 = $genre3 = $genre4 = null;

            $genres = [];
            foreach (get_the_terms($product_id, 'product_tag') as $product_tag)
                if (str_contains($product_tag->name, '|||||'))
                    $genres[] = str_replace('|||||', '', $product_tag->name);

            foreach ($genres as $index => $genre)
                if ($index < 8)
                    ${'genre' . ($index + 1)} = $genre;

            $row = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT * FROM `wp_zb_booking_history` WHERE `wc_order_id` = $order_id ORDER BY `booking_id` DESC"])), true);
            $row = $row[0];

            $sans_start_time    = !empty($row['booking_time']) ? wp_date('H:i', $row['booking_time']) : 0;
            $sans_start_day     = !empty($row['booking_time']) ? wp_date('l', $row['booking_time']) : 0;

            $players_phone = get_post_meta($order_id, 'players_phone', true);
            $players_phone = !empty($players_phone) ? $players_phone : [];

            foreach ($players_phone as $element)
                if (is_array($element)) {
                    $players_phone = array_column($players_phone, 'phone');
                    break;
                }
            $improved_players_phone = array_values(array_filter(array_map('normalizePhoneNumber', $players_phone), 'isValidIranianMobileNumber'));

            $phone1 = $phone2 = $phone3 = $phone4 = $phone5 = $phone6 = $phone7 = $phone8 = $phone9 = 0;
            foreach ($improved_players_phone as $index => $item)
                if ($index < 10)
                    ${'phone' . ($index + 1)} = $item;

            $utm_source     = get_post_meta($order_id, '_wc_order_attribution_utm_source', true);
            $session_entry  = get_post_meta($order_id, '_wc_order_attribution_session_entry', true);
            $referrer       = get_post_meta($order_id, '_wc_order_attribution_referrer', true);
            $utm_medium     = get_post_meta($order_id, '_wc_order_attribution_utm_medium', true);
            if (strpos($utm_source, 'escapezoom.co') !== false || strpos($session_entry, 'escapezoom.co') !== false || strpos($referrer, 'escapezoom.co') !== false || strpos($utm_medium, 'cpc') !== false)
                $referrer = 'escapezoom.co';
            else
                $referrer = $utm_source;

            $pish_per_person    = get_post_meta( $order_id, 'ticket_tedad', true );
            $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : get_post_meta( $product_id, 'pish_pardakht_per_person', true );
            $pish_per_person    = !empty( $pish_per_person ) ? $pish_per_person : 1;

            $pish_final = get_post_meta($order_id, "_order_total_2", true) ? : get_post_meta($order_id, "_order_total", true);

            $item_total = (int)$pish_final / (int)$pish_per_person * (int)$quantity;

            $commission = 10;
            if (get_post_meta($product_id, "darsad", true))
                $commission = get_post_meta($product_id, "darsad", true);

            $net_profit = $item_total * ($commission / 100);

            $tax_free = [2762, 21755, 353952, 87471, 145024];
            if ( in_array($product_id, $tax_free) )
                $net_profit /= 1.1;

            $wpdb->query("INSERT INTO marketing
                        (order_id, reserve_date, reserve_date_g, reserve_time, reserve_day, status, quantity, city, hood, sans_start_time, sans_start_day, genre1, genre2, genre3, genre4, duration,
                         paid, net_profit, referrer, main_phone, phone1, phone2, phone3, phone4, phone5, phone6, phone7, phone8)
                VALUES ('{$order_id}','{$order_date_only}', '{$order_date_only_g}', '{$order_time}', '{$reserve_day}', '{$order_status}', '{$quantity}', '{$city_name}',
                        '{$hood}', '{$sans_start_time}', '{$sans_start_day}', '{$genre1}', '{$genre2}', '{$genre3}', '{$genre4}', '{$duration}', '{$pish}', '{$net_profit}', '{$referrer}', '{$phone1}', '{$phone2}',
                        '{$phone3}', '{$phone4}', '{$phone5}', '{$phone6}', '{$phone7}', '{$phone8}', '{$phone9}');");

        endwhile;
        wp_reset_postdata();
    endif;
}
