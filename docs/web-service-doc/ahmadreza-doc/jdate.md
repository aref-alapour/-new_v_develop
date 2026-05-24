# تابع `jdate`

## آدرس دقیق

- **فایل:** `wp-content/themes/escapezoom-v2/ahmadreza/jdate.php`
- **خط:** حدود ۱۲ تا ۲۸۸

## امضا

```php
function jdate($format, $timestamp = "", $none = "", $time_zone = "Asia/Tehran", $tr_num = "fa")
```

- **$format:** رشته فرمت شمسی (مثل `date()`: Y/m/d, l, F, H:i و ...).
- **$timestamp:** اختیاری؛ خالی = `time()`.
- **$tr_num:** `fa` اعداد فارسی، `en` انگلیسی.

## کار دقیق

- تاریخ میلادی را به **شمسی (جلالی)** تبدیل و طبق `$format` برمی‌گرداند.
- از `gregorian_to_jalali`, `jdate_words`, `tr_num` استفاده می‌کند.
- تابع اصلی تاریخ شمسی در کل تم است.

## محل استفاده در سایت

استفاده گسترده در تم برای نمایش تاریخ شمسی:

- **ahmadreza/shortcodes/single-product-days.php:** `jdate('d', $date)`, `jdate('l', $date)`.
- **inc/saeed-codes.php:** تاریخ سفارش، سانس، تیکت، چت (چندین مورد).
- **woocommerce/myaccount:** sans-manager, dashboard, sells, notices و checkout (thankyou, form-checkout).
- **ticket.php, reserve.php, profile.php.**
- **template/team, template/layout/comments.php.**
- **app/ajax/callbacks:** reserve_get_table, panel_sells_get_tables, panel_orders_get, panel_wallet_lists_get, panel_points_get, panel_comments_list_get, get_author_posts و ...
- **author.php, page-marketing.php** و سایر قالب‌ها.

## برای تغییر چه کار کنیم

1. **فرمت در یک صفحه:** در همان تمپلیت رشته اول `jdate` را عوض کنید (مثلاً Y/m/d به Y.m.d).
2. **منطقه زمانی:** پارامتر چهارم را در فراخوانی یا پیش‌فرض در jdate.php تغییر دهید.
3. **جایگزینی کتابخانه:** اگر از پکیج دیگر (مثلاً morilog/jalali) استفاده کنید، همه فراخوانی‌های `jdate` را با API جدید جایگزین کنید یا یک wrapper با همان امضای `jdate` بنویسید.

## تابع/کد مشابه در سایت

- PHP: `date()` برای میلادی؛ معادل شمسی در core نیست.
- همین فایل: `jstrftime`, `jmktime`, `gregorian_to_jalali`, `jalali_to_gregorian` و ... با jdate کار می‌کنند.
- **ادغام:** جایگزین دیگری در تم برای تاریخ شمسی تعریف نشده؛ همه جا از همین jdate استفاده می‌شود.

## بهینه‌سازی

1. کش برای فرمت‌های پرتکرار در یک درخواست (یک بار jdate و ذخیره در متغیر).
2. ثابت‌های فرمت (مثلاً EZ_DATE_FORMAT, EZ_TIME_FORMAT) برای یکسان‌سازی در کل سایت.
3. از تغییر غیرضروری داخل jdate.php خودداری کنید؛ در صورت نیاز wrapper در هلپر تم بنویسید.
