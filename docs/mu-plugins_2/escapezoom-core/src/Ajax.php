<?php
namespace EscapeZoom\Core;

/**
 * کلاس واحد AJAX هسته – فقط واسط امن و سریع
 *
 * تعریف callback یا لیست آن در هسته نیست.
 * قالب (مثلاً team) با registerHandler() هندلر را ثبت می‌کند و callbackها را خودش مشخص می‌کند.
 */
class Ajax
{
    /** نام action وردپرس برای درگاه واحد */
    public const GATEWAY_ACTION = 'ez_ajax';

    /** نام پارامتر درخواست برای انتخاب هندلر */
    public const HANDLER_FIELD = 'handler';

    /** نام مجاز برای callback: حروف، عدد، زیرخط، خط تیره */
    private const CALLBACK_NAME_PATTERN = '/^[a-z0-9_-]+$/i';

    /** @var array<string, array> نام هندلر => تنظیمات */
    private static array $handlers = [];

    /**
     * ثبت یک هندلر AJAX از طرف قالب یا پلاگین
     *
     * @param string $name نام یکتا (مثلاً team)
     * @param array $config [
     *   'nonce_action' => string (الزامی),
     *   'nonce_field' => string (پیش‌فرض: 'nonce'),
     *   'callback_field' => string (پیش‌فرض: 'callback'),
     *   'callbacks_dir' => string مسیر مطلق پوشه فایل‌های callback (الزامی),
     *   'allowed_callbacks' => array|callable لیست نام فایل‌ها بدون .php یا تابعی که آرایه برمی‌گرداند (الزامی),
     *   'check_access' => callable|null تابعی که true/false برمی‌گرداند؛ در غیر این صورت فقط لاگین چک می‌شود (اختیاری),
     *   'require_login' => bool (پیش‌فرض: true),
     *   'before_callback' => callable|null تابعی که قبل از بارگذاری فایل با نام callback صدا زده می‌شود (اختیاری)
     * ]
     */
    public static function registerHandler(string $name, array $config): void
    {
        $name = self::sanitizeHandlerName($name);
        if ($name === '') {
            return;
        }
        self::$handlers[$name] = [
            'nonce_action'   => $config['nonce_action'] ?? '',
            'nonce_field'    => $config['nonce_field'] ?? 'nonce',
            'callback_field' => $config['callback_field'] ?? 'callback',
            'callbacks_dir'  => isset($config['callbacks_dir']) ? rtrim($config['callbacks_dir'], DIRECTORY_SEPARATOR) : '',
            'allowed_callbacks' => $config['allowed_callbacks'] ?? [],
            'check_access'   => $config['check_access'] ?? null,
            'require_login'  => $config['require_login'] ?? true,
            'before_callback' => $config['before_callback'] ?? null,
        ];
    }

    /**
     * نقطه ورود واحد – از ez-ajax.php صدا زده می‌شود
     */
    public static function handle(): void
    {
        if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::sendError('Method not allowed', 405);
        }

        $handlerName = isset($_POST[self::HANDLER_FIELD])
            ? self::sanitizeHandlerName(sanitize_text_field(wp_unslash($_POST[self::HANDLER_FIELD])))
            : '';

        if ($handlerName === '' || !isset(self::$handlers[$handlerName])) {
            self::sendError('Invalid handler', 400);
        }

        $h = self::$handlers[$handlerName];

        if (!empty($h['require_login']) && !is_user_logged_in()) {
            self::sendError('Unauthorized', 401);
        }

        $nonceField = $h['nonce_field'];
        $nonceRaw = isset($_POST[$nonceField]) ? sanitize_text_field(wp_unslash($_POST[$nonceField])) : '';
        if ($nonceRaw === '' || !wp_verify_nonce($nonceRaw, $h['nonce_action'])) {
            self::sendError('Invalid nonce', 403);
        }

        if (is_callable($h['check_access']) && !$h['check_access']()) {
            self::sendError('Access denied', 403);
        }

        $callbackField = $h['callback_field'];
        $rawCallback = isset($_POST[$callbackField]) ? sanitize_text_field(wp_unslash($_POST[$callbackField])) : '';
        if ($rawCallback === '' || !preg_match(self::CALLBACK_NAME_PATTERN, $rawCallback)) {
            self::sendError('Invalid callback', 400);
        }

        $allowed = $h['allowed_callbacks'];
        if (is_callable($allowed)) {
            $allowed = $allowed();
        }
        if (!is_array($allowed) || !in_array($rawCallback, $allowed, true)) {
            self::sendError('Invalid callback', 400);
        }

        $callbacksDir = $h['callbacks_dir'];
        if ($callbacksDir === '' || !is_dir($callbacksDir)) {
            self::sendError('Handler misconfigured', 500);
        }

        $callbackFile = $callbacksDir . DIRECTORY_SEPARATOR . $rawCallback . '.php';
        if (!is_file($callbackFile) || !is_readable($callbackFile)) {
            self::sendError('Callback not found', 404);
        }

        if (is_callable($h['before_callback'] ?? null)) {
            $h['before_callback']($rawCallback);
        }

        try {
            require_once $callbackFile;
        } catch (\Throwable $e) {
            $detail = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            if (defined('WP_DEBUG') && WP_DEBUG) {
                self::sendError('Callback error: ' . $detail, 500);
            }
            wp_send_json_error(['message' => 'Callback error', 'detail' => $detail]);
            wp_die();
        }
        wp_die();
    }

    /**
     * فقط کاراکترهای مجاز برای نام هندلر
     */
    private static function sanitizeHandlerName(string $name): string
    {
        $name = preg_replace('/[^a-z0-9_]/i', '', $name);
        return $name === null ? '' : $name;
    }

    private static function sendError(string $message, int $httpCode = 400): void
    {
        if ($httpCode !== 200) {
            status_header($httpCode);
        }
        wp_send_json_error(['message' => $message]);
        wp_die();
    }
}
