<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Games\Services;

/**
 * لینک کوتاه بازی‌ها از طریق سرویس eszm (هم‌منطق با تم v2 و api-shortener).
 * هر بازی به‌عنوان یک محصول با type=product و item_id=product_id ثبت می‌شود.
 */
final class EzShortlinkService
{
    private const API_URL = 'https://eszm.ir/shortener_core/api.php';
    private const DEFAULT_TOKEN = 'AUQvoz46mbdiMu5fD7YpLB7JrH7SivHw64JRvPQPeCOTvHJx7APTBaxDryF80Jda';

    /**
     * لینک کوتاه را از API بگیر یا از فرمت پیش‌فرض بساز و در دیتابیس ذخیره کن.
     * اگر قبلاً shortlink در content ذخیره شده باشد، همان برگردانده می‌شود مگر force_refresh=true.
     *
     * @return string|null لینک کوتاه (مثلاً eszm.ir?r=123 یا همان خروجی API)
     */
    public function getOrCreateForProduct(int $product_id, string $original_url, bool $force_refresh = false): ?string
    {
        global $wpdb;
        $content_table = $wpdb->prefix . 'ez_product_content';

        if (!$force_refresh) {
            $existing = $wpdb->get_var($wpdb->prepare(
                "SELECT shortlink FROM {$content_table} WHERE product_id = %d",
                $product_id
            ));
            if (!empty($existing)) {
                return $existing;
            }
        }

        $shortlink = $this->callApi($original_url, 'product', $product_id);
        if ($shortlink === null) {
            $shortlink = $this->fallbackShortlink($product_id);
        }

        if ($shortlink !== null) {
            $wpdb->update(
                $content_table,
                ['shortlink' => $shortlink],
                ['product_id' => $product_id]
            );
        }

        return $shortlink;
    }

    /**
     * فراخوانی API eszm (هم‌ساختار با تم v2).
     */
    private function callApi(string $original_url, string $type, int $item_id): ?string
    {
        $token = defined('EZ_SHORTLINK_TOKEN') ? EZ_SHORTLINK_TOKEN : self::DEFAULT_TOKEN;
        $token = apply_filters('ez_shortlink_api_token', $token);

        $response = wp_remote_post(
            apply_filters('ez_shortlink_api_url', self::API_URL),
            [
                'body'    => [
                    'original_url' => $original_url,
                    'type'         => $type,
                    'item_id'      => $item_id,
                ],
                'headers' => [
                    'X-AUTH-TOKEN'   => $token,
                    'Content-Type'   => 'application/x-www-form-urlencoded',
                ],
                'timeout' => 30,
            ]
        );

        if (is_wp_error($response)) {
            return null;
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (!is_array($data) || empty($data['shortlink'])) {
            return null;
        }

        return is_string($data['shortlink']) ? $data['shortlink'] : null;
    }

    /**
     * فرمت پیش‌فرض لینک کوتاه (مثل تم v2) وقتی API در دسترس نباشد.
     */
    private function fallbackShortlink(int $product_id): string
    {
        $base = apply_filters('ez_shortlink_base_domain', 'eszm.ir');
        return $base . '?r=' . $product_id;
    }
}
