<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Admin\Screens;

/**
 * Base for admin list/add/edit screens. Handles capability and nonce.
 */
abstract class BaseScreen
{
    protected const CAPABILITY = 'manage_options';

    public static function render(): void
    {
        if (!current_user_can(self::CAPABILITY)) {
            wp_die(esc_html__('دسترسی غیرمجاز.', 'escapezoom-core'));
        }
        static::dispatch();
    }

    abstract protected static function dispatch(): void;
}
