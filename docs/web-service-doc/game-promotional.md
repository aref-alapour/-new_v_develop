## خلاصه‌ی کلی وب‌سرویس `game-promotional.php`

این فایل یک **وب‌سرویس PHP سبک** است که برای **پیدا کردن لیست بازی‌های پروموشنال (تبلیغاتی) یک شهر با یک تَگ مشخص** استفاده می‌شود.  
به‌صورت خلاصه:

- ورودی را از طریق **POST (JSON یا فرم)** می‌گیرد.
- از روی `slug` شهر (مثل نام انگلیسی شهر) داخل جدول `wp_options` وردپرس دنبال تنظیمات پروموشن آن شهر می‌گردد.
- از این تنظیمات، لیست `product_id`‌های بازی‌های پروموشنال را استخراج می‌کند.
- سپس روی جدول `products_data` جستجو می‌کند تا فقط آن بازی‌هایی را نگه دارد که **هم**:
  - در لیست پروموشنال شهر هستند،
  - و **تگ خاصی** (`tag_id`) را در فیلد تگ‌هایشان دارند.
- در نهایت لیست `product_id`های پیدا شده را به صورت JSON برمی‌گرداند.

این سرویس برای سناریوهایی مثل «بازی‌های تخفیف‌دار/پیشنهادی شهر X که تگ Y را دارند» کاربرد دارد.

---

## پیش‌نیازها و تنظیمات ابتدایی

- **CORS**:
  - `Access-Control-Allow-Origin: *` → همه‌ی originها مجازند.
  - هدرهای مجاز و متدها (`GET, POST, OPTIONS, PUT, DELETE, PATCH`) مشخص شده‌اند.
  - درخواست‌های `OPTIONS` با کد 204 پاسخ داده و بلافاصله `exit` می‌شوند (برای preflight).

- **تنظیمات PHP**:
  - `error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);`
  - `date_default_timezone_set("Asia/Tehran");`

- **اتصال دیتابیس**:
  - به‌جای `db-connect.php` این فایل `md-connect.php` را لود می‌کند:
    - `require 'md-connect.php';`
  - این فایل احتمالاً دو اتصال Medoo/DB مختلف را آماده می‌کند:
    - `global $ez_database;` → برای دیتابیس اصلی وردپرس (`wp_options` و ...).
    - `global $qr_database;` → برای دیتابیس محصولات (`products_data` و ...).

- **کنترل دامنه‌ی مجاز**:
  - فقط روی این دامنه‌ها اجازه می‌دهد:
    - `escapezoom.ir`
    - `escapezoom.co`
    - `bak.escapezoom.ir`
    - `dev-api.escapezoom.ir`
    - `zoom.escapezoom.ir`
    - `goriza.ir`
    - `dev.escapezoom.local`
  - اگر `HTTP_HOST` خارج از این لیست باشد:
    - سعی می‌کند در جدول `hackers` یک رکورد با `host` و `referer` ثبت کند (با استفاده از `$conn->query`؛ که از `md-connect.php` می‌آید).
    - سپس `die('Get outta here');`

- **محدودیت متد و نوع محتوا**:
  - اگر متد درخواست **POST** نباشد:
    - `405` و خروجی JSON: `{"error":"Invalid Request Method"}`.
  - در حالت POST:
    - اگر `CONTENT_TYPE` حاوی `application/json` باشد:
      - بدنه‌ی خام `php://input` را `json_decode` کرده و در `$data` می‌ریزد.
    - اگر `application/x-www-form-urlencoded` باشد:
      - `$_POST` را به JSON و بعد به object تبدیل می‌کند.
    - در غیر این دو حالت:
      - `415` و JSON: `{"error":"Unsupported Media Type"}`.

- **متغیر `home_url`**:
  - پیش‌فرض: `https://escapezoom.ir`.
  - اگر هاست `dev.escapezoom.local` باشد:
    - `http://dev.escapezoom.local`.
  - در ادامه‌ی این فایل از `home_url` استفاده‌ای نشده؛ بیشتر برای یکپارچگی با سایر وب‌سرویس‌ها تعریف شده است.

---

## ساختار ورودی و شرط اولیه

بخش اصلی منطق از این شرط شروع می‌شود:

```php
if ($data && $data->tag_id) {
    ...
}
```

بنابراین ورودی معتبر باید:

- حتماً `$data` داشته باشد (بدنه‌ی POST پارس شده باشد).
- حتماً داخل این داده فیلدی به نام `tag_id` موجود و truthy باشد.

علاوه بر `tag_id`، در ادامه از `slug` هم استفاده می‌شود، پس ورودی عملی پیشنهادی به شکل زیر است:

```json
{
  "tag_id": 123,
  "slug": "tehran"
}
```

یا معادل آن اگر فرم‌-encoded ارسال شود.

---

## منطق دیتابیس و فیلترینگ

### ۱. آماده‌سازی متغیرها و اتصال‌ها

داخل شرط:

```php
global $ez_database;
global $qr_database;
$city_promotional_name = 'promotional_products_' . $data->slug;
```

- `ez_database` و `qr_database` از Medoo هستند (مطابق نام فایل `md-connect`).
- نام گزینه (option) وردپرسی که نگهدارنده‌ی بازی‌های پروموشنال برای آن شهر است را می‌سازد:
  - `promotional_products_{slug}`
  - مثلاً اگر `slug = 'tehran'` → `promotional_products_tehran`.

### ۲. خواندن لیست بازی‌های پروموشنال شهر از `wp_options`

```php
$games = $ez_database->select('wp_options', ['option_value'], ['option_name' => $city_promotional_name]);
```

- این کوئری:
  - جدول: `wp_options`.
  - شرط: `option_name = 'promotional_products_{slug}'`.
  - ستون خروجی: فقط `option_value`.

- اگر `$games` مقدار داشته باشد:

  ```php
  $game_value = unserialize($games[0]["option_value"]);
  if (!empty($game_value["products"])) {
      $product_ids = $game_value["products"];
      ...
  }
  ```

  - `option_value` به‌صورت `serialize` شده ذخیره شده است؛ با `unserialize` تبدیل به آرایه می‌شود.
  - انتظار می‌رود ساختارش چیزی شبیه زیر باشد:

    ```php
    $game_value = [
      "products" => [101, 102, 103, ...],
      // شاید فیلدهای دیگری مثل عنوان، توضیحات، و غیره
    ];
    ```

  - اگر کلید `"products"` خالی نباشد:
    - `$product_ids` را می‌گیرد که یک آرایه از شناسه‌ی محصولات است.

### ۳. اعتبارسنجی لیست محصولات

```php
if (!empty($product_ids) && is_array($product_ids)) {
    $ids_str = implode(',', array_map('intval', $product_ids));
    $tag_id = '%' . intval($data->tag_id) . '%';
    ...
}
```

- `ids_str` یک رشته‌ی comma-separated از `product_id`هاست، اما در ادامه در کوئری Medoo مستقیم از آرایه‌ی `product_ids` استفاده می‌کند، نه از `ids_str` (یعنی این متغیر فعلاً بلااستفاده است؛ بیشتر شبیه کدی است که از نسخه‌ی SQL-خام به Medoo مهاجرت کرده).
- `tag_id` هم به‌صورت رشته‌ی `%{tag_id}%` ساخته می‌شود، اما باز در کوئری مدو از این متغیر استفاده نمی‌شود و به جای آن مقدار عددی `tag_id` به Medoo داده شده است؛ یعنی این هم در کد فعلی بلااستفاده است.

### ۴. فیلتر بازی‌ها بر اساس تگ در `products_data`

کامنت کد:

```php
// جستجو در جدول products_data برای product_idهایی که هم در لیست هستند و هم tags_id مشابه دارند
// استفاده از Medoo با [product_id] و [tags_id[~]] برای LIKE
```

و سپس:

```php
$results = $qr_database->select('products_data', ['product_id'], [
    'product_id' => $product_ids,
    'tags_id[~]' => intval($data->tag_id)
]);
```

تحلیل:

- جدول: `products_data`
- ستون خروجی: فقط `product_id`.
- شرط‌ها:
  - `product_id` باید داخل آرایه‌ی `$product_ids` باشد:
    - یعنی فقط بازی‌هایی را در نظر می‌گیرد که از قبل به‌عنوان پروموشنال برای آن شهر تنظیم شده‌اند.
  - `tags_id[~]` با مقدار `intval($data->tag_id)`:
    - در Medoo، `[~]` یعنی `LIKE` در SQL.
    - پس این شرط تبدیل به:

      ```sql
      WHERE product_id IN (...)
        AND tags_id LIKE '%{tag_id}%'
      ```

    - فرض: فیلد `tags_id` در `products_data` یک رشته است که لیست idهای تگ‌ها را (مثلاً جدا شده با `,`) نگه می‌دارد.

خروجی `$results`:

- آرایه‌ای از ردیف‌ها به‌صورت:

```php
[
  ['product_id' => 101],
  ['product_id' => 203],
  ...
]
```

یعنی بازی‌های پروموشنالی که آن `tag_id` خاص را هم دارند.

### ۵. خروجی JSON

بعد از کوئری:

```php
echo json_encode(['product_ids' => $results]);
```

- اگر `$results` خالی باشد، خروجی `product_ids: []` خواهد بود.
- اگر قبلاً در هیچ یک از مراحل بالا داده‌ای نبوده (مثلاً:
  - city_promotional_name در `wp_options` وجود نداشته،
  - یا `game_value["products"]` خالی بوده،
  - یا `product_ids` خالی/غیرآرایه بوده)،
  - در هر یک از این حالات خروجی زیر داده می‌شود:

```php
echo json_encode(['product_ids' => []]);
```

---

## محل‌های استفاده (usage) در پروژه

- با جستجو در کل کد (`game-promotional.php`) هیچ ارجاع مستقیمی پیدا نشد:
  - یعنی فعلاً:
    - **یا این وب‌سرویس رزرو/آماده برای استفاده‌ی آینده است**،
    - یا از خارج از این ریپازیتوری (مثلاً اپ موبایل، اسکریپت خارجی، یا URL مستقیم) به آن فراخوانی می‌شود.
- اما بر اساس الگوی سایر وب‌سرویس‌ها:
  - آدرس آن احتمالاً به صورت:
    - `https://escapezoom.ir/web-service/game-promotional.php`
  - یا در محیط لوکال:
    - `http://dev.escapezoom.local/web-service/game-promotional.php`
  - استفاده می‌شود.
- برای استفاده معمولاً کافی است یک درخواست POST با بدنه‌ی مثلاٌ:

```json
{
  "slug": "tehran",
  "tag_id": 15
}
```

به این آدرس ارسال شود تا لیست بازی‌های پروموشنالی شهر تهران که تگ ۱۵ دارند، برگردد.

---

## جمع‌بندی فنی

- **هدف**: برگرداندن لیست `product_id` بازی‌هایی که:
  - برای یک شهر خاص (`slug`) به‌عنوان «پروموشنال» تنظیم شده‌اند، و
  - دارای تگ مشخصی (`tag_id`) هستند.
- **وابستگی‌ها**:
  - `md-connect.php` که:
    - اتصال Medoo به دیتابیس وردپرس (`ez_database`) و دیتابیس محصولات (`qr_database`) را فراهم می‌کند.
    - احتمالاً اتصال `$conn` برای ثبت لاگ در جدول `hackers` را هم ایجاد می‌کند.
  - جدول‌های:
    - `wp_options` (کلید `promotional_products_{slug}` و مقدار serialize شده شامل آرایه‌ی `products`).
    - `products_data` (ستون‌های `product_id` و `tags_id`).
    - `hackers` برای لاگ درخواست‌های هاست غیرمجاز.
- **ورودی**:
  - POST JSON/form با:
    - `slug` (شهر)
    - `tag_id` (شناسه‌ی تگ، عددی)
- **خروجی**:
  - JSON با فرم:

    ```json
    {
      "product_ids": [
        { "product_id": 101 },
        { "product_id": 230 }
      ]
    }
    ```

  - یا:

    ```json
    { "product_ids": [] }
    ```

  - در صورت عدم وجود داده‌ی مناسب.

این مستند، رفتار `game-promotional.php` را از نظر ورودی، منطق سمت دیتابیس، فیلترینگ پروموشن و تگ، و خروجی نهایی به‌طور کامل توضیح می‌دهد؛ در صورت نیاز می‌توانیم براساسش refactor یا تست‌های مشخص هم طراحی کنیم.

