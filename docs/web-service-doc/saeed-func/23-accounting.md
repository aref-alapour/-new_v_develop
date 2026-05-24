# حسابداری و برداشت (مدیر سانس)

## held_status_accounting_management
- **تابع در saeed-codes.php:** `held_status_accounting_management()` — اکشن `admin_menu`
- **جایگزین:** تابع اصلی است.
- **کارایی:** منوی ادمین برای حسابداری وضعیت «held» (سفارش‌های در انتظار) اضافه می‌کند.
- **بهینه‌سازی:** عنوان و capability را از ثابت بخوان؛ فقط برای نقش‌های مجاز منو را نشان بده.

## held_status_accounting_management_ui_func
- **کارایی:** محتوای صفحهٔ آن منو را رندر می‌کند (جدول/لیست سفارش‌های held و اقدامات).
- **بهینه‌سازی:** کوئری را با pagination و فیلتر محدود کن؛ خروجی HTML را به template part ببر.

## ez_calendar_ui_func
- **کارایی:** تقویم رزرو برای محصول را در ادمین رندر می‌کند؛ داده از ez_webservice (ez_calendar) و ez_reservation می‌آید.
- **بهینه‌سازی:** وابستگی به fullCalendar یا کتابخانهٔ مشابه را مستند کن؛ محدودهٔ تاریخ را محدود کن تا بار کم شود.

## update_held_sans_table_func
- **تابع در saeed-codes.php:** `update_held_sans_table_func()` — از topsale و لاجیک held صدا زده می‌شود.
- **کارایی:** جدول held_orders_list را از سفارش‌های با وضعیت مشخص (مثلاً held، completed) و زمان رزرو به‌روزرسانی می‌کند.
- **بهینه‌سازی:** (۱) لیست وضعیت‌ها و محدودهٔ زمانی را در ثابت تعریف کن. (۲) به‌روزرسانی را batch کن تا قفل جدول طولانی نشود. (۳) وابستگی به ez_reservation را مستند کن.

## ez_withdrawal_ui_func / ez_withdrawal_paid_ui_func / ez_withdrawal_rejected_ui_func
- **کارایی:** صفحات ادمین برای لیست برداشت‌ها: در انتظار، پرداخت‌شده، ردشده. هر کدام جدول و دکمه‌های اقدام را رندر می‌کنند.
- **بهینه‌سازی:** کوئری و فیلتر را مشترک در یک تابع کمکی انجام بده؛ pagination و جستجو اضافه کن؛ نام وضعیت‌ها را در ثابت تعریف کن.

## single_schedule_changed
- **تابع در saeed-codes.php:** `single_schedule_changed($product_id, $booking_time, $state)` — اکشنش کامنت شده؛ **بیکار در وضع فعلی.**
- **کارایی:** در صورت فعال‌سازی؛ بعد از تغییر زمان یک سانس، با ez_webservice (single_schedule_products_set) وب‌سرویس را به‌روزرسانی می‌کند.
- **بهینه‌سازی:** اگر دوباره فعال شد، وابستگی به رویداد «تغییر سانس» را در docblock بنویس؛ ارسال را غیرهمزمان در نظر بگیر.
