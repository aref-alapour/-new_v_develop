<?php
// SMS Templates Settings Page for WordPress Admin

// Register the settings page in the admin menu
add_action('admin_menu', function () {
    add_menu_page(
        'مدیریت قالب های پیامک',
        'قالب های پیامک',
        'manage_options',
        'sms-templates-settings',
        'sms_templates_settings_page',
        'dashicons-email-alt',
        62
    );
});

// Register the option to store SMS templates data
add_action('admin_init', function () {
    register_setting('sms_templates_settings_group', 'sms_templates_settings');

    // Add default templates if none exist
    if (empty(get_option('sms_templates_settings'))) {
        $default_templates = [
            1 => [
                'title' => 'پیام خوش‌آمدگویی',
                'content' => 'کاربر عزیز اسکیپ زوم، 
درخواست شما دریافت شد و در حال بررسی است.
لطفاً کمی صبور باشید، به‌زودی پشتیبان با شما تماس خواهد گرفت.
اسکیپ زوم؛ مرجع بازیهای گروهی
لغو 11',
                'created_at' => current_time('mysql')
            ],
            2 => [
                'title' => 'تایید رزرو',
                'content' => 'کاربر گرامی،
رزرو شما با موفقیت تایید شد.
زمان: {time}
تاریخ: {date}
اسکیپ زوم؛ مرجع بازیهای گروهی
لغو 11',
                'created_at' => current_time('mysql')
            ],
            3 => [
                'title' => 'یادآوری رزرو',
                'content' => 'کاربر عزیز،
یادآوری: رزرو شما فردا برگزار خواهد شد.
لطفاً به موقع حاضر شوید.
اسکیپ زوم؛ مرجع بازیهای گروهی
لغو 11',
                'created_at' => current_time('mysql')
            ]
        ];
        update_option('sms_templates_settings', $default_templates);
    }
});

// AJAX handler to add new template
add_action('wp_ajax_add_sms_template', 'add_sms_template_ajax');
function add_sms_template_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    check_ajax_referer('sms_templates_nonce', 'nonce');

    $title = sanitize_text_field($_POST['title']);
    $content = sanitize_textarea_field($_POST['content']);

    if (empty($title) || empty($content)) {
        wp_send_json_error('عنوان و محتوا نمی‌تواند خالی باشد.');
    }

    $templates = get_option('sms_templates_settings', []);
    $new_id = !empty($templates) ? max(array_keys($templates)) + 1 : 1;

    $templates[$new_id] = [
        'title' => $title,
        'content' => $content,
        'created_at' => current_time('mysql')
    ];

    update_option('sms_templates_settings', $templates);
    wp_send_json_success(['id' => $new_id, 'title' => $title, 'content' => $content]);
}

// AJAX handler to delete template
add_action('wp_ajax_delete_sms_template', 'delete_sms_template_ajax');
function delete_sms_template_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    check_ajax_referer('sms_templates_nonce', 'nonce');

    $template_id = intval($_POST['template_id']);
    $templates = get_option('sms_templates_settings', []);

    if (isset($templates[$template_id])) {
        unset($templates[$template_id]);
        update_option('sms_templates_settings', $templates);
        wp_send_json_success('قالب با موفقیت حذف شد.');
    } else {
        wp_send_json_error('قالب پیدا نشد.');
    }
}

// AJAX handler to update template
add_action('wp_ajax_update_sms_template', 'update_sms_template_ajax');
function update_sms_template_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    check_ajax_referer('sms_templates_nonce', 'nonce');

    $template_id = intval($_POST['template_id']);
    $title = sanitize_text_field($_POST['title']);
    $content = sanitize_textarea_field($_POST['content']);

    if (empty($title) || empty($content)) {
        wp_send_json_error('عنوان و محتوا نمی‌تواند خالی باشد.');
    }

    $templates = get_option('sms_templates_settings', []);

    if (isset($templates[$template_id])) {
        $templates[$template_id]['title'] = $title;
        $templates[$template_id]['content'] = $content;
        $templates[$template_id]['updated_at'] = current_time('mysql');

        update_option('sms_templates_settings', $templates);
        wp_send_json_success('قالب با موفقیت به‌روزرسانی شد.');
    } else {
        wp_send_json_error('قالب پیدا نشد.');
    }
}

// The settings page callback
function sms_templates_settings_page()
{
    // Check user permissions
    if (!current_user_can('manage_options')) {
        wp_die('شما دسترسی لازم برای این صفحه را ندارید.');
    }

    $templates = get_option('sms_templates_settings', []);
?>
    <div class="wrap">
        <h1 class="wp-heading-inline">مدیریت قالب های پیامک</h1>
        <a href="#" class="page-title-action" id="add-new-template">افزودن قالب جدید</a>

        <div id="template-form-modal" style="display: none;">
            <div class="modal-backdrop"></div>
            <div class="modal-content">
                <h2 id="modal-title">افزودن قالب جدید</h2>
                <form id="template-form">
                    <input type="hidden" id="template-id" value="">
                    <table class="form-table">
                        <tr>
                            <th scope="row"><label for="template-title">عنوان قالب</label></th>
                            <td><input type="text" id="template-title" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="template-content">محتوای پیامک</label></th>
                            <td>
                                <textarea id="template-content" rows="5" cols="50" class="large-text" required placeholder="محتوای پیامک خود را اینجا بنویسید..."></textarea>
                                <p class="description">می‌توانید از متغیرهای زیر استفاده کنید: {name}, {phone}, {date}, {time}</p>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <button type="submit" class="button button-primary" id="save-template">ذخیره قالب</button>
                        <button type="button" class="button" id="cancel-template">انصراف</button>
                    </p>
                </form>
            </div>
        </div>

        <div class="tablenav top">
            <div class="alignleft actions">
                <p class="description">در این بخش می‌توانید قالب‌های پیامک خود را مدیریت کنید. این قالب‌ها در صفحه پیامک CRM نمایش داده خواهند شد.</p>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-cb check-column">
                        <input type="checkbox" id="cb-select-all-1">
                    </th>
                    <th scope="col" class="manage-column column-title">عنوان</th>
                    <th scope="col" class="manage-column column-content">محتوای پیامک</th>
                    <th scope="col" class="manage-column column-date">تاریخ ایجاد</th>
                    <th scope="col" class="manage-column column-actions">عملیات</th>
                </tr>
            </thead>
            <tbody id="templates-list">
                <?php if (empty($templates)): ?>
                    <tr class="no-items">
                        <td class="colspanchange" colspan="5">هیچ قالبی یافت نشد. <a href="#" id="add-first-template">اولین قالب خود را ایجاد کنید</a>.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($templates as $id => $template): ?>
                        <tr data-template-id="<?php echo $id; ?>">
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="template[]" value="<?php echo $id; ?>">
                            </th>
                            <td class="column-title">
                                <strong><?php echo esc_html($template['title']); ?></strong>
                                <div class="row-actions">
                                    <span class="edit">
                                        <a href="#" class="edit-template" data-id="<?php echo $id; ?>">ویرایش</a> |
                                    </span>
                                    <span class="trash">
                                        <a href="#" class="delete-template" data-id="<?php echo $id; ?>" style="color: #a00;">حذف</a>
                                    </span>
                                </div>
                            </td>
                            <td class="column-content">
                                <div class="template-preview">
                                    <?php echo esc_html(wp_trim_words($template['content'], 10)); ?>
                                </div>
                            </td>
                            <td class="column-date">
                                <?php echo isset($template['created_at']) ? date_i18n('Y/m/d H:i', strtotime($template['created_at'])) : '---'; ?>
                            </td>
                            <td class="column-actions">
                                <button type="button" class="button button-small preview-template" data-content="<?php echo esc_attr($template['content']); ?>">پیش نمایش</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div id="preview-modal" style="display: none;">
        <div class="modal-backdrop"></div>
        <div class="modal-content">
            <h2>پیش نمایش قالب</h2>
            <div id="preview-content" style="background: #f9f9f9; padding: 15px; border: 1px solid #ddd; border-radius: 4px; font-family: Tahoma, Arial, sans-serif; direction: rtl;"></div>
            <p class="submit">
                <button type="button" class="button" id="close-preview">بستن</button>
            </p>
        </div>
    </div>

    <style>
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 100000;
        }

        .modal-content {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 4px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            z-index: 100001;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .template-preview {
            max-width: 300px;
            word-wrap: break-word;
        }

        #preview-content {
            white-space: pre-wrap;
            line-height: 1.6;
        }
    </style>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var nonce = '<?php echo wp_create_nonce('sms_templates_nonce'); ?>';

            // Add new template modal
            $('#add-new-template, #add-first-template').on('click', function(e) {
                e.preventDefault();
                $('#modal-title').text('افزودن قالب جدید');
                $('#template-id').val('');
                $('#template-title').val('');
                $('#template-content').val('');
                $('#template-form-modal').show();
            });

            // Edit template
            $('.edit-template').on('click', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var row = $('tr[data-template-id="' + id + '"]');
                var title = row.find('.column-title strong').text();

                // Get content via AJAX or from data attribute
                $.post(ajaxurl, {
                    action: 'get_sms_template',
                    template_id: id,
                    nonce: nonce
                }, function(response) {
                    if (response.success) {
                        $('#modal-title').text('ویرایش قالب');
                        $('#template-id').val(id);
                        $('#template-title').val(title);
                        $('#template-content').val(response.data.content);
                        $('#template-form-modal').show();
                    }
                });
            });

            // Preview template
            $('.preview-template').on('click', function(e) {
                e.preventDefault();
                var content = $(this).data('content');
                $('#preview-content').text(content);
                $('#preview-modal').show();
            });

            // Close modals
            $('#cancel-template, .modal-backdrop, #close-preview').on('click', function() {
                $('#template-form-modal, #preview-modal').hide();
            });

            // Save template
            $('#template-form').on('submit', function(e) {
                e.preventDefault();
                var templateId = $('#template-id').val();
                var title = $('#template-title').val();
                var content = $('#template-content').val();
                var action = templateId ? 'update_sms_template' : 'add_sms_template';

                var data = {
                    action: action,
                    title: title,
                    content: content,
                    nonce: nonce
                };

                if (templateId) {
                    data.template_id = templateId;
                }

                $.post(ajaxurl, data, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('خطا: ' + response.data);
                    }
                });
            });

            // Delete template
            $('.delete-template').on('click', function(e) {
                e.preventDefault();
                if (!confirm('آیا مطمئن هستید که می‌خواهید این قالب را حذف کنید؟')) {
                    return;
                }

                var id = $(this).data('id');
                $.post(ajaxurl, {
                    action: 'delete_sms_template',
                    template_id: id,
                    nonce: nonce
                }, function(response) {
                    if (response.success) {
                        $('tr[data-template-id="' + id + '"]').fadeOut(function() {
                            $(this).remove();
                            if ($('#templates-list tr').length === 0) {
                                $('#templates-list').html('<tr class="no-items"><td class="colspanchange" colspan="5">هیچ قالبی یافت نشد. <a href="#" id="add-first-template">اولین قالب خود را ایجاد کنید</a>.</td></tr>');
                            }
                        });
                    } else {
                        alert('خطا: ' + response.data);
                    }
                });
            });
        });
    </script>
<?php
}

// AJAX handler to get single template (for editing)
add_action('wp_ajax_get_sms_template', 'get_sms_template_ajax');
function get_sms_template_ajax()
{
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }

    check_ajax_referer('sms_templates_nonce', 'nonce');

    $template_id = intval($_POST['template_id']);
    $templates = get_option('sms_templates_settings', []);

    if (isset($templates[$template_id])) {
        wp_send_json_success($templates[$template_id]);
    } else {
        wp_send_json_error('قالب پیدا نشد.');
    }
}
