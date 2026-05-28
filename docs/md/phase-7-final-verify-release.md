# فاز ۷ — Final Verify & Release (Reservation)

## نتیجه

این فاز برای رزرو با خروجی سبز روی تست/بیلد بسته شد و مسیرهای اصلی migration تثبیت شدند.

## Verify اجراشده

- `php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php` → `RESULT: PASS`
- `php vendor/bin/pest tests/Unit/PayloadCipherTest.php tests/Unit/AjaxSharedSecretFallbackTest.php tests/Unit/PanelAjaxSecurityServiceTest.php tests/Unit/PanelProductAuthorizationServiceTest.php` → `11 passed`
- `npm run build:front:js` در `wp-content/themes/escapezoom-v2` → build موفق

## نقاط تکمیل‌شده نسبت به پلن

- Boot و shared-secret پایدار در mode پروژه داخلی
- Crypto policy قفل‌شده: read signature-only / write encryption+signature
- حذف fallback read path رزرو (native-only برای مسیرهای اصلی)
- migration map وابستگی‌های reservation ثبت شد
- instrumentation latency با `X-EZ-Booking-Elapsed-Ms` اضافه شد
- team/panel runtime dependency به `web-service` حذف شد (callsite + include fallback)
- telemetry تیم/پنل کامل شد: `X-EZ-Booking-Elapsed-Ms` برای `game_search`, `check_playing`, toggle/bulk writeها
- duplicate boot emission کاهش یافت و چاپ مستقیم boot از `header.php` و `template/team/layout.php` حذف شد (مسیر hook-based باقی ماند)

## یادداشت عملیاتی

- برای تایید نهایی SLA روی محیط مرورگر، HAR جدید لازم است تا `wait` و `X-EZ-Booking-Elapsed-Ms` کنار هم بررسی شوند.
- حذف فیزیکی کامل فایل‌های legacy باقی‌مانده در `web-service` هنوز نیازمند پاس نهایی Gate و یک pass cleanup جداگانه است.
