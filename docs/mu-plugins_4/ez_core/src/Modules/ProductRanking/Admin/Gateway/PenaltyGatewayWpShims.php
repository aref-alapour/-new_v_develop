<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\ProductRanking\Admin\Gateway;

final class PenaltyGatewayWpShims
{
    public static function ensure(): void
    {
        if (! function_exists('esc_html')) {
            /**
             * @param mixed $text
             */
            function esc_html($text): string
            {
                return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
            }
        }

        if (! function_exists('esc_attr')) {
            /**
             * @param mixed $text
             */
            function esc_attr($text): string
            {
                return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
            }
        }

        if (! function_exists('wp_json_encode')) {
            /**
             * @param mixed $data
             */
            function wp_json_encode($data, int $options = 0, int $depth = 512): string|false
            {
                return json_encode($data, $options | JSON_UNESCAPED_UNICODE, $depth);
            }
        }

        if (! function_exists('esc_url')) {
            /**
             * @param mixed $url
             */
            function esc_url($url): string
            {
                $url = (string) $url;

                return filter_var($url, FILTER_SANITIZE_URL) ?: '';
            }
        }
    }
}
