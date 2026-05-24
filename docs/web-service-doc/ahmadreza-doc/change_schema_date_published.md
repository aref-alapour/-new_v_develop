# تابع `change_schema_date_published`

## آدرس دقیق

- **فایل:** `wp-content/themes/escapezoom-v2/ahmadreza/init.php`
- **خط:** حدود ۸۱۸ تا ۸۲۲

## امضا

```php
function change_schema_date_published($data)
```

- ورودی: آرایه اسکیمای Yoast (article یا webpage). خروجی: همان آرایه با `datePublished` تنظیم‌شده.

## کار دقیق

- از `global $post` تاریخ انتشار پست را می‌گیرد.
- با `date(DATE_ATOM, strtotime($post->post_date))` به فرمت ATOM تبدیل می‌کند.
- در `$data['datePublished']` قرار می‌دهد و آرایه را برمی‌گرداند.
- روی دو فیلتر Yoast وصل شده: `wpseo_schema_article` و `wpseo_schema_webpage`.

## محل استفاده در سایت

- فقط در `ahmadreza/init.php` حدود خط ۸۲۶–۸۲۷: `add_filter('wpseo_schema_article', ...)` و `add_filter('wpseo_schema_webpage', ...)`.

## برای تغییر چه کار کنیم

1. **فرمت تاریخ:** به‌جای `DATE_ATOM` می‌توان فرمت دیگر استفاده کرد؛ ترجیحاً ISO 8601 حفظ شود.
2. **منبع تاریخ:** اگر از متا یا ACF می‌خواهید، به‌جای `$post->post_date` از `get_post_meta` یا `get_field` استفاده کنید.
3. **فقط برای نوع خاص:** با `get_post_type()` فقط برای post/page آرایه را تغییر دهید.

## تابع/کد مشابه در سایت

- Yoast خود اسکیمای article/webpage را می‌سازد؛ این فیلترها فقط `datePublished` را override می‌کنند.
- **ادغام:** تابع دیگری برای تغییر اسکیمای Yoast تعریف نشده.

## بهینه‌سازی

1. قبل از استفاده چک کنید `$post` موجود و معتبر است.
2. با `isset($data['datePublished'])` از تغییر ساختار نسخه‌های بعدی Yoast جلوگیری کنید.
3. نام با پیشوند تم (مثلاً `ez_change_schema_date_published`) برای وضوح.
