## خلاصه‌ی کلی وب‌سرویس `comments-order.php`

این فایل یک **وب‌سرویس PHP** است که برای **پیدا کردن سفارش‌هایی که در بازه‌ی مشخصی از زمان مناسب ارسال پیام/نظر (کامنت)** هستند استفاده می‌شود.  
به‌صورت کلی:

- از کلاینت (فرانت‌اند) یک **درخواست POST** دریافت می‌کند که شامل:
  - یک `time` (تایم‌استمپ ثانیه‌ای، معمولاً «الان» یا نزدیک به الان)،
  - و یک لیست از `product_id`‌ها (اتاق‌ها / بازی‌ها).
- برای هر اتاق، مدت زمان بازی (`duration`) را از `products_data` می‌گیرد.
- در جدول `wp_zb_booking_history` بررسی می‌کند در بازه‌ای حول `time` چه رزروهایی بوده‌اند.
- روی هر رزرو، یک **بازه‌ی مجاز برای ارسال پیام/نظر** تعریف می‌کند:
  - از نیم‌ساعت بعد از شروع سانس (`booking_time + 1800`)
  - تا نیم‌ساعت بعد از پایان سانس (`booking_time + duration*60 + 1800`)
- اگر `time` فعلی در این بازه باشد، اطلاعات سفارش را در خروجی قرار می‌دهد.
- خروجی به‌صورت:
  - **JSON آرایه‌ای از سفارش‌ها** (هر عنصر شامل `order_id`, `order_quantity`, `user_name`, `user_level`, `customer_id`, `room_id`)
  - یا رشته‌ی `'null'` (اگر هیچ سفارش واجد شرایطی پیدا نشود) برمی‌گردد.

این سرویس عملاً به سیستم می‌گوید: «الان، برای کدام سفارش‌ها (در کدام بازی‌ها) زمان مناسبی برای درخواست نظر/پیام از کاربر است؟».

---

## محل استفاده از وب‌سرویس

با جستجو در کد، این وب‌سرویس در فایل زیر استفاده می‌شود:

- `wp-content/themes/escapezoom-v2/woocommerce/myaccount/pages/sans-manager.php`
  - در این صفحه، یک درخواست AJAX به آدرس:
    - `site_url('web-service/comments-order.php')`
  - ارسال می‌شود.
  - این یعنی در صفحه‌ی مدیریت سانس‌ها در حساب کاربری، از این سرویس برای گرفتن سفارش‌هایی که الان «زمان مناسب برای کامنت» دارند استفاده می‌شود (مثلاً برای نمایش لیست سفارش‌هایی که می‌توان به آن‌ها پیامک/نوتیفیکیشن نظرسنجی فرستاد یا در UI کاربر نمایش داد که «برای این سانس می‌توانید نظر ثبت کنید»).

---

## ساختار و پیش‌نیازها در ابتدای فایل

در ابتدای `comments-order.php`:

- **CORS** برای همه‌ی originها باز است:
  - `Access-Control-Allow-Origin: *`
  - هدرهای مجاز و متدهای مجاز (`GET, POST, OPTIONS, PUT, DELETE, PATCH`) تنظیم شده است.
  - برای درخواست‌های `OPTIONS` پاسخ 204 داده و `exit` می‌کند (preflight).

- تنظیمات PHP:
  - `error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);`
  - `date_default_timezone_set("Asia/Tehran");`

- فایل‌های مورد نیاز:
  - `db-connect.php` → برقراری اتصال دیتابیس (`$conn`).
  - `jdf.php` → توابع تاریخ جلالی (در این فایل مستقیماً استفاده نشده، اما برای همسانی load شده).
  - `helper-functions.php` → توابع کمکی عمومی (اینجا مستقیماً استفاده‌ای از آن‌ها نشده).

- کنترل دامنه‌های مجاز:
  - اجازه فقط به:
    - `escapezoom.ir`
    - `escapezoom.co`
    - `bak.escapezoom.ir`
    - `dev-api.escapezoom.ir`
    - `zoom.escapezoom.ir`
    - `goriza.ir`
    - `wo.escapezoom.local`
  - اگر `HTTP_HOST` یکی از این‌ها نباشد:
    - یک رکورد در جدول `hackers` ذخیره می‌کند (`host`, `referer`).
    - متن `Get outta here` را چاپ و اسکریپت را متوقف می‌کند.

---

## نحوه‌ی دریافت و ساخت ورودی‌ها

### ۱. محدودیت روی متد درخواست

- اگر متد درخواست **POST** نباشد:
  - `http_response_code(405)`
  - خروجی: `{"error":"Invalid Request Method"}` (به‌صورت JSON).

### 2. تشخیص نوع محتوا و پارس کردن بدنه

- از `$_SERVER['CONTENT_TYPE']` استفاده می‌کند:
  - اگر حاوی `application/json` باشد:
    - بدنه‌ی خام `php://input` را `json_decode` می‌کند و در `$data` قرار می‌دهد.
  - اگر حاوی `application/x-www-form-urlencoded` باشد:
    - `$_POST` را ابتدا `json_encode` و دوباره `json_decode` می‌کند (تا به صورت object استفاده شود).
  - در غیر این دو حالت:
    - `415 Unsupported Media Type` و خروجی: `{"error":"Unsupported Media Type"}`.

نتیجه: در نهایت اگر موفق باشد، `$data` یک **آبجکت PHP** است که انتظار می‌رود ساختار زیر را داشته باشد:

```json
{
  "data": {
    "time": <int>,
    "product_id": [<int>, <int>, ...]
  }
}
```

---

## منطق اصلی سرویس

کل منطق از خط 48 به بعد درون شرط `if ($data) { ... }` قرار دارد، یعنی فقط زمانی اجرا می‌شود که ورودی معتبر باشد.

### ۱. استخراج پارامترها

- `$request = json_decode(json_encode($data));`
  - این کار `$data` را دوباره به object خالص تبدیل می‌کند؛ در عمل تأثیر زیادی ندارد ولی یکسان‌سازی نوع انجام می‌دهد.

- `\$time = $request->data->time;`
  - این یک **تایم‌استمپ ثانیه‌ای** (احتمالاً `time()` سمت کلاینت یا نزدیک به آن) است.

- `\$product_ids = $request->data->product_id;`
  - این یک **آرایه** از شناسه‌ی محصولات/اتاق‌ها (room_id ها) است.

### ۲. امن‌سازی و آماده‌سازی شناسه‌ها برای کوئری

برای ساختن عبارت `IN` در SQL:

- از `array_map` استفاده می‌کند:

  ```php
  $escaped_ids = array_map(function ($id) use ($conn) {
      return "'" . $conn->real_escape_string($id) . "'";
  }, $product_ids);
  ```

  - هر `id` را با `real_escape_string` امن می‌کند.
  - دور آن `'` می‌گذارد تا به شکل `'123'`، `'456'` درآید.

- سپس:
  - `$ids_list = implode(', ', $escaped_ids);`
  - خروجی مثل: `'101', '102', '103'`.

### ۳. گرفتن اطلاعات مدت زمان هر اتاق (`duration`)

- کوئری:

  ```sql
  SELECT `product_id`,`duration`
  FROM `products_data`
  WHERE `product_id` IN ($ids_list);
  ```

- خروجی با:
  - `$resultRoom = $conn->query($sql);`
  - `$products = $resultRoom->fetch_all(MYSQLI_ASSOC);`

در این‌جا `$products` آرایه‌ای از رکوردها است، هرکدام:

```php
[
  'product_id' => <id>,
  'duration'   => <دقیقه>
]
```

### ۴. حلقه روی اتاق‌ها و پیدا کردن رزروهای مرتبط

ابتدا:

- `$orders = [];` → آرایه‌ی نهایی سفارش‌ها.

اگر `$products` خالی نباشد:

- برای هر `$item` در `$products`:

  ```php
  $id = $item['product_id'];
  $duration = intval($item['duration']);

  $start_time = $time - ($duration * 60);
  $end_time   = $time + ($duration * 60);
  ```

  - **اینجا یک بازه‌ی زمانی خام تعریف می‌کند**:
    - از `now - duration` تا `now + duration`.
    - استفاده‌ی بعدی: پیدا کردن رزروهایی که در حوالی `time` قرار دارند.

  - کوئری روی `wp_zb_booking_history`:

    ```sql
    SELECT `wc_order_id`,`booking_time`,`name`,`quantity`,`level`,`customer_id`
    FROM `wp_zb_booking_history`
    WHERE `room_id` = $id
      AND `booking_time` BETWEEN $start_time AND $end_time
    ```

  - نتیجه:
    - `$resultOrders = $conn->query($sql);`
    - `$ordersResult = $resultOrders->fetch_all(MYSQLI_ASSOC);`

### ۵. پالایش رزروها و تعریف بازه‌ی مجاز برای «کامنت»

اگر `$ordersResult` خالی نباشد:

- برای هر `$item` (رزرو) در `$ordersResult`:

  1. **فیلتر رزروهای ناقص**:

     ```php
     if ($item['wc_order_id'] === null || $item['name'] === null || empty($item['name'])) {
         continue;
     }
     ```

     - سفارش‌هایی که:
       - `wc_order_id` ندارند، یا
       - `name` ندارند،
       - یا نام خالی دارند،
     - **رد می‌شوند**؛ فقط سفارش‌های کامل و معتبر ادامه می‌دهند.

  2. **محاسبه‌ی بازه‌ی مناسب ارسال کامنت**:

     ```php
     $start_comment = intval($item['booking_time']) + 1800;
     $end_commet   = (intval($item['booking_time']) + ($duration * 60)) + 1800;
     ```

     - `booking_time` = زمان شروع سانس (ثانیه).
     - `duration` = مدت بازی به دقیقه.
     - **بازه تعریف شده**:
       - شروع: **۳۰ دقیقه بعد از شروع بازی**.
       - پایان: **۳۰ دقیقه بعد از پایان بازی**.

  3. **تشخیص این‌که الان در بازه‌ی کامنت هستیم یا نه**:

     ```php
     $order = [];
     if ($start_comment < $time and $end_commet > $time) {
         $order['order_id']       = $item['wc_order_id'];
         $order['order_quantity'] = $item['quantity'];
         $order['user_name']      = $item['name'];
         $order['user_level']     = $item['level'];
         $order['customer_id']    = $item['customer_id'];
         $order['room_id']        = $id;
     }
     $orders[] = $order;
     ```

     - اگر `time` فعلی بین `start_comment` و `end_commet` باشد:
       - یعنی:
         - بازی شروع شده، کمی زمان گذشته،
         - بازی باید تمام شده باشد،
         - و هنوز آن ۳۰ دقیقه‌ی بعد از پایان هم تمام نشده؛
       - در این سناریو منطقی است که به کاربر پیام/درخواست نظر بدهیم.

     - `order` با اطلاعات مهم پر می‌شود:
       - `order_id`: شناسه‌ی سفارش ووکامرس.
       - `order_quantity`: تعداد بلیت‌ها.
       - `user_name`: نام مشتری ثبت‌شده در رزرو.
       - `user_level`: سطح کاربر (احتمالاً برای تم/برچسب در UI).
       - `customer_id`: شناسه‌ی کاربر/مشتری.
       - `room_id`: شناسه‌ی بازی/اتاق.

     - نکته: حتی اگر شرط برقرار نباشد، `order` خالی باقی می‌ماند ولی در انتها `orders[] = $order;` انجام می‌شود؛ یعنی ممکن است آرایه‌هایی خالی هم در `$orders` باشد. در عمل مصرف‌کننده باید روی عناصر خالی فیلتر انجام دهد یا از سمت کد این قسمت بهبود یابد (می‌توانست `orders[]` را فقط در صورت پر شدن `order` اضافه کند).

### ۶. خروجی نهایی

بعد از تمام حلقه‌ها:

- اگر `count($orders) > 0`:
  - `echo json_encode($orders);`
  - خروجی: آرایه‌ی JSON از سفارش‌ها (احتمالاً شامل برخی عناصر خالی اگر موارد بالا بهبود داده نشود).
- در غیر این صورت:
  - `echo 'null';`
  - یعنی رشته‌ی ساده‌ی `'null'`، نه JSON `null`؛ مصرف‌کننده باید حواسش به این تفاوت باشد.

---

## جمع‌بندی فنی

- **نقش فایل**: تعیین می‌کند که در یک لحظه‌ی زمانی مشخص (`time`) و برای مجموعه‌ای از اتاق‌ها (`product_id`ها)، کدام رزروها در بازه‌ی زمانی مناسب برای ارسال درخواست نظر/پیام هستند.
- **وابستگی دیتابیس**:
  - جدول `products_data` برای گرفتن `duration` هر اتاق.
  - جدول `wp_zb_booking_history` برای رزروها با فیلدهای:
    - `room_id`, `booking_time`, `wc_order_id`, `name`, `quantity`, `level`, `customer_id`.
  - جدول `hackers` برای لاگ دامنه‌های نامعتبر.
- **ورودی**:
  - POST با JSON یا فرم-encoded حاوی `data.time` و `data.product_id[]`.
- **خروجی**:
  - JSON آرایه‌ای از سفارش‌ها (برای استفاده در JS/UI).
  - یا رشته `'null'` در صورت نبود مورد مناسب.
- **محل استفاده‌ی اصلی**:
  - صفحه‌ی `sans-manager.php` در حساب کاربری ووکامرس، برای کنترل و نمایش سفارش‌هایی که در بازه‌ی پس از بازی، آماده‌ی دریافت پیام/کامنت هستند.

