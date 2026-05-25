## خلاصه‌ی کلی `queryable.php`

این فایل یک **وب‌سرویس جستجوی بازی** است که بر اساس عبارت جستجو (`term`) لیست بازی‌ها را از جدول `products_data` پیدا می‌کند و بسته به نوع منبع (`source`) خروجی را به شکل‌های مختلف (JSON برای API یا HTML برای سئو/سایت اصلی) برمی‌گرداند.  
به‌طور خلاصه:

- ورودی را از طریق **POST** (JSON یا `application/x-www-form-urlencoded`) می‌گیرد.
- روی جدول `products_data` جستجو می‌کند تا بازی‌هایی که `title`شان حاوی `term` است را برگرداند.
- اگر عبارت دو بخشی باشد (دو کلمه)، نتایج هر کلمه را جداگانه می‌گیرد و سپس اشتراک آن‌ها را محاسبه می‌کند تا دقت جستجو بالاتر شود.
- بر اساس فیلد `source` در ورودی، خروجی را به صورت:
  - لیست بازی‌ها برای دعوت‌نامه (`invitation`),
  - لیست بازی‌های فیلترشده بر اساس نوع محصول برای کالکشن (`collection`),
  - لیست برای سرچ هدر صفحه‌ی اصلی (`home_header_search`),
  - یا HTML رندرشده برای وب‌سایت،
برمی‌گرداند.

این وب‌سرویس نقطه‌ی مرکزی جستجوی محصولات/اتاق‌ها در سایت و اپ است.

---

## ساختار عمومی و امنیت

- **CORS**:
  - برای همه‌ی originها باز است (`Access-Control-Allow-Origin: *`) و هدرها/متدهای لازم برای AJAX تنظیم شده‌اند.
  - درخواست‌های `OPTIONS` با کد 204 پاسخ داده شده و متوقف می‌شوند (preflight).

- **خطا و timezone**:
  - `error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);`

- **کنترل دامنه‌ی مجاز**:

```php
if (! (HTTP_HOST در بین escapezoom.ir, escapezoom.co, bak.escapezoom.ir, dev-api.escapezoom.ir, zoom.escapezoom.ir, w2.razriazi.ir, dev.escapezoom.local)) {
    INSERT INTO hackers (host, referer) ...
    die('Get outta here');
}
```

- **نوع متد و Content-Type**:
  - فقط متد **POST** مجاز است؛ در غیر این صورت:
    - `405` و JSON `{ "error": "Invalid Request Method" }`.
  - برای POST:
    - اگر `CONTENT_TYPE` شامل `application/json` باشد:
      - بدنه‌ی `php://input` را `json_decode` می‌کند.
    - اگر `application/x-www-form-urlencoded` باشد:
      - `$_POST` را به JSON و بعد object تبدیل می‌کند.
    - در غیر این دو حالت:
      - `415` و `{ "error": "Unsupported Media Type" }`.

- بعد از پارس ورودی `session_start()` هم انجام می‌شود (برای سناریوهای دیگر که احتمالاً از سشن استفاده می‌کنند، هرچند در این فایل مستقیماً از آن استفاده نشده است).

---

## ورودی اصلی و محاسبه‌ی `home_url`

پس از اطمینان از وجود `$data`:

```php
require 'db-connect.php';
$term = $data->term;
```

- `term`: عبارت جستجو، مثلاً نام بازی یا بخشی از آن.

تنظیم `home_url`:

- اگر هاست `dev.escapezoom.local` باشد:
  - `home_url = 'http://dev.escapezoom.local'`.
- در غیر این صورت:
  - اگر `$data->url` ست شده باشد از آن استفاده می‌کند، وگرنه `escapezoom.ir`:

```php
$url = $data->url ?? 'escapezoom.ir';
if ($url == 'dev.escapezoom.local') {
    $home_url = 'http://dev.escapezoom.local';
} else {
    $home_url = 'https://' . $url;
}
```

این به سرویس اجازه می‌دهد برای دامنه‌های مختلف (مثلاً ساب‌دامین‌ها یا سایت‌های آینه) هم پاسخ بدهد.

---

## منطق جستجو بر اساس `term`

### حالت ۱: عبارت دوکلمه‌ای

اگر `term` شامل دقیقاً دو بخش باشد و بخش اول خالی نباشد:

```php
$term_parts = explode(' ', $term);
if (count($term_parts) == 2 && !empty($term_parts[0])) {
    $res1 = get_search_result_func($term_parts[0]);
    $res2 = get_search_result_func($term_parts[1]);
    ...
}
```

- `res1`: نتایج جستجو برای کلمه‌ی اول.
- `res2`: نتایج برای کلمه‌ی دوم.

سپس:

- `ids_arr1` و `ids_arr2`: آرایه‌ی `product_id`‌های هر دسته.
- `products_temp[product_id] = res` برای هر result تا بتوان از آن به‌عنوان map استفاده کرد.
- اگر بخش دوم (`term_parts[1]`) خالی نباشد:
  - روی **اشتراک** `ids_arr1` و `ids_arr2` حلقه می‌کند و فقط محصولاتی را برمی‌گرداند که هر دو کلمه در عنوانشان match شده‌اند.
- اگر بخش دوم خالی باشد:
  - کل `products_temp` استفاده می‌شود (یعنی unionی از دو جستجو).

### حالت ۲: سایر عبارات (یک‌کلمه‌ای یا بیشتر از دو بخش)

اگر شرط بالا برقرار نباشد:

```php
$products = get_search_result_func($term);
```

یعنی یک بار مستقیماً روی کل عبارت جستجو می‌کند.

---

## خروجی بر اساس `source`

اگر `$data->source` ست شده باشد، مسیر خروجی به سمت JSON API می‌رود؛ در غیر این صورت خروجی HTML است.

### ۱. `source = 'invitation'`

هدف: لیست بازی‌ها برای استفاده در سیستم دعوت‌نامه (احتمالاً برای ارسال پیامک/نوتیفیکیشن دعوت).

روی حداکثر ۳۰ نتیجه‌ی اول (`array_slice`) حلقه می‌زند:

```php
$api_data[] = [
  'product_id' => (int) $product['product_id'],
  'title'      => $product['title'],
  'image'      => $home_url . '/wp-content/uploads/' . unserialize($product['data'])['image'],
  'city'       => unserialize($product['data'])['city'],
];
```

- توجه: `product['data']` خودش در `get_search_result_func` به صورت `serialize(['city' => city_name, 'image' => image])` ذخیره شده است، اینجا `unserialize` می‌شود.

### ۲. `source = 'collection'`

هدف: جستجوی بازی‌ها برای ساخت یا فیلتر کالکشن‌ها، با محدود کردن به یک نوع محصول خاص (مثلاً فقط اتاق فرار).

- ابتدا `type` را از ورودی می‌گیرد:

```php
$type = $data->type;
```

- سپس با `array_filter` روی نتایج جستجو فقط محصولاتی را نگه می‌دارد که `product['type'] === $type`.
- روی ۳۰ نتیجه‌ی اول حلقه می‌زند و آرایه‌ای مشابه ولی بدون `city` می‌سازد:

```php
$api_data[] = [
  'product_id' => (int) $product['product_id'],
  'title'      => $product['title'],
  'image'      => $home_url . '/wp-content/uploads/' . unserialize($product['data'])['image'],
];
```

### ۳. `source = 'home_header_search'`

هدف: استفاده در سرچ هدر صفحه‌ی اصلی سایت (autocomplete/لیست نتایج).

- روی ۳۰ نتیجه‌ی اول حلقه می‌زند و ساختار زیر را برمی‌گرداند:

```php
$api_data[] = [
  'product_id' => (int) $product['product_id'],
  'title'      => $product['title'],
  'image'      => $home_url . '/wp-content/uploads/' . unserialize($product['data'])['image'],
  'city'       => unserialize($product['data'])['city'],
  'url'        => $product['url'],
];
```

- این خروجی برای ساخت لیست clickable نتایج (با تصویر، شهر، عنوان و URL نسبی) مناسب است.

### خروجی JSON و پایان

در هر سه حالت بالا:

```php
echo json_encode($api_data);
exit;
```

وب‌سرویس فقط داده‌ی خام JSON برمی‌گرداند.

---

## خروجی HTML (بدون `source`)

اگر `source` ست نشده باشد، سرویس به‌جای JSON آرایه‌ای، یک رشته‌ی HTML آماده برای inject در صفحه برمی‌گرداند:

```php
$result = '';
foreach (@array_slice((array)$products, 0, 30, true) as $product) {
    $data = unserialize($product['data']);

    if (!empty($data->url))
        $home_url = 'https://' . $data->url;

    $name  = $product['title'];
    $city  = $data['city'];
    $image = $home_url . '/wp-content/uploads/' . $data['image'];
    $url   = "$home_url/room/" . urlencode($product['url']) . "/";

    $result .= '<p><img class=ax-search src="' . $image . '" > <a href="' . $url . '">    <span style="color: #202020 !important;font-weight: bold;" >' . $name . '</span></a> </p>';
}
echo json_encode($result);
```

نکات:

- برای هر محصول یک `<p>` شامل:
  - تصویر (`img.ax-search`),
  - لینک به صفحه‌ی بازی (`/room/{url}/`),
  - و عنوان بازی داخل `<span>` bold ساخته می‌شود.
- در انتها کل HTML به‌عنوان **یک رشته‌ی JSON-encode شده** برمی‌گردد (یعنی فرانت‌اند بعداً آن را `JSON.parse` کرده و در DOM قرار می‌دهد).
- اگر داده‌ی سریال‌شده (`$data`) فیلد `url` خودش داشته باشد، `home_url` را از روی آن override می‌کند تا از دامنه‌ی اختصاصی بازی استفاده کند.

اگر `$data` اصلاً ست نشده باشد، اسکریپت در انتها با `die('Fuck off');` متوقف می‌شود (متن تند، ولی عملاً فقط برای درخواست‌های نامعتبر است).

---

## تابع جستجو: `get_search_result_func($term)`

این تابع هسته‌ی اصلی جستجو روی دیتابیس است.

**منطق**:

1. از جدول `products_data` **همه‌ی رکوردها** را می‌خواند:

```php
$result = $conn->query("SELECT * FROM products_data");
while ($row = $result->fetch_assoc())
    $products_data[$row['product_id']] = $row;
```

2. (کد قدیمی برای sort بر اساس محبوبیت از `products_order` کامنت شده و فعلاً استفاده نمی‌شود؛ در نتیجه ترتیب نتایج صرفاً بر اساس ترتیب fetch از دیتابیس است).

3. `sorted_product_list` را برابر با کل `products_data` می‌گیرد.

4. روی هر محصول:

```php
if (@strpos($product['title'], $term) !== false) {
    $temp['product_id'] = $product['product_id'];
    $temp['type']       = $product['product_type'];
    $temp['url']        = $product['url'];
    $temp['title']      = $product['title'];
    $temp['data']       = serialize([
        'city'  => $product['city_name'],
        'image' => $product['image'],
    ]);
    $products[] = $temp;
}
```

- فقط محصولاتی که `title` آن‌ها شامل `term` (به‌صورت substring، case-sensitive) باشد در نتیجه قرار می‌گیرند.
- از `@` استفاده شده تا اگر فیلدی وجود نداشت، خطا suppress شود (که البته می‌تواند باگ‌ها را مخفی کند).
- خروجی نهایی آرایه‌ای از محصول‌های match شده است که در لایه‌ی بالا (`queryable.php`) برای ساخت JSON/HTML استفاده می‌شود.

---

## تابع `logintotag($thingtolog)`

نقشی مشابه نسخه‌ای که در `helper-functions.php` دیده می‌شود دارد:

```php
INSERT INTO tags (tag_id, tag_title, products)
VALUES (1, 1, serialize($thingtolog));
```

- برای دیباگ/لاگ‌کردن داده‌ها در جدول `tags` استفاده می‌شود.

---

## جمع‌بندی

- `queryable.php` وب‌سرویس مرکزی جستجوی بازی‌ها در EscapeZoom است که:
  - عبارت ورودی کاربر را از چند مسیر (JSON، فرم، دامنه‌های مختلف) دریافت می‌کند،
  - روی عنوان بازی‌ها در `products_data` جستجو انجام می‌دهد،
  - و خروجی را بسته به سناریو (دعوت‌نامه، کالکشن، سرچ هدر، یا HTML معمولی سایت) آماده و برمی‌گرداند.
- تابع `get_search_result_func` منطق جستجو را کپسوله می‌کند و با بازگرداندن `product_id`, `type`, `url`, `title`, و داده‌ی سریال‌شده‌ی `city`/`image`، بقیه‌ی بخش‌ها را قادر می‌سازد ساختار خروجی دلخواه خود را بسازند.
- این فایل در کنار وب‌سرویس‌هایی مثل `game-promotional.php`, `game-suggested.php`, و `comments-order.php`، بخش مهمی از لایه‌ی API جستجو و پیشنهاد بازی در سیستم شما را تشکیل می‌دهد.

