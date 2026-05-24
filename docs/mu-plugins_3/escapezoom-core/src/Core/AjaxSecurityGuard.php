<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Core;

/**
 * ثابت‌های مشترک برای nonce fragment ادمین (enqueue، Trait، assert).
 */
final class EzAdminAjaxConfig
{
    /** اکشن nonce برای درخواست‌های fragment ادمین (HTMX و fetch با query). */
    public const HTMX_ADMIN_NONCE_ACTION = 'ez_htmx_admin';

    public const HTMX_NONCE_HEADER = 'HTTP_X_EZ_HTMX_NONCE';
}

/**
 * اعتبارسنجی مشترک برای wp_ajax ادمین: capability، nonce پست، nonce fragment (GET/هدر HTMX).
 */
trait AjaxSecurityGuard
{

    /**
     * @param non-empty-string $cap
     */
    protected static function assertAjaxCapability(string $cap = 'manage_options'): void
    {
        if (! current_user_can($cap)) {
            wp_send_json_error(['message' => __('دسترسی ندارید.', 'escapezoom-core')], 403);
        }
    }

    /**
     * @param non-empty-string $action نام اکشن wp_create_nonce (مثلاً ez_gen_ajax_save)
     * @param non-empty-string $postKey معمولاً nonce
     */
    protected static function assertAjaxNonce(string $action, string $postKey = 'nonce'): void
    {
        $nonce = isset($_POST[$postKey]) ? sanitize_text_field((string) wp_unslash($_POST[$postKey])) : '';
        if ($nonce === '' || ! wp_verify_nonce($nonce, $action)) {
            wp_send_json_error(['general' => __('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core')], 400);
        }
    }

    /**
     * برای handlerهای JSON که fragment GET را هم می‌پذیرند — در صورت خطا JSON خطا.
     *
     * @param non-empty-string $action
     */
    protected static function assertAdminFragmentNonceJson(string $action = EzAdminAjaxConfig::HTMX_ADMIN_NONCE_ACTION): void
    {
        if (self::fragmentNonceIsValid($action)) {
            return;
        }
        wp_send_json_error(['message' => __('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core')], 403);
    }

    /**
     * برای handlerهایی که HTML خام برمی‌گردانند و exit می‌کنند.
     *
     * @param non-empty-string $action
     */
    protected static function assertAdminFragmentNonceHtml(string $action = EzAdminAjaxConfig::HTMX_ADMIN_NONCE_ACTION): void
    {
        if (self::fragmentNonceIsValid($action)) {
            return;
        }
        status_header(403);
        wp_die(esc_html__('اعتبارسنجی امنیتی ناموفق.', 'escapezoom-core'), '', ['response' => 403]);
    }

    /**
     * @param non-empty-string $action
     */
    private static function fragmentNonceIsValid(string $action): bool
    {
        $fromRequest = '';
        if (isset($_GET['_wpnonce'])) {
            $fromRequest = sanitize_text_field((string) wp_unslash($_GET['_wpnonce']));
        } elseif (isset($_POST['_wpnonce'])) {
            $fromRequest = sanitize_text_field((string) wp_unslash($_POST['_wpnonce']));
        }
        if ($fromRequest !== '' && wp_verify_nonce($fromRequest, $action)) {
            return true;
        }

        $header = isset($_SERVER[EzAdminAjaxConfig::HTMX_NONCE_HEADER])
            ? sanitize_text_field((string) wp_unslash($_SERVER[EzAdminAjaxConfig::HTMX_NONCE_HEADER]))
            : '';
        if ($header !== '' && wp_verify_nonce($header, $action)) {
            return true;
        }

        return false;
    }
}
