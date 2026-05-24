# تابع `has_role`

## آدرس دقیق

- **فایل:** `wp-content/themes/escapezoom-v2/ahmadreza/init.php`
- **خط:** حدود ۵۰۱ تا ۵۰۸

## امضا

```php
function has_role(...$roles): bool
```

## کار دقیق

- نقش کاربر لاگین‌شده را از `wp_get_current_user()` می‌گیرد.
- اگر کاربر نقشی نداشته باشد، `false` برمی‌گرداند.
- فقط **نقش اول** (`$user->roles[0]`) با آرایه `$roles` مقایسه می‌شود و نتیجه boolean برمی‌گرداند.

## محل استفاده در سایت

| فایل | خط (تقریبی) | کاربرد |
|------|-------------|--------|
| ahmadreza/init.php | 140,144,148,152,156,179 | نمایش/مخفی کردن آیتم‌های منوی اکانت ووکامرس |
| inc/saeed-codes.php | 23 | شرط محتوا بر اساس نقش |
| woocommerce/myaccount/pages/cancellation-requests.php | 14 | چک دسترسی |
| woocommerce/myaccount/pages/cancellation-history.php | 14 | چک دسترسی |
| app/ajax/callbacks/user_cancellation_requests_get.php | 36 | چک دسترسی AJAX |
| app/ajax/callbacks/user_cancellation_history_get.php | 17 | چک دسترسی AJAX |

## برای تغییر چه کار کنیم

1. **منوی اکانت:** در `ahmadreza/init.php` داخل فیلتر `woocommerce_account_menu_items` شرط‌های `has_role` را ویرایش کنید.
2. **دسترسی صفحات کنسلی:** در فایل‌های cancellation و دو callback AJAX شرط نقش را عوض کنید.
3. **چند نقش:** برای بررسی «هر یک از چند نقش» باید تابع را طوری تغییر دهید که روی همه `$user->roles` حلقه بزند یا از `in_array` با آرایه نقش‌ها استفاده کنید.

## تابع/کد مشابه در سایت

- وردپرس: `current_user_can('capability')` برای capability؛ برای نقش مستقیم تابع استاندارد نداریم.
- **ادغام:** می‌توان تابع را به هلپر مشترک (مثلاً `app/functions/helper/user.php`) منتقل کرد تا از ahmadreza و saeed-codes یک جا تعریف شود.

## بهینه‌سازی

1. بررسی چند نقش: به‌جای فقط `$user->roles[0]` با حلقه روی `$user->roles` چک کنید.
2. انتقال به هلپر مشترک برای تعریف واحد در کل تم.
