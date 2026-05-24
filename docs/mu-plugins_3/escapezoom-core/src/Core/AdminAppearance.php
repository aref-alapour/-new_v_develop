<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Core;

/**
 * رنگ سایدبار/تاپ‌بار ادمین (#002816) و فونت یکان‌بخ.
 * استایل‌های ادمین فقط در wp-admin لود می‌شوند؛ فرانت فقط فونت body را می‌گیرد (بهینه).
 */
final class AdminAppearance
{
    private const ADMIN_BRAND_COLOR = '#002816';
    private const ADMIN_BRAND_HOVER  = '#003520';

    public static function register(): void
    {
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAdminStyles'], 5);
        add_action('wp_enqueue_scripts', [self::class, 'enqueueFrontFont'], 5);
        add_action('admin_head', [self::class, 'adminBrandColorCss'], 20);
        add_filter('admin_footer_text', [self::class, 'adminFooterLeft'], 10, 1);
        add_filter('update_footer', [self::class, 'adminFooterRight'], 11, 1);
    }

    /**
     * متن چپ فوتر ادمین: پروژه اسکیپ زوم ورژن ۳ — آرتین فناوران برخط.
     */
    public static function adminFooterLeft(string $text): string
    {
        return 'پروژه اسکیپ زوم ورژن 3 ساخته شده در آرتین فناوران برخط با تکنولوژی لاراول در هسته وردپرس';
    }

    /**
     * متن راست فوتر ادمین: فقط لینک AFB.co.ir (جایگزین نگارش وردپرس).
     */
    public static function adminFooterRight(string $text): string
    {
        return '<a href="https://afb.co.ir" target="_blank" rel="noopener">AFB.co.ir</a>';
    }

    /**
     * فقط در ادمین: فونت یکان‌بخ + فایل فونت‌ها (روی تم لود نمی‌شود).
     */
    public static function enqueueAdminStyles(): void
    {
        if (!is_admin()) {
            return;
        }
        $base = plugin_dir_url(dirname(__DIR__, 2) . '/escapezoom-core.php');
        wp_enqueue_style(
            'ez-yekan-bakh',
            $base . 'assets/css/yekan-bakh.css',
            [],
            '1.0.0'
        );
        wp_add_inline_style('ez-yekan-bakh', self::globalFontCss('admin'));
    }

    /**
     * فقط در فرانت (تم): فقط فونت یکان‌بخ برای body — بدون هیچ استایل ادمین.
     */
    public static function enqueueFrontFont(): void
    {
        if (is_admin()) {
            return;
        }
        $base = plugin_dir_url(dirname(__DIR__, 2) . '/escapezoom-core.php');
        wp_enqueue_style(
            'ez-yekan-bakh-front',
            $base . 'assets/css/yekan-bakh.css',
            [],
            '1.0.0'
        );
        wp_add_inline_style('ez-yekan-bakh-front', self::globalFontCss('front'));
    }

    private static function globalFontCss(string $context): string
    {
        $family = 'yekanbakh, "Yekan Bakh", sans-serif';
        if ($context === 'admin') {
            // فقط ادمین؛ روی تم اصلاً لود نمی‌شود
            return sprintf(
                '#wpwrap, #wpbody, #wpcontent, #wpbody-content, .wrap, body { font-family: %1$s !important; } .rtl h1, .rtl h2, .rtl h3, .rtl h4, .rtl h5, .rtl h6 { font-family: %1$s !important; font-weight: 600; }',
                $family
            );
        }
        return sprintf(
            'body { font-family: %s; }',
            $family
        );
    }

    /**
     * فقط در admin_head اجرا می‌شود — فقط پس‌زمینه؛ عرض (مثلاً 160px) دست نخورده.
     */
    public static function adminBrandColorCss(): void
    {
        $color = self::ADMIN_BRAND_COLOR;
        $hover = self::ADMIN_BRAND_HOVER;
        ?>
        <style id="ez-admin-brand-color">
        /* باکس‌های دشبورد */
        .postbox { border-radius: 10px; }
        /* تاپ‌بار */
        #wpadminbar,
        #wpadminbar .ab-submenu { background-color: <?php echo esc_attr($color); ?> !important; }
        #wpadminbar .ab-item:hover,
        #wpadminbar .ab-item:focus,
        #wpadminbar .ab-submenu .ab-item:hover { background-color: <?php echo esc_attr($hover); ?> !important; }
        /* سایدبار — فقط background-color تا با #adminmenuback/#adminmenuwrap (width:160px) تداخل نگیرد */
        #adminmenuback,
        #adminmenuwrap,
        #adminmenumain,
        #adminmenu,
        #adminmenu .wp-submenu { background-color: <?php echo esc_attr($color); ?> !important; }
        #adminmenu .wp-has-current-submenu .wp-submenu,
        #adminmenu .wp-has-current-submenu .wp-submenu.sub-open { background-color: <?php echo esc_attr($hover); ?> !important; }
        #adminmenu li.menu-top:hover,
        #adminmenu li.opensub > a.menu-top,
        #adminmenu .current .wp-submenu li a:hover { background-color: <?php echo esc_attr($hover); ?> !important; }
        </style>
        <?php
    }
}
