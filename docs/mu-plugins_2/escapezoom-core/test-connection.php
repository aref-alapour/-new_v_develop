<?php
/**
 * فایل تست اتصال دیتابیس
 * 
 * دسترسی: http://your-site.local/wp-content/mu-plugins/escapezoom-core/test-connection.php
 */

// بارگذاری WordPress
require_once dirname(__FILE__) . '/../../../../wp-load.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html dir="rtl" lang="fa">
<head>
    <meta charset="UTF-8">
    <title>تست اتصال دیتابیس - EscapeZoom Core</title>
    <style>
        body { font-family: Tahoma, Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
        .test-item { margin: 20px 0; padding: 15px; border-right: 4px solid #2196F3; background: #f9f9f9; }
        .success { border-right-color: #4CAF50; background: #E8F5E9; }
        .error { border-right-color: #f44336; background: #FFEBEE; }
        .warning { border-right-color: #FF9800; background: #FFF3E0; }
        pre { background: #263238; color: #AEDD94; padding: 15px; border-radius: 5px; overflow-x: auto; direction: ltr; text-align: left; }
        .badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-success { background: #4CAF50; color: white; }
        .badge-error { background: #f44336; color: white; }
        .badge-warning { background: #FF9800; color: white; }
    </style>
</head>
<body>
<div class="container">
    <h1>🔍 تست اتصال دیتابیس - EscapeZoom Core</h1>
    
    <?php
    $tests_passed = 0;
    $tests_failed = 0;
    
    // Test 1: چک کردن بارگذاری Composer
    echo '<div class="test-item">';
    echo '<h3>1️⃣ چک کردن Composer Autoloader</h3>';
    if (class_exists('\EscapeZoom\Core\Database')) {
        echo '<span class="badge badge-success">✓ موفق</span>';
        echo '<p>کلاس Database بارگذاری شده است.</p>';
        $tests_passed++;
    } else {
        echo '<span class="badge badge-error">✗ خطا</span>';
        echo '<p>کلاس Database یافت نشد! لطفاً <code>composer install</code> را اجرا کنید.</p>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 2: چک کردن ثابت‌های دیتابیس
    echo '<div class="test-item">';
    echo '<h3>2️⃣ چک کردن ثابت‌های دیتابیس</h3>';
    $constants_ok = true;
    
    echo '<ul>';
    $required_constants = ['DB_NAME', 'DB_USER', 'DB_PASSWORD', 'DB_HOST'];
    foreach ($required_constants as $const) {
        if (defined($const)) {
            echo "<li>✓ {$const}: " . (strlen(constant($const)) > 0 ? '✓' : '✗ خالی') . "</li>";
        } else {
            echo "<li>✗ {$const}: تعریف نشده</li>";
            $constants_ok = false;
        }
    }
    echo '</ul>';
    
    echo '<h4>دیتابیس External:</h4><ul>';
    $ext_constants = ['DB_EXT_NAME', 'DB_EXT_USER', 'DB_EXT_PASSWORD', 'DB_EXT_HOST'];
    foreach ($ext_constants as $const) {
        if (defined($const)) {
            echo "<li>✓ {$const}: " . (strlen(constant($const)) > 0 ? '✓' : '✗ خالی') . "</li>";
        } else {
            echo "<li>✗ {$const}: تعریف نشده</li>";
        }
    }
    echo '</ul>';
    
    if ($constants_ok) {
        echo '<span class="badge badge-success">✓ موفق</span>';
        $tests_passed++;
    } else {
        echo '<span class="badge badge-error">✗ خطا</span>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 3: تست اتصال به دیتابیس Default
    echo '<div class="test-item">';
    echo '<h3>3️⃣ تست اتصال به دیتابیس اصلی (Default)</h3>';
    try {
        \EscapeZoom\Core\Database::boot();
        $connection = \EscapeZoom\Core\Database::connection('default');
        
        $result = $connection->select('SELECT DATABASE() as db_name, VERSION() as version, NOW() as current_time');
        
        echo '<span class="badge badge-success">✓ موفق</span>';
        echo '<pre>';
        echo "دیتابیس: " . $result[0]->db_name . "\n";
        echo "نسخه MySQL: " . $result[0]->version . "\n";
        echo "زمان سرور: " . $result[0]->current_time . "\n";
        echo '</pre>';
        
        // تست جدول wp_users
        $user_count = $connection->table('wp_users')->count();
        echo "<p>تعداد کاربران: <strong>{$user_count}</strong></p>";
        
        // تست جدول wp_markting
        $marketing_count = $connection->table('wp_markting')->count();
        echo "<p>تعداد رکوردهای مارکتینگ: <strong>{$marketing_count}</strong></p>";
        
        $tests_passed++;
    } catch (\Exception $e) {
        echo '<span class="badge badge-error">✗ خطا</span>';
        echo '<pre style="background: #FFEBEE; color: #c62828;">';
        echo "خطا: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine();
        echo '</pre>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 4: تست اتصال به دیتابیس External
    echo '<div class="test-item">';
    echo '<h3>4️⃣ تست اتصال به دیتابیس External</h3>';
    try {
        $connection = \EscapeZoom\Core\Database::connection('external');
        
        $result = $connection->select('SELECT DATABASE() as db_name');
        
        echo '<span class="badge badge-success">✓ موفق</span>';
        echo '<pre>';
        echo "دیتابیس: " . $result[0]->db_name . "\n";
        echo '</pre>';
        
        // تست جدول products_data
        $products_count = $connection->table('products_data')->count();
        echo "<p>تعداد محصولات (products_data): <strong>{$products_count}</strong></p>";
        
        // تست جدول wp_zb_booking_history
        $bookings_count = $connection->table('wp_zb_booking_history')->count();
        echo "<p>تعداد رزروها (wp_zb_booking_history): <strong>{$bookings_count}</strong></p>";
        
        $tests_passed++;
    } catch (\Exception $e) {
        echo '<span class="badge badge-error">✗ خطا</span>';
        echo '<pre style="background: #FFEBEE; color: #c62828;">';
        echo "خطا: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine();
        echo '</pre>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 5: تست مدل‌ها
    echo '<div class="test-item">';
    echo '<h3>5️⃣ تست مدل‌های Eloquent</h3>';
    try {
        // تست Marketing Model
        $marketing = \EscapeZoom\Core\Models\Marketing::first();
        echo '<p>✓ مدل Marketing: کار می‌کند';
        if ($marketing) {
            echo " (نمونه order_id: {$marketing->order_id})";
        }
        echo '</p>';
        
        // تست ProductData Model
        $product = \EscapeZoom\Core\Models\ProductData::first();
        echo '<p>✓ مدل ProductData: کار می‌کند';
        if ($product) {
            echo " (نمونه title: {$product->title})";
        }
        echo '</p>';
        
        // تست BookingHistory Model
        $booking = \EscapeZoom\Core\Models\BookingHistory::first();
        echo '<p>✓ مدل BookingHistory: کار می‌کند';
        if ($booking) {
            echo " (نمونه booking_id: {$booking->booking_id})";
        }
        echo '</p>';
        
        echo '<span class="badge badge-success">✓ موفق</span>';
        $tests_passed++;
    } catch (\Exception $e) {
        echo '<span class="badge badge-error">✗ خطا</span>';
        echo '<pre style="background: #FFEBEE; color: #c62828;">';
        echo "خطا: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine();
        echo '</pre>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 6: تست Helper Functions
    echo '<div class="test-item">';
    echo '<h3>6️⃣ تست Helper Functions</h3>';
    try {
        // تست ez_db()
        $db_test = ez_db('default');
        echo '<p>✓ تابع ez_db() کار می‌کند</p>';
        
        // تست ez_table()
        $count = ez_table('wp_users')->count();
        echo "<p>✓ تابع ez_table() کار می‌کند (تعداد users: {$count})</p>";
        
        // تست ez_external_table()
        $ext_count = ez_external_table('products_data')->count();
        echo "<p>✓ تابع ez_external_table() کار می‌کند (تعداد products: {$ext_count})</p>";
        
        echo '<span class="badge badge-success">✓ موفق</span>';
        $tests_passed++;
    } catch (\Exception $e) {
        echo '<span class="badge badge-error">✗ خطا</span>';
        echo '<pre style="background: #FFEBEE; color: #c62828;">';
        echo "خطا: " . $e->getMessage() . "\n";
        echo "File: " . $e->getFile() . ":" . $e->getLine();
        echo '</pre>';
        $tests_failed++;
    }
    echo '</div>';
    
    // Test 7: تست Query Performance
    echo '<div class="test-item">';
    echo '<h3>7️⃣ تست Performance</h3>';
    try {
        $start = microtime(true);
        $start_queries = get_num_queries();
        
        // تست یک query ساده
        $users = ez_table('wp_users')->limit(10)->get();
        
        $end = microtime(true);
        $end_queries = get_num_queries();
        
        $time = round(($end - $start) * 1000, 2);
        $queries = $end_queries - $start_queries;
        
        echo '<span class="badge badge-success">✓ موفق</span>';
        echo "<p>زمان اجرا: <strong>{$time} ms</strong></p>";
        echo "<p>تعداد queries: <strong>{$queries}</strong></p>";
        echo "<p>تعداد کاربران دریافتی: <strong>" . count($users) . "</strong></p>";
        
        $tests_passed++;
    } catch (\Exception $e) {
        echo '<span class="badge badge-error">✗ خطا</span>';
        echo '<pre style="background: #FFEBEE; color: #c62828;">';
        echo "خطا: " . $e->getMessage();
        echo '</pre>';
        $tests_failed++;
    }
    echo '</div>';
    
    // خلاصه نتایج
    echo '<div class="test-item ' . ($tests_failed == 0 ? 'success' : 'warning') . '">';
    echo '<h2>📊 نتیجه کلی</h2>';
    echo "<p><strong>تست‌های موفق:</strong> {$tests_passed}</p>";
    echo "<p><strong>تست‌های ناموفق:</strong> {$tests_failed}</p>";
    
    if ($tests_failed == 0) {
        echo '<h3 style="color: #4CAF50;">✅ همه چیز عالی است! اتصالات کار می‌کنند.</h3>';
    } else {
        echo '<h3 style="color: #FF9800;">⚠️ برخی مشکلات وجود دارد. لطفاً خطاها را بررسی کنید.</h3>';
    }
    echo '</div>';
    ?>
</div>
</body>
</html>
