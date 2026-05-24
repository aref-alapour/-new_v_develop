## خلاصه‌ی کلی `helper-functions.php`

این فایل مجموعه‌ای از **توابع کمکی مشترک** برای وب‌سرویس‌های EscapeZoom است؛ مخصوصاً برای:

- ارسال پیامک (دو روش مختلف، SOAP و REST).
- تشخیص نوع روز (تعطیل، بسته، عادی) براساس جدول `calendar_data`.
- گرفتن سانس‌های یک بازی از جدول `products_data`.
- لاگ‌کردن داده‌ها در دیتابیس یا فایل.
- محاسبات کمکی روی مختصات (بررسی داخل بودن نقطه در باکس جغرافیایی).
- تبدیل URLها و معادل‌سازی نوع محصول بین کلید انگلیسی و عنوان فارسی.

این توابع در وب‌سرویس‌هایی مثل `sans_management.php`, `reservation.php`, و سایر اسکریپت‌های تحت `web-service` استفاده می‌شوند تا منطق تکراری در یک نقطه متمرکز شود.

---

## توابع ارسال پیامک

### ۱. تابع `ez_sendpayamak2($phone___number, $msg__text, $number = "2191307900")`

**کاربرد**: ارسال پیامک به یک شماره، با استفاده از وب‌سرویس SOAP (`payamak-panel.com`).

**رفتار**:

- کش WSDL SOAP را غیرفعال می‌کند:

```php
ini_set("soap.wsdl_cache_enabled", "0");
```

- یک `SoapClient` روی آدرس:
  - `http://api.payamak-panel.com/post/send.asmx?wsdl`
  - با تنظیم `encoding => 'UTF-8'` می‌سازد.

- پارامترهای لازم برای ارسال SMS را تنظیم می‌کند:
  - `username = "xescape"`
  - `password = "2kkh7Gm36%#X91h"`
  - `from = $number` (پیش‌فرض: `2191307900`)
  - `to = array("$phone___number")` (لیست شماره‌ها)
  - `text = $msg__text`
  - سایر فیلدها: `isflash`, `udh`, `recId`, `status`.

- سپس:

```php
$status = $client->GetCredit([...])->GetCreditResult;
$status .= $client->SendSms($parameters)->SendSmsResult;
return $status;
```

- در صورت استثنای SOAP (`SoapFault`):
  - `faultstring` را در `$status_err` می‌گذارد،
  - اما در نهایت `return $status;` را انجام می‌دهد که در بلوک try تعریف شده؛ این یک ایراد جزئی است (در خطای واقعی ممکن است `$status` مقدار قبلی داشته باشد).

**نکات مهم**:

- **اطلاعات کاربری (username/password) در کد هاردکد شده‌اند**؛ باید در سطح امنیتی/پیکربندی به آن توجه شود.
- نداشتن هندلینگ خطای شفاف برای caller (فقط یک رشته‌ی status برمی‌گرداند).

---

### ۲. تابع `ez_sendpayamak($phone___number, $msg__text, $number = "2191307900")`

**کاربرد**: ارسال پیامک با استفاده از API REST همان سرویس پیامک (`SendSMS`)، با cURL.

**رفتار**:

- یک cURL session می‌سازد و تنظیمات زیر را اعمال می‌کند:

```php
CURLOPT_URL            => "http://api.payamak-panel.com:4520/rest/api/SendSMS/SendSMS",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_CUSTOMREQUEST  => "POST",
CURLOPT_POSTFIELDS     => "username=xescape&password=2kkh7Gm36%#X91h&to=$phone___number&from=$number&text=$msg__text&isflash=false",
CURLOPT_HTTPHEADER     => ["content-type: application/x-www-form-urlencoded"],
```

- درخواست را اجرا می‌کند:
  - `$response = curl_exec($curl);`
  - `$err = curl_error($curl);`
  - سپس `curl_close($curl);`

- اگر خطای cURL رخ دهد:
  - با `echo "cURL Error #:" . $err;` در خروجی چاپ می‌کند (به‌جای برگرداندن خطا به caller).
- در صورت عدم خطا:
  - `// echo $response;` کامنت شده است، یعنی عملاً چیزی به caller برنمی‌گرداند.

**نکات**:

- این تابع ظاهراً برای ارسال fire-and-forget پیامک استفاده می‌شود.
- مثل تابع قبلی، username/password در کد هاردکد هستند.

---

## توابع مرتبط با تقویم و سانس‌ها

### ۳. تابع `get_day_type2($day)`

**کاربرد**: نسخه‌ی قدیمی‌تر/جایگزین `get_day_type` برای تشخیص نوع روز (تعطیل/بسته/عادی) بر اساس جدول `calendar_data`.

**ورودی**:

- `$day`: تایم‌استمپ (احتمالاً ثانیه‌ای) که قرار است نوع روز آن مشخص شود.

**منطق**:

- از جدول `calendar_data`، ستون `data` را می‌خواند:

```php
$result = $conn->query("SELECT data FROM `calendar_data`");
$calendar_data = $result->fetch_all(MYSQLI_ASSOC);
$calendar_data = json_decode(json_encode(unserialize($calendar_data[0]['data'])), true);
```

- دو لیست از `calendar_data` به‌دست می‌آید:
  - `holidays` → رشته‌ای از تایم‌استمپ‌ها (جداشده با `,`) که روزهای تعطیل هستند.
  - `closed_days` → رشته‌ای مشابه برای روزهای بسته.

- برای هر مقدار در `holidays`:
  - آن را به `int` تبدیل و **۱۲,۶۰۰ ثانیه (۳:۳۰ ساعت)** کم می‌کند تا از GMT+3:30 به زمان محلی برگردد.
  - اگر `day` بین `[calendar_day, calendar_day + 86399]` باشد:
    - یعنی در همان روز تقویمی قرار می‌گیرد → `return 'holidays';`

- اگر در تعطیلات نبود، مشابه همین منطق روی `closed_days` اعمال می‌شود:
  - در صورت تطابق → `return 'closed';`

- در غیر این صورت → `return 'normals';`

**نکته**:

- این نسخه از تابع با بازه‌ی روز (۰ تا ۸۶,۳۹۹ ثانیه) کار می‌کند و انعطاف بیشتری برای تشخیص «هر زمانی در آن روز» دارد؛ ولی در پروژه، نسخه‌ی جدیدتر (`get_day_type`) بیشتر استفاده می‌شود.

---

### ۴. تابع `get_day_type($day)`

**کاربرد**: نسخه‌ی اصلی/فعلی تشخیص نوع روز که در وب‌سرویس‌هایی مثل `sans_management.php` استفاده می‌شود.

**ورودی**:

- `$day`: تایم‌استمپ (ثانیه‌ای) که قرار است نوع روز آن مشخص شود.

**منطق**:

- ابتدا `day` را با ۱۲,۶۰۰ ثانیه (۳:۳۰ ساعت) **جابه‌جا** می‌کند:

```php
$day += 12600;
```

- از جدول `calendar_data`، ستون `data` را می‌خواند و `unserialize` می‌کند:

```php
$result = $conn->query("SELECT data FROM `calendar_data`");
$calendar_data = $result->fetch_all(MYSQLI_ASSOC);
$calendar_data = unserialize($calendar_data[0]['data']);
```

- سپس:
  - اگر `day` داخل آرایه‌ی `holidays` (که با `explode(',', $calendar_data->holidays)` ساخته شده) باشد:
    - `return 'holidays';`
  - اگر `day` داخل آرایه‌ی `closed_days` باشد:
    - `return 'closed';`
  - در غیر این صورت:
    - `return 'normals';`

**تفاوت با `get_day_type2`**:

- به‌جای کار با بازه‌ی ۲۴ ساعته، مستقیماً با **تایم‌استمپ دقیق** تطبیق می‌دهد:
  - یعنی `calendar_data->holidays` خودش باید شامل تایم‌استمپ‌های با شیفت اعمال‌شده باشد.
- در کدهای جدید مثل `sans_management.php` از همین نسخه استفاده می‌شود.

---

### ۵. تابع `get_sanses($product_id)`

**کاربرد**: گرفتن لیست سانس‌های یک محصول (اتاق/بازی) از جدول `products_data`.

**ورودی**:

- `product_id`: شناسه‌ی بازی/اتاق.

**منطق**:

- از جدول `products_data` رکوردی با `product_id` خواسته‌شده را می‌خواند:

```php
$result = $conn->query(sprintf("SELECT * FROM products_data WHERE product_id LIKE %s", $product_id));
$product_obj = $result->fetch_all(MYSQLI_ASSOC)[0];
```

- فیلد `schedule` این رکورد را `unserialize` می‌کند:

```php
$sanses = unserialize($product_obj['schedule']);
```

- سپس با یک رفت‌وبرگشت `json_encode` / `json_decode` آن را به آرایه‌ی associative (نه object) تبدیل می‌کند:

```php
return json_decode(json_encode($sanses), true);
```

**خروجی**:

- آرایه‌ای چندسطحی که معمولاً ساختاری نزدیک به این دارد:

```php
[
  'holidays' => [
    ['time' => '18:00', 'price' => 100000, 'off_price' => 90000, ...],
    ...
  ],
  'normals' => [...],
  'closed'  => [...],
]
```

- این ساختار در وب‌سرویس‌هایی مانند `sans_management.php` استفاده می‌شود تا برای هر روز، لیست سانس‌ها را بدست آورد.

---

### ۶. تابع `logintotag($thingtolog)`

**کاربرد**: لاگ‌کردن هر داده‌ای در جدول `tags`، احتمالاً برای دیباگ یا ذخیره‌ی موقتی اطلاعات.

**منطق**:

```php
$conn->query(sprintf(
    "INSERT INTO tags (tag_id,tag_title,products) VALUES ('%s', '%s', '%s')",
    1, 1, serialize($thingtolog)
));
```

- همیشه با `tag_id = 1` و `tag_title = 1`، و فیلد `products` = `serialize($thingtolog)` یک رکورد ثبت می‌کند.
- یعنی به‌نظر می‌رسد بیشتر برای **دیباگ** استفاده شده باشد تا منطق محصولی.

---

### ۷. تابع `logintofile($log)`

**کاربرد**: لاگ‌کردن هر داده‌ای در یک فایل `log.log` در همان مسیر اسکریپت.

**منطق**:

```php
file_put_contents(
    'log.log',
    "[" . date("Y-m-d H:i:s") . "] " . print_r($log, true) . PHP_EOL,
    FILE_APPEND
);
```

- یک خط شامل:
  - تاریخ/ساعت فعلی،
  - خروجی `print_r` از `$log`,
  - و یک newline،
  - به انتهای فایل `log.log` اضافه می‌کند.

---

### ۸. تابع `saeed_print($val)`

**کاربرد**: ابزاری ساده برای دیباگ در خروجی HTML.

**منطق**:

```php
echo '<pre>'; print_r($val); echo '</pre>';
```

- خروجی را با تگ `<pre>` قالب‌بندی می‌کند تا ساختار آرایه/آبجکت خواناتر شود.

---

### ۹. تابع `is_point_within_bounds($point, $bounds)`

**کاربرد**: تشخیص این‌که یک نقطه‌ی جغرافیایی (lat,lng) داخل یک باکس (مستطیل) مشخص قرار دارد یا نه؛ احتمالاً برای فیلترکردن بازی‌ها یا شعب بر اساس محدوده‌ی نقشه.

**ورودی‌ها**:

- `$point`: رشته‌ای مثل `"35.7,51.4"` (lat,lng).
- `$bounds`: آبجکت/stdClass با ساختار:

```php
$bounds = (object)[
  'sw' => (object)['lat' => ..., 'lng' => ...], // جنوب‌غربی
  'ne' => (object)['lat' => ..., 'lng' => ...], // شمال‌شرقی
];
```

**منطق**:

- `point` را با `explode(',', $point)` به دو بخش تبدیل می‌کند و lat/lng را استخراج می‌کند.
- حداقل و حداکثر lat/lng را از بین `sw` و `ne` حساب می‌کند:

```php
$minLat = min($bounds->sw->lat, $bounds->ne->lat);
$maxLat = max($bounds->sw->lat, $bounds->ne->lat);
$minLng = min($bounds->sw->lng, $bounds->ne->lng);
$maxLng = max($bounds->sw->lng, $bounds->ne->lng);
```

- اگر:

```php
$lat >= $minLat && $lat <= $maxLat &&
$lng >= $minLng && $lng <= $maxLng
```

- آنگاه `true` برمی‌گرداند (نقطه داخل محدوده است)، در غیر این صورت `false`.

---

### ۱۰. تابع `trim_home_url($url)`

**کاربرد**: حذف `home_url()` از ابتدای یک URL کامل، برای گرفتن مسیر نسبی.

**منطق**:

```php
return str_replace(home_url(), '', $url);
```

- اگر مثلاً:
  - `home_url() = 'https://escapezoom.ir'`
  - و `$url = 'https://escapezoom.ir/room/some-game/'`
  - خروجی: `'/room/some-game/'`

- این تابع معمولاً در جاهایی استفاده می‌شود که نیاز داریم از URL کامل، فقط مسیر داخلی سایت را داشته باشیم.

---

### ۱۱. تابع `get_product_type_equivalent($product_type)`

**کاربرد**: نگاشت بین **کد نوع محصول** (کلید انگلیسی) و **عنوان فارسی** آن، و برعکس.

**دیکشنری داخلی**:

```php
$product_types = [
    'escaperoom'     => 'اتاق فرار',
    'cafegame'       => 'کافه بازی',
    'cinema'         => 'سینما ترس',
    'rageroom'       => 'اتاق خشم',
    'lasertag'       => 'لیزرتگ',
    'bubblefootball' => 'فوتبال حبابی',
    'paintball'      => 'پینت بال',
    'haunted_house'  => 'هانتد هاوس',
];
```

**منطق**:

- اگر `$product_type` یکی از کلیدهای آرایه باشد (انگلیسی):

```php
if (array_key_exists($product_type, $product_types))
    return $product_types[$product_type];
```

  - خروجی: معادل فارسی (مثلاً `'escaperoom'` → `'اتاق فرار'`).

- در غیر این صورت:

```php
return array_search($product_type, $product_types);
```

  - یعنی اگر ورودی فارسی باشد (مثلاً `'اتاق فرار'`)، دنبال **کلید متناظر** در آرایه می‌گردد و آن را برمی‌گرداند (`'escaperoom'`).

**خروجی**:

- یا عنوان فارسی نوع محصول (برای نمایش در UI)،
- یا کلید انگلیسی (برای ذخیره در دیتابیس/فیلترها)،
- یا `false` (اگر مقدار ورودی در هیچ‌کدام از دو سمت نگاشت پیدا نشود).

---

## جمع‌بندی

- این فایل مجموعه‌ای از **ابزارهای افقی** برای بقیه‌ی وب‌سرویس‌هاست:
  - ارسال SMS، تشخیص روز تعطیل/بسته، گرفتن سانس‌ها، نگاشت نوع محصول، و لاگ‌کردن.
- توابع کلیدی که در منطق رزرو و سانس‌ها نقش اساسی دارند:
  - `get_day_type` و `get_sanses` هستند که مستقیماً روی جدول‌های `calendar_data` و `products_data` کار می‌کنند.
- سایر توابع (`logintofile`, `saeed_print`, `is_point_within_bounds`, ...) بیشتر نقش **کمکی/دیباگ** یا ابزار برای قابلیت‌های فرعی (مثل فیلتر مکانی) دارند.
- این مستند می‌تواند مبنای refactor (جداکردن توابع مرتبط، حذف کدهای قدیمی مثل `get_day_type2`، یا امن‌سازی اطلاعات حساس) در آینده باشد.

