# standardization_products_html_list

## تابع در web-service.php

```php
function standardization_products_html_list($products, $only_free_sanses = false)
```

(در فراخوانی از `format_products_to_html_query` یک آرگومان سوم `$badge_ads` پاس داده می‌شود که در امضا و بدنه استفاده نمی‌شود.)

## کارایی

- **نقش:** تبدیل همان لیست محصولات به **رشتهٔ HTML کارت‌های لیست** (ساختار مشابه اسلایدر ولی برای نمایش به‌صورت لیست در صفحه).
- **ورودی و خروجی:** مثل `standardization_products_html_swiper`: ورودی `$products` و `$only_free_sanses`؛ خروجی رشتهٔ HTML با `ob_start` / `ob_get_clean`.
- **منطق:** عملاً همان منطق و قالب کارتِ `standardization_products_html_swiper` را دارد (بر اساس active: غیرفعال، اکسپایر، به‌زودی، عادی؛ همان switch برای برچسب نوع محصول؛ همان فیلدهای کارت). تفاوت در استفادهٔ بعدی در فرانت است (لیست به‌جای اسلایدر).
- **کجا صدا زده می‌شود:** فقط از داخل `format_products_to_html_query` وقتی `$format == 'html_list'` (هندلر `sort_products_get`).

## جایگزینی

- **حذف:** اگر خروجی لیست HTML از web-service حذف شود، در `format_products_to_html_query` شاخهٔ `html_list` را حذف کن و این تابع را می‌توانی حذف یا به ماژول قالب منتقل کنی.
- **ادغام با swiper:** چون ساختار کارت یکی است، می‌توانی یک تابع واحد مثلاً `standardization_products_html_cards($products, $only_free_sanses, $card_layout)` داشته باشی که با `$card_layout === 'swiper'` یا `'list'` فقط در wrapper یا کلاس تفاوت بگذارد و از تکرار صدها خط HTML جلوگیری کند.

## بهینه‌سازی

1. **حذف تکرار با swiper:** بزرگ‌ترین بهینه‌سازی این است که بلوک‌های تکراری HTML را در یک تابع/فایل قالب مشترک ببری و هر دو تابع فقط آن را با پارامتر layout صدا بزنند (جزئیات در فایل `standardization_products_html_swiper.md`).
2. **برچسب نوع محصول:** همان switch را در یک تابع کمکی متمرکز کن.
3. **$home_url و escape:** مثل swiper؛ وابستگی به global را کم کن و خروجی را برای XSS امن کن.
4. **پارامتر سوم:** یا از فراخوانی حذف شود یا در امضا و قالب استفاده شود.
