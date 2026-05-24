# تحلیل خط‌به‌خط کدهای به‌روزرسانی products_order

این سند تمام کدهای مرتبط را آورده و هر بخش را توضیح می‌دهد؛ در پایان نحوهٔ اجرا خلاصه شده است.

---

## فهرست فایل‌ها

| فایل | نقش |
|------|-----|
| `run-update-products-order.php` | اسکریپت یک‌جا برای اجرای همهٔ به‌روزرسانی‌ها |
| `wp-content/themes/escapezoom-v2/inc/saeed-codes.php` | توابع محاسبه + ارسال به web-service |
| `web-service/web-service.php` | دریافت داده و ذخیره در جدول `products_order` |
| `wp-content/themes/escapezoom-v2/app/ajax/callbacks/site/product_add_comment.php` | با هر کامنت، یک رکورد در `hottest_products` |

---

# بخش ۱: run-update-products-order.php

## کد کامل

```php
<?php
/**
 * اسکریپت به‌روزرسانی یک‌جا همهٔ لیست‌های products_order
 * ...
 */
date_default_timezone_set('Asia/Tehran');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);

define('RUN_KEY', 'ez_update_products_order_2026');

$wp_load = __DIR__ . '/wp-load.php';
if (!is_file($wp_load)) {
    $wp_load = __DIR__ . '/../wp-load.php';
}
if (!is_file($wp_load)) {
    die('خطا: wp-load.php پیدا نشد. مسیر را در ابتدای این فایل تنظیم کنید.');
}
require_once $wp_load;

$saeed_codes = ABSPATH . 'wp-content/themes/escapezoom-v2/inc/saeed-codes.php';
if (!is_file($saeed_codes)) {
    $saeed_codes = __DIR__ . '/wp-content/themes/escapezoom-v2/inc/saeed-codes.php';
}
if (!is_file($saeed_codes)) {
    die('خطا: فایل inc/saeed-codes.php تم پیدا نشد.');
}
require_once $saeed_codes;

if (php_sapi_name() !== 'cli') {
    $key = isset($_GET['key']) ? $_GET['key'] : '';
    if (RUN_KEY === '' || $key !== RUN_KEY) {
        status_header(403);
        die('دسترسی مجاز نیست. از CLI اجرا کنید یا key را در URL قرار دهید.');
    }
    header('Content-Type: text/html; charset=utf-8');
}

echo "شروع به‌روزرسانی products_order …\n<br>";
$steps = [];
$ok    = true;

try {
    if (function_exists('update_held_sans_table_func')) {
        update_held_sans_table_func();
        $steps[] = ['held_orders_list', true, 'به‌روز شد'];
    } else { ... }
} catch (Exception $e) { ... }

try {
    if (function_exists('ez_queryable_set_hottest_products')) {
        ez_queryable_set_hottest_products();
        $steps[] = ['hottest (داغ‌ترین‌ها)', true, 'ارسال به products_order'];
    } else { ... }
} catch (Exception $e) { ... }

try {
    if (function_exists('ez_queryable_set_popular_products')) {
        ez_queryable_set_popular_products();
        $steps[] = ['popular (محبوب‌ها)', true, 'ارسال به products_order'];
    } else { ... }
} catch (Exception $e) { ... }

try {
    if (function_exists('ez_queryable_set_topsale_products')) {
        ez_queryable_set_topsale_products();
        $steps[] = ['topsale (پرفروش‌ترین‌ها)', true, 'ارسال به products_order'];
    } else { ... }
} catch (Exception $e) { ... }

try {
    if (function_exists('ez_queryable_set_recent_products')) {
        ez_queryable_set_recent_products();
        $steps[] = ['recent (جدیدترین‌ها)', true, 'ارسال به products_order'];
    } else { ... }
} catch (Exception $e) { ... }

foreach ($steps as $s) { ... }
if ($ok) { echo "همهٔ مراحل با موفقیت انجام شد. ..."; } else { ... }
```

## تحلیل خط‌به‌خط

| خطوط | کار کد |
|------|--------|
| `date_default_timezone_set('Asia/Tehran')` | منطقهٔ زمانی را برای زمان‌های بعدی (مثل `time()`) روی تهران تنظیم می‌کند. |
| `error_reporting(...)` | فقط خطاهای جدی را نشان می‌دهد تا هشدارها خروجی را شلوغ نکنند. |
| `define('RUN_KEY', ...)` | کلید امنیتی برای اجرا از مرورگر. اگر در URL نباشد، درخواست از مرورگر رد می‌شود. |
| `$wp_load = __DIR__ . '/wp-load.php'` | اول فرض می‌کند فایل در روت وردپرس است. |
| `if (!is_file($wp_load)) { $wp_load = __DIR__ . '/../wp-load.php' }` | اگر نبود، یک سطح بالاتر (پوشهٔ والد) را امتحان می‌کند. |
| `if (!is_file($wp_load)) { die(...) }` | اگر باز هم پیدا نشد، اسکریپت متوقف و پیام خطا چاپ می‌شود. |
| `require_once $wp_load` | وردپرس را بارگذاری می‌کند؛ بعد از این، ثابت `ABSPATH` و همهٔ توابع وردپرس در دسترس هستند. |
| `$saeed_codes = ABSPATH . 'wp-content/themes/escapezoom-v2/inc/saeed-codes.php'` | مسیر فایل توابع تم را با روت وردپرس می‌سازد. |
| `if (!is_file($saeed_codes)) { $saeed_codes = __DIR__ . '/wp-content/...' }` | اگر تم در روت پروژه باشد، مسیر نسبی از خود فایل امتحان می‌شود. |
| `require_once $saeed_codes` | توابع `ez_queryable_set_*` و `update_held_sans_table_func` و `ez_webservice` لود می‌شوند. |
| `if (php_sapi_name() !== 'cli')` | اگر از مرورگر اجرا شده (نه از ترمینال)، شرط داخلش اجرا می‌شود. |
| `$key = isset($_GET['key']) ? $_GET['key'] : ''` | مقدار پارامتر `key` در آدرس را می‌گیرد. |
| `if (RUN_KEY === '' \|\| $key !== RUN_KEY)` | اگر کلید تعریف نشده یا با کلید URL یکی نباشد، دسترسی رد می‌شود. |
| `status_header(403); die(...)` | پاسخ 403 و قطع اجرا تا کسی بدون کلید از مرورگر اسکریپت را نزند. |
| `header('Content-Type: text/html; charset=utf-8')` | برای نمایش درست فارسی در مرورگر. |
| بعد از آن | به ترتیب: به‌روزرسانی `held_orders_list`، سپس داغ‌ترین، محبوب، پرفروش، جدیدترین. هر مرحله در `try/catch` است تا خطا یکی، بقیه را خراب نکند. |
| آخر | آرایهٔ `$steps` چاپ می‌شود و در پایان یک جمع‌بندی موفق/ناموفق نمایش داده می‌شود. |

---

# بخش ۲: توابع در saeed-codes.php

## ۲.۱ تابع ez_webservice

```php
function ez_webservice( $data ) {
    if ( $_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    } elseif ( $_SERVER['HTTP_HOST'] == 'wo.escapezoom.local' ) {
        $base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    } else {
        $base_url = 'https://' . $_SERVER['HTTP_HOST'] . '/web-service/web-service.php';
    }
    $response = wp_remote_post( $base_url, array(
            'method'        => 'POST',
            'timeout'       => 45,
            'redirection'   => 5,
            'httpversion'   => '1.0',
            'blocking'      => true,
            'headers'       => ['Content-Type' => 'application/json'],
            'body'          => json_encode($data),
            'cookies'       => array()
    ) );
    if ( is_array($response) ){
        return $response['body'];
    }
}
```

| خط | کار |
|----|-----|
| شرط اول و دوم | هر دو برای لوکال؛ آدرس با `http` ساخته می‌شود. |
| `else` | در بقیهٔ دامنه‌ها آدرس با `https` و همان مسیر `/web-service/web-service.php`. |
| `wp_remote_post` | درخواست POST به آن آدرس با بدنهٔ JSON. |
| `body => json_encode($data)` | همان آرایه‌ای که از طرف توابع `ez_queryable_set_*` فرستاده می‌شود (مثلاً `['type' => 'hottest_products_set', 'data' => [...]]`). |
| `return $response['body']` | پاسخ متنی سرویس را برمی‌گرداند (مثلاً برای `get_products_30days_views_count` خروجی JSON است). |

---

## ۲.۲ تابع ez_queryable_set_hottest_products (داغ‌ترین‌ها)

```php
function ez_queryable_set_hottest_products() {
    global $wpdb;

    $wpdb->get_results( "DELETE FROM hottest_products WHERE time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY))");

    $C          = 4.3; // مثلاً میانگین همه $w_rate ها
    $m          = 15;  // مثلاً میانگین همه w_cm30 ها یا یک عدد ثابت
    $max_views  = 4000; // ماکزیمم بازدیدهای ماهانه یک بازی

    $rows = $wpdb->get_results( "SELECT * FROM hottest_products", ARRAY_A );

    $hottest = [];
    foreach ( $rows as $row )
        if ( !isset( $hottest[$row['product_id']] ) ) {
            $hottest[$row['product_id']]['w_rate']              = $row['w_rate'] * $row['w_comments_count'];
            $hottest[$row['product_id']]['w_comments_count']    = $row['w_comments_count'];
        } else {
            $hottest[$row['product_id']]['w_rate']              += $row['w_rate'] * $row['w_comments_count'];
            $hottest[$row['product_id']]['w_comments_count']    += $row['w_comments_count'];
        }

    $product_ids    = implode(',', array_keys($hottest));
    $views          = (array)(json_decode( ez_webservice( array ('type' => 'get_products_30days_views_count', 'data' => ['product_ids' => $product_ids]) ) ));

    foreach ( $hottest as $product_id => $hottest_item ) :
        $w_rate             = $hottest_item['w_rate'] / $hottest_item['w_comments_count'];
        $w_comments_count   = $hottest_item['w_comments_count'];
        $view               = $views[$product_id];

        $bayesian_score = get_bayesian_score($w_rate, $w_comments_count, $C, $m);

        $normalized_bayesian_score = 0.6 * $bayesian_score + 0.4 * log($w_comments_count + 1);
        $normalized_views = log($view + 1) / log($max_views + 1) * 5;
        $hot_score[$product_id] = 0.67 * $normalized_bayesian_score + 0.33 * $normalized_views;

        saeed_print([ ... ]);
    endforeach;

    $penalty_product_ids = [24194,354862,...];
    if (time() <= strtotime('2026-02-19 23:59:59'))
        foreach ($penalty_product_ids as $pid)
            if (isset($hot_score[$pid]) || array_key_exists($pid, $hot_score))
                unset($hot_score[$pid]);

    asort($hot_score);
    $product_data = [];
    foreach ( $hot_score as $product_id => $count )
        $product_data[] = $product_id;
    ez_webservice( array('type' => 'hottest_products_set', 'data' => array_reverse($product_data)) );
}
```

| خط / بلوک | کار |
|-----------|-----|
| `DELETE FROM hottest_products WHERE time < ... 90 DAY` | رکوردهای قدیمی‌تر از ۹۰ روز از جدول حذف می‌شوند تا فقط کامنت‌های اخیر در امتیاز داغ نقش داشته باشند. |
| `$C, $m, $max_views` | ثابت‌های فرمول: میانگین امتیاز (C)، حد اطمینان (m)، و سقف بازدید برای نرمال‌سازی. |
| `SELECT * FROM hottest_products` | همهٔ رکوردهای باقی‌مانده (کامنت‌های ۹۰ روز با وزن و امتیاز). |
| حلقهٔ اول روی `$rows` | برای هر محصول: جمع وزنی امتیاز (`w_rate * w_comments_count`) و جمع وزن نظرات (`w_comments_count`) را در آرایهٔ `$hottest` نگه می‌دارد. |
| `$product_ids = implode(',', ...)` | لیست id محصولات برای درخواست بازدید ۳۰ روز. |
| `ez_webservice(..., 'get_products_30days_views_count', ...)` | از web-service بازدید ۳۰ روز هر محصول را می‌گیرد. خروجی باید آبجکت `product_id => تعداد` باشد. |
| حلقهٔ دوم روی `$hottest` | برای هر محصول: میانگین امتیاز، امتیاز بیز، نرمال بیز، نرمال بازدید، و در نهایت `hot_score` را حساب می‌کند. |
| `get_bayesian_score($w_rate, $w_comments_count, $C, $m)` | امتیاز بیز را از روی میانگین امتیاز و تعداد نظرات برمی‌گرداند. |
| `$normalized_bayesian_score` | ترکیب امتیاز بیز با لگاریتم تعداد نظرات. |
| `$normalized_views` | بازدید ۳۰ روز را روی مقیاس ۰ تا ۵ نرمال می‌کند. |
| `$hot_score[...] = 0.67 * ... + 0.33 * ...` | امتیاز نهایی داغ = ۶۷٪ بخش نظر + ۳۳٪ بخش بازدید. |
| `$penalty_product_ids` و شرط زمان | تا تاریخ مشخص، این محصولات از لیست داغ حذف می‌شوند. |
| `asort($hot_score)` | مرتب‌سازی صعودی بر اساس امتیاز (کم‌داغ‌تر اول). |
| `$product_data[] = $product_id` و `array_reverse` | ترتیب را برعکس می‌کند تا داغ‌ترین اول باشد، بعد با `ez_webservice('hottest_products_set', ...)` به سرویس فرستاده و در `products_order` ذخیره می‌شود. |

---

## ۲.۳ تابع get_bayesian_score

```php
function get_bayesian_score($R, $v, $C, $m) {
    return $v + $m ? (($v / ($v + $m)) * $R) + (($m / ($v + $m)) * $C) : 0;
}
```

| پارامتر | معنی |
|---------|------|
| `$R` | میانگین امتیاز محصول. |
| `$v` | تعداد نظرات. |
| `$C` | میانگین سراسری امتیاز (۴.۳). |
| `$m` | پارامتر اطمینان (۱۵). |

فرمول: اگر `v+m` صفر نباشد، امتیاز بیز = وزن‌دار بین امتیاز خود محصول و میانگین کلی؛ هرچه نظرات بیشتر باشد، وزن `R` بیشتر می‌شود.

---

## ۲.۴ تابع ez_queryable_set_popular_products (محبوب‌ها)

```php
function ez_queryable_set_popular_products() {
    $penalty_products = [383915, 382454, 24194, ...];
    $args = array (
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'meta_query'        => array (
                    array(
                            'key'     => 'product_state',
                            'value'   => 'active',
                            'compare' => 'LIKE',
                    ),
            ),
    );
    $query = new WP_Query($args);
    $popular_products = [];
    while ($query->have_posts()) : $query->the_post();
        $comments_count = $comments_count_penalty = (int)get_post_meta(get_the_ID(), 'comments_count_new', TRUE);
        $rate           = get_post_meta(get_the_ID(), 'product_rates', TRUE);
        if ( in_array(get_the_ID(), $penalty_products) )
            $comments_count_penalty = $comments_count_penalty / 2.5;
        $temp = [];
        $temp['comments_count'] = $comments_count_penalty;
        $temp['rate']           = $comments_count != 0 ? ((int)$rate[1094] + ... + (int)$rate[1098]) / 5 / 20 / $comments_count : 1;
        $popular_products[get_the_ID()] = $temp;
    endwhile;
    wp_reset_postdata();
    $penalty_product_ids = [...];
    if (time() <= strtotime('2026-02-19 23:59:59'))
        foreach ($penalty_product_ids as $pid)
            if (isset($popular_products[$pid]))
                unset($popular_products[$pid]);
    ez_webservice( array('type' => 'popular_products_set', 'data' => $popular_products) );
}
```

| بخش | کار |
|-----|-----|
| `WP_Query` | فقط محصولات منتشرشده با `product_state` شبیه `active`. |
| `comments_count_new` | تعداد نظرات جدید از متا. |
| `product_rates` | آرایهٔ امتیازها؛ ایندکس‌های ۱۰۹۴–۱۰۹۸ برای محاسبهٔ میانگین استفاده می‌شوند. |
| `$comments_count_penalty / 2.5` | برای چند محصول خاص تعداد نظر نصف می‌شود تا در رتبهٔ محبوب کمتر بیایند. |
| `$temp['rate']` | میانگین امتیاز نرمال‌شده (تقسیم بر ۲۰ و تعداد نظرات). |
| در پایان | لیست `product_id => ['comments_count'=>..., 'rate'=>...]` با `popular_products_set` به web-service فرستاده می‌شود؛ آنجا با بازدید ترکیب و ترتیب نهایی در `products_order.popular` ذخیره می‌شود. |

---

## ۲.۵ تابع ez_queryable_set_topsale_products (پرفروش‌ترین‌ها)

```php
function ez_queryable_set_topsale_products() {
    global $wpdb;
    update_held_sans_table_func();
    $rows = $wpdb->get_results( "SELECT * FROM held_orders_list", ARRAY_A );
    $power_map = [
            1 => 0.2,
            2 => 0.4,
            3 => 1,
            4 => 1,
    ];
    $topsale = [];
    foreach ( $rows as $row )
        if ( !isset( $topsale[$row['room_id']] ) )
            $topsale[$row['room_id']] = $row['count'] * $power_map[$row['level']] ?? 1;
        else
            $topsale[$row['room_id']] += $row['count'] * $power_map[$row['level']] ?? 1;
    asort($topsale);
    $penalty_product_ids = [...];
    if (time() <= strtotime('2026-02-19 23:59:59'))
        foreach ($penalty_product_ids as $pid)
            if (isset($topsale[$pid]) || array_key_exists($pid, $topsale))
                unset($topsale[$pid]);
    $product_data = [];
    foreach ( $topsale as $product_id => $count )
        $product_data[] = $product_id;
    ez_webservice( array('type' => 'topsale_products_set', 'data' => array_reverse($product_data)) );
}
```

| خط / بلوک | کار |
|-----------|-----|
| `update_held_sans_table_func()` | جدول `held_orders_list` را از رزروهای واقعی پر/به‌روز می‌کند. |
| `SELECT * FROM held_orders_list` | هر ردیف = یک رزرو شمرده‌شده (room_id، count، level). |
| `$power_map` | ضریب سطح کاربر: ۱→۰.۲، ۲→۰.۴، ۳ و ۴→۱. |
| حلقه روی `$rows` | برای هر `room_id` امتیاز = جمع (count × ضریب سطح). |
| `asort($topsale)` | مرتب صعودی؛ کم‌فروش‌تر اول. |
| حذف پنالتی و ساخت `$product_data` و `array_reverse` | پرفروش‌ترین اول؛ سپس با `topsale_products_set` به سرویس فرستاده و در `products_order.topsale` ذخیره می‌شود. |

---

## ۲.۶ تابع update_held_sans_table_func

```php
function update_held_sans_table_func () {
    global $wpdb;
    $penalty_products = [73114, 261541, 261593];
    $partially_orders = [];
    $temp = $wpdb->get_results( "SELECT wp_posts.ID FROM wp_posts WHERE post_status = 'wc-partially-paid' OR post_status = 'wc-held' OR post_status = 'wc-completed'", ARRAY_A );
    foreach ( $temp as $order_arr )
        $partially_orders[] = $order_arr['ID'];
    $partially_orders = implode(',', $partially_orders);
    $rows = json_decode(ez_reservation(array('type' => 'query_execution', 'data' => ['query' => "SELECT wc_order_id as ID, booking_time as booking_time FROM wp_zb_booking_history WHERE `wc_order_id` IN ($partially_orders)"])), true);
    foreach ( $rows as $row ) {
        if ( $row['booking_time'] < time() - 4 * 3600 && time() - 30 * 24 * 3600 < $row['booking_time']  ) {
            $order_id = $row['ID'];
            $duplicate_check = $wpdb->get_results( "SELECT * FROM held_orders_list WHERE order_id LIKE '" . $order_id . "'", ARRAY_A );
            if ( count($duplicate_check) < 1 ) {
                $order = wc_get_order($order_id);
                foreach ($order->get_items() as $item) {
                    $product_id = $item->get_product_id();
                    $quantity = $item->get_quantity();
                    if ( in_array($product_id, $penalty_products) )
                        $quantity = $quantity / 1.5;
                }
                $user_id = $order->get_user_id();
                $wpdb->insert( 'held_orders_list', array(
                        'room_id'   => $product_id,
                        'order_id'  => $order_id,
                        'count'     => $quantity,
                        'user_id'   => $user_id,
                        'level'     => get_user_level($user_id),
                        'held_time' => $row['booking_time']
                ));
            }
        }
    }
    $wpdb->get_results( "DELETE FROM held_orders_list WHERE held_time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY))");
}
```

| بخش | کار |
|-----|-----|
| `SELECT ... wp_posts ... wc-partially-paid OR wc-held OR wc-completed` | شناسهٔ سفارش‌هایی که پرداخت بخشی، نگه‌داشته‌شده یا تکمیل‌شده‌اند. |
| `ez_reservation(..., 'query_execution', ...)` | روی دیتابیس رزرو، از `wp_zb_booking_history` رزروهای مربوط به این سفارش‌ها با `booking_time` گرفته می‌شود. |
| شرط `booking_time` | فقط رزروهایی که: حداقل ۴ ساعت گذشته باشند و حداکثر ۳۰ روز قبل (تا رزروهای خیلی قدیمی حذف شوند). |
| `duplicate_check` | اگر این `order_id` قبلاً در `held_orders_list` نباشد، یک رکورد اضافه می‌شود. |
| حلقهٔ `get_items()` | برای هر آیتم، `product_id` و `quantity` و در صورت پنالتی، کاهش مقدار. **توجه:** در کد فعلی، `$wpdb->insert` خارج از این حلقه است؛ یعنی فقط **آخرین** آیتم سفارش در جدول درج می‌شود. |
| `get_user_level($user_id)` | سطح کاربر برای ضریب در محاسبهٔ پرفروش. |
| `DELETE ... held_time < ... 30 DAY` | رزروهای قدیمی‌تر از ۳۰ روز از `held_orders_list` پاک می‌شوند. |

---

## ۲.۷ تابع ez_queryable_set_recent_products (جدیدترین‌ها)

```php
function ez_queryable_set_recent_products() {
    $args = array (
            'post_type'         => 'product',
            'post_status'       => 'publish',
            'posts_per_page'    => -1,
            'meta_query'        => array(
                    'relation' => 'OR',
                    array( 'key' => 'product_state', 'value' => 'active', 'compare' => '==' ),
                    array( 'key' => 'product_state', 'value' => 'updated', 'compare' => '==' ),
            ),
    );
    $query = new WP_Query($args);
    while ($query->have_posts()) : $query->the_post();
        $product_data[] = get_the_ID();
    endwhile;
    wp_reset_postdata();
    ez_webservice( array('type' => 'recent_products_set', 'data' => $product_data) );
}
```

| بخش | کار |
|-----|-----|
| `meta_query` | محصولات با وضعیت `active` یا `updated`. |
| حلقه | فقط id محصولات در آرایهٔ `$product_data` جمع می‌شوند (ترتیب به ترتیب خروجی WP_Query است). |
| `recent_products_set` | همین آرایه به web-service فرستاده و در `products_order.recent` ذخیره می‌شود. |

---

# بخش ۳: web-service.php (دریافت و ذخیره در products_order)

## ۳.۱ popular_products_set

```php
if ($data->type == 'popular_products_set') {
    $products = $data->data;
    $temp = [];
    $products_alt = [];
    foreach ($products as $product_id => $data) {
        $result = $conn->query("SELECT * FROM product_views WHERE product_id LIKE '" . $product_id . "'");
        if ($result->num_rows > 0)
            $row = $result->fetch_assoc();
        $views      = $row['views'];
        $views30    = array_sum(array_slice((array)unserialize($row['views30']), -31, 30, true));
        $temp[$product_id] = ['comments_count' => $data->comments_count, 'rate' => $data->rate, 'views' => $row['views'], 'views30' => $views30];
        $products_alt[$product_id] = round(($data->comments_count * $data->rate) + ($row['views'] * $views30 / 925000));
    }
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
```

| بخش | کار |
|-----|-----|
| `$products` | آبجکت product_id => { comments_count, rate } از وردپرس. |
| برای هر محصول | از جدول `product_views` همان دیتابیس، `views` و ۳۰ روز آخر `views30` خوانده می‌شود. |
| `$products_alt[...]` | فرمول محبوب: (تعداد نظر × امتیاز) + (بازدید کل × بازدید ۳۰ روز / ۹۲۵۰۰۰). |
| `asort` و `array_reverse` | ترتیب از محبوب‌ترین به کم‌محبوب. |
| آخر | یک ردیف `products_order` وجود داشته باشد UPDATE، وگرنه INSERT؛ ستون `popular` با آرایهٔ سریال‌شده پر می‌شود. |

---

## ۳.۲ topsale_products_set

```php
if ($data->type == 'topsale_products_set') {
    $products = $data->data;
    $result = $conn->query("SELECT * FROM `products_order`");
    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `topsale`= '%s'",  serialize($products)));
    else
        $conn->query(sprintf("INSERT INTO products_order (topsale) VALUES ('%s')", serialize($products)));
}
```

لیست پرفروش (آرایهٔ product_id) از وردپرس آمده؛ همان‌طور که هست در ستون `topsale` ذخیره می‌شود (UPDATE یا INSERT).

---

## ۳.۳ recent_products_set

```php
if ($data->type == 'recent_products_set') {
    $products = $data->data;
    $result = $conn->query("SELECT * FROM `products_order`");
    if ($result->num_rows > 0)
        $conn->query(sprintf("UPDATE `products_order` SET `recent`= '%s'", serialize($products)));
    else
        $conn->query(sprintf("INSERT INTO products_order (recent) VALUES ('%s')", serialize($products)));
}
```

آرایهٔ جدیدترین محصولات در ستون `recent` قرار می‌گیرد.

---

## ۳.۴ hottest_products_set

```php
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
    if ($row_exists)
        $conn->query(sprintf("UPDATE `products_order` SET `hottest`= '%s'",  serialize($final_products)));
    else
        $conn->query(sprintf("INSERT INTO products_order (hottest) VALUES ('%s')", serialize($final_products)));
}
```

| بخش | کار |
|-----|-----|
| `$products` | لیست داغ‌ترین از وردپرس. |
| خواندن `products_order` | اگر ردیف وجود داشت، ستون `recent` هم خوانده می‌شود. |
| `$hottest_lookup` | برای چک سریع که کدام id در لیست داغ هست. |
| حلقه روی `$recent_products` | هر محصولی که در لیست داغ نبود، به `$recent_products_to_add` اضافه می‌شود. |
| `$final_products` | اول همهٔ داغ‌ترین‌ها، بعد بقیه به ترتیب لیست جدیدترین‌ها. |
| آخر | این لیست نهایی در ستون `hottest` ذخیره می‌شود. |

---

# بخش ۴: پر شدن hottest_products (با هر کامنت)

**فایل:** `wp-content/themes/escapezoom-v2/app/ajax/callbacks/site/product_add_comment.php`

```php
$power_map = [
    1 => 1,
    2 => 2,
    3 => 7,
    4 => 20,
];
$wpdb->insert( 'hottest_products', array(
    'product_id'        => $product_id,
    'comment_id'        => $comment_id,
    'w_rate'            => (int)get_comment_meta($comment_id, 'rating', true),
    'w_comments_count'  => $power_map[$user_level] ?? 1,
    'time'              => time()
));
```

| فیلد | معنی |
|------|------|
| `product_id` | محصولی که روی آن کامنت گذاشته شده. |
| `comment_id` | همان کامنت. |
| `w_rate` | امتیاز همان کامنت (۱–۵ یا مقیاس مشابه). |
| `w_comments_count` | وزن بر اساس سطح کاربر: ۱→۱، ۲→۲، ۳→۷، ۴→۲۰. |
| `time` | زمان ثبت برای فیلتر ۹۰ روز بعداً. |

با هر ثبت کامنت، یک رکورد به `hottest_products` اضافه می‌شود و در اجرای بعدی داغ‌ترین‌ها استفاده می‌شود.

---

# خلاصهٔ نحوهٔ اجرا

1. **اجرای یک‌جا (همهٔ لیست‌ها)**  
   - مرورگر:  
     `https://دامین/run-update-products-order.php?key=ez_update_products_order_2026`  
   - ترمینال:  
     `php run-update-products-order.php`  
   (از مسیری که فایل در آن است اجرا شود؛ یا مسیر کامل به فایل بدهید.)

2. **پیش‌نیاز**  
   - وردپرس و تم لود شوند (با همان مسیرهای داخل اسکریپت).  
   - دامنه/هاست طوری باشد که درخواست از وردپرس به آدرس همان سایت (مثلاً `https://دامین/web-service/web-service.php`) برسد.  
   - دیتابیس رزرو و جداول وردپرس (`hottest_products`, `held_orders_list`, متا محصولات و …) و دیتابیس web-service (`product_views`, `products_order`) در دسترس و به‌روز باشند.

3. **ترتیب منطقی اجرا در اسکریپت**  
   - اول `update_held_sans_table_func()` تا `held_orders_list` برای پرفروش به‌روز شود.  
   - بعد به ترتیب: داغ‌ترین، محبوب، پرفروش، جدیدترین. هر کدام داده را محاسبه و با `ez_webservice` به web-service می‌فرستند و آنجا در `products_order` ذخیره می‌شود.

4. **نکتهٔ API بازدید ۳۰ روز**  
   برای داغ‌ترین‌ها، وردپرس نوع `get_products_30days_views_count` را صدا می‌زند. اگر در web-service این نوع هندل نشده باشد، باید در همان سرویس با خواندن جدول `product_views` و جمع ۳۰ روز آخر `views30` پیاده شود و خروجی به صورت `{ product_id: تعداد }` برگردد.

با این ترتیب، کل کد از «اجرا» تا «ذخیره در products_order» خط‌به‌خط قابل پیگیری و اجرا است.
