# تابع `GetYoastTitle`

## آدرس دقیق

- **فایل:** `wp-content/themes/escapezoom-v2/ahmadreza/init.php`
- **خط:** حدود ۷۱۷ تا ۷۲۹

## امضا

```php
function GetYoastTitle()
```

- بدون پارامتر؛ خروجی string.

## کار دقیق

- اگر صفحه **singular** نباشد، رشته خالی `''` برمی‌گرداند.
- اگر Yoast SEO فعال باشد (`WPSEO_VERSION`)، عنوان سئو را با `WPSEO_Frontend::get_instance()->title('')` برمی‌گرداند.
- وگرنه با `get_the_title()` عنوان پیش‌فرض را برمی‌گرداند.

## محل استفاده در سایت

| فایل | خط (تقریبی) | کاربرد |
|------|-------------|--------|
| ahmadreza/init.php | 791 | در اکشن `wp_head` روی صفحه محصول؛ فیلد `"name"` در اسکیمای JSON-LD نوع Product با `GetYoastTitle()` پر می‌شود. |

## برای تغییر چه کار کنیم

1. **منبع عنوان سئو:** اگر پلاگین سئو عوض شد، به‌جای Yoast از API همان پلاگین استفاده کنید.
2. **صفحات غیر singular:** اگر در آرشیو/صفحه اصلی هم عنوان لازم است، شرط را عوض کنید و منبع مناسب (مثلاً `get_bloginfo('name')`) اضافه کنید.
3. **نام تابع:** برای یکدست‌سازی می‌توان به `ez_get_seo_title()` تغییر داد.

## تابع/کد مشابه در سایت

- Yoast: `WPSEO_Frontend::get_instance()->title('')`؛ این تابع wrapper با چک singular و fallback است.
- **ادغام:** تابع دیگری برای «عنوان سئو» تعریف نشده؛ همین تابع نقطه واحد تغییر است.

## بهینه‌سازی

1. نام با پیشوند تم و سبک یکسان (مثلاً `ez_get_seo_title()`).
2. قبل از فراخوانی Yoast با `class_exists('WPSEO_Frontend')` چک کنید.
3. در انتها `return apply_filters('ez_seo_title', $title);` برای قابلیت تغییر توسط تم/پلاگین.
