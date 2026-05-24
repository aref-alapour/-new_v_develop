# helper/api.php

**مسیر:** `wp-content/themes/escapezoom-v2/app/functions/helper/api.php`  
**بارگذاری:** در `app/functions/init.php` **include نمی‌شود**. احتمالاً از جای دیگری (مثلاً web-service یا تم) با `require`/`include` لود می‌شود.

---

## خلاصه

شامل توابع کمکی عمومی (سفارش، تیکت، موبایل، احراز هویت، دامنه)، دو تابع تکراری با `get_order_ids.php` و `reply-comments.php`، و کلاس **Escapezoom_Checkout** برای پردازش چکاوت ووکامرس (ایجاد سفارش، خطوط، پرداخت/بدون پرداخت).

---

## فهرست توابع و کلاس (با خط تقریبی)

| نام | خطوط تقریبی | توضیح کوتاه |
|-----|--------------|--------------|
| `get_orders_ids_by_product_id` | ۴–۶۴ | همان منطق `get_order_ids.php` با تفاوت جزئی در کوئری (GROUP BY و total) |
| `get_owner_id_by_product_id` | ۶۶–۶۸ | برگرداندن `user_ebtal` از post meta |
| `add_new_point` | ۷۰–۷۵ | درج رکورد در جدول `points`؛ در user_level_system/functions.php هم تعریف شده |
| `ticket_verify` | ۷۷–۸۴ | بررسی author تیکت با user |
| `get_ticket_status` | ۸۶–۱۰۱ | وضعیت تیکت: closed / respond / pending / open بر اساس متا |
| `ez_validate_mobile` | ۱۰۳–۱۱۸ | اعتبارسنجی شماره موبایل ایران (۰۹ یا ۹)، برگشت بدون صفر اول |
| `ez_verify_user_by` | ۱۲۰–۱۴۷ | احراز با username و verify_code (otp) یا password |
| `ez_get_domain` | ۱۴۹–۱۵۶ | دامنه سایت بدون http(s) |
| `get_user_points` | ۱۵۸–۱۶۳ | جمع امتیاز کاربر از جدول points — **باگ:** استفاده از `LIKE` و عدم استفاده صحیح از prepare |
| `get_post_reply_comments` | ۱۶۵–۱۹۵ | ریپلای تو در تو؛ **تکراری** با reply-comments.php با تفاوت در author_title (اینجا display_name) |
| کلاس `Escapezoom_Checkout` | ۲۰۵–۷۸۰ | چکاوت ووکامرس: فیلدها، ایجاد سفارش، خطوط، اعتبارسنجی، پرداخت |

---

## تداخل و کد تکراری

- **get_orders_ids_by_product_id:** در `get_order_ids.php` هم هست؛ تفاوت در بخش `$total` (اینجا زیرکوئری با GROUP BY دارد). بهتر است فقط یک نسخه نگه داشته شود و از یک فایل استفاده شود.
- **get_post_reply_comments:** در `reply-comments.php` هم تعریف شده؛ نسخه api از `get_user_by()->data->display_name` برای author استفاده می‌کند، نسخه helper از `comment_author`. یکسان‌سازی در یک فایل (ترجیحاً helper) و حذف نسخه دوم توصیه می‌شود.
- **add_new_point:** در `user_level_system/functions.php` هم هست؛ یک تابع مشترک کافی است.

---

## باگ امنیتی/پایایی در get_user_points (این فایل)

```php
$wpdb->get_results( $wpdb->prepare( "SELECT SUM(point) as total FROM points WHERE user_id LIKE {$user_id}" ) )
```

- `{$user_id}` داخل رشته به prepare اضافه نشده و LIKE برای عدد مناسب نیست. نسخه صحیح در `helper/get_user_points.php` با `%d` و بدون LIKE است. این تابع در api.php را یا حذف کنید یا با نسخه helper جایگزین کنید.

---

## کلاس Escapezoom_Checkout

- **نقش:** جایگزین/کپی منطق چکاوت WC؛ ایجاد سفارش، خطوط محصول/فی/حمل/مالیات/کوپن، اعتبارسنجی، پرداخت یا بدون پرداخت.
- **استفاده:** با `Escapezoom_Checkout::instance()` و متدهایی مثل `process_checkout()`, `create_order()`, `get_value()`.
- **متد مهم:** `process_checkout()` در انتها به `process_order_payment` یا `process_order_without_payment` می‌رسد و خروجی را برمی‌گرداند (بدون ارسال JSON یا ریدایرکت در این نسخه).

اگر این کلاس فقط از یک نقطه (مثلاً web-service) استفاده می‌شود، بهتر است بارگذاری api.php فقط در همان مسیر باشد تا در تم اصلی بدون نیاز لود نشود.

---

## استفاده در سایت

- توابعی مثل `ez_validate_mobile`, `ez_verify_user_by`, `ez_get_domain` برای لاگین/ثبت‌نام/احراز.
- تیکت: `ticket_verify`, `get_ticket_status`.
- گزارش/پنل: `get_orders_ids_by_product_id`, `get_owner_id_by_product_id`.
- چکاوت: کلاس Escapezoom_Checkout.

---

## نحوه تغییر

- برای تغییر منطق چکاوت: متدهای کلاس Escapezoom_Checkout (مثلاً `create_order`, `validate_checkout`, `process_customer`) را در همین فایل یا با ارث‌بری ویرایش کنید.
- برای یکسان‌سازی امتیاز: یا فقط از `add_point` (add-point.php) و `add_new_point` یک جا استفاده کنید و بقیه را حذف کنید.
- حذف توابع تکراری و استفاده از helperهای موجود (get_order_ids, reply-comments, get_user_points) باعث تمیزتر شدن و کاهش باگ می‌شود.

---

## بهینه‌سازی پیشنهادی

1. این فایل را در جایی که واقعاً لازم است include کنید و در init تم قرار ندهید تا بار اضافی نداشته باشید.
2. توابع تکراری را حذف و به helperهای موجود ارجاع دهید.
3. باگ `get_user_points` را با نسخه `helper/get_user_points.php` جایگزین یا حذف کنید.
4. در صورت استفاده طولانی‌مدت از Escapezoom_Checkout، در نظر بگیرید آن را به یک فایل جدا (مثلاً class-escapezoom-checkout.php) منتقل کنید.
