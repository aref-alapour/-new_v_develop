# کوپن و محدودیت کاربر

## restrict_coupon_to_user_ids
- **تابع در saeed-codes.php:** `restrict_coupon_to_user_ids($valid, $coupon)` — فیلتر `woocommerce_coupon_is_valid`
- **جایگزین:** تابع اصلی است.
- **کارایی:** کوپن فقط برای کاربران مشخص (لیست user_id در متا کوپن) معتبر است؛ وگرنه valid را false می‌کند.
- **بهینه‌سازی:** نام متای usage_restriction_user_ids را در ثابت تعریف کن؛ در صورت خالی بودن لیست، رفتار (همه مجاز یا هیچ‌کس) را مستند کن.

## first_bought_coupon
- **تابع در saeed-codes.php:** `first_bought_coupon($valid, $coupon)` — فیلتر `woocommerce_coupon_is_valid`
- **کارایی:** کوپن فقط برای کسی که اولین خرید را انجام داده معتبر است؛ با if_user_has_bought چک می‌کند.
- **بهینه‌سازی:** if_user_has_bought را به‌صورت کش یا با یک کوئری بهینه پیاده کن تا در هر بار چک کوپن سنگین نشود.

## coupon_validation_block_on_special_discount
- **تابع در saeed-codes.php:** `coupon_validation_block_on_special_discount($valid, $coupon)` — فیلتر `woocommerce_coupon_is_valid`
- **کارایی:** وقتی برای محصول تخفیف ویژه فعال است، استفاده از کوپن را باطل می‌کند.
- **بهینه‌سازی:** شرط «تخفیف ویژه فعال» را در یک تابع کمکی چک کن؛ با متای محصول و تاریخ هماهنگ کن.

## add_usage_restriction_user_ids / save_coupon_usage_restriction
- **کارایی:** در صفحهٔ ویرایش کوپن فیلد «محدودیت کاربران» را اضافه و ذخیره می‌کنند.
- **بهینه‌سازی:** نام متا را ثابت کن؛ مقدار را به آرایهٔ اعداد (user_id) sanitize کن.

## if_user_has_bought
- **تابع در saeed-codes.php:** `if_user_has_bought($user_id)`
- **جایگزین:** تابع اصلی است؛ برای کوپن اولین خرید و جاهای دیگر استفاده می‌شود.
- **کارایی:** برمی‌گرداند آیا این کاربر حداقل یک سفارش تکمیل‌شده دارد یا نه.
- **بهینه‌سازی:** کوئری را با وضعیت‌های مشخص (مثلاً wc-completed) محدود کن؛ در صورت تکراری بودن، کش کاربری در نظر بگیر.

## if_user_commented
- **تابع در saeed-codes.php:** `if_user_commented($phone, $product_id)`
- **جایگزین:** تابع اصلی است؛ در لاجیک یادآور نظر استفاده می‌شود.
- **کارایی:** برمی‌گرداند آیا این شماره برای این محصول نظر تأییدشده دارد یا نه.
- **بهینه‌سازی:** جستجو را روی comment و comment_meta با شاخص انجام بده؛ در صورت حجم بالا کش نکن مگر لازم باشد.
