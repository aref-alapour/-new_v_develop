<?php

declare(strict_types=1);

namespace EscapeZoom\Core\Modules\Redirects;

/**
 * مدیریت ریدایرکت‌های قابل‌پیکربندی در جدول wp_ez_redirects.
 *
 * سه نوع match:
 * - exact  : from_path == مسیر فعلی
 * - prefix : مسیر فعلی با from_path شروع می‌شود
 * - regex  : from_path به‌عنوان الگوی PCRE روی path اعمال می‌شود
 */
final class RedirectManager
{
    public static function register(): void
    {
        add_action('template_redirect', [self::class, 'handleRedirect'], 1);
    }

    public static function handleRedirect(): void
    {
        if (is_admin() || wp_doing_ajax() || is_feed()) {
            return;
        }

        // از ریدایرکت روی login / REST / cron / preview جلوگیری کن
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $path       = self::normalizePath($requestUri);

        if ($path === '' || self::isExcludedPath($path)) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';

        if (! self::redirectsTableExists($table)) {
            return;
        }

        $rule = self::findMatchingRule($path, $table);
        if ($rule === null) {
            self::log404IfNeeded($path);
            return;
        }

        // در صورت وجود زنجیره‌ای از ریدایرکت‌ها (A→B و B→C) مقصد نهایی را پیدا کن
        $effectiveRule = self::resolveFinalRule($rule, $table);

        // آمار بازدید را فقط روی اولین قانون (مسیر اولیه کاربر) ثبت می‌کنیم
        self::incrementHitCounter((int) $rule->id, $table);

        $target = self::buildTargetUrl((string) $effectiveRule->to_url);
        $code   = (int) $effectiveRule->status_code;

        if ($target === '' || ! RedirectStatusCodes::isValid($code)) {
            return;
        }

        wp_redirect($target, $code);
        exit;
    }

    private static function normalizePath(string $requestUri): string
    {
        $parsed = parse_url($requestUri);
        $path   = $parsed['path'] ?? '';

        if ($path === '') {
            return '/';
        }

        if ($path[0] !== '/') {
            $path = '/' . $path;
        }

        return $path;
    }

    private static function isExcludedPath(string $path): bool
    {
        // مسیرهای حساس که نباید ریدایرکت شوند
        if (strpos($path, '/wp-login.php') === 0) {
            return true;
        }
        if (strpos($path, '/wp-json/') === 0) {
            return true;
        }
        if (strpos($path, '/wp-cron.php') === 0) {
            return true;
        }
        if (strpos($path, '/xmlrpc.php') === 0) {
            return true;
        }

        return false;
    }

    /**
     * @return object|null
     */
    private static function findMatchingRule(string $path, string $table): ?object
    {
        global $wpdb;

        // 1) exact — هر دو حالت با و بدون اسلش انتهایی را در نظر بگیر
        $pathNorm      = $path === '/' ? '/' : rtrim($path, '/');
        $pathWithSlash = $pathNorm === '/' ? '/' : $pathNorm . '/';
        $exact         = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table} WHERE is_active = 1 AND match_type = 'exact' AND (from_path = %s OR from_path = %s) LIMIT 1",
                $pathNorm,
                $pathWithSlash
            )
        );

        if ($exact !== null) {
            return $exact;
        }

        // 2) prefix — طول from_path نزولی
        $prefixRules = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1 AND match_type = 'prefix' ORDER BY CHAR_LENGTH(from_path) DESC"
        );

        if (!empty($prefixRules)) {
            foreach ($prefixRules as $rule) {
                $fromPath = (string) $rule->from_path;
                if ($fromPath === '') {
                    continue;
                }
                if (str_starts_with($path, $fromPath)) {
                    return $rule;
                }
            }
        }

        // 3) regex
        $regexRules = $wpdb->get_results(
            "SELECT * FROM {$table} WHERE is_active = 1 AND match_type = 'regex'"
        );

        if (empty($regexRules)) {
            return null;
        }

        foreach ($regexRules as $rule) {
            $pattern = (string) $rule->from_path;
            if ($pattern === '') {
                continue;
            }

            // اگر regex نامعتبر باشد، بی‌سروصدا رد می‌شود
            $result = @preg_match($pattern, $path);
            if ($result === 1) {
                return $rule;
            }
        }

        return null;
    }

    /**
     * برای ادمین: قانونی که روی این مسیر match می‌شود را برمی‌گرداند (فقط خواندن).
     */
    public static function getMatchingRuleForPath(string $path): ?object
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';
        if (! self::redirectsTableExists($table)) {
            return null;
        }
        $path = self::normalizePath($path);
        if ($path === '' || self::isExcludedPath($path)) {
            return null;
        }
        return self::findMatchingRule($path, $table);
    }

    /**
     * برای ادمین: مقصد نهایی پس از فلت کردن زنجیره (برای نرمال‌سازی جدول).
     */
    public static function getFinalTargetUrl(object $rule): string
    {
        global $wpdb;
        $table = $wpdb->prefix . 'ez_redirects';
        if (! self::redirectsTableExists($table)) {
            return (string) $rule->to_url;
        }
        $effective = self::resolveFinalRule($rule, $table);
        return (string) $effective->to_url;
    }

    private static function redirectsTableExists(string $table): bool
    {
        global $wpdb;

        // اگر جدول هنوز ساخته نشده، بی‌سروصدا رد شو
        $tableExists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $wpdb->esc_like($table)
            )
        );

        return $tableExists === $table;
    }

    /**
     * اگر درخواست واقعاً 404 باشد، آن را در جدول لاگ 404 ذخیره می‌کند (در صورت وجود جدول).
     */
    private static function log404IfNeeded(string $path): void
    {
        if (! function_exists('is_404') || ! is_404()) {
            return;
        }

        global $wpdb;

        $table = $wpdb->prefix . 'ez_redirect_404_logs';

        // اگر جدول لاگ 404 هنوز ساخته نشده، بی‌سروصدا رد شو
        $tableExists = $wpdb->get_var(
            $wpdb->prepare(
                "SHOW TABLES LIKE %s",
                $wpdb->esc_like($table)
            )
        );

        if ($tableExists !== $table) {
            return;
        }

        $referrer  = isset($_SERVER['HTTP_REFERER']) ? (string) wp_unslash((string) $_SERVER['HTTP_REFERER']) : '';
        $userAgent = isset($_SERVER['HTTP_USER_AGENT']) ? (string) wp_unslash((string) $_SERVER['HTTP_USER_AGENT']) : '';

        // برای جلوگیری از رشته‌های خیلی بلند روی لاگ، کمی محدودیت اعمال می‌کنیم
        if ($referrer !== '') {
            $referrer = mb_substr($referrer, 0, 1000);
        }
        if ($userAgent !== '') {
            $userAgent = mb_substr($userAgent, 0, 500);
        }

        // اگر رکوردی برای این path وجود داشته باشد، فقط شمارنده را افزایش بده
        $existingId = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM {$table} WHERE path = %s LIMIT 1",
                $path
            )
        );

        $now = current_time('mysql');

        if ($existingId !== null) {
            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$table} SET hit_count = hit_count + 1, last_hit_at = %s, updated_at = %s WHERE id = %d",
                    $now,
                    $now,
                    (int) $existingId
                )
            );

            return;
        }

        $wpdb->insert(
            $table,
            [
                'path'       => $path,
                'referrer'   => $referrer,
                'user_agent' => $userAgent,
                'hit_count'  => 1,
                'last_hit_at'=> $now,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
            ]
        );
    }

    /**
     * زنجیره‌های ریدایرکت را تا حد ممکن به مقصد نهایی فلت می‌کند
     * تا کاربر فقط یک‌بار ریدایرکت شود (A→B→C ⇒ A→C).
     */
    private static function resolveFinalRule(object $rule, string $table, int $maxDepth = 5): object
    {
        $current = $rule;
        $visited = [];

        for ($i = 0; $i < $maxDepth; $i++) {
            $toUrl = (string) $current->to_url;
            if ($toUrl === '') {
                break;
            }

            $parsed   = parse_url($toUrl);
            $nextPath = $parsed['path'] ?? '';

            // فقط مسیرهای نسبی داخلی که با / شروع می‌شوند را دنبال می‌کنیم
            if ($nextPath === '' || $nextPath[0] !== '/') {
                break;
            }

            $nextPathNorm      = $nextPath === '/' ? '/' : rtrim($nextPath, '/');
            $nextPathWithSlash = $nextPathNorm === '/' ? '/' : $nextPathNorm . '/';

            if (in_array($nextPathNorm, $visited, true)) {
                // جلوگیری از حلقه‌های احتمالی
                break;
            }

            $visited[] = $nextPathNorm;

            // فقط قوانین exact را برای مرحله بعدی در نظر می‌گیریم
            global $wpdb;
            $nextRule = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table} WHERE is_active = 1 AND match_type = 'exact' AND (from_path = %s OR from_path = %s) LIMIT 1",
                    $nextPathNorm,
                    $nextPathWithSlash
                )
            );

            if ($nextRule === null) {
                break;
            }

            // اگر عملاً به همان from_path برگشته‌ایم، حلقه است
            if ((string) $nextRule->from_path === (string) $current->from_path) {
                break;
            }

            $current = $nextRule;
        }

        return $current;
    }

    private static function incrementHitCounter(int $id, string $table): void
    {
        global $wpdb;
        $now = current_time('mysql');

        $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$table} SET hits = hits + 1, last_hit_at = %s WHERE id = %d",
                $now,
                $id
            )
        );
    }

    private static function buildTargetUrl(string $toUrl): string
    {
        $trimmed = trim($toUrl);
        if ($trimmed === '') {
            return '';
        }

        // اگر اسکیم دارد، همان را برگردان
        if (preg_match('#^[a-z][a-z0-9+.-]*://#i', $trimmed) === 1) {
            return $trimmed;
        }

        // مسیر نسبی → home_url
        if ($trimmed[0] === '/') {
            return home_url($trimmed);
        }

        // هر چیز دیگر را به‌عنوان همان URL برگردان
        return $trimmed;
    }
}

