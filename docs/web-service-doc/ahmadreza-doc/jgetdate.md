# تابع `jgetdate`

## آدرس دقیق

- **فایل:** `wp-content/themes/escapezoom-v2/ahmadreza/jdate.php`
- **خط:** حدود ۶۵۸ تا ۶۸۲

## امضا

```php
function jgetdate($timestamp = "", $none = "", $timezone = "Asia/Tehran", $tn = "en")
```

## کار دقیق

- معادل **getdate()** برای شمسی: timestamp می‌گیرد و آرایه‌ای با کلیدهای seconds, minutes, hours, mday, wday, mon, year, yday, weekday, month, 0 (مقادیر شمسی) برمی‌گرداند.

## محل استفاده در سایت

- فقط **داخل jdate.php** توسط سایر توابع استفاده می‌شود.
- در تم فراخوانی مستقیم `jgetdate` یافت نشد.

## برای تغییر چه کار کنیم

- در صورت نیاز به آرایه اجزای تاریخ شمسی در تم/پلاگین از این تابع استفاده کنید.
- تغییر رفتار فقط با ویرایش jdate.php.

## تابع/کد مشابه در سایت

- PHP: `getdate($timestamp)` برای میلادی.

## بهینه‌سازی

- بدون استفاده مستقیم در تم؛ بخشی از API کتابخانه jdate است.
