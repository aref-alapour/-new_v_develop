<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use EscapeZoom\Core\Modules\Games\Models\Area;
use EscapeZoom\Core\Modules\Games\Models\Brand;
use EscapeZoom\Core\Modules\Games\Models\City;
use EscapeZoom\Core\Modules\Games\Models\EzUser;
use EscapeZoom\Core\Modules\Games\Models\GameType;
use EscapeZoom\Core\Modules\Games\Models\Genre;
use EscapeZoom\Core\Modules\Games\Models\Mood;
use EscapeZoom\Core\Modules\Games\Models\Product;
use EscapeZoom\Core\Modules\Games\Models\Theme;

/**
 * لیست و فرم افزودن/ویرایش بازی‌ها مستقیم روی ez_products و پایوت‌ها (بدون CPT).
 */
final class GameProductScreen extends BaseScreen
{
    private const PAGE_SLUG   = 'escapezoom-games';
    private const NONCE_SAVE  = 'ez_save_game';
    private const NONCE_DELETE = 'ez_delete_game';
    protected const CAPABILITY = 'manage_options';

    public static function render(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('دسترسی غیرمجاز.', 'escapezoom-core'));
        }
        static::dispatch();
    }

    protected static function dispatch(): void
    {
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ez_game_nonce'])) {
            if (!wp_verify_nonce(sanitize_text_field((string) $_POST['ez_game_nonce']), self::NONCE_SAVE)) {
                wp_die(esc_html__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
            }
            static::handleSave();
            return;
        }

        if ($action === 'delete' && $id > 0 && isset($_GET['_wpnonce'])) {
            if (!wp_verify_nonce(sanitize_text_field((string) $_GET['_wpnonce']), self::NONCE_DELETE . $id)) {
                wp_die(esc_html__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
            }
            Product::query()->where('product_id', $id)->delete();
            wp_safe_redirect(static::listUrl());
            exit;
        }

        if ($action === 'add') {
            static::renderForm(null);
            return;
        }
        if ($action === 'edit' && $id > 0) {
            $item = Product::query()->with(['genres', 'moods', 'themes', 'areas'])->find($id);
            if (!$item) {
                wp_die(esc_html__('بازی یافت نشد.', 'escapezoom-core'));
            }
            static::renderForm($item);
            return;
        }
        static::renderList();
    }

    private static function listUrl(): string
    {
        return admin_url('admin.php?page=' . self::PAGE_SLUG);
    }

    private static function addUrl(): string
    {
        return admin_url('admin.php?page=' . self::PAGE_SLUG . '&action=add');
    }

    private static function editUrl(int $id): string
    {
        return admin_url('admin.php?page=' . self::PAGE_SLUG . '&action=edit&id=' . $id);
    }

    private static function deleteUrl(int $id): string
    {
        return wp_nonce_url(admin_url('admin.php?page=' . self::PAGE_SLUG . '&action=delete&id=' . $id), self::NONCE_DELETE . $id);
    }

    /** مقدار اختیاری FK: خالی یا 0 → null برای جلوگیری از خطای FK. */
    private static function optionalFk(string $postKey): ?int
    {
        if (!isset($_POST[$postKey])) {
            return null;
        }
        $raw = $_POST[$postKey];
        if ($raw === '' || (is_string($raw) && trim($raw) === '')) {
            return null;
        }
        $v = absint($raw);
        return $v === 0 ? null : $v;
    }

    private static function handleSave(): void
    {
        $id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
        $slug = isset($_POST['slug']) ? sanitize_title((string) ($_POST['slug'] ?? '')) : '';
        if ($slug === '') {
            $slug = 'game-' . ($id ?: time());
        }

        $areaIds = isset($_POST['area_ids']) && is_array($_POST['area_ids'])
            ? array_map('absint', $_POST['area_ids'])
            : [];
        $genreIds = isset($_POST['genre_ids']) && is_array($_POST['genre_ids']) ? array_map('absint', $_POST['genre_ids']) : [];
        $moodIds  = isset($_POST['mood_ids']) && is_array($_POST['mood_ids']) ? array_map('absint', $_POST['mood_ids']) : [];
        $themeIds = isset($_POST['theme_ids']) && is_array($_POST['theme_ids']) ? array_map('absint', $_POST['theme_ids']) : [];

        $data = [
            'slug'                 => $slug,
            'title'                => isset($_POST['title']) ? sanitize_text_field((string) $_POST['title']) : '',
            'brand_id'             => static::optionalFk('brand_id'),
            'city_id'              => static::optionalFk('city_id'),
            'game_type_id'         => static::optionalFk('game_type_id'),
            'owner_id'             => static::optionalFk('owner_id'),
            'manager_id'           => static::optionalFk('manager_id'),
            'image_url_cache'      => isset($_POST['image_url_cache']) ? esc_url_raw((string) $_POST['image_url_cache']) : null,
            'min_price'            => isset($_POST['min_price']) ? absint($_POST['min_price']) : 0,
            'capacity_min'         => isset($_POST['capacity_min']) && $_POST['capacity_min'] !== '' ? absint($_POST['capacity_min']) : null,
            'capacity_max'         => isset($_POST['capacity_max']) && $_POST['capacity_max'] !== '' ? absint($_POST['capacity_max']) : null,
            'duration_minutes'     => isset($_POST['duration_minutes']) && $_POST['duration_minutes'] !== '' ? absint($_POST['duration_minutes']) : null,
            'booking_cutoff_min'   => isset($_POST['booking_cutoff_min']) ? absint($_POST['booking_cutoff_min']) : 30,
            'difficulty_level'     => isset($_POST['difficulty_level']) && $_POST['difficulty_level'] !== '' ? absint($_POST['difficulty_level']) : null,
            'age_limit'            => isset($_POST['age_limit']) && $_POST['age_limit'] !== '' ? absint($_POST['age_limit']) : null,
            'status'               => isset($_POST['status']) ? sanitize_text_field((string) $_POST['status']) : 'publish',
            'sale_status'          => isset($_POST['sale_status']) ? sanitize_text_field((string) $_POST['sale_status']) : 'active',
            'hood_name'            => isset($_POST['hood_name']) ? sanitize_text_field((string) $_POST['hood_name']) : null,
        ];

        if ($id > 0) {
            $product = Product::query()->find($id);
            if ($product) {
                $product->fill($data)->save();
                $product->areas()->sync(array_filter($areaIds));
                $product->genres()->sync(array_filter($genreIds));
                $product->moods()->sync(array_filter($moodIds));
                $product->themes()->sync(array_filter($themeIds));
                static::updateCaches($product);
                wp_safe_redirect(static::listUrl());
                exit;
            }
        }
        $product = Product::query()->create($data);
        $product->areas()->sync(array_filter($areaIds));
        $product->genres()->sync(array_filter($genreIds));
        $product->moods()->sync(array_filter($moodIds));
        $product->themes()->sync(array_filter($themeIds));
        static::updateCaches($product);
        wp_safe_redirect(static::listUrl());
        exit;
    }

    private static function updateCaches(Product $product): void
    {
        $product->refresh();
        $product->load(['genres', 'moods', 'themes']);
        if ($product->brand_id && $product->brand) {
            $product->brand_title_cache = $product->brand->title;
        }
        $product->city_name_cache = $product->city_name;
        $product->load('areas');
        $product->areas_cache = $product->areas->isNotEmpty()
            ? $product->areas->pluck('name')->implode(', ')
            : '';
        if ($product->genres->isNotEmpty()) {
            $product->genres_cache = $product->genres->pluck('name')->implode(', ');
        }
        if ($product->moods->isNotEmpty()) {
            $product->moods_cache = $product->moods->pluck('name')->implode(', ');
        }
        if ($product->themes->isNotEmpty()) {
            $product->themes_cache = $product->themes->pluck('name')->implode(', ');
        }
        $product->url_path_cache = '/room/' . $product->slug . '/';
        $product->save();
    }

    private static function renderList(): void
    {
        $items = Product::query()->orderBy('product_id', 'desc')->get();
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html__('بازی‌ها', 'escapezoom-core') . '</h1>';
        echo ' <a href="' . esc_url(static::addUrl()) . '" class="page-title-action">' . esc_html__('افزودن بازی', 'escapezoom-core') . '</a>';
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>ID</th><th>' . esc_html__('عنوان', 'escapezoom-core') . '</th><th>Slug</th><th>' . esc_html__('وضعیت', 'escapezoom-core') . '</th><th></th></tr></thead><tbody>';
        foreach ($items as $row) {
            echo '<tr>';
            echo '<td>' . (int) $row->product_id . '</td>';
            echo '<td>' . esc_html($row->title) . '</td>';
            echo '<td>' . esc_html($row->slug) . '</td>';
            echo '<td>' . esc_html($row->status) . '</td>';
            $pid = (int) $row->product_id;
            echo '<td><a href="' . esc_url(static::editUrl($pid)) . '">' . esc_html__('ویرایش', 'escapezoom-core') . '</a> | ';
            echo '<a href="' . esc_url(static::deleteUrl($pid)) . '" class="ez-delete-confirm">' . esc_html__('حذف', 'escapezoom-core') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
    }

    private static function renderForm(?Product $item): void
    {
        $isEdit = $item !== null;
        $productId = $isEdit ? (int) $item->product_id : 0;
        $cityId = $isEdit ? (int) $item->city_id : 0;
        $areas = $cityId > 0
            ? Area::query()->where('city_id', $cityId)->where('is_active', 1)->orderBy('name')->pluck('name', 'id')->all()
            : [];
        $cities = ['' => '— ' . esc_html__('انتخاب شهر', 'escapezoom-core') . ' —'] + City::query()->where('is_active', 1)->orderBy('name')->pluck('name', 'id')->all();
        $brands = ['' => '—'] + Brand::orderBy('title')->pluck('title', 'id')->toArray();
        $types  = ['' => '—'] + GameType::orderBy('title')->pluck('title', 'id')->toArray();
        $users  = ['' => '—'] + EzUser::orderBy('display_name')->pluck('display_name', 'id')->toArray();
        $genres = Genre::orderBy('name')->pluck('name', 'id')->toArray();
        $moods  = Mood::orderBy('name')->pluck('name', 'id')->toArray();
        $themes = Theme::orderBy('name')->pluck('name', 'id')->toArray();
        $areaIds = $isEdit ? $item->areas->pluck('id')->all() : [];
        $genreIds = $isEdit ? $item->genres->pluck('id')->all() : [];
        $moodIds  = $isEdit ? $item->moods->pluck('id')->all() : [];
        $themeIds = $isEdit ? $item->themes->pluck('id')->all() : [];

        // مناطق به‌صورت شهر → [ area_id => name ] برای اسکریپت تغییر شهر
        $areasByCity = Area::query()->where('is_active', 1)->orderBy('name')->get()
            ->groupBy('city_id')
            ->map(fn ($collection) => $collection->pluck('name', 'id')->all())
            ->all();

        $shortLink = $isEdit ? home_url('/room/' . $item->slug . '/') : '';

        static::renderFormPostboxOpen($isEdit, $productId);
        wp_nonce_field(self::NONCE_SAVE, 'ez_game_nonce');
        if ($isEdit) {
            echo '<input type="hidden" name="product_id" value="' . (int) $productId . '">';
        }

        // ——— باکس: اطلاعات اصلی ———
        static::renderPostbox(
            __('اطلاعات اصلی', 'escapezoom-core'),
            __('عنوان، شناسهٔ URL و تصویر شاخص بازی.', 'escapezoom-core'),
            static function () use ($item, $isEdit, $shortLink): void {
                $title = $isEdit ? $item->title : '';
                $slug = $isEdit ? $item->slug : '';
                $img = $isEdit ? (string) $item->image_url_cache : '';
                echo '<table class="form-table" role="presentation">';
                echo '<tr><th scope="row"><label for="title">' . esc_html__('عنوان', 'escapezoom-core') . '</label></th><td>';
                echo '<input name="title" id="title" type="text" value="' . esc_attr($title) . '" class="regular-text" required>';
                echo '<p class="description">' . esc_html__('نام نمایشی بازی.', 'escapezoom-core') . '</p></td></tr>';
                echo '<tr><th scope="row"><label for="slug">Slug</label></th><td>';
                echo '<input name="slug" id="slug" type="text" value="' . esc_attr($slug) . '" class="regular-text">';
                echo '<p class="description">' . esc_html__('برای URL: /room/{slug}/', 'escapezoom-core') . '</p></td></tr>';
                if ($isEdit && $shortLink !== '') {
                    echo '<tr><th scope="row"><label>' . esc_html__('لینک کوتاه', 'escapezoom-core') . '</label></th><td>';
                    echo '<code style="display:block;padding:6px 0;">' . esc_html($shortLink) . '</code>';
                    echo '<p class="description">' . esc_html__('نمایش فقط؛ قابل ویرایش نیست.', 'escapezoom-core') . '</p></td></tr>';
                }
                echo '<tr><th scope="row"><label for="image_url_cache">' . esc_html__('تصویر شاخص / بنر', 'escapezoom-core') . '</label></th><td>';
                echo '<input name="image_url_cache" id="image_url_cache" type="url" value="' . esc_attr($img) . '" class="large-text"> ';
                echo '<button type="button" class="button" id="ez_select_image">' . esc_html__('انتخاب از رسانه', 'escapezoom-core') . '</button>';
                echo '<p class="description">' . esc_html__('آدرس تصویر یا با دکمه از کتابخانه انتخاب کنید.', 'escapezoom-core') . '</p></td></tr>';
                echo '</table>';
            }
        );

        // ——— باکس: رزرو و فروش ———
        static::renderPostbox(
            __('رزرو و فروش', 'escapezoom-core'),
            __('وضعیت فروش، مالک، مدیر سشن و پارامترهای رزرو.', 'escapezoom-core'),
            static function () use ($item, $isEdit, $users): void {
                echo '<table class="form-table" role="presentation">';
                echo '<tr><th scope="row"><label for="sale_status">' . esc_html__('وضعیت فروش', 'escapezoom-core') . '</label></th><td>';
                echo '<select name="sale_status" id="sale_status">';
                echo '<option value="active"' . ($isEdit && $item->sale_status === 'active' ? ' selected' : '') . '>' . esc_html__('فعال', 'escapezoom-core') . '</option>';
                echo '<option value="inactive"' . ($isEdit && $item->sale_status === 'inactive' ? ' selected' : '') . '>' . esc_html__('غیرفعال', 'escapezoom-core') . '</option></select></td></tr>';
                echo '<tr><th scope="row"><label for="owner_id">' . esc_html__('مالک', 'escapezoom-core') . '</label></th><td><select name="owner_id" id="owner_id">';
                foreach ($users as $k => $v) {
                    if ($k === '') {
                        echo '<option value="">—</option>';
                        continue;
                    }
                    $s = $isEdit && (int) $item->owner_id === (int) $k ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="manager_id">' . esc_html__('مدیر سشن', 'escapezoom-core') . '</label></th><td><select name="manager_id" id="manager_id">';
                foreach ($users as $k => $v) {
                    if ($k === '') {
                        echo '<option value="">—</option>';
                        continue;
                    }
                    $s = $isEdit && (int) $item->manager_id === (int) $k ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="min_price">' . esc_html__('حداقل قیمت', 'escapezoom-core') . '</label></th><td>';
                echo '<input name="min_price" id="min_price" type="number" value="' . ($isEdit ? (int) $item->min_price : 0) . '" min="0" class="small-text"> ';
                echo '<span class="description">' . esc_html__('واحد: تومان (یا ارز سایت)', 'escapezoom-core') . '</span></td></tr>';
                echo '<tr><th scope="row"><label for="booking_cutoff_min">' . esc_html__('حد قطع رزرو (دقیقه قبل از شروع)', 'escapezoom-core') . '</label></th><td>';
                echo '<input name="booking_cutoff_min" id="booking_cutoff_min" type="number" value="' . ($isEdit ? (int) $item->booking_cutoff_min : 30) . '" min="0" class="small-text"> ';
                echo '<p class="description">' . esc_html__('پیش‌فرض ۳۰ دقیقه.', 'escapezoom-core') . '</p></td></tr>';
                echo '</table>';
            }
        );

        // ——— باکس: تعداد نفرات، سن، مدت بازی (یک خط؛ اسلایدر دو دستگیره + سن پیش‌فرض ۱۴ + مدت ۳۰/۶۰/۹۰/۱۲۰) ———
        static::renderPostbox(
            __('تعداد نفرات، سن و مدت بازی', 'escapezoom-core'),
            __('ظرفیت با دو دستگیره، حداقل سن و مدت بازی (۳۰، ۶۰، ۹۰، ۱۲۰ دقیقه).', 'escapezoom-core'),
            static function () use ($item, $isEdit): void {
                $capMin = $isEdit && $item->capacity_min !== null ? max(1, min(20, (int) $item->capacity_min)) : 4;
                $capMax = $isEdit && $item->capacity_max !== null ? max(1, min(20, (int) $item->capacity_max)) : 6;
                if ($capMax < $capMin + 2) {
                    $capMax = min(20, $capMin + 2);
                }
                if ($capMin > $capMax - 2) {
                    $capMin = max(1, $capMax - 2);
                }
                $ageNum = $isEdit && $item->age_limit !== null ? max(8, min(20, (int) $item->age_limit)) : 14;
                $duration_options = [30, 60, 90, 120];
                $duration = $isEdit && $item->duration_minutes !== null ? (int) $item->duration_minutes : 60;
                $duration = in_array($duration, $duration_options, true) ? $duration : 60;
                $durationIdx = array_search($duration, $duration_options, true);
                $durationIdx = $durationIdx !== false ? $durationIdx : 1;
                echo '<table class="form-table ez-capacity-age-row" role="presentation"><tr>';
                echo '<td class="ez-capacity-age-cell">';
                echo '<label class="ez-capacity-age-label">' . esc_html__('تعداد نفرات', 'escapezoom-core') . '</label>';
                echo '<div class="ez-dual-slider ez-capacity-wrapper" data-gap="2" data-min="1" data-max="20">';
                echo '<input type="hidden" name="capacity_min" class="ez-capacity-min-input" id="ez_game_capacity_min_value" value="' . esc_attr((string) $capMin) . '">';
                echo '<input type="hidden" name="capacity_max" class="ez-capacity-max-input" id="ez_game_capacity_max_value" value="' . esc_attr((string) $capMax) . '">';
                echo '<p class="ez-capacity-display" aria-live="polite">' . esc_html(sprintf(__('%s تا %s نفر', 'escapezoom-core'), $capMin, $capMax)) . '</p>';
                echo '<div class="ez-dual-slider-track">';
                echo '<div class="ez-dual-slider-fill"></div>';
                echo '<span class="ez-dual-slider-handle ez-handle-min" data-handle="min" role="slider" tabindex="0" aria-valuenow="' . (int) $capMin . '" aria-valuemin="1" aria-valuemax="20"></span>';
                echo '<span class="ez-dual-slider-handle ez-handle-max" data-handle="max" role="slider" tabindex="0" aria-valuenow="' . (int) $capMax . '" aria-valuemin="1" aria-valuemax="20"></span>';
                echo '</div></div></td>';
                echo '<td class="ez-capacity-age-cell">';
                echo '<label class="ez-capacity-age-label">' . esc_html__('حداقل سن', 'escapezoom-core') . '</label>';
                echo '<div class="ez-age-range-wrapper">';
                echo '<input type="hidden" name="age_limit" id="ez_game_age_value" value="' . esc_attr((string) $ageNum) . '">';
                echo '<p class="ez-capacity-display" id="ez_game_age_display">' . esc_html(__('سن', 'escapezoom-core') . ' ' . $ageNum) . '</p>';
                echo '<input type="range" id="ez_game_age_sl" class="ez-single-slider" min="8" max="20" step="1" value="' . esc_attr((string) $ageNum) . '" aria-label="' . esc_attr__('حداقل سن', 'escapezoom-core') . '">';
                echo '</div></td>';
                echo '<td class="ez-capacity-age-cell">';
                echo '<label class="ez-capacity-age-label">' . esc_html__('مدت بازی', 'escapezoom-core') . '</label>';
                echo '<div class="ez-duration-range-wrapper">';
                echo '<input type="hidden" name="duration_minutes" id="ez_game_duration_value" value="' . esc_attr((string) $duration) . '">';
                echo '<p class="ez-capacity-display" id="ez_game_duration_display">' . esc_html($duration . ' ' . __('دقیقه', 'escapezoom-core')) . '</p>';
                echo '<input type="range" id="ez_game_duration_sl" class="ez-single-slider" min="0" max="3" step="1" value="' . (int) $durationIdx . '" aria-label="' . esc_attr__('مدت بازی', 'escapezoom-core') . '">';
                echo '</div></td>';
                echo '</tr></table>';
                static::renderCapacityAgeScript();
            }
        );

        // ——— باکس: متا و سطح ———
        static::renderPostbox(
            __('متا و سطح', 'escapezoom-core'),
            __('سطح سختی و وضعیت انتشار.', 'escapezoom-core'),
            static function () use ($item, $isEdit): void {
                $diff = $isEdit && $item->difficulty_level !== null ? (int) $item->difficulty_level : '';
                echo '<table class="form-table" role="presentation">';
                echo '<tr><th scope="row"><label for="difficulty_level">' . esc_html__('سطح بازی', 'escapezoom-core') . '</label></th><td>';
                echo '<select name="difficulty_level" id="difficulty_level"><option value="">—</option>';
                for ($i = 1; $i <= 10; $i++) {
                    $sel = $diff === $i ? ' selected' : '';
                    echo '<option value="' . $i . '"' . $sel . '>' . $i . '</option>';
                }
                echo '</select> ';
                echo '<span class="description">' . esc_html__('۱ (آسان) تا ۱۰ (بسیار سخت)', 'escapezoom-core') . '</span></td></tr>';
                echo '<tr><th scope="row"><label for="status">' . esc_html__('وضعیت انتشار', 'escapezoom-core') . '</label></th><td>';
                echo '<select name="status" id="status">';
                echo '<option value="publish"' . ($isEdit && $item->status === 'publish' ? ' selected' : '') . '>' . esc_html__('منتشر شده', 'escapezoom-core') . '</option>';
                echo '<option value="draft"' . ($isEdit && $item->status === 'draft' ? ' selected' : '') . '>' . esc_html__('پیش‌نویس', 'escapezoom-core') . '</option></select></td></tr>';
                echo '</table>';
            }
        );

        // ——— باکس: مکان ———
        static::renderPostbox(
            __('مکان', 'escapezoom-core'),
            __('شهر، مناطق و محله.', 'escapezoom-core'),
            static function () use ($item, $isEdit, $cities, $areas, $areaIds): void {
                $cityId = $isEdit ? (int) $item->city_id : 0;
                $hood = $isEdit ? (string) $item->hood_name : '';
                echo '<table class="form-table" role="presentation">';
                echo '<tr><th scope="row"><label for="city_id">' . esc_html__('شهر', 'escapezoom-core') . '</label></th><td><select name="city_id" id="city_id">';
                foreach ($cities as $k => $v) {
                    $s = $cityId === (int) $k ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="area_ids">' . esc_html__('مناطق', 'escapezoom-core') . '</label></th><td>';
                echo '<select name="area_ids[]" id="area_ids" multiple class="regular-text" style="height:auto;min-height:80px;">';
                foreach ($areas as $aid => $name) {
                    $s = in_array((int) $aid, $areaIds, true) ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $aid) . '"' . $s . '>' . esc_html($name) . '</option>';
                }
                echo '</select>';
                echo '<p class="description">' . esc_html__('ابتدا شهر را انتخاب کنید؛ مناطق همان شهر در اینجا نمایش داده می‌شوند.', 'escapezoom-core') . '</p></td></tr>';
                echo '<tr><th scope="row"><label for="hood_name">' . esc_html__('محله', 'escapezoom-core') . '</label></th><td>';
                echo '<input name="hood_name" id="hood_name" type="text" value="' . esc_attr($hood) . '" class="regular-text"></td></tr>';
                echo '</table>';
            }
        );

        // ——— باکس: وابستگی‌ها ———
        static::renderPostbox(
            __('وابستگی‌ها', 'escapezoom-core'),
            __('برند، نوع بازی، ژانرها، مودها و تم‌ها.', 'escapezoom-core'),
            static function () use ($item, $isEdit, $brands, $types, $genres, $moods, $themes, $genreIds, $moodIds, $themeIds): void {
                echo '<table class="form-table" role="presentation">';
                echo '<tr><th scope="row"><label for="brand_id">' . esc_html__('برند', 'escapezoom-core') . '</label></th><td><select name="brand_id" id="brand_id">';
                foreach ($brands as $k => $v) {
                    if ($k === '') {
                        echo '<option value="">—</option>';
                        continue;
                    }
                    $s = $isEdit && (int) $item->brand_id === (int) $k ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="game_type_id">' . esc_html__('نوع بازی', 'escapezoom-core') . '</label></th><td><select name="game_type_id" id="game_type_id">';
                foreach ($types as $k => $v) {
                    if ($k === '') {
                        echo '<option value="">—</option>';
                        continue;
                    }
                    $s = $isEdit && (int) $item->game_type_id === (int) $k ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="genre_ids">' . esc_html__('ژانرها', 'escapezoom-core') . '</label></th><td><select name="genre_ids[]" id="genre_ids" multiple class="regular-text" style="height:auto;min-height:80px;">';
                foreach ($genres as $k => $v) {
                    $s = in_array((int) $k, $genreIds, true) ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="mood_ids">' . esc_html__('مودها', 'escapezoom-core') . '</label></th><td><select name="mood_ids[]" id="mood_ids" multiple class="regular-text" style="height:auto;min-height:80px;">';
                foreach ($moods as $k => $v) {
                    $s = in_array((int) $k, $moodIds, true) ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '<tr><th scope="row"><label for="theme_ids">' . esc_html__('تم‌ها', 'escapezoom-core') . '</label></th><td><select name="theme_ids[]" id="theme_ids" multiple class="regular-text" style="height:auto;min-height:80px;">';
                foreach ($themes as $k => $v) {
                    $s = in_array((int) $k, $themeIds, true) ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $s . '>' . esc_html($v) . '</option>';
                }
                echo '</select></td></tr>';
                echo '</table>';
            }
        );

        echo '<p class="submit">';
        submit_button(__('ذخیره', 'escapezoom-core'), 'primary', 'submit', false);
        echo ' <a href="' . esc_url(static::listUrl()) . '" class="button">' . esc_html__('انصراف', 'escapezoom-core') . '</a>';
        echo '</p></form></div>';

        static::renderFormCityAreasScript($areasByCity);
        static::renderFormMediaScript();
    }

    /** @param array<int, array<int, string>> $areasByCity شهر => [ area_id => name ] */
    private static function renderFormCityAreasScript(array $areasByCity): void
    {
        $json = wp_json_encode($areasByCity);
        ?>
<script>
(function() {
    var areasByCity = <?php echo $json ?: '{}'; ?>;
    var citySelect = document.getElementById('city_id');
    var areaSelect = document.getElementById('area_ids');
    if (!citySelect || !areaSelect) return;
    function fillAreas(cityId) {
        var id = String(cityId || '');
        var list = areasByCity[id] || {};
        areaSelect.innerHTML = '';
        Object.keys(list).forEach(function(areaId) {
            var opt = document.createElement('option');
            opt.value = areaId;
            opt.textContent = list[areaId];
            areaSelect.appendChild(opt);
        });
    }
    citySelect.addEventListener('change', function() { fillAreas(this.value); });
})();
</script>
        <?php
    }

    private static function renderFormPostboxOpen(bool $isEdit, int $productId): void
    {
        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html($isEdit ? __('ویرایش بازی', 'escapezoom-core') : __('افزودن بازی', 'escapezoom-core')) . '</h1>';
        echo '<form method="post" action="" id="ez-game-form">';
    }

    private static function renderPostbox(string $title, string $description, callable $content): void
    {
        echo '<div class="postbox"><div class="postbox-header"><h2 class="hndle">' . esc_html($title) . '</h2></div>';
        echo '<div class="inside">';
        if ($description !== '') {
            echo '<p class="description" style="margin-top:0;">' . esc_html($description) . '</p>';
        }
        $content();
        echo '</div></div>';
    }

    private static function renderCapacityAgeScript(): void
    {
        $fmt = esc_js(__('%s تا %s نفر', 'escapezoom-core'));
        $sen = esc_js(__('سن', 'escapezoom-core'));
        $dagh = esc_js(__('دقیقه', 'escapezoom-core'));
        ?>
<script>
(function() {
    var GAP = 2, RANGE_MIN = 1, RANGE_MAX = 20;
    document.querySelectorAll('.ez-dual-slider').forEach(function(slider) {
        var track = slider.querySelector('.ez-dual-slider-track');
        var fill = slider.querySelector('.ez-dual-slider-fill');
        var minIn = slider.querySelector('.ez-capacity-min-input');
        var maxIn = slider.querySelector('.ez-capacity-max-input');
        var display = slider.querySelector('.ez-capacity-display');
        var hMin = slider.querySelector('.ez-handle-min');
        var hMax = slider.querySelector('.ez-handle-max');
        if (!track || !fill || !minIn || !maxIn || !hMin || !hMax) return;
        function pct(v) { return ((v - RANGE_MIN) / (RANGE_MAX - RANGE_MIN)) * 100; }
        function valFromX(x) { var r = track.getBoundingClientRect(); var p = (x - r.left) / r.width; return Math.round(RANGE_MIN + p * (RANGE_MAX - RANGE_MIN)); }
        function updateUI(min, max) {
            min = Math.max(RANGE_MIN, Math.min(RANGE_MAX, min));
            max = Math.max(RANGE_MIN, Math.min(RANGE_MAX, max));
            if (max < min + GAP) max = min + GAP;
            if (min > max - GAP) min = max - GAP;
            if (max > RANGE_MAX) { max = RANGE_MAX; min = max - GAP; }
            if (min < RANGE_MIN) { min = RANGE_MIN; max = min + GAP; }
            minIn.value = String(min);
            maxIn.value = String(max);
            fill.style.left = pct(min) + '%';
            fill.style.width = (pct(max) - pct(min)) + '%';
            hMin.style.left = pct(min) + '%';
            hMax.style.left = pct(max) + '%';
            hMin.setAttribute('aria-valuenow', min);
            hMax.setAttribute('aria-valuenow', max);
            if (display) display.textContent = '<?php echo $fmt; ?>'.replace('%s', String(min)).replace('%s', String(max));
        }
        function drag(handle) {
            function move(e) {
                var x = e.touches ? e.touches[0].clientX : e.clientX;
                var v = valFromX(x);
                var min = parseInt(minIn.value, 10);
                var max = parseInt(maxIn.value, 10);
                if (handle.classList.contains('ez-handle-min')) {
                    min = Math.max(RANGE_MIN, Math.min(max - GAP, v));
                    updateUI(min, max);
                } else {
                    max = Math.min(RANGE_MAX, Math.max(min + GAP, v));
                    updateUI(min, max);
                }
            }
            function stop() {
                document.removeEventListener('mousemove', move);
                document.removeEventListener('mouseup', stop);
                document.removeEventListener('touchmove', move, { passive: false });
                document.removeEventListener('touchend', stop);
            }
            document.addEventListener('mousemove', move);
            document.addEventListener('mouseup', stop);
            document.addEventListener('touchmove', move, { passive: false });
            document.addEventListener('touchend', stop);
        }
        var min = parseInt(minIn.value, 10) || RANGE_MIN;
        var max = parseInt(maxIn.value, 10) || min + GAP;
        updateUI(min, max);
        [hMin, hMax].forEach(function(h) {
            h.addEventListener('mousedown', function(e) { e.preventDefault(); drag(h); });
            h.addEventListener('touchstart', function(e) { e.preventDefault(); drag(h); }, { passive: false });
        });
        track.addEventListener('click', function(e) {
            var v = valFromX(e.clientX);
            var min = parseInt(minIn.value, 10);
            var max = parseInt(maxIn.value, 10);
            if (v < (min + max) / 2) { min = Math.max(RANGE_MIN, Math.min(v, max - GAP)); updateUI(min, max); }
            else { max = Math.min(RANGE_MAX, Math.max(v, min + GAP)); updateUI(min, max); }
        });
    });
    var ageIn = document.getElementById('ez_game_age_value');
    var ageSl = document.getElementById('ez_game_age_sl');
    var ageDisplay = document.getElementById('ez_game_age_display');
    if (ageSl && ageIn && ageDisplay) {
        ageSl.addEventListener('input', function() { var v = ageSl.value; ageIn.value = v; ageDisplay.textContent = '<?php echo $sen; ?> ' + v; });
    }
    var durIn = document.getElementById('ez_game_duration_value');
    var durSl = document.getElementById('ez_game_duration_sl');
    var durDisplay = document.getElementById('ez_game_duration_display');
    if (durSl && durIn && durDisplay) {
        var opts = [30, 60, 90, 120];
        durSl.addEventListener('input', function() { var i = parseInt(durSl.value, 10); durIn.value = String(opts[i]); durDisplay.textContent = opts[i] + ' <?php echo $dagh; ?>'; });
    }
})();
</script>
        <?php
    }

    private static function renderFormMediaScript(): void
    {
        wp_enqueue_media();
        ?>
<script>
(function() {
    var btn = document.getElementById('ez_select_image');
    var input = document.getElementById('image_url_cache');
    if (!btn || !input) return;
    btn.addEventListener('click', function() {
        var frame = wp.media({
            library: { type: 'image' },
            multiple: false
        });
        frame.on('select', function() {
            var att = frame.state().get('selection').first().toJSON();
            if (att && att.url) input.value = att.url;
        });
        frame.open();
    });
})();
</script>
        <?php
    }
}
