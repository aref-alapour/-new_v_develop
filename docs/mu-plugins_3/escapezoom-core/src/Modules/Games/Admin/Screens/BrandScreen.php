<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use EscapeZoom\Core\Modules\Games\Models\Brand;

final class BrandScreen extends BaseCrudScreen
{
    protected static function getPageSlug(): string
    {
        return 'escapezoom-brands';
    }

    protected static function getNonceAction(): string
    {
        return 'ez_save_brand';
    }

    protected static function getNonceDelete(): string
    {
        return 'ez_delete_brand';
    }

    /** @inheritdoc */
    protected static function getModelClass(): string
    {
        return Brand::class;
    }

    protected static function getListTitle(): string
    {
        return __('برندها', 'escapezoom-core');
    }

    protected static function getListColumns(): array
    {
        return [
            'title'      => __('عنوان', 'escapezoom-core'),
            'slug'       => 'Slug',
            'address'    => __('آدرس', 'escapezoom-core'),
            'score'      => __('امتیاز', 'escapezoom-core'),
            'reputation' => __('اعتبار', 'escapezoom-core'),
        ];
    }

    protected static function getFormFields(): array
    {
        return [
            'title'       => ['label' => __('عنوان', 'escapezoom-core'), 'type' => 'text', 'required' => true],
            'slug'        => ['label' => 'Slug', 'type' => 'text'],
            'logo'         => ['label' => __('لوگو (URL یا متن)', 'escapezoom-core'), 'type' => 'text'],
            'description'  => ['label' => __('توضیحات', 'escapezoom-core'), 'type' => 'editor'],
            'thumbnail_id' => ['label' => __('تصویر شاخص (شناسه رسانه)', 'escapezoom-core'), 'type' => 'number'],
            'address'      => ['label' => __('آدرس', 'escapezoom-core'), 'type' => 'text'],
            'score'       => ['label' => __('امتیاز', 'escapezoom-core'), 'type' => 'number'],
            'reputation'  => ['label' => __('اعتبار', 'escapezoom-core'), 'type' => 'number'],
            'game_types'  => ['label' => __('انواع بازی (JSON)', 'escapezoom-core'), 'type' => 'textarea'],
            'teams'       => ['label' => __('تیم‌ها (JSON)', 'escapezoom-core'), 'type' => 'textarea'],
        ];
    }

    protected static function getOrderBy(): array
    {
        return ['id' => 'asc'];
    }

    protected static function getAddTitle(): string
    {
        return __('افزودن برند', 'escapezoom-core');
    }

    protected static function getEditTitle(): string
    {
        return __('ویرایش برند', 'escapezoom-core');
    }

    /** @inheritdoc — دیکد JSON برای game_types/teams؛ ساخت slug از عنوان اگر خالی باشد؛ جلوگیری از slug خالی در ایجاد. */
    protected static function gatherFormData(): array
    {
        $data = parent::gatherFormData();
        $data['game_types'] = self::decodeJsonField($data['game_types'] ?? '');
        $data['teams']     = self::decodeJsonField($data['teams'] ?? '');
        if (isset($data['title']) && (string) ($data['slug'] ?? '') === '' && (string) $data['title'] !== '') {
            $data['slug'] = sanitize_title((string) $data['title']);
        }
        if ((string) ($data['slug'] ?? '') === '') {
            $data['slug'] = sanitize_title('brand-' . (string) time());
        }
        return $data;
    }

    /** @return array|null آرایه یا null برای ذخیره در ستون JSON. */
    private static function decodeJsonField(mixed $value): ?array
    {
        if (is_array($value)) {
            return $value;
        }
        $s = is_string($value) ? trim($value) : '';
        if ($s === '') {
            return null;
        }
        $decoded = json_decode($s, true);
        return is_array($decoded) ? $decoded : null;
    }
}
