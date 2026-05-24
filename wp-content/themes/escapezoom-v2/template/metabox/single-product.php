<?php
add_action( 'cmb2_admin_init', 'ez_add_product_custom_metabox' );
function ez_add_product_custom_metabox() {
    $prefix = '_ez_product_';

    $cmb = new_cmb2_box( array(
        'id'            => $prefix . 'rank_metabox',
        'title'         => __( 'رتبه‌بندی سالیانه محصول', 'escapezoom' ),
        'object_types'  => array( 'product' ), // نوع پست محصول
    ) );

    $group_field_id = $cmb->add_field( array(
        'id'          => $prefix . 'yearly_ranks',
        'type'        => 'group',
        'description' => __( 'افزودن ردیف جدید برای هر سال', 'escapezoom' ),
        'options'     => array(
            'group_title'   => __( 'ردیف {#}', 'escapezoom' ),
            'add_button'    => __( 'افزودن ردیف جدید', 'escapezoom' ),
            'remove_button' => __( 'حذف', 'escapezoom' ),
            'sortable'      => true,
        ),
    ) );

    // تاریخ (سال شمسی)
    $cmb->add_group_field( $group_field_id, array(
        'name'       => __( 'سال', 'escapezoom' ),
        'id'         => 'year',
        'type'       => 'text_year_shamsi',
        'attributes' => array(
            'placeholder' => 'مثال: 1402',
            'pattern'     => '\d{4}',
            'style'       => 'width:100px;',
        ),
        'desc'       => 'سال را به صورت شمسی وارد کنید (مثال: 1402)',
    ) );

    // رتبه (1 تا 3)
    $cmb->add_group_field( $group_field_id, array(
        'name'       => __( 'رتبه', 'escapezoom' ),
        'id'         => 'rank',
        'type'       => 'select',
        'options'    => array(
            ''  => 'انتخاب کنید', // گزینه پیش‌فرض خالی
            '1' => '1',
            '2' => '2',
            '3' => '3',
        ),
        'desc'       => 'عدد بین 1 تا 3 (انتخاب این فیلد الزامی است)',
        'attributes' => array(
            'style'    => 'width:60px;',
            
        ),
        'sanitization_cb' => function( $value, $field_args, $field ) {
            // فقط اگر مقدار معتبر بود ذخیره شود، در غیر این صورت مقدار خالی ذخیره نشود
            $allowed = array( '1', '2', '3' );
            if ( in_array( $value, $allowed, true ) ) {
                return $value;
            }
            return '';
        },
        'escape_cb' => 'esc_attr',
        'save_field' => true, // فقط در صورت انتخاب ذخیره شود
    ) );

    // متن توضیحی
    $cmb->add_group_field( $group_field_id, array(
        'name' => __( 'توضیحات', 'escapezoom' ),
        'id'   => 'desc',
        'type' => 'text',
        'attributes' => array(
            'placeholder' => 'توضیحات مربوط به این سال و رتبه',
        ),
    ) );
}

// فیلد سفارشی برای سال شمسی (در صورت نیاز به تقویم شمسی پیشرفته‌تر باید افزونه یا جاوااسکریپت اضافه شود)
add_filter( 'cmb2_render_text_year_shamsi', 'ez_render_text_year_shamsi', 10, 5 );
function ez_render_text_year_shamsi( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {
    ?>
    <input type="text" class="cmb2-text-small" name="<?php echo $field_type_object->_name(); ?>" id="<?php echo $field_type_object->_id(); ?>" value="<?php echo esc_attr( $escaped_value ); ?>" pattern="\d{4}" maxlength="4" placeholder="مثال: 1402" style="width:100px;" />
    <?php
}
