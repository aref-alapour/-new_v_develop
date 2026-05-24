<?php
/**
 * monopoly_metabox (+2 more)
 *
 * توابع: monopoly_metabox, monopoly_metabox_frontend, save_monopoly_metabox_data هوک‌ها: add_meta_boxes, save_post_product
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6260-6293)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('add_meta_boxes', 'monopoly_metabox');
function monopoly_metabox() {
    add_meta_box(
        'monopoly_metabox',
        'زوم کلاب',
        'monopoly_metabox_frontend',
        'product',
        'side',
        'high'
    );
}
/*-----------------------------------------------------------------------*/
function monopoly_metabox_frontend($post) {
    $monopoly = get_post_meta($post->ID, 'monopoly', true);

    wp_nonce_field('monopoly_metabox_action', 'monopoly_metabox'); ?>

    <div id="monopoly_metabox">
        <h3>زوم کلاب؟</h3>
        <input type="checkbox" name="monopoly_product" value="1" <?php echo checked( 1, $monopoly, false ) ?>>
    </div>

    <?php
}
/*-----------------------------------------------------------------------*/
add_action('save_post_product', 'save_monopoly_metabox_data');
function save_monopoly_metabox_data($post_id) {

    if (!isset($_POST['monopoly_metabox']) || !wp_verify_nonce($_POST['monopoly_metabox'], 'monopoly_metabox_action')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    update_post_meta($post_id, 'monopoly', isset($_POST['monopoly_product']) ? 1 : 0);
}

