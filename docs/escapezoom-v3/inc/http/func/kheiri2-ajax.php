<?php

/**
 * Kheiri2 AJAX Handler - مستقیم و سریع
 */

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

try {
    if (!defined('ABSPATH')) {
        require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-load.php');
    }
} catch (Exception $e) {
    die(json_encode(['success' => false, 'message' => 'خطا در بارگذاری وردپرس']));
}

$action = isset($_POST['action']) ? sanitize_text_field($_POST['action']) : '';

if ($action === 'auth') {
    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';

    // بررسی نام کاربری و رمز عبور
    $correct_username = 'lordaref';
    $correct_password = 'fullstackdeveloper';

    if (empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'نام کاربری و رمز عبور را وارد کنید']);
        exit;
    }

    if ($username === $correct_username && $password === $correct_password) {
        echo json_encode(['success' => true, 'message' => 'احراز هویت موفق بود']);
        exit;
    } else {
        echo json_encode(['success' => false, 'message' => 'نام کاربری یا رمز عبور اشتباه است']);
        exit;
    }
}

if ($action === 'create_shortlink') {
    $original_link = isset($_POST['original_link']) ? esc_url_raw($_POST['original_link']) : '';
    $type = isset($_POST['type']) ? sanitize_text_field($_POST['type']) : 'custom';

    if (empty($original_link)) {
        echo json_encode(['success' => false, 'data' => 'لینک اصلی را وارد کنید']);
        exit;
    }

    // اعتبارسنجی URL
    if (!filter_var($original_link, FILTER_VALIDATE_URL)) {
        echo json_encode(['success' => false, 'data' => 'لینک وارد شده معتبر نیست']);
        exit;
    }

    // استفاده از کلاس EZ_API_Shortener موجود
    if (class_exists('EZ_API_Shortener')) {
        $api_shortener = new EZ_API_Shortener();
        
        // استفاده از متد create_custom_shortlink
        $shortlink = $api_shortener->create_custom_shortlink($original_link);
        
        if ($shortlink) {
            echo json_encode(['success' => true, 'data' => ['shortlink' => $shortlink]]);
            exit;
        } else {
            // لاگ کردن خطا برای دیباگ
            error_log('Kheiri2 AJAX: Failed to create shortlink for: ' . $original_link);
            
            // بررسی لاگ‌های اخیر برای دریافت جزئیات خطا
            $error_message = 'خطا در ساخت لینک کوتاه. لطفاً لاگ سرور را بررسی کنید.';
            
            // تلاش برای دریافت جزئیات بیشتر از خطا با استفاده از Reflection
            try {
                $reflection = new ReflectionClass($api_shortener);
                $property = $reflection->getProperty('api_url');
                $property->setAccessible(true);
                $api_url = $property->getValue($api_shortener);
                
                $error_message .= ' API URL: ' . $api_url;
            } catch (Exception $e) {
                // Ignore
            }
            
            echo json_encode([
                'success' => false, 
                'data' => $error_message,
                'debug' => [
                    'original_url' => $original_link,
                    'type' => $type,
                    'class_exists' => class_exists('EZ_API_Shortener')
                ]
            ]);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'data' => 'کلاس EZ_API_Shortener یافت نشد']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Action نامعتبر']);
exit;
