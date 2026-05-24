# standardization_products

## تابع در web-service.php

```php
function standardization_products($products, $only_free_sanses = false)
```

## کارایی

- **نقش:** تبدیل لیست محصولات خام (از جدول `products_data` / نتیجهٔ کوئری) به آرایهٔ یکدست برای API/فرانت؛ محاسبهٔ سانس‌های آزاد امروز و حذف پیشوند از نام شهر.
- **ورودی:**  
  - `$products`: آرایهٔ آبجکت‌های محصول (با فیلدهایی مثل product_id، schedule، discount_data، city_name، …).  
  - `$only_free_sanses`: اگر true باشد، محصولاتی که امروز سانس آزاد ندارند از خروجی حذف می‌شوند.
- **خروجی:** آرایهٔ `$formatted_products`؛ هر عنصر شامل: product_id، type (معادل انگلیسی نوع محصول)، title، price، ads، image (URL کامل)، age، level، duration، url، city_id، city_name (بدون پیشوند)، hood_name، genres، tags، number_min، number_max، event (تخفیف ویژه)، comments_count، rate، free_sanses، geo، active.
- **منطق اصلی:**  
  1. از جدول `wp_zb_booking_history` سانس‌های رزروشده برای این محصولات را می‌خواند و سانس‌های «امروز» و بعد از `time() + (auto_disable * 60)` را به‌عنوان پر در نظر می‌گیرد.  
  2. برای هر محصول با `get_day_type2(time())` و `schedule` سانس‌های آزاد امروز را می‌شمارد و در `free_sanses` می‌گذارد.  
  3. اگر `$only_free_sanses` true باشد و `free_sanses` خالی باشد، آن محصول را skip می‌کند.  
  4. از `city_name` پیشوندهای ثابت (اتاق فرار، لیزرتگ، …) را حذف می‌کند.  
  5. نوع محصول را با `get_product_type_equivalent($product->product_type)` به مقدار معادل (مثلاً escaperoom) تبدیل می‌کند.
- **کجا صدا زده می‌شود:**  
  - داخل همین فایل توسط `format_products_to_html_query` (برای خروج غیر HTML).  
  - داخل `standardization_products_html_swiper` و `standardization_products_html_list` در خط اول بدنه، قبل از رندر HTML.

## جایگزینی

- **حذف:** امکان‌پذیر نیست مگر تمام مسیرهای `sort_products_get` و رندر کارت محصول را عوض کنی و خروجی را از جای دیگری (مثلاً سرویس جدا یا دیتابیس با ساختار دیگر) بسازی.
- **جایگزین با سرویس/کلاس:** می‌توان این منطق را در یک کلاس مثلاً `ProductFormatter` منتقل کرد: متدهایی مثل `getFreeSansesForProducts()` و `formatProductRow()` تا تست و استفادهٔ مجدد راحت‌تر شود. فراخوانی‌های فعلی به همان تابع یا به کلاس جدید redirect شوند.

## بهینه‌سازی

1. **کوئری booking:** به‌جای یک کوئری برای همهٔ product_idها و سپس حلقه در PHP، همان یک کوئری را نگه دار ولی شرط `room_id IN (...)` را با لیست ایمن (prepared statement یا escape) بزن تا از SQL injection جلوگیری شود. در صورت تعداد زیاد محصول، می‌توان به batch تقسیم کرد.
2. **ثابت‌ها:** لیست `$remove_prefixes` و مقادیر ثابت (مثل ۹۲۵۰۰۰ در popular) را در بالای فایل یا فایل config تعریف کن.
3. **get_day_type2 و get_product_type_equivalent:** وابستگی به helper-functions را در docblock بنویس. اگر این توابع در فایل دیگری هستند، نام فایل را ذکر کن.
4. **خروجی schedule:** وابستگی به ساختار `schedule` (normals/holidays و فیلد time) را مستند کن تا با تغییر ساختار در ووکامرس سازگار بماند.
5. **$home_url:** مقداردهی داخل تابع با چک `empty($home_url)` وابسته به global است؛ ترجیحاً `$home_url` را به‌صورت آرگومان یا از یک تابع کمکی بگیر تا تست و تغییر دامنه راحت‌تر شود.
