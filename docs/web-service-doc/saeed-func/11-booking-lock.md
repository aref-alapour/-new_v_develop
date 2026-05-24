# قفل سانس و باز کردن بعد از پرداخت

## ez_add_booking_lock

- **تابع در saeed-codes.php:** `ez_add_booking_lock($product_id, $booking_time)`
- **جایگزین:** تابع اصلی است؛ از لاجیک چک‌اوت/رزرو صدا زده می‌شود.
- **کارایی:** قفل سانس را در سرویس رزرو (ez_reservation با نوع add_sans_lock) ثبت می‌کند.
- **بهینه‌سازی:** (۱) وابستگی به session یا پارامتر رزرو را در docblock بنویس. (۲) در صورت خطای ez_reservation، مقدار برگشتی و لاگ را هندل کن. (۳) زمان انقضای قفل را در سرویس رزرو یا اینجا مشخص کن.

## ez_remove_booking_lock

- **تابع در saeed-codes.php:** `ez_remove_booking_lock($product_id, $booking_time)`
- **جایگزین:** تابع اصلی است؛ از visit_single_room_unlock_booking و لاجیک پرداخت/لغو صدا زده می‌شود.
- **کارایی:** قفل سانس را در سرویس رزرو حذف می‌کند (ez_reservation با نوع remove_sans_lock یا معادل).
- **بهینه‌سازی:** مثل ez_add_booking_lock؛ خطا و انقضا را هندل کن.

## ez_get_booking_lock

- **تابع در saeed-codes.php:** `ez_get_booking_lock($product_id)`
- **جایگزین:** تابع اصلی است؛ از همان بخش و AJAX صدا زده می‌شود.
- **کارایی:** لیست قفل‌های سانس یک محصول را از سرویس رزرو (ez_reservation با نوع get_sans_lock) می‌گیرد و برمی‌گرداند.
- **بهینه‌سازی:** (۱) در صورت خطای شبکه، آرایهٔ خالی یا null برگردان و لاگ کن. (۲) کش کوتاه‌مدت در صورت تکراری بودن درخواست در نظر بگیر.

## visit_single_room_unlock_booking

- **تابع در saeed-codes.php:** `visit_single_room_unlock_booking()`
- **جایگزین:** تابع اصلی است؛ اکشن `wp` صدا می‌زند.
- **کارایی:** در صفحهٔ تک‌محصول؛ بعد از بازدید یا پرداخت، قفل‌های مربوط به کاربر/سفارش را با ez_get_booking_lock می‌خواند و با ez_remove_booking_lock برمی‌دارد.
- **بهینه‌سازی:** (۱) شرط «بازدید یا پرداخت» (مثلاً پارامتر یا session) را مستند کن. (۲) فقط قفل‌های مربوط به همین کاربر/سفارش را بردار تا قفل دیگران دستکاری نشود.

## tracking_back_btn_in_checkout_page

- **تابع در saeed-codes.php:** `tracking_back_btn_in_checkout_page()`
- **جایگزین:** تابع اصلی است؛ اکشن `wp` صدا می‌زند.
- **کارایی:** دکمهٔ بازگشت به پیگیری را در صفحهٔ چک‌اوت رندر یا لینک می‌دهد.
- **بهینه‌سازی:** لینک پیگیری را از یک تابع کمکی یا option بخوان؛ فقط در صفحهٔ چک‌اوت نمایش بده (با is_checkout).
