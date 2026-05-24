# رضایت و امتیاز نظرات

## ez_update_product_satisfaction_stats

- **تابع در saeed-codes.php:** `ez_update_product_satisfaction_stats($product_id, $new_value)`
- **جایگزین:** تابع اصلی است؛ از my_approve_comment_callback و لاجیک نظرات صدا زده می‌شود.
- **کارایی:** بعد از ثبت/تأیید نظر، آمار رضایت محصول را به‌روزرسانی می‌کند (احتمالاً در متا یا جدول جدا و sync با web-service).
- **بهینه‌سازی:** (۱) وابستگی به ساختار متا/جدول را در docblock بنویس. (۲) در صورت حجم زیاد نظرات، به‌روزرسانی را غیرهمزمان (async) یا در صف انجام بده.

## ez_update_order_satisfaction

- **تابع در saeed-codes.php:** `ez_update_order_satisfaction($order_id, $new_value)`
- **جایگزین:** تابع اصلی است.
- **کارایی:** رضایت سفارش را با مقدار جدید به‌روزرسانی می‌کند.
- **بهینه‌سازی:** وابستگی به متای سفارش یا جدول را مستند کن؛ در صورت نیاز به چند سفارش، batch کن.

## ez_calculate_product_satisfaction

- **تابع در saeed-codes.php:** `ez_calculate_product_satisfaction($product_id)`
- **جایگزین:** تابع اصلی است.
- **کارایی:** امتیاز رضایت یک محصول را از نظرات محاسبه می‌کند.
- **بهینه‌سازی:** فرمول امتیاز را در یک جا تعریف کن؛ در صورت تعداد زیاد نظرات، محدودیت تاریخ یا نمونه‌گیری در نظر بگیر.

## ez_rebuild_product_satisfaction_stats

- **تابع در saeed-codes.php:** `ez_rebuild_product_satisfaction_stats($product_id)`
- **جایگزین:** تابع اصلی است.
- **کارایی:** بازسازی کامل آمار رضایت یک محصول از صفر (از نظرات).
- **بهینه‌سازی:** برای بازسازی همهٔ محصولات، از batch و صف استفاده کن تا timeout نشود.
