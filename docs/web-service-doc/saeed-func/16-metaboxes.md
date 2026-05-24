# متاباکس‌های محصول

## reservation_info_metabox / reservation_info_callback / save_reservation_info
- **جایگزین:** توابع اصلی؛ متاباکس اطلاعات رزرو (قیمت، سانس و غیره).
- **کارایی:** متاباکس را ثبت، فرم را رندر و روی save_post ذخیره می‌کنند؛ save_reservation_info با ez_reservation سینک می‌کند.
- **بهینه‌سازی:** (۱) process_price_field را در docblock توضیح بده. (۲) نام متاها را در ثابت تعریف کن. (۳) سینک با وب‌سرویس را در صورت خطا لاگ کن و به کاربر اطلاع بده.

## product_options_metabox / product_options_callback / save_product_options
- **کارایی:** متاباکس گزینه‌های محصول (نوع محصول، شهر و غیره).
- **بهینه‌سازی:** لیست گزینه‌ها را از یک منبع واحد بخوان؛ وابستگی به ez_reservation (update_product_sub_data) را مستند کن.

## get_day_type / get_sanses
- **تابع در saeed-codes.php:** `get_day_type($day)`, `get_sanses($product_id)`
- **جایگزین:** توابع اصلی؛ در web-service/helper-functions.php هم نسخهٔ مشابه هست — یک منبع واحد انتخاب کن.
- **کارایی:** از timestamp نوع روز (امروز/فردا/…) را برمی‌گرداند؛ از متای محصول لیست سانس‌ها (normals/holidays) را برمی‌گرداند. در همین فایل، thankyou، callbacks، saeed، reservation، sans_management استفاده می‌شوند.
- **بهینه‌سازی:** (۱) حذف تکرار با helper-functions؛ یا فقط از یکی استفاده کن. (۲) فرمت خروجی get_sanses را در docblock ثابت کن. (۳) در صورت خواندن متای زیاد، کش کوتاه در نظر بگیر.

## product_videos_metabox / product_videos_metabox_frontend / save_product_videos_metabox_data
- **کارایی:** متاباکس ویدئوهای محصول؛ رندر فرم و ذخیره.
- **بهینه‌سازی:** نام متا و تعداد فیلدها را در ثابت تعریف کن؛ فایل آپلود را با wp_handle_upload و بررسی نوع انجام بده.

## monopoly_metabox / monopoly_metabox_frontend / save_monopoly_metabox_data
- **کارایی:** متاباکس انحصار (بله/خیر).
- **بهینه‌سازی:** نام متا را ثابت کن؛ مقدار را به 0/1 نرمال کن.

## product_content_metabox + *_frontend (introduction_text, scenario, rules, introduction_video) / save_product_content_metabox_data
- **کارایی:** متاباکس محتوای محصول (معرفی، سناریو، قوانین، ویدئو معرفی) و ذخیرهٔ یکجا.
- **بهینه‌سازی:** نام متاها را در آرایهٔ ثابت تعریف کن؛ برای فیلدهای متنی بزرگ sanitize و escape در خروجی انجام بده.

## special_discount / special_discount_func / special_discount_save_func
- **کارایی:** متاباکس تخفیف ویژه (درصد و تاریخ)؛ ذخیره و سینک با ez_webservice (update_product_discount_data).
- **بهینه‌سازی:** وابستگی به web-service را مستند کن؛ اعتبار تاریخ و درصد را چک کن.

## add_fields_to_product_tag / save_fields_to_product_tag
- **کارایی:** فیلدهای اضافه برای taxonomy محصول (product_tag).
- **بهینه‌سازی:** نام فیلدها و متا ترم را در ثابت تعریف کن؛ در save از nonce و permission چک کن.

## city_page_product_categories_meta_box / display_city_page_product_categories_meta_box / save_city_page_product_categories_meta_box
- **کارایی:** متاباکس دسته‌بندی‌های نمایش در صفحهٔ شهر.
- **بهینه‌سازی:** نام متا را ثابت کن؛ ذخیره را به آرایهٔ term_idها محدود و sanitize کن.
