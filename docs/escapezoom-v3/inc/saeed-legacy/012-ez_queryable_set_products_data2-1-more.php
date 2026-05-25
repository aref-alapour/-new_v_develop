<?php
/**
 * ez_queryable_set_products_data2 (+1 more)
 *
 * توابع: ez_queryable_set_products_data2, ez_queryable_set_products_data هوک‌ها: woocommerce_after_register_post_type
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 488-627)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function ez_queryable_set_products_data2() {
    add_action('woocommerce_after_register_post_type', 'ez_queryable_set_products_data');
}
/*===============================*/
function ez_queryable_set_products_data() {

    $ez_home = get_option('ez_home');
//    $nuwruz_items = explode(',', $ez_home['nuwruz_items']);

    $args = array(
        'post_type'      => 'product',
        'post_status'    => 'publish',
        //        'post__not_in'      => array(689477),
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => 'product_state',
                'value'   => 'active',
                'compare' => '==',
            ),
            array(
                'key'     => 'product_state',
                'value'   => 'updated',
                'compare' => '==',
            ),
        ),
    );
    $query = new WP_Query($args);
    while ($query->have_posts()) : $query->the_post();
        global $product;

        $id = get_the_ID();

        $min_price  = get_post_meta($id, 'min_price', TRUE);
        $owner_id   = get_post_meta($id, 'user_ebtal', true);
        $manager_id = get_post_meta($id, 'sans_manager', true );

        if ( get_post_meta($id, 'special_discount_enable', true) )
            $discount_data = [
                'special_discount_percentage'   => get_post_meta($id, 'special_discount_percentage', true),
                'special_discount_date'         => get_post_meta($id, 'special_discount_date', true),
            ];
        else
            $discount_data = '';

        $trimmed_url    = trim(wp_get_attachment_image_url( $product->get_image_id(), 'post-thumbnail'), "https://escapezoom.ir/wp-content/uploads/");
//        $image          = wp_upload_dir()['basedir'] . '/' . $trimmed_url;

        $terms = get_the_terms($id, 'product_cat');
        if ( count( $terms ) > 1 ) {

            foreach ( $terms as $term ) {
                if ( $term->parent == 0 )
                    $product_type = $term->name;

                else {
                    $city_name  = $term->name;
                    $city_id    = $term->term_id;
                }
            }

        } else {
            $product_type   = get_term($terms[0]->parent)->name;
            $city_name      = $terms[0]->name;
            $city_id        = $terms[0]->term_id;
        }

        $user_info  = get_userdata($owner_id);
        $contact_info = [
            'owner_phone'       => get_userdata($owner_id)->user_login,
            'chat_id'           => get_user_meta($owner_id, 'chat_id', true),
            'manager_phone'     => get_userdata($manager_id)->user_login,
            'manager_chat_id'   => get_user_meta($manager_id, 'chat_id', true),
        ];

        $product_rates  = get_post_meta($id, 'clone_product_rates', true);
        $comments_count = get_post_meta($id, 'clone_comments_count_new', true);

        $decor    = (int)$comments_count !== 0 ? $product_rates[1094] / $comments_count / 20 : 0;
        $moaama   = (int)$comments_count !== 0 ? $product_rates[1095] / $comments_count / 20 : 0;
        $tazegi   = (int)$comments_count !== 0 ? $product_rates[1098] / $comments_count / 20 : 0;
        $act      = (int)$comments_count !== 0 ? $product_rates[1096] / $comments_count / 20 : 0;
        $barkhord = (int)$comments_count !== 0 ? $product_rates[1097] / $comments_count / 20 : 0;

        $temp = new stdClass();

        $temp->id               = $id;
        $temp->type             = $product_type;
        $temp->title            = get_the_title();
        $temp->price            = !empty( $min_price ) ? $min_price : get_field("price_asli", $id);
        $temp->notable          = 0;
        $temp->special          = get_field("special_room", $id) ? 1 : 0;
        $temp->active           = get_field('product_state', $id);
        $temp->monopoly         = get_post_meta($id, 'monopoly', true) ? 1 : 0;
        $temp->brand_id         = get_post_meta($id, 'product_brand', true);
        $temp->discount_data    = $discount_data;
        $temp->instant_off      = get_post_meta($id, 'instant_off', true);
        $temp->geo              = get_field('room_lat', $id) . ',' . get_field('room_long', $id);
        $temp->image            = $trimmed_url;
        $temp->age_limit        = get_field("room_age_limit", $id);
        $temp->level            = get_field("room_level", $id);
        $temp->schedule         = ['normals' => get_post_meta($id, 'schedule_normals', true), 'holidays' => get_post_meta($id, 'schedule_holidays', true)];
        $temp->duration         = get_field("room_duration", $id);
        $temp->url              = trim(urldecode(get_permalink()), "https://escapezoom.ir/room/");
        if ( $_SERVER['HTTP_HOST'] == 'dev.escapezoom.local' )
            $temp->url              = trim(urldecode(get_permalink()), 'http://' . $_SERVER['HTTP_HOST'] . '/room');
        $temp->hood             = get_field("room_loc", $id);
        $temp->city_id          = $city_id;
        $temp->city_name        = $city_name;
        $temp->auto_disable     = get_post_meta($id, 'auto_disable', true);
        $temp->pish_person      = get_post_meta($id, 'pish_pardakht_per_person', true);
        $temp->contact_info     = $contact_info;
        $temp->owner_phone      = $user_info->user_login;
        $temp->chat_id          = get_user_meta($owner_id, 'chat_id', true);
        $temp->owner_id         = $owner_id;
        $temp->manager_id       = $manager_id;
        $temp->comments_count   = $comments_count;
        $temp->rate             = round(($decor + $moaama + $tazegi + $act + $barkhord) / 5, 2);

        preg_match_all('/\d+/', get_field("room_tedad", $id), $matches); // get numbers from string
        if ( !empty( $matches[0] ) ) {
            $temp->count_min    = min($matches[0]);
            $temp->count_max    = max($matches[0]);
        }

        foreach (get_the_terms($id, 'product_tag') as $product_tag) {
            $temp->tags_id[]    = $product_tag->term_id;
            $temp->tags_title[] = $product_tag->name;
        }

        $product_data[] = $temp;

    endwhile;
    wp_reset_postdata();



    $response = ez_webservice( array('type' => 'data_products_set', 'data' => $product_data) );
}
