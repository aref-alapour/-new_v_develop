# تیکتینگ و تماس

## contact_us_declare

- **تابع در saeed-codes.php:** `contact_us_declare()` — اکشن `init`
- **جایگزین:** تابع اصلی؛ ثبت پست‌تایپ یا REST تماس با ما.
- **کارایی:** پست‌تایپ/اندپوینت تماس با ما را ثبت می‌کند.
- **بهینه‌سازی:** نام اسلاگ و قابلیت‌ها را در ثابت‌ها تعریف کن؛ با register_post_type یا register_rest_route سازگار نگه دار.

## ticketing_declare

- **تابع در saeed-codes.php:** `ticketing_declare()` — اکشن `init`
- **جایگزین:** تابع اصلی؛ ثبت پست‌تایپ تیکت.
- **کارایی:** پست‌تایپ تیکت را ثبت می‌کند.
- **بهینه‌سازی:** مثل contact_us_declare؛ آرگومان‌های ثبت را در یک آرایهٔ خوانا نگه دار.

## add_submenu_to_ticketing

- **تابع در saeed-codes.php:** `add_submenu_to_ticketing()` — اکشن `admin_menu`
- **کارایی:** زیرمنوی ادمین برای تیکت اضافه می‌کند.
- **بهینه‌سازی:** عنوان و capability را از ثابت بخوان.

## ticket_monitoring_callback_func

- **تابع در saeed-codes.php:** `ticket_monitoring_callback_func()` — صفحهٔ ادمین تیکت
- **کارایی:** محتوای صفحهٔ نظارت تیکت‌ها را رندر می‌کند.
- **بهینه‌سازی:** خروجی HTML را به template part ببر؛ کوئری لیست تیکت را با pagination محدود کن.

## ticketing_messages_metabox_function / ticketing_messages_metabox_content_function

- **جایگزین:** توابع اصلی؛ متاباکس پیام‌های تیکت.
- **کارایی:** متاباکس را ثبت و محتوای آن را رندر/ذخیره می‌کنند.
- **بهینه‌سازی:** نام متاباکس و nonce را در ثابت تعریف کن؛ ذخیره را با capability چک کن.

## ticket_status_function / user_info_function

- **کارایی:** فیلدهای وضعیت و اطلاعات کاربر در متاباکس تیکت.
- **بهینه‌سازی:** نام فیلدها و متاها را در یک جا تعریف کن.

## program_date_box_save

- **تابع در saeed-codes.php:** `program_date_box_save($ticket_id)` — اکشن `save_post`
- **کارایی:** ذخیرهٔ تاریخ برنامهٔ تیکت در متا.
- **بهینه‌سازی:** nonce و capability چک کن؛ مقدار را sanitize کن.

## ticketing_admin_seen

- **تابع در saeed-codes.php:** `ticketing_admin_seen()` — اکشن `admin_head`
- **کارایی:** علامت‌زدن دیده‌شدن تیکت توسط ادمین (احتمالاً متا یا جدول).
- **بهینه‌سازی:** فقط در صفحهٔ ویرایش تیکت اجرا شود؛ به‌روزرسانی را با AJAX انجام بده تا صفحه رفرش نشود.

## set_custom_edit_teacher_columns / custom_ticket_column

- **کارایی:** ستون‌های سفارشی لیست تیکت در ادمین و پر کردن محتوا.
- **بهینه‌سازی:** نام ستون‌ها را در ثابت تعریف کن؛ برای ستون‌های سنگین از کش استفاده کن.

## ws_sortable_manufacturer_column

- **تابع در saeed-codes.php:** فیلتر sortable columns تیکت
- **کارایی:** ستون‌ها را قابل مرتب‌سازی می‌کند.
- **بهینه‌سازی:** با نام ستون‌های set_custom_edit_teacher_columns هماهنگ نگه دار.

## pending_posts_bubble_wpse_89028

- **تابع در saeed-codes.php:** اکشن `admin_menu`
- **کارایی:** حباب تعداد تیکت در انتظار روی منو.
- **بهینه‌سازی:** کوئری شمارش را به‌صورت کش یا با یک بار در هر بار لود منو انجام بده.

## recursive_array_search_php_91365

- **تابع در saeed-codes.php:** `recursive_array_search_php_91365($needle, $haystack)`
- **جایگزین:** تابع کمکی؛ داخل wpse246143_add_admin_quick_link استفاده می‌شود.
- **کارایی:** جستجوی بازگشتی needle در آرایهٔ چندسطحی haystack.
- **بهینه‌سازی:** در صورت عمق زیاد، محدودیت عمق بگذار تا stack overflow نشود.

## wpse246143_add_admin_quick_link / wpse246143_register_waiting / wpse246143_map_waiting

- **کارایی:** لینک سریع «در انتظار» در لیست تیکت؛ ثبت وضعیت «در انتظار»؛ فیلتر parse_query برای نمایش تیکت‌های در انتظار.
- **بهینه‌سازی:** نام وضعیت (waiting) را در یک جا تعریف کن؛ با taxonomy یا post status سازگار نگه دار.

