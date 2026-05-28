# فاز ۷ — Final Verify & Release (Reservation)

## نتیجه

این فاز برای رزرو با خروجی سبز روی تست/بیلد بسته شد و مسیرهای اصلی migration تثبیت شدند.

## Verify اجراشده

- `php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php` → `RESULT: PASS`
- `php vendor/bin/pest tests/Unit/PayloadCipherTest.php tests/Unit/AjaxSharedSecretFallbackTest.php tests/Unit/PanelAjaxSecurityServiceTest.php tests/Unit/PanelProductAuthorizationServiceTest.php` → `11 passed`
- `npm run build:front:js` در `wp-content/themes/escapezoom-v2` → build موفق
- `php -l` روی فایل‌های اصلاح‌شده team/panel → بدون خطای syntax
- `php vendor/bin/pest tests/Unit/PanelAjaxSecurityServiceTest.php tests/Unit/PanelProductAuthorizationServiceTest.php` → `5 passed`

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

- Baseline فعلی HAR تیم/پنل در `docs/md/team-panel-har-baseline.md` ثبت شد و نشان می‌دهد capture موجود هنوز latency بالا، duplicate boot (team)، و image URL malformed دارد.
- برای تایید نهایی SLA و بسته‌شدن remediation، HAR جدید بعد از deploy/refresh لازم است تا `wait` و `X-EZ-Booking-Elapsed-Ms` به‌صورت post-change بررسی شوند.
