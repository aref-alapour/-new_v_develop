<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

use Illuminate\Database\Eloquent\Model;

/**
 * Base CRUD screen: list, add, edit, delete with nonce and capability.
 * Subclasses define: page slug, nonces, model class, list columns, form fields, labels.
 */
abstract class BaseCrudScreen extends BaseScreen
{
    abstract protected static function getPageSlug(): string;
    abstract protected static function getNonceAction(): string;
    abstract protected static function getNonceDelete(): string;
    /** @var class-string<Model> */
    abstract protected static function getModelClass(): string;
    abstract protected static function getListTitle(): string;
    /** @return array<string, string|array{label: string, callback?: callable}> */
    abstract protected static function getListColumns(): array;
    /** @return array<string, array{label: string, type: string, required?: bool, options?: array|callable}> */
    abstract protected static function getFormFields(): array;

    /**
     * Whether "add" should open classic separate form instead of modal dialog.
     * When useCrudModal() is true, add always goes to list and modal is used.
     */
    protected static function useAddForm(): bool
    {
        return !static::useCrudModal();
    }

    protected static function getAddTitle(): string
    {
        return __('افزودن', 'escapezoom-core');
    }

    protected static function getEditTitle(): string
    {
        return __('ویرایش', 'escapezoom-core');
    }

    /** @return array<string, 'asc'|'desc'> */
    protected static function getOrderBy(): array
    {
        return ['id' => 'asc'];
    }

    /** @return string[] Relation names to eager-load for list (e.g. ['city']) */
    protected static function getListWith(): array
    {
        return [];
    }

    /** آیا دکمه/لینک «افزودن» در لیست نمایش داده شود (صفحه‌های option-style می‌توانند false برگردانند). */
    protected static function getShowAddButton(): bool
    {
        return true;
    }

    // ---------- مودال CRUD متمرکز ----------

    /** نام اکشن wp_ajax برای ذخیره (افزودن). اگر خالی باشد این اسکرین از مودال استفاده نمی‌کند. */
    protected static function getModalAjaxSaveAction(): string
    {
        return '';
    }

    /** نام اکشن wp_ajax برای به‌روزرسانی (ویرایش در مودال). null = فقط افزودن. */
    protected static function getModalAjaxUpdateAction(): ?string
    {
        return null;
    }

    /** پس از ذخیره: 'reload' یا 'htmx'. */
    protected static function getModalRefreshMode(): string
    {
        return 'reload';
    }

    /** اگر refreshMode === 'htmx'، نام رویداد برای htmx.trigger(body, eventName). */
    protected static function getModalRefreshEventName(): ?string
    {
        return null;
    }

    /** id یکتا برای المنت مودال. */
    protected static function getModalDialogId(): string
    {
        return 'ez-crud-modal-' . str_replace('_', '-', static::getPageSlug());
    }

    /** id فرم داخل مودال. */
    protected static function getModalFormId(): string
    {
        return static::getModalDialogId() . '-form';
    }

    /** عنوان مودال در حالت افزودن. */
    protected static function getModalAddTitle(): string
    {
        return static::getAddTitle();
    }

    /** عنوان مودال در حالت ویرایش. */
    protected static function getModalEditTitle(): string
    {
        return static::getEditTitle();
    }

    /** آیا لینک «ویرایش» مودال را با دادهٔ ردیف باز کند؟ */
    protected static function supportsModalEdit(): bool
    {
        return false;
    }

    /** استفاده از مودال متمرکز (یک نقطهٔ واحد برای HTML و JS). */
    protected static function useCrudModal(): bool
    {
        return static::getModalAjaxSaveAction() !== '';
    }

    /**
     * دادهٔ ردیف برای پر کردن مودال ویرایش (data-* روی لینک ویرایش).
     * @param object $row مدل یا stdClass ردیف جدول
     * @return array<string, mixed>
     */
    protected static function getRowDataForModal($row): array
    {
        $out = ['id' => $row->id ?? 0];
        foreach (array_keys(static::getFormFields()) as $key) {
            $def = static::getFormFields()[$key];
            $type = $def['type'] ?? 'text';
            $out[$key] = $row->{$key} ?? ($type === 'checkbox' ? true : '');
        }
        return $out;
    }

    protected static function getNonceName(): string
    {
        return 'ez_crud_nonce';
    }

    protected static function dispatch(): void
    {
        $action = isset($_GET['action']) ? sanitize_key($_GET['action']) : 'list';
        $id = isset($_GET['id']) ? absint($_GET['id']) : 0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[static::getNonceName()])) {
            if (!wp_verify_nonce(sanitize_text_field((string) $_POST[static::getNonceName()]), static::getNonceAction())) {
                wp_die(esc_html__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
            }
            static::handleSave();
            return;
        }

        if ($action === 'delete' && $id > 0 && isset($_GET['_wpnonce'])) {
            if (!wp_verify_nonce(sanitize_text_field((string) $_GET['_wpnonce']), static::getNonceDelete() . $id)) {
                wp_die(esc_html__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'));
            }
            $modelClass = static::getModelClass();
            $modelClass::query()->where('id', $id)->delete();
            wp_safe_redirect(static::listUrl());
            exit;
        }

        if ($action === 'add') {
            if (!static::useAddForm()) {
                // For modal-based screens, redirect to list URL; JS will handle opening the dialog.
                wp_safe_redirect(static::listUrl());
                exit;
            }
            static::renderForm(null);
            return;
        }
        if ($action === 'edit' && $id > 0) {
            $modelClass = static::getModelClass();
            $item = $modelClass::query()->find($id);
            if (!$item) {
                wp_die(esc_html__('رکورد یافت نشد.', 'escapezoom-core'));
            }
            static::renderForm($item);
            return;
        }
        static::renderList();
    }

    protected static function listUrl(): string
    {
        return admin_url('admin.php?page=' . static::getPageSlug());
    }

    protected static function addUrl(): string
    {
        return admin_url('admin.php?page=' . static::getPageSlug() . '&action=add');
    }

    protected static function editUrl(int $id): string
    {
        return admin_url('admin.php?page=' . static::getPageSlug() . '&action=edit&id=' . $id);
    }

    protected static function deleteUrl(int $id): string
    {
        return wp_nonce_url(
            admin_url('admin.php?page=' . static::getPageSlug() . '&action=delete&id=' . $id),
            static::getNonceDelete() . $id
        );
    }

    protected static function handleSave(): void
    {
        $modelClass = static::getModelClass();
        $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
        $data = static::gatherFormData();
        $fillable = (new $modelClass())->getFillable();
        $data = array_intersect_key($data, array_flip($fillable));

        if ($id > 0) {
            $model = $modelClass::query()->find($id);
            if ($model instanceof Model) {
                $model->fill($data)->save();
            }
        } else {
            $modelClass::query()->create($data);
        }
        wp_safe_redirect(static::listUrl());
        exit;
    }

    /** @return array<string, mixed> */
    protected static function gatherFormData(): array
    {
        $out = [];
        foreach (static::getFormFields() as $name => $def) {
            $type = $def['type'] ?? 'text';
            if ($type === 'checkbox') {
                $out[$name] = isset($_POST[$name]) && $_POST[$name] !== '' && $_POST[$name] !== '0';
                continue;
            }
            if (!isset($_POST[$name])) {
                continue;
            }
            $raw = $_POST[$name];
            if ($type === 'number') {
                $out[$name] = is_numeric($raw) ? (int) $raw : 0;
            } elseif ($type === 'select') {
                $out[$name] = $raw === '' ? 0 : (is_numeric($raw) ? (int) $raw : sanitize_text_field((string) $raw));
            } elseif ($type === 'textarea') {
                $out[$name] = sanitize_textarea_field((string) $raw);
            } elseif ($type === 'editor') {
                $out[$name] = wp_kses_post(wp_unslash((string) $raw));
            } else {
                $out[$name] = sanitize_text_field((string) $raw);
            }
        }
        return $out;
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
        $useModal = static::useCrudModal();
        $supportEdit = $useModal && static::supportsModalEdit();

        echo '<div class="wrap">';
        echo '<h1 class="wp-heading-inline">' . esc_html(static::getListTitle()) . '</h1>';
        if (static::getShowAddButton()) {
            if ($useModal) {
                echo ' <a href="#" class="page-title-action ez-crud-modal-open" data-dialog-id="' . esc_attr(static::getModalDialogId()) . '">' . esc_html(static::getAddTitle()) . '</a>';
            } else {
                echo ' <a href="' . esc_url(static::addUrl()) . '" class="page-title-action">' . esc_html(static::getAddTitle()) . '</a>';
            }
        }
        echo '<hr class="wp-header-end">';
        echo '<table class="wp-list-table widefat fixed striped"><thead><tr>';
        echo '<th>ID</th>';
        foreach ($columns as $key => $col) {
            $label = is_array($col) ? ($col['label'] ?? $key) : $col;
            echo '<th>' . esc_html($label) . '</th>';
        }
        echo '<th></th></tr></thead><tbody>';
        foreach ($items as $row) {
            echo '<tr>';
            echo '<td>' . (int) $row->id . '</td>';
            foreach (array_keys($columns) as $key) {
                $col = $columns[$key];
                $label = is_array($col) ? ($col['label'] ?? $key) : $col;
                $callback = is_array($col) && isset($col['callback']) ? $col['callback'] : null;
                $value = $callback ? $callback($row) : ($row->{$key} ?? '');
                if (is_bool($value)) {
                    $value = $value ? '✓' : '—';
                }
                echo '<td>' . esc_html((string) $value) . '</td>';
            }
            $rowId = (int) $row->id;
            echo '<td>';
            if ($supportEdit) {
                $rowData = static::getRowDataForModal($row);
                $dataAttrs = '';
                foreach ($rowData as $k => $v) {
                    $dataAttrs .= ' data-' . esc_attr((string) $k) . '="' . esc_attr(is_bool($v) ? ($v ? '1' : '0') : (string) $v) . '"';
                }
                echo '<a href="#" class="ez-crud-modal-edit"' . $dataAttrs . '>' . esc_html__('ویرایش', 'escapezoom-core') . '</a>';
            } else {
                echo '<a href="' . esc_url(static::editUrl($rowId)) . '">' . esc_html__('ویرایش', 'escapezoom-core') . '</a>';
            }
            echo ' | ';
            echo '<a href="' . esc_url(static::deleteUrl($rowId)) . '" onclick="return confirm(\'' . esc_attr__('حذف شود؟', 'escapezoom-core') . '\');">' . esc_html__('حذف', 'escapezoom-core') . '</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table></div>';
        if ($useModal) {
            static::renderCrudModal();
        }
    }

    /**
     * رندر مودال CRUD یکسان (DaisyUI + Alpine). فقط وقتی useCrudModal() true باشد فراخوانی می‌شود.
     */
    protected static function renderCrudModal(): void
    {
        if (!static::useCrudModal()) {
            return;
        }
        $fields = static::getFormFields();
        $fieldNames = array_keys($fields);
        $checkboxFields = [];
        foreach ($fields as $name => $def) {
            if (($def['type'] ?? 'text') === 'checkbox') {
                $checkboxFields[] = $name;
            }
        }
        $saveAction = static::getModalAjaxSaveAction();
        $updateAction = static::getModalAjaxUpdateAction();
        $config = [
            'dialogId'         => static::getModalDialogId(),
            'formId'            => static::getModalFormId(),
            'ajaxUrl'           => admin_url('admin-ajax.php'),
            'saveAction'        => $saveAction,
            'updateAction'      => $updateAction,
            'saveNonce'         => wp_create_nonce($saveAction),
            'updateNonce'       => $updateAction ? wp_create_nonce($updateAction) : '',
            'refreshMode'       => static::getModalRefreshMode(),
            'refreshEventName'   => static::getModalRefreshEventName(),
            'addTitle'          => static::getModalAddTitle(),
            'editTitle'         => static::getModalEditTitle(),
            'fields'            => $fieldNames,
            'checkboxFields'     => $checkboxFields,
        ];
        $addTitleEsc = esc_html(static::getModalAddTitle());
        $editTitleEsc = esc_html(static::getModalEditTitle());
        echo '<script>window.ezCrudModalConfig = ' . wp_json_encode($config) . ';</script>';
        echo '<div x-data="window.EzCrudModal(window.ezCrudModalConfig)" class="ez-crud-modal-container">';
        echo '<div id="' . esc_attr(static::getModalDialogId()) . '" ';
        echo 'class="modal fixed inset-0 z-[100000] flex items-center justify-center bg-black/50" ';
        echo ':class="dialogOpen ? \'modal-open pointer-events-auto opacity-100\' : \'pointer-events-none opacity-0\'" role="dialog" aria-modal="true" ';
        echo 'aria-label="' . esc_attr($addTitleEsc) . '" @click.self="dialogOpen = false">';
        echo '<div class="w-[80%] max-w-[380px] bg-white rounded-xl shadow-xl px-5 py-4 max-h-[90vh] overflow-auto relative" @click.stop>';
        echo '<h3 class="m-0 mb-3 text-xl font-bold text-black" x-text="formData.id ? \'' . esc_js($editTitleEsc) . '\' : \'' . esc_js($addTitleEsc) . '\'"></h3>';
        echo '<form id="' . esc_attr(static::getModalFormId()) . '" @submit.prevent="submitForm()" class="pt-2 pb-0">';
        echo '<input type="hidden" name="id" x-model="formData.id">';
        static::renderModalFormFields([]);
        echo '<template x-if="errors.general"><div class="alert alert-error mt-4" x-text="errors.general"></div></template>';
        echo '<div class="flex items-center justify-end gap-3 mt-6 pt-5 border-t border-gray-200">';
        echo '<button type="button" class="btn btn-ghost bg-base-200 text-base-content/80 shadow-none border-none hover:bg-base-300 hover:text-base-content hover:shadow-none hover:border-none" @click="dialogOpen = false">' . esc_html__('انصراف', 'escapezoom-core') . '</button>';
        echo '<button type="submit"';
        echo ' class="btn btn-primary min-w-[110px] justify-center gap-2 disabled:opacity-70 disabled:bg-primary disabled:text-white disabled:cursor-not-allowed"';
        echo ' :disabled="submitting">';
        echo '  <span class="inline-flex items-center justify-center gap-2">';
        echo '    <span x-show="!submitting">' . esc_html__('ذخیره', 'escapezoom-core') . '</span>';
        echo '    <span x-show="submitting" x-cloak class="loading loading-spinner loading-sm"></span>';
        echo '  </span>';
        echo '</button>';
        echo '</div>';
        echo '</form>';
        echo '</div></div></div>';
        static::renderCrudModalScript();
    }

    /**
     * اسکریپت اتصال دکمه افزودن و لینک ویرایش به مودال (delegate روی body).
     */
    protected static function renderCrudModalScript(): void
    {
        $dialogId = esc_js(static::getModalDialogId());
        echo '<script>';
        echo "document.addEventListener('DOMContentLoaded', function() {";
        echo "function getCtx() { var el = document.querySelector('[x-data*=\"EzCrudModal\"]'); return el && el._x_dataStack && el._x_dataStack[0] ? el._x_dataStack[0] : null; }";
        echo "document.body.addEventListener('click', function(e) {";
        echo "var openBtn = e.target.closest('a.ez-crud-modal-open[data-dialog-id=\"" . $dialogId . "\"]');";
        echo "if (openBtn) { e.preventDefault(); var ctx = getCtx(); if (ctx && ctx.open) ctx.open(); return; }";
        echo "var editLink = e.target.closest('a.ez-crud-modal-edit');";
        echo "if (editLink && editLink.closest('.wrap')) {";
        echo "e.preventDefault(); var ctx = getCtx(); if (!ctx || !ctx.openEdit) return;";
        echo "var row = {}; for (var i = 0; i < editLink.attributes.length; i++) { var a = editLink.attributes[i]; if (a.name.startsWith('data-')) row[a.name.slice(5)] = a.value; }";
        echo "ctx.openEdit(row); }";
        echo "}); });";
        echo '</script>';
    }

    /**
     * رندر فیلدهای فرم مودال از getFormFields() با Alpine x-model.
     * @param array<string, mixed> $formData دادهٔ اولیه (استفاده نمی‌شود؛ مودال همیشه با Alpine است).
     */
    protected static function renderModalFormFields(array $formData = []): void
    {
        $fields = static::getFormFields();
        $names = array_keys($fields);
        $lastKey = end($names);
        foreach ($fields as $name => $def) {
            $isLast = ($name === $lastKey);
            $label = $def['label'] ?? $name;
            $type = $def['type'] ?? 'text';
            $required = !empty($def['required']);
            $hint = $def['hint'] ?? null;
            $placeholder = $def['placeholder'] ?? '';
            $mb = $isLast && !$hint ? 'mb-0' : 'mb-4';
            echo '<div class="form-control w-full ' . $mb . '">';

            $isCheckbox = ($type === 'checkbox');
            if (! $isCheckbox) {
                echo '<label class="label"><span class="label-text">' . esc_html($label);
                if ($required) {
                    echo ' <span class="text-error">*</span>';
                }
                echo '</span></label>';
            }

            if ($type === 'checkbox') {
                echo '<label class="label cursor-pointer justify-start gap-2">';
                echo '<input type="checkbox" name="' . esc_attr($name) . '" value="1" class="checkbox checkbox-primary checkbox-sm" x-model="formData.' . esc_attr($name) . '">';
                echo '<span class="label-text">';
                echo esc_html($label);
                if ($required) {
                    echo ' <span class="text-error">*</span>';
                }
                echo '</span></label>';
            } elseif ($type === 'select') {
                $options = $def['options'] ?? [];
                if (is_callable($options)) {
                    $options = $options();
                }
                $selectAttrs = 'name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="select select-bordered w-full rounded-md"' . ($required ? ' required' : '');
                echo '<select ' . $selectAttrs . ' x-model="formData.' . esc_attr($name) . '">';
                foreach ($options as $k => $v) {
                    echo '<option value="' . esc_attr((string) $k) . '">' . esc_html((string) $v) . '</option>';
                }
                echo '</select>';
            } else {
                $inputType = $type === 'number' ? 'number' : 'text';
                $extra = $type === 'number' ? ' min="0"' : '';
                $cls = 'input input-bordered w-full rounded-md';
                echo '<input type="' . esc_attr($inputType) . '" name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="' . esc_attr($cls) . '" placeholder="' . esc_attr($placeholder) . '" ' . $extra . ($required ? ' required' : '') . ' x-model="formData.' . esc_attr($name) . '">';
            }

            echo '<template x-if="errors.' . esc_attr($name) . '"><div class="text-error text-sm mt-1" x-text="errors.' . esc_attr($name) . '"></div></template>';
            if ($hint) {
                echo '<div class="text-sm text-error mt-2 p-3 bg-red-50 rounded-lg">' . esc_html($hint) . '</div>';
            }
            echo '</div>';
        }
    }

    /**
     * @param Model|null $item
     */
    protected static function renderForm($item): void
    {
        $isEdit = $item !== null;
        $title = $isEdit ? static::getEditTitle() : static::getAddTitle();
        echo '<div class="wrap"><h1>' . esc_html($title) . '</h1>';
        echo '<form method="post" action="">';
        wp_nonce_field(static::getNonceAction(), static::getNonceName());
        if ($isEdit && $item instanceof Model) {
            echo '<input type="hidden" name="id" value="' . (int) $item->getKey() . '">';
        }
        echo '<table class="form-table" role="presentation">';
        foreach (static::getFormFields() as $name => $def) {
            $label = $def['label'] ?? $name;
            $type = $def['type'] ?? 'text';
            $required = !empty($def['required']);
            $value = $isEdit && $item instanceof Model ? ($item->{$name} ?? '') : '';
            if ($type === 'checkbox') {
                $checked = $isEdit ? (bool) ($item->{$name} ?? false) : true;
                echo '<tr><th scope="row">' . esc_html($label) . '</th><td><label><input type="checkbox" name="' . esc_attr($name) . '" value="1" ' . ($checked ? 'checked' : '') . '> ' . esc_html($label) . '</label></td></tr>';
                continue;
            }
            if ($type === 'textarea') {
                echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th><td><textarea name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="large-text" rows="4"' . ($required ? ' required' : '') . '>' . esc_textarea((string) $value) . '</textarea></td></tr>';
                continue;
            }
            if ($type === 'editor') {
                echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th><td>';
                wp_editor((string) $value, $name, [
                    'textarea_name' => $name,
                    'textarea_rows' => 10,
                    'media_buttons' => true,
                    'teeny'         => false,
                    'quicktags'     => true,
                    'tinymce'       => ['toolbar1' => 'formatselect,bold,italic,link,unlink,bullist,numlist,blockquote'],
                ]);
                echo '</td></tr>';
                continue;
            }
            if ($type === 'select') {
                $options = $def['options'] ?? [];
                if (is_callable($options)) {
                    $options = $options();
                }
                echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th><td><select name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" class="regular-text"' . ($required ? ' required' : '') . '>';
                foreach ($options as $k => $v) {
                    $isEmpty = ($value === '' || $value === null || $value === 0);
                    $sel = ($isEmpty && $k === '') || ((string) $value === (string) $k) ? ' selected' : '';
                    echo '<option value="' . esc_attr((string) $k) . '"' . $sel . '>' . esc_html((string) $v) . '</option>';
                }
                echo '</select></td></tr>';
                continue;
            }
            $inputType = $type === 'number' ? 'number' : 'text';
            $extra = $type === 'number' ? ' min="0"' : '';
            echo '<tr><th scope="row"><label for="' . esc_attr($name) . '">' . esc_html($label) . '</label></th><td><input name="' . esc_attr($name) . '" id="' . esc_attr($name) . '" type="' . esc_attr($inputType) . '" value="' . esc_attr((string) $value) . '" class="regular-text"' . $extra . ($required ? ' required' : '') . '></td></tr>';
        }
        echo '</table>';
        submit_button($isEdit ? __('ذخیره', 'escapezoom-core') : __('افزودن', 'escapezoom-core'));
        echo ' <a href="' . esc_url(static::listUrl()) . '" class="button">' . esc_html__('انصراف', 'escapezoom-core') . '</a>';
        echo '</form></div>';
    }
}
