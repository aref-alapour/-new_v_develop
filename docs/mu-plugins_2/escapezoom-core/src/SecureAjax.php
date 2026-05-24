<?php
namespace EscapeZoom\Core;

/**
 * سیستم AJAX امن و مخفی
 * آدرس مبهم، داده رمزشده، بدون افشای ساختار
 */
class SecureAjax
{
    private static $query_key = 'ezq';
    private static $handlers = [];
    
    public static function init()
    {
        // ثبت query var مبهم
        add_filter('query_vars', [__CLASS__, 'register_query_var']);
        // گوش دادن به درخواست‌ها
        add_action('template_redirect', [__CLASS__, 'handle_request'], 1);
        
        // ثبت handlerها
        self::register_handlers();
    }
    
    public static function register_query_var($vars)
    {
        $vars[] = self::$query_key;
        return $vars;
    }
    
    private static function register_handlers()
    {
        // هر handler یک کد عددی دارد
        self::$handlers = [
            1 => ['type' => 'fast', 'callback' => [__CLASS__, 'users_list']],
            2 => ['type' => 'wp', 'file' => 'users_add.php'],
            3 => ['type' => 'wp', 'file' => 'users_edit.php'],
            4 => ['type' => 'wp', 'file' => 'users_delete.php'],
            5 => ['type' => 'wp', 'file' => 'users_create_password.php'],
            6 => ['type' => 'wp', 'file' => 'banking_info_get.php'],
            7 => ['type' => 'wp', 'file' => 'banking_info_update.php'],
        ];
    }
    
    public static function handle_request()
    {
        global $wp_query;
        
        if (!$wp_query->get(self::$query_key)) {
            return;
        }
        
        // تنظیم header
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        nocache_headers();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            self::error('Method not allowed', 405);
        }
        
        // دریافت داده رمزشده
        $raw = file_get_contents('php://input');
        if (!$raw) {
            self::error('Empty request', 400);
        }
        
        // رمزگشایی
        $data = self::decrypt($raw);
        if (!$data || !isset($data['t'], $data['a'])) {
            self::error('Invalid data', 400);
        }
        
        $token = $data['t'];
        $action = (int) $data['a'];
        $params = $data['p'] ?? [];
        
        // بررسی توکن
        if (!function_exists('ez_ajax_token_verify')) {
            self::error('Auth unavailable', 500);
        }
        
        $payload = ez_ajax_token_verify($token);
        if (!$payload || ($payload['scope'] ?? '') !== 'team') {
            self::error('Invalid token', 403);
        }
        
        $token_user_id = $payload['user_id'];
        
        // action 0 = team callback by name (games_info_get, withdrawals_get, ...) — خروجی فقط JSON
        if ($action === 0) {
            self::execute_team_callback($params, $token_user_id);
            exit;
        }
        
        // اجرای handler
        if (!isset(self::$handlers[$action])) {
            self::error('Unknown action', 400);
        }
        
        $handler = self::$handlers[$action];
        
        if ($handler['type'] === 'fast') {
            // مسیر سریع: بدون لود کامل WP
            self::execute_fast($handler, $params);
        } else {
            // مسیر عادی: با WP کامل
            self::execute_wp($handler, $params, $token_user_id);
        }
        
        exit;
    }
    
    private static function execute_fast($handler, $params)
    {
        if (!class_exists('\EscapeZoom\Core\Database') || !Database::getCapsule()) {
            Database::boot();
        }
        
        if (isset($handler['callback']) && is_callable($handler['callback'])) {
            $result = call_user_func($handler['callback'], $params);
            echo json_encode($result, JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        self::error('Handler not callable', 500);
    }
    
    private static function execute_wp($handler, $params, $token_user_id)
    {
        // بررسی session
        if (!function_exists('get_current_user_id')) {
            self::error('WP not loaded', 500);
        }
        
        $current_user_id = get_current_user_id();
        if ($token_user_id !== $current_user_id) {
            self::error('Session mismatch', 403);
        }
        
        // اجرای callback
        if (isset($handler['file'])) {
            $callback_file = get_template_directory() . '/app/ajax/callbacks/team/' . $handler['file'];
            if (!is_file($callback_file)) {
                self::error('Callback not found', 500);
            }
            
            $_POST = array_merge($_POST, $params);
            require $callback_file;
            wp_die();
        }
        
        self::error('Handler invalid', 500);
    }
    
    /**
     * اجرای callback تیم با نام (action 0) — خروجی فقط JSON از طرف فایل callback
     */
    private static function execute_team_callback(array $params, $token_user_id)
    {
        if (!function_exists('get_current_user_id')) {
            self::error('WP not loaded', 500);
        }
        $current_user_id = get_current_user_id();
        if ($token_user_id !== $current_user_id) {
            self::error('Session mismatch', 403);
        }
        $callback = isset($params['callback']) ? preg_replace('/[^a-z0-9_-]/i', '', (string) $params['callback']) : '';
        if ($callback === '') {
            self::error('Invalid callback', 400);
        }
        $theme_dir = get_template_directory();
        $callbacks_dir = $theme_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'ajax' . DIRECTORY_SEPARATOR . 'callbacks' . DIRECTORY_SEPARATOR . 'team';
        if (!is_dir($callbacks_dir)) {
            self::error('Team callbacks dir not found', 500);
        }
        $glob = glob($callbacks_dir . DIRECTORY_SEPARATOR . '*.php');
        $allowed = is_array($glob) ? array_map(static function ($path) {
            return basename($path, '.php');
        }, $glob) : [];
        if (!in_array($callback, $allowed, true)) {
            self::error('Callback not allowed', 400);
        }
        $callback_file = $callbacks_dir . DIRECTORY_SEPARATOR . $callback . '.php';
        if (!is_file($callback_file) || !is_readable($callback_file)) {
            self::error('Callback not found', 404);
        }
        $_POST = array_merge($_POST, $params);
        require $callback_file;
        wp_die();
    }
    
    private static function error($msg, $code = 500)
    {
        http_response_code($code);
        die(json_encode(['success' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE));
    }
    
    /**
     * رمزنگاری داده
     */
    public static function encrypt($data)
    {
        if (!defined('EZ_AJAX_SECRET')) {
            return false;
        }
        
        $json = json_encode($data);
        $iv = openssl_random_pseudo_bytes(16);
        $encrypted = openssl_encrypt($json, 'AES-256-CBC', substr(EZ_AJAX_SECRET, 0, 32), 0, $iv);
        
        return base64_encode($iv . '::' . $encrypted);
    }
    
    /**
     * رمزگشایی داده
     */
    private static function decrypt($encrypted)
    {
        // سعی در base64 ساده (موقت - بعداً encryption کامل اضافه می‌شود)
        $decoded = base64_decode($encrypted, true);
        if ($decoded) {
            $data = json_decode($decoded, true);
            if (is_array($data)) {
                return $data;
            }
        }
        
        // اگر encryption کامل بود
        if (!defined('EZ_AJAX_SECRET')) {
            return false;
        }
        
        $decoded = base64_decode($encrypted);
        if (!$decoded || strpos($decoded, '::') === false) {
            return false;
        }
        
        list($iv, $data) = explode('::', $decoded, 2);
        $decrypted = openssl_decrypt($data, 'AES-256-CBC', substr(EZ_AJAX_SECRET, 0, 32), 0, $iv);
        
        return $decrypted ? json_decode($decrypted, true) : false;
    }
    
    /**
     * لیست کاربران (مسیر سریع)
     */
    public static function users_list($params)
    {
        $timings = ['start' => microtime(true)];
        
        $db = Database::connection('default');
        $timings['db_connected'] = microtime(true);
        
        $search = $params['search'] ?? '';
        $role = $params['role'] ?? '';
        $level = (int) ($params['level'] ?? 0);
        $page = max(1, (int) ($params['page'] ?? 1));
        $per_page = min(100, max(10, (int) ($params['items_per_page'] ?? 50)));
        
        $where = [];
        if (!empty($search)) {
            if (is_numeric($search)) {
                $where[] = "u.ID = " . intval($search);
            } else {
                $s = $db->getPdo()->quote("%{$search}%");
                $where[] = "(u.user_login LIKE {$s} OR u.user_email LIKE {$s} OR u.display_name LIKE {$s} OR EXISTS (SELECT 1 FROM wp_usermeta m WHERE m.user_id = u.ID AND m.meta_key IN ('first_name', 'last_name', 'billing_phone') AND m.meta_value LIKE {$s} LIMIT 1))";
            }
        }
        if (!empty($role)) {
            $r = $db->getPdo()->quote("%\"{$role}\"%");
            $where[] = "EXISTS (SELECT 1 FROM wp_usermeta m2 WHERE m2.user_id = u.ID AND m2.meta_key = 'wp_capabilities' AND m2.meta_value LIKE {$r} LIMIT 1)";
        }
        
        $where_sql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        $level_join = $level_having = '';
        
        if ($level) {
            $level_join = " INNER JOIN (SELECT user_id, SUM(point) as total FROM points GROUP BY user_id) p ON p.user_id = u.ID ";
            $level_having = " HAVING " . ($level == 1 ? "p.total <= 150" : ($level == 2 ? "p.total > 150 AND p.total <= 700" : ($level == 3 ? "p.total > 700 AND p.total <= 7000" : "p.total > 7000")));
        }
        
        $count_sql = $level ? "SELECT COUNT(*) as total FROM (SELECT u.ID FROM wp_users u {$level_join} {$where_sql} GROUP BY u.ID {$level_having}) t" : "SELECT COUNT(DISTINCT u.ID) as total FROM wp_users u {$level_join} {$where_sql}";
        $total = (int) ($db->selectOne($count_sql)->total ?? 0);
        $timings['count_done'] = microtime(true);
        
        $offset = ($page - 1) * $per_page;
        $users_sql = "SELECT u.ID, u.user_login, u.user_email, u.display_name, u.user_registered FROM wp_users u {$level_join} {$where_sql} " . ($level ? "GROUP BY u.ID, u.user_login, u.user_email, u.display_name, u.user_registered {$level_having}" : "") . " ORDER BY u.user_registered DESC LIMIT {$per_page} OFFSET {$offset}";
        $users = $db->select($users_sql);
        $timings['users_fetched'] = microtime(true);
        
        if (empty($users)) {
            return ['success' => true, 'data' => ['users' => [], 'pagination' => ['total' => 0, 'per_page' => $per_page, 'current_page' => $page, 'total_pages' => 0]]];
        }
        
        $ids = implode(',', array_map('intval', array_column($users, 'ID')));
        $all_meta = [];
        foreach ($db->select("SELECT user_id, meta_key, meta_value FROM wp_usermeta WHERE user_id IN ({$ids}) AND meta_key IN ('first_name', 'last_name', 'billing_phone', 'billing_first_name', 'billing_last_name', 'wp_capabilities', 'withdrawal_owner_shaba')") as $m) {
            $all_meta[$m->user_id][$m->meta_key] = $m->meta_value;
        }
        $timings['meta_fetched'] = microtime(true);
        
        $points = [];
        foreach ($db->select("SELECT user_id, SUM(point) as total FROM points WHERE user_id IN ({$ids}) GROUP BY user_id") as $p) {
            $points[$p->user_id] = (int) $p->total;
        }
        $timings['points_fetched'] = microtime(true);
        
        $role_map = ['administrator' => 'مدیر', 'accounting' => 'حسابدار', 'sans_manager' => 'مدیر سانس', 'shopist' => 'شاپ منیجر', 'poshtiban' => 'پشتیبان', 'compiler' => 'مجموعه‌دار', 'supervisor' => 'شاپ منیجر', 'customer' => 'مشتری', 'subscriber' => 'مشترک'];
        
        $rows = [];
        foreach ($users as $u) {
            $meta = $all_meta[$u->ID] ?? [];
            $phone = $meta['billing_phone'] ?? $u->user_login;
            if (!preg_match('/^09\d{9}$/', $phone)) $phone = 'شماره تلفن معتبر ثبت نشده است';
            $fn = $meta['first_name'] ?? $meta['billing_first_name'] ?? '';
            $ln = $meta['last_name'] ?? $meta['billing_last_name'] ?? '';
            $name = trim($fn . ' ' . $ln) ?: 'نام و نام خانوادگی ثبت نشده است';
            $caps = @unserialize($meta['wp_capabilities'] ?? '');
            $r = !empty($caps) ? array_key_first($caps) : 'customer';
            $pts = $points[$u->ID] ?? 0;
            $lvl = ($pts <= 150) ? 1 : (($pts <= 700) ? 2 : (($pts <= 7000) ? 3 : 4));
            
            $rows[] = [
                'id' => $u->ID,
                'phone' => $phone,
                'name' => $name,
                'first_name' => $fn,
                'last_name' => $ln,
                'role' => $r,
                'role_persian' => $role_map[$r] ?? $r,
                'level' => $lvl,
                'iban' => $meta['withdrawal_owner_shaba'] ?? '',
                'registered' => $u->user_registered
            ];
        }
        
        $timings['processing_done'] = microtime(true);
        
        // محاسبه زمان‌های دقیق
        $profile = [
            'db_connect' => round(($timings['db_connected'] - $timings['start']) * 1000, 2),
            'count_query' => round(($timings['count_done'] - $timings['db_connected']) * 1000, 2),
            'users_query' => round(($timings['users_fetched'] - $timings['count_done']) * 1000, 2),
            'meta_query' => round(($timings['meta_fetched'] - $timings['users_fetched']) * 1000, 2),
            'points_query' => round(($timings['points_fetched'] - $timings['meta_fetched']) * 1000, 2),
            'processing' => round(($timings['processing_done'] - $timings['points_fetched']) * 1000, 2),
            'total' => round(($timings['processing_done'] - $timings['start']) * 1000, 2),
        ];
        
        return [
            'success' => true,
            'data' => [
                'users' => $rows,
                'pagination' => [
                    'total' => $total,
                    'per_page' => $per_page,
                    'current_page' => $page,
                    'total_pages' => $per_page > 0 ? (int) ceil($total / $per_page) : 1
                ]
            ],
            'debug' => [
                'timings_ms' => $profile,
                'queries' => 4
            ]
        ];
    }
}
