<?php
/**
 * special_discount (+2 more)
 *
 * توابع: special_discount, special_discount_func, special_discount_save_func هوک‌ها: add_meta_boxes, save_post
 *
 * منبع: saeed-codes.php (بازهٔ خطوط 6032-6097)
 * نوع: توابع/هوک‌های دائمی
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action('add_meta_boxes', 'special_discount');
function special_discount() {
    add_meta_box(
        'special_discount',
        'تخفیف ویژه',
        'special_discount_func',
        'product',
        'normal',
        'high'
    );
}
/*============================================================*/
function special_discount_func ($post) {

    $special_discount_enable        = get_post_meta($post->ID, 'special_discount_enable', true);
    $special_discount_percentage    = get_post_meta($post->ID, 'special_discount_percentage', true);
    $special_discount_date          = get_post_meta($post->ID, 'special_discount_date', true); ?>

    <div class="reservation_info_section_wrapper">
        <label style="width: 120px;display: inline-block;">فعال: </label>
        <input type="checkbox" name="special_discount_enable" <?php echo checked( 1, $special_discount_enable, false ); ?> >
    </div>

    <div class="reservation_info_section_wrapper">
        <label style="width: 120px;display: inline-block;">درصد: </label>
        <input type="text" name="special_discount_percentage" style="width: 210px;" value="<?php echo $special_discount_percentage; ?>" >
    </div>

    <div class="reservation_info_section_wrapper">
        <label style="width: 120px;display: inline-block;">تا تاریخ: </label>
        <input type="datetime-local" name="special_discount_date" style="width: 210px;" value="<?php echo $special_discount_date ? date('Y-m-d\TH:i', $special_discount_date) : ''; ?>" >
    </div>

    <?php
}
/*============================================================*/
add_action('save_post', 'special_discount_save_func', 10, 1);
function special_discount_save_func($product_id) {

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $product_id)) return;

/**
 * POST: special_discount_enable
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: ez_webservice
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/076-special_discount-2-more.php:56
 */
    if (isset($_POST['special_discount_enable']))
        update_post_meta($product_id, 'special_discount_enable', 1);
    else
        update_post_meta($product_id, 'special_discount_enable', 0);

/**
 * POST: special_discount_percentage
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: ez_webservice
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/076-special_discount-2-more.php:61
 */
    if (isset($_POST['special_discount_percentage']))
        update_post_meta($product_id, 'special_discount_percentage', sanitize_text_field($_POST['special_discount_percentage']));

/**
 * POST: special_discount_date
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: ez_webservice
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/076-special_discount-2-more.php:64
 */
    if (isset($_POST['special_discount_date']))
        update_post_meta($product_id, 'special_discount_date', sanitize_text_field(strtotime( $_POST['special_discount_date'] )));

/**
 * POST: special_discount_enable
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: ez_webservice
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/076-special_discount-2-more.php:67
 */
    if (isset($_POST['special_discount_enable']))
        $discount_data = [
            'special_discount_percentage'   => $_POST['special_discount_percentage'],
            'special_discount_date'         => strtotime( $_POST['special_discount_date'] ),
        ];
    else
        $discount_data = [];

    ez_webservice( array('type' => 'update_product_discount_data', 'data' => array('product_id' => $product_id, 'discount_data' => $discount_data)) );

/**
 * POST: tax_input
 *
 * هدف: نامشخص — بدنه را بخوانید
 * استفاده: POST
 * وابستگی: —
 * امنیت: بدون احراز هویت
 * وضعیت: در انتظار تایید تیم
 * منبع: saeed-legacy/076-special_discount-2-more.php:77
 */
    if (isset($_POST['tax_input']['product_brand']))
        update_post_meta($product_id, 'product_brand', sanitize_text_field($_POST['tax_input']['product_brand'][1]));
}
