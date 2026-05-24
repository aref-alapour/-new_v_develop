# آیتم سفارش و ویرایش

## custom_checkout_create_order_line_item
- **تابع در saeed-codes.php:** `custom_checkout_create_order_line_item($order_id, $items)` — اکشن `woocommerce_before_save_order_items`
- **جایگزین:** تابع اصلی است.
- **کارایی:** هنگام ذخیرهٔ آیتم‌های سفارش از طرف ادمین؛ quantity را از آیتم‌ها می‌خواند و با ez_reservation (query_execution) در wp_zb_booking_history به‌روزرسانی می‌کند.
- **بهینه‌سازی:** (۱) فقط در صورت تغییر quantity به‌روزرسانی کن. (۲) وابستگی به ساختار $items را در docblock بنویس. (۳) در صورت خطای ez_reservation، لاگ کن و به کاربر ادمین اطلاع بده.

## wc_make_processing_orders_editable
- **تابع در saeed-codes.php:** `wc_make_processing_orders_editable($is_editable, $order)` — فیلتر `wc_order_is_editable`
- **جایگزین:** تابع اصلی است.
- **کارایی:** سفارش‌های با وضعیت «در حال پردازش» (processing) را قابل ویرایش می‌کند تا پشتیبان بتواند آیتم/مقدار را تغییر دهد.
- **بهینه‌سازی:** فقط برای نقش ادمین یا sans_manager true برگردان تا مشتری نتواند سفارش پرداخت‌شده را عوض کند.
