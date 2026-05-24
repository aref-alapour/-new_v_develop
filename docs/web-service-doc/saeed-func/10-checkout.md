# چک‌اوت و پرداخت

## sc_woocommerce_form_field_heading

- **تابع در saeed-codes.php:** `sc_woocommerce_form_field_heading($field, $key, $args, $value)`
- **جایگزین:** تابع اصلی است؛ فیلتر `woocommerce_form_field_heading` را هندل می‌کند.
- **کارایی:** نمایش heading فیلد چک‌اوت را سفارشی می‌کند.
- **بهینه‌سازی:** فقط در صورت نیاز به تغییر، مقدار برگشتی را عوض کن؛ وگرنه همان `$field` را برگردان تا با تم سازگار بماند.

## send_sms_comment_url

- **تابع در saeed-codes.php:** `send_sms_comment_url($order_id)`
- **جایگزین:** تابع اصلی است؛ اکشن `woocommerce_checkout_update_order_meta` صدا می‌زند.
- **کارایی:** بعد از ثبت سفارش، لینک نظر را به مشتری با SMS می‌فرستد (احتمالاً از add_to_sms_queue یا ez_sendpayamak3).
- **بهینه‌سازی:** (۱) متن پیام و لینک را از یک template یا option بخوان. (۲) در صورت خطای ارسال، لاگ کن و در صورت نیاز به ادمین اعلان بده. (۳) ارسال را غیرهمزمان در صف انجام بده تا چک‌اوت کند نشود.

## ez_review_order_prices_table

- **تابع در saeed-codes.php:** `ez_review_order_prices_table($order)`
- **جایگزین:** تابع اصلی است؛ اکشن `woocommerce_review_order_after_order_total` صدا می‌زند.
- **کارایی:** جدول قیمت‌ها را در خلاصه سفارش (قبل از پرداخت) رندر می‌کند (سانس، سپرده، مبلغ نهایی و غیره).
- **بهینه‌سازی:** (۱) دادهٔ order را فقط یک بار بخوان و در متغیر نگه دار. (۲) خروجی HTML را به template part ببر. (۳) برای ترجمه از توابع i18n استفاده کن.

## ez_final_payment_amount

- **تابع در saeed-codes.php:** `ez_final_payment_amount($total, $cart)`
- **جایگزین:** تابع اصلی است؛ فیلتر `woocommerce_calculated_total` صدا می‌زند.
- **کارایی:** مبلغ نهایی (پیش‌پرداخت آنلاین) را بر اساس سبد و قوانین کسب‌وکار محاسبه می‌کند.
- **بهینه‌سازی:** (۱) فرمول محاسبه را در docblock یا تابع کمکی جدا بنویس. (۲) وابستگی به متا/کوپن را مستند کن. (۳) در صورت خطا یا مقدار نامعتبر، مقدار قبلی یا صفر برگردان و لاگ کن.

## store_ez_payment_method

- **تابع در saeed-codes.php:** `store_ez_payment_method($order_id)`
- **جایگزین:** تابع اصلی است؛ اکشن `woocommerce_checkout_update_order_meta` صدا می‌زند.
- **کارایی:** روش پرداخت انتخاب‌شده را در متای سفارش ذخیره می‌کند.
- **بهینه‌سازی:** نام کلید متا را در یک ثابت تعریف کن؛ در صورت نبود روش پرداخت، مقدار پیش‌فرض یا خالی ذخیره کن.

## ez_get_coupon_discount_amount

- **تابع در saeed-codes.php:** `ez_get_coupon_discount_amount($coupon_code, $total_amount)`
- **جایگزین:** تابع اصلی است؛ داخل همین فایل در لاجیک جمع کل استفاده می‌شود.
- **کارایی:** مبلغ تخفیف کوپن را برای مبلغ داده‌شده محاسبه می‌کند.
- **بهینه‌سازی:** وابستگی به نوع کوپن (درصد/مبلغ ثابت) و قوانین را مستند کن؛ کش کوپن در صورت تکراری بودن درخواست در نظر بگیر.

## disable_multiple_coupons

- **تابع در saeed-codes.php:** `disable_multiple_coupons($enabled)`
- **جایگزین:** تابع اصلی است؛ فیلتر `woocommerce_coupons_enabled` صدا می‌زند.
- **کارایی:** استفادهٔ هم‌زمان چند کوپن را غیرفعال می‌کند (با برگرداندن false در شرایط خاص).
- **بهینه‌سازی:** شرط غیرفعال‌سازی را در یک جا بنویس؛ در صورت نیاز به استثنا (مثلاً برای ادمین) شرط اضافه کن.

## change_coupon_error_msg

- **تابع در saeed-codes.php:** `change_coupon_error_msg($err, $err_code, $coupon)`
- **جایگزین:** تابع اصلی است؛ فیلتر `woocommerce_coupon_error` صدا می‌زند.
- **کارایی:** متن خطای کوپن را به پیام دلخواه تغییر می‌دهد.
- **بهینه‌سازی:** نگاشت err_code به متن را در یک آرایهٔ ثابت تعریف کن؛ برای ترجمه از __() استفاده کن.

## controle_before_place_order

- **تابع در saeed-codes.php:** `controle_before_place_order()`
- **جایگزین:** تابع اصلی است؛ اکشن `woocommerce_before_calculate_totals` صدا می‌زند.
- **کارایی:** اعتبارسنجی قبل از ثبت سفارش (مثلاً موجودی سانس، حداقل مبلغ و غیره).
- **بهینه‌سازی:** (۱) شرطهای اعتبارسنجی را در توابع کوچک جدا کن. (۲) در صورت خطا، با اضافه کردن notice به WC و جلوگیری از محاسبهٔ نهایی، کاربر را مطلع کن.

## conflict_before_place_order_validation

- **تابع در saeed-codes.php:** `conflict_before_place_order_validation($data, $errors)`
- **جایگزین:** تابع اصلی است؛ اکشن `woocommerce_after_checkout_validation` صدا می‌زند.
- **کارایی:** جلوگیری از تداخل سانس؛ قبل از place order با ez_reservation یا دیتابیس چک می‌کند که سانس هنوز آزاد باشد و در صورت تداخل خطا به `$errors` اضافه می‌کند.
- **بهینه‌سازی:** (۱) وابستگی به session/فیلد چک‌اوت را مستند کن. (۲) در صورت خطای شبکه یا timeout سرویس رزرو، پیام واضح به کاربر برگردان و در صورت امکان retry پیشنهاد بده.
