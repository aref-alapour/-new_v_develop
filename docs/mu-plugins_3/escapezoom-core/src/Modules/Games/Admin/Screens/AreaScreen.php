<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use EscapeZoom\Core\Core\AjaxSecurityGuard;
use EscapeZoom\Core\Modules\Games\Models\Area;
use EscapeZoom\Core\Modules\Games\Models\City;

final class AreaScreen extends BaseCrudScreen
{
    use AjaxSecurityGuard;

    protected static function getPageSlug(): string
    {
        return 'escapezoom-areas';
    }

    protected static function getNonceAction(): string
    {
        return 'ez_save_area';
    }

    protected static function getNonceDelete(): string
    {
        return 'ez_delete_area';
    }

    /** @inheritdoc */
    protected static function getModelClass(): string
    {
        return Area::class;
    }

    protected static function getListTitle(): string
    {
        return __('مناطق', 'escapezoom-core');
    }

    protected static function getListColumns(): array
    {
        return [
            'name'     => __('نام', 'escapezoom-core'),
            'slug'     => 'Slug',
            'city_id'  => [
                'label'    => __('شهر', 'escapezoom-core'),
                'callback' => static function (\Illuminate\Database\Eloquent\Model $row): string {
                    if (!$row->city_id) {
                        return '—';
                    }
                    $city = $row->relationLoaded('city') ? $row->city : City::find($row->city_id);
                    return $city ? $city->name : (string) $row->city_id;
                },
            ],
            'is_active' => __('فعال', 'escapezoom-core'),
        ];
    }

    protected static function getFormFields(): array
    {
        return [
            'city_id'  => [
                'label'   => __('شهر', 'escapezoom-core'),
                'type'    => 'select',
                'options' => static function (): array {
                    return ['' => '— انتخاب شهر —'] + City::query()->orderBy('name')->pluck('name', 'id')->toArray();
                },
                'required' => true,
            ],
            'name'     => ['label' => __('نام', 'escapezoom-core'), 'type' => 'text', 'required' => true],
            'slug'     => ['label' => 'Slug', 'type' => 'text'],
            'is_active' => ['label' => __('فعال', 'escapezoom-core'), 'type' => 'checkbox'],
        ];
    }

    protected static function getOrderBy(): array
    {
        return ['name' => 'asc'];
    }

    /** @inheritdoc */
    protected static function getListWith(): array
    {
        return ['city'];
    }

    protected static function getAddTitle(): string
    {
        return __('افزودن منطقه', 'escapezoom-core');
    }

    protected static function getEditTitle(): string
    {
        return __('ویرایش منطقه', 'escapezoom-core');
    }

    protected static function getShowAddButton(): bool
    {
        return true;
    }

    protected static function getModalAjaxSaveAction(): string
    {
        return 'ez_area_ajax_save';
    }

    protected static function getModalDialogId(): string
    {
        return 'ez-area-add-dialog';
    }

    protected static function getModalFormId(): string
    {
        return 'ez-area-add-form';
    }

    protected static function getModalRefreshMode(): string
    {
        return 'htmx';
    }

    protected static function getModalRefreshEventName(): ?string
    {
        return 'refreshAreasTable';
    }

    protected static function getModalAjaxUpdateAction(): ?string
    {
        return 'ez_area_ajax_update';
    }

    protected static function supportsModalEdit(): bool
    {
        return true;
    }

    /** @inheritdoc */
    protected static function getRowDataForModal($row): array
    {
        return [
            'id'        => (int) ($row->id ?? 0),
            'city_id'   => (int) ($row->city_id ?? 0),
            'name'      => (string) ($row->name ?? ''),
            'slug'      => (string) ($row->slug ?? ''),
            'is_active' => isset($row->is_active) ? (bool) $row->is_active : true,
        ];
    }

    protected static function renderList(): void
    {
        $modelClass = static::getModelClass();
        $query = $modelClass::query();
        $with = static::getListWith();
        if ($with !== []) {
            $query->with($with);
        }
        foreach (static::getOrderBy() as $col => $dir) {
            $query->orderBy($col, $dir);
        }
        $items = $query->get();
        $columns = static::getListColumns();
        $refreshUrl = admin_url('admin-ajax.php') . '?action=ez_area_refresh_table';
        echo '<div class="wrap" id="ez-areas-table-container">';
        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped" hx-trigger="refreshAreasTable from:body" hx-get="' . esc_url($refreshUrl) . '" hx-target="#ez-areas-table-container" hx-swap="innerHTML"><thead><tr><th>ID</th>';
        foreach ($columns as $key => $col) {
            $label = is_array($col) ? ($col['label'] ?? $key) : $col;
            echo '<th>' . esc_html($label) . '</th>';
        }
        echo '<th></th></tr></thead><tbody>';
        foreach ($items as $row) {
            $rowData = static::getRowDataForModal($row);
            $dataAttrs = '';
            foreach ($rowData as $k => $v) {
                $dataAttrs .= ' data-' . esc_attr((string) $k) . '="' . esc_attr(is_bool($v) ? ($v ? '1' : '0') : (string) $v) . '"';
            }
            echo '<tr><td>' . (int) $row->id . '</td>';
            foreach (array_keys($columns) as $key) {
                $col = $columns[$key];
                $callback = is_array($col) && isset($col['callback']) ? $col['callback'] : null;
                $value = $callback ? $callback($row) : ($row->{$key} ?? '');
                if (is_bool($value)) {
                    $value = $value ? '✓' : '—';
                }
                echo '<td>' . esc_html((string) $value) . '</td>';
            }
            echo '<td><a href="#" class="ez-crud-modal-edit"' . $dataAttrs . '>' . esc_html__('ویرایش', 'escapezoom-core') . '</a> | ';
            echo '<a href="' . esc_url(static::deleteUrl((int) $row->id)) . '" class="ez-delete-confirm">' . esc_html__('حذف', 'escapezoom-core') . '</a></td></tr>';
        }
        echo '</tbody></table></div>';
        static::renderCrudModal();
    }

    public static function ajaxSave(): void
    {
        static::assertAjaxCapability();
        static::assertAjaxNonce('ez_area_ajax_save');
        $city_id = isset($_POST['city_id']) ? absint($_POST['city_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
        $slug = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
        $is_active = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1;
        if ($city_id < 1) {
            wp_send_json_error(['city_id' => __('انتخاب شهر الزامی است.', 'escapezoom-core')], 400);
        }
        if ($name === '') {
            wp_send_json_error(['name' => __('نام الزامی است.', 'escapezoom-core')], 400);
        }
        if ($slug === '') {
            $slug = sanitize_title($name);
        }

        // یونیک بودن نام در هر شهر
        if (Area::query()->where('city_id', $city_id)->where('name', $name)->exists()) {
            wp_send_json_error(['name' => __('این نام در این شهر قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        // یونیک بودن اسلاگ در هر شهر
        if ($slug !== '' && Area::query()->where('city_id', $city_id)->where('slug', $slug)->exists()) {
            wp_send_json_error(['slug' => __('این اسلاگ در این شهر قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        $area = Area::query()->create([
            'city_id' => $city_id,
            'name'    => $name,
            'slug'    => $slug !== '' ? $slug : null,
            'is_active' => $is_active,
        ]);
        wp_send_json_success(['id' => (int) $area->id, 'name' => $area->name]);
    }

    public static function ajaxUpdate(): void
    {
        static::assertAjaxCapability();
        static::assertAjaxNonce('ez_area_ajax_update');
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $city_id = isset($_POST['city_id']) ? absint($_POST['city_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
        $slug = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
        $is_active = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1;

        if ($id < 1) {
            wp_send_json_error(['general' => __('شناسه رکورد نامعتبر است.', 'escapezoom-core')], 400);
        }
        if ($city_id < 1) {
            wp_send_json_error(['city_id' => __('انتخاب شهر الزامی است.', 'escapezoom-core')], 400);
        }
        if ($name === '') {
            wp_send_json_error(['name' => __('نام الزامی است.', 'escapezoom-core')], 400);
        }

        $area = Area::query()->find($id);
        if (!$area) {
            wp_send_json_error(['general' => __('رکورد یافت نشد.', 'escapezoom-core')], 404);
        }

        if (Area::query()->where('city_id', $city_id)->where('name', $name)->where('id', '!=', $id)->exists()) {
            wp_send_json_error(['name' => __('این نام در این شهر قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }
        if ($slug !== '' && Area::query()->where('city_id', $city_id)->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            wp_send_json_error(['slug' => __('این اسلاگ در این شهر قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        if ($slug === '') {
            $slug = sanitize_title($name);
        }

        $area->city_id = $city_id;
        $area->name = $name;
        $area->slug = $slug !== '' ? $slug : null;
        $area->is_active = $is_active;
        $area->save();

        wp_send_json_success(['id' => (int) $area->id, 'name' => $area->name]);
    }

    public static function ajaxRefreshTable(): void
    {
        static::assertAjaxCapability();
        static::assertAdminFragmentNonceHtml();
        $modelClass = static::getModelClass();
        $query = $modelClass::query();
        $with = static::getListWith();
        if ($with !== []) {
            $query->with($with);
        }
        foreach (static::getOrderBy() as $col => $dir) {
            $query->orderBy($col, $dir);
        }
        $items = $query->get();
        $columns = static::getListColumns();
        $refreshUrl = admin_url('admin-ajax.php') . '?action=ez_area_refresh_table';
        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped" hx-trigger="refreshAreasTable from:body" hx-get="' . esc_url($refreshUrl) . '" hx-target="#ez-areas-table-container" hx-swap="innerHTML"><thead><tr><th>ID</th>';
        foreach ($columns as $key => $col) {
            $label = is_array($col) ? ($col['label'] ?? $key) : $col;
            echo '<th>' . esc_html($label) . '</th>';
        }
        echo '<th></th></tr></thead><tbody>';
        foreach ($items as $row) {
            $rowData = static::getRowDataForModal($row);
            $dataAttrs = '';
            foreach ($rowData as $k => $v) {
                $dataAttrs .= ' data-' . esc_attr((string) $k) . '="' . esc_attr(is_bool($v) ? ($v ? '1' : '0') : (string) $v) . '"';
            }
            echo '<tr><td>' . (int) $row->id . '</td>';
            foreach (array_keys($columns) as $key) {
                $col = $columns[$key];
                $callback = is_array($col) && isset($col['callback']) ? $col['callback'] : null;
                $value = $callback ? $callback($row) : ($row->{$key} ?? '');
                if (is_bool($value)) {
                    $value = $value ? '✓' : '—';
                }
                echo '<td>' . esc_html((string) $value) . '</td>';
            }
            echo '<td><a href="#" class="ez-crud-modal-edit"' . $dataAttrs . '>' . esc_html__('ویرایش', 'escapezoom-core') . '</a> | ';
            echo '<a href="' . esc_url(static::deleteUrl((int) $row->id)) . '" class="ez-delete-confirm">' . esc_html__('حذف', 'escapezoom-core') . '</a></td></tr>';
        }
        echo '</tbody></table>';
        exit;
    }
}
