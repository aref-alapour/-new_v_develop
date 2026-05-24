<?php
/**
 * product_videos_metabox (+2 more)
 *
 * توابع: product_videos_metabox, product_videos_metabox_frontend, save_product_videos_metabox_data هوک‌ها: add_meta_boxes, save_post_product
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6122-6259)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('add_meta_boxes', 'product_videos_metabox');
function product_videos_metabox() {
    add_meta_box(
        'product_videos_metabox',
        'ویدئوها',
        'product_videos_metabox_frontend',
        'product',
        'side',
        'high'
    );
}
/*-----------------------------------------------------------------------*/
function product_videos_metabox_frontend($post) {
    $introduction   = get_post_meta($post->ID, 'introduction', true);
    $teaser         = get_post_meta($post->ID, 'teaser', true);

    if (!$introduction) $introduction = [];
    if (!$teaser) $teaser = [];

    wp_nonce_field('product_videos_metabox_action', 'product_videos_metabox'); ?>

    <div id="product_videos_metabox">
        <h3>ویدئو معرفی</h3>
        <input type="text" name="introduction[title]" value="<?php echo esc_attr($introduction['title'] ?? ''); ?>" placeholder="عنوان" />
        <input type="text" name="introduction[video_id]" value="<?php echo esc_attr($introduction['video_id'] ?? ''); ?>" placeholder="آی دی ویدئو" />
        <br /><hr>

        <h3>ویدئو تیزر</h3>
        <input type="text" name="teaser[title]" value="<?php echo esc_attr($teaser['title'] ?? ''); ?>" placeholder="عنوان" />
        <input type="text" name="teaser[video_id]" value="<?php echo esc_attr($teaser['video_id'] ?? ''); ?>" placeholder="آی دی ویدئو" />
        <br />
    </div>

    <?php
}
/*-----------------------------------------------------------------------*/
add_action('save_post_product', 'save_product_videos_metabox_data');
function save_product_videos_metabox_data($post_id) {
    global $wpdb;

    if (!isset($_POST['product_videos_metabox']) || !wp_verify_nonce($_POST['product_videos_metabox'], 'product_videos_metabox_action')) return;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    $terms = get_the_terms($post_id, 'product_cat');
    if ( count( $terms ) > 1 ) {
        foreach ( $terms as $term )
            if ( $term->parent == 0 )
                $product_type = $term->name;
    } else
        $product_type = get_term($terms[0]->parent)->name;

/**
 * POST: introduction
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: $wpdb
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/081-product_videos_metabox-2-more.php:66
 */
    if (isset($_POST['introduction'])) {
        update_post_meta($post_id, 'introduction', $_POST['introduction']);

        $video_id       = $_POST['introduction']['video_id'];
        $video_title    = $_POST['introduction']['title'];
        $video_tag      = 'ویدئو معرفی ' . $product_type;
        $created_at     = time();

        if ( !empty($video_title) || !empty($video_id) ) :

            $existing_record = $wpdb->get_row($wpdb->prepare("SELECT video_id, video_title FROM escapezoom_videos WHERE video_tag = %s AND post_id = %d", $video_tag, $post_id));
            if ($existing_record) {
                if ($existing_record->video_id !== $video_id || $existing_record->video_title !== $video_title) {
                    $wpdb->update(
                        'escapezoom_videos',
                        [
                            'video_id'    => $video_id,
                            'video_title' => $video_title,
                            'created_at'  => $created_at,
                        ],
                        [
                            'video_tag' => $video_tag,
                            'post_id'   => $post_id,
                        ]
                    );
                }

            } else {
                $wpdb->insert(
                    'escapezoom_videos',
                    [
                        'post_id'     => $post_id,
                        'video_id'    => $video_id,
                        'video_title' => $video_title,
                        'video_tag'   => $video_tag,
                        'created_at'  => $created_at,
                    ]
                );
            }

        endif;
    }

/**
 * POST: teaser
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: $wpdb
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/081-product_videos_metabox-2-more.php:109
 */
    if (isset($_POST['teaser'])) {
        update_post_meta($post_id, 'teaser', $_POST['teaser']);

        $video_id       = $_POST['teaser']['video_id'];
        $video_title    = $_POST['teaser']['title'];
        $video_tag      = 'تیزر ' . $product_type;
        $created_at     = time();

        if ( !empty($video_title) || !empty($video_id) ) :

            $existing_record = $wpdb->get_row($wpdb->prepare("SELECT video_id, video_title FROM escapezoom_videos WHERE video_tag = %s AND post_id = %d", $video_tag, $post_id));
            if ($existing_record) {
                if ($existing_record->video_id !== $video_id || $existing_record->video_title !== $video_title) {
                    $wpdb->update(
                        'escapezoom_videos',
                        [
                            'video_id'    => $video_id,
                            'video_title' => $video_title,
                            'created_at'  => $created_at,
                        ],
                        [
                            'video_tag' => $video_tag,
                            'post_id'   => $post_id,
                        ]
                    );
                }

            } else {
                $wpdb->insert(
                    'escapezoom_videos',
                    [
                        'post_id'     => $post_id,
                        'video_id'    => $video_id,
                        'video_title' => $video_title,
                        'video_tag'   => $video_tag,
                        'created_at'  => $created_at,
                    ]
                );
            }

        endif;
    }
}
