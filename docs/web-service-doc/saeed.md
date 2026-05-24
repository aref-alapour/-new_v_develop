## خلاصه‌ی کلی `saeed.php`

این فایل از نظر ساختار و سناریوها بسیار شبیه `reservation.php` است و **هسته‌ی مدیریت سانس/رزرو** برای یک کانال دیگر (احتمالاً اپ/کلاینت خاص) محسوب می‌شود، با این تفاوت‌های مهم:

- در ابتدای فایل یک **مکانیزم ضد اسپم/Rate Limit** مبتنی بر کوکی رمزنگاری‌شده (`sbjs_mindest`) دارد.
- خروجی `get_sanses` در این فایل به‌صورت **رمزنگاری‌شده** (`encrypt_data` با AES-128-CBC و hex) برمی‌گردد.
- در بخش `get_sanses` یک **Rate Limiting بر اساس IP و product_id** روی جدول `ip_activity` پیاده شده است.
- بقیه‌ی سناریوها (`display`, `hazf_kon`, `baz_kon`, `exchange_sans`, `remove_sans`, قفل سانس، `sans_management`, `sans_management_web`, `open_sans`, `close_sans`, `close_all_sanses`, `get_pending_sanses`) تقریباً همان‌هایی هستند که در `reservation.php` دیدی، با تغییرات جزئی.

به‌طور خلاصه: `saeed.php` نسخه‌ی **سخت‌گیرتر و امن‌تر** از سرویس رزرو است که هم ورودی را محدود می‌کند، هم خروجی حساس را رمز می‌کند.

---

## لایه‌ی امنیت ورودی (Cookie + Host + Method)

### ۱. محدودیت دامنه

- مانند سایر وب‌سرویس‌ها:
  - فقط روی دامنه‌های شناخته‌شده‌ی EscapeZoom پاسخ می‌دهد.
  - در غیر این صورت، `host` و `referer` را در جدول `hackers` ثبت و با متن `Get outta here` خارج می‌شود.

### ۲. ضد اسپم با کوکی `sbjs_mindest`

بلافاصله بعد از includeها:

```php
if (isset($_COOKIE['sbjs_mindest'])) {
    $token  = $_COOKIE['sbjs_mindest'];
    $secret = 'fe5#A378fc1792!ff15e5';
    $expiry_time = decrypt_data($token, $secret);
    if ($expiry_time < time())
        die('Too much requests');
} else {
    die('Too much requests');
}
```

- کوکی `sbjs_mindest` یک رشته‌ی hex است که شامل IV و ciphertext است.
- تابع `decrypt_data`:
  - `hex2bin` → جدا کردن IV و متن رمز،
  - `openssl_decrypt` با `AES-128-CBC` و `OPENSSL_RAW_DATA`,
  - خروجی: `expiry_time` (timestamp).
- اگر:
  - کوکی نباشد،
  - یا `expiry_time < time()` → درخواست با پیام `Too much requests` رد می‌شود.

این یعنی قبل از رسیدن به هر `type`، کلاینت باید قبلاً یک توکن معتبر گرفته باشد که هنوز منقضی نشده.

### ۳. محدودیت متد و Content-Type

- فقط POST:
  - در غیر این صورت:
    - `405` و JSON `"Invalid Request Method"`.
- در POST:
  - `application/json` → `php://input` را `json_decode` می‌کند.
  - `application/x-www-form-urlencoded` → `$_POST` را تبدیل می‌کند.
  - سایر `CONTENT_TYPE`ها → `415` و `"Unsupported Media Type"`.

---

## اشتراک سناریوها با `reservation.php`

بعد از لایه‌ی مشترک (CORS، زمان، includeها)، اکثر سناریوهای زیر **عیناً** همان چیزهایی هستند که در `reservation.php` تحلیل شدند:

- `close_all_sanses_of_all_products`: اسکریپت بستن همه سانس‌های یک لیست بزرگ از محصولات در دو روز خاص.
- `query_execution`: اجرای کوئری SQL دلخواه.
- `display`: رندر HTML سانس‌ها برای اپراتور/کاربر، با محاسبه‌ی تخفیف و auto_disable.
- `hazf_kon` / `baz_kon`: بستن/باز کردن سانس تکی در UI قدیمی.
- `exchange_sans_select_hour` / `exchange_sans`: جابجایی سانس رزرو شده و اطلاع‌رسانی (SMS + پیام).
- `remove_sans`: آزادکردن سانس و ارسال پیام به مالک/مدیر.
- `add_sans_lock` / `remove_sans_lock` / `get_sans_lock`: ثبت و حذف قفل‌های جدول `booking_lock_schedule` (ولی `get_sans_lock` فعلاً همیشه `[]` برمی‌گرداند).
- `create_purchase_url`: ساخت HTML + JS برای مرحله‌ی پیش‌پرداخت و لینک `/checkout`.
- `update_product_sub_data`: آپدیت `schedule` و `pish_person`.
- `panel_sanses_display`: نمایش سانس‌ها در پنل مخصوص نقش‌های `compiler`/`sans_manager`.
- `sans_management`, `sans_management_web`, `open_sans`, `close_sans`, `close_all_sanses`, `get_pending_sanses`: APIهای مدیریت سانس مثل `reservation.php`.

برای این سناریوها می‌توان به مستند `reservation.md` مراجعه کرد؛ در ادامه فقط روی بخش‌های **ویژه‌ی `saeed.php`** تمرکز می‌کنم: Rate Limit IP و `get_sanses` رمزنگاری‌شده.

---

## Rate Limit بر اساس IP در `get_sanses`

در این فایل، `get_sanses` نسبت به نسخه‌ی `reservation.php` دو تفاوت کلیدی دارد:

1. استفاده از `ip_activity` برای Rate Limit.
2. رمزنگاری خروجی قبل از ارسال.

### ورودی `get_sanses`

```json
{
  "type": "get_sanses",
  "data": {
    "day_start_time": <timestamp>,
    "product_id": <int>,
    "days": <int, optional>,
    "ip": "<client_ip>"
  }
}
```

- `product_id` به `(int)` cast می‌شود.
- `days` پیش‌فرض ۱.
- `ip` را از client می‌گیرد و با `real_escape_string` برای استفاده در SQL امن می‌کند.

### منطق `ip_activity`

```php
$ip   = $conn->real_escape_string($args->ip);
$time = microtime(true);
$request_limit = 2;
$time_window   = 10.0;

$result = $conn->query("SELECT ip, product_id, last_time, blocked, request_count FROM ip_activity WHERE ip = '{$ip}' LIMIT 1");
```

اگر رکورد برای این IP وجود داشته باشد:

- `prevProduct`, `prevTime`, `isBlocked`, `request_count` را می‌گیرد.
- اگر `blocked == 1`:
  - `403 Forbidden` و `exit`.
- اگر `prevProduct !== product_id` و زمان بین دو درخواست (`time - prevTime`) کمتر از `time_window` (۱۰ ثانیه) باشد:
  - `request_count++`.
  - اگر `request_count >= request_limit` (۲):
    - `blocked = 1`, `request_count = 0` در DB،
    - `429 Too Many Requests` و `exit`.
  - وگرنه فقط `product_id`, `last_time`, `request_count` آپدیت می‌شود.
- در غیر این حالت:
  - اگر محصول عوض شده باشد → `request_count = 1`،
  - در هر صورت رکورد با product_id و last_time جدید آپدیت می‌شود.

اگر رکوردی برای این IP نباشد:

```php
INSERT INTO ip_activity (ip, product_id, last_time, blocked, request_count)
VALUES (ip, product_id, time, 0, 1)
```

**نتیجه**:

- در بازه‌ی ۱۰ ثانیه، اگر یک IP به بیش از ۲ **محصول متفاوت** درخواست `get_sanses` بفرستد، بلاک می‌شود.
- درخواست‌های تکراری برای یک محصول همان request_count را دست‌نخورده نگه می‌گذارند.

این Rate Limit، در کنار کوکی `sbjs_mindest`, لایه‌ی حفاظتی قوی‌تری روی `get_sanses` ایجاد می‌کند.

---

## `get_sanses` و رمزنگاری خروجی

پس از عبور از Rate Limit، بقیه‌ی منطق `get_sanses` همان الگوی `reservation.php` را دنبال می‌کند:

1. خواندن `products_data` برای `product_id`.
2. محاسبه‌ی تخفیف کلی (`discount_data` اگر فعال باشد).
3. محاسبه‌ی `auto_disable` (بر اساس `auto_disable` در `products_data`).
4. خواندن قفل‌های سانس (`get_sans_lock`) و پاک‌سازی قفل‌های منقضی (اگر `get_sans_lock` درست کار کند).
5. ساخت آرایه‌ی `days_time_arr` برای `days` روز آینده.
6. برای هر روز:
   - `day_type = get_day_type(time_res); sanses = get_sanses(product_id);`.
   - `sanses_list` = لیست timestamp شروع همه سانس‌ها.
   - اگر خالی بود، روی کل `wp_zb_booking_history` برای محصول کوئری می‌زند؛ در غیر این صورت، فقط روی `booking_time IN (list)`.
   - `order_objs` = map از `booking_time` به `status`.
7. برای هر سانس:
   - `status` = `reserved` / `non_reservable` / `reserving` / `reservable` بسته به:
     - `order_obj['status']`,
     - و این‌که در `bookings` هست یا نه.
   - `discount_final` به صورت زیر محاسبه می‌شود:

```php
$discount_final = (int)$sans['off_price']
    ? $sans['off_price'] * (1 - $discount / 100)
    : $sans['price'] * (1 - $discount / 100);

$reservation_data[$key][] = [
    'time'      => $firstTimeTs,
    'price'     => (int) $sans['price'],
    'off_price' => $discount_final != $sans['price'] ? (int) $discount_final : 0,
    'status'    => $status,
];
```

8. اگر فقط یک روز باشد، آرایه را flatten می‌کند (برای راحتی مصرف API).
9. اگر هیچ سانسی نبود، خروجی را به `[]` تنظیم می‌کند.

### رمزنگاری پاسخ

تفاوت کلیدی با `reservation.php` در این خط است:

```php
echo json_encode(encrypt_data(json_encode($reservation_data_final), '77v60cdKbe1ZAv8V'));
// echo json_encode($reservation_data_final);
```

- تابع `encrypt_data`:

```php
function encrypt_data($plaintext, $key) {
    $ivlen           = openssl_cipher_iv_length($cipher="AES-128-CBC");
    $iv              = openssl_random_pseudo_bytes($ivlen);
    $ciphertext_raw  = openssl_encrypt($plaintext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
    return bin2hex($iv . $ciphertext_raw);
}
```

- **ورودی این تابع**: JSONِ متن خام `reservation_data_final`.
- **خروجی**:
  - رشته‌ی hex شامل IV و ciphertext (`AES-128-CBC`).
- سپس این رشته‌ی hex با `json_encode` بسته‌بندی می‌شود (در حقیقت تبدیل به `"\"<hex-string>\""` می‌شود).

برای مصرف:

- کلاینت باید:
  - JSON response را بخواند → رشته‌ی hex را دریافت کند،
  - آن را با کلید `77v60cdKbe1ZAv8V` و همان الگوریتم (`AES-128-CBC`) و تابعی مشابه `decrypt_data` سمت خودش **رمزگشایی** کند تا به JSON sanseها برسد.

این مکانیزم برای جلوگیری از **sniffing ساده‌ی ساختار و وضعیت سانس‌ها** روی wire یا جلوگیری از misuse مستقیم endpoint است.

---

## تفاوت‌های ظریف با `reservation.php`

- **Rate Limit IP + کوکی**: تنها در این فایل وجود دارد.
- **رمزنگاری خروجی `get_sanses`**: در `reservation.php` خام JSON برمی‌گردد، در `saeed.php` رمزنگاری می‌شود.
- **ساختار باقی سناریوها**:
  - تقریباً خط‌به‌خط مشابه `reservation.php` است (حتی نام توابع view، نحوه‌ی ارسال SMS، تماس با `impec.ir`).
  - در صورت نیاز به درک عمیق هر سناریو، می‌توان به `reservation.md` مراجعه کرد؛ رفتار اینجا همان است، فقط اضافه‌شدن لایه‌ی امنیت و رمزنگاری را باید در نظر گرفت.

---

## جمع‌بندی

- `saeed.php` را می‌توان به‌عنوان **نسخه‌ی امن/کلاینت-محور** از منطق رزرو دانست:
  - تمام عملیات مدیریتی سانس (بستن/بازکردن/جابجایی/لیست‌گرفتن/تخفیف/ساخت لینک خرید) را مثل `reservation.php` ارائه می‌دهد.
  - اما:
    - قبل از هر چیز، با کوکی رمزنگاری‌شده (`sbjs_mindest`) و جدول `ip_activity` درخواست‌ها را کنترل و محدود می‌کند.
    - خروجی حساس `get_sanses` را رمز می‌کند تا فقط کلاینت‌هایی که کلید را دارند بتوانند داده‌ی خام را ببینند.
- سیستم قفل `booking_lock_schedule` در این فایل هم مثل `reservation.php` به‌خاطر `return []` در `get_sans_lock` عملاً غیرفعال است؛ اگر بخواهی واقعاً concurrency را مدیریت کنی، باید این تابع را اصلاح کنی.  
- این مستند در کنار `reservation.md`، تصویری جامع از تمام نقاط ورود مرتبط با سانس و رزرو در وب‌سرویس‌های EscapeZoom می‌دهد و برای هر refactor امنیتی/معماری بعدی ضروری است.

