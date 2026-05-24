<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use EscapeZoom\Core\Core\AjaxSecurityGuard;
use EscapeZoom\Core\Modules\Games\Models\GameType;

final class GameTypeScreen extends BaseCrudScreen
{
    use AjaxSecurityGuard;

    protected static function getPageSlug(): string
    {
        return 'escapezoom-game-types';
    }

    protected static function getNonceAction(): string
    {
        return 'ez_save_game_type';
    }

    protected static function getNonceDelete(): string
    {
        return 'ez_delete_game_type';
    }

    /** @inheritdoc */
    protected static function getModelClass(): string
    {
        return GameType::class;
    }

    protected static function getListTitle(): string
    {
        return __('انواع بازی', 'escapezoom-core');
    }

    protected static function getListColumns(): array
    {
        return [
            'title'     => __('عنوان', 'escapezoom-core'),
            'slug'      => 'Slug',
            'is_active' => __('فعال', 'escapezoom-core'),
            'created_at'=> [
                'label'    => __('تاریخ ایجاد', 'escapezoom-core'),
                'callback' => static function ($row): string {
                    if (!isset($row->created_at) || $row->created_at === null) {
                        return '—';
                    }
                    $ts = is_string($row->created_at) ? $row->created_at : (string) $row->created_at;
                    return mysql2date('Y-m-d H:i', $ts);
                },
            ],
        ];
    }

    protected static function getFormFields(): array
    {
        return [
            'title'     => ['label' => __('عنوان', 'escapezoom-core'), 'type' => 'text', 'required' => true],
            'slug'      => [
                'label' => __('اسلاگ (اختیاری)', 'escapezoom-core'),
                'type'  => 'text',
                'hint'  => __('برای ساخت صفحه آرشیو، slug را به زبان انگلیسی پر کنید.', 'escapezoom-core'),
            ],
            'is_active' => ['label' => __('فعال', 'escapezoom-core'), 'type' => 'checkbox'],
        ];
    }

    protected static function getOrderBy(): array
    {
        return ['id' => 'asc'];
    }

    protected static function getAddTitle(): string
    {
        return __('افزودن نوع بازی', 'escapezoom-core');
    }

    protected static function getEditTitle(): string
    {
        return __('ویرایش نوع بازی', 'escapezoom-core');
    }

    protected static function getShowAddButton(): bool
    {
        return true;
    }

    protected static function getModalAjaxSaveAction(): string
    {
        return 'ez_gt_ajax_save';
    }

    protected static function getModalAjaxUpdateAction(): ?string
    {
        return 'ez_gt_ajax_update';
    }

    protected static function getModalRefreshMode(): string
    {
        return 'htmx';
    }

    protected static function getModalRefreshEventName(): ?string
    {
        return 'refreshGameTypesTable';
    }

    protected static function getModalDialogId(): string
    {
        return 'ez-gt-form-dialog';
    }

    protected static function getModalFormId(): string
    {
        return 'ez-gt-form-dialog-form';
    }

    protected static function supportsModalEdit(): bool
    {
        return true;
    }

    /** @inheritdoc */
    protected static function getRowDataForModal($row): array
    {
        return [
            'id'       => (int) ($row->id ?? 0),
            'title'    => (string) ($row->title ?? ''),
            'slug'     => (string) ($row->slug ?? ''),
            'is_active' => isset($row->is_active) ? (bool) $row->is_active : true,
        ];
    }

    protected static function renderList(): void
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ez_game_types';
        $items = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id ASC");

        echo '<div class="wrap" id="ez-games-table-container">';
        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        if (static::getShowAddButton()) {
            echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
        }
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped" 
                     id="ez-games-table"
                     hx-trigger="refreshGameTypesTable from:body"
                     hx-get="' . esc_url(admin_url('admin-ajax.php')) . '?action=ez_gt_refresh_table"
                     hx-target="#ez-games-table-container"
                     hx-swap="innerHTML"><thead><tr>';
        echo '<th>ID</th>';
        echo '<th>' . esc_html__('عنوان', 'escapezoom-core') . '</th>';
        echo '<th>Slug</th>';
        echo '<th></th></tr></thead><tbody>';
        foreach ($items as $row) {
            $rowData = static::getRowDataForModal($row);
            $dataAttrs = '';
            foreach ($rowData as $k => $v) {
                $dataAttrs .= ' data-' . esc_attr((string) $k) . '="' . esc_attr(is_bool($v) ? ($v ? '1' : '0') : (string) $v) . '"';
            }
            $rowId = (int) $row->id;
            echo '<tr>';
            echo '<td>' . $rowId . '</td>';
            echo '<td>' . esc_html($row->title ?? '') . '</td>';
            echo '<td>' . esc_html($row->slug ?? '') . '</td>';
            echo '<td><a href="#" class="ez-crud-modal-edit"' . $dataAttrs . '>' . esc_html__('ویرایش', 'escapezoom-core') . '</a> | ';
            echo '<a href="' . esc_url(static::deleteUrl($rowId)) . '" onclick="return confirm(\'' . esc_attr__('حذف شود؟', 'escapezoom-core') . '\');">' . esc_html__('حذف', 'escapezoom-core') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
        static::renderCrudModal();
    }

    protected static function gatherFormData(): array
    {
        $data = parent::gatherFormData();
        if (isset($data['title']) && (string) ($data['slug'] ?? '') === '' && (string) $data['title'] !== '') {
            $data['slug'] = sanitize_title((string) $data['title']);
        }
        return $data;
    }

    /**
     * AJAX handler for table refresh (HTMX). فقط فرگمنت داخل #ez-games-table-container برمی‌گردد (بدون مودال).
     */
    public static function ajaxRefreshTable(): void
    {
        static::assertAjaxCapability();
        static::assertAdminFragmentNonceHtml();

        global $wpdb;
        $table_name = $wpdb->prefix . 'ez_game_types';
        $items = $wpdb->get_results("SELECT * FROM {$table_name} ORDER BY id ASC");

        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        if (static::getShowAddButton()) {
            echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
        }
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped"
                     hx-trigger="refreshGameTypesTable from:body"
                     hx-get="' . esc_url(admin_url('admin-ajax.php')) . '?action=ez_gt_refresh_table"
                     hx-target="#ez-games-table-container"
                     hx-swap="innerHTML"><thead><tr>';
        echo '<th>ID</th>';
        echo '<th>' . esc_html__('عنوان', 'escapezoom-core') . '</th>';
        echo '<th>Slug</th>';
        echo '<th></th></tr></thead><tbody>';
        foreach ($items as $row) {
            $rowData = static::getRowDataForModal($row);
            $dataAttrs = '';
            foreach ($rowData as $k => $v) {
                $dataAttrs .= ' data-' . esc_attr((string) $k) . '="' . esc_attr(is_bool($v) ? ($v ? '1' : '0') : (string) $v) . '"';
            }
            $rowId = (int) $row->id;
            echo '<tr>';
            echo '<td>' . $rowId . '</td>';
            echo '<td>' . esc_html($row->title ?? '') . '</td>';
            echo '<td>' . esc_html($row->slug ?? '') . '</td>';
            echo '<td><a href="#" class="ez-crud-modal-edit"' . $dataAttrs . '>' . esc_html__('ویرایش', 'escapezoom-core') . '</a> | ';
            echo '<a href="' . esc_url(static::deleteUrl($rowId)) . '" onclick="return confirm(\'' . esc_attr__('حذف شود؟', 'escapezoom-core') . '\');">' . esc_html__('حذف', 'escapezoom-core') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
        exit;
    }

    /**
     * AJAX handler for quick add (called via wp_ajax_ez_gt_ajax_save).
     */
    public static function ajaxSave(): void
    {
        static::assertAjaxCapability();
        static::assertAjaxNonce('ez_gt_ajax_save');

        $title = isset($_POST['title']) ? sanitize_text_field((string) $_POST['title']) : '';
        $slug  = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';

        global $wpdb;
        $table_name = $wpdb->prefix . 'ez_game_types';

        if ($title === '') {
            wp_send_json_error(['title' => __('عنوان الزامی است.', 'escapezoom-core')], 400);
        }

        // عنوان یونیک
        $titleExists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE title = %s", $title));
        if ($titleExists > 0) {
            wp_send_json_error(['title' => __('این عنوان قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        // اعتبارسنجی فرمت و یونیک بودن اسلاگ (در صورت پر بودن)
        if ($slug !== '') {
            if (!preg_match('/^[a-z0-9-]+$/', $slug)) {
                wp_send_json_error(['slug' => __('اسلاگ باید فقط شامل حروف انگلیسی کوچک، اعداد و خط تیره (-) باشد.', 'escapezoom-core')], 400);
            }
            $slugExists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE slug = %s", $slug));
            if ($slugExists > 0) {
                wp_send_json_error(['slug' => __('این اسلاگ قبلاً استفاده شده است.', 'escapezoom-core')], 400);
            }
        }

        // Insert into wp_ez_game_types table
        $result = $wpdb->insert(
            $table_name,
            [
                'title' => $title,
                'slug'  => $slug !== '' ? $slug : null,  // Store as NULL if empty
            ],
            ['%s', '%s']
        );

        if ($result === false) {
            wp_send_json_error(['message' => __('خطا در ذخیره در دیتابیس.', 'escapezoom-core')], 500);
        }

        $insert_id = $wpdb->insert_id;

        wp_send_json_success([
            'id'    => (int) $insert_id,
            'title' => $title,
            'slug'  => $slug,
        ]);
    }

    /**
     * AJAX handler for update (مودال ویرایش).
     */
    public static function ajaxUpdate(): void
    {
        static::assertAjaxCapability();
        static::assertAjaxNonce('ez_gt_ajax_update');

        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $title = isset($_POST['title']) ? sanitize_text_field((string) $_POST['title']) : '';
        $slug = isset($_POST['slug']) ? sanitize_title((string) $_POST['slug']) : '';

        if ($id < 1) {
            wp_send_json_error(['general' => __('شناسه رکورد نامعتبر است.', 'escapezoom-core')], 400);
        }
        if ($title === '') {
            wp_send_json_error(['title' => __('عنوان الزامی است.', 'escapezoom-core')], 400);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'ez_game_types';
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE id = %d", $id));
        if (!$exists) {
            wp_send_json_error(['general' => __('رکورد یافت نشد.', 'escapezoom-core')], 404);
        }

        // عنوان یونیک (به‌جز رکورد فعلی)
        $titleExists = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table_name} WHERE title = %s AND id != %d", $title, $id));
        if ($titleExists > 0) {
            wp_send_json_error(['title' => __('این عنوان قبلاً استفاده شده است.', 'escapezoom-core')], 400);
        }

        if ($slug !== '' && !preg_match('/^[a-z0-9-]+$/', $slug)) {
            wp_send_json_error(['slug' => __('اسلاگ باید فقط شامل حروف انگلیسی کوچک، اعداد و خط تیره (-) باشد.', 'escapezoom-core')], 400);
        }
        if ($slug !== '') {
            $slugExists = (int) $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_name} WHERE slug = %s AND id != %d", $slug, $id));
            if ($slugExists > 0) {
                wp_send_json_error(['slug' => __('این اسلاگ قبلاً استفاده شده است.', 'escapezoom-core')], 400);
            }
        }

        $updated = $wpdb->update(
            $table_name,
            ['title' => $title, 'slug' => $slug],
            ['id' => $id],
            ['%s', '%s'],
            ['%d']
        );

        if ($updated === false) {
            wp_send_json_error(['message' => __('خطا در به‌روزرسانی.', 'escapezoom-core')], 500);
        }

        wp_send_json_success(['id' => $id, 'title' => $title, 'slug' => $slug]);
    }
}
