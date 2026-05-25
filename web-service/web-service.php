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

require 'db-connect.php';
if ( ! function_exists( 'jdate' ) ) {
	require_once __DIR__ . '/jdf.php';
}
require 'helper-functions.php';

global $conn;

if (!($_SERVER['HTTP_HOST'] == 'escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'escapezoom.co' || $_SERVER['HTTP_HOST'] == 'bak.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'dev-api.escapezoom.ir' || $_SERVER['HTTP_HOST'] == 'goriza.ir' || $_SERVER['HTTP_HOST'] == 'dev.escapezoom.local')) {
    $conn->query(sprintf("INSERT INTO hackers (host, referer) VALUES ('%s', '%s')", $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']));
    die('Get outta here');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $content_type = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';

    if (str_contains($content_type, 'application/json'))
        $data = json_decode(file_get_contents("php://input"));

    elseif (str_contains($content_type, 'application/x-www-form-urlencoded'))
        $data = json_decode(json_encode($_POST));

    else {
        http_response_code(415);
        echo json_encode(['error' => 'Unsupported Media Type']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Invalid Request Method']);
}

//$home_url = 'https://escapezoom.ir';
if ($_SERVER['HTTP_HOST'] == 'dev.escapezoom.local') {
    $home_url = 'http://dev.escapezoom.local';
} else {
    $home_url = 'https://' . $_SERVER['HTTP_HOST'];
}

/*******************************************/
if ($data->type == 'data_products_set') {
    //    logintotag(file_get_contents("php://input"));
}
//die();
/*******************************************/
if ($data->type == 'popular_products_set') {

    $products = $data->data;

    $temp = [];

    $products_alt = [];
    //    $products_views30 = [];
    foreach ($products as $product_id => $data) {

        $result = $conn->query("SELECT * FROM product_views WHERE product_id LIKE '" . $product_id . "'");

        if ($result->num_rows > 0)
            $row = $result->fetch_assoc();

        $views      = $row['views'];
        $views30    = array_sum(array_slice((array)unserialize($row['views30']), -31, 30, true));

        $temp[$product_id] = ['comments_count' => $data->comments_count, 'rate' => $data->rate, 'views' => $row['views'], 'views30' => $views30];

        $products_alt[$product_id] = round(($data->comments_count * $data->rate) + ($row['views'] * $views30 / 925000)); // (تعداد کامنت ها * امتیاز کامنت ها) * ( بازدید کل * بازدید30روز / 925000 )

        //        $products_views30[$product_id] = $views30;
    }

    //    asort($products_views30);
    //    logintotag($products_views30);

    asort($products_alt);

    foreach ($products_alt as $product_id => $value)
        $popular_products[] = $product_id;

    $popular_products = array_reverse($popular_products);

    $result = $conn->query("SELECT * FROM `products_order`");

    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `popular`= '%s'",  serialize($popular_products)));

    else
        $conn->query(sprintf("INSERT INTO products_order (popular) VALUES ('%s')", serialize($popular_products)));
}
/*******************************************/
if ($data->type == 'topsale_products_set') {

    $products = $data->data;

    $result = $conn->query("SELECT * FROM `products_order`");

    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `topsale`= '%s'",  serialize($products)));

    else
        $conn->query(sprintf("INSERT INTO products_order (topsale) VALUES ('%s')", serialize($products)));
}
/*******************************************/
if ($data->type == 'recent_products_set') {

    $products = $data->data;

    $result = $conn->query("SELECT * FROM `products_order`");

    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `recent`= '%s'", serialize($products)));

    else
        $conn->query(sprintf("INSERT INTO products_order (recent) VALUES ('%s')", serialize($products)));
}
/*******************************************/
if ($data->type == 'trend_products_set') {

    $products = $data->data;

    $result = $conn->query("SELECT * FROM `products_order`");

    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `trend`= '%s'",  serialize($products)));

    else
        $conn->query(sprintf("INSERT INTO products_order (trend) VALUES ('%s')", serialize($products)));
}
/*******************************************/
if ($data->type == 'hottest_products_set') {

    $products = $data->data;

    $hottest_products = $products;

    $result = $conn->query("SELECT * FROM `products_order`");
    $recent_products = [];
    $row_exists = false;
    if ($result->num_rows > 0) {
        $row_exists = true;
        $row = $result->fetch_assoc();
        if (!empty($row['recent'])) {
            $recent_products = unserialize($row['recent']);
        }
    }

    $hottest_products_ids = is_array($hottest_products) ? $hottest_products : [];

    $hottest_lookup = array_flip($hottest_products_ids);

    $recent_products_to_add = [];
    if (is_array($recent_products)) {
        foreach ($recent_products as $recent_product_id) {
            if (!isset($hottest_lookup[$recent_product_id])) {
                $recent_products_to_add[] = $recent_product_id;
            }
        }
    }

    $final_products = array_merge($hottest_products_ids, $recent_products_to_add);

    // ذخیره لیست نهایی
    if ($row_exists)
        $conn->query(sprintf("UPDATE `products_order` SET `hottest`= '%s'",  serialize($final_products)));

    else
        $conn->query(sprintf("INSERT INTO products_order (hottest) VALUES ('%s')", serialize($final_products)));


    //
    //
    //    $result = $conn->query("SELECT * FROM `products_order`");
    //
    //    if ($result->num_rows > 0)
    //        $conn->query(sprintf("UPDATE `products_order` SET `hottest`= '%s'",  serialize($products)));
    //
    //    else
    //        $conn->query(sprintf("INSERT INTO products_order (hottest) VALUES ('%s')", serialize($products)));
}
/*******************************************/
if ($data->type == 'nuwruz_products_set') {

    $products = $data->data;

    $result = $conn->query("SELECT * FROM `products_order`");

    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `nuwruz`= '%s'",  serialize($products)));

    else
        $conn->query(sprintf("INSERT INTO products_order (nuwruz) VALUES ('%s')", serialize($products)));
}
/*******************************************/
if ($data->type == 'ez_calendar') {

    $calendar_data = $data->data;

    $result = $conn->query("SELECT * FROM `calendar_data`");

    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `calendar_data` SET `data`= '%s'",  serialize($calendar_data)));

    else
        $conn->query(sprintf("INSERT INTO calendar_data (data) VALUES ('%s')", serialize($calendar_data)));
}
/*******************************************/
if ($data->type == 'data_products_set') {
    $res = $conn->query("DELETE FROM `products_data` WHERE `active` = 'active' OR `active` = 'updated'");

    $products = $data->data;

    //    $products = 'a:1:{i:866;O:8:"stdClass":32:{s:2:"id";s:4:"4134";s:4:"type";s:17:"اتاق فرار";s:5:"title";s:8:"سقوط";s:5:"price";s:6:"230000";s:7:"notable";s:1:"0";s:7:"special";s:1:"1";s:6:"active";s:1:"1";s:8:"brand_id";s:3:"691";s:13:"discount_data";s:0:"";s:3:"geo";s:33:"35.8267038759784,50.9434103965759";s:5:"image";s:24:"2021/04/fall-300-370.jpg";s:9:"age_limit";s:2:"16";s:5:"level";s:1:"1";s:8:"schedule";O:8:"stdClass":2:{s:7:"normals";a:6:{i:0;O:8:"stdClass":3:{s:4:"time";s:5:"12:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:1;O:8:"stdClass":3:{s:4:"time";s:5:"14:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:2;O:8:"stdClass":3:{s:4:"time";s:5:"16:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:3;O:8:"stdClass":3:{s:4:"time";s:5:"18:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:4;O:8:"stdClass":3:{s:4:"time";s:5:"20:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:5;O:8:"stdClass":3:{s:4:"time";s:5:"22:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}}s:8:"holidays";a:6:{i:0;O:8:"stdClass":3:{s:4:"time";s:5:"12:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:1;O:8:"stdClass":3:{s:4:"time";s:5:"14:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:2;O:8:"stdClass":3:{s:4:"time";s:5:"16:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:3;O:8:"stdClass":3:{s:4:"time";s:5:"18:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:4;O:8:"stdClass":3:{s:4:"time";s:5:"20:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:5;O:8:"stdClass":3:{s:4:"time";s:5:"22:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}}}s:8:"duration";s:3:"100";s:3:"url";s:26:"اتاق-فرار-سقوط";s:4:"hood";s:10:"گلشهر";s:7:"city_id";s:3:"162";s:9:"city_name";s:6:"کرج";s:12:"auto_disable";s:2:"30";s:11:"pish_person";s:1:"1";s:12:"contact_info";O:8:"stdClass":3:{s:11:"owner_phone";s:10:"9385198118";s:7:"chat_id";s:0:"";s:15:"manager_chat_id";s:1:"0";}s:11:"owner_phone";s:10:"9385198118";s:7:"chat_id";s:0:"";s:8:"owner_id";s:4:"3082";s:10:"manager_id";s:0:"";s:14:"comments_count";s:3:"352";s:4:"rate";s:4:"4.79";s:9:"count_min";s:1:"5";s:9:"count_max";s:2:"10";s:7:"tags_id";a:3:{i:0;s:3:"124";i:1;s:3:"512";i:2;s:3:"739";}s:10:"tags_title";a:3:{i:0;s:17:"|||||ترسناک";i:1;s:17:"|||||تعاملی";i:2;s:10:"گلشهر";}}}';

    //    $products = unserialize($products);

    $products = json_decode(json_encode($products));

    foreach ($products as $product) {

        $sql = sprintf(
            "
            INSERT INTO products_data (
                product_id,
                product_type,
                title,
                price,
                notable,
                special,
                active,
                monopoly,
                brand_id,
                discount_data,
                instant_off,
                geo,
                image,
                age_limit,
                level,
                schedule,
                duration,
                url,
                hood,
                city_id,
                city_name,
                tags_id,
                tags_title,
                count_min,
                count_max,
                pish_person,
                auto_disable,
                contact_info,
                owner_id,
                manager_id,
                comments_count,
                rate

            ) VALUES (
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            )",
            $product->id,
            $product->type,
            $product->title,
            $product->price,
            $product->notable,
            $product->special,
            $product->active,
            $product->monopoly,
            $product->brand_id,
            !empty($product->discount_data) ? serialize($product->discount_data) : '',
            !empty($product->instant_off) ? serialize($product->instant_off) : NULL,
            $product->geo,
            $product->image,
            $product->age_limit,
            $product->level,
            serialize($product->schedule),
            $product->duration,
            $product->url,
            $product->hood,
            $product->city_id,
            $product->city_name,
            serialize($product->tags_id),
            serialize($product->tags_title),
            $product->count_min,
            $product->count_max,
            $product->pish_person,
            (int)($product->auto_disable),
            serialize($product->contact_info),
            $product->owner_id,
            $product->manager_id,
            $product->comments_count,
            $product->rate,
        );

        //        logintotag($product->id);

        $conn->query($sql);
        /*==========================*/
        // update product view with all the products
        $res = $conn->query("SELECT * FROM `product_views` WHERE product_id LIKE '" . $product->id . "'");
        if ($res->num_rows < 1)
            $conn->query("INSERT INTO product_views (product_id) VALUES ($product->id)");
    }


    // اگه دوپلیکیت داشتیم پاک کن
    $sql = "DELETE p1 FROM products_data p1
        INNER JOIN products_data p2
        WHERE
        p1.product_id = p2.product_id AND
        p1.ID > p2.ID";

    $conn->query($sql);
}
/*******************************************/
if ($data->type == 'data_products_set_nactive') {
    $res = $conn->query("DELETE FROM `products_data` WHERE (`active` != 'active' AND `active` != 'updated')");

    $products = $data->data;

    //    $products = 'a:1:{i:866;O:8:"stdClass":32:{s:2:"id";s:4:"4134";s:4:"type";s:17:"اتاق فرار";s:5:"title";s:8:"سقوط";s:5:"price";s:6:"230000";s:7:"notable";s:1:"0";s:7:"special";s:1:"1";s:6:"active";s:1:"1";s:8:"brand_id";s:3:"691";s:13:"discount_data";s:0:"";s:3:"geo";s:33:"35.8267038759784,50.9434103965759";s:5:"image";s:24:"2021/04/fall-300-370.jpg";s:9:"age_limit";s:2:"16";s:5:"level";s:1:"1";s:8:"schedule";O:8:"stdClass":2:{s:7:"normals";a:6:{i:0;O:8:"stdClass":3:{s:4:"time";s:5:"12:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:1;O:8:"stdClass":3:{s:4:"time";s:5:"14:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:2;O:8:"stdClass":3:{s:4:"time";s:5:"16:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:3;O:8:"stdClass":3:{s:4:"time";s:5:"18:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:4;O:8:"stdClass":3:{s:4:"time";s:5:"20:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:5;O:8:"stdClass":3:{s:4:"time";s:5:"22:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}}s:8:"holidays";a:6:{i:0;O:8:"stdClass":3:{s:4:"time";s:5:"12:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:1;O:8:"stdClass":3:{s:4:"time";s:5:"14:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:2;O:8:"stdClass":3:{s:4:"time";s:5:"16:00";s:5:"price";s:6:"230000";s:9:"off_price";s:1:"0";}i:3;O:8:"stdClass":3:{s:4:"time";s:5:"18:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:4;O:8:"stdClass":3:{s:4:"time";s:5:"20:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}i:5;O:8:"stdClass":3:{s:4:"time";s:5:"22:00";s:5:"price";s:6:"250000";s:9:"off_price";s:1:"0";}}}s:8:"duration";s:3:"100";s:3:"url";s:26:"اتاق-فرار-سقوط";s:4:"hood";s:10:"گلشهر";s:7:"city_id";s:3:"162";s:9:"city_name";s:6:"کرج";s:12:"auto_disable";s:2:"30";s:11:"pish_person";s:1:"1";s:12:"contact_info";O:8:"stdClass":3:{s:11:"owner_phone";s:10:"9385198118";s:7:"chat_id";s:0:"";s:15:"manager_chat_id";s:1:"0";}s:11:"owner_phone";s:10:"9385198118";s:7:"chat_id";s:0:"";s:8:"owner_id";s:4:"3082";s:10:"manager_id";s:0:"";s:14:"comments_count";s:3:"352";s:4:"rate";s:4:"4.79";s:9:"count_min";s:1:"5";s:9:"count_max";s:2:"10";s:7:"tags_id";a:3:{i:0;s:3:"124";i:1;s:3:"512";i:2;s:3:"739";}s:10:"tags_title";a:3:{i:0;s:17:"|||||ترسناک";i:1;s:17:"|||||تعاملی";i:2;s:10:"گلشهر";}}}';

    //    $products = unserialize($products);

    $products = json_decode(json_encode($products));

    foreach ($products as $product) {

        $sql = sprintf(
            "
            INSERT INTO products_data (
                product_id,
                product_type,
                title,
                price,
                notable,
                special,
                active,
                monopoly,
                brand_id,
                discount_data,
                instant_off,
                geo,
                image,
                age_limit,
                level,
                schedule,
                duration,
                url,
                hood,
                city_id,
                city_name,
                tags_id,
                tags_title,
                count_min,
                count_max,
                pish_person,
                auto_disable,
                contact_info,
                owner_id,
                manager_id,
                comments_count,
                rate

            ) VALUES (
                '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'
            )",
            $product->id,
            $product->type,
            $product->title,
            (int)($product->price),
            $product->notable,
            $product->special,
            $product->active,
            $product->monopoly,
            (int)($product->brand_id),
            !empty($product->discount_data) ? serialize($product->discount_data) : '',
            !empty($product->instant_off) ? serialize($product->instant_off) : NULL,
            $product->geo,
            $product->image,
            (int)($product->age_limit),
            (int)($product->level),
            serialize($product->schedule),
            (int)($product->duration),
            $product->url,
            $product->hood,
            $product->city_id,
            $product->city_name,
            serialize($product->tags_id),
            serialize($product->tags_title),
            $product->count_min,
            $product->count_max,
            $product->pish_person,
            (int)($product->auto_disable),
            serialize($product->contact_info),
            $product->owner_id,
            $product->manager_id,
            $product->comments_count,
            $product->rate,
        );

        $conn->query($sql);
        /*==========================*/
        // update product view with all the products
        $res = $conn->query("SELECT * FROM `product_views` WHERE product_id LIKE '" . $product->id . "'");
        if ($res->num_rows < 1)
            $conn->query("INSERT INTO product_views (product_id) VALUES ($product->id)");
    }

    // اگه دوپلیکیت داشتیم پاک کن
    $sql = "DELETE p1 FROM products_data p1
        INNER JOIN products_data p2
        WHERE
        p1.product_id = p2.product_id AND
        p1.ID > p2.ID";

    $conn->query($sql);
}
/*******************************************/
if ($data->type == 'schedule_products_set') {

    $products = $data->data;
    $products = json_decode(json_encode($products));

    foreach ($products as $product) {
        $sql = sprintf("UPDATE `products_data` SET `schedule`= '%s' WHERE `product_id` = '%s'", serialize($product->schedule), $product->id);
        $conn->query($sql);
    }
}
/*******************************************/
if ($data->type == 'single_schedule_products_set') {

    $reserved_booking = $data->data;
    $reserved_booking = json_decode(json_encode($reserved_booking));

    $result = $conn->query("SELECT * FROM products_data WHERE product_id LIKE '" . $reserved_booking->product_id . "'");

    if ($result->num_rows > 0)
        $row = $result->fetch_assoc();

    $schedule = unserialize($row['schedule']);

    if ($reserved_booking->state == 'add')
        $schedule[] = $reserved_booking->booking;

    elseif ($reserved_booking->state == 'remove')
        if (($key = array_search($reserved_booking->booking, (array)$schedule)) !== false)
            unset($schedule[$key]);

    $sql = sprintf("UPDATE `products_data` SET `schedule`= '%s' WHERE `product_id` = '%s'", serialize($schedule), $reserved_booking->product_id);
    $conn->query($sql);
}
/*******************************************/
if ($data->type == 'sort_products_get') {

    $args = $data->data;

    $source             = $args->source;
    $limit              = $args->limit;
    $sort_type          = $args->sort_type;
    $page               = $args->page;
    $is_mobile          = $args->is_mobile;
    $only_events        = $args->only_events;
    $event_type         = $args->event_type;
    $most_discounts     = $args->most_discount;
    $only_ads           = $args->only_ads;
    $deactivate         = $args->deactivate;
    $exclude_ads        = $args->exclude_ads;
    $format             = $args->format;
    $unpin_ads          = $args->unpin_ads;
    $badge_ads          = !isset($args->badge_ads) ? true : $args->badge_ads;
    $random             = $args->random;
    $random_memory      = $args->random_memory ? explode(',', $args->random_memory) : [];
    $show_more          = $args->show_more;
    $show_more_url      = $args->show_more_url;
    $only_free_sanses   = $args->only_free_sanses;
    $active_soon        = $args->active_soon;

    if (!isset($args->params)) // initialized params if it's undefined
        $args->params = new stdClass();

    if ($source) {
        $format = $format == 'api' ? $format : 'html_swiper';

        if ($source == 'home_trends') {
            $sort_type  = 'trend';
            $random     = true;
            $limit      = 40;
        }

        if ($source == 'home_quick_search') {
            $limit      = 150;
            $sort_type  = 'recent';
            $random     = true;
            $deactivate = true;
        }

        if ($source == 'home_cities_escaperoom') {
            $limit                      = 40;
            $sort_type                  = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $args->params->product_type = "اتاق فرار";
            $unpin_ads                  = false;
        }

        if ($source == 'home_cities_cinema') {
            $limit                      = 40;
            $sort_type                  = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $args->params->product_type = "سینما ترس";
        }

        if ($source == 'home_cities_lasertag') {
            $limit                      = 40;
            $sort_type                  = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $args->params->product_type = "لیزرتگ";
        }

        if ($source == 'home_discounts_event') {
            $limit          = 40;
            $sort_type      = !is_null($args->params->sort_type) ? $args->params->sort_type : 'recent';
            $only_events    = true;
            $event_type     = 'discount';
            $random         = true;

            if ( $most_discounts || !is_null($args->params->sort_type) )
                $random = false;
        }

        if ($source == 'map_search') {
            $limit              = 60;
            $sort_type          = 'recent';
            $only_free_sanses   = true;
            $random             = true;
            $format             = 'api';
        }

        if ($source == 'cat_trends') {
            $sort_type  = 'trend';
            $random     = true;
        }

        if (str_contains($source, 'city_page_product_')) {
            $args->params->city_id  = [explode('city_page_product_', $source)[1]];
            $limit                  = 40;
            $sort_type              = 'hottest';
            $random                 = true;
        }

        if (str_contains($source, 'city_page_discounts_event_')) {
            $args->params->city_id  = explode(',', explode('city_page_discounts_event_', $source)[1]);
            $limit                  = 40;
            $sort_type              = 'recent';
            $only_events            = true;
            $event_type             = 'discount';
            $random                 = true;
        }

        if (str_contains($source, 'type_page_cat_')) {
            $type_city      = explode('type_page_cat_', $source)[1];
            $product_type   = explode('_', $type_city)[0];
            $city_id        = explode('_', $type_city)[1];

            $args->params->city_id      = $city_id == -1 ?: [$city_id];
            $limit                      = 40;
            $sort_type                  = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $random                     = !is_null($args->params->sort_type) ? false : true;
            $args->params->product_type = get_product_type_equivalent($product_type);
        }

        if ($source == 'cat_sansyab') {

            $sort_type  = 'recent';
            $random     = false;

            if ($args->params->sort_type == -1) {
                $random = true;
                $random_memory = $args->params->random_memory ? explode(',', $args->params->random_memory) : [];
            } else
                $sort_type = !is_null($args->params->sort_type) ? $args->params->sort_type : 'recent';

            $limit                  = $limit ?: 200;
            $page                   = !is_null($args->params->page) ? $args->params->page : 1;
            $args->max_num_pages    = true;
        }

        if (str_contains($source, 'type_page_discounts_event_')) {
            $args->params->product_type = get_product_type_equivalent(explode('type_page_discounts_event_', $source)[1]);
            $limit                      = 40;
            $sort_type                  = 'recent';
            $only_events                = true;
            $event_type                 = 'discount';
            $random                     = true;
        }

        if (str_contains($source, 'type_page_escaperoom_genre_')) {
            $sort_type                  = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $limit                      = 40;
            $args->params->tag          = explode('type_page_escaperoom_genre_', $source)[1] == 'horror' ? [124] : -124;
            $unpin_ads                  = true;
            $badge_ads                  = false;
            $args->params->city_id      = -1;
            $random                     = true;
            $args->params->product_type = "اتاق فرار";
        }

        if ($source == 'genre_page') {
            $sort_type                  = !is_null($args->params->sort_type) ? $args->params->sort_type : 'recent';
            $limit                      = 40;
            $unpin_ads                  = false;
            $badge_ads                  = false;
            $random                     = false;
            $format                     = 'html_list';
        }

        if ($source == 'hood_page') {
            $sort_type              = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $limit                  = 40;
            $unpin_ads              = false;
            $badge_ads              = false;
            $args->params->city_id  = -1;
            $random                 = false;
        }

        if ($source == 'typecity_page_ads') {
            $sort_type              = !is_null($args->params->sort_type) ? $args->params->sort_type : 'recent';
            $limit                  = 40;
            $unpin_ads              = true;
            $badge_ads              = true;
            $random                 = true;
            $only_ads               = true;
        }

        if ($source == 'typecity_page_monopoly') {
            $sort_type              = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $limit                  = 40;
            $unpin_ads              = true;
            $badge_ads              = false;
            $random                 = true;
        }

        if (str_contains($source, 'typecity_page_genre_')) {

            $genre = explode('typecity_page_genre_', $source)[1];
            if ($genre)  // اگر در کل ژانری ست شده بود.
                if ($genre == 'horror')
                    $args->params->tag = [124];
                elseif ($genre == 'exciting')
                    $args->params->tag = [178];
                elseif ($genre == 'family')
                    $args->params->tag = [-124. - 178];
                elseif ($genre == 'nonhorror')
                    $args->params->tag = -124;

            //            $args->params->tag  = explode('typecity_page_genre_', $source)[1] == 'horror' ? [124] : -124;
            $sort_type          = !is_null($args->params->sort_type) ? $args->params->sort_type : 'hottest';
            $unpin_ads          = true;
            $limit              = 40;
            $badge_ads          = false;
            $random             = false;
        }
    }

    if (!empty($args->url)) {
        if ($_SERVER['HTTP_HOST'] == 'dev.escapezoom.local') {
            $home_url = 'http://' . $args->url;
        } else {
            $home_url = 'https://' . $args->url;
        }
    }

    $only_ads_rows = [];
    if ($sort_type == 'hottest' && !$unpin_ads) {
        $result = $conn->query("SELECT product_id FROM `products_data` WHERE active LIKE 'active' AND special LIKE 1;");
        if ($result->num_rows > 0)
            $only_ads_rows = array_column($result->fetch_all(MYSQLI_ASSOC), 'product_id');
    }

    $result = $conn->query(sprintf("SELECT %s from products_order",  $sort_type));
    if ($result->num_rows > 0)
        $row = $result->fetch_assoc();

    $products_id = unserialize($row[$sort_type]);

    $products_id = array_unique(array_merge($products_id, $only_ads_rows));

    if ($source == 'suggested') // در جدول sorts این مورد با بقیه فرق داره
        $products_id = $products_id[$args->params->slug];

    if ($random) {
        $products_id = array_diff($products_id, $random_memory);
        shuffle($products_id);
    }

    if ($deactivate)
        $where_clauses[] = "(active = 'active' OR active = 'deactivated' OR active = 'soon' OR active = 'expired' OR active = 'temp' OR active = 'updated')";
    else {

        $base_condition = "(active = 'active' OR active = 'updated')";
        if ($sort_type == 'recent' and $active_soon)
            $base_condition .= " OR active = 'soon'";
        $where_clauses[] = "($base_condition)";
    }

    if (isset($args->params->exclude_products))
        $where_clauses[] = "product_id NOT IN (" . implode(',', $args->params->exclude_products) . ")";

    $sql = "SELECT * FROM products_data WHERE " . implode(' AND ', $where_clauses);

    $nactive_products_data = [];

    $products_obj = '';
    $result = $conn->query($sql);
    if ($result->num_rows > 0)
        while ($products_obj = $result->fetch_assoc()) {

            foreach (unserialize($products_obj['tags_title']) as $key => $tag_title) {
                if (str_contains($tag_title, '|||||'))
                    $products_obj['genres'][] = [
                        'title' => str_replace('|||||', '', $tag_title),
                        'id'    => (int)unserialize($products_obj['tags_id'])[$key],
                    ];
                else
                    $products_obj['tags'][] = [
                        'title' => $tag_title,
                        'id'    => (int)unserialize($products_obj['tags_id'])[$key],
                    ];
            }

            if ($products_obj['active'] == 'active' or $products_obj['active'] == 'updated')
                $products_data[$products_obj['product_id']] = $products_obj;
            else
                $nactive_products_data[$products_obj['product_id']] = $products_obj;
        }

    $sorted_product_list = [];
    foreach ($products_id as $product_id)
        if (isset($products_data[$product_id]))
            $sorted_product_list[] = $products_data[$product_id];

    $products = $sorted_product_list;

    $products = array_merge($products, $nactive_products_data);

    $schedule_arg = $args->params->schedule;

    if (($schedule_arg != -1 && $schedule_arg[0] != -1 && !empty($schedule_arg)) && $schedule_arg[1] > time()) { // if schedule filter is requested

        $schedule_initial_date = (new DateTime('@' . $schedule_arg[0]))->setTimezone(new DateTimeZone('Asia/Tehran'))->format('Y-m-d'); // کدام روز؟ امروز؟ فردا؟ پسفردا؟

        if ($schedule_initial_date >= date('Y-m-d')) {

            $day_type = get_day_type2($schedule_arg[0]);

            $result = $conn->query("SELECT `room_id`, `booking_time` FROM wp_zb_booking_history WHERE booking_time >= UNIX_TIMESTAMP()");
            if ($result->num_rows > 0)
                $bookings = $result->fetch_all(MYSQLI_ASSOC);

            foreach ($bookings as $booking)
                if (time() <= $booking['booking_time'])
                    $room_booked[$booking['room_id']][] = $booking['booking_time']; // سانس های پر شده

            foreach ($products as $product) :
                $product_id = $product['product_id'];

                $sanses = json_decode(json_encode(unserialize($products_data[$product_id]['schedule'])), true);

                $schedule_list = [];
                foreach ($sanses[$day_type] as $sans) {
                    $firstTimeTs = strtotime($schedule_initial_date . ' ' . $sans['time'] . ' Asia/Tehran');

                    if (!in_array($firstTimeTs, (array)$room_booked[$product_id])) // چک میکنه سانس بسته یا پرشده نشده باشه پس داره سانس های خالی رو پیدا میکنه
                        $schedule_list[] = $firstTimeTs;
                }

                $products_schedule[$product_id] = $schedule_list;
            endforeach;
        }
    }

    if ($sort_type == 'hottest' && !$unpin_ads) { // only popular type has special (AD) product order (Pinned ads at top)
        $special_arr        = [];
        $non_special_arr    = [];
        foreach ($products as $product) {
            if ($product['special'])
                $special_arr[] = $product;
            else
                $non_special_arr[] = $product;
        }

        shuffle($special_arr);

        $products = array_merge($special_arr, $non_special_arr);
    }

    if ($sort_type == 'recent' and $active_soon) { // اگر ترتیب بر اساس جدیدترین ها بود، محصولاتی که state به زودی دارن باید پین بشن

        $soon_arr = [];
        $non_soon_arr = [];
        foreach ($products as $product)
            if ($product['active'] == 'soon')
                $soon_arr[] = $product;
            else
                $non_soon_arr[] = $product;

        shuffle($soon_arr);

        $products = array_merge($soon_arr, $non_soon_arr);
    }

    if (isset($args->params)) {
        foreach ($args->params as $key => $param) {
            $temp_products = [];

            if ($key == 'brand_id') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param == $product['brand_id'])
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'product_type') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param == $product['product_type'])
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'city_id') {
                if ($param != -1) {
                    if (is_array($param)) {
                        foreach ($param as $city_id) {
                            foreach ($products as $product) {
                                if ($city_id == $product['city_id']) {
                                    $temp_products[] = $product;
                                }
                            }
                        }
                    } elseif (!$param) {
                        foreach ($products as $product) {
                            if (15 != $product['city_id'] and 162 != $product['city_id'] and 122 != $product['city_id']) {
                                $temp_products[] = $product;
                            }
                        }
                    }

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'tag') {
                if ($param != -1) {
                    if (is_array($param)) {
                        foreach ($products as $product) {
                            $product_tags = (array)unserialize($product['tags_id']);

                            $include_product = false;
                            foreach ($param as $tag_id) {
                                if ($tag_id > 0) {
                                    // Include if product has the positive tag
                                    if (in_array($tag_id, $product_tags)) {
                                        $include_product = true;
                                        break; // one positive tag match is enough
                                    }
                                } elseif ($tag_id < 0) {
                                    // Exclude if product has the negative tag (abs)
                                    if (in_array(abs($tag_id), $product_tags)) {
                                        $include_product = false;
                                        break 2; // exclude the product immediately
                                    }
                                }
                            }

                            if ($include_product) {
                                $flag = false;
                                foreach ($temp_products as $temp_product) {
                                    if ($temp_product['product_id'] == $product['product_id']) {
                                        $flag = true;
                                        break;
                                    }
                                }

                                if (!$flag) {
                                    $temp_products[] = $product;
                                }
                            }
                        }
                    } elseif ($param < 0) {
                        foreach ($products as $product) {
                            $product_tags = (array)unserialize($product['tags_id']);
                            if (!in_array(abs($param), $product_tags)) {
                                $flag = false;
                                foreach ($temp_products as $temp_product) {
                                    if ($temp_product['product_id'] == $product['product_id']) {
                                        $flag = true;
                                        break;
                                    }
                                }
                                if (!$flag) {
                                    $temp_products[] = $product;
                                }
                            }
                        }
                    }

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'schedule') {
                if ($param != -1 and $param[0] != -1) {
                    foreach ($products as $product) {
                        foreach ($products_schedule[$product['product_id']] as $unix) {
                            if ($param[0] <= $unix and $unix <= $param[1]) {
                                $temp_products[] = $product;
                                break;
                            }
                        }
                    }
                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'price') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param[0] <= $product['price'] and $product['price'] <= $param[1])
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'level') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param == $product['level'])
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'monopoly') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param == $product['monopoly'])
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'age') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param <= $product['age_limit'] and $product['age_limit'] <= $param)
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'duration') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ($param <= $product['duration'] and $product['duration'] <= $param)
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'count') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if ((int)$param <= (int)$product['count_max'])
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }

            if ($key == 'bounds') {
                if ($param != -1) {
                    foreach ($products as $product)
                        if (is_point_within_bounds($product['geo'], $param))
                            $temp_products[] = $product;

                    $products = $temp_products;
                    $temp_products = [];
                }
            }
        }
    } // filters

    if ($only_events) {

        $event_arr = [];
        foreach ($products as $product)
            if ($event_type == 'discount')
                if (!empty(unserialize($product['discount_data'])) and unserialize($product['discount_data'])->special_discount_date > time())
                    $event_arr[] = $product;

        if ( $most_discounts ) {

            usort($event_arr, function($a, $b) {
                $ad = unserialize($a['discount_data']);
                $bd = unserialize($b['discount_data']);

                $a_percent = isset($ad->special_discount_percentage) ? (int)$ad->special_discount_percentage : 0;
                $b_percent = isset($bd->special_discount_percentage) ? (int)$bd->special_discount_percentage : 0;


                return $b_percent <=> $a_percent;
            });
        }

        $products = $event_arr;
    }

    if ($only_ads) {
        $special_arr = [];
        foreach ($products as $product)
            if ($product['special'])
                $special_arr[] = $product;

        shuffle($special_arr);
        $products = $special_arr;
    }

    if ($exclude_ads) {
        $non_special_arr = [];
        foreach ($products as $product)
            if (!$product['special'])
                $non_special_arr[] = $product;

        $products = $non_special_arr;
    }

    $page   = $page ?: 1;
    $limit  = $limit ?: (count($products) ?: 1);

    $max_num_pages = ceil(count($products) / (int)$limit);

    if ($random)
        $products = array_slice($products, 0, $limit);
    else
        $products = array_slice($products, ($page - 1) * $limit, $limit); // this plays the pagination role

    $products_clone = $products; // remembers these ids to exclude for the next time (page:2, page:3 , ....)

    $products = format_products_to_html_query($products, $format, $is_mobile, $show_more, $show_more_url, $badge_ads, $only_free_sanses);

    $data = new stdClass();
    $data->products = $products;

    if ($args->max_num_pages)
        $data->max_num_pages = $max_num_pages;

    if ($random)
        foreach ($products_clone as $product_clone)
            $data->products_id[] = (int)$product_clone['product_id'];

    echo json_encode($data);
}
/*******************************************/
if ($data->type == 'get_by_products_id') {

    $args = $data->data;

    $products_id    = $args->products_id;
    $format         = $args->format;

    $products_id = implode(',', (array)$products_id ?? [0]);

    $query = sprintf(
        "SELECT * FROM products_data WHERE product_id IN (%s) ORDER BY FIELD(product_id, %s)",
        $products_id,
        $products_id
    );
    $result = $conn->query($query);
    if ($result->num_rows > 0)
        while ($row = $result->fetch_assoc())
            $products[] = $row;

    $products = json_decode(json_encode($products));

    foreach ($products as $pkey => $product) {
        foreach (unserialize($product->tags_title) as $tkey => $tag_title) {
            if (str_contains($tag_title, '|||||'))
                $products[$pkey]->genres[] = [
                    'title' => str_replace('|||||', '', $tag_title),
                    'id'    => (int)unserialize($product->tags_id)[$tkey],
                ];
            else
                $products[$pkey]->tags[] = [
                    'title' => $tag_title,
                    'id'    => (int)unserialize($product->tags_id)[$tkey],
                ];
        }
    }

    $products = format_products_to_html_query($products, $format, 0, 0, 0, 0, 0);

    echo json_encode($products);
}
/*******************************************/
if ($data->type == 'post_view_process') {

    $args = $data->data;

    $product_id = $args->product_id;
    $ip         = $args->ip;
    $user_agent = $args->user_agent;

    $user_agent_black_list = ['bingbot', 'Facebot', 'TelegramBot', 'Googlebot', 'AdsBot',  'WhatsApp', 'AhrefsBot', 'lscache_runner', 'python', 'bot', 'Bot', 'MJ12bot', 'YandexBot', 'SemrushBot', 'Discordbot', 'BLEXBot', 'Applebot'];
    foreach ($user_agent_black_list as $bot)
        if (substr_count($user_agent, $bot) > 0)
            exit;

    $res = $conn->query("SELECT * FROM post_view_ip_checker WHERE product_id LIKE '" . $product_id . "' AND ip LIKE '" . $ip . "'");

    if ($res->num_rows < 1) {

        $result = $conn->query("SELECT * FROM product_views WHERE product_id LIKE '" . $product_id . "'");

        if ($result->num_rows > 0)
            $row = $result->fetch_assoc();

        $views      = $row['views'];
        $views30    = unserialize($row['views30']);
        $diff       = round((time() - strtotime("2023-08-13")) / (60 * 60 * 24));

        if (isset($views30[$diff]))
            $views30[$diff] += 1;
        else
            $views30[$diff] = 1;

        $conn->query(sprintf("UPDATE `product_views` SET `views`= '%s', `views30`= '%s' WHERE `product_id` = '%s'", ++$views, serialize($views30), $product_id)); // update views
        $conn->query(sprintf("INSERT INTO post_view_ip_checker (product_id, ip, view_at) VALUES ('%s', '%s', '%s')", $product_id, $ip, time()));
    }

    $conn->query(sprintf("DELETE FROM post_view_ip_checker WHERE %s - view_at > 24 * 60 * 60", time())); // check each time if any ip has passed its 24 hrs in black list so free up it again.
}
/*******************************************/
if ($data->type == 'cpc_tracking') {

    $args = $data->data;

    $ip     = $args->ip;
    $ref    = $args->ref;

    $user_agent_black_list = ['bingbot', 'Facebot', 'TelegramBot', 'Googlebot', 'AdsBot',  'WhatsApp', 'AhrefsBot', 'lscache_runner', 'python', 'bot', 'Bot', 'MJ12bot', 'YandexBot', 'SemrushBot', 'Discordbot', 'BLEXBot', 'Applebot'];
    foreach ($user_agent_black_list as $bot)
        if (substr_count($user_agent, $bot) > 0)
            exit;

    parse_str(parse_url($ref)['query'], $query_strings); // extract query strings into $query_strings

    if (empty($query_strings['utm_source']))
        exit;

    $res = $conn->query("SELECT * FROM cpc_tracking WHERE ip LIKE '" . $ip . "'");

    if ($res->num_rows < 1) { // new IP

        $source     = $query_strings['utm_source'];
        $medium     = $query_strings['utm_medium'];
        $terms      = [];
        $campaign   = $query_strings['utm_campaign'];
        $count      = 1;

        $terms[] = [
            'term' => $query_strings['utm_term'],
            'time' => time(),
        ];

        $conn->query(sprintf("INSERT INTO cpc_tracking (ip, medium, source, terms, campaign, count) VALUES ('%s', '%s', '%s', '%s', '%s', '%s')", $ip, $medium, $source, serialize($terms), $campaign, $count));
    } else {

        $row = $res->fetch_assoc();

        $terms  = unserialize($row['terms']);
        $count  = ++$row['count'];

        $exist_flag = 0;
        foreach ($terms as $key => $term)
            if ($term['term'] == $query_strings['utm_term'])
                $exist_flag = (int)$key;

        if ($exist_flag)
            $terms[$exist_flag]['time'] = time();

        else
            $terms[] = [
                'term' => $query_strings['utm_term'],
                'time' => time(),
            ];

        $conn->query(sprintf("UPDATE `cpc_tracking` SET `terms`= '%s', `count`= '%s' WHERE `ip` = '%s'", serialize($terms), $count, $ip));
    }
}
/*******************************************/
if ($data->type == 'load_plp_days') {

    ob_start();

    $args = $data->data;

    $product_id     = $args->product_id;
    $wp_is_mobile   = $args->wp_is_mobile;

    $get_time = strtotime(date('Y-m-d ') . ' ' . 'Asia/Tehran'); ?>

    <div data-time="<?php echo $get_time; ?>" data-ezservice="<?php echo $product_id; ?>" class="day-itemx active-day">
        <?php
        if ($wp_is_mobile): ?>

            <a class="day-num-view" data-time="<?php echo $get_time; ?>" data-ezservice="<?php echo $product_id; ?>" href="#">
                <p class="month-name-view"><span class='frist-day-view today'>امروز</span></p>
            </a>

        <?php
        else: ?>

            <a class="day-num-view " data-time="<?php echo $get_time; ?>" data-ezservice="<?php echo $product_id; ?>" href="#">
                <span class='frist-day-view today'>امروز</span>
            </a>

        <?php
        endif ?>
    </div>

    <?php
    $x = 1;
    while ($x <= 21) {

        if ($x == 1)
            $get_d = $get_time;

        if ($x != 1) { ?>

            <div data-time="<?php echo $get_d; ?>" data-ezservice="<?php echo $product_id; ?>" class="day-itemx d-<?php echo $x; ?>">
                <?php
                if ($wp_is_mobile): ?>
                    <a class="day-num-view" href="#" data-ezservice="<?php echo $product_id; ?>" data-time="<?php echo $get_d; ?>">
                        <p class="month-name-view">
                            <span class='frist-day-view'><?php echo jdate('j', $get_d) ?></span>
                            <span class='mobile-m-view'><?= jdate('l', $get_d) ?></span>
                        </p>
                    </a>

                <?php
                else: ?>
                    <a class="day-num-view" data-time="<?php echo $get_d; ?>" data-ezservice="<?php echo $product_id; ?>" href="#">
                        <span class='frist-day-view'><?= jdate('l', $get_d) ?></span>
                        <p class="month-name-view"><?= jdate('j', $get_d) ?></p>
                        <small class="day-name-view"><?= jdate('F', $get_d) ?></small>
                    </a>
                <?php
                endif ?>
            </div>
        <?php }
        $get_d = $get_d + 86400;
        $x++;
    }

    $res = ob_get_clean();

    //    automation_management($product_id);

    echo json_encode($res);
}
/********************************************************************************************************************************/
if ($data->type == 'update_product_discount_data') {

    $args = $data->data;

    $product_id     = $args->product_id;
    $discount_data  = $args->discount_data;

    $sql = sprintf("UPDATE `products_data` SET `discount_data`= '%s' WHERE `product_id` = '%s';", serialize($discount_data), $product_id);
    $conn->query($sql);
}
/*******************************************/
function format_products_to_html_query($products, $format, $is_mobile, $show_more, $show_more_url, $badge_ads, $only_free_sanses)
{

    $products = json_decode(json_encode($products));

    if ($format == 'html_swiper')
        return standardization_products_html_swiper($products, $only_free_sanses, $badge_ads);

    elseif ($format == 'html_list')
        return standardization_products_html_list($products, $only_free_sanses, $badge_ads);

    else
        return standardization_products($products, $only_free_sanses);

    return $products;
}
/*******************************************/
function standardization_products($products, $only_free_sanses = false)
{
    global $conn, $home_url;
    
    if (empty($home_url)) {
        if ($_SERVER['HTTP_HOST'] == 'dev.escapezoom.local') {
            $home_url = 'http://dev.escapezoom.local';
        } else {
            $home_url = 'https://escapezoom.ir';
        }
    }

    /*******************************/
    // Calculate free sanses counts

    $products_string = 0; // اگه محصولی نداریم پس خروجی کوئری دیتابیس باید خالی باشد.
    if (!empty($products)) {
        foreach ($products as $product)
            $products_arr[] = $product->product_id;
        $products_string = implode(',', $products_arr);
    }

    $result = $conn->query("SELECT `room_id`, `booking_time` FROM wp_zb_booking_history WHERE room_id IN ($products_string)");
    if ($result->num_rows > 0)
        $bookings = $result->fetch_all(MYSQLI_ASSOC);

    foreach ($bookings as $booking)
        if (time() <= $booking['booking_time'])
            $room_booked[$booking['room_id']][] = $booking['booking_time']; // سانس های پر شده

    /*******************************/

    foreach ($products as $product) {
        $product_id = $product->product_id;

        $event = [];
        if (!empty($product->discount_data)) {
            $event = [
                'off_percentage'    => (int)(unserialize($product->discount_data)->special_discount_percentage),
                'expire_date'       => (int)(unserialize($product->discount_data)->special_discount_date)
            ];
        }

        $free_sanses    = null;
        $sanses         = json_decode(json_encode(unserialize($product->schedule)), true);
        $schedule_list  = [];
        foreach ($sanses[get_day_type2(time())] as $sans) {
            $firstTimeTs = strtotime(date("Y-m-d", time()) . ' ' . $sans['time'] . ' Asia/Tehran');

            if (!in_array($firstTimeTs, (array)$room_booked[$product_id]) && time() + ((int)$product->auto_disable * 60) <= $firstTimeTs)
                $schedule_list[] = $firstTimeTs;
        }

        if ($schedule_list)
            $free_sanses = count($schedule_list);

        if (!$free_sanses and $only_free_sanses) continue;
        $city_name = $product->city_name;

        // حذف برخی پیشوندها از ابتدای نام دسته‌بندی شهر
        $remove_prefixes = [
            'اتاق فرار',
            'لیزرتگ',
            'سینما ترس',
            'اتاق خشم',
            'فوتبال حبابی',
            'کافه بازی',
            'بردگیم',
            'برد گیم',
            'پینتبال',
        ];

        foreach ($remove_prefixes as $prefix) {
            // اگر نام با پیشوند به همراه فاصله شروع شده باشد
            if (mb_strpos($city_name, $prefix . ' ') === 0) {
                $city_name = trim(mb_substr($city_name, mb_strlen($prefix)));
                break;
            }
            // اگر نام دقیقا با پیشوند (بدون فاصله بعدش) شروع شده باشد
            if (mb_strpos($city_name, $prefix) === 0) {
                $city_name = trim(mb_substr($city_name, mb_strlen($prefix)));
                break;
            }
        }
        $formatted_products[] = [
            'product_id'    => (int)$product_id,
            'type'          => get_product_type_equivalent($product->product_type),
            'title'         => $product->title,
            'price'         => (int)$product->price,
            'ads'           => $product->special ? true : false,
            'image'         => (isset($home_url) ? $home_url : 'https://escapezoom.ir') . '/wp-content/uploads/' . $product->image,
            'age'           => (int)$product->age_limit,
            'level'         => 5 - (int)$product->level,
            'duration'      => (int)$product->duration,
            'url'           => $product->url,
            'city_id'       => (int)$product->city_id,
            'city_name'     => $city_name,
            'hood_name'     => $product->hood,
            'genres'        => $product->genres,
            'tags'          => $product->tags,
            'number_min'    => (int)$product->count_min,
            'number_max'    => (int)$product->count_max,
            'event'         => $event,
            'comments_count' => (int)$product->comments_count,
            'rate'          => $product->rate,
            'free_sanses'   => $free_sanses,
            'geo'           => $product->geo,
            'active'        => $product->active,
        ];
    }

    return $formatted_products;
}
/*******************************************/
function standardization_products_html_swiper($products, $only_free_sanses = false)
{
    global $home_url;

    if (empty($home_url)) {
        if ($_SERVER['HTTP_HOST'] == 'dev.escapezoom.local') {
            $home_url = 'http://dev.escapezoom.local';
        } else {
            $home_url = 'https://escapezoom.ir';
        }
    }

    ob_start();

    $products = standardization_products($products, $only_free_sanses);
    foreach ($products as $product):

        $ads_badge = '';
        // if ($product['ads'])
        //     $ads_badge = '<span class="cart-class" style="position: absolute;top: 10px;right: 10px;width: 30px;height: 20px;justify-content: center;display: flex;align-items: center;border-radius: 6px;border: 1px solid #fff;color: #fff;padding: 5px 0 0;">AD</span>';

        $product_type = $product['type'];
        $product_cat_alt = null;
        switch ($product_type) {
            case 'cafegame':
                $product_cat_alt = 'کافه بازی ';
                break;
            case 'cinema':
                $product_cat_alt = 'سینما ترس ';
                break;
            case 'rageroom':
                $product_cat_alt = 'اتاق خشم ';
                break;
            case 'lasertag':
                $product_cat_alt = 'لیزرتگ ';
                break;
            case 'bubblefootball':
                $product_cat_alt = 'فوتبال حبابی ';
                break;
            case 'paintball':
                $product_cat_alt = 'پینت بال ';
                break;
            case 'haunted_house':
                $product_cat_alt = 'هانتد هاوس ';
                break;
            default:
                $product_cat_alt = 'اتاق فرار';
                break;
        } ?>

        <?php if ($product['active'] == 'temp' or $product['active'] == 'deactivated'): // رزرو غیرفعال 
        ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/" class="relative after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                        </a>
                        <span class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">غیرفعال</span>
                    </div>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
            </article>
        <?php elseif ($product['active'] == 'expired'): // اکسپایر شده 
        ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/" class="relative after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                            <?php
                            if ($product['active'] == 'updated'): ?>
                                <span style="position: absolute;color: #fff;background: #F21543;border-radius: 8px;padding: 0 15px;margin: 10px;font-size: 10px;top: 0">آپدیت شد</span>
                            <?php
                            endif; ?>
                        </a>
                        <span class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">اکسپایر شده</span>

                    </div>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
            </article>
        <?php elseif ($product['active'] == 'soon'): // به زودی 
        ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                            <span class="bg-[#2B7FFF] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">به زودی</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
            </article>
        <?php else: ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                            <?php
                            if ($product['active'] == 'updated'): ?>
                                <span class="bg-[#F21543] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">آپدیت شد</span>
                            <?php
                            endif; ?>
                        </a>
                        <button type="button"
                            class="absolute bottom-2 right-2 flex h-7.5 w-7.5 items-center justify-center rounded-full bg-[#EFC101]/30 lg:hidden mobile-hover">
                            <span class="flex h-4.5 w-4.5 items-center justify-center rounded-full bg-white drop-shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="5" height="11" viewBox="0 0 5 11" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M2.5 0C2.78179 0 3.05204 0.111941 3.2513 0.311198C3.45055 0.510455 3.5625 0.780706 3.5625 1.0625C3.5625 1.34429 3.45055 1.61454 3.2513 1.8138C3.05204 2.01305 2.78179 2.125 2.5 2.125C2.21821 2.125 1.94796 2.01305 1.7487 1.8138C1.54944 1.61454 1.4375 1.34429 1.4375 1.0625C1.4375 0.780706 1.54944 0.510455 1.7487 0.311198C1.94796 0.111941 2.21821 0 2.5 0Z"
                                        fill="#827748"></path>
                                    <path d="M2.71211 9.77811V3.82812H1.86211M1.22461 9.77811H4.1996" stroke="#827748"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <a href="<?= $home_url . '/room/' . $product['url'] ?>/"
                        class="absolute left-0 top-0 flex h-full w-full flex-col justify-between bg-slate-900/80 px-1.5 md:px-4 py-2.5 md:py-5 text-2xs text-white transition-all max-lg:hidden lg:scale-90 lg:opacity-0 lg:hover:scale-100 lg:hover:opacity-100">
                        <?php if ($product_type == 'escaperoom'): ?>
                            <div class="mx-auto flex w-[90%] items-center justify-center gap-x-1 rounded bg-white/20 px-6 py-1.5 leading-none">
                                <div class="max-lg:hidden lg:flex lg:item-center lg:justify-between w-full">
                                    <span class="text-[#FFFFFF]/70 text-2xs leading-none flex items-center">امروز</span>
                                    <?php if ($product['free_sanses']): ?>
                                        <span class="flex items-center gap-x-1">
                                            <span class="text-xl leading-none font-bold"><?= $product['free_sanses'] ?></span>
                                            <span class="text-2xs leading-none">سانس</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xl leading-none">تکمیله</span>
                                    <?php endif; ?>
                                </div>
                                <div class="lg:hidden py-1 text-nowrap text-3xs font-bold line-clamp-1" name="genres">
                                    <?php
                                    $titles = [];
                                    foreach ($product['genres'] as $genre) {
                                        $titles[] = $genre->title;
                                    }
                                    $result = implode(" . ", $titles);
                                    echo $result;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <span class="flex items-center justify-between py-1">
                            <span>
                                <span class="max-lg:hidden">مدت زمان سانس</span>
                                <span class="lg:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                        <path d="M7.00039 1.89844C3.96039 1.89844 1.40039 4.45844 1.40039 7.49844C1.40039 10.5384 3.96039 13.0984 7.00039 13.0984C10.0404 13.0984 12.6004 10.5384 12.6004 7.49844C12.6004 4.45844 10.0404 1.89844 7.00039 1.89844ZM8.63372 9.59844L6.53372 7.73177V3.7651H7.46706V7.2651L9.33372 8.89844L8.63372 9.59844Z"
                                            fill="white"></path>
                                    </svg>
                                </span>
                            </span>
                            <span><span class="ml-px text-base font-bold" dir="ltr"><?= $product['duration'] ?></span> دقیقه </span>
                        </span>
                        <span class="flex items-center justify-between border-b border-t border-b-white/50 border-t-white/50 py-3">
                            <?php if ($product_type == 'escaperoom'): ?>
                                <span>
                                    <span class="max-lg:hidden">میزان سختی</span>
                                    <span class="lg:hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none">
                                            <path d="M9.75078 4.97094V3.73594C9.75078 2.00694 8.32078 0.648438 6.50078 0.648438C4.68078 0.648438 3.25078 2.00694 3.25078 3.73594V4.97094C2.14578 4.97094 1.30078 5.77369 1.30078 6.82344V11.1459C1.30078 12.1957 2.14578 12.9984 3.25078 12.9984H9.75078C10.8558 12.9984 11.7008 12.1957 11.7008 11.1459V6.82344C11.7008 5.77369 10.8558 4.97094 9.75078 4.97094ZM4.55078 3.73594C4.55078 2.68619 5.39578 1.88344 6.50078 1.88344C7.60578 1.88344 8.45078 2.68619 8.45078 3.73594V4.97094H4.55078V3.73594ZM7.15078 9.91094C7.15078 10.2814 6.89078 10.5284 6.50078 10.5284C6.11078 10.5284 5.85078 10.2814 5.85078 9.91094V8.05844C5.85078 7.68794 6.11078 7.44094 6.50078 7.44094C6.89078 7.44094 7.15078 7.68794 7.15078 8.05844V9.91094Z"
                                                fill="white"></path>
                                        </svg>
                                    </span>
                                </span>
                                <span>
                                    <span class="ml-px text-base font-bold">
                                        <?= $product['level'] ?>
                                    </span> از <span class="text-base font-bold">
                                        4
                                    </span>
                                </span>
                            <?php else: ?>
                                <span>
                                    <span class="max-lg:hidden">مناسب سن</span>
                                    <span class="lg:hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                            <path d="M13.2742 7.36244C12.7944 6.16677 11.9578 5.13089 10.8675 4.38244L12.8794 2.46244L12.2872 1.89844L1.11905 12.5344L1.71127 13.0984L3.85333 11.0624C4.8096 11.5928 5.89276 11.8806 6.99922 11.8984C8.36824 11.8494 9.69217 11.4194 10.8074 10.6616C11.9226 9.90379 12.7802 8.85138 13.2742 7.63444C13.3076 7.54655 13.3076 7.45032 13.2742 7.36244ZM6.99922 10.0984C6.42028 10.0982 5.8566 9.92158 5.39057 9.59444L6.15919 8.87044C6.4779 9.03674 6.84463 9.10014 7.20438 9.05113C7.56412 9.00213 7.89748 8.84335 8.15445 8.59863C8.41142 8.3539 8.57814 8.03643 8.6296 7.69382C8.68106 7.35122 8.61448 7.00196 8.43986 6.69844L9.20008 5.97444C9.49597 6.36148 9.67372 6.8189 9.71367 7.29614C9.75362 7.77338 9.65421 8.25185 9.42645 8.67864C9.19868 9.10544 8.85142 9.46394 8.42306 9.7145C7.9947 9.96507 7.50193 10.0979 6.99922 10.0984ZM2.18168 9.82244L4.28174 7.82244C4.27097 7.71476 4.26677 7.60658 4.26914 7.49844C4.27025 6.8092 4.55824 6.14849 5.06999 5.66113C5.58174 5.17376 6.2755 4.8995 6.99922 4.89844C7.11013 4.89914 7.22091 4.90582 7.33103 4.91844L8.91867 3.41044C8.30075 3.20839 7.65253 3.10302 6.99922 3.09844C5.6302 3.14747 4.30627 3.57746 3.19106 4.33527C2.07585 5.09308 1.21824 6.1455 0.724241 7.36244C0.690878 7.45032 0.690878 7.54655 0.724241 7.63444C1.04725 8.45129 1.54336 9.19608 2.18168 9.82244Z"
                                                fill="white"></path>
                                        </svg>
                                    </span>
                                </span>
                                <span class="ml-px text-base font-bold"><?= $product['age'] ?>+</span>
                            <?php endif; ?>
                        </span>
                        <span class="flex items-center justify-between max-lg:py-1 lg:border-b lg:border-b-white/50 lg:pb-3">
                            <span>
                                <span class="max-lg:hidden">ظرفیت هر سانس</span>
                                <span class="lg:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                        <circle cx="6.99961" cy="4.35313" r="3.15" fill="white"></circle>
                                        <ellipse cx="7.00039" cy="11.3562" rx="5.6" ry="2.45" fill="white"></ellipse>
                                    </svg>
                                </span>
                            </span>
                            <span>
                                <span class="ml-px text-base font-bold">
                                    <?= $product['number_min'] ?>
                                </span> تـا <span class="ml-px text-base font-bold">
                                    <?= $product['number_max'] ?>
                                </span>
                                <span class="max-lg:hidden">نفر</span>
                            </span>
                        </span>
                        <span class="flex items-center justify-center mx-auto rounded-xl bg-[#5091FB]/40 px-2 py-0.5 relative w-[90%] h-[30px] lg:hidden">
                            <button type="button"
                                class="absolute right-[6px] top-[6px]">
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-white drop-shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0 0 13 10" fill="none">
                                        <path d="M11.5863 4.27438C11.751 4.50513 11.8333 4.62104 11.8333 4.79167C11.8333 4.96283 11.751 5.07821 11.5863 5.30896C10.8464 6.34679 8.95654 8.58333 6.41667 8.58333C3.87625 8.58333 1.98692 6.34625 1.247 5.30896C1.08233 5.07821 1 4.96229 1 4.79167C1 4.6205 1.08233 4.50513 1.247 4.27438C1.98692 3.23654 3.87679 1 6.41667 1C8.95708 1 10.8464 3.23708 11.5863 4.27438Z"
                                            stroke="#294276" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        </path>
                                        <path d="M8.04102 4.78906C8.04102 4.35809 7.86981 3.94476 7.56506 3.64001C7.26032 3.33527 6.84699 3.16406 6.41602 3.16406C5.98504 3.16406 5.57171 3.33527 5.26697 3.64001C4.96222 3.94476 4.79102 4.35809 4.79102 4.78906C4.79102 5.22004 4.96222 5.63336 5.26697 5.93811C5.57171 6.24286 5.98504 6.41406 6.41602 6.41406C6.84699 6.41406 7.26032 6.24286 7.56506 5.93811C7.86981 5.63336 8.04102 5.22004 8.04102 4.78906Z"
                                            stroke="#294276" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        </path>
                                    </svg>
                                </span>
                            </button>
                            <span class="text-md font-bold leading-none pt-1">مشاهده</span>
                        </span>
                        <span class="max-lg:hidden text-2xs pt-2 text-center">
                            <?php
                            $titles = [];
                            foreach ($product['genres'] as $genre) {
                                $titles[] = $genre->title;
                            }
                            $result = implode(" . ", $titles);
                            echo $result;
                            ?>
                        </span>
                    </a>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                    <span class="text-sm rounded-[4px] flex items-center justify-center leading-none pt-px bg-yellow-400 text-slate-900 w-[31px] h-[18.5px]" name="rate">
                        <?= $product_cat_alt == 'اتاق فرار' ? $product['rate'] : $product['rate'] * 5 ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
                <div class="flex items-center justify-center gap-x-2 bg-[#ECECEE] px-2 rounded-[6px] mt-3">
                    <?php if ($product['event'] && $product['event']['expire_date'] > time()): ?>
                        <span class="bg-[#F21543] text-white rounded-[40px] w-8 h-4 flex items-center justify-center">
                            <span class="text-heavy text-md pt-1">
                                <?= $product['event']['off_percentage'] ?>
                            </span>
                            <span class="text-heavy text-md pt-1">%</span>
                        </span>
                    <?php endif; ?>
                    <div>
                        <span class="text-[#62748E] ml-1">از</span>
                        <span>
                            <span class="ml-px text-md font-bold" name="price">
                                <?= number_format($product['price']) ?>
                            </span>
                            <span class="text-[#62748E]">تومان</span>
                        </span>
                    </div>
                </div>
            </article>
        <?php endif; ?>

    <?php endforeach;

    return ob_get_clean();
}
/*******************************************/
function standardization_products_html_list($products, $only_free_sanses = false)
{
    global $home_url;

    if (empty($home_url)) {
        if ($_SERVER['HTTP_HOST'] == 'dev.escapezoom.local') {
            $home_url = 'http://dev.escapezoom.local';
        } else {
            $home_url = 'https://escapezoom.ir';
        }
    }

    ob_start();
    $products = standardization_products($products, $only_free_sanses);
    foreach ($products as $product): ?>
        <?php
        $product_type = $product['type'];
        $product_cat_alt = null;
        switch ($product_type) {
            case 'cafegame':
                $product_cat_alt = 'کافه بازی ';
                break;
            case 'cinema':
                $product_cat_alt = 'سینما ترس ';
                break;
            case 'rageroom':
                $product_cat_alt = 'اتاق خشم ';
                break;
            case 'lasertag':
                $product_cat_alt = 'لیزرتگ ';
                break;
            case 'bubblefootball':
                $product_cat_alt = 'فوتبال حبابی ';
                break;
            case 'paintball':
                $product_cat_alt = 'پینت بال ';
                break;
            case 'haunted_house':
                $product_cat_alt = 'هانتد هاوس ';
                break;
            default:
                $product_cat_alt = 'اتاق فرار';
                break;
        } ?>

        <?php if ($product['active'] == 'temp' or $product['active'] == 'deactivated'): // رزرو غیرفعال   
        ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/" class="relative after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                        </a>
                        <span class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">غیرفعال</span>
                    </div>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
            </article>
        <?php elseif ($product['active'] == 'expired'): // اکسپایر شده 
        ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/" class="relative after:bg-white/60 after:absolute after:top-0 after:left-0 after:bottom-0 after:right-0">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                            <?php
                            if ($product['active'] == 'updated'): ?>
                                <span style="position: absolute;color: #fff;background: #F21543;border-radius: 8px;padding: 0 15px;margin: 10px;font-size: 10px;top: 0">آپدیت شد</span>
                            <?php
                            endif; ?>
                        </a>
                        <span class="bg-[#62748E] rounded-[9px] text-white w-[118px] h-[34px] absolute top-1/2 left-1/2 -translate-y-1/2 -translate-x-1/2 font-bold text-center text-shadow">اکسپایر شده</span>

                    </div>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
            </article>
        <?php elseif ($product['active'] == 'soon'): // به زودی 
        ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                            <span class="bg-[#2B7FFF] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">به زودی</span>
                        </a>
                    </div>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
            </article>
        <?php else: ?>
            <article class="embla__slide" name="product-card" data-product-id="<?= $product['product_id'] ?>">
                <div class="relative overflow-hidden rounded-xlh lg:rounded-2xl lg:shadow-8">
                    <div class="relative">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <img alt="<?= $product_cat_alt . $product['title'] ?>"
                                loading="lazy" width="200" height="248" decoding="async" data-nimg="1"
                                class=" h-[192px]  lg:h-[236px] object-cover" src="<?= $product['image'] ?>"
                                style="color: transparent;">
                            <?php
                            if ($product['active'] == 'updated'): ?>
                                <span class="bg-[#F21543] text-white leading-none px-2.5 py-1 rounded-[40px] h-5 text-sm absolute top-4 right-4 font-bold">آپدیت شد</span>
                            <?php
                            endif; ?>
                        </a>
                        <button type="button"
                            class="absolute bottom-2 right-2 flex h-7.5 w-7.5 items-center justify-center rounded-full bg-[#EFC101]/30 lg:hidden mobile-hover">
                            <span class="flex h-4.5 w-4.5 items-center justify-center rounded-full bg-white drop-shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="5" height="11" viewBox="0 0 5 11" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M2.5 0C2.78179 0 3.05204 0.111941 3.2513 0.311198C3.45055 0.510455 3.5625 0.780706 3.5625 1.0625C3.5625 1.34429 3.45055 1.61454 3.2513 1.8138C3.05204 2.01305 2.78179 2.125 2.5 2.125C2.21821 2.125 1.94796 2.01305 1.7487 1.8138C1.54944 1.61454 1.4375 1.34429 1.4375 1.0625C1.4375 0.780706 1.54944 0.510455 1.7487 0.311198C1.94796 0.111941 2.21821 0 2.5 0Z"
                                        fill="#827748"></path>
                                    <path d="M2.71211 9.77811V3.82812H1.86211M1.22461 9.77811H4.1996" stroke="#827748"
                                        stroke-linecap="round" stroke-linejoin="round"></path>
                                </svg>
                            </span>
                        </button>
                    </div>
                    <a href="<?= $home_url . '/room/' . $product['url'] ?>/"
                        class="absolute left-0 top-0 flex h-full w-full flex-col justify-between bg-slate-900/80 px-1.5 md:px-4 py-2.5 md:py-5 text-2xs text-white transition-all max-lg:hidden lg:scale-90 lg:opacity-0 lg:hover:scale-100 lg:hover:opacity-100">
                        <?php if ($product_type == 'escaperoom'): ?>
                            <div class="mx-auto flex w-[90%] items-center justify-center gap-x-1 rounded bg-white/20 px-6 py-1.5 leading-none">
                                <div class="max-lg:hidden lg:flex lg:item-center lg:justify-between w-full">
                                    <span class="text-[#FFFFFF]/70 text-2xs leading-none flex items-center">امروز</span>
                                    <?php if ($product['free_sanses']): ?>
                                        <span class="flex items-center gap-x-1">
                                            <span class="text-xl leading-none font-bold"><?= $product['free_sanses'] ?></span>
                                            <span class="text-2xs leading-none">سانس</span>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xl leading-none">تکمیله</span>
                                    <?php endif; ?>
                                </div>
                                <div class="lg:hidden py-1 text-nowrap text-3xs font-bold line-clamp-1" name="genres">
                                    <?php
                                    $titles = [];
                                    foreach ($product['genres'] as $genre) {
                                        $titles[] = $genre->title;
                                    }
                                    $result = implode(" . ", $titles);
                                    echo $result;
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        <span class="flex items-center justify-between py-1">
                            <span>
                                <span class="max-lg:hidden">مدت زمان سانس</span>
                                <span class="lg:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                        <path d="M7.00039 1.89844C3.96039 1.89844 1.40039 4.45844 1.40039 7.49844C1.40039 10.5384 3.96039 13.0984 7.00039 13.0984C10.0404 13.0984 12.6004 10.5384 12.6004 7.49844C12.6004 4.45844 10.0404 1.89844 7.00039 1.89844ZM8.63372 9.59844L6.53372 7.73177V3.7651H7.46706V7.2651L9.33372 8.89844L8.63372 9.59844Z"
                                            fill="white"></path>
                                    </svg>
                                </span>
                            </span>
                            <span><span class="ml-px text-base font-bold" dir="ltr"><?= $product['duration'] ?></span> دقیقه </span>
                        </span>
                        <span class="flex items-center justify-between border-b border-t border-b-white/50 border-t-white/50 py-3">
                            <?php if ($product_type == 'escaperoom'): ?>
                                <span>
                                    <span class="max-lg:hidden">میزان سختی</span>
                                    <span class="lg:hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 13 13" fill="none">
                                            <path d="M9.75078 4.97094V3.73594C9.75078 2.00694 8.32078 0.648438 6.50078 0.648438C4.68078 0.648438 3.25078 2.00694 3.25078 3.73594V4.97094C2.14578 4.97094 1.30078 5.77369 1.30078 6.82344V11.1459C1.30078 12.1957 2.14578 12.9984 3.25078 12.9984H9.75078C10.8558 12.9984 11.7008 12.1957 11.7008 11.1459V6.82344C11.7008 5.77369 10.8558 4.97094 9.75078 4.97094ZM4.55078 3.73594C4.55078 2.68619 5.39578 1.88344 6.50078 1.88344C7.60578 1.88344 8.45078 2.68619 8.45078 3.73594V4.97094H4.55078V3.73594ZM7.15078 9.91094C7.15078 10.2814 6.89078 10.5284 6.50078 10.5284C6.11078 10.5284 5.85078 10.2814 5.85078 9.91094V8.05844C5.85078 7.68794 6.11078 7.44094 6.50078 7.44094C6.89078 7.44094 7.15078 7.68794 7.15078 8.05844V9.91094Z"
                                                fill="white"></path>
                                        </svg>
                                    </span>
                                </span>
                                <span>
                                    <span class="ml-px text-base font-bold">
                                        <?= $product['level'] ?>
                                    </span> از <span class="text-base font-bold">
                                        4
                                    </span>
                                </span>
                            <?php else: ?>
                                <span>
                                    <span class="max-lg:hidden">مناسب سن</span>
                                    <span class="lg:hidden">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                            <path d="M13.2742 7.36244C12.7944 6.16677 11.9578 5.13089 10.8675 4.38244L12.8794 2.46244L12.2872 1.89844L1.11905 12.5344L1.71127 13.0984L3.85333 11.0624C4.8096 11.5928 5.89276 11.8806 6.99922 11.8984C8.36824 11.8494 9.69217 11.4194 10.8074 10.6616C11.9226 9.90379 12.7802 8.85138 13.2742 7.63444C13.3076 7.54655 13.3076 7.45032 13.2742 7.36244ZM6.99922 10.0984C6.42028 10.0982 5.8566 9.92158 5.39057 9.59444L6.15919 8.87044C6.4779 9.03674 6.84463 9.10014 7.20438 9.05113C7.56412 9.00213 7.89748 8.84335 8.15445 8.59863C8.41142 8.3539 8.57814 8.03643 8.6296 7.69382C8.68106 7.35122 8.61448 7.00196 8.43986 6.69844L9.20008 5.97444C9.49597 6.36148 9.67372 6.8189 9.71367 7.29614C9.75362 7.77338 9.65421 8.25185 9.42645 8.67864C9.19868 9.10544 8.85142 9.46394 8.42306 9.7145C7.9947 9.96507 7.50193 10.0979 6.99922 10.0984ZM2.18168 9.82244L4.28174 7.82244C4.27097 7.71476 4.26677 7.60658 4.26914 7.49844C4.27025 6.8092 4.55824 6.14849 5.06999 5.66113C5.58174 5.17376 6.2755 4.8995 6.99922 4.89844C7.11013 4.89914 7.22091 4.90582 7.33103 4.91844L8.91867 3.41044C8.30075 3.20839 7.65253 3.10302 6.99922 3.09844C5.6302 3.14747 4.30627 3.57746 3.19106 4.33527C2.07585 5.09308 1.21824 6.1455 0.724241 7.36244C0.690878 7.45032 0.690878 7.54655 0.724241 7.63444C1.04725 8.45129 1.54336 9.19608 2.18168 9.82244Z"
                                                fill="white"></path>
                                        </svg>
                                    </span>
                                </span>
                                <span class="ml-px text-base font-bold"><?= $product['age'] ?>+</span>
                            <?php endif; ?>
                        </span>
                        <span class="flex items-center justify-between max-lg:py-1 lg:border-b lg:border-b-white/50 lg:pb-3">
                            <span>
                                <span class="max-lg:hidden">ظرفیت هر سانس</span>
                                <span class="lg:hidden">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="15" viewBox="0 0 14 15" fill="none">
                                        <circle cx="6.99961" cy="4.35313" r="3.15" fill="white"></circle>
                                        <ellipse cx="7.00039" cy="11.3562" rx="5.6" ry="2.45" fill="white"></ellipse>
                                    </svg>
                                </span>
                            </span>
                            <span>
                                <span class="ml-px text-base font-bold">
                                    <?= $product['number_min'] ?>
                                </span> تـا <span class="ml-px text-base font-bold">
                                    <?= $product['number_max'] ?>
                                </span>
                                <span class="max-lg:hidden">نفر</span>
                            </span>
                        </span>
                        <span class="flex items-center justify-center mx-auto rounded-xl bg-[#5091FB]/40 px-2 py-0.5 relative w-[90%] h-[30px] lg:hidden">
                            <button type="button"
                                class="absolute right-[6px] top-[6px]">
                                <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full bg-white drop-shadow">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="10" viewBox="0 0 13 10" fill="none">
                                        <path d="M11.5863 4.27438C11.751 4.50513 11.8333 4.62104 11.8333 4.79167C11.8333 4.96283 11.751 5.07821 11.5863 5.30896C10.8464 6.34679 8.95654 8.58333 6.41667 8.58333C3.87625 8.58333 1.98692 6.34625 1.247 5.30896C1.08233 5.07821 1 4.96229 1 4.79167C1 4.6205 1.08233 4.50513 1.247 4.27438C1.98692 3.23654 3.87679 1 6.41667 1C8.95708 1 10.8464 3.23708 11.5863 4.27438Z"
                                            stroke="#294276" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        </path>
                                        <path d="M8.04102 4.78906C8.04102 4.35809 7.86981 3.94476 7.56506 3.64001C7.26032 3.33527 6.84699 3.16406 6.41602 3.16406C5.98504 3.16406 5.57171 3.33527 5.26697 3.64001C4.96222 3.94476 4.79102 4.35809 4.79102 4.78906C4.79102 5.22004 4.96222 5.63336 5.26697 5.93811C5.57171 6.24286 5.98504 6.41406 6.41602 6.41406C6.84699 6.41406 7.26032 6.24286 7.56506 5.93811C7.86981 5.63336 8.04102 5.22004 8.04102 4.78906Z"
                                            stroke="#294276" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                        </path>
                                    </svg>
                                </span>
                            </button>
                            <span class="text-md font-bold leading-none pt-1">مشاهده</span>
                        </span>
                        <span class="max-lg:hidden text-2xs pt-2 text-center">
                            <?php
                            $titles = [];
                            foreach ($product['genres'] as $genre) {
                                $titles[] = $genre->title;
                            }
                            $result = implode(" . ", $titles);
                            echo $result;
                            ?>
                        </span>
                    </a>
                </div>
                <div class="flex items-center justify-between my-3">
                    <span class="text-base font-medium text-[#62748E] leading-none">
                        <?= $product_cat_alt ?>
                    </span>
                    <span class="text-sm rounded-[4px] flex items-center justify-center leading-none pt-px bg-yellow-400 text-slate-900 w-[31px] h-[18.5px]" name="rate">
                        <?= $product_cat_alt == 'اتاق فرار' ? $product['rate'] : $product['rate'] * 5 ?>
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <h3 class="w-full truncate text-base font-bold lg:text-[22px]" title="<?= $product['title'] ?>" name="title">
                        <a href="<?= $home_url . '/room/' . $product['url'] ?>/">
                            <?= $product['title'] ?>
                        </a>
                    </h3>
                </div>
                <p class="text-4xs text-[#565656] lg:text-2xs lg:[word-spacing:0.375rem] mt-3 flex items-center gap-x-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="15" viewBox="0 0 12 15" fill="none" class="mx-0 ">
                        <path d="M5.99984 0.833374C3.0665 0.833374 0.666504 3.23337 0.666504 6.16671C0.666504 9.76671 5.33317 13.8334 5.53317 14.0334C5.6665 14.1 5.8665 14.1667 5.99984 14.1667C6.13317 14.1667 6.33317 14.1 6.4665 14.0334C6.6665 13.8334 11.3332 9.76671 11.3332 6.16671C11.3332 3.23337 8.93317 0.833374 5.99984 0.833374ZM5.99984 12.6334C4.59984 11.3 1.99984 8.43337 1.99984 6.16671C1.99984 3.96671 3.79984 2.16671 5.99984 2.16671C8.19984 2.16671 9.99984 3.96671 9.99984 6.16671C9.99984 8.36671 7.39984 11.3 5.99984 12.6334ZM5.99984 3.50004C4.53317 3.50004 3.33317 4.70004 3.33317 6.16671C3.33317 7.63337 4.53317 8.83337 5.99984 8.83337C7.4665 8.83337 8.6665 7.63337 8.6665 6.16671C8.6665 4.70004 7.4665 3.50004 5.99984 3.50004ZM5.99984 7.50004C5.2665 7.50004 4.6665 6.90004 4.6665 6.16671C4.6665 5.43337 5.2665 4.83337 5.99984 4.83337C6.73317 4.83337 7.33317 5.43337 7.33317 6.16671C7.33317 6.90004 6.73317 7.50004 5.99984 7.50004Z"
                            fill="#90A1B9" />
                    </svg>
                    <span class="text-2xs pt-1" name="address"> <?= $product['hood_name'] . ' . ' . $product['city_name'] ?></span>
                </p>
                <div class="flex items-center justify-center gap-x-2 bg-[#ECECEE] px-2 rounded-[6px] mt-3">
                    <?php if ($product['event'] && $product['event']['expire_date'] > time()): ?>
                        <span class="bg-[#F21543] text-white rounded-[40px] w-8 h-4 flex items-center justify-center">
                            <span class="text-heavy text-md pt-1">
                                <?= $product['event']['off_percentage'] ?>
                            </span>
                            <span class="text-heavy text-md pt-1">%</span>
                        </span>
                    <?php endif; ?>
                    <div>
                        <span class="text-[#62748E] ml-1">از</span>
                        <span>
                            <span class="ml-px text-md font-bold" name="price">
                                <?= number_format($product['price']) ?>
                            </span>
                            <span class="text-[#62748E]">تومان</span>
                        </span>
                    </div>
                </div>
            </article>
        <?php endif; ?>

<?php endforeach;

    return ob_get_clean();
}
/*******************************************/
function automation_management($product_id, $booking_time = '')
{

    if ($product_id == 28325) :

        header("Access-Control-Allow-Origin: *");
        header("Content-Type: application/json");

        $url    = 'https://www.t4f.ir/fun/2177/checkout/day_data?fun_id=2177&date_id=2253';
        $proxy  = "43.134.121.40";
        $port   = 3128;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_PROXYTYPE, 'HTTP');
        curl_setopt($ch, CURLOPT_PROXY, $proxy);
        curl_setopt($ch, CURLOPT_PROXYPORT, $port);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'cookie:  uid=eyJpdiI6IjdzNzlaRkVJMjREd1p2OUlUVkRiN2c9PSIsInZhbHVlIjoia05kc0txbE1uMjk0cmd5RU5DN0t1bUJBN01Mak9JdVIvdnovRkRJSEN1UFZiUk9LR3N6MlVFNk1RLzJxckRaTkRnQVZkWXdsYWZVL29zUEtFZHgveStOeUNWMVdldDlUZ1dVaWFaNnZVd1U9IiwibWFjIjoiYTQ4ZGFiMmFhMWJmMjFmMWExZDViMDRhYzA0ODQ3ODIwNWUzNzY2YTIwYjVhNGVkOGJhM2JlNTcyZDA1ZjVlYyIsInRhZyI6IiJ9; _ga_4SL2MHWJCC=GS1.1.1728457211.3.1.1728459443.0.0.0; XSRF-TOKEN=eyJpdiI6InlHaDd3Tm02K2pkblpSUng3eU1xSmc9PSIsInZhbHVlIjoiWGRwZEVHNXl5MjBtWUYyYW4wYVVoWFhOV2xzZERHa2JiVzlPcklYbVYxUHUvREZITmNDdFZrd2RVMG1xbzdDTmcxblRjbXhnUythcXNsS0NNL09yZlQ5TFFxOUNRWkVhdWFLUTEvK2xUSS8vTmNhOTBCUU5QR2d2TmtuU2FaLzciLCJtYWMiOiI2MmJmYTJkZWRlODQ4ZTQ4MzQ4YWFlZGNmZDU4OTJlNjc4Njc5YTJkM2JhY2MwYmExMGUyOTk1MmVkYzBjOTA5IiwidGFnIjoiIn0%3D; t4f_session=eyJpdiI6ImRGMmllb0dPMFBCdG13WHY0a3J5L1E9PSIsInZhbHVlIjoidlBnMzJTK3A5Z2c1a3U3WXA5ZXJjeUtTMTRmK1M4Ym8vQ2o1anZpQjJseHV4Zmh3YUxSampoeUE2RS9hZTI4YTFwRVJnRFlNV1BlNkVxSnRmUVJNRUd4bXduTDIwQWZoMUUvci9ETTZGYXBPbGZNWVkxZWZucXlzTkd1TXZ0VnMiLCJtYWMiOiI3MGQ3NGZjNTk0MzBjODBlOTk4OGI4YzE1YTM4MTY5MTJiNTA1NzQ1MDgxZDk1MjE5ZWZmNWUyOGQzNjExMjhjIiwidGFnIjoiIn0%3D;',
            'referer: https://www.t4f.ir/fun/2177/checkout?by=web',
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

        if (curl_errno($ch)) {
            logintotag(['error' => curl_error($ch)]);
        } else {
            preg_match_all('/<option[^>]*value="[^"]*"[^>]*data-status="([^"]*)"[^>]*data-start-at="([^"]*)"[^>]*>/', json_decode($body, true)['html'], $matches, PREG_SET_ORDER);
            foreach ($matches as $match) {
                $type = (int)$match[1];
                $date = $match[2];

                if ($type) {
                    $result[] = [
                        'date' => $date,
                        'type' => $type
                    ];
                }
            }

            logintotag($result);
        }


    endif;
}
