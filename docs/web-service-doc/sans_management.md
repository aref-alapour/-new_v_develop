## خلاصه‌ی کلی

این فایل یک **اسکریپت PHP سمت سرور** است که به‌عنوان وب‌سرویس برای «مدیریت سانس‌ها» در پنل تیم و همچنین برای جستجوی بازی‌ها استفاده می‌شود. ورودی‌ها را به‌صورت **POST با بدنه‌ی JSON یا فرم** می‌گیرد و بر اساس مقدار `type` روی `\$data` چند سناریوی مختلف را اجرا می‌کند:

- `sans_management_web` برای ساختن لیست سانس‌ها و وضعیت آن‌ها (باز/بسته/رزرو شده) و رندر HTML کارت‌های هر سانس در پنل تیم.
- `check_playing` برای تشخیص اینکه الان کدام سانس در حال اجراست و رندر یک باکس «در حال بازی».
- `open_sans` برای «باز کردن» یک سانس (حذف رکورد قفل از جدول رزروها).
- `close_sans` برای «بستن» یک سانس (ایجاد رکورد با `status = 2` در جدول رزروها).
- `close_all_sanses` برای بستن دسته‌ای سانس‌ها با رعایت شرایط رزرو نشدن و رسیدن به زمان auto_disable.
- `game_search` برای جستجوی بازی‌ها بر اساس نام و برگرداندن HTML لینک‌دار نتایج.

همه‌ی این سناریوها روی **دیتابیس MySQL** (از طریق `\$conn`) کار می‌کنند و بخشی از منطق اصلی مدیریت سانس در بک‌اند EscapeZoom هستند.

---

## محل‌های استفاده از این وب‌سرویس

### ۱. پنل تیم – صفحه‌ی مدیریت سانس‌ها

فایل `template/team/pages/sans_management.php` در قالب، چندین درخواست AJAX به این وب‌سرویس می‌فرستد:

- **ساخت جدول سانس‌ها برای یک اتاق/روز خاص**:
  - URL: `site_url('web-service/team/sans_management.php')`
  - `type: "sans_management_web"`
  - `data: { "day_start_time": day, "product_id": room }`
  - خروجی: HTML کارت‌های سانس (با دکمه‌ی باز/بسته و باکس رزرو شده).

- **چک کردن سانس‌های در حال بازی**:
  - `type: "check_playing"`
  - `data: { "day_start_time": date, "product_id": product_id }`
  - خروجی: HTML یک یا چند باکس «در حال بازی» اگر سانسی در بازه‌ی زمانی بازی باشد.

- **باز و بسته کردن یک سانس تکی**:
  - `type: "open_sans"` یا `type: "close_sans"` (با استفاده از `${action}_sans`)
  - `data: { "sans_time": parseInt(time), "product_id": parseInt(product) }`
  - خروجی: JSON با `new_status` و پیام خطا/موفقیت.

- **بستن همه‌ی سانس‌های روز**:
  - `type: "close_all_sanses"`
  - `data: { "day_start_time": day_start_time, "product_id": product_id }`
  - خروجی: JSON با `success` و متن «تمام سانس های درخواستی بسته شد.» یا خطای مناسب.

### ۲. پنل تیم – صفحه‌ی کامنت‌ها (جستجوی بازی)

در `template/team/pages/comments.php` و همین‌طور پایین‌تر خود `sans_management.php`، جستجوی بازی در این وب‌سرویس زده می‌شود:

- URL: `site_url('web-service/team/sans_management.php')`
- `type: "game_search"`
- `data: { "term": term }`
- خروجی: HTML شامل `<a>`های نتایج جستجو با `data-id`، تصویر، نام بازی و شهر.

### ۳. وب‌سرویس‌های دیگر مرتبط

در فایل‌های `web-service/saeed.php` و `web-service/reservation.php` هم انواع دیگری از `sans_management` و `sans_management_web` برای مصرف اپلیکیشن یا بخش‌های دیگر سایت پیاده شده‌اند، اما فایل حاضر (`team/sans_management.php`) مخصوص **فضای پنل تیم** است و به آدرس `web-service/team/sans_management.php` متصل می‌شود.

---

## ساختار کلی فایل و پیش‌نیازها

- در ابتدای فایل:
  - **CORS** فعال شده (برای همه‌ی originها).
  - متد مجاز: `GET, POST, OPTIONS, PUT, DELETE, PATCH`.
  - برای درخواست `OPTIONS`، پاسخ 204 داده و خروج می‌کند (پشتیبانی preflight).
  - `error_reporting` فقط خطاهای مهم را نگه می‌دارد.
  - `timezone` روی `Asia/Tehran`.
  - `db-connect.php`, `jdf.php`, `helper-functions.php` لود می‌شوند.
  - چک `$_SERVER['HTTP_HOST']` برای دامنه‌های مجاز؛ در حالت غیرمجاز، در جدول `hackers` لاگ کرده و `Get outta here` می‌دهد.

- ورودی:
  - فقط روی **متد POST** کار می‌کند؛ در غیر این صورت `405` با JSON خطا.
  - اگر `CONTENT_TYPE` حاوی `application/json` باشد:
    - بدنه‌ی خام `php://input` را `json_decode` می‌کند و در `\$data` می‌گذارد.
  - اگر `application/x-www-form-urlencoded` باشد:
    - `\$_POST` را به JSON و بعد object تبدیل می‌کند.
  - در غیر این دو حالت، `415 Unsupported Media Type`.

- متغیر مهم:
  - `\$data->type` تعیین می‌کند کدام بلوک منطقی اجرا شود.
  - `\$data->data` شیء حاوی پارامترهای هر سناریو است.

---

## تحلیل سناریوها (به تفکیک type)

### ۱. سناریوی `sans_management_web`

**هدف**: ساخت مجموعه‌ای از کارت‌های سانس برای یک روز و یک اتاق (room) مشخص در پنل تیم؛ شامل وضعیت سانس، اطلاعات کاربر رزروکننده، و دکمه‌های باز/بسته.

**ورودی‌ها**:

- `\$data->data->day_start_time` → تایم‌استمپ شروع روز (روی 00:00).
- `\$data->data->product_id` → شناسه‌ی بازی/اتاق (room id).

**مراحل ساخت داده‌ها**:

1. **خواندن تنظیمات محصول**:
   - از جدول `products_data`، رکوردی را با `product_id` برابر دریافت می‌کند.
   - `auto_disable` به‌صورت `time() + auto_disable * 60` محاسبه می‌شود (کاربرد مستقیم در این بلوک دیده نمی‌شود اما برای سازگاری با سایر وب‌سرویس‌هاست).

2. **محاسبه‌ی نوع روز و سانس‌ها**:
   - `\$day_type = get_day_type($time_res);`
   - `\$sanses = get_sanses($product_id);`
   - فرض: `get_sanses` آرایه‌ای شبیه `['weekday' => [...], 'weekend' => [...]]` برمی‌گرداند و هر سانس یک `time` (مثلاً `"18:00"`) و قیمت/تخفیف دارد.

3. **آماده‌سازی آرایه‌ی رزروهای موقت**:
   - `\$bookings` از روی `\$bookings_objs` پر می‌شود، اما در این فایل `\$bookings_objs` جایی ست نمی‌شود؛ احتمالاً در `helper-functions.php` به‌صورت global آماده می‌شود.
   - اگر تایم‌استمپ یک سانس در `\$bookings` باشد، وضعیت `reserving` در نظر گرفته می‌شود.

4. **ساخت لیست تایم‌استمپ‌های همه‌ی سانس‌های روز**:
   - برای هر `\$sans` در `\$sanses[\$day_type]`:
     - `\$firstTimeTs = strtotime(date("Y-m-d", $time_res) . ' ' . $sans['time']);`
     - این مقدار در `\$sanses_list` جمع می‌شود.
   - در نهایت `implode(',', $sanses_list)` برای استفاده در `IN (...)` کوئری.

5. **خواندن وضعیت واقعی از تاریخچه‌ی رزرو (`wp_zb_booking_history`)**:
   - کوئری:
     - `SELECT wc_order_id, status, booking_time, name, level, phone, quantity FROM wp_zb_booking_history WHERE room_id LIKE %s AND booking_time IN (%s)`
   - خروجی در `\$sans_objs` و سپس map می‌شود به `\$order_objs[booking_time] = sans_obj`.

6. **ساخت آرایه‌ی `\$reservation_data`**:
   - برای هر سانس:
     - `time` = `\$firstTimeTs`.
     - از `\$order_objs[$firstTimeTs]` وضعیت را می‌گیرد.
     - اگر `status == 1` → سانس رزرو شده:
       - `status = 'reserved'`
       - `reserved_data` حاوی `name`, `level`, `phone`, `quantity`, `order_id`.
     - اگر `status == 2` → سانس قفل شده (بسته، اما قابل باز کردن):
       - `status = 'openable'`.
     - اگر در `\$bookings` بود → `status = 'reserving'`.
     - در غیر این حالت → `status = 'closeable'`.

**منطق سطح کاربر/تم (level)**:

- از روی `\$data['reserved_data']['level']` تم رنگ و عنوان سطح بازیکن تعیین می‌شود:
  - `1` → رنگ `[#858585]` و عنوان `تازه وارد`.
  - `2` → رنگ `[#252728]` و عنوان `نوپا`.
  - `3` → رنگ `[#00B2FF]` و عنوان `با تجربه`.
  - سایر مقادیر → رنگ `primary-500` و عنوان `کارکشته`.
- این اطلاعات در آرایه‌ی `\$user_info` جمع می‌شود و بعد برای `data-user-info` روی کارت `reserved` به‌صورت `json_encode` جاگذاری می‌گردد.

**خروجی HTML (رندر نهایی)**:

برای هر آیتم در `\$reservation_data`، بر اساس `status` یکی از سه بلاک HTML زیر چاپ می‌شود:

- **reserved**:
  - `div` با کلاس‌های استایل، زمان سانس (`jdate('H:i')`) و نام کاربر رزروکننده.
  - `data-user-info` یک JSON است که در فرانت‌اند برای نمایش مودال اطلاعات استفاده می‌شود.

- **closeable**:
  - کارت سفید با دکمه‌ای با متن «باز» و کلاس `toggle-btn`.
  - `data-room-action="close"`؛ فرانت‌اند با این دیتا تصمیم می‌گیرد درخواست `close_sans` بزند.

- **openable**:
  - کارت سفید با دکمه «بسته» و `data-room-action="open"`.

این بلاک با `exit;` تمام می‌شود، پس هیچ سناریوی دیگری در همان درخواست اجرا نمی‌شود.

---

### ۲. سناریوی `check_playing`

**هدف**: تشخیص اینکه در روز و اتاق مشخص، الان کدام سانس در حال بازی است (بر اساس زمان حال و `duration` بازی) و نمایش HTML وضعیت.

**ورودی‌ها**:

- `day_start_time` و `product_id` مشابه سناریوی قبل.

**گام‌ها**:

1. خواندن رکورد محصول از `products_data` و گرفتن `auto_disable` (مثل قبل).
2. محاسبه‌ی `day_type` و `sanses` همانند سناریوی `sans_management_web`.
3. پر کردن `\$bookings` از `\$bookings_objs` (مثل قبل).
4. ساختن `\$sanses_list` و گرفتن `\$sans_objs` از `wp_zb_booking_history`، و ساخت `\$order_objs` map.
5. ساخت `\$reservation_data` با همان منطق وضعیت (`reserved`, `openable`, `reserving`, `closeable`) و `reserved_data`.

**منطق تشخیص «در حال بازی»**:

- روی `\$reservation_data` حلقه می‌زند؛ فقط وقتی `status == 'reserved'` است وارد بدنه‌ی HTML می‌شود.
- دوباره داخل حلقه، محصول را از `products_data` می‌خواند و `\$product_obj['duration']` را می‌گیرد.
- شرط کلیدی:
  - اگر `data['time'] < time()` و `time() < data['time'] + duration * 60`
    - یعنی:
      - الان بعد از شروع سانس هستیم.
      - هنوز مدت زمان بازی تمام نشده.
    - در این حالت یک بلاک HTML با ساختار:
      - تصویر بازی، نام سانس، نام رزروکننده، تعداد بلیت‌ها، تگ سطح کاربر و دکمه/label «در حال بازی» تولید می‌شود.

**خروجی**:

- فقط HTML سانس‌های در حال بازی چاپ می‌شود (اگر هیچ سانسی در این بازه نباشد، چیزی چاپ نمی‌شود).
- در پایان `exit;`.

---

### ۳. سناریوی `open_sans`

**هدف**: باز کردن (آزاد کردن) یک سانس بسته؛ یعنی حذف قفل/رکورد مربوط به آن از `wp_zb_booking_history`.

**ورودی‌ها**:

- `sans_time` → تایم‌استمپ سانس.
- `product_id` → شناسه‌ی بازی.

**منطق**:

- `DELETE FROM wp_zb_booking_history WHERE room_id = product_id AND booking_time = sans_time;`
- سپس `\$new_status` را به‌صورت JSON برمی‌گرداند:
  - `new_status: 'closeable'`
  - `success_message: 'با موفقیت باز شد!'`
  - `error_message: ''`

**مصرف در فرانت‌اند**:

- بعد از موفقیت، فرانت‌اند باید وضعیت UI آن سانس را به حالت «باز» (closeable) و دکمه‌ی «باز» تغییر دهد.

---

### ۴. سناریوی `close_sans`

**هدف**: بستن یک سانس (قابل بازگشت) با اعمال چند شرط:

- اگر قبلاً رزرو شده (`status = 1`) → اجازه‌ی بستن ندارد.
- اگر در حالت «رزرو موقت/قفل» دیگران باشد → وضعیت `reserving`.
- در غیر این صورت → رکوردی با `status = 2` برای بستن سانس ثبت می‌کند.

**ورودی‌ها**:

- `sans_time`, `product_id`, و اختیاری `user_id`.

**منطق**:

1. تنظیم فلگ‌ها:
   - `lock_flag` و `reserved_flag` پیش‌فرض false.
   - `locked_sanses` خالی است (در این فایل پر نمی‌شود؛ شاید در آینده از منبع دیگری ست شود).

2. چک قفل‌های دیگر:
   - اگر `locked_sanses` پر باشد و `booking_time == sans_time` → `lock_flag = true`.

3. چک رزرو قطعی:
   - کوئری `SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE product_id AND booking_time LIKE sans_time`.
   - اگر رکوردی پیدا شود → `reserved_flag = true`.

4. تصمیم‌گیری:
   - اگر `reserved_flag == true`:
     - `new_status = 'reserved'` و پیام خطای «یک کاربر این سانس را رزرو کرده است.»
   - else اگر `lock_flag == true`:
     - `new_status = 'reserving'` بدون پیام.
   - else:
     - `INSERT INTO wp_zb_booking_history (... status = 2 ...)` با `customer_id = user_id`, `booking_time = sans_time`, `booked_time = time()`.
     - `new_status = 'openable'` و پیام موفقیت «با موفقیت بسته شد!».

5. بازگشت JSON با `echo json_encode($new_status);`.

---

### ۵. سناریوی `close_all_sanses`

**هدف**: بستن گروهی سانس‌های یک روز برای یک بازی، با رعایت:

- احترام به `auto_disable` (فقط سانس‌هایی که بعد از آن هستند).
- بستن نکردن سانس‌هایی که رزرو قطعی شده‌اند (`status = 1`).

**ورودی‌ها**:

- `day_start_time`, `product_id`, و اختیاری `user_id`.

**مراحل ساخت داده**:

1. خواندن تنظیمات محصول از `products_data`:
   - گرفتن `auto_disable`: `time() + auto_disable * 60`.

2. محاسبه‌ی روز و سانس‌ها:
   - `day_type`, `sanses`، مثل قبل.

3. ساخت `sanses_list` از تایم‌استمپ کل سانس‌های روز.

4. گرفتن وضعیت‌ها از `wp_zb_booking_history`:
   - `SELECT status, booking_time FROM wp_zb_booking_history WHERE room_id LIKE product_id AND booking_time IN (sanses_list)`.
   - ساخت map `order_objs[booking_time] = sans_obj`.

5. حلقه‌ی تصمیم‌گیری:

   - برای هر سانس:
     - `firstTimeTs`، `price` (برای این سناریو استفاده‌ای از `price` نمی‌شود).
     - `order_obj = order_objs[firstTimeTs]`.
     - دو متغیر زمانی `t1` و `t2` برای بازه‌ی 00:00 تا 08:00 همان روز؛ اگر سانس در این بازه است، از منطق auto_disable صرف‌نظر می‌شود.
     - اگر سانس خارج از این بازه بود (`!($t1 < firstTimeTs && $t2 > firstTimeTs)`) و `firstTimeTs >= auto_disable`:
       - اگر `order_obj['status'] == 1` → `reserved_flag = true` (یعنی حداقل یک سانس روز رزرو شده).
       - اگر `order_obj['status']` نه 1 و نه 2 باشد → `ready_to_close[] = firstTimeTs`.

6. بعد از حلقه:

   - اگر `ready_to_close` خالی باشد:
     - `400` و JSON:
       - `"error": "هیچ سانسی برای بسته شدن وجود ندارد."`
   - اگر `reserved_flag == true`:
     - `400` و JSON:
       - `"error": "دست کم یکی از سانس های شما رزرو شده است و نمی توانید همه سانس ها را ببندید."`
   - در غیر این صورت:
     - برای هر `sans_time` در `ready_to_close`:
       - `INSERT INTO wp_zb_booking_history (... status = 2 ...)` (مشابه `close_sans`).
     - سپس:
       - `success: true`
       - `data: ["تمام سانس های درخواستی بسته شد."]`

---

### ۶. سناریوی `game_search`

**هدف**: جستجوی بازی‌ها بر اساس عبارت ورودی و برگرداندن HTML لینک‌دار برای استفاده در UI (مثلاً autocomplete).

**ورودی‌ها**:

- `\$data->data->term` → رشته‌ی جستجو.

**منطق جستجو**:

1. `\$term_parts = explode(' ', $term);`

2. اگر تعداد بخش‌ها ۲ و بخش اول خالی نباشد:

   - برای هر قسمت:
     - `res1 = get_search_result_func_callback(term_parts[0])`
     - `res2 = get_search_result_func_callback(term_parts[1])`
   - سپس:
     - از `res1` و `res2` آرایه‌ای از `product_id`ها می‌سازد (`ids_arr1`, `ids_arr2`) و همین‌طور `products_temp[product_id] = res`.
     - اگر بخش دوم خالی نباشد:
       - `products` = `array_intersect(ids_arr1, ids_arr2)` روی `products_temp`.
     - در غیر این صورت:
       - `products = products_temp`.

3. در غیر این حالت (عبارت یک‌تکه یا بیشتر از دو تکه):

   - `products = get_search_result_func_callback($term);`

**ساخت خروجی HTML**:

- روی `array_slice((array)$products, 0, 50)` حلقه می‌زند (حداکثر ۵۰ نتیجه).
- برای هر `product`:
  - `data = unserialize($product['data']);`
  - `name = $product['title'];`
  - `city = $data['city'];`
  - `image = $home_url . '/wp-content/uploads/' . $data['image'];`
  - `url = "$home_url/room/" . urlencode($product['url']) . "/";` (در HTML استفاده نمی‌شود، ولی آماده است).
  - `\$result .= '<a ...>'` با:
    - `data-id` = `product_id`.
    - تصویر (`<img>`).
    - متن `name (city)`.
- در پایان `echo $result;`.

---

## تحلیل تابع `get_search_result_func_callback`

```php
function get_search_result_func_callback($term)
{
    global $conn;

    $result = $conn->query("SELECT * FROM products_data ");
    if ($result->num_rows > 0)
        while ($row = $result->fetch_assoc())
            $products_data[$row['product_id']] = $row;

    $sorted_product_list = $products_data;

    $products = [];
    foreach ($sorted_product_list as $product) {

        $temp = [];
        if (@strpos($product['title'], $term) !== false) {

            @$temp['product_id']    = $product['product_id'];
            @$temp['type']          = $product['product_type'];
            @$temp['url']           = $product['url'];
            @$temp['title']         = $product['title'];
            @$temp['data']          = serialize(['city' => $product['city_name'], 'image' => $product['image']]);

            $products[] = $temp;
        }
    }

    return $products;
}
```

**نقش تابع**:

- یک لایه‌ی جستجو روی جدول `products_data` فراهم می‌کند که:
  - کل جدول را یک‌جا در حافظه می‌خواند.
  - سپس روی عنوان (`title`) به‌صورت `strpos` (جستجوی substring ساده) فیلتر می‌کند.
  - نتیجه را به شکل آرایه‌ای از آبجکت‌های سبک (با فیلدهای `product_id`, `type`, `url`, `title`, `data`) برمی‌گرداند.

**نکات پیاده‌سازی**:

- از `@` برای suppress کردن خطاها استفاده شده (`@strpos`, `@$temp[...]`)، که می‌تواند خطاهای منطقی را پنهان کند.
- به‌جای فیلتر سمت SQL (مثلاً `WHERE title LIKE '%term%'`) همه‌ی رکوردها را می‌کشد و در PHP فیلتر می‌کند؛ این روی دیتابیس‌های بزرگ می‌تواند هزینه‌ی بالایی داشته باشد ولی کد را ساده نگه می‌دارد.
- فیلد `data` را خودش `serialize` می‌کند تا بعداً در `game_search` دوباره `unserialize` شود.

---

## جمع‌بندی فلو داده

- **از سمت کلاینت (پنل تیم)**:
  - JS در صفحات پنل (`template/team/pages/sans_management.php`, `.../comments.php`) درخواست‌های AJAX به `web-service/team/sans_management.php` با `type`های مختلف می‌فرستد.

- **در این فایل**:
  - بر اساس `\$data->type`:
    - **سانس‌ها را از روی جداول `products_data` و `wp_zb_booking_history` می‌خواند**.
    - وضعیت سانس را `reserved/openable/reserving/closeable` تعیین می‌کند.
    - برای `sans_management_web` و `check_playing` خروجی HTML تولید می‌کند.
    - برای `open_sans`, `close_sans`, `close_all_sanses` رکوردها را در `wp_zb_booking_history` درج/حذف می‌کند و خروجی JSON می‌دهد.
    - برای `game_search` از تابع `get_search_result_func_callback` استفاده می‌کند و HTML نتایج را برمی‌گرداند.

- **در سمت فرانت‌اند**:
  - HTML برگشتی مستقیماً در DOM جایگذاری می‌شود (کارت‌های سانس، باکس «در حال بازی»، لیست نتایج جستجوی بازی).
  - JSON برگشتی برای به‌روزرسانی وضعیت دکمه‌ها و نمایش toast/message موفقیت/خطا استفاده می‌شود.

این مستند کل رفتار فایل `web-service/team/sans_management.php` را در سطح **نقطه‌ی استفاده، نحوه‌ی ساخت داده‌ها و نقش هر سناریو/تابع** پوشش می‌دهد.

