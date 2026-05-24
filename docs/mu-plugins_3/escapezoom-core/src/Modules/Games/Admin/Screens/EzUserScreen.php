<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use EscapeZoom\Core\Modules\Games\Models\EzUser;

final class EzUserScreen extends BaseCrudScreen
{
    protected static function getPageSlug(): string
    {
        return 'escapezoom-ez-users';
    }

    protected static function getNonceAction(): string
    {
        return 'ez_save_ez_user';
    }

    protected static function getNonceDelete(): string
    {
        return 'ez_delete_ez_user';
    }

    /** @inheritdoc */
    protected static function getModelClass(): string
    {
        return EzUser::class;
    }

    protected static function getListTitle(): string
    {
        return __('کاربران EZ', 'escapezoom-core');
    }

    protected static function getListColumns(): array
    {
        return [
            'display_name'  => __('نام نمایشی', 'escapezoom-core'),
            'phone'         => __('تلفن', 'escapezoom-core'),
            'internal_role' => __('نقش', 'escapezoom-core'),
            'wp_user_id'    => __('کاربر وردپرس', 'escapezoom-core'),
        ];
    }

    protected static function getFormFields(): array
    {
        return [
            'display_name'  => ['label' => __('نام نمایشی', 'escapezoom-core'), 'type' => 'text', 'required' => true],
            'phone'         => ['label' => __('تلفن', 'escapezoom-core'), 'type' => 'text'],
            'internal_role' => [
                'label'   => __('نقش داخلی', 'escapezoom-core'),
                'type'    => 'select',
                'options' => [
                    'customer' => __('مشتری', 'escapezoom-core'),
                    'owner'    => __('مالک', 'escapezoom-core'),
                    'manager'  => __('مدیر', 'escapezoom-core'),
                ],
            ],
            'wp_user_id'    => ['label' => __('شناسه کاربر وردپرس', 'escapezoom-core'), 'type' => 'number'],
            'first_name'    => ['label' => __('نام', 'escapezoom-core'), 'type' => 'text'],
            'last_name'     => ['label' => __('نام خانوادگی', 'escapezoom-core'), 'type' => 'text'],
            'national_id'   => ['label' => __('کد ملی', 'escapezoom-core'), 'type' => 'text'],
            'iban'          => ['label' => __('شبا', 'escapezoom-core'), 'type' => 'text'],
            'avatar_id'     => ['label' => __('شناسه آواتار', 'escapezoom-core'), 'type' => 'number'],
            'level'         => ['label' => __('سطح', 'escapezoom-core'), 'type' => 'number'],
            'points_total'  => ['label' => __('امتیاز کل', 'escapezoom-core'), 'type' => 'number'],
            'orders_count'  => ['label' => __('تعداد سفارش', 'escapezoom-core'), 'type' => 'number'],
            'status'        => ['label' => __('وضعیت', 'escapezoom-core'), 'type' => 'text'],
            'birth_date'    => ['label' => __('تاریخ تولد', 'escapezoom-core'), 'type' => 'text'],
            'locations_cache' => ['label' => __('کش موقعیت‌ها (JSON)', 'escapezoom-core'), 'type' => 'textarea'],
        ];
    }

    protected static function getOrderBy(): array
    {
        return ['id' => 'asc'];
    }

    protected static function getAddTitle(): string
    {
        return __('افزودن کاربر EZ', 'escapezoom-core');
    }

    protected static function getEditTitle(): string
    {
        return __('ویرایش کاربر EZ', 'escapezoom-core');
    }
}
