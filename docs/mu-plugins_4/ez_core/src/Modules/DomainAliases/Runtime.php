<?php

namespace EscapeZoom\Core\Modules\DomainAliases;

/**
 * Multi-host front: rewrites home/siteurl/content URLs to the current HTTP Host when applicable.
 * Does not modify reading options (show_on_front, page_on_front, page_for_posts) — blog index + home.php stay intact.
 */
final class Runtime
{
    private const OPTION_KEY = 'ez_domain_aliases';

    /** @var array<string, mixed>|null */
    private static ?array $config = null;

    private static bool $resolved = false;

    /** Plain DB values, read before any filters run. */
    private static string $dbHome = '';

    private static string $requestHost = '';

    private static bool $applyUrlFilters = false;

    private static bool $forceSslRewrite = false;

    private static string $redirectUrl = '';

    public static function register(): void
    {
        add_action('plugins_loaded', [self::class, 'onPluginsLoaded'], 3);
    }

    public static function onPluginsLoaded(): void
    {
        if (defined('WP_INSTALLING') && WP_INSTALLING) {
            return;
        }

        $host = self::detectRequestHost();
        if ($host === '') {
            return;
        }

        self::$requestHost = $host;
        self::$dbHome = (string) get_option('home', '');
        self::$config = self::loadConfig();

        self::resolveForRequest($host);

        if (self::$redirectUrl !== '') {
            add_action('template_redirect', [self::class, 'maybeRedirect'], 0);
        }

        if (self::$applyUrlFilters) {
            add_filter('option_home', [self::class, 'filterOptionHome'], 10, 1);
            add_filter('option_siteurl', [self::class, 'filterOptionSiteurl'], 10, 1);
            add_filter('content_url', [self::class, 'filterContentUrl'], 10, 2);
            // WP_PLUGIN_URL is defined before plugins load; base URLs would otherwise stay on the DB canonical host.
            add_filter('plugins_url', [self::class, 'filterPluginsUrl'], 10, 3);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function loadConfig(): array
    {
        $raw = get_option(self::OPTION_KEY, []);
        if (!is_array($raw)) {
            return self::defaultConfig();
        }

        return array_replace_recursive(self::defaultConfig(), $raw);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaultConfig(): array
    {
        return [
            'main_host' => '',
            /** accept_all | strict */
            'fallback' => 'accept_all',
            'force_ssl_global' => false,
            /** hostname => [ redirect_url => string, force_ssl => bool ] */
            'domains' => [],
        ];
    }

    public static function optionKey(): string
    {
        return self::OPTION_KEY;
    }

    /**
     * Host-only key: lowercase, no scheme, path, or port (matches HTTP_HOST host part).
     */
    public static function canonicalHostKey(string $input): string
    {
        return self::normalizeHostKey($input);
    }

    private static function detectRequestHost(): string
    {
        if (php_sapi_name() === 'cli' && empty($_SERVER['HTTP_HOST'])) {
            return '';
        }
        $h = isset($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : (isset($_SERVER['SERVER_NAME']) ? (string) $_SERVER['SERVER_NAME'] : '');
        $h = strtolower(trim($h));

        return $h === '' ? '' : $h;
    }

    private static function normalizeHostKey(string $input): string
    {
        $h = strtolower(trim($input));
        $h = preg_replace('#^https?://#', '', $h) ?? $h;
        $h = explode('/', $h, 2)[0];
        $h = explode(':', $h, 2)[0];

        return $h;
    }

    /**
     * Compare hosts: exact match on host part (request may include port).
     */
    private static function requestHostMatchesKey(string $requestWithMaybePort, string $normalizedKey): bool
    {
        $reqBase = explode(':', $requestWithMaybePort, 2)[0];

        return $reqBase === $normalizedKey;
    }

    private static function resolveForRequest(string $requestHost): void
    {
        if (self::$resolved) {
            return;
        }
        self::$resolved = true;

        $cfg = self::$config ?? self::defaultConfig();
        $main = self::normalizeHostKey((string) ($cfg['main_host'] ?? ''));
        $fallback = (string) ($cfg['fallback'] ?? 'accept_all');
        if ($fallback !== 'strict') {
            $fallback = 'accept_all';
        }

        /** @var array<string, array<string, mixed>> $domains */
        $domains = isset($cfg['domains']) && is_array($cfg['domains']) ? $cfg['domains'] : [];

        $matchedRedirect = '';
        $matchedForceSsl = false;
        $matchedDomain = false;

        foreach ($domains as $rawKey => $entry) {
            $key = self::normalizeHostKey((string) $rawKey);
            if ($key === '' || !self::requestHostMatchesKey($requestHost, $key)) {
                continue;
            }
            $matchedDomain = true;
            if (is_array($entry)) {
                $matchedRedirect = isset($entry['redirect_url']) ? trim((string) $entry['redirect_url']) : '';
                $matchedForceSsl = !empty($entry['force_ssl']);
            }
            break;
        }

        $forceSslGlobal = !empty($cfg['force_ssl_global']);

        if ($matchedRedirect !== '') {
            self::$redirectUrl = $matchedRedirect;
            self::$applyUrlFilters = false;

            return;
        }

        $isMain = $main !== '' && self::requestHostMatchesKey($requestHost, $main);

        if ($matchedDomain) {
            self::$applyUrlFilters = true;
            self::$forceSslRewrite = $matchedForceSsl || $forceSslGlobal;

            return;
        }

        if ($isMain) {
            self::$applyUrlFilters = true;
            self::$forceSslRewrite = $forceSslGlobal;

            return;
        }

        if ($fallback === 'accept_all') {
            self::$applyUrlFilters = true;
            self::$forceSslRewrite = $forceSslGlobal;

            return;
        }

        /** strict: unlisted host → canonical home from DB (no filtered options yet). */
        self::$applyUrlFilters = false;
        add_action('template_redirect', [self::class, 'strictRedirect'], 0);

        $homeHost = parse_url(self::$dbHome, PHP_URL_HOST);
        $homeHost = is_string($homeHost) ? strtolower($homeHost) : '';
        $reqBase = explode(':', $requestHost, 2)[0];

        if ($homeHost !== '' && $reqBase === $homeHost) {
            remove_action('template_redirect', [self::class, 'strictRedirect'], 0);
        }
    }

    public static function strictRedirect(): void
    {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $target = self::$dbHome;
        if ($target === '') {
            return;
        }

        $path = self::safeRequestPathAndQuery();
        $joined = untrailingslashit($target) . $path;

        wp_safe_redirect($joined, 301);
        exit;
    }

    /**
     * Path + query from REQUEST_URI only — blocks protocol-relative tricks (//evil.host/).
     */
    private static function safeRequestPathAndQuery(): string
    {
        $raw = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $parsed = wp_parse_url($raw);
        if (!is_array($parsed)) {
            return '/';
        }
        $path = isset($parsed['path']) ? $parsed['path'] : '/';
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }
        if (strlen($path) >= 2 && $path[0] === '/' && $path[1] === '/') {
            return '/';
        }
        if (!empty($parsed['query'])) {
            $path .= '?' . $parsed['query'];
        }

        return $path;
    }

    public static function maybeRedirect(): void
    {
        if (is_admin() || wp_doing_ajax() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return;
        }

        $url = self::$redirectUrl;
        if ($url === '') {
            return;
        }

        if (!preg_match('#^https?://#i', $url)) {
            $url = 'https://' . $url;
        }

        $out = esc_url_raw($url);
        if ($out === '' || !filter_var($out, FILTER_VALIDATE_URL)) {
            return;
        }

        // May point to another host (admin-configured); wp_safe_redirect() would block that.
        wp_redirect($out, 302);
        exit;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function filterOptionHome($value)
    {
        return is_string($value) ? self::rewriteUrl($value) : $value;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    public static function filterOptionSiteurl($value)
    {
        return is_string($value) ? self::rewriteUrl($value) : $value;
    }

    /**
     * @param mixed $url
     * @return mixed
     */
    public static function filterContentUrl($url, $path = '')
    {
        $u = is_string($url) ? self::rewriteUrl($url) : $url;

        return $u;
    }

    /**
     * @param mixed $url
     * @return mixed
     */
    public static function filterPluginsUrl($url, $path = '', $plugin = '')
    {
        return is_string($url) ? self::rewriteUrl($url) : $url;
    }

    private static function rewriteUrl(string $url): string
    {
        if ($url === '' || self::isAdminContextUrl($url)) {
            return $url;
        }

        $parts = parse_url($url);
        if (!is_array($parts) || empty($parts['host'])) {
            return $url;
        }

        $current = self::$requestHost;
        if ($current === '') {
            return $url;
        }

        $origHost = $parts['host'];
        if (!empty($parts['port'])) {
            $origHost .= ':' . $parts['port'];
        }

        $new = preg_replace('#' . preg_quote($origHost, '#') . '#i', $current, $url, 1);
        if (!is_string($new)) {
            return $url;
        }

        if (self::$forceSslRewrite) {
            $new = preg_replace('#^http:#i', 'https:', $new) ?? $new;
        }

        return $new;
    }

    private static function isAdminContextUrl(string $url): bool
    {
        return (bool) preg_match('#wp-admin#i', $url);
    }
}
