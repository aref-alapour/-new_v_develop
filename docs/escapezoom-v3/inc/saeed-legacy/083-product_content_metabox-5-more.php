<?php
/**
 * product_content_metabox (+5 more)
 *
 * توابع: product_content_metabox, product_introduction_text_metabox_frontend, product_scenario_metabox_frontend, product_rules_metabox_frontend, product_introduction_video_metabox_frontend, save_product_content_metabox_data هوک‌ها: add_meta_boxes, save_post_product
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6294-6349)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('add_meta_boxes', 'product_content_metabox');
function product_content_metabox() {
    add_meta_box('product_introduction_text_metabox', 'چکیده معرفی', 'product_introduction_text_metabox_frontend', 'product', 'normal', 'low');
    add_meta_box('product_scenario_metabox', 'سناریو', 'product_scenario_metabox_frontend', 'product', 'normal', 'low');
    add_meta_box('product_rules_metabox','قوانین','product_rules_metabox_frontend','product','normal','low');
    add_meta_box('product_introduction_video_metabox', 'ویدیو معرفی', 'product_introduction_video_metabox_frontend', 'product', 'normal', 'low');
}
/*-----------------------------------------------------------------------*/
function product_introduction_text_metabox_frontend($post) {
    $product_introduction_text = get_post_meta($post->ID, 'product_introduction_text', true);

    wp_editor($product_introduction_text, 'product_introduction_text_editor', array(
        'textarea_name' => 'product_introduction_text',
        'media_buttons' => true,
        'textarea_rows' => 5,
    ));
}
/*-----------------------------------------------------------------------*/
function product_scenario_metabox_frontend($post) {
    $product_scenario = get_post_meta($post->ID, 'product_scenario', true);

    wp_editor($product_scenario, 'product_scenario_editor', array(
        'textarea_name' => 'product_scenario',
        'media_buttons' => true,
        'textarea_rows' => 5,
    ));
}
/*-----------------------------------------------------------------------*/
function product_rules_metabox_frontend($post) {
    $product_rules = get_post_meta($post->ID, 'product_rules', true);

    wp_editor($product_rules, 'product_rules_editor', array(
        'textarea_name' => 'product_rules',
        'media_buttons' => true,
        'textarea_rows' => 5,
    ));
}
/*-----------------------------------------------------------------------*/
function product_introduction_video_metabox_frontend($post) {
    $product_introduction_video = get_post_meta($post->ID, 'product_introduction_video', true);

    wp_editor($product_introduction_video, 'product_introduction_video_editor', array(
        'textarea_name' => 'product_introduction_video',
        'media_buttons' => false,
        'textarea_rows' => 5,
    ));
}
/*-----------------------------------------------------------------------*/
add_action('save_post_product', 'save_product_content_metabox_data');
function save_product_content_metabox_data($post_id) {

    update_post_meta($post_id, 'product_introduction_text', $_POST['product_introduction_text']);
    update_post_meta($post_id, 'product_scenario', $_POST['product_scenario']);
    update_post_meta($post_id, 'product_rules', $_POST['product_rules']);
    update_post_meta($post_id, 'product_introduction_video', $_POST['product_introduction_video']);
}
