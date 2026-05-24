<?php
/**
 * CMB2 Metabox for WooCommerce Product Categories - FAQ Section
 * این فایل متاباکس CMB2 را برای دسته‌بندی‌های محصولات ووکامرس ایجاد می‌کند
 * که امکان افزودن سوالات متداول (FAQ) به صورت تکرار شونده را فراهم می‌کند
 */

add_action('cmb2_admin_init', 'ez_add_product_category_faq_metabox');

function ez_add_product_category_faq_metabox()
{
    $prefix = '_ez_product_cat_';

    // ایجاد CMB2 box برای taxonomy
    $cmb_term = new_cmb2_box(array(
        'id'               => $prefix . 'faq_metabox',
        'title'            => __('سوالات متداول', 'escapezoom'),
        'object_types'     => array('term'), // برای taxonomy
        'taxonomies'       => array('product_cat'), // فقط برای دسته‌بندی‌های محصول
        'new_term_section' => true, // نمایش در صفحه افزودن دسته‌بندی جدید
    ));

    // فیلد گروهی تکرار شونده برای سوالات متداول
    $group_field_id = $cmb_term->add_field(array(
        'id'          => $prefix . 'faqs',
        'type'        => 'group',
        'description' => __('افزودن سوالات متداول برای این دسته‌بندی', 'escapezoom'),
        'options'     => array(
            'group_title'   => __('سوال {#}', 'escapezoom'),
            'add_button'    => __('افزودن سوال جدید', 'escapezoom'),
            'remove_button' => __('حذف سوال', 'escapezoom'),
            'sortable'      => true, // امکان مرتب‌سازی
        ),
    ));

    // فیلد عنوان سوال
    $cmb_term->add_group_field($group_field_id, array(
        'name'       => __('عنوان سوال', 'escapezoom'),
        'id'         => 'question',
        'type'       => 'text',
        'attributes' => array(
            'placeholder' => 'مثال: سوال شما چیست؟',
        ),
    ));

    // فیلد پاسخ
    $cmb_term->add_group_field($group_field_id, array(
        'name'       => __('پاسخ', 'escapezoom'),
        'id'         => 'answer',
        'type'       => 'textarea',
        'attributes' => array(
            'placeholder' => 'پاسخ سوال را اینجا بنویسید...',
            'rows'        => 4,
        ),
    ));
      // ایجاد CMB2 box برای توضیحات لیست همه
    $cmb_term_list_desc = new_cmb2_box(array(
        'id'               => $prefix . 'list_description_metabox',
        'title'            => __('توضیحات لیست همه', 'escapezoom'),
        'object_types'     => array('term'), // برای taxonomy
        'taxonomies'       => array('product_cat'), // فقط برای دسته‌بندی‌های محصول
        'new_term_section' => true, // نمایش در صفحه افزودن دسته‌بندی جدید
    ));

    // فیلد textarea برای توضیحات لیست همه
    $cmb_term_list_desc->add_field(array(
        'name'       => __('توضیحات لیست همه', 'escapezoom'),
        'desc'       => __('توضیح کوتاه برای لیست همه (مثلا: سینما ترس های تهران)', 'escapezoom'),
        'id'         => $prefix . 'list_description',
        'type'       => 'textarea',
        'attributes' => array(
            'placeholder' => 'مثال: بهترین سینما ترس های تهران را در اینجا مشاهده کنید...',
            'rows'        => 4,
        ),
    ));
}

