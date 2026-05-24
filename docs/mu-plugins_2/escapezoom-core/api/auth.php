<?php
/**
 * توکن امن برای AJAX اختصاصی Team
 * استفاده: ez_ajax_token_create() و ez_ajax_token_verify()
 */

if (!defined('ABSPATH')) {
    return;
}

/**
 * ایجاد توکن برای درخواست‌های AJAX (فقط وقتی وردپرس لود شده)
 * وابسته به get_current_user_id() و EZ_AJAX_SECRET
 *
 * @param string $scope محدوده (مثلاً team)
 * @param int $ttl عمر توکن به ثانیه
 * @return string توکن امضا شده یا رشته خالی در صورت خطا
 */
function ez_ajax_token_create($scope = 'team', $ttl = 3600) {
    if (!defined('ABSPATH') || !function_exists('get_current_user_id')) {
        return '';
    }
    $user_id = get_current_user_id();
    if (!$user_id) {
        return '';
    }
    if (!defined('EZ_AJAX_SECRET') || EZ_AJAX_SECRET === '' || strpos(EZ_AJAX_SECRET, 'CHANGE_ME') === 0) {
        return '';
    }
    $exp = time() + (int) $ttl;
    $payload = json_encode(['user_id' => (int) $user_id, 'exp' => $exp, 'scope' => $scope]);
    $payload_b64 = strtr(base64_encode($payload), '+/', '-_');
    $sig = hash_hmac('sha256', $payload_b64, EZ_AJAX_SECRET, true);
    $sig_b64 = strtr(base64_encode($sig), '+/', '-_');
    return $payload_b64 . '.' . rtrim($sig_b64, '=');
}

/**
 * اعتبارسنجی توکن و برگرداندن payload (بدون نیاز به لود وردپرس)
 * فقط EZ_AJAX_SECRET لازم است (از wp-config)
 *
 * @param string $token توکن دریافتی
 * @return array|false آرایه شامل user_id, exp, scope یا false
 */
function ez_ajax_token_verify($token) {
    if (!defined('EZ_AJAX_SECRET') || empty($token) || strpos($token, '.') === false) {
        return false;
    }
    $parts = explode('.', $token, 2);
    $payload_b64 = $parts[0];
    $sig_b64 = str_pad(strtr($parts[1], '-_', '+/'), strlen($parts[1]) % 4 ? strlen($parts[1]) + (4 - strlen($parts[1]) % 4) : strlen($parts[1]), '=');
    $sig = base64_decode($sig_b64, true);
    if ($sig === false || !hash_equals(hash_hmac('sha256', $payload_b64, EZ_AJAX_SECRET, true), $sig)) {
        return false;
    }
    $payload = base64_decode(strtr($payload_b64, '-_', '+/'), true);
    if ($payload === false) {
        return false;
    }
    $data = json_decode($payload, true);
    if (!is_array($data) || !isset($data['user_id'], $data['exp'], $data['scope'])) {
        return false;
    }
    if ((int) $data['exp'] < time()) {
        return false;
    }
    return [
        'user_id' => (int) $data['user_id'],
        'exp' => (int) $data['exp'],
        'scope' => $data['scope']
    ];
}
