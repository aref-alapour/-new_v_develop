# create_call_me_table.php

**مسیر:** `app/functions/create_call_me_table.php`  
**بارگذاری:** در `app/functions/init.php` بارگذاری **نمی‌شود**. فقط با هوک `after_setup_theme` اجرا می‌شود؛ یعنی تابع در هر بارگذاری صفحه (بعد از setup theme) در دسترس است و اگر فایل جایی require شده باشد، هوک ثبت می‌شود. در حالت فعلی باید از جای دیگری (مثلاً تم یا پلاگین) این فایل را include کرده باشند وگرنه این فایل اصلاً لود نمی‌شود.

---

## نقش کلی

ساخت جدول `{prefix}call_me` برای ذخیره درخواست‌های «تماس با من» (موضوع، شماره تلفن، وضعیت، تاریخ ایجاد).

---

## تابع `create_call_me_table()`

- **ورودی:** ندارد.
- **خروجی:** ندارد.

### جدول: `{prefix}call_me`

| ستون     | نوع        | توضیح          |
|----------|------------|----------------|
| id       | int(11) PK | شناسه          |
| subject  | varchar(255) | موضوع درخواست |
| phone    | varchar(20) | شماره تلفن     |
| status   | varchar(20) | وضعیت (پیش‌فرض: pending) |
| created_at | datetime | زمان ثبت       |

ایندکس: `subject`, `phone`, `status`, `created_at`.

از `wpdb->get_charset_collate()` و `dbDelta` استفاده می‌کند.

---

## زمان اجرا

- **هوک:** `add_action('after_setup_theme', 'create_call_me_table');`  
یعنی در هر بارگذاری، بعد از `after_setup_theme` یک بار اجرا می‌شود. برخلاف `create_cancellation_tables` اینجا option برای «یک بار اجرا» وجود ندارد؛ بنابراین در هر بارگذاری `dbDelta` صدا زده می‌شود. برای محیط پرترافیک بهتر است مشابه cancellation یک option چک شود تا فقط در نصب/به‌روزرسانی schema اجرا شود.

---

## کجا استفاده می‌شود

- **جدول:** توسط فرم/آدرس «تماس با من» یا پنل ادمین برای نمایش/مدیریت درخواست‌های تماس.
- **فایل:** چون در `functions/init.php` نیست، باید در قالب یا پلاگین جایی `require` این فایل شود؛ در غیر این صورت جدول ساخته نمی‌شود مگر اینکه فایل از طریق فایل دیگری لود شده باشد.

---

## تغییر و نگهداری

- اگر می‌خواهید این فایل جزو بارگذاری ثابت app باشد، در `app/functions/init.php` یک خط `include_once "create_call_me_table.php";` اضافه کنید.
- برای جلوگیری از اجرای مکرر dbDelta، داخل تابع چیزی شبیه زیر اضافه کنید:
  - اگر `get_option('call_me_table_created')` بود، return کنید؛ در غیر این صورت بعد از dbDelta، `update_option('call_me_table_created', true);` بگذارید.

---

## بهینه‌سازی

۱. اضافه کردن چک option (مثل cancellation) تا فقط یک بار در نصب/آپدیت schema، dbDelta اجرا شود.  
۲. قرار دادن این فایل در `app/functions/init.php` اگر قرار است جدول call_me همیشه در این تم استفاده شود.
