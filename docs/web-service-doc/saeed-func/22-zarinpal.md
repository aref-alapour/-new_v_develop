# زرین‌پال و سبد

## fix_quantity_if_not_allowed
- **تابع در saeed-codes.php:** `fix_quantity_if_not_allowed($cart_item_key, $product_id, $quantity)` — اکشن `woocommerce_add_to_cart`
- **جایگزین:** تابع اصلی است.
- **کارایی:** تعداد اضافه‌شده به سبد را با حداکثر مجاز محصول مقایسه می‌کند و در صورت بیشتر بودن، quantity را محدود یا خطا می‌دهد.
- **بهینه‌سازی:** حداکثر مجاز را از متای محصول یا قوانین کسب‌وکار در یک تابع کمکی بخوان؛ با set_quantity یا حذف آیتم سبد رفتار یکسان داشته باش.

## switch_zarinpal_gateway_by_domain
- **تابع در saeed-codes.php:** `switch_zarinpal_gateway_by_domain($available_gateways)` — فیلتر `woocommerce_available_payment_gateways`
- **جایگزین:** تابع اصلی است.
- **کارایی:** بر اساس دامنهٔ فعلی، درگاه زرین‌پال (یا زرین‌پال کو) را در لیست درگاه‌ها فعال/غیرفعال می‌کند.
- **بهینه‌سازی:** نگاشت دامنه به شناسهٔ درگاه را در آرایهٔ ثابت تعریف کن؛ در صورت نبود دامنه، رفتار پیش‌فرض (یکی از دو درگاه) را مستند کن.

## get_order_id_by_authority
- **تابع در saeed-codes.php:** `get_order_id_by_authority($authority)` — داخل لاجیک زرین‌پال
- **کارایی:** از authority برگشتی زرین‌پال، order_id را از متا یا جدول تراکنش پیدا می‌کند.
- **بهینه‌سازی:** نام متا/جدول را ثابت کن؛ در صورت عدم یافتن، null برگردان و در caller هندل کن.

## zarinpal_paid_transactions_process / zarinpal_co_paid_transactions_process
- **کارایی:** با کرون اجرا می‌شوند؛ تراکنش‌های پرداخت‌شدهٔ زرین‌پال (و زرین‌پال کو) را از جدول می‌خوانند، با API تأیید می‌کنند و وضعیت سفارش/رزرو را به‌روزرسانی می‌کنند.
- **بهینه‌سازی:** (۱) تعداد حداکثر تراکنش در هر اجرا را limit کن. (۲) verify_zarinpal_payment را برای تأیید نهایی صدا بزن و خطا را لاگ کن. (۳) بعد از به‌روزرسانی، رکورد تراکنش را علامت‌گذاری کن تا دوباره پردازش نشود.

## verify_zarinpal_payment
- **تابع در saeed-codes.php:** `verify_zarinpal_payment($merchantCode, $sandbox, $accessToken, $authority, $verify_amount, $order)`
- **جایگزین:** تابع اصلی است؛ داخل پردازش تراکنش‌ها صدا زده می‌شود.
- **کارایی:** با API زرین‌پال تراکنش را verify می‌کند و در صورت موفقیت، مبلغ و وضعیت را با سفارش تطبیق می‌دهد.
- **بهینه‌سازی:** (۱) کلیدها و sandbox را از option بخوان. (۲) پاسخ API و خطاها را چک کن و به caller برگردان. (۳) در صورت عدم تطابق مبلغ، لاگ کن و به کاربر اطلاع بده.
