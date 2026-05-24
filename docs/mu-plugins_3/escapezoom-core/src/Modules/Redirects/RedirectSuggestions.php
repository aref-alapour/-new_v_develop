<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Redirects;

/**
 * پیشنهاد ریدایرکت پس از تغییر نامک (مشابه Yoast SEO).
 *
 * وقتی نامک برگه، نوشته، بازی یا برند تغییر کند، یک نوتیس ادمین نمایش داده می‌شود
 * با لینک «اضافه کردن ریدایرکت» برای ریدایرکت از آدرس قدیم به جدید.
 */
final class RedirectSuggestions
{
    private const TRANSIENT_OLD_SLUG_PREFIX = 'ez_redirect_old_slug_';
    private const TRANSIENT_SUGGESTIONS_KEY  = 'ez_redirect_suggestions_';
    private const POST_TYPES                 = ['post', 'page', 'ez_game', 'ez_brand'];

    public static function register(): void
    {
        add_action('pre_post_update', [self::class, 'storeOldSlug'], 10, 2);
        add_action('save_post', [self::class, 'onSavePost'], 20, 3);
        add_action('admin_notices', [self::class, 'renderSuggestionsPlaceholder'], 1);
        add_action('admin_notices', [self::class, 'showSuggestionsNotice'], 10);
        add_action('wp_ajax_ez_get_redirect_suggestions', [self::class, 'ajaxGetRedirectSuggestions']);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueInlineNoticeScript'], 10);
    }

    /**
     * قبل از به‌روزرسانی پست، نامک قبلی (فعلی در دیتابیس) را در ترنزینت ذخیره کن.
     */
    public static function storeOldSlug(int $post_id, array $data): void
    {
        $post = get_post($post_id);
        if (! $post || ! in_array($post->post_type, self::POST_TYPES, true)) {
            return;
        }
        $new_slug = isset($data['post_name']) ? $data['post_name'] : null;
        if ($new_slug !== null && $new_slug !== $post->post_name) {
            set_transient(
                self::TRANSIENT_OLD_SLUG_PREFIX . $post_id,
                $post->post_name,
                MINUTE_IN_SECONDS * 5
            );
        }
    }

    /**
     * پس از ذخیره پست، اگر نامک عوض شده پیشنهاد ریدایرکت اضافه کن.
     */
    public static function onSavePost(int $post_id, \WP_Post $post, bool $update): void
    {
        if (! $update || ! in_array($post->post_type, self::POST_TYPES, true)) {
            return;
        }

        $old_slug = get_transient(self::TRANSIENT_OLD_SLUG_PREFIX . $post_id);
        delete_transient(self::TRANSIENT_OLD_SLUG_PREFIX . $post_id);

        if ($old_slug === false || $old_slug === '' || $old_slug === $post->post_name) {
            return;
        }

        $new_slug = $post->post_name ?: '';
        if ($new_slug === '') {
            return;
        }

        $from_path = self::buildFromPath($post->post_type, $old_slug, $post_id);
        $to_url    = self::buildToUrl($post->post_type, $new_slug, $post_id);
        $label     = self::getLabel($post->post_type, $post->post_title);

        self::addSuggestion($from_path, $to_url, $label);
    }

    /**
     * برای بازی (محصول): نامک در ez_products ذخیره می‌شود؛ از داخل EZ_Games_DB صدا زده می‌شود.
     */
    public static function suggestProductRedirect(string $old_slug, string $new_slug, string $title = ''): void
    {
        if ($old_slug === '' || $new_slug === '' || $old_slug === $new_slug) {
            return;
        }
        $from_path = '/room/' . trim($old_slug, '/') . '/';
        $to_url    = '/room/' . trim($new_slug, '/') . '/';
        $label     = $title !== '' ? $title : sprintf(__('بازی: %s', 'escapezoom-core'), $new_slug);
        self::addSuggestion($from_path, $to_url, $label);
    }

    /**
     * برای برند (CPT یا فرم ادمین برندها): از EZ_Brands_DB یا EZ_Brands_Admin صدا زده می‌شود.
     */
    public static function suggestBrandRedirect(string $old_slug, string $new_slug, string $title = ''): void
    {
        if ($old_slug === '' || $new_slug === '' || $old_slug === $new_slug) {
            return;
        }
        $from_path = '/brand/' . trim($old_slug, '/') . '/';
        $to_url    = '/brand/' . trim($new_slug, '/') . '/';
        $label     = $title !== '' ? $title : sprintf(__('برند: %s', 'escapezoom-core'), $new_slug);
        self::addSuggestion($from_path, $to_url, $label);
    }

    /**
     * @param int $post_id Optional; for post type used to infer path prefix from permalink when option is empty.
     */
    private static function buildFromPath(string $post_type, string $old_slug, int $post_id = 0): string
    {
        $slug = trim($old_slug, '/');
        if ($post_type === 'ez_game') {
            return '/room/' . $slug . '/';
        }
        if ($post_type === 'ez_brand') {
            return '/brand/' . $slug . '/';
        }
        if ($post_type === 'post') {
            $prefix = RedirectAdmin::getBlogPrefix();
            if ($prefix === '' && $post_id > 0) {
                $permalink = get_permalink($post_id);
                if ($permalink) {
                    $path = (string) parse_url($permalink, PHP_URL_PATH);
                    $path = trim($path, '/');
                    if ($path !== '' && str_contains($path, '/')) {
                        $parent = dirname($path);
                        if ($parent !== '.' && $parent !== '') {
                            $prefix = '/' . rtrim($parent, '/');
                        }
                    }
                }
            }
            if ($prefix !== '') {
                return rtrim($prefix, '/') . '/' . $slug . '/';
            }
        }
        return '/' . $slug . '/';
    }

    /**
     * مسیر مقصد بدون دامنه؛ برای نوشته از permalink واقعی استفاده می‌شود تا /blog/ حفظ شود.
     */
    private static function buildToUrl(string $post_type, string $new_slug, int $post_id): string
    {
        $slug = trim($new_slug, '/');
        if ($post_type === 'ez_game') {
            return '/room/' . $slug . '/';
        }
        if ($post_type === 'ez_brand') {
            return '/brand/' . $slug . '/';
        }
        if ($post_type === 'page') {
            return '/' . $slug . '/';
        }
        if ($post_type === 'post' && $post_id > 0) {
            $permalink = get_permalink($post_id);
            if ($permalink) {
                $path = (string) parse_url($permalink, PHP_URL_PATH);
                if ($path !== '') {
                    $path = '/' . trim($path, '/');
                    return $path === '/' ? '/' . $slug . '/' : $path . (str_ends_with($path, '/') ? '' : '/');
                }
            }
            $prefix = RedirectAdmin::getBlogPrefix();
            if ($prefix !== '') {
                return rtrim($prefix, '/') . '/' . $slug . '/';
            }
            return '/' . $slug . '/';
        }
        return '/' . $slug . '/';
    }

    private static function getLabel(string $post_type, string $title): string
    {
        $titles = [
            'post'     => __('نوشته', 'escapezoom-core'),
            'page'     => __('برگه', 'escapezoom-core'),
            'ez_game'  => __('بازی', 'escapezoom-core'),
            'ez_brand' => __('برند', 'escapezoom-core'),
        ];
        $type_label = $titles[$post_type] ?? $post_type;
        return $title !== '' ? $type_label . ': ' . $title : $type_label;
    }

    private static function addSuggestion(string $from_path, string $to_url, string $label): void
    {
        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            return;
        }

        $key       = self::TRANSIENT_SUGGESTIONS_KEY . $user_id;
        $existing  = get_transient($key);
        $suggestions = is_array($existing) ? $existing : [];

        $suggestions[] = [
            'from_path' => $from_path,
            'to_url'    => $to_url,
            'label'     => $label,
        ];

        set_transient($key, $suggestions, MINUTE_IN_SECONDS * 15);
    }

    /**
     * لیست صفحاتی که نوتیس پیشنهاد ریدایرکت داخل placeholder نمایش داده می‌شود.
     */
    private static function isListScreenForPlaceholder(): bool
    {
        $screen = get_current_screen();
        if (! $screen) {
            return false;
        }
        $list_screens = ['edit-post', 'edit-page', 'edit-ez_game', 'edit-ez_brand'];
        return in_array($screen->id, $list_screens, true);
    }

    /**
     * خروجی HTML نوتیس پیشنهاد از آرایهٔ suggestions (بدون حذف ترنزینت).
     */
    private static function buildNoticeHtml(array $suggestions): string
    {
        if (count($suggestions) === 0) {
            return '';
        }
        $html = '<div class="notice notice-info is-dismissible"><p><strong>' . esc_html__('پیشنهاد ریدایرکت (تغییر نامک)', 'escapezoom-core') . '</strong></p><ul style="list-style:disc;margin-right:1.5em;">';
        foreach ($suggestions as $item) {
            $from_path = $item['from_path'] ?? '';
            $to_url    = $item['to_url'] ?? '';
            $label     = $item['label'] ?? '';
            if ($from_path === '' || $to_url === '') {
                continue;
            }
            $add_url = admin_url('admin-post.php');
            $add_url = add_query_arg([
                'action'    => 'ez_add_redirect_suggestion',
                'from_path' => $from_path,
                'to_url'    => $to_url,
                '_wpnonce'  => wp_create_nonce('ez_add_redirect_suggestion'),
            ], $add_url);
            $form_url = admin_url('admin.php?page=' . RedirectAdmin::PAGE_SLUG . '-add');
            $form_url = add_query_arg(['from_path' => $from_path, 'to_url' => $to_url], $form_url);
            $html .= '<li>' . esc_html($label);
            $html .= ' — <a href="' . esc_url($add_url) . '">' . esc_html__('اضافه کردن ریدایرکت (۳۰۱)', 'escapezoom-core') . '</a>';
            $html .= ' | <a href="' . esc_url($form_url) . '">' . esc_html__('ویرایش و سپس ذخیره', 'escapezoom-core') . '</a>';
            $html .= '</li>';
        }
        $html .= '</ul></div>';
        return $html;
    }

    /**
     * در صفحات لیست پست/برگه/بازی/برند: placeholder خالی یا پر از نوتیس (در لود اول).
     */
    public static function renderSuggestionsPlaceholder(): void
    {
        if (! self::isListScreenForPlaceholder()) {
            return;
        }
        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            echo '<div id="ez-redirect-suggestions-placeholder"></div>';
            return;
        }
        $key         = self::TRANSIENT_SUGGESTIONS_KEY . $user_id;
        $suggestions = get_transient($key);
        echo '<div id="ez-redirect-suggestions-placeholder">';
        if (is_array($suggestions) && count($suggestions) > 0) {
            echo self::buildNoticeHtml($suggestions);
            delete_transient($key);
        }
        echo '</div>';
    }

    public static function showSuggestionsNotice(): void
    {
        if (self::isListScreenForPlaceholder()) {
            return;
        }
        $screen = get_current_screen();
        if (! $screen || (strpos($screen->id, 'escapezoom') === false && strpos($screen->id, 'post') === false && strpos($screen->id, 'page') === false)) {
            return;
        }

        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            return;
        }

        $key         = self::TRANSIENT_SUGGESTIONS_KEY . $user_id;
        $suggestions = get_transient($key);
        if (! is_array($suggestions) || count($suggestions) === 0) {
            return;
        }

        echo self::buildNoticeHtml($suggestions);
        delete_transient($key);
    }

    /**
     * AJAX: برگرداندن پیشنهادهای ریدایرکت به صورت JSON و پاک کردن ترنزینت.
     */
    public static function ajaxGetRedirectSuggestions(): void
    {
        if (! current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'Unauthorized'], 403);
        }
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field((string) wp_unslash($_GET['_wpnonce'])) : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, 'ez_get_redirect_suggestions')) {
            wp_send_json_error(['message' => 'Forbidden'], 403);
        }
        $user_id = get_current_user_id();
        if ($user_id <= 0) {
            wp_send_json_success(['html' => '', 'suggestions' => []]);
        }
        $key         = self::TRANSIENT_SUGGESTIONS_KEY . $user_id;
        $suggestions = get_transient($key);
        $suggestions = is_array($suggestions) ? $suggestions : [];
        $html        = self::buildNoticeHtml($suggestions);
        if (count($suggestions) > 0) {
            delete_transient($key);
        }
        wp_send_json_success(['html' => $html, 'suggestions' => $suggestions]);
    }

    /**
     * اسکریپت نوتیس درجا بعد از موفقیت inline-edit (ویرایش سریع).
     */
    public static function enqueueInlineNoticeScript(string $hook): void
    {
        if ($hook !== 'edit.php') {
            return;
        }
        $screen = get_current_screen();
        if (! $screen || ! in_array($screen->id, ['edit-post', 'edit-page', 'edit-ez_game', 'edit-ez_brand'], true)) {
            return;
        }
        wp_enqueue_script('jquery');
        wp_add_inline_script('jquery', self::getInlineNoticeScript());
    }

    private static function getInlineNoticeScript(): string
    {
        $ajax_url = esc_js(admin_url('admin-ajax.php'));
        $nonce    = esc_js(wp_create_nonce('ez_get_redirect_suggestions'));
        return <<<JS
(function(){
	function isInlineSaveRequest(settings) {
		if (!settings || !settings.url) return false;
		if (settings.url.indexOf('admin-ajax') === -1) return false;
		var d = settings.data;
		if (typeof d === 'string' && d.indexOf('inline-save') !== -1) return true;
		if (d && typeof d === 'object' && d.action === 'inline-save') return true;
		return false;
	}
	jQuery(document).on('ajaxComplete', function(e, xhr, settings) {
		if (!isInlineSaveRequest(settings)) return;
		setTimeout(function() {
			var placeholder = document.getElementById('ez-redirect-suggestions-placeholder');
			if (!placeholder) return;
			jQuery.getJSON('{$ajax_url}', { action: 'ez_get_redirect_suggestions', _wpnonce: '{$nonce}' })
				.done(function(res) {
					if (res.success && res.data && res.data.html) {
						placeholder.innerHTML = res.data.html;
					}
				});
		}, 150);
	});
})();
JS;
    }
}
