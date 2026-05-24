# create_cancellation_tables.php

**مسیر:** `app/functions/create_cancellation_tables.php`  
**بارگذاری:** از طریق `app/functions/init.php` (include_once).

---

## نقش کلی

ساخت دو جدول وردپرس برای سیستم «درخواست لغو رزرو» و «لاگ عملیات لغو» در صورت نبودن آن‌ها.

---

## تابع `create_cancellation_tables()`

- **خطوط تقریبی:** ۶–۵۲
- **ورودی:** ندارد.
- **خروجی:** ندارد؛ فقط با `dbDelta` جداول را ایجاد یا به‌روز می‌کند.

### جدول اول: `{prefix}cancellation_requests`

| ستون       | نوع        | توضیح کوتاه        |
|------------|------------|---------------------|
| ID         | int(11) PK | شناسه درخواست      |
| order_id   | int(11)    | شناسه سفارش        |
| product_id | int(11)    | شناسه محصول/بازی   |
| requester_id | int(11)  | شناسه درخواست‌دهنده |
| requester_type | varchar(20) | نوع درخواست‌دهنده (مثلاً customer/shop) |
| reason_id  | int(11)    | دلیل لغو (اختیاری) |
| status     | varchar(20) | وضعیت (پیش‌فرض: pending) |
| sans_time  | int(11)    | زمان سانس (احتمالاً timestamp) |
| created_at | int(11)    | زمان ایجاد         |
| updated_at | int(11)    | زمان به‌روزرسانی (اختیاری) |

ایندکس: `order_id`, `product_id`, `requester_id`, `status`.

### جدول دوم: `{prefix}cancellation_log`

| ستون       | نوع        | توضیح کوتاه   |
|------------|------------|----------------|
| ID         | int(11) PK | شناسه لاگ      |
| request_id | int(11)    | شناسه درخواست  |
| product_id | int(11)    | شناسه محصول    |
| user_id    | int(11)    | کاربر انجام‌دهنده |
| user_role  | varchar(20) | نقش کاربر     |
| action     | varchar(20) | نوع عمل (مثلاً approve/reject) |
| action_time | int(11)   | زمان عمل      |

ایندکس: `request_id`, `product_id`, `user_id`.

---

## زمان اجرا

۱. **بعد از تعویض تم:** `add_action('after_switch_theme', 'create_cancellation_tables');`  
۲. **در init:** یک بار با چک `!get_option('cancellation_tables_created')` اجرا می‌شود و بعد option را true می‌کند تا در هر درخواست دوباره dbDelta اجرا نشود.

---

## کجا استفاده می‌شود

- **مستقیم:** فقط توسط هوک‌های بالا؛ تابع به‌صورت دستی در جای دیگری صدا زده نمی‌شود.
- **جداول:** توسط AJAX/کد مربوط به «لغو رزرو» (مثلاً callbacks مرتبط با cancellation در پنل کاربر یا ادمین) استفاده می‌شوند.

---

## تغییر و نگهداری

- برای تغییر ساختار جدول: دستور SQL داخل تابع را ویرایش کنید و یک بار option را حذف کنید (`delete_option('cancellation_tables_created')`) تا در بارگذاری بعدی دوباره `create_cancellation_tables` و `dbDelta` اجرا شود. توجه: `dbDelta` فقط ستون/ایندکس اضافه یا تغییر می‌دهد؛ برای حذف ستون معمولاً باید مهاجرت دستی بنویسید.
- برای غیرفعال کردن: هوک‌های `after_switch_theme` و `init` را حذف یا کامنت کنید (در این صورت جداول فقط اگر قبلاً ساخته شده باشند باقی می‌مانند).

---

## بهینه‌سازی

- اجرا فقط با چک option در init مناسب است؛ نیازی به تغییر برای بار اول نیست.
- اگر در آینده چند نسخه از schema داشتید، بهتر است یک «نسخه schema» در option ذخیره شود و در صورت افزایش نسخه، فقط مهاجرتهای لازم اجرا شوند.
