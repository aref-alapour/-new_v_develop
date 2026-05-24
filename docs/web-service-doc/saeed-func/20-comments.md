# نظرات و امتیاز

## comment_privacy_system_token_verify_redirect
- **تابع در saeed-codes.php:** `comment_privacy_system_token_verify_redirect()` — اکشن `wp`
- **جایگزین:** تابع اصلی است.
- **کارایی:** توکن حریم خصوصی نظر را چک می‌کند و در صورت نامعتبر بودن ریدایرکت یا جلوگیری از ارسال انجام می‌دهد.
- **بهینه‌سازی:** وابستگی به پارامتر/کوکی را مستند کن؛ در صورت نامعتبر بودن پیام یکسان به کاربر نشان بده.

## rating_in_details_admin_metabox / rating_in_details_admin_html / saving_rating_in_details_admin
- **کارایی:** متاباکس امتیاز (ستاره) در صفحهٔ ویرایش نظر در ادمین؛ نمایش و ذخیره.
- **بهینه‌سازی:** نام متای comment_rating را ثابت کن؛ مقدار را به بازهٔ مجاز محدود کن.

## my_approve_comment_callback
- **تابع در saeed-codes.php:** `my_approve_comment_callback($new_status, $old_status, $comment)` — اکشن `transition_comment_status`
- **جایگزین:** تابع اصلی است.
- **کارایی:** وقتی نظر از حالت غیرتأیید به تأیید تغییر می‌کند؛ آمار امتیاز محصول و رضایت را به‌روزرسانی می‌کند (ez_update_product_satisfaction_stats و غیره) و احتمالاً SMS یا sync با web-service.
- **بهینه‌سازی:** (۱) مراحل را در توابع جدا انجام بده. (۲) فقط وقتی old != approved و new == approved اجرا شود. (۳) کارهای سنگین را در صف بگذار.

## ez_remove_product_comment
- **تابع در saeed-codes.php:** `ez_remove_product_comment($comment_id)` — اکشن `trash_comment`
- **کارایی:** هنگام حذف نظر؛ آمار محصول (تعداد نظرات، امتیاز) را به‌روزرسانی می‌کند.
- **بهینه‌سازی:** وابستگی به متای محصول را مستند کن؛ در صورت خطا لاگ کن.

## comment_reminder_sms_process
- **تابع در saeed-codes.php:** `comment_reminder_sms_process()` — کرون
- **کارایی:** برای سفارش‌هایی که کاربر هنوز نظر نداده، پیامک یادآور می‌فرستد (با add_to_sms_queue و لاجیک if_user_commented).
- **بهینه‌سازی:** محدودهٔ زمانی (چند روز بعد از خرید) و تعداد حداکثر یادآور را قابل تنظیم کن؛ ارسال را batch کن تا timeout نشود.
