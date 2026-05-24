# توابع کمکی

## randString / base64_url_encode / base64_url_decode
- **کارایی:** تولید رشتهٔ تصادفی؛ انکود/دیکد base64 به‌صورت URL-safe. در همین فایل برای توکن و لینک استفاده می‌شوند.
- **بهینه‌سازی:** طول و کاراکترهای مجاز randString را پارامتر کن؛ از random_bytes برای امنیت بالاتر استفاده کن. تکراری بودن base64 در helper-functions را حذف کن.

## trim_home_url
- **تابع در saeed-codes.php:** `trim_home_url($url)` — در قالب و callbacks، city، navbar، brands و غیره
- **جایگزین:** تابع اصلی است؛ در web-service/helper-functions.php هم تعریف شده — یک منبع واحد انتخاب کن.
- **کارایی:** دامنهٔ سایت را از اول URL حذف می‌کند و فقط مسیر نسبی برمی‌گرداند.
- **بهینه‌سازی:** از get_home_url() برای مقایسه استفاده کن تا با تنظیمات وردپرس سازگار بماند؛ تکرار را با helper-functions حذف کن.

## get_term_link_flat
- **تابع در saeed-codes.php:** `get_term_link_flat($term, $taxonomy = 'category')` — در single-product و navbar
- **کارایی:** لینک ترم را به‌صورت مسیر تخت (بدون دامنهٔ کامل) برمی‌گرداند.
- **بهینه‌سازی:** وابستگی به get_term_link و trim_home_url را مستند کن؛ در صورت object/ID هر دو را پشتیبانی کن.

## persianToEnglish / englishToPersian / normalizePhoneNumber / isValidIranianMobileNumber
- **کارایی:** تبدیل اعداد فارسی/انگلیسی؛ نرمال کردن شمارهٔ موبایل؛ اعتبارسنجی شمارهٔ ایران. در لاجیک موبایل و SMS استفاده می‌شوند.
- **بهینه‌سازی:** الگوی شمارهٔ ایران را در یک ثابت (regex) تعریف کن؛ خروجی normalize را همیشه با فرمت یکسان (مثلاً 09…) برگردان.

## ez_get_product_meta
- **تابع در saeed-codes.php:** `ez_get_product_meta($product_id)` — در همین فایل، thankyou، ticket، callbacks و غیره
- **جایگزین:** تابع اصلی است.
- **کارایی:** آبجکتی از متاهای مهم محصول (نوع، سانس، شهر، مالک و غیره) می‌سازد و برمی‌گرداند تا در چند جا استفاده شود.
- **بهینه‌سازی:** (۱) لیست فیلدهای خوانده‌شده را در docblock یا آرایهٔ ثابت بنویس. (۲) کش کوتاه‌مدت برای product_id در همان درخواست در نظر بگیر تا چند بار متا نخوانی. (۳) در صورت product_id نامعتبر، null یا WP_Error برگردان.

## change_product_short_description_title
- **تابع در saeed-codes.php:** فیلتر `gettext` — ترجمه/تغییر عنوان توضیح کوتاه محصول.
- **کارایی:** متن «توضیح کوتاه» را به عنوان دلخواه تغییر می‌دهد.
- **بهینه‌سازی:** فقط وقتی $text و $domain مطابق هستند تغییر بده تا با سایر ترجمه‌ها تداخل نداشته باشد.

## get_product_type_equivalent / get_parent_category_name_by_child_id
- **کارایی:** نگاشت نوع محصول به مقدار معادل؛ نام دستهٔ والد از روی ID فرزند. داخل همین فایل استفاده می‌شوند.
- **بهینه‌سازی:** نگاشت را در آرایهٔ ثابت تعریف کن؛ get_parent را با get_term و parent انجام بده و کش ترم در نظر بگیر.

## get_bayesian_score
- **تابع در saeed-codes.php:** `get_bayesian_score($R, $v, $C, $m)` — در ez_queryable_set_hottest_products
- **کارایی:** امتیاز بیزی را از پارامترهای امتیاز، تعداد، میانگین کلی و حداقل تعداد محاسبه می‌کند.
- **بهینه‌سازی:** فرمول را در docblock بنویس؛ مقادیر C و m را از بالا یا option بخوان تا قابل تنظیم باشند.

## encrypt_data
- **تابع در saeed-codes.php:** `encrypt_data($plaintext, $key)` — در لاجیک حساس
- **کارایی:** داده را با کلید رمزنگاری می‌کند.
- **بهینه‌سازی:** الگوریتم و روش (مثلاً AES) را مستند کن؛ کلید را از ثابت یا option امن بخوان؛ دیکد را در تابع جدا decrypt_data انجام بده.
