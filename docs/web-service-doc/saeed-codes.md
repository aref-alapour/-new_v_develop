## نقش کلی `saeed-codes.php`

این فایل یکی از **سنگین‌ترین و مهم‌ترین فایل‌های منطق تجاری قالب** است و مثل یک «فریم‌ورک داخلی» برای اتصال وردپرس/WooCommerce به وب‌سرویس‌ها و سرویس‌های بک‌اند EscapeZoom عمل می‌کند. مهم‌ترین مسئولیت‌های این فایل:

- غیرفعال کردن admin bar برای نقش‌های خاص (`customer`, `compiler`, `sans_manager`).
- چند ابزار دیباگ سریع (`saeed_store`, `saeed_print`).
- اضافه‌کردن ستون‌های سفارشی به لیست سفارش‌های ووکامرس (سانس و مبلغ پیش‌پرداخت) و پر کردن‌شان از `wp_zb_booking_history` و متاهای سفارش.
- دو wrapper اصلی برای ارتباط با وب‌سرویس‌ها:
  - `ez_webservice` → `web-service/web-service.php`
  - `ez_reservation` → `web-service/reservation.php`
- شورت‌کد `[product_query]` و AJAX برای جستجوی بازی‌ها با `web-service/queryable.php`.
- ثبت و زمان‌بندی **ده‌ها cron job** که:
  - محصولات «داغ»، «محبوب»، «پرفروش» و «جدید» را محاسبه و در وب‌سرویس ذخیره می‌کنند.
  - داده‌ی `products_data` (active و non-active) را با پست‌های ووکامرس سینک می‌کنند.
  - به‌صورت زمان‌بندی‌شده عملیات دیگری مثل SMS queue، پردازش تراکنش‌های زرین‌پال، به‌روزرسانی امتیاز کامنت‌ها، یادآور پیامک نظرات و بهینه‌سازی جداول رزرو را اجرا می‌کنند.
- مجموعه‌ای از توابع محاسباتی و کمکی (در ادامه‌ی فایل) برای:
  - محاسبه‌ی امتیاز بیزی، نرمال‌سازی بازدیدها، نگهداری جداول کمکی (`hottest_products`, `held_orders_list`، ...).
  - مدیریت صف SMS، مدیریت کیف‌پول‌ها، امتیازدهی نظرات و غیره.

این مستند روی **بخش‌هایی که مستقیم با وب‌سرویس‌ها و لیست محصولات و سانس‌ها در ارتباط‌اند** تمرکز می‌کند، چون بخش‌های پایینی فایل (SMS، Zarinpal، مدیریت کیف‌پول، ... ) عملاً ماژول‌های جداگانه‌ای هستند که اگر لازم شد می‌توان برای هر کدام مستند اختصاصی نوشت.

---

## تنظیمات اولیه و includeها

- `date_default_timezone_set('Asia/Tehran');`
- `error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);`
- `define('COMMENT_NEW_VER_TIMESTAMP', 1746044999);`  
  استفاده‌ی این ثابت در بخش‌های پایین‌تر برای تفکیک نسخه‌ی جدید سیستم امتیازدهی کامنت‌هاست.
- includeهای مهم:
  - `require_once 'api/api.php';` → شامل توابع API داخلی قالب (RESTهای سفارشی).
  - `require_once 'medoo/init.php';` → آماده‌سازی Medoo برای اتصال مستقیم به DB.
  - `require_once 'jwt-authentication-for-wp-rest-api/jwt-auth.php';` → احراز هویت JWT برای REST API.

---

## کنترل admin bar برای نقش‌ها

```php
add_action('after_setup_theme', 'disable_admin_bar_for_non_admins');
function disable_admin_bar_for_non_admins() {
    if (has_role('customer') || has_role('compiler') || has_role('sans_manager')) {
        add_filter('show_admin_bar', '__return_false');
    }
}
```

- اگر کاربر نقش «مشتری»، «اتاق‌ساز (`compiler`)» یا «مدیر سانس (`sans_manager`)» داشته باشد، admin bar وردپرس برایش مخفی می‌شود.
- `has_role` در جای دیگری (احتمالاً در همین فایل یا جای دیگر قالب) تعریف شده و نقش فعال کاربر را چک می‌کند.

---

## ابزارهای دیباگ: `saeed_store`, `saeed_print`

### `saeed_store($val = '', $die = false)`

- با `add_option(microtime(true) * 1000, $val)` یک option با کلید یکتا (بر اساس microtime) در دیتابیس ذخیره می‌کند.
- برای dump کردن اطلاعات در DB (برای بررسی با phpMyAdmin) مفید است.
- اگر `$die == true`، بلافاصله `die()` می‌کند.

### `saeed_print($val = '', $die = false)`

- مقدار را در `<pre>` با `print_r` چاپ می‌کند.
- اگر `$die == true`، بلافاصله `die()`.
- در بخش‌های مختلف، خصوصاً در محاسبه‌ی hottest، از این تابع برای چاپ آرایه‌های تشخیصی استفاده شده است.

---

## سفارشی‌سازی لیست سفارش‌های ووکامرس

### افزودن ستون‌ها: `sans_time` و `deposit`

```php
add_filter('manage_edit-shop_order_columns', 'custom_shop_order_column', 20);
function custom_shop_order_column($columns) {
    $reordered_columns = [];
    foreach ($columns as $key => $column) {
        $reordered_columns[$key] = $column;
        if ($key == 'order_status') {
            $reordered_columns['sans_time'] = __('سانس', 'theme_domain');
            $reordered_columns['deposit']   = __('سپرده', 'theme_domain');
        }
    }
    return $reordered_columns;
}

add_filter('manage_edit-shop_order_sortable_columns', 'custom_shop_order_sortable_columns');
function custom_shop_order_sortable_columns($sortable_columns) {
    $sortable_columns['sans_time'] = 'sans_time';
    $sortable_columns['deposit']   = 'deposit';
    return $sortable_columns;
}
```

- ستون «سانس» و «سپرده» بعد از ستون وضعیت سفارش اضافه می‌شوند و قابل sort شدن هستند.

### پر کردن محتوای ستون‌ها: `custom_orders_list_column_content`

```php
add_action('manage_shop_order_posts_custom_column', 'custom_orders_list_column_content', 20, 2);
function custom_orders_list_column_content($column, $post_id) {
    static $booking_cache = [];
    switch ($column) {
        case 'sans_time':
            // ...
        case 'deposit':
            // ...
    }
}
```

**ستون `sans_time`**:

- از cache استاتیک (`$booking_cache`) استفاده می‌کند تا برای هر سفارش فقط یک‌بار از DB بخواند.
- ابتدا سعی می‌کند از Medoo استفاده کند (سریع‌تر از wpdb):

```php
if (function_exists('medoo')) {
    $medoo = medoo();
    $booking = $medoo->get('wp_zb_booking_history', 'booking_time', [
        'wc_order_id' => $post_id,
        'ORDER'       => ['booking_id' => 'DESC']
    ]);
    $booking_cache[$post_id] = $booking;
}
```

- اگر Medoo در دسترس نباشد:
  - یک فراخوانی به `ez_reservation` با `type = query_execution` می‌زند که inside `reservation.php` یک SELECT روی `wp_zb_booking_history` انجام می‌دهد و آخرین `booking_time` را برمی‌گرداند.
- اگر `booking_time` پیدا شد:
  - با `wp_date('H:i ..... Y-m-d', $n)` به‌صورت خوانا در ستون چاپ می‌شود (ساعت و تاریخ سانس برای آن سفارش).

**ستون `deposit`**:

- ابتدا متای `_order_total_2` را می‌خواند (مبلغ پیش‌پرداخت).
- اگر خالی بود از `_order_total` استفاده می‌کند (مبلغ کل).
- مقدار را `number_format` کرده و با پسوند «تومان» نمایش می‌دهد.

---

## توابع ارتباط با وب‌سرویس‌ها

### `ez_webservice($data)`

**نقش**: wrapper برای صدا زدن `web-service/web-service.php` از داخل قالب.

- بر اساس `HTTP_HOST`، `base_url` را تعیین می‌کند:
  - لوکال (`wo.escapezoom.local`) → `http://host/web-service/web-service.php`
  - بقیه → `https://host/web-service/web-service.php`
- یک `wp_remote_post` با:
  - `headers: Content-Type: application/json`
  - `body: json_encode($data)`
اجرا می‌کند.
- اگر پاسخ آرایه باشد، `body` را برمی‌گرداند (همان JSON برگشتی از `web-service.php`).

### `ez_reservation($data)`

**نقش**: wrapper برای `web-service/reservation.php`.

- ساختن `base_url` مشابه بالا (فقط endpoint فرق می‌کند).
- `wp_remote_post` با:
  - `body: $data` (اینجا برخلاف `ez_webservice`، خود `$data` باید همان ساختاری باشد که `reservation.php` انتظار دارد؛ در کد بالا دیده شد که بعضاً آرایه‌ی `['type' => ..., 'data' => ...]` را مستقیم می‌فرستند).
- اگر response code = 200:
  - `body` را برمی‌گرداند.
- در غیر این صورت، آرایه‌ی `['error' => response_code]` را برمی‌گرداند.

این دو تابع در جاهای مختلف فایل (و بقیه‌ی قالب) برای هر نوع ارتباط با لایه‌ی وب‌سرویس استفاده می‌شوند.

---

## شورت‌کد جستجوی محصول: `[product_query]`

```php
add_shortcode('product_query', 'product_query');
function product_query() { ?>
    <form ... id="search-form">
        <input id="search_top2" name="s" type="search" placeholder=" جستجو سرگرمی..." ... >
        <input type="hidden" id="post_type" name="post_type" value="product">
    </form>
    <p id="search_result2" style="display:none"></p>
    <script> ... AJAX ... </script>
    <style> ... </style>
<?php }
```

- فرم یک سرچ ساده است که submit آن با JS متوقف می‌شود.
- روی `keyup` در `#search_top2`:
  - اگر مقدار خالی باشد → نتیجه (`#search_result2`) مخفی می‌شود.
  - در غیر این صورت، یک AJAX `POST` به:
    - `https://escapezoom.ir/web-service/queryable.php`
  - با `data`:

```js
{
  term: $('#search_top2').val(),
  url:  '<?php echo $_SERVER['HTTP_HOST'] ?>'
}
```

- `queryable.php` بر اساس `term` لیست محصولات را برمی‌گرداند (که قبلاً در `queryable.md` توضیح داده شد).
- پاسخ `data` مستقیماً داخل `<p id="search_result2">` ریخته و نشان داده می‌شود.
- CSS پایین تگ ظاهر dropdown نتایج را تنظیم می‌کند.

---

## ثبت cron jobها (فقط روی `escapezoom.ir`)

بخش زیر فقط اگر `HTTP_HOST == 'escapezoom.ir'` باشد اجرا می‌شود:

```php
add_filter('cron_schedules', function ($schedules) { ... });
add_action('ez_queryable_set_hottest_cron', 'ez_queryable_set_hottest_products');
add_action('ez_queryable_set_popular_cron', 'ez_queryable_set_popular_products');
add_action('ez_queryable_set_topsale_cron', 'ez_queryable_set_topsale_products');
add_action('ez_queryable_set_recent_cron', 'ez_queryable_set_recent_products');
add_action('ez_queryable_set_data_cron', 'ez_queryable_set_products_data');
add_action('ez_queryable_set_data_nactive_cron', 'ez_queryable_set_products_data_nactive');
...
add_action('ez_satisfaction_on_comments_cron', 'ez_satisfaction_on_comments');
add_action('ez_sms_sending_queue_cron', 'ez_sms_sending_queue_schedule');
add_action('ez_remove_expired_sms_queue_cron', 'ez_remove_expired_sms_queue_schedule');
add_action('wp_zb_booking_history_today_optimize_cron', 'wp_zb_booking_history_today_optimize');
add_action('update_comments_stars_cron', 'update_comments_stars');
add_action('comment_reminder_sms_process_cron', 'comment_reminder_sms_process');
add_action('zarinpal_paid_transactions_process_cron', 'zarinpal_paid_transactions_process');
add_action('zarinpal_co_paid_transactions_process_cron', 'zarinpal_co_paid_transactions_process');
```

- یک schedule جدید به نام `every_10_secs` (هر ۱۰ ثانیه) و `every_two_minutes` (هر ۲ دقیقه) اضافه می‌کند.
- برای هر job، اگر هنوز زمان‌بندی نشده باشد (`wp_next_scheduled`)، آن را با interval مناسب (hourly/daily/twicedaily/…) ثبت می‌کند.
- این کران‌ها توابع پایین‌تر فایل را فراخوانی می‌کنند که:
  - لیست‌های محصولات (`hottest/popular/topsale/recent`) را حساب و به `web-service` sync می‌کنند.
  - داده‌ی `products_data` را با پست‌های ووکامرس sync می‌کنند.
  - SMS queue و سایر لاجیک‌های غیرمرتبط با وب‌سرویس reservation/queryable را پردازش می‌کنند.

در ادامه روی توابع مربوط به sync و سورت محصولات تمرکز می‌کنیم.

---

## محاسبه‌ی «داغ‌ترین» محصولات: `ez_queryable_set_hottest_products`

```php
function ez_queryable_set_hottest_products() {
    global $wpdb;

    $wpdb->get_results("DELETE FROM hottest_products WHERE time < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY))");
    $C = 4.3;  // میانگین امتیاز
    $m = 15;   // حداقل تعداد کامنت
    $max_views = 4000;

    $rows = $wpdb->get_results("SELECT * FROM hottest_products", ARRAY_A);
    ...
}
```

- جدول `hottest_products` ظاهراً با هر ثبت/به‌روزرسانی کامنت پر می‌شود (در بخش‌های پایین‌تر فایل).
- این تابع:
  1. رکوردهای قدیمی‌تر از ۹۰ روز را پاک می‌کند.
  2. مجموع `w_rate * w_comments_count` و خود `w_comments_count` را برای هر `product_id` جمع می‌کند (چندین رکورد → weighted average).
  3. لیست `product_ids` را می‌سازد.
  4. با صدا زدن `ez_webservice(type='get_products_30days_views_count')` تعداد بازدید ۳۰ روز اخیر هر محصول را از وب‌سرویس می‌گیرد.
  5. برای هر محصول:
     - `w_rate` = میانگین rate وزن‌دار،
     - از تابع `get_bayesian_score(w_rate, w_comments_count, C, m)` استفاده می‌کند (ترکیب امتیاز و تعداد کامنت‌ها).
     - `normalized_bayesian_score` = ۰.۶ * امتیاز بیزی + ۰.۴ * log(count+1).
     - `normalized_views` = نرمال‌سازی log(view+1) روی بازه‌ی ۰-۵.
     - `hot_score` = ۰.۶۷ * normalized_bayesian_score + ۰.۳۳ * normalized_views.
  6. برخی `product_id`ها (لیست penalty) را تا تاریخ مشخصی حذف می‌کند (بخاطر سیاست‌ها/کیفیت).
  7. بر اساس `hot_score` sort می‌کند، فقط `product_id`ها را نگه می‌دارد و:

```php
ez_webservice(['type' => 'hottest_products_set', 'data' => array_reverse($product_data)]);
```

- `web-service.php` سپس این لیست را در `products_order.hottest` ذخیره می‌کند (طبق مستند `web-service.md`).

---

## محاسبه‌ی «محبوب‌ترین» محصولات: `ez_queryable_set_popular_products`

```php
function ez_queryable_set_popular_products() {
    $penalty_products = [...];
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'   => 'product_state',
                'value' => 'active',
                'compare' => 'LIKE',
            ],
        ],
    ];
    $query = new WP_Query($args);
    ...
}
```

- همه‌ی محصولات active را می‌گیرد.
- برای هر محصول:
  - `comments_count = get_post_meta('comments_count_new')`.
  - `rate = get_post_meta('product_rates')` (آرایه با چند ایندکس برای فیلدهای مختلف).
  - اگر محصول در `penalty_products` باشد:
    - `comments_count_penalty = comments_count / 2.5` (اثر کامنت‌ها کمتر می‌شود).
  - یک امتیاز rate میانگین به شکل:

```php
$temp['rate'] = $comments_count != 0
    ? (($rate[1094] + ... + $rate[1098]) / 5 / 20 / $comments_count)
    : 1;
```

- سپس آرایه‌ی `popular_products[product_id] = ['comments_count' => ..., 'rate' => ...]` را می‌سازد.
- دوباره لیست دیگری از `penalty_product_ids` را حذف می‌کند.
- در نهایت:

```php
ez_webservice(['type' => 'popular_products_set', 'data' => $popular_products]);
```

- و `web-service.php` این داده را در `products_order.popular` ذخیره می‌کند و از آن برای محاسبه‌ی سورت‌های front استفاده می‌کند.

---

## محاسبه‌ی «پرفروش‌ترین» (topsale): `ez_queryable_set_topsale_products`

```php
function ez_queryable_set_topsale_products() {
    global $wpdb;

    update_held_sans_table_func();
    $rows = $wpdb->get_results("SELECT * FROM held_orders_list", ARRAY_A);

    $power_map = [1 => 0.2, 2 => 0.4, 3 => 1, 4 => 1];
    $topsale = [];
    foreach ($rows as $row) {
        if (!isset($topsale[$row['room_id']]))
            $topsale[$row['room_id']] = $row['count'] * ($power_map[$row['level']] ?? 1);
        else
            $topsale[$row['room_id']] += $row['count'] * ($power_map[$row['level']] ?? 1);
    }
    asort($topsale);
    ...
    ez_webservice(['type' => 'topsale_products_set', 'data' => array_reverse($product_data)]);
}
```

- `held_orders_list` یک جدول کمکی است که احتمالاً از رزروهای موفق (یا orderهای hold شده برای >24 ساعت) پر می‌شود (تابع `update_held_sans_table_func` در جای دیگری تعریف شده).
- `level` به‌معنی سطح سانس/بازی است و با `power_map` وزن‌دهی می‌شود.
- با جمع وزن‌ها برای هر `room_id`، امتیاز topsale ساخته می‌شود.
- بعد از اعمال penalty‌ها، لیست `product_id`ها به صورت نزولی (بیشترین امتیاز اول) به `web-service (topsale_products_set)` فرستاده می‌شود.

---

## محاسبه‌ی «جدیدترین» محصولات: `ez_queryable_set_recent_products`

- همه‌ی پست‌های `product` با `product_state = active|updated` را می‌گیرد.
- فقط IDها را در آرایه `product_data[]` ذخیره می‌کند.
- این لیست به `web-service (recent_products_set)` فرستاده می‌شود تا در `products_order.recent` ذخیره شود.

---

## سینک داده‌ی محصولات با `products_data`: `ez_queryable_set_products_data` و `_nactive`

این دو تابع لایه‌ی میانی بین **پست‌های ووکامرس** و جدول backend `products_data` هستند و ساختارهای `stdClass` را دقیقاً مطابق چیزی که `web-service.php` در بخش `data_products_set` انتظار داشت می‌سازند.

### ۱. `ez_queryable_set_products_data` (برای active/updated)

- کوئری `WP_Query` با:
  - `post_type = product`,
  - `post_status = publish`,
  - `meta_query` برای `product_state = active|updated`.
- برای هر محصول:
  - متا و فیلدهای ACF مختلف را می‌خواند:
    - حداقل قیمت (`min_price` یا فیلد ACF `price_asli`).
    - `owner_id` و `manager_id` از متا.
    - اگر `special_discount_enable` فعال باشد:
      - `discount_data = { special_discount_percentage, special_discount_date }`؛ وگرنه رشته‌ی خالی.
    - URL تصویر شاخص را گرفته و بخش `https://escapezoom.ir/wp-content/uploads/` را از ابتدای آن برمی‌دارد تا یک مسیر نسبی ذخیره شود.
    - از `product_cat` نوع محصول (`product_type`) و شهر (`city_name`, `city_id`) را استخراج می‌کند.
    - `contact_info` را بر اساس `owner_id`/`manager_id` می‌سازد.
    - `clone_product_rates` و `clone_comments_count_new` را برای محاسبه‌ی میانگین امتیاز (`rate`) استفاده می‌کند.
    - `schedule_normals` و `schedule_holidays` را به‌صورت آرایه در `schedule` قرار می‌دهد.
    - تگ‌ها (`product_tag`) را به دو آرایه `tags_id` و `tags_title` می‌ریزد.
    - از `room_tedad` اعداد را استخراج می‌کند تا `count_min`/`count_max` (حداقل/حداکثر نفرات) را مشخص کند.
  - همه‌ی این‌ها را در یک `stdClass $temp` قرار می‌دهد:

```php
$temp->id             = $id;
$temp->type           = $product_type;
$temp->title          = get_the_title();
$temp->price          = ...;
$temp->notable        = 0;
$temp->special        = special_room ? 1 : 0;
$temp->active         = product_state;
$temp->monopoly       = monopoly ? 1 : 0;
$temp->brand_id       = product_brand;
$temp->discount_data  = $discount_data;
$temp->instant_off    = get_post_meta('instant_off');
$temp->geo            = lat . ',' . long;
$temp->image          = $trimmed_url;
$temp->age_limit      = room_age_limit;
$temp->level          = room_level;
$temp->schedule       = ['normals' => ..., 'holidays' => ...];
$temp->duration       = room_duration;
$temp->url            = slug نسبی /room/...
$temp->hood           = room_loc;
$temp->city_id        = $city_id;
$temp->city_name      = $city_name;
$temp->auto_disable   = auto_disable;
$temp->pish_person    = pish_pardakht_per_person;
$temp->contact_info   = $contact_info;
$temp->owner_phone    = owner_login;
$temp->chat_id        = owner_chat_id;
$temp->owner_id       = $owner_id;
$temp->manager_id     = $manager_id;
$temp->comments_count = $comments_count;
$temp->rate           = میانگین پنج فیلد rate;
$temp->count_min      = حداقل تعداد نفرات;
$temp->count_max      = حداکثر تعداد نفرات;
$temp->tags_id        = [...];
$temp->tags_title     = [...];
```

- همه‌ی این `temp`ها در یک آرایه‌ی `$product_data[]` جمع می‌شوند.
- در انتها:

```php
ez_webservice(['type' => 'data_products_set', 'data' => $product_data]);
```

- که در `web-service.php` منجر به بازسازی کامل جدول `products_data` (برای active/updated) می‌شود.

### ۲. `ez_queryable_set_products_data_nactive` (برای stateهای دیگر)

- تقریباً همان منطق، اما meta_query روی `product_state != active` و `!= updated` است (بازی‌های غیرفعال، به زودی، اکسپایر شده و ...).
- objectهای `temp` تقریباً هم‌ساختارند.
- در پایان:

```php
ez_webservice(['type' => 'data_products_set_nactive', 'data' => $product_data]);
```

- که در `web-service.php` قسمت `data_products_set_nactive`، این داده‌ها را در `products_data` برای محصولات غیرفعال می‌نویسد.

---

## سایر ماژول‌ها (به‌صورت خلاصه)

در ادامه‌ی `saeed-codes.php`، بخش‌های دیگری هم وجود دارد که هر کدام ماژول نسبتاً مستقلی هستند:

- **مدیریت امتیازدهی و نظرسنجی‌ها**:
  - توابعی مثل `ez_satisfaction_on_comments`, `update_comments_stars`, `comment_reminder_sms_process` که با استفاده از timestamp `COMMENT_NEW_VER_TIMESTAMP` و جداول سفارشی، سیستم جدید rating/comment را محاسبه و sync می‌کنند.

- **مدیریت SMS queue**:
  - `ez_sms_sending_queue_schedule`, `ez_remove_expired_sms_queue_schedule` و توابع مرتبط با ارسال SMS دسته‌ای، مدیریت صف، و پاک‌کردن پیامک‌های منقضی.

- **پردازش تراکنش‌های زرین‌پال**:
  - `zarinpal_paid_transactions_process`, `zarinpal_co_paid_transactions_process` که جدول تراکنش‌ها را می‌خوانند، وضعیت‌ها را sync می‌کنند و در صورت نیاز رزرو/سفارش را به‌روزرسانی می‌کنند.

- **بهینه‌سازی جدول رزروهای روز**:
  - `wp_zb_booking_history_today_optimize` که با نقل‌وانتقال داده‌ها بین جداول و پاک‌سازی رکوردهای قدیمی، عملکرد را بهبود می‌دهد.

این توابع با وب‌سرویس‌ها و داده‌های `wp_zb_booking_history` / `products_data` در تعامل‌اند ولی منطق‌شان بیشتر روی عملیات پس‌زمینه و بهینه‌سازی است تا API مستقیم.

---

## جمع‌بندی

- `saeed-codes.php` لایه‌ی glue بین **وردپرس/WooCommerce** و **لایه‌ی وب‌سرویس PHP** شماست:
  - cron jobها را ثبت و هر ساعت/روز داده‌ی محصولات، لیست‌های سورت و آمارها را به `web-service.php` push می‌کند.
  - در admin (لیست سفارش‌ها) و فرانت‌اند (جستجو، کارت‌ها، سانس‌ها) از توابع wrapper (`ez_webservice`, `ez_reservation`) و داده‌های `products_data` و `wp_zb_booking_history` استفاده می‌کند.
- برای هر تغییر در مدل داده، sort محصولات، یا UI جستجو/سانس و صفحه‌های لیست، باید این فایل را به‌عنوان **جای اصلی هماهنگی بین دیتابیس وردپرس و وب‌سرویس‌ها** در نظر بگیری و به‌روزرسانی‌اش را با `web-service.php` و `reservation.php` هماهنگ کنی.  
- این مستند مهم‌ترین بخش‌های مربوط به **جریان داده‌ی محصولات و سانس‌ها** را پوشش می‌دهد؛ اگر نیاز داشتی می‌توانیم برای بخش‌های SMS، Zarinpal، امتیازدهی نظرات و کیف‌پول نیز مستندات جزئی‌تر بنویسیم.

