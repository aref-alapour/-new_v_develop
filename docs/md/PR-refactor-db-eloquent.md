# Pull Request: جایگزینی Medoo با Eloquent (EscapeZoom Core)

**Branch:** `refactor/db-replace-medoo-eloquent` → `master`

---

## Summary
- تمام استفاده‌های **Medoo** در تم escapezoom-v2 (به‌جز پوشه **web-service**) با **Laravel Eloquent** از طریق **EscapeZoom Core** (mu-plugin) جایگزین شد.
- توابع و فایل‌های وابسته به `medoo()` و `medoo_queries()` حذف یا بازنویسی شدند و از `ez_table()`, `ez_external_table()`, مدل `Marketing` و در صورت نیاز `ez_query()` استفاده می‌شود.
- پوشه **web-service** طبق درخواست دست‌نخورده باقی مانده است.

## Why
- یکپارچه‌سازی لایه دیتابیس با **Eloquent** و امکان استفاده از ORM، رابطه‌ها و کوئری‌بیلدر استاندارد.
- حذف وابستگی به Medoo و کاهش کد نگهداری در تم.
- آماده‌سازی برای تسک بعدی (مدل‌سازی دیتابیس و رفع خطاها).

## How
- **Theme:** حذف `inc/medoo/` (Medoo.php و init.php) و هر `require` مربوط به Medoo.
- **ورودی دیتابیس:** استفاده از `ez_table($table)` برای دیتابیس پیش‌فرض وردپرس، `ez_external_table($table)` برای دیتابیس خارجی (مثل products_data)، و مدل `\EscapeZoom\Core\Models\Marketing` برای جدول wp_markting.
- **نقشه‌گذاری:** کوئری‌های Medoo (select/count/update/insert/delete و سینتکس `[~]`, `[>=]`, `[<=]`, `ORDER`, `LIMIT`) به معادل Eloquent (where/whereIn/orderBy/offset/limit/update/insert/delete و like) تبدیل شد.
- **فایل‌های تغییر یافته:** functions.php، پنل کاربری (dashboard، profile)، گزارش‌ها و AJAX تیم (orders، users، marketing، cancellation، comments، transactions و غیره)، گزینه‌های ادمین (orders-log، order-status-log، ads-landing، call-me)، shortcode و قالب call-me-notify، گزارش‌ها (fetch_data)، جستجو و سینک محصول (save-user-search، get-popular-searches، auto-sync-products و مشابه)، و صفحه تست (page-aref-test).
- **حذف:** `template/reports/Medoo.php` و `template/team/ajax/callbacks/users_get_medoo_backup.php`.

## Risk
- ممکن است در برخی سناریوها (مثلاً جداول بدون prefix یا نام متفاوت) نیاز به اصلاح نام جدول یا اتصال باشد.
- رفع خطاهای احتمالی به تسک بعدی موکول شده است.

## Test
- تست دستی صفحات و AJAXهای مرتبط (داشبورد، گزارش‌ها، لغو سانس، جستجو، و غیره) پس از merge پیشنهاد می‌شود.
- اطمینان از فعال بودن **EscapeZoom Core** (mu-plugin) روی محیط تست.

---

**Commit:** `refactor(db): replace Medoo with Eloquent (EscapeZoom Core) except web-service`
