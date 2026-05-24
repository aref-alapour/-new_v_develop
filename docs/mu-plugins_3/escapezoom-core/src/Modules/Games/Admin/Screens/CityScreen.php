<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use EscapeZoom\Core\Core\AjaxSecurityGuard;
use EscapeZoom\Core\Modules\Games\Models\City;

final class CityScreen extends BaseCrudScreen
{
    use AjaxSecurityGuard;

    protected static function getPageSlug(): string
    {
        return 'escapezoom-cities';
    }

    protected static function getNonceAction(): string
    {
        return 'ez_save_city';
    }

    protected static function getNonceDelete(): string
    {
        return 'ez_delete_city';
    }

    /** @inheritdoc */
    protected static function getModelClass(): string
    {
        return City::class;
    }

    protected static function getListTitle(): string
    {
        return __('شهرها', 'escapezoom-core');
    }

    protected static function getListColumns(): array
    {
        return [
            'name'      => __('نام', 'escapezoom-core'),
            'slug'      => 'Slug',
            'is_active' => __('فعال', 'escapezoom-core'),
        ];
    }

    protected static function getFormFields(): array
    {
        return [
            'name'      => ['label' => __('نام', 'escapezoom-core'), 'type' => 'text', 'required' => true],
            'slug'      => ['label' => 'Slug', 'type' => 'text'],
            'is_active' => ['label' => __('فعال', 'escapezoom-core'), 'type' => 'checkbox'],
        ];
    }

    protected static function getOrderBy(): array
    {
        return ['name' => 'asc'];
    }

    protected static function getAddTitle(): string
    {
        return __('افزودن شهر', 'escapezoom-core');
    }

    protected static function getEditTitle(): string
    {
        return __('ویرایش شهر', 'escapezoom-core');
    }

    protected static function getShowAddButton(): bool
    {
        return true;
    }

    protected static function getModalAjaxSaveAction(): string
    {
        return 'ez_city_ajax_save';
    }

    protected static function getModalDialogId(): string
    {
        return 'ez-city-add-dialog';
    }

    protected static function getModalFormId(): string
    {
        return 'ez-city-add-form';
    }

    protected static function getModalRefreshMode(): string
    {
        return 'htmx';
    }

    protected static function getModalRefreshEventName(): ?string
    {
        return 'refreshCitiesTable';
    }

    protected static function getModalAjaxUpdateAction(): ?string
    {
        return 'ez_city_ajax_update';
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
            'name'      => (string) ($row->name ?? ''),
            'slug'      => (string) ($row->slug ?? ''),
            'is_active' => isset($row->is_active) ? (bool) $row->is_active : true,
        ];
    }

    protected static function renderList(): void
    {
        $modelClass = static::getModelClass();
        $query = $modelClass::query();
        foreach (static::getOrderBy() as $col => $dir) {
            $query->orderBy($col, $dir);
        }
        $items = $query->get();
        $columns = static::getListColumns();
        $refreshUrl = admin_url('admin-ajax.php') . '?action=ez_city_refresh_table';
        echo '<div class="wrap" id="ez-cities-table-container">';
        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped" hx-trigger="refreshCitiesTable from:body" hx-get="' . esc_url($refreshUrl) . '" hx-target="#ez-cities-table-container" hx-swap="innerHTML"><thead><tr><th>ID</th>';
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
        static::assertAjaxNonce('ez_city_ajax_save');
        $name = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
        $slug = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
        $is_active = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1;

        if ($name === '') {
            wp_send_json_error(['name' => __('نام الزامی است.', 'escapezoom-core')], 400);
        }

        if ($slug === '') {
            $slug = sanitize_title($name);
        }

        // یونیک بودن نام
        if (City::query()->where('name', $name)->exists()) {
            wp_send_json_error(['name' => __('این نام قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        // یونیک بودن اسلاگ
        if ($slug !== '' && City::query()->where('slug', $slug)->exists()) {
            wp_send_json_error(['slug' => __('این اسلاگ قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        $city = City::query()->create([
            'name'      => $name,
            'slug'      => $slug !== '' ? $slug : null,
            'is_active' => $is_active,
        ]);
        wp_send_json_success(['id' => (int) $city->id, 'name' => $city->name]);
    }

    public static function ajaxUpdate(): void
    {
        static::assertAjaxCapability();
        static::assertAjaxNonce('ez_city_ajax_update');
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field((string) $_POST['name']) : '';
        $slug = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';
        $is_active = isset($_POST['is_active']) && (int) $_POST['is_active'] === 1;

        if ($id < 1) {
            wp_send_json_error(['general' => __('شناسه رکورد نامعتبر است.', 'escapezoom-core')], 400);
        }
        if ($name === '') {
            wp_send_json_error(['name' => __('نام الزامی است.', 'escapezoom-core')], 400);
        }

        $city = City::query()->find($id);
        if (!$city) {
            wp_send_json_error(['general' => __('رکورد یافت نشد.', 'escapezoom-core')], 404);
        }

        if (City::query()->where('name', $name)->where('id', '!=', $id)->exists()) {
            wp_send_json_error(['name' => __('این نام قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }
        if ($slug !== '' && City::query()->where('slug', $slug)->where('id', '!=', $id)->exists()) {
            wp_send_json_error(['slug' => __('این اسلاگ قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        if ($slug === '') {
            $slug = sanitize_title($name);
        }

        $city->name = $name;
        $city->slug = $slug !== '' ? $slug : null;
        $city->is_active = $is_active;
        $city->save();

        wp_send_json_success(['id' => (int) $city->id, 'name' => $city->name]);
    }

    public static function ajaxRefreshTable(): void
    {
        static::assertAjaxCapability();
        static::assertAdminFragmentNonceHtml();
        $modelClass = static::getModelClass();
        $query = $modelClass::query();
        foreach (static::getOrderBy() as $col => $dir) {
            $query->orderBy($col, $dir);
        }
        $items = $query->get();
        $columns = static::getListColumns();
        $refreshUrl = admin_url('admin-ajax.php') . '?action=ez_city_refresh_table';
        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped" hx-trigger="refreshCitiesTable from:body" hx-get="' . esc_url($refreshUrl) . '" hx-target="#ez-cities-table-container" hx-swap="innerHTML"><thead><tr><th>ID</th>';
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
