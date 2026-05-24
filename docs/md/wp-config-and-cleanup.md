# تنظیمات پیشنهادی wp-config و پاکسازی Auto-Draft

## هدف
کمک به تمیز ماندن دیتابیس و کاهش ردیف‌های زائد در `wp_posts` و `wp_postmeta`.

## تنظیمات پیشنهادی در wp-config.php
توسعه‌دهنده این مقادیر را **خودش** در `wp-config.php` اضافه می‌کند (بدون ویرایش خودکار توسط پلاگین):

```php
define('AUTOSAVE_INTERVAL', 300);
define('WP_POST_REVISIONS', false);   // یا 2
define('EMPTY_TRASH_DAYS', 7);
```

## لایه‌های مهار Auto-Draft در EscapeZoom Core

1. **غیرفعال کردن Heartbeat در ادمین**  
   در موپلاگین `escapezoom-core` اسکریپت `heartbeat` با `wp_deregister_script('heartbeat')` در هوک `admin_enqueue_scripts` غیرفعال می‌شود تا با هر بار باز شدن "Add New" ردیف auto-draft جدید ساخته نشود.

2. **پاکسازی فقط در Job شبانه**  
   هیچ پاکسازی فوری روی `admin_init` انجام نمی‌شود؛ پاکسازی فقط در Job روزانه (لایه ۳) اجرا می‌شود.

3. **CleanupAutoDraftsJob (Action Scheduler)**  
   - Job: `src/Jobs/CleanupAutoDraftsJob.php`  
   - زمان‌بندی: روزانه یک بار (ترجیحاً ساعت ۴ صبح محلی)، گروه `escapezoom`.  
   - منطق: حذف ردیف‌های `wp_posts` با `post_status = 'auto-draft'` و `post_modified` قدیمی‌تر از ۲۴ ساعت؛ قبل از آن حذف ردیف‌های مرتبط در `wp_postmeta`.  
   - در صورت خطا، لاگ در `ez_advance_log` با `action_name = CleanupAutoDraftsJob` و `request_type = job_failure` ثبت می‌شود.

## قانون ۲۲
استفاده از Action Scheduler برای این Job (بدون `wp_schedule_event`).
