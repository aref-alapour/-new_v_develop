# مستندات پوشه ahmadreza (تم escapezoom-v2)

این سند تمام فایل‌ها و توابع داخل `wp-content/themes/escapezoom-v2/ahmadreza` را شرح می‌دهد، باگ‌های شناخته‌شده را ذکر می‌کند و پیشنهاد بهینه‌سازی ارائه می‌دهد.

**نقطه بارگذاری:** فایل `ahmadreza/init.php` از `functions.php` تم (حدود خط ۵۱۷) با `require` یا `include` فراخوانی می‌شود.

---

## ۱. ساختار پوشه

```
ahmadreza/
├── init.php              # بوت‌استرپ اصلی، هوک‌ها، رژستر شورتکدها
├── jdate.php             # توابع تاریخ شمسی (جلالی)
├── acf/
│   └── product-category-fields.php  # تعریف گروه فیلدهای ACF
├── admin/
│   ├── index.php         # خالی (فقط <?php)
│   └── init.php          # خالی
├── app/
│   ├── index.php         # خالی
│   └── init.php          # include هلپرهای تم اصلی
├── template/
│   ├── index.php         # خالی
│   └── init.php         # include یک صفحه از تم اصلی
└── shortcodes/
    ├── single-product-days.php
    ├── home-trend-rooms.php
    ├── home-discounts-event.php
    ├── home-scary-cinema.php
    ├── city-escape-rooms.php
    └── home-cities-lasertag.php
```

---

## ۲. فایل init.php (ریشه ahmadreza)

### ۲.۱ توابع تعریف‌شده

| تابع | کاربرد |
|------|--------|
| `write_log($data)` | در حالت `WP_DEBUG` داده را با `error_log` چاپ می‌کند (آرایه/شی با `print_r`). |
| `has_role(...$roles): bool` | بررسی می‌کند نقش اولین نقش کاربر جاری در `$roles` باشد. |
| `is_wc_login_page(): bool` | ترکیب `is_account_page() && !is_user_logged_in()`. |
| `get_replies($items): void` | چاپ بازگشتی لیست کامنت‌ها با قالب HTML (برای درخت پاسخ). |
| `GetYoastTitle()` | در صفحه تکی، عنوان Yoast یا در غیر این صورت `get_the_title()`. |
| `change_schema_date_published($data)` | در فیلترهای Yoast، `datePublished` را با تاریخ انتشار پست تنظیم می‌کند. |

### ۲.۲ بارگذاری فایل‌های جانبی

- **jdate:** اگر تابع `jdate` وجود نداشته باشد، `jdate.php` بارگذاری می‌شود.
- **ACF:** همه فایل‌های `ahmadreza/acf/*.php` با `glob` بارگذاری می‌شوند.
- **Shortcodes:** همه فایل‌های `ahmadreza/shortcodes/*.php` با `require_once` بارگذاری می‌شوند.

### ۲.۳ اکشن‌ها و فیلترهای مهم

- **after_setup_theme:** پشتیبانی `title-tag`.
- **after_switch_theme:** `flush_rewrite_rules` و آپدیت آپشن.
- **woocommerce_account_content:** حذف اکشن پیش‌فرض و جایگزینی با تمپلیت سفارشی بر اساس `$wp->query_vars` و `wc_get_template('myaccount/pages/...')`.
- **init:**  
  - رژستر اندپوینت‌های اکانت: sans-manager, sells, wallet, notices, credit, sans-settings, offers, products, orders, comments, invitation, my-collections, settings, points, cancellation-requests, cancellation-history.  
  - قانون رورایت: `profile/([a-z0-9-]+)`, `r/([^/]*)`, `t/([^/]*)`.  
  - کوئری وارها: profile, reserve, ticket.  
  - شروع session در صورت نبود، و `ob_start`.  
  - ریدایرکت در صورت وجود `comment_page`, `e`, `_g`.  
  - رژستر CPT با نام `notification`.
- **woocommerce_account_menu_items:** سفارشی‌سازی آیتم‌های منوی اکانت بر اساس نقش (مثل customer، sans_manager) و آیتم‌های اضافی (notices, credit, cancellation-requests و ...).
- **woocommerce_account_menu_item_classes:** چندین بار (پریوریتی‌های مختلف) برای دادن کلاس `is-active` به آیتم «درخواست‌ها» وقتی صفحه `cancellation-history` باز است — **تکراری و قابل ادغام در یک فیلتر**.
- **wp_enqueue_scripts:** رژستر/انکیو اسکریپت و استایل (select2، persian-datepicker، lightbox، tippy، qrcode، custom-styles و ...).
- **template_include:** برای `profile`، `reserve`، `ticket` تمپلیت مخصوص یا ۴۰۴.
- **get_avatar:** جایگزینی آواتار با تصویر سفارشی از user meta یا پیش‌فرض.
- **woocommerce_enqueue_styles:** غیرفعال کردن استایل‌های ووکامرس با `__return_empty_array`.
- **woocommerce_checkout_fields:** حذف/تغییر فیلدهای چک‌اوت و افزودن فیلدهای بازیکنان و تایم رزرو.
- **woocommerce_order_is_paid_statuses:** اضافه کردن وضعیت‌های `held` و `walletx`.
- **query_vars:** اضافه کردن profile, reserve, ticket.
- **wp_head:** در صفحه محصول، خروجی اسکیمای JSON-LD (Product، امتیاز، تاریخ، نوع محصول).
- **save_post_notification / before_delete_post:** همگام‌سازی با جدول `notifications` در دیتابیس.

### ۲.۴ شورتکد تعریف‌شده در init.php

- **esadv:** نمایش یک محصول با تصویر، لینک، عنوان و توضیح کوتاه (پارامترهای `id`, `desc`). خروجی با `ob_start` / `ob_get_clean` برگردانده می‌شود.

### ۲.۵ نکات و بهینه‌سازی

- فیلتر `woocommerce_account_menu_item_classes` برای cancellation-requests چندین بار با پریوریتی‌های ۵، ۱۰، ۱۵، ۲۰، ۲۵، ۳۰، ۳۵، ۴۰، ۴۵، ۵۰، ۱۰۰ تکرار شده؛ کافی است **یک بار** با یک پریوریتی مناسب اجرا شود و کلاس `is-active` در همان یک callback تنظیم شود.
- تابع `write_log` با نام عمومی است؛ بهتر است با پیشوند نام تم یا ماژول نام‌گذاری شود تا تداخل با پلاگین‌ها کم شود.
- `GetYoastTitle` با نامگذاری camelCase و بدون پیشوند در فضای سراسری است؛ در صورت تمایل می‌توان به یک تابع با پیشوند تم منتقل شد.

---

## ۳. فایل jdate.php

کتابخانه تاریخ شمسی (جلالی) با توابع زیر:

| تابع | کاربرد |
|------|--------|
| `jdate($format, $timestamp, $none, $time_zone, $tr_num)` | خروجی تاریخ شمسی با فرمت مشابه `date()`. |
| `jstrftime($format, $timestamp, ...)` | مشابه `strftime` با فرمت جلالی. |
| `jmktime($h, $m, $s, $jm, $jd, $jy, ...)` | ساخت timestamp از اجزای زمان و تاریخ شمسی. |
| `jgetdate($timestamp, ...)` | آرایه‌ای شبیه `getdate()` برای تاریخ شمسی. |
| `jcheckdate($jm, $jd, $jy)` | اعتبارسنجی تاریخ شمسی. |
| `tr_num($str, $mod, $mf)` | تبدیل اعداد انگلیسی/فارسی. |
| `jdate_words($array, $mod)` | تبدیل عدد به حروف (ماه، روز، سال و ...) برای جلالی. |
| `gregorian_to_jalali($gy, $gm, $gd, $mod)` | میلادی به شمسی. |
| `jalali_to_gregorian($jy, $jm, $jd, $mod)` | شمسی به میلادی. |

**وابستگی:** فقط توابع داخلی PHP و خود همین فایل.  
**استفاده در ahmadreza:** در شورتکد `single-product-days` از `jdate('d', $date)` و `jdate('l', $date)` استفاده شده است.

---

## ۴. acf/product-category-fields.php

در هوک `acf/include_fields` یک گروه فیلد ACF با عنوان «اطلاعات دسته بندی» رژستر می‌شود:

- **موقعیت:** برای تاکسونومی `product_cat`، `product_tag` و پست‌تایپ `page`.
- **فیلدها:**
  - **slider (repeater):** تصویر دسکتاپ، تصویر موبایل، لینک، عنوان.
  - **short-description:** textarea.
  - **faq (repeater):** عنوان، توضیحات (wysiwyg).
  - **video:** متن (لینک ویدئو).
  - **icon:** تصویر.

وابستگی: فقط پلاگین ACF و تابع `acf_add_local_field_group`.

---

## ۵. admin و app و template

- **admin/index.php و admin/init.php:** خالی یا فقط تگ باز PHP؛ از init.php اصلی ahmadreza فراخوانی نمی‌شوند.
- **app/init.php:**  
  `include_once Theme_PATH."app/functions/helper/product-options.php"` و `include_once Theme_PATH."app/functions/helper/api.php"`.  
  این فایل از init.php ریشه ahmadreza بارگذاری **نمی‌شود**؛ در صورت نیاز باید در جایی (مثلاً functions.php تم) include شود.
- **template/init.php:**  
  `include_once Theme_PATH."template/pages/my-collections.php"`.  
  همین‌طور از init.php ریشه ahmadreza بارگذاری نمی‌شود.

یعنی در حال حاضر فقط **init.php** و **jdate.php** و **acf/*.php** و **shortcodes/*.php** در بارگذاری مستقیم ahmadreza نقش دارند.

---

## ۶. شورتکدها

### ۶.۱ single-product-days

- **فایل:** `shortcodes/single-product-days.php`
- **نام شورتکد:** `single-product-days`
- **عملکرد:** نمایش انتخاب روز برای رزرو (امروز + ۱۵ روز بعد) با دکمه‌های «روز قبل» / «روز بعد» و اسلاید تاریخ‌ها. از `jdate('d', $date)` و `jdate('l', $date)` استفاده می‌کند.
- **اصلاح انجام‌شده:** خروجی با `ob_start` جمع و با `return ob_get_clean()` برگردانده می‌شود تا شورتکد به‌درستی خروجی داشته باشد.

### ۶.۲ home-trend-rooms

- **فایل:** `shortcodes/home-trend-rooms.php`
- **نام شورتکد:** `home-trend-rooms`
- **عملکرد:** بخش «اتاق فرارهای ترند» با فیلتر امروز/فردا/پس‌فردا. از `getStartAndEndTimestamps` برای امروز، فردا و پس‌فردا استفاده می‌کند. داده از `ez_webservice` با `source: home_trends` و `sort_products_get` گرفته می‌شود. خروجی با `ob_start` / `ob_get_clean` برگردانده می‌شود.
- **وابستگی:** تابع `getStartAndEndTimestamps` از تم (مثلاً `app/functions/helper/set-timestamp.php`) باید در همان درخواست بارگذاری شده باشد.

### ۶.۳ home-discounts-event (باگ)

- **فایل:** `shortcodes/home-discounts-event.php`
- **نام شورتکد:** `home-discounts-event`
- **عملکرد:** بخش «تخفیف داغ هفته» با فیلتر امروز/فردا/پس‌فردا و اسلایدر محصولات از `ez_webservice` با `source: home_discounts_event`.
- **باگ:** متغیرهای `$todayStart`, `$todayEnd`, `$tomorrowStart`, `$tomorrowEnd`, `$dayAfterTomorrowStart`, `$dayAfterTomorrowEnd` در HTML استفاده شده‌اند اما **هیچ‌جا تعریف نشده‌اند** (فراخوانی `getStartAndEndTimestamps` برای امروز، فردا و پس‌فردا وجود ندارد). این باعث Notice و مقادیر خالی در دکمه‌های فیلتر می‌شود.
- **اصلاح پیشنهادی:** در ابتدای تابع شورتکد، مشابه `home-trend-rooms.php`، سه جفت مقدار برای امروز، فردا و پس‌فردا با `getStartAndEndTimestamps` محاسبه شوند.

### ۶.۴ home-scary-cinema (باگ)

- **فایل:** `shortcodes/home-scary-cinema.php`
- **نام شورتکد:** `home-scary-cinema`
- **عملکرد:** بخش «سینما ترس‌های تهران» با فیلتر شهر و نوع مرتب‌سازی؛ داده از `ez_webservice` با `source: home_cities_cinema` و `city_id: [913]` و غیره.
- **باگ:** بلافاصله بعد از `?>` متن `ob_start();` به صورت خروجی HTML چاپ می‌شود و سپس تگ `?>` بعدی شروع بلوک PHP است. یعنی `ob_start()` هرگز اجرا نمی‌شود و در انتها `ob_get_clean()` خروجی واقعی اسلایدر را برنمی‌گرداند و ممکن است رشته «ob_start();» در صفحه دیده شود.
- **اصلاح پیشنهادی:** حذف `?>` قبل از `ob_start();` و قرار دادن فقط `ob_start(); ?>` تا قبل از اولین خروجی HTML، بافر با `ob_start()` شروع شود.

### ۶.۵ city-escape-rooms (باگ)

- **فایل:** `shortcodes/city-escape-rooms.php`
- **نام شورتکد:** `city-escape-rooms`
- **عملکرد:** بخش «اتاق فرارهای تهران» با فیلتر شهر، سبک بازی و نوع مرتب‌سازی؛ داده از `ez_webservice` با `source: home_cities_escaperoom`.
- **باگ:** قبل از اولین خروجی HTML هیچ `ob_start()` فراخوانی نشده؛ فقط در انتها `return ob_get_clean();` وجود دارد. در این حالت `ob_get_clean()` یا خروجی قبلی بافر سطح بالاتر را برمی‌گرداند یا رفتار نامشخص دارد و خروجی این شورتکد ممکن است خالی یا نادرست باشد.
- **اصلاح پیشنهادی:** درست بعد از تعریف `$args` و گرفتن `$cities_rooms`، قبل از هر گونه `echo` یا خروجی HTML، یک بار `ob_start();` فراخوانی شود.

### ۶.۶ home-cities-lasertag

- **فایل:** `shortcodes/home-cities-lasertag.php`
- **نام شورتکد:** `home-cities-lasertag`
- **عملکرد:** بخش «لیزرتگ‌های کرج» با فیلتر شهر و نوع مرتب‌سازی؛ داده از `ez_webservice` با `source: home_cities_lasertag`. استفاده از `ob_start` و `return ob_get_clean()` درست است.

---

## ۷. خلاصه باگ‌ها و اصلاحات (انجام‌شده)

| فایل | باگ | وضعیت |
|------|-----|--------|
| home-discounts-event.php | تعریف نشدن `$todayStart`, `$todayEnd`, ... | اصلاح شد: محاسبه امروز/فردا/پس‌فردا با `getStartAndEndTimestamps` در ابتدای تابع اضافه شد. |
| home-scary-cinema.php | `?> ob_start(); ?>` باعث چاپ متن و عدم اجرای ob_start | اصلاح شد: `ob_start();` قبل از خروجی HTML قرار گرفت. |
| city-escape-rooms.php | نبود `ob_start()` قبل از خروجی | اصلاح شد: `ob_start();` قبل از اولین خروجی HTML اضافه شد. |
| single-product-days.php | عدم return خروجی شورتکد | اصلاح شد: خروجی با ob_start/ob_get_clean جمع و return می‌شود. |

---

## ۸. وابستگی‌های خارجی

- **تم اصلی:** ثابت‌های `Theme_PATH`, `Theme_URL` (و در صورت استفاده از app/init و template/init، مسیرهای تم).
- **ووکامرس:** قالب‌های myaccount، چک‌اوت، منوی اکانت.
- **ACF:** برای فیلدهای دسته‌بندی و صفحه.
- **تابع کمکی تم:** `getStartAndEndTimestamps` در `app/functions/helper/set-timestamp.php` (برای شورتکدهایی که فیلتر روز دارند).
- **سرویس وب:** `ez_webservice()` برای دریافت لیست محصولات در شورتکدهای ترند، تخفیف، سینما ترس، اتاق فرار شهر، لیزرتگ.

---

## ۹. پیشنهاد نگهداری

1. اصلاح سه باگ شورتکد (home-discounts-event، home-scary-cinema، city-escape-rooms) و در صورت تمایل اصلاح return در single-product-days.
2. ادغام تمام فیلترهای `woocommerce_account_menu_item_classes` مربوط به cancellation-requests در یک فیلتر با یک پریوریتی.
3. در صورت استفاده واقعی از `app/init.php` و `template/init.php`، اطمینان از بارگذاری آن‌ها از یک نقطه مرکزی (مثلاً functions.php یا init.php ریشه ahmadreza).
4. نام‌گذاری توابع سراسری (مثل `write_log`, `GetYoastTitle`) با پیشوند تم یا ماژول برای جلوگیری از تداخل با پلاگین‌ها و تم‌های دیگر.

اگر بخواهید، می‌توانم متن دقیق پچ‌های پیشنهادی (diff) برای هر یک از این فایل‌ها را هم بنویسم.
