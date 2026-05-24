<?php
// اضافه کردن فیلد به فرم ایجاد برچسب جدید
add_action('product_tag_add_form_fields', 'custom_product_tag_image_field_add', 10, 2);
function custom_product_tag_image_field_add($taxonomy) {
    ?>
    <div class="form-field term-group">
        <label for="tag-image-id"><?php _e('تصویر برچسب', 'textdomain'); ?></label>
        <input type="hidden" id="tag-image-id" name="tag-image-id" value="">
        <div id="tag-image-wrapper"></div>
        <p>
            <input type="button" class="button button-secondary tag_media_button" id="tag_media_button" value="<?php _e('انتخاب تصویر', 'textdomain'); ?>" />
            <input type="button" class="button button-secondary tag_media_remove" id="tag_media_remove" value="<?php _e('حذف تصویر', 'textdomain'); ?>" />
        </p>
    </div>
    <?php
}

// اضافه کردن فیلد به فرم ویرایش برچسب
add_action('product_tag_edit_form_fields', 'custom_product_tag_image_field_edit', 10, 2);
function custom_product_tag_image_field_edit($term, $taxonomy) {
    $image_id = get_term_meta($term->term_id, 'tag-image-id', true);
    ?>
    <tr class="form-field term-group-wrap">
        <th scope="row"><label for="tag-image-id"><?php _e('تصویر برچسب', 'textdomain'); ?></label></th>
        <td>
            <input type="hidden" id="tag-image-id" name="tag-image-id" value="<?php echo esc_attr($image_id); ?>">
            <div id="tag-image-wrapper">
                <?php if ($image_id) { echo wp_get_attachment_image($image_id, 'thumbnail'); } ?>
            </div>
            <p>
                <input type="button" class="button button-secondary tag_media_button" id="tag_media_button" value="<?php _e('انتخاب تصویر', 'textdomain'); ?>" />
                <input type="button" class="button button-secondary tag_media_remove" id="tag_media_remove" value="<?php _e('حذف تصویر', 'textdomain'); ?>" />
            </p>
        </td>
    </tr>
    <?php
}

// ذخیره متای ترم هنگام ایجاد یا ویرایش
add_action('created_product_tag', 'save_custom_product_tag_image', 10, 2);
add_action('edited_product_tag', 'save_custom_product_tag_image', 10, 2);
function save_custom_product_tag_image($term_id, $tt_id) {
    if (isset($_POST['tag-image-id']) && '' !== $_POST['tag-image-id']) {
        update_term_meta($term_id, 'tag-image-id', absint($_POST['tag-image-id']));
    } else {
        update_term_meta($term_id, 'tag-image-id', '');
    }
}

// لود اسکریپت‌های آپلودر
add_action('admin_enqueue_scripts', 'custom_product_tag_admin_scripts');
function custom_product_tag_admin_scripts($hook) {
    if ('edit-tags.php' === $hook || 'term.php' === $hook) {
        if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'product_tag') {
            wp_enqueue_media();
            wp_enqueue_script('custom-tag-media', get_stylesheet_directory_uri() . '/js/custom-tag-media.js', array('jquery'), null, true);
        }
    }
}
