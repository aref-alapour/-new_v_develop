<?php
/**
 * display_city_page_product_categories_meta_box
 *
 * توابع: display_city_page_product_categories_meta_box
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6495-6545)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function display_city_page_product_categories_meta_box($post) {
    $selected_categories = get_post_meta($post->ID, 'city_page_product_categories', true);
    $selected_categories = !empty($selected_categories) ? (array) $selected_categories : [];

    $assign_as_city_page = get_post_meta($post->ID, 'assign_as_city_page', true);

    $categories = get_terms([
        'taxonomy'   => 'product_cat',
        'hide_empty' => false,
    ]); ?>

    <label for="assign_as_city_page" style="margin: 20px 0;display: inline-block;cursor:pointer;">
        <input type="checkbox" id="assign_as_city_page" name="assign_as_city_page" value="1" <?php checked($assign_as_city_page, '1'); ?>>
        اختصاص به عنوان صفحه شهر
    </label>

    <select name="city_page_product_categories[]" multiple="multiple" style="width:100%; height: 150px;" class="city_page_product_categories">
        <?php foreach ($categories as $category):
            $selected = in_array($category->term_id, $selected_categories) ? 'selected="selected"' : ''; ?>
            <option value="<?php echo esc_attr($category->term_id); ?>" <?php echo $selected; ?>><?php echo $category->name . ' (' . get_parent_category_name_by_child_id($category->term_id) . ')'; ?></option>
        <?php endforeach; ?>
    </select>

    <?php
    if ( !$assign_as_city_page ) : ?>
        <style>
            .city_page_product_categories + .select2 {
                display: none;
            }
        </style>
    <?php
    endif; ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('.city_page_product_categories').select2({
                placeholder: "انتخاب کنید...",
                allowClear: true
            });

            $('body').on('change', '#assign_as_city_page', function(){
                if($(this).is(':checked'))
                    $('.city_page_product_categories').siblings('.select2').show();
                else
                    $('.city_page_product_categories').siblings('.select2').hide();
            });

        });
    </script>
    <?php
}
