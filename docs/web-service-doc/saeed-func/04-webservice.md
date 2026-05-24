# Wrapperهای وب‌سرویس

## ez_webservice

- **تابع در saeed-codes.php:** `ez_webservice($data)`
- **جایگزین چه تابعی:** جایگزین مستقیم فراخوانی‌های `wp_remote_post` به آدرس ثابت web-service است؛ یک نقطهٔ واحد برای تمام درخواست‌ها به `web-service.php`.
- **کارایی:** بر اساس `HTTP_HOST` آدرس پایه را تعیین می‌کند (لوکال = http، غیرلوکال = https)، سپس `wp_remote_post` با `Content-Type: application/json` و `body: json_encode($data)` اجرا می‌کند. در صورت پاسخ آرایه‌ای، `body` را برمی‌گرداند. در سراسر قالب و همین فایل برای sort_products_get، hottest_products_set، data_products_set و غیره استفاده می‌شود.
- **بهینه‌سازی:** (۱) شرط لوکال دو بار یکسان است؛ یک بار چک کن و یک `$base_url` بساز. (۲) در صورت خطای HTTP یا timeout، مقدار برگشتی تعریف نشده است؛ حتماً `is_wp_error($response)` و کد وضعیت را چک کن و در صورت خطا `null` یا آرایهٔ خطا برگردان. (۳) آدرس پایه را از یک option یا ثابت بخوان تا بدون تغییر کد بتوان محیط را عوض کرد.

---

## ez_reservation

- **تابع در saeed-codes.php:** `ez_reservation($data)`
- **جایگزین چه تابعی:** مثل ez_webservice، جایگزین فراخوانی مستقیم به `reservation.php` است.
- **کارایی:** آدرس را مثل ez_webservice می‌سازد ولی endpoint برابر `reservation.php` است. body بدون json_encode فرستاده می‌شود (طبق انتظار reservation.php). فقط در صورت کد ۲۰۰، body برگردانده می‌شود؛ وگرنه آرایهٔ خطا. در thankyou، form-checkout، api/callbacks، cancellation_functions و غیره برای query_execution، get_sans_lock، add_sans_lock و غیره استفاده می‌شود.
- **بهینه‌سازی:** (۱) تکراری بودن منطق آدرس با ez_webservice؛ یک تابع کمکی مثل `ez_web_service_base_url($script)` بساز و هر دو از آن استفاده کنند. (۲) نوع و ساختار `$data` را در docblock یا نوع آرگومان مشخص کن تا با reservation.php هماهنگ بماند. (۳) خطای شبکه/timeout را مثل ez_webservice هندل کن و خروجی یکسان برگردان.
