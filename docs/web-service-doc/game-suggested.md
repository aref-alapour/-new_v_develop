## خلاصه‌ی کلی وب‌سرویس `game-suggested.php`

این فایل یک **وب‌سرویس PHP** شبیه به `game-promotional.php` است که برای **پیدا کردن بازی‌های پیشنهادی (suggested) یک شهر بر اساس یک تگ خاص** استفاده می‌شود.  
به‌طور خلاصه:

- ورودی را از طریق **درخواست POST** (بدنه‌ی JSON یا فرم-encoded) دریافت می‌کند.
- بر اساس `slug` شهر، از جدول `wp_options` وردپرس گزینه‌ای با نام `suggested_products_{slug}` را می‌خواند.
- از درون این گزینه، لیست `product_id`‌های بازی‌های «پیشنهادی» آن شهر را استخراج می‌کند.
- سپس روی جدول `products_data` فیلتر می‌کند تا فقط آن بازی‌هایی را نگه دارد که:
  - در لیست suggested آن شهر هستند،
  - و فیلد `tags_id` آن‌ها شامل `tag_id` خواسته شده است.
- در نهایت لیست `product_id`های پیدا شده را به صورت JSON برمی‌گرداند.

---

## پیش‌نیازها و تنظیمات ابتدایی

- **CORS**:
  - با هدرهای:
    - `Access-Control-Allow-Origin: *`
    - `Access-Control-Allow-Headers: ...`
    - `Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE, PATCH`
    - `Access-Control-Max-Age: 1728000`
  - درخواست‌های `OPTIONS`:
    - با `http_response_code(204)` پاسخ داده می‌شوند و `exit` می‌شود (برای preflight).

- **تنظیمات PHP**:
  - `error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);`
  - `date_default_timezone_set("Asia/Tehran");`

- **اتصال دیتابیس / Medoo**:
  - `require 'md-connect.php';`
  - این فایل، اتصال‌های زیر را آماده می‌کند:
    - `global $ez_database;` → دیتابیس وردپرس (شامل `wp_options`).
    - `global $qr_database;` → دیتابیس محصولات `products_data`.
    - و احتمالاً کانکشن `$conn` برای کوئری مستقیم (در ثبت لاگ hackers استفاده می‌شود).

- **کنترل دامنه‌های مجاز**:

  ```php
  if (! ($_SERVER['HTTP_HOST'] == 'escapezoom.ir' || ... || $_SERVER['HTTP_HOST'] == 'dev.escapezoom.local')) {
      $conn->query(sprintf("INSERT INTO hackers (host, referer) VALUES ('%s', '%s')", $_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER']));
      die('Get outta here');
  }
  ```

  - فقط روی دامنه‌های رسمی (escapezoom.ir، dev-api، bak، zoom، goriza، لوکال و ...) پاسخ می‌دهد.
  - در غیر این صورت، دامنه و ریفرر را در جدول `hackers` لاگ کرده و بلافاصله اسکریپت را متوقف می‌کند.

- **محدودیت متد و نوع محتوا**:
  - فقط متد **POST**:
    - در غیر این صورت:
      - `405` و JSON: `{"error":"Invalid Request Method"}`.
  - برای POST:
    - اگر `CONTENT_TYPE` شامل `application/json` باشد:
      - بدنه‌ی خام (`php://input`) را `json_decode` کرده و در `$data` می‌ریزد.
    - اگر `application/x-www-form-urlencoded` باشد:
      - `$_POST` را به JSON و object تبدیل می‌کند.
    - در غیر این دو حالت:
      - `415` و JSON: `{"error":"Unsupported Media Type"}`.

- **`home_url`**:
  - پیش‌فرض: `https://escapezoom.ir`.
  - اگر هاست `dev.escapezoom.local` باشد:
    - `http://dev.escapezoom.local`.
  - در این فایل مستقیماً از `home_url` استفاده نمی‌شود، فقط برای سازگاری با سایر سرویس‌ها تعریف شده است.

---

## ساختار ورودی

منطق اصلی از این شرط شروع می‌شود:

```php
if ($data && $data->tag_id) {
    ...
}
```

یعنی:

- باید `$data` وجود داشته باشد (بدنه‌ی POST به‌درستی پارس شده باشد).
- باید فیلدی به نام `tag_id` در ورودی وجود داشته باشد و مقدار آن truthy باشد.

علاوه بر `tag_id`، در ادامه از `slug` هم استفاده می‌شود، پس ورودی معمول به شکل زیر است:

```json
{
  "tag_id": 15,
  "slug": "tehran"
}
```

یا معادل فرم-encoded (در حالت `application/x-www-form-urlencoded`).

---

## منطق دیتابیس و فیلتر بازی‌ها

### ۱. آماده‌سازی اتصال‌ها و نام گزینه

در ابتدای بلاک:

```php
global $ez_database;
global $qr_database;
$city_suggets_name = 'suggested_products_' . $data->slug;
```

- دو اتصال Medoo را global می‌کند:
  - `ez_database` برای وردپرس،
  - `qr_database` برای دیتابیس محصولات.
- نام گزینه (option) وردپرسی برای بازی‌های پیشنهادی آن شهر را تولید می‌کند:
  - `suggested_products_{slug}`
  - مثال:
    - `slug = "tehran"` → `suggested_products_tehran`.

### ۲. خواندن لیست بازی‌های پیشنهادی شهر از `wp_options`

```php
$games = $ez_database->select('wp_options', ['option_value'], ['option_name' => $city_suggets_name]);
```

- جدول: `wp_options`.
- شرط: `option_name = 'suggested_products_{slug}'`.
- خروجی: فقط ستون `option_value`.

اگر `$games` مقدار داشته باشد:

```php
$game_value = unserialize($games[0]["option_value"]);
if (!empty($game_value["products"])) {
    $product_ids = $game_value["products"];
    ...
}
```

- `option_value` به‌صورت `serialize` شده ذخیره شده است؛ با `unserialize` آرایه می‌شود.
- انتظار می‌رود ساختاری شبیه:

```php
$game_value = [
  'products' => [101, 202, 303, ...],
  // احتمالاً مقادیر دیگری مثل عنوان، توضیح و ...
];
```

- اگر کلید `products` خالی نباشد:
  - `product_ids` آرایه‌ی شناسه‌ی محصول‌های پیشنهادی آن شهر خواهد بود.

### ۳. اعتبارسنجی لیست `product_ids`

```php
if (!empty($product_ids) && is_array($product_ids)) {
    $ids_str = implode(',', array_map('intval', $product_ids));
    $tag_id = '%' . intval($data->tag_id) . '%';
    ...
}
```

- اینجا دو متغیر محاسبه می‌شود:
  - `ids_str`: رشته‌ی comma-separated از `product_id`ها (برای استفاده‌ی احتمالی در SQL خام).
  - `tag_id`: رشته‌ی `%{tag_id}%` (برای LIKE).
- اما در نسخه‌ی فعلی کد:
  - هیچ‌یک از `ids_str` و `tag_id` **در ادامه استفاده نمی‌شوند**؛
  - چون کوئری اصلی با Medoo نوشته شده و مستقیماً از خود `product_ids` استفاده می‌کند.
  - این بخش نشانه‌ی مهاجرت از نسخه‌ای بر پایه‌ی SQL خام به Medoo است و می‌تواند برای تمیزی کد حذف شود.

### ۴. جستجو در `products_data` بر اساس تگ

کامنت کد:

```php
// جستجو در جدول products_data برای product_idهایی که هم در لیست هستند و هم tags_id مشابه دارند
// استفاده از Medoo با [product_id] و [tags_id[~]] برای LIKE
```

کوئری Medoo:

```php
$results = $qr_database->select('products_data', ['product_id'], [
    'product_id' => $product_ids,
    'tags_id[~]' => intval($data->tag_id)
]);
```

تحلیل:

- جدول: `products_data`.
- خروجی: فقط ستون `product_id`.
- شرط‌ها:
  - `product_id` باید عضو آرایه‌ی `$product_ids` باشد:
    - یعنی فقط بازی‌هایی که قبلاً به‌عنوان suggested برای آن شهر تنظیم شده‌اند.
  - `tags_id[~]` با مقدار `intval($data->tag_id)`:
    - در Medoo `[~]` برای `LIKE` استفاده می‌شود.
    - بنابراین SQL نهایی تقریباً معادل است با:

```sql
SELECT product_id
FROM products_data
WHERE product_id IN (<لیست product_ids>)
  AND tags_id LIKE '%<tag_id>%';
```

- فرض: ستون `tags_id` در `products_data` رشته‌ای است که لیست شناسه‌ی تگ‌ها را (مثلاً با جداکننده‌های خاص) نگه می‌دارد؛ این الگو در پروژه‌های قبلی EscapeZoom هم استفاده شده است.

نتیجه:

- `$results` آرایه‌ای از آبجکت‌هایی است که هر کدام یک `product_id` دارند، مثلاً:

```php
[
  ['product_id' => 101],
  ['product_id' => 305],
  ...
]
```

### ۵. خروجی نهایی

- اگر به این مرحله برسد و کوئری Medoo اجرا شود:

```php
echo json_encode(['product_ids' => $results]);
```

- در سناریوهای دیگر (خطا یا نبود داده) سه نوع خروجی خالی داریم:

```php
echo json_encode(['product_ids' => []]);
```

استیت‌هایی که منجر به خروجی خالی می‌شوند:

- گزینه‌ی `suggested_products_{slug}` در `wp_options` پیدا نشده (`$games` خالی است).
- گزینه پیدا شده ولی `game_value["products"]` خالی است.
- `product_ids` خالی است یا آرایه نیست.

در هیچ‌کدام از این حالت‌ها خطای HTTP کدی خاصی ست نمی‌شود؛ همیشه پاسخ 200 با `product_ids: []` برمی‌گردد (بعد از عبور از مرحله‌ی validate متد و content type).

---

## محل استفاده در پروژه

با جستجوی `"game-suggested.php"` در کل پروژه:

- در فایل `wp-content/themes/escapezoom-v2/template/product-archive/genre.php`، آدرس این وب‌سرویس ساخته می‌شود:

  ```js
  let suggestUrlWebService = 'https://' + location.hostname + '/web-service/game-suggested.php';
  // در برخی شرایط برای لوکال یا http:
  suggestUrlWebService = 'http://' + location.hostname + '/web-service/game-suggested.php';
  ```

- نتیجه:
  - **مصرف‌کننده‌ی اصلی این وب‌سرویس، صفحه‌ی آرشیو محصول/ژانر** است (لیست بازی‌ها بر اساس ژانر).
  - در این صفحه، با ارسال `slug` شهر و `tag_id` (احتمالاً تگ ژانر یا نوع پیشنهاد)، از این سرویس لیست `product_id`های پیشنهادی را می‌گیرد تا در فرانت‌اند:
    - بعضی بازی‌ها را هایلایت کند،
    - یا سکشن جداگانه‌ای برای «بازی‌های پیشنهادی در این ژانر/شهر» بسازد.

---

## جمع‌بندی فنی

- **نقش وب‌سرویس**:
  - برگرداندن بازی‌های پیشنهادی برای یک شهر خاص که با تگ مشخصی (`tag_id`) هم‌خوانی دارند.
  - نقطه‌ی اتصال بین:
    - تنظیمات مدیریتی (که در `wp_options` به‌صورت `suggested_products_{slug}` ذخیره شده)،
    - و داده‌های محصولات (`products_data` با فیلد `tags_id`).

- **وابستگی‌ها**:
  - `md-connect.php` (اتصال Medoo و احتمالا `$conn`).
  - جدول‌های:
    - `wp_options` (کلید `suggested_products_{slug}`، مقدار serialize شده با آرایه‌ی `products`).
    - `products_data` (فیلدهای `product_id` و `tags_id`).
    - `hackers` برای لاگ هاست‌های غیرمجاز.

- **ورودی**:
  - POST JSON/form شامل:

```json
{
  "slug": "tehran",
  "tag_id": 15
}
```

- **خروجی**:
  - در حالت موفق/عادی:

```json
{
  "product_ids": [
    { "product_id": 101 },
    { "product_id": 305 }
  ]
}
```

  - در حالت خالی بودن داده‌ها:

```json
{
  "product_ids": []
}
```

این مستند، رفتار `game-suggested.php` را از لحاظ ورودی، منطق دیتابیس، فیلترینگ با `tag_id`، محل استفاده در فرانت‌اند و شکل خروجی نهایی پوشش می‌دهد و می‌تواند مبنایی برای توسعه‌ی بیشتر یا refactor این سرویس باشد.

