<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Core;

use EscapeZoom\Core\Core\EzAdminAjaxConfig;

/**
 * Centralized Asset Manager for EscapeZoom Core
 * Handles all CSS, JS, and third-party library loading
 */
final class AssetManager
{
    private static string $plugin_root;
    private static string $version;

    public static function init(): void
    {
        // Path to main plugin file (escapezoom-core.php) for correct plugins_url() base
        $plugin_dir = dirname(__DIR__, 3);
        self::$plugin_root = $plugin_dir . '/escapezoom-core.php';
        self::$version = '1.0.0';

        // Enqueue assets for both admin and front-end
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminAssets']);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontAssets']);
        add_action('admin_footer', [self::class, 'renderDeleteConfirmModal'], 5);
    }

    /**
     * مودال تأیید حذف سراسری (DaisyUI) — برای لینک‌های دارای کلاس ez-delete-confirm.
     */
    public static function renderDeleteConfirmModal(): void
    {
        if (! is_admin()) {
            return;
        }
        $message = __('آیا از حذف مطمئن هستید؟', 'escapezoom-core');
        ?>
        <div id="ez-delete-confirm-modal" class="modal fixed inset-0 z-[100010] flex items-center justify-center bg-black/50 pointer-events-none opacity-0 transition-opacity" role="dialog" aria-modal="true" aria-labelledby="ez-delete-confirm-title">
            <div class="modal-box bg-base-100 shadow-xl rounded-2xl relative pointer-events-auto max-w-md" role="document">
                <h3 id="ez-delete-confirm-title" class="font-bold text-lg mb-2"><?php echo esc_html($message); ?></h3>
                <p class="text-base-content/80 mb-6"><?php esc_html_e('این عمل قابل بازگشت نیست.', 'escapezoom-core'); ?></p>
                <div class="flex gap-2 justify-end">
                    <button type="button" class="btn btn-ghost ez-delete-confirm-cancel"><?php esc_html_e('انصراف', 'escapezoom-core'); ?></button>
                    <a id="ez-delete-confirm-proceed" href="#" class="btn btn-error ez-delete-proceed-btn"><span class="ez-delete-proceed-label"><?php esc_html_e('حذف', 'escapezoom-core'); ?></span></a>
                </div>
            </div>
            <div class="modal-backdrop" style="position:absolute;inset:0" aria-hidden="true"></div>
        </div>
        <script>
        window.ezShowTableSkeleton = function(containerId) {
            if (!containerId) return;
            var container = typeof containerId === 'string' ? document.getElementById(containerId) : containerId;
            if (!container) return;
            var table = container.querySelector('table');
            var tbody = table ? table.querySelector('tbody') : null;
            var thead = table ? table.querySelector('thead') : null;
            if (!tbody || !thead) return;
            var colCount = thead.querySelectorAll('th').length;
            if (colCount <= 0) return;
            var skeletonRows = 6;
            var i, j, rowsHtml = '';
            for (i = 0; i < skeletonRows; i++) {
                rowsHtml += '<tr>';
                for (j = 0; j < colCount; j++) {
                    rowsHtml += '<td><span class="ez-skeleton-cell"></span></td>';
                }
                rowsHtml += '</tr>';
            }
            tbody.innerHTML = rowsHtml;
        };
        window.ezShowTableSkeletonAndRefresh = function(eventName) {
            if (!eventName) return;
            var containerIdByEvent = { ez_archive_map_refresh: 'ez-archive-map-table-container' };
            var containerId = containerIdByEvent[eventName];
            if (!containerId) {
                var triggerEl = document.querySelector('[hx-trigger*="' + eventName + '"]');
                if (triggerEl) {
                    var t = triggerEl.getAttribute('hx-target');
                    if (t && t.indexOf('#') === 0) containerId = t.slice(1);
                }
            }
            if (containerId) window.ezShowTableSkeleton(containerId);
            if (typeof htmx !== 'undefined' && htmx.trigger) htmx.trigger(document.body, eventName);
            else document.body.dispatchEvent(new CustomEvent(eventName));
        };
        (function(){
            var searchLabel = <?php echo wp_json_encode(__('جستجو', 'escapezoom-core')); ?>;
            var clearChar = '\u2715';
            window.ezInitSearchForm = function(container) {
                var root = container && container.nodeType ? container : document;
                var forms = root.querySelectorAll ? root.querySelectorAll('form.ez-table-search-form') : [];
                forms.forEach(function(form) {
                    var input = form.querySelector('input[name="search"]');
                    var btn = form.querySelector('.ez-search-submit-btn');
                    if (!input || !btn) return;
                    var hasValue = (input.value || '').trim() !== '';
                    if (hasValue) {
                        btn.textContent = clearChar;
                        btn.type = 'button';
                        btn.classList.add('ez-search-clear-btn');
                    } else {
                        btn.textContent = searchLabel;
                        btn.type = 'submit';
                        btn.classList.remove('ez-search-clear-btn');
                    }
                    btn.disabled = false;
                });
            };
            document.body.addEventListener('click', function(e) {
                var btn = e.target.closest('.ez-search-clear-btn');
                if (!btn) return;
                e.preventDefault();
                var form = btn.closest('form.ez-table-search-form');
                if (!form) return;
                var input = form.querySelector('input[name="search"]');
                if (input) input.value = '';
                btn.disabled = true;
                if (typeof htmx !== 'undefined' && htmx.trigger) htmx.trigger(form, 'submit');
            });
            document.body.addEventListener('htmx:beforeRequest', function(ev) {
                var elt = ev.detail && ev.detail.elt;
                if (!elt) return;
                var form = elt.closest && elt.closest('form.ez-table-search-form');
                if (form) {
                    var b = form.querySelector('.ez-search-submit-btn, .ez-search-clear-btn');
                    if (b) b.disabled = true;
                }
            });
            document.body.addEventListener('htmx:afterRequest', function(ev) {
                var elt = ev.detail && ev.detail.elt;
                if (!elt) return;
                var form = elt.closest && elt.closest('form.ez-table-search-form');
                if (form) {
                    var b = form.querySelector('.ez-search-submit-btn, .ez-search-clear-btn');
                    if (b) b.disabled = false;
                    window.ezInitSearchForm(form.parentElement);
                }
            });
            document.body.addEventListener('htmx:afterSwap', function(ev) {
                var target = ev.detail && ev.detail.target;
                if (target && target.id) window.ezInitSearchForm(target);
            });
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', function() { window.ezInitSearchForm(); });
            else window.ezInitSearchForm();
        })();
        (function(){
            var modal = document.getElementById('ez-delete-confirm-modal');
            var proceedBtn = document.getElementById('ez-delete-confirm-proceed');
            if (!modal || !proceedBtn) return;
            var deleteUrl = '';
            var currentDeleteLink = null;
            var originalProceedLabel = proceedBtn.innerHTML;
            function setProceedLoading(loading) {
                if (loading) {
                    proceedBtn.innerHTML = '<span class="ez-delete-spinner"></span>';
                    proceedBtn.classList.add('ez-delete-proceed-loading');
                    proceedBtn.style.pointerEvents = 'none';
                } else {
                    proceedBtn.innerHTML = originalProceedLabel;
                    proceedBtn.classList.remove('ez-delete-proceed-loading');
                    proceedBtn.style.pointerEvents = '';
                }
            }
            function openModal(link) {
                currentDeleteLink = link || null;
                deleteUrl = (link && link.getAttribute('href')) ? link.getAttribute('href') : '';
                if (deleteUrl === '#' || deleteUrl === '') deleteUrl = '';
                proceedBtn.href = deleteUrl || '#';
                modal.classList.add('modal-open');
                modal.style.pointerEvents = 'auto';
                modal.style.opacity = '1';
            }
            function closeModal() {
                setProceedLoading(false);
                modal.classList.remove('modal-open');
                modal.style.pointerEvents = 'none';
                modal.style.opacity = '0';
                deleteUrl = '';
                currentDeleteLink = null;
            }
            document.body.addEventListener('click', function(e) {
                var link = e.target.closest && e.target.closest('a.ez-delete-confirm');
                if (link) {
                    e.preventDefault();
                    openModal(link);
                }
                if (e.target.closest && e.target.closest('.ez-delete-confirm-cancel')) closeModal();
                if (e.target === modal || (e.target.closest && e.target.closest('.modal-backdrop'))) closeModal();
            });
            proceedBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (currentDeleteLink && currentDeleteLink.getAttribute('data-ajax-delete-action')) {
                    var action = currentDeleteLink.getAttribute('data-ajax-delete-action');
                    var id = currentDeleteLink.getAttribute('data-id');
                    var nonce = currentDeleteLink.getAttribute('data-nonce');
                    var refreshEvent = currentDeleteLink.getAttribute('data-refresh-event');
                    if (!action || !id || !nonce) { closeModal(); return; }
                    setProceedLoading(true);
                    var formData = new FormData();
                    formData.append('action', action);
                    formData.append('id', id);
                    formData.append('nonce', nonce);
                    fetch(typeof ajaxurl !== 'undefined' ? ajaxurl : '<?php echo esc_js(admin_url('admin-ajax.php')); ?>', { method: 'POST', body: formData, credentials: 'same-origin' })
                        .then(function(r) { return r.json(); })
                        .then(function(res) {
                            setProceedLoading(false);
                            if (res && res.success) {
                                closeModal();
                                if (refreshEvent && typeof window.ezShowTableSkeletonAndRefresh === 'function') {
                                    window.ezShowTableSkeletonAndRefresh(refreshEvent);
                                } else if (refreshEvent) {
                                    if (typeof htmx !== 'undefined' && htmx.trigger) htmx.trigger(document.body, refreshEvent);
                                    else document.body.dispatchEvent(new CustomEvent(refreshEvent));
                                }
                            } else if (res && res.data && res.data.message) { alert(res.data.message); }
                        })
                        .catch(function() { setProceedLoading(false); closeModal(); });
                    return;
                }
                if (deleteUrl) window.location.href = deleteUrl;
                else closeModal();
            });
        })();
        </script>
        <?php
    }

    /**
     * Enqueue assets for WordPress admin area
     */
    public static function enqueueAdminAssets(string $hook): void
    {
        if (!is_admin()) {
            return;
        }

        // Load DaisyUI/Tailwind CSS for admin
        $plugin_dir = dirname(self::$plugin_root);
        $admin_css_file = $plugin_dir . '/dist/css/admin-bundle.css';
        wp_enqueue_style(
            'ez-admin-styles',
            plugins_url('dist/css/admin-bundle.css', self::$plugin_root),
            [],
            file_exists($admin_css_file) ? filemtime($admin_css_file) : self::$version
        );

        // Load HTMX
        wp_enqueue_script(
            'ez-htmx',
            plugins_url('assets/vendor/htmx/htmx.min.js', self::$plugin_root),
            [],
            '2.0.2',
            true
        );

        $admin_htmx_nonce_js = $plugin_dir . '/assets/js/admin-htmx-nonce.js';
        wp_enqueue_script(
            'ez-admin-htmx-nonce',
            plugins_url('assets/js/admin-htmx-nonce.js', self::$plugin_root),
            ['ez-htmx'],
            is_file($admin_htmx_nonce_js) ? (string) filemtime($admin_htmx_nonce_js) : self::$version,
            true
        );
        wp_localize_script(
            'ez-admin-htmx-nonce',
            'ezHtmxNonce',
            [
                'nonce' => wp_create_nonce(EzAdminAjaxConfig::HTMX_ADMIN_NONCE_ACTION),
            ]
        );

        // مودال CRUD متمرکز — حتماً قبل از Alpine لود شود تا هنگام x-data تابع تعریف شده باشد
        $crud_modal_js = $plugin_dir . '/assets/js/admin-crud-modal.js';
        wp_enqueue_script(
            'ez-admin-crud-modal',
            plugins_url('assets/js/admin-crud-modal.js', self::$plugin_root),
            [],
            file_exists($crud_modal_js) ? (string) filemtime($crud_modal_js) : self::$version,
            true
        );

        // Alpine.js با defer — بعد از admin-crud-modal
        wp_enqueue_script(
            'ez-alpinejs',
            plugins_url('assets/vendor/alpine/cdn.min.js', self::$plugin_root),
            ['ez-admin-crud-modal'],
            '3.14.3',
            true
        );
        wp_script_add_data('ez-alpinejs', 'defer', true);
    }

    /**
     * Enqueue assets for front-end
     */
    public static function enqueueFrontAssets(): void
    {
        if (is_admin()) {
            return;
        }

        // Load DaisyUI/Tailwind CSS for front-end
        $plugin_dir = dirname(self::$plugin_root);
        $front_css_file = $plugin_dir . '/dist/css/front-bundle.css';
        wp_enqueue_style(
            'ez-front-styles',
            plugins_url('dist/css/front-bundle.css', self::$plugin_root),
            [],
            file_exists($front_css_file) ? filemtime($front_css_file) : self::$version
        );

        // Load Alpine.js for front-end (if needed)
        if (self::shouldLoadAlpineOnFront()) {
            wp_enqueue_script(
                'ez-alpinejs-front',
                plugins_url('assets/vendor/alpine/cdn.min.js', self::$plugin_root),
                [],
                '3.14.3',
                true
            );
            wp_script_add_data('ez-alpinejs-front', 'defer', true);
        }

        // Load HTMX for front-end (if needed)
        if (self::shouldLoadHtmxOnFront()) {
            wp_enqueue_script(
                'ez-htmx-front',
                plugins_url('assets/vendor/htmx/htmx.min.js', self::$plugin_root),
                [],
                '2.0.2',
                true
            );
        }

        // Stencil components (front-end only) – built into plugin dist (index.js = ESM, index.cjs.js = legacy)
        $stencil_esm = $plugin_dir . '/dist/js/index.js';
        if (is_file($stencil_esm)) {
            wp_enqueue_script(
                'ez-components-esm',
                plugins_url('dist/js/index.js', self::$plugin_root),
                [],
                filemtime($stencil_esm),
                true
            );
            add_filter('script_loader_tag', static function (string $tag, string $handle, string $src): string {
                if ($handle === 'ez-components-esm') {
                    return str_replace('<script ', '<script type="module" ', $tag);
                }
                return $tag;
            }, 10, 3);
        }

        $stencil_nomodule = $plugin_dir . '/dist/js/index.cjs.js';
        if (is_file($stencil_nomodule)) {
            wp_enqueue_script(
                'ez-components-nomodule',
                plugins_url('dist/js/index.cjs.js', self::$plugin_root),
                [],
                filemtime($stencil_nomodule),
                true
            );
            add_filter('script_loader_tag', static function (string $tag, string $handle, string $src): string {
                if ($handle === 'ez-components-nomodule') {
                    return str_replace('<script ', '<script nomodule ', $tag);
                }
                return $tag;
            }, 10, 3);
        }
    }

    /**
     * Check if Alpine.js should be loaded on front-end
     */
    private static function shouldLoadAlpineOnFront(): bool
    {
        // Only load on pages that need interactive components
        return is_page('games') || is_post_type_archive('ez_game') || is_singular('ez_game');
    }

    /**
     * Check if HTMX should be loaded on front-end
     */
    private static function shouldLoadHtmxOnFront(): bool
    {
        // Load on pages that need dynamic content loading (games, brand single)
        if (is_page('games') || is_post_type_archive('ez_game')) {
            return true;
        }
        if (get_query_var('ez_brand_slug')) {
            return true;
        }
        return false;
    }

    /**
     * Get asset URL with versioning
     */
    public static function getAssetUrl(string $path): string
    {
        $plugin_dir = dirname(self::$plugin_root);
        $full_path = $plugin_dir . '/' . ltrim($path, '/');
        $version = file_exists($full_path) ? filemtime($full_path) : self::$version;
        
        return plugins_url($path, self::$plugin_root) . '?ver=' . $version;
    }
}
