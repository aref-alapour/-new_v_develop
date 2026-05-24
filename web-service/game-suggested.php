<?php
// Allow CORS for all origins
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Accept, Origin, DNT, X-CustomHeader, Keep-Alive, User-Agent, X-Requested-With, If-Modified-Since, Cache-Control, Content-Type, Content-Range, Range");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH");
header("Access-Control-Max-Age: 1728000");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(204);
    exit;
}

error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
date_default_timezone_set("Asia/Tehran");

require 'md-connect.php';

if (! ($_SERVER['HTTP_HOST'] == 'escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'escapezoom.co' || $_SERVER['HTTP_HOST'] == 'bak.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'dev-api.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'zoom.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'goriza.ir' || $_SERVER['HTTP_HOST'] == 'localhost')) {
    $conn->query(sprintf("INSERT INTO hackers (host, referer) VALUES ('%s', '%s')", $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']));
    die('Get outta here');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $content_type = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

    if (str_contains($content_type, 'application/json')) {
        $data = json_decode(file_get_contents("php://input"));
    } elseif (str_contains($content_type, 'application/x-www-form-urlencoded')) {
        $data = json_decode(json_encode($_POST));
    } else {
        http_response_code(415);
        echo json_encode(['error' => 'Unsupported Media Type']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid Request Method']);
}

$home_url = 'https://escapezoom.ir';
if ($_SERVER['HTTP_HOST'] == 'localhost') {
    $home_url = 'http://localhost/escapezoom_wp';
}

if ($data && $data->tag_id) {
    global $ez_database;
    global $qr_database;
    $city_suggets_name = 'suggested_products_' . $data->slug;
    $games = $ez_database->select('wp_options', ['option_value'], ['option_name' => $city_suggets_name]);
    if ($games) {
        $game_value = unserialize($games[0]["option_value"]);
        if (!empty($game_value["products"])) {
            // دریافت لیست idهای محصولات
            $product_ids = $game_value["products"];
            if (!empty($product_ids) && is_array($product_ids)) {
                // ساخت رشته idها برای استفاده در SQL
                $ids_str = implode(',', array_map('intval', $product_ids));
                $tag_id = '%' . intval($data->tag_id) . '%';

                // جستجو در جدول products_data برای product_idهایی که هم در لیست هستند و هم tags_id مشابه دارند
                // استفاده از Medoo با [product_id] و [tags_id[~]] برای LIKE
                $results = $qr_database->select('products_data', ['product_id'], [
                    'product_id' => $product_ids,
                    'tags_id[~]' => intval($data->tag_id)
                ]);
                // خروجی product_idهای پیدا شده
                echo json_encode(['product_ids' => $results]);
            } else {
                echo json_encode(['product_ids' => []]);
            }
        } else {
            echo json_encode(['product_ids' => []]);
        }
    } else {
        echo json_encode(['product_ids' => []]);
    }
}
