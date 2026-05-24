<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Brands\PostType;

/**
 * Meta boxes for ez_brand CPT.
 * Data is synced to wp_ez_brands via EZ_Brands_DB on save.
 */
final class EZ_Brands_Metaboxes
{
    private static bool $registered = false;

    public static function register(): void
    {
        if (self::$registered) {
            return;
        }
        self::$registered = true;

        add_action('add_meta_boxes_' . EZ_Brands_CPT::POST_TYPE, [self::class, 'add_meta_boxes']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueue_assets']);
    }

    public static function add_meta_boxes(): void
    {
        add_meta_box(
            'ez_brand_details',
            __('جزئیات برند', 'escapezoom-core'),
            [self::class, 'render_details_metabox'],
            EZ_Brands_CPT::POST_TYPE,
            'normal',
            'high'
        );
    }

    public static function enqueue_assets(string $hook): void
    {
        global $post_type;
        if (!in_array($hook, ['post.php', 'post-new.php'], true) || $post_type !== EZ_Brands_CPT::POST_TYPE) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_editor();
        wp_enqueue_script('editor');
        wp_enqueue_style('editor-buttons');

        wp_add_inline_script('jquery', self::get_brand_media_script());
    }

    private static function get_brand_media_script(): string
    {
        return <<<'JS'
jQuery(function($) {
    // Logo
    $('#ez-brand-logo-upload').on('click', function(e) {
        e.preventDefault();
        var f = wp.media({ title: 'انتخاب لوگو', button: { text: 'انتخاب' }, multiple: false });
        f.on('select', function() {
            var att = f.state().get('selection').first().toJSON();
            $('#ez_brand_logo').val(att.url);
            $('#ez-brand-logo-preview').html(att.url ? '<img src="' + att.url + '" style="max-width:150px;height:auto;">' : '');
        });
        f.open();
    });
    $('#ez-brand-logo-remove').on('click', function(e) {
        e.preventDefault();
        $('#ez_brand_logo').val('');
        $('#ez-brand-logo-preview').html('');
    });
    // Thumbnail
    $('#ez-brand-thumbnail-upload').on('click', function(e) {
        e.preventDefault();
        var f = wp.media({ title: 'انتخاب تصویر شاخص', button: { text: 'انتخاب' }, multiple: false });
        f.on('select', function() {
            var att = f.state().get('selection').first().toJSON();
            $('#ez_brand_thumbnail_id').val(att.id);
            var url = att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;
            $('#ez-brand-thumbnail-preview').html('<img src="' + url + '" style="max-width:150px;height:auto;">');
        });
        f.open();
    });
    $('#ez-brand-thumbnail-remove').on('click', function(e) {
        e.preventDefault();
        $('#ez_brand_thumbnail_id').val('');
        $('#ez-brand-thumbnail-preview').html('');
    });
});
JS;
    }

    public static function render_details_metabox(\WP_Post $post): void
    {
        wp_nonce_field('ez_brand_save', 'ez_brand_nonce');

        $brand = EZ_Brands_DB::get_by_post_id((int) $post->ID);
        $logo = $brand ? ($brand->logo ?? '') : '';
        $thumbnail_id = 0;
        $thumbnail_url = '';
        if ($brand && !empty($brand->thumbnail_url)) {
            $thumbnail_url = $brand->thumbnail_url;
        }
        if ($brand && !empty($brand->thumbnail_id)) {
            $thumbnail_id = (int) $brand->thumbnail_id;
            if (!$thumbnail_url && $thumbnail_id) {
                $thumbnail_url = wp_get_attachment_image_url($thumbnail_id, 'thumbnail') ?: '';
            }
        }
        $description = $brand ? ($brand->description ?? '') : '';
        $address = $brand ? ($brand->address ?? '') : '';
        $phone = $brand ? ($brand->phone ?? '') : '';
        $instagram = $brand ? ($brand->instagram ?? '') : '';
        $website = $brand ? ($brand->website ?? '') : '';
        $established_year = $brand ? ($brand->established_year ?? '') : '';
        $score = $brand ? (float) ($brand->score ?? 0) : 0.0;
        $reputation = $brand ? (int) ($brand->reputation ?? 0) : 0;
        $game_types = $brand ? ($brand->game_types ?? null) : null;
        $teams = $brand ? ($brand->teams ?? null) : null;
        if (is_string($game_types)) {
            $game_types = json_decode($game_types, true);
        }
        if (is_string($teams)) {
            $teams = json_decode($teams, true);
        }
        $game_types = is_array($game_types) ? $game_types : [];
        $teams = is_array($teams) ? $teams : [];
        ?>
        <table class="form-table" role="presentation">
            <tr>
                <th scope="row"><label for="ez_brand_logo"><?php esc_html_e('لوگو (URL)', 'escapezoom-core'); ?></label></th>
                <td>
                    <input type="url" name="ez_brand_logo" id="ez_brand_logo" class="large-text" value="<?php echo esc_attr($logo); ?>" dir="ltr">
                    <button type="button" class="button" id="ez-brand-logo-upload"><?php esc_html_e('انتخاب تصویر', 'escapezoom-core'); ?></button>
                    <button type="button" class="button" id="ez-brand-logo-remove"><?php esc_html_e('حذف', 'escapezoom-core'); ?></button>
                    <div id="ez-brand-logo-preview" style="margin-top:8px;"><?php if ($logo) { ?><img src="<?php echo esc_url($logo); ?>" style="max-width:150px;height:auto;"><?php } ?></div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label><?php esc_html_e('تصویر شاخص', 'escapezoom-core'); ?></label></th>
                <td>
                    <input type="hidden" name="ez_brand_thumbnail_id" id="ez_brand_thumbnail_id" value="<?php echo esc_attr((string) $thumbnail_id); ?>">
                    <button type="button" class="button" id="ez-brand-thumbnail-upload"><?php esc_html_e('انتخاب تصویر', 'escapezoom-core'); ?></button>
                    <button type="button" class="button" id="ez-brand-thumbnail-remove"><?php esc_html_e('حذف', 'escapezoom-core'); ?></button>
                    <div id="ez-brand-thumbnail-preview" style="margin-top:8px;"><?php if ($thumbnail_url) { ?><img src="<?php echo esc_url($thumbnail_url); ?>" style="max-width:150px;height:auto;"><?php } ?></div>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_description"><?php esc_html_e('توضیحات', 'escapezoom-core'); ?></label></th>
                <td>
                    <?php
                    wp_editor($description, 'ez_brand_description', [
                        'textarea_name' => 'ez_brand_description',
                        'textarea_rows' => 10,
                        'media_buttons' => true,
                        'teeny' => false,
                        'quicktags' => true,
                    ]);
                    ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_address"><?php esc_html_e('آدرس', 'escapezoom-core'); ?></label></th>
                <td><input type="text" name="ez_brand_address" id="ez_brand_address" class="large-text" value="<?php echo esc_attr($address); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_phone"><?php esc_html_e('تلفن', 'escapezoom-core'); ?></label></th>
                <td><input type="text" name="ez_brand_phone" id="ez_brand_phone" class="regular-text" value="<?php echo esc_attr($phone); ?>"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_instagram"><?php esc_html_e('اینستاگرام', 'escapezoom-core'); ?></label></th>
                <td><input type="url" name="ez_brand_instagram" id="ez_brand_instagram" class="large-text" value="<?php echo esc_attr($instagram); ?>" dir="ltr"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_website"><?php esc_html_e('وب‌سایت', 'escapezoom-core'); ?></label></th>
                <td><input type="url" name="ez_brand_website" id="ez_brand_website" class="large-text" value="<?php echo esc_attr($website); ?>" dir="ltr"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_established_year"><?php esc_html_e('سال تأسیس', 'escapezoom-core'); ?></label></th>
                <td><input type="number" name="ez_brand_established_year" id="ez_brand_established_year" class="small-text" value="<?php echo esc_attr((string) $established_year); ?>" min="1300" max="1500" placeholder="1400"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_score"><?php esc_html_e('امتیاز', 'escapezoom-core'); ?></label></th>
                <td><input type="number" name="ez_brand_score" id="ez_brand_score" class="small-text" value="<?php echo esc_attr((string) $score); ?>" min="0" max="5" step="0.1"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_reputation"><?php esc_html_e('اعتبار', 'escapezoom-core'); ?></label></th>
                <td><input type="number" name="ez_brand_reputation" id="ez_brand_reputation" class="small-text" value="<?php echo esc_attr((string) $reputation); ?>" min="0"></td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_game_types"><?php esc_html_e('انواع بازی', 'escapezoom-core'); ?></label></th>
                <td>
                    <p class="description"><?php esc_html_e('هر خط یک مورد (یا آرایه JSON).', 'escapezoom-core'); ?></p>
                    <textarea name="ez_brand_game_types" id="ez_brand_game_types" class="large-text" rows="4"><?php echo esc_textarea(is_array($game_types) ? implode("\n", $game_types) : ''); ?></textarea>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="ez_brand_teams"><?php esc_html_e('تیم‌ها', 'escapezoom-core'); ?></label></th>
                <td>
                    <textarea name="ez_brand_teams" id="ez_brand_teams" class="large-text" rows="4"><?php echo esc_textarea(is_array($teams) ? implode("\n", $teams) : ''); ?></textarea>
                </td>
            </tr>
        </table>
        <?php
    }
}
