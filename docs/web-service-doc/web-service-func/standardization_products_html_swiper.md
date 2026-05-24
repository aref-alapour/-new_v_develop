# standardization_products_html_swiper

## تابع در web-service.php

```php
function standardization_products_html_swiper($products, $only_free_sanses = false)
```

(در فراخوانی از `format_products_to_html_query` یک آرگومان سوم `$badge_ads` هم پاس داده می‌شود که در امضای تابع نیست و در بدنه استفاده نمی‌شود.)

## کارایی

- **نقش:** تبدیل همان لیست محصولات به **رشتهٔ HTML کارت‌های اسلایدر** (برای استفاده در اسلایدر فرانت، مثلاً Embla).
- **ورودی:** مثل `standardization_products`: `$products` (خام)، `$only_free_sanses`.
- **خروجی:** رشتهٔ HTML (با `ob_start` / `ob_get_clean`)؛ هر محصول یک `<article class="embla__slide">` با تصویر، عنوان، آدرس، نوع، امتیاز، قیمت، سانس آزاد، مدت، ظرفیت و غیره.
- **منطق:**  
  1. در صورت خالی بودن `$home_url` آن را از `HTTP_HOST` پر می‌کند.  
  2. `standardization_products($products, $only_free_sanses)` را صدا می‌زند و با آرایهٔ فرمت‌شده حلقه می‌زند.  
  3. بر اساس `active` (temp/deactivated، expired، soon، یا عادی) قالب کارت را عوض می‌کند (غیرفعال، اکسپایر، به‌زودی، یا کارت کامل با overlay و ژانر و …).  
  4. نگاشت نوع محصول به برچسب فارسی (اتاق فرار، کافه بازی، سینما ترس و …) با یک switch در همین تابع انجام می‌شود.
- **کجا صدا زده می‌شود:** فقط از داخل `format_products_to_html_query` وقتی `$format == 'html_swiper'` (هندلر `sort_products_get`).

## جایگزینی

- **حذف:** اگر دیگر خروجی اسلایدر از web-service نخواهی، می‌توانی در `format_products_to_html_query` شاخهٔ `html_swiper` را حذف کنی و فقط JSON یا `html_list` بماند؛ در آن صورت این تابع را می‌توانی حذف یا به ماژول جدا (مثلاً قالب‌های ایمیال/صفحه) منتقل کنی.
- **جایگزین با قالب جدا:** خروجی HTML طولانی است؛ می‌توانی قالب را به فایل‌های جدا (مثلاً `templates/product-card-swiper.php`) ببری و این تابع فقط آن فایل را include کند و خروجی را برگرداند؛ یا از یک موتور قالب ساده (مثلاً جایگزین placeholder) استفاده کنی تا ویرایش و تم تمیزتر شود.

## بهینه‌سازی

1. **تکرار با html_list:** بیشتر بلوک‌های HTML با `standardization_products_html_list` مشترک است؛ یک تابع کمکی مثلاً `render_product_card($product, $product_cat_alt, $layout)` بساز که `$layout` یکی از `swiper` یا `list` باشد و فقط در جزئیات (کلاس یا wrapper) فرق کند؛ هم نگهداری راحت‌تر می‌شود هم باگ کمتر.
2. **نگاشت type به برچسب:** همان switch برای `product_cat_alt` در هر دو تابع HTML تکرار شده؛ آن را در یک تابع مثلاً `get_product_type_label($type)` در helper-functions متمرکز کن و از هر دو جا صدا بزن.
3. **$home_url:** مثل `standardization_products`، ترجیحاً از آرگومان یا config بگیر تا وابستگی به global کمتر شود.
4. **امنیت خروجی:** برای مقادیری که از محصول می‌آیند (مثل title، url) از `htmlspecialchars` یا تابع escape تم استفاده کن تا XSS نشود.
5. **پارامتر سوم:** اگر در آینده `badge_ads` استفاده شد، آن را به امضا اضافه کن و در قالب (مثلاً نمایش badge AD) استفاده کن؛ وگرنه از فراخوانی حذف کن تا گیج‌کننده نباشد.
