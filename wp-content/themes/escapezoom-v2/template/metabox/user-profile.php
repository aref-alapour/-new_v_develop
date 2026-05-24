<?php
/**
 * CMB2: فیلدهای اضافه روی پروفایل کاربر در ادمین (user meta).
 */

add_action('cmb2_admin_init', function () {
    $cmb_user = new_cmb2_box([
        'id'               => 'ez_user_profile_extra',
        'title'            => 'اسکیپ زوم — اطلاعات تکمیلی',
        'object_types'     => ['user'],
        'show_names'       => true,
        'new_user_section' => 'add-new-user',
    ]);

    $cmb_user->add_field([
        'name'       => 'تعداد بازی انجام‌شده (قبل از سیستم فعلی)',
        'desc'       => 'عددی که اینجا وارد می‌کنید به تعداد بازی‌های ثبت‌شده در سایت اضافه می‌شود (برای نمایش در پروفایل عمومی).',
        'id'         => 'ez_previous_played_games',
        'type'       => 'text_small',
        'attributes' => [
            'type'  => 'number',
            'min'   => '0',
            'step'  => '1',
            'style' => 'max-width:120px;',
        ],
        'default'    => '0',
        'sanitize_cb'=> function ($value) {
            return (string) max(0, absint($value));
        },
    ]);
});
