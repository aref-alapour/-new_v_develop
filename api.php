<?php
header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0, s-maxage=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('CDN-Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Surrogate-Control: no-store, no-cache, must-revalidate, max-age=0');
header('X-LiteSpeed-Cache-Control: no-cache');
header('X-Accel-Buffering: no');
// Handle simple GET request for server time
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_server_time') {
    $tz        = new DateTimeZone('Asia/Tehran');
    $tehranNow = new DateTime('now', $tz);
    $tehranNow->setTime(0, 0, 0);
    $current   = (int) $tehranNow->getTimestamp();

    $jdf = __DIR__ . '/web-service/jdf.php';
    if (is_readable($jdf)) {
        require_once $jdf;
    }

    $days = [];
    for ($i = 1; $i <= 15; $i++) {
        $ts     = $current + ($i * 86400);
        $days[] = [
            'ts'   => $ts,
            'day'  => function_exists('jdate') ? jdate('d', $ts) : '',
            'name' => function_exists('jdate') ? jdate('l', $ts) : '',
        ];
    }

    echo json_encode([
        'timestamp'    => time(),
        'current_date' => $current,
        'days'         => $days,
    ]);
    exit;
}
// اعتبارسنجی توکن
function validateToken($token, $timestamp) {
    $secret = 'your-secret-key'; // کلید مخفی شما
    $expectedToken = hash_hmac('sha256', $timestamp, $secret);

    $currentTime = time();
    if (abs($currentTime - $timestamp) > 300) {
        return false; // توکن منقضی شده
    }
    return hash_equals($expectedToken, $token);
}

// چک کردن متد و پارامترهای لازم
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['filters']) || !isset($_POST['token']) || !isset($_POST['timestamp'])) {
    http_response_code(403);
    echo json_encode(['error' => 'دسترسی غیرمجاز: پارامترهای ناقص']);
    exit;
}

$token = $_POST['token'];
$timestamp = $_POST['timestamp'];

if (!validateToken($token, $timestamp)) {
    http_response_code(403);
    echo json_encode(['error' => 'توکن نامعتبر است']);
    exit;
}

// خواندن filters از POST (فرمت JSON)
$filters = json_decode($_POST['filters'], true);
if (!is_array($filters)) {
    $filters = [];
}

// اگر در query string پارامتر action وجود داشت، به filters اضافه کن
if (isset($_GET['action'])) {
    $filters['action'] = $_GET['action'];
}

// حالا دوباره filters را به JSON تبدیل و در $_POST جایگزین کن
$_POST['filters'] = json_encode($filters);

// بارگذاری و اجرای fetch_data.php
//require_once $_SERVER['DOCUMENT_ROOT'] . '/wp-content/themes/escapezoom-v2/template/reports/fetch_data.php';
