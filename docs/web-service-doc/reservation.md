## خلاصه‌ی کلی `reservation.php`

این فایل، **هسته‌ی اصلی منطق سانس و رزرو** در وب‌سرویس EscapeZoom است و تقریباً تمام سناریوهای مدیریت سانس را پوشش می‌دهد؛ از نمایش سانس‌ها برای کاربر و اپراتور، تا بستن/بازکردن سانس‌ها، قفل موقت سانس در حین رزرو، جابجایی سانس رزرو شده، تولید لینک خرید، و APIهای مخصوص اپ.  
ورودی‌ها با یک پارامتر `type` و یک شیء `data` ارسال می‌شوند و هر مقدار `type` یک سناریوی جدا را فعال می‌کند.

سناریوهای مهم:

- `display` / `panel_sanses_display`: رندر HTML سانس‌ها (برای کاربر و اپراتور) در نسخه‌ی دسکتاپ/موبایل.
- `hazf_kon` / `baz_kon`: بستن و باز کردن سانس تکی در پنل قدیمی (HTML).
- `exchange_sans_select_hour` / `exchange_sans`: جابجایی سانس رزرو شده (انتخاب مقصد و ثبت + ارسال پیامک/پیام به مالک/مدیر).
- `remove_sans`: آزاد کردن سانس رزرو شده و اطلاع‌رسانی به مجموعه‌دار.
- `add_sans_lock`, `remove_sans_lock`, `get_sans_lock`: قفل موقت سانس در حین رزرو (هرچند در پیاده‌سازی فعلی `get_sans_lock` همیشه آرایه‌ی خالی برمی‌گرداند).
- `create_purchase_url`: ساخت فرم/لینک نهایی خرید (`/checkout` با `quantity`, `add-to-cart`, `book`).
- `update_product_sub_data`: به‌روزرسانی `schedule` و `pish_person` در `products_data`.
- `get_sanses`: API اصلی برای دریافت سانس‌ها (قیمت، تخفیف داغ، تخفیف آنی، وضعیت `reservable/reserved/...`) برای اپ/فرانت‌اند جدید.
- `sans_management` و `sans_management_web`: APIهای مدیریتی برای وضعیت سانس‌ها (مشابه چیزی که در `team/sans_management.php` و `saeed.php` هم دیده بودیم).
- `open_sans`, `close_sans`, `close_all_sanses`: باز/بستن سانس تکی یا همه‌ی سانس‌ها برای یک روز (نسخه‌ی app-friendly).
- `get_pending_sanses`: لیست سانس‌های آینده‌ی رزرو شده برای یک محصول.
- و یک سناریوی خاص `close_all_sanses_of_all_products` برای بستن دسته‌جمعی سانس‌های تعداد زیادی اتاق در بازه‌ی زمانی ثابت (احتمالاً اسکریپت عملیاتی یک‌بار مصرف).

---

## ورودی/خروجی کلی و امنیت

- **CORS** برای همه‌ی originها باز است، هدرها و متدها (`GET, POST, OPTIONS, PUT, DELETE, PATCH`) تنظیم شده‌اند و درخواست‌های `OPTIONS` با `204` پاسخ داده و قطع می‌شوند.
- **فقط متد POST** پذیرفته می‌شود:
  - اگر `CONTENT_TYPE` شامل `application/json` باشد:
    - بدنه‌ی `php://input` را `json_decode` کرده و در `$data` می‌گذارد.
  - اگر `application/x-www-form-urlencoded` باشد:
    - از `$_POST` یک object می‌سازد.
  - در غیر این دو حالت:
    - `415` و JSON `{ "error": "Unsupported Media Type" }`.
  - اگر متد غیر POST باشد:
    - `405` و JSON `{ "error": "Invalid Request Method" }`.
- دامنه‌های مجاز: فقط چند دامنه‌ی معروف EscapeZoom (`escapezoom.ir`, `escapezoom.co`, `bak.escapezoom.ir`, `dev-api.escapezoom.ir`, `goriza.ir`, `dev.escapezoom.local` و ...)؛ بقیه:
  - در جدول `hackers` لاگ می‌شوند،
  - و با پیام `Get outta here` متوقف می‌شوند.
- فایل‌های کمکی:
  - `db-connect.php` برای `$conn` (MySQL).
  - `md-connect.php` برای اتصال Medoo (`$ez_database` و ...).
  - `jdf.php` برای تاریخ شمسی (`jdate`, `jstrftime`).
  - `helper-functions.php` برای `get_day_type`, `get_day_type2`, `get_sanses`, `ez_sendpayamak` و غیره.

ساختار عمومی ورودی:

```json
{
  "type": "<scenario>",
  "data": { ... سناریو-اسپسیفیک ... }
}
```

---

## سناریوی ویژه: `close_all_sanses_of_all_products`

این بلوک در ابتدای فایل و با یک لیست بسیار بزرگ از `products_id` و دو `time_ress` هاردکد شده است.

**هدف**: بستن همه‌ی سانس‌های یک مجموعه‌ بزرگی از اتاق‌ها، در دو روز مشخص (احتمالاً برای مناسبت/تعطیلی سراسری).

**منطق**:

- `time_ress`: دو timestamp روز شروع (مثلاً دو روز خاص).
- `products_id`: آرایه‌ی طولانی از `product_id`ها.
- برای هر `time_res` و هر `product_id`:
  - `day_type = get_day_type(time_res)`.
  - `sanses = get_sanses(product_id)`.
  - روی `sanses[day_type]` حلقه می‌زند:
    - `start = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);`
    - برای هر `start` یک رکورد در `wp_zb_booking_history` با:
      - `status = 2` (غیرقابل رزرو/بسته),
      - `quantity = 0`,
      - `name` و `phone` خالی
      - ثبت می‌کند.

این سناریو بیشتر شبیه **اسکریپت migration/maintenance** است تا API روزانه.

---

## ۱. سناریوی `query_execution`

**نقش**: اجرای مستقیم یک کوئری SQL ارسال‌شده از بیرون و برگرداندن نتیجه (ابزار ادمین).

**ورودی**:

```json
{
  "type": "query_execution",
  "data": {
    "query": "SELECT ...",
    "single_value": true|false
  }
}
```

**منطق**:

- `query` را بدون هیچ فیلتر/امنیتی روی `$conn` اجرا می‌کند.
- اگر `single_value = true`:
  - فقط عنصر اول `fetch_all` را برمی‌گرداند (`$row[0]`).
- خروجی:
  - `echo json_encode(json_decode(json_encode($row)))` (عملاً یک normalize به stdClass/array).

**نکته امنیتی**: این endpoint بسیار قدرتمند و خطرناک است و باید فقط پشت احراز هویت/دسترس داخلی استفاده شود.

---

## ۲. سناریوهای نمایش سانس‌ها: `display` و `panel_sanses_display`

هر دو سناریو با ساخت یک `stdClass $args` و سپس صدا زدن توابع view (`operator_*_view`, `user_*_view`) کار می‌کنند، تفاوتشان در نحوه‌ی تعیین `day_type` و `discount` است.

### ۲.۱. `display`

**هدف**: رندر سانس‌ها هم برای اپراتور و هم برای کاربر (دسکتاپ/موبایل)، بسته به نقش کاربر.

**ورودی (`data`)**:

- `time_res`: timestamp شروع روز (۰۰:۰۰).
- `ezservice`: `product_id` بازی.
- `is_mobile`: true/false.
- `auto_disable`: عدد دقیقه (برای بستن رزرو بعد از X دقیقه قبل از سانس).
- `o900`: base64-encoded user_id.
- `o985`: base64-encoded user_role.

**منطق**:

1. decode `user_id` و `user_role`.
2. `bookings_objs = get_sans_lock(product_id)` و پاک‌کردن قفل‌های منقضی (قدیمی‌تر از ۵ دقیقه یا گذشته از خود سانس).
3. `day_type = get_day_type2(time_res)` و `sanses = get_sanses(product_id)`.
4. کوئری روی `products_data` برای گرفتن `product_obj` و استخراج تخفیف خاص (`discount_data` → `special_discount_percentage`).
5. `bookings` = لیست `booking_time` از قفل‌ها.
6. ساخت آبجکت `args` با:
   - `time_res`, `service_id`, `user_role`, `day_type`, `bookings`, `sanses`, `auto_disable`, `discount`.
7. تصمیم روی نوع view:
   - برای نقش‌های مدیریتی (`administrator`, `admin`, `shop_manager`, `poshtiban`, `shopist`, `contentist`):
     - اگر موبایل → هر دو `operator_mobile_view` و `user_mobile_view` (نمایش ترکیبی),
     - اگر دسکتاپ → `operator_desktop_view` و `user_desktop_view`.
   - برای `compiler` یا `sans_manager`:
     - ابتدا بررسی می‌کند که این user مالک/مدیر همین محصول هست یا نه (`owner_id` / `manager_id`).
     - اگر مالک بود → فقط operator-view،
     - در غیر این صورت → user-view.
   - سایر نقش‌ها:
     - فقط user-view.
8. خروجی HTML با `ob_get_clean()` گرفته و به صورت `json_encode` برمی‌گردد.

### ۲.۲. `panel_sanses_display`

**هدف**: نمایش سانس‌ها برای پنل داخلی (special operator view)؛ فقط اپراتور، نه user.

**تفاوت‌ها**:

- از `get_day_type` (نه `get_day_type2`) استفاده می‌کند.
- `discount` در این سناریو در نظر گرفته نمی‌شود، فقط وضعیت سانس‌ها مهم است.
- فقط در صورتی که نقش `compiler` یا `sans_manager` باشد و مالک محصول باشد، یک زیر-HTML خاص (`operator_desktop_view` به‌صورت inline در خود بلوک) رندر می‌کند (کد داخل همان بلاک نوشته شده، نه توابع مشترک).

خروجی هم مثل قبل HTML-encoded به صورت JSON است.

---

## ۳. سناریوهای قدیمی UI: `hazf_kon` و `baz_kon`

این دو برای پنل قدیمی (HTML کلاسیک) استفاده می‌شوند.

### ۳.۱. `hazf_kon` (حذف/بستن سانس)

**ورودی (`data`)**:

- `start`: timestamp سانس.
- `service`: `product_id`.
- `o900` (اختیاری): user_id.

**منطق**:

- `get_sans_lock` برای این محصول را می‌خواند:
  - اگر سانس در قفل‌ها پیدا شود (`lock_flag = true`):
    - HTML نشان‌دهنده‌ی «در حال رزرو» + یک `alert` JS نمایش می‌دهد و اجازه‌ی بستن نمی‌دهد.
  - در غیر این صورت:
    - در `wp_zb_booking_history` با `status = 2`، `customer_id = user_id`، یک رکورد برای این سانس درج می‌کند (بسته‌ کردن سانس).
    - HTML جدیدی برمی‌گرداند که وضعیت سانس را «غیرقابل رزرو» و دکمه‌ی «بازکن» (`time_click_bazkon`) نشان می‌دهد.

خروجی: HTML درون `json_encode(ob_get_clean())`.

### ۳.۲. `baz_kon` (باز کردن سانس)

**ورودی**:

- `start`: timestamp.
- `service`: product_id.

**منطق**:

- رکوردهای `wp_zb_booking_history` برای این سانس را حذف می‌کند (سانس دوباره آزاد می‌شود).
- HTML جدید با وضعیت «قابل رزرو» و دکمه‌ی «حذف کن» (`time_click_hazf`) برمی‌گرداند.

---

## ۴. جابجایی و حذف سانس رزرو شده: `exchange_sans_select_hour`, `exchange_sans`, `remove_sans`

### ۴.۱. `exchange_sans_select_hour`

**هدف**: در جریان جابجایی سانس، ابتدا سانس‌های خالی (قابل انتقال) را لیست می‌کند.

**ورودی**:

- `day_time`: timestamp روز (شروع).
- `room_id`: product_id.

**منطق**:

1. `day_type = get_day_type(day_time); sanses = get_sanses(room_id);`.
2. برای هر سانس آن روز:
   - `firstTimeTs` را می‌سازد.
   - از `wp_zb_booking_history` وضعیت آن سانس را می‌گیرد.
   - اگر `status` نه ۱ و نه ۲ (نه رزرو شده و نه بسته):
     - `open_sanses[firstTimeTs] = jdate('H:i', firstTimeTs);`.
3. خروجی: `json_encode($open_sanses)` (map از timestamp به ساعت خوانا).

### ۴.۲. `exchange_sans`

**هدف**: جابجایی واقعی سانس رزرو شده (تغییر `booking_time`).

**ورودی**:

- `room_id`, `room_name`.
- `player_phone`, `player_name`.
- `origin_time`, `destination_time`.

**منطق**:

1. با `jstrftime` دو رشته‌ی `t1` و `t2` (زمان قدیم/جدید) می‌سازد.
2. رکورد `wp_zb_booking_history` مربوط به این سانس را آپدیت می‌کند و `booking_time` را به `destination_time` تغییر می‌دهد.
3. `products_data` را می‌خواند، `contact_info` را `unserialize` می‌کند و:
   - `owner_phone`, `manager_phone`, `chat_id`, `manager_chat_id` را می‌گیرد.
4. با `ez_sendpayamak` SMS می‌فرستد:
   - به خود بازیکن: اطلاع تغییر سانس.
   - به مالک: اطلاع درخواست بازیکن و تغییر سانس.
   - به مدیر (اگر داریم و با مالک متفاوت است): همان متن.
5. اگر `chat_id` تنظیم شده باشد:
   - یک متن URL-encoded شده آماده می‌کند،
   - آن را به `impec.ir` می‌فرستد (احتمالاً سرویس تلگرام/چت) برای `chat_id` و `manager_chat_id`.
6. خروجی: `json_encode(1)` (فقط موفقیت/شکست منطقی).

### ۴.۳. `remove_sans`

**هدف**: حذف/آزادکردن یک سانس رزرو شده و اطلاع‌رسانی به مجموعه‌دار.

**ورودی**:

- `room_id`, `room_name`, `origin_time`.

**منطق**:

1. با `jstrftime` متن زمان (`t1`) برای پیامک می‌سازد.
2. رکوردهای `wp_zb_booking_history` برای این سانس را حذف می‌کند.
3. `products_data` و `contact_info` را می‌خواند.
4. SMS به `owner_phone` و در صورت وجود/تفاوت، به `manager_phone` می‌فرستد:
   - «سانس X بازی Y برای فروش مجدد باز شد».
5. اگر `chat_id` وجود داشته باشد:
   - همان پیام را به `impec.ir` برای `chat_id`/`manager_chat_id` می‌فرستد.
6. خروجی: `json_encode($chat_id)` (صرفاً برای دیباگ).

---

## ۵. قفل سانس‌ها: `add_sans_lock`, `remove_sans_lock`, `get_sans_lock`

### ۵.۱. `add_sans_lock`

**ورودی**:

- `product_id`, `booking_time`.

**منطق**:

- رکوردی در جدول `booking_lock_schedule` با ‌فیلدهای `product_id`, `booking_time`, `lock_time = time()` درج می‌کند.
- `return true;` (توجه: این `return` به caller HTTP برنمی‌گردد، چون هنوز echo نشده است).

### ۵.۲. `remove_sans_lock`

**ورودی**:

- `product_id`, `booking_time`.

**منطق**:

- `remove_sans_lock` را صدا می‌زند (تابع پایین فایل)، که:
  - رکورد مربوطه را از `booking_lock_schedule` حذف می‌کند و `true` برمی‌گرداند.

### ۵.۳. `get_sans_lock`

**منطق**:

```php
$result = $conn->query("SELECT * FROM booking_lock_schedule WHERE product_id LIKE '$product_id'");
if ($result->num_rows > 0) $product_obj = $result->fetch_all(MYSQLI_ASSOC);
return [];
return $product_obj;
```

- به‌خاطر یک `return []` قبل از `return $product_obj`، این تابع **همیشه آرایه‌ی خالی** برمی‌گرداند و هرگز داده‌های واقعی قفل را برنمی‌گرداند.
- با این وضعیت، تمام جاهایی که به قفل‌ها تکیه می‌کنند (`display`, `panel_sanses_display`, `hazf_kon`, `get_sanses`, ...) عملاً قفل را نادیده می‌گیرند.
- این می‌تواند یک باگ یا تصمیم موقتی برای غیرفعال‌کردن سیستم قفل باشد.

---

## ۶. ساخت لینک خرید: `create_purchase_url`

**هدف**: بعد از انتخاب سانس و مبلغ، یک UI کوچک (HTML) برمی‌گرداند که کاربر در آن:

- تعداد نفرات را انتخاب می‌کند،
- مبلغ پیش‌پرداخت را می‌بیند،
- و در نهایت لینک `/checkout` مناسب ساخته می‌شود.

**ورودی**:

- `room_id`, `start`, `pricesel`.

**منطق**:

1. `products_data` برای `room_id` را می‌خواند، تخفیف `discount_data` را محاسبه می‌کند (`special_discount_percentage`).
2. `pricesel` را با درنظر گرفتن تخفیف اعمال می‌کند:
   - `pricesel *= (1 - $discount / 100)`.
3. HTML شامل:
   - عنوان بازی،
   - تاریخ و ساعت (`jdate`),
   - `select` برای انتخاب تعداد نفر (`count_min` تا `count_max`),
   - محاسبه‌ی مبلغ پیش‌پرداخت بر اساس `pish_person * pricesel` (بر حسب هزار تومان)،
   - دکمه‌ی «رزرو و پیش پرداخت» و «بازگشت و تغییر سانس».
4. در اسکریپت JS:

```js
function getNewVal(item) {
    var origin = window.location.origin;
    var url_final = origin + "/checkout/?" +
        "quantity=" + item.value +
        "&add-to-cart=" + room_id +
        "&book=" + start;
    $("#go_final").attr("href", url_final);
}
```

5. در انتها `die()` می‌کند (لینک `echo json_encode($chat_id)` بعد از `die` عملاً unreachable است).

---

## ۷. `update_product_sub_data`

**هدف**: به‌روزرسانی schedule سانس و مقدار پیش‌پرداخت (`pish_person`) برای یک محصول.

**ورودی**:

- `room_id`, `schedule`, `pish_person`.

**منطق**:

- `schedule` را `serialize` کرده و در `products_data.schedule` به‌روز می‌کند.
- `pish_person` را در `products_data.pish_person` ذخیره می‌کند.

استفاده می‌تواند از یک UI ادمین یا API داخلی باشد.

---

## ۸. API سانس‌ها برای اپ: `get_sanses`

این یکی از **مهم‌ترین سناریوها** است؛ خروجی آن توسط اپ/SPA برای نمایش لیست سانس‌ها، قیمت‌ها و تخفیف‌ها استفاده می‌شود.

**ورودی (`data`)**:

- `day_start_time`: timestamp شروع روز.
- `product_id`.
- `days` (اختیاری): تعداد روزهایی که می‌خواهیم سانس‌ها را بگیریم (پیش‌فرض ۱).

**منطق**:

1. از `products_data` رکورد محصول را می‌گیرد:
   - اگر `discount_data` فعال باشد (`special_discount_date > now`):
     - `hot_discount = special_discount_percentage`.
   - `auto_disable = time() + auto_disable(minutes)`.
2. قفل‌های سانس (`get_sans_lock`) را می‌گیرد و قفل‌های منقضی را پاک می‌کند (اما باگ گفته‌شده باعث می‌شود عملاً قفل‌ها همیشه خالی باشند).
3. برای `i = 0..days-1`، آرایه‌ی `days_time_arr` را می‌سازد: `day_start_time + i * 86400`.
4. برای هر روز (`time_res`):
   - `day_type = get_day_type2(time_res)`.
   - `sanses = get_sanses(product_id)`.
   - `bookings` را از روی قفل‌ها می‌سازد.
   - برای هر سانس:
     - `firstTimeTs` را محاسبه می‌کند و در `sanses_list` می‌ریزد.
   - اگر `sanses_list` خالی بود:
     - کوئری ساده روی همه‌ی `wp_zb_booking_history` برای آن محصول می‌زند.
   - اگر `sanses_list` پر بود:
     - کوئری `SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE product_id AND booking_time IN (list)` اجرا می‌کند.
   - `order_objs[booking_time] = status`.
5. برای هر سانس:
   - `firstTimeTs`, `price`, `order_obj` محاسبه می‌شود.
   - از بازه‌ی ۰۰:۰۰–۰۸:۰۰ صرف‌نظر می‌کند.
   - فقط اگر `firstTimeTs >= auto_disable` (یعنی به اندازه‌ی کافی به زمان سانس نزدیک هستیم) پردازش ادامه می‌دهد.
   - وضعیت سانس (`status`) را تعیین می‌کند:
     - `status == 1` → `reserved`.
     - `status == 2` → `non_reservable`.
     - اگر در `bookings` باشد → `reserving`.
     - در غیر این صورت → `reservable`.
6. تخفیف آنی (`instant_off`) را محاسبه می‌کند:
   - اگر **تخفیف داغ (`hot_discount`) فعال نباشد**:
     - `instant_off` را از `row[0]['instant_off']` و روز جاری (`$day_type`) می‌گیرد.
     - اگر داده خالی نبود و `hour != -1`, `percentage != -1`:
       - اگر `firstTimeTs - now <= hour * 3600` (کمتر از `hour` ساعت تا سانس مانده):
         - تخفیف آنی فعال می‌شود:
           - `instant_off_percentage = percentage`.
           - `instant_off_expiry_time = firstTimeTs`.
7. قیمت نهایی تخفیف‌خورده:
   - اگر `sans['off_price']` وجود دارد:
     - `discount_final = off_price * (1 - (hot_discount + instant_off_percentage)/100)`.
   - در غیر این صورت:
     - `discount_final = price * (1 - ...)`.
   - اگر `discount_final != sans['price']` آن را `round` می‌کند، در غیر این صورت صفر می‌گذارد (یعنی `0` معادل «بدون تخفیف اضافه» است).
8. مقدار نهایی برای هر سانس:

```json
{
  "time": <timestamp>,
  "price": <int base price>,
  "off_price": <int final discounted price or 0>,
  "status": "reservable|reserved|non_reservable|reserving",
  "instant_off": <timestamp or null>
}
```

خروجی:

- اگر فقط یک روز خواسته شده، به‌جای آرایه‌ی دوبعدی، مستقیم آرایه‌ی آن روز را برمی‌گرداند.
- در صورت نبود سانس، آرایه‌ی خالی `[]`.

---

## ۹. `sans_management` و `sans_management_web`

این دو شبیه وب‌سرویس‌های `team/sans_management.php` هستند اما به‌صورت JSON (و در `sans_management_web` هم بخشی HTML در سایر فایل‌ها).

### ۹.۱. `sans_management`

**هدف**: برگرداندن وضعیت سانس‌ها برای مدیریت (باز/بسته/رزرو/در حال رزرو) در قالب JSON سبک.

**ورودی**:

- `day_start_time`, `product_id`.

**منطق**:

- `products_data` را می‌خواند، `auto_disable` را محاسبه می‌کند.
- قفل‌ها را پاک‌سازی می‌کند و `bookings` (timestamps قفل‌ها) را می‌سازد.
- `day_type = get_day_type(time_res); sanses = get_sanses(product_id);`.
- `sanses_list` و کوئری `wp_zb_booking_history` برای گرفتن `status` هر `booking_time`.
- برای هر سانس (خارج از ۰۰:۰۰–۰۸:۰۰ و بعد از `auto_disable`):
  - اگر `status == 1` → `reserved`.
  - اگر `status == 2` → `openable`.
  - اگر در `bookings` → `reserving`.
  - در غیر این صورت → `closeable`.

خروجی:

```json
[
  {
    "time": <timestamp>,
    "price": <base_price>,
    "off_price": <off_price_or_0>,
    "status": "reserved|openable|reserving|closeable"
  },
  ...
]
```

### ۹.۲. `sans_management_web`

**هدف**: نسخه‌ی وبی/گرافیکی‌تر که علاوه بر status، اطلاعات رزرو (`reserved_data`) را هم برمی‌گرداند و در پایین فایل همان HTML بردهایی که در `team/sans_management.php` دیدی ساخته می‌شود.

**خروجی هر سانس**:

```json
{
  "time": <timestamp>,
  "status": "reserved|openable|reserving|closeable",
  "reserved_data": {
    "customer_id": ...,
    "booked_time": ...,
    "name": ...,
    "level": <int>,
    "phone": ...,
    "quantity": <int>,
    "order_id": <int>
  } | null
}
```

در ادامه، با استفاده از این `reservation_data` و `user_level`، سطح بازیکن (`تازه وارد/نوپا/...`) تعیین شده و کارت‌های HTML (با badge لغو، دکمه‌های open/close و ...) رندر می‌شود.

---

## ۱۰. باز/بستن سانس برای اپ: `open_sans`, `close_sans`, `close_all_sanses`

این سه سناریو نسخه‌ی JSON-محور برای اپ/فرانت‌اند مدرن هستند (در مقابل `hazf_kon`/`baz_kon` که HTML برمی‌گردانند).

### ۱۰.۱. `open_sans`

- ورودی: `sans_time`, `product_id`.
- عمل:
  - حذف رکوردهای `wp_zb_booking_history` برای این سانس.
- خروجی:

```json
{
  "new_status": "closeable",
  "error_message": "",
  "success_message": "با موفقیت باز شد!"
}
```

### ۱۰.۲. `close_sans`

- ورودی: `sans_time`, `product_id`, اختیاری `user_id`.
- عمل:
  - `get_sans_lock` برای این محصول را بررسی می‌کند (اما باگ باعث می‌شود همیشه خالی باشد).
  - اگر در `wp_zb_booking_history` رکوردی وجود داشته باشد → `reserved_flag = true`.
  - اگر `reserved_flag`:
    - `new_status = 'reserved'` + پیام خطا.
  - else اگر `lock_flag`:
    - `new_status = 'reserving'`.
  - else:
    - یک رکورد با `status = 2` (بسته) درج می‌کند و:

```json
{
  "new_status": "openable",
  "error_message": "",
  "success_message": "با موفقیت بسته شد!"
}
```

### ۱۰.۳. `close_all_sanses`

- ورودی: `day_start_time`, `product_id`, اختیاری `user_id`.
- مشابه سناریوی `close_all_sanses_of_all_products` اما برای یک اتاق/روز:
  - timetable و `auto_disable` را از `products_data` می‌گیرد.
  - سانس‌های روز را محاسبه می‌کند.
  - روی `wp_zb_booking_history`، وضعیت هر سانس را می‌گیرد.
  - اگر `status == 1` (رزرو شده) → `reserved_flag = true`.
  - اگر `status` نه ۱ و نه ۲، و سانس بعد از `auto_disable` است → در `ready_to_close` می‌گذارد.
- اگر هیچ سانسی آماده‌ی بسته شدن نباشد:
  - `400` و پیام «هیچ سانسی برای بسته شدن وجود ندارد.».
- اگر هر سانسی رزرو شده باشد:
  - `400` و پیام «دست کم یکی از سانس های شما رزرو شده است...» (اجازه‌ی بستن همه‌ی سانس‌ها را نمی‌دهد).
- در غیر این صورت:
  - برای هر `sans_time` در `ready_to_close` یک رکورد `status = 2` درج می‌کند.
- خروجی موفق:

```json
{
  "success": true,
  "data": ["تمام سانس های درخواستی بسته شد."]
}
```

---

## ۱۱. `get_pending_sanses`

**هدف**: آوردن لیست سانس‌های آینده که **رزرو قطعی** (`status = 1`) دارند برای یک محصول.

**ورودی**:

- `product_id`.

**منطق**:

- `now = time()`.
- از `wp_zb_booking_history` همه‌ی رکوردهایی که:
  - `status = 1`,
  - `booking_time > now`,
  - `room_id = product_id`
  را می‌گیرد.
- برای هر رکورد:

```json
{
  "user_id":    customer_id,
  "order_id":   wc_order_id,
  "product_id": room_id,
  "sans_time":  booking_time
}
```

خروجی: آرایه‌ی JSON از این اشیاء.

---

## ۱۲. توابع view (`operator_desktop_view`, `operator_mobile_view`, `user_desktop_view`, `user_mobile_view`)

این توابع:

- `sanses` و `day_type` و `order_objs` را استفاده می‌کنند تا برای هر سانس:
  - تمبر زمان (`H:i`),
  - قیمت/تخفیف (و در `user_*` تخفیف کلی `discount`),
  - وضعیت رزرو:
    - برای اپراتور: رزرو شده، غیر قابل رزرو، قابل رزرو + دکمه‌های حذف/بازکن.
    - برای کاربر: رزرو شده/غیرقابل رزرو/در حال رزرو/قابل رزرو (با دکمه‌ی رزرو).
- CSS کلاس‌هایی مثل `reserved-bg`, `green-bg`, `red-bg` و دکمه‌های JS (`time_click`, `time_click_hazf`, `time_click_bazkon`) را تولید می‌کنند.

این توابع منطق تجاری جدیدی ندارند، بلکه **نمایش UI** بر اساس داده‌هایی است که در سناریوهای بالا آماده شده‌اند.

---

## ۱۳. کمکی‌ها: `get_sans_lock`, `remove_sans_lock`

- `remove_sans_lock` درست عمل می‌کند (حذف رکورد از `booking_lock_schedule` برای یک `product_id` و `booking_time`).
- `get_sans_lock` به‌خاطر `return []` عملاً **غیرعملیاتی** است:
  - اگر این خط برداشته شود، خروجی واقعی `booking_lock_schedule` (برای قفل‌های فعال رزرو) برگردانده می‌شود.
  - در طراحی اصلی، قفل‌ها باید مانع از بستن/رزرو دوباره‌ی سانسی شوند که کاربر دیگری در حال رزرو آن است.

---

## جمع‌بندی نهایی

- `reservation.php` مرکز ثقل مدیریت سانس و رزرو در سیستم شماست:
  - همه‌چیز از جستجوی سانس، قفل، رزرو، بستن/بازکردن، جابجایی، تا تولید لینک خرید و UI اپراتور/کاربر در این فایل مدیریت می‌شود.
- توابع/سناریوهای اصلی:
  - `get_sanses`, `sans_management`, `sans_management_web` برای APIهای مدرن،
  - `hazf_kon`, `baz_kon`, `display`, `panel_sanses_display` برای UIهای قدیمی‌تر،
  - `exchange_sans`, `remove_sans` برای مدیریت پس از رزرو، همراه با SMS و نوتیفیکیشن،
  - `open_sans`, `close_sans`, `close_all_sanses` برای کنترل آسان وضعیت سانس‌ها در اپ.
- نکات مهم برای refactor/بهبود:
  - فعال/اصلاح‌کردن `get_sans_lock` تا سیستم قفل سانس واقعاً کار کند،
  - ایمن‌سازی `query_execution`,
  - یکپارچه‌کردن منطق status سانس (در چند جای فایل تقریباً مشابه تکرار شده است)،
  - جدا کردن viewها از منطق تجاری برای تمیزتر شدن ساختار.

