# آنالیز ورودی پوشه app

## ۱. app/init.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/init.php`

**نقش:** نقطه ورود پوشه app؛ فقط دو فایل را لود می‌کند.

**محتوای واقعی:**
- `require_once Theme_PATH . "app/ajax/init.php";`
- `require_once Theme_PATH . "app/functions/init.php";`

**کجا استفاده می‌شود:** تم escapezoom-v2 احتمالاً در `functions.php` یا فایل بارگذاری تم، این فایل را با `require`/`require_once` فراخوانی می‌کند تا AJAX و توابع کمکی فعال شوند.

**تغییر:** برای اضافه کردن ماژول جدید به app، یا همینجا یک خط `require_once` اضافه کنید یا داخل `app/functions/init.php` / `app/ajax/init.php` منطق جدید را بارگذاری کنید.

**بهینه‌سازی:** همین ساختار ساده کافی است؛ نیازی به تغییر خاصی نیست.

---

## ۲. app/ajax/init.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/ajax/init.php`

**نقش:** ثبت یک هندلر واحد برای تمام درخواست‌های AJAX تم؛ بر اساس پارامتر `callback` فایل مربوط از پوشه `callbacks` را بارگذاری می‌کند.

### اکشن‌ها
- `wp_ajax_v2_ajax_handler` — کاربر لاگین‌شده
- `wp_ajax_nopriv_v2_ajax_handler` — مهمان

هر دو به تابع `v2_ajax_handler_callback` وصل هستند.

### تابع `v2_ajax_handler_callback()`

۱. **Headers ضد کش:** در صورت ارسال نشدن header، `Cache-Control`, `Pragma`, `Expires` برای جلوگیری از کش پاسخ AJAX تنظیم می‌شوند.
۲. **LiteSpeed Cache:** اگر ثابت `LSCACHE_NO_CACHE` تعریف شده باشد، اکشن `litespeed_control_set_nocache` برای این درخواست فراخوانی می‌شود.
۳. **Nonce:** خط `check_ajax_referer` کامنت شده؛ یعنی در حال حاضر اعتبارسنجی nonce انجام نمی‌شود (ریسک امنیتی).
۴. **بارگذاری callback:**
   - `$callback_file = Theme_PATH . "app/ajax/callbacks/" . $_POST['callback'] . '.php'`
   - اگر فایل وجود داشته باشد: `require_once $callback_file`
   - در پایان: `wp_die()`

**مشکل امنیتی:** مقدار `$_POST['callback']` مستقیم در مسیر فایل استفاده می‌شود. اگر ورودی اعتبارسنجی نشود، احتمال Path Traversal یا بارگذاری فایل دلخواه وجود دارد. باید `callback` را به یک لیست مجاز (مثلاً فقط نام فایل بدون `/` و `..`) محدود کرد.

**کجا استفاده می‌شود:** هر درخواست AJAX فرانت که با action `v2_ajax_handler` و پارامتر `callback` (مثلاً `panel_orders_get`) ارسال شود، به این هندلر می‌رسد و فایل همان نام از `app/ajax/callbacks/` اجرا می‌شود.

**تغییر:** برای اضافه کردن یک endpoint جدید، کافی است فایل جدیدی در `app/ajax/callbacks` با نام دلخواه (مثلاً `my_action.php`) بسازید و از فرانت با `callback=my_action` صدا بزنید. برای حذف یک endpoint، فایل مربوط از callbacks را حذف کنید.

**بهینه‌سازی پیشنهادی:**
- حتماً nonce را با `check_ajax_referer('v2-ajax-nonce', 'nonce')` فعال کنید.
- `$_POST['callback']` را با `sanitize_file_name()` یا یک لیست سفید (whitelist) اعتبارسنجی کنید و فقط در صورت مجاز بودن، مسیر را بسازید.

---

## ۳. app/functions/init.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/functions/init.php`

**نقش:** بارگذاری تمام helperها و فایل ایجاد جداول لغو رزرو.

**فایل‌های بارگذاری‌شده (به ترتیب):**
1. `helper/product-options.php`
2. `helper/reply-comments.php`
3. `helper/get_order_ids.php`
4. `helper/get_user_points.php`
5. `helper/set-timestamp.php`
6. `helper/text-align.php`
7. `helper/cities_type.php`
8. `helper/add-point.php`
9. `helper/custom_product-tag-image_field.php`
10. `helper/user_level_system/actions-points.php`
11. `helper/user_level_system/functions.php`
12. `create_cancellation_tables.php`

**فایل‌های داخل app/functions که لود نمی‌شوند:**
- `create_call_me_table.php` — در init نیست؛ اگر جایی require نشود، جدول call_me فقط با هوک `after_setup_theme` ساخته می‌شود (در اولین بارگذاری تم).
- `helper/api.php` — در init نیست؛ توابع/کلاس داخل آن فقط در صورتی در دسترس هستند که جای دیگری این فایل را لود کرده باشد.

**کجا استفاده می‌شود:** بلافاصله بعد از لود `app/init.php`؛ پس تمام قالب و فایل‌هایی که بعد از تم لود می‌شوند به توابع این helperها دسترسی دارند.

**تغییر:** برای اضافه کردن helper جدید، یک خط `include_once "helper/نام فایل.php";` اضافه کنید. برای حذف، خط مربوط را حذف کنید (و وابستگی‌های آن را در قالب/پلاگین بررسی کنید).

**بهینه‌سازی:** می‌توان به‌جای چندین `include_once` جدا، در محیط production از یک autoload یا لیست واحد نگهداری کرد؛ برای تم کوچک/متوسط همین ترتیب و لیست صریح هم قابل قبول است.
