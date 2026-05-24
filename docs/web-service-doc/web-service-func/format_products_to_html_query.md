# format_products_to_html_query

## تابع در web-service.php

```php
function format_products_to_html_query($products, $format, $is_mobile, $show_more, $show_more_url, $badge_ads, $only_free_sanses)
```

## کارایی

- **نقش:** روتینگ بر اساس `$format`؛ خروجی محصولات را به یکی از سه شکل برمی‌گرداند: آرایهٔ استاندارد (JSON) یا HTML اسلایدر یا HTML لیست.
- **ورودی:**  
  - `$products`: آرایه/آبجکت محصولات خام (از دیتابیس یا سرویس).  
  - `$format`: یکی از `html_swiper`، `html_list` یا هر چیز دیگر (پیش‌فرض = خروجی استاندارد).  
  - `$is_mobile`, `$show_more`, `$show_more_url`, `$badge_ads`: در امضا هستند ولی در بدنه استفاده نمی‌شوند.  
  - `$only_free_sanses`: فیلتر «فقط محصولات با سانس آزاد».
- **خروجی:**  
  - اگر `$format == 'html_swiper'` → `standardization_products_html_swiper($products, $only_free_sanses, $badge_ads)` (رشتهٔ HTML).  
  - اگر `$format == 'html_list'` → `standardization_products_html_list($products, $only_free_sanses, $badge_ads)` (رشتهٔ HTML).  
  - وگرنه → `standardization_products($products, $only_free_sanses)` (آرایهٔ استاندارد).
- **کجا صدا زده می‌شود:**  
  - داخل همین فایل در دو جای هندلر `sort_products_get`: یکی با پارامترهای کامل (خط ~۱۰۷۸)، یکی با آرگومان‌های ۰ (خط ~۱۱۲۹).

## جایگزینی

- **حذف تابع:** نمی‌شود بدون جایگزین حذف کرد؛ هر دو نقطهٔ فراخوانی باید مستقیماً یکی از سه تابع زیر را صدا بزنند:
  - برای خروج JSON: `standardization_products($products, $only_free_sanses)`.
  - برای خروج اسلایدر: `standardization_products_html_swiper($products, $only_free_sanses, $badge_ads)`.
  - برای خروج لیست: `standardization_products_html_list($products, $only_free_sanses, $badge_ads)`.
- **جایگزین با یک تابع دیگر:** می‌توان یک تابع جدید با نام مثلاً `ez_format_products($products, $format, $options)` تعریف کرد که `$options` آرایهٔ اختیاری (only_free_sanses، badge_ads و …) باشد و داخلش همان سه تابع را بر اساس `$format` صدا بزند؛ سپس فقط دو جای فراخوانی را به این تابع جدید تغییر بدهی.

## بهینه‌سازی

1. **پارامترهای بیکار:** یا از امضا حذف کن (`$is_mobile`, `$show_more`, `$show_more_url`) یا در بدنه استفاده کن (مثلاً برای لینک «بیشتر» یا نسخهٔ موبایل). در غیر این صورت خواننده گیج می‌شود.
2. **یکسان‌سازی فراخوانی:** در خط ~۱۱۲۹ آرگومان‌ها با ۰ فراخوانی شده‌اند؛ اگر همیشه همان حالت مدنظر است، یا مقدار پیش‌فرض در تابع بگذار یا یک wrapper با نام واضح بساز تا نیازی به ۰،۰،۰،۰ نباشد.
3. **نوع خروجی:** در docblock بنویس که در صورت `html_swiper` / `html_list` خروجی string است و در غیر آن آرایه؛ تا caller بداند چه نوعی برمی‌گردد.
4. **وابستگی:** وابستگی به `standardization_products` و دو تابع HTML را در همان فایل یا در docblock ذکر کن تا در refactor بعدی گم نشوند.
