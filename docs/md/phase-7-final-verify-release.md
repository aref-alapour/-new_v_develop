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
- مسیر standalone برای `/ajax` با سوییچ canary اضافه شد:
  - `EZ_AJAX_STANDALONE_ENABLED=true` ⇒ bypass rewrite/template_redirect
  - rollback فوری با خاموش‌کردن flag
  - **Deploy:** `.htaccess` rule + `docs/project/ops/apache-ajax-light.conf` (all POST `/ajax` → `ez-ajax-standalone.php`)
  - WP-path fallback: `GatewayDispatcher` uses `ArrayStore` rate limiter on `ez_ajax_gateway` rewrite
- booking action registration برای مسیر standalone به حالت lazy بر اساس action درآمد (کاهش eager load).
- contract نسخه‌ای `booking.sans_management_data` (v2 JSON) اضافه شد (پشت rollout flag در UI).
- telemetry تفکیکی pre-dispatch اضافه شد:
  - `X-EZ-Gateway-Phase-Rate-Ms`
  - `X-EZ-Gateway-Phase-Auth-Ms`
  - `X-EZ-Gateway-Phase-Crypto-Ms`
  - `X-EZ-Gateway-Phase-Policy-Ms`
  - `X-EZ-Gateway-Phase-Owner-Ms`
  - `X-EZ-Gateway-PreDispatch-Ms`

## یادداشت عملیاتی

- Baseline فعلی HAR تیم/پنل در `docs/md/team-panel-har-baseline.md` ثبت شد و نشان می‌دهد capture موجود هنوز latency بالا، duplicate boot (team)، و image URL malformed دارد.
- برای تایید نهایی SLA و بسته‌شدن remediation، HAR جدید بعد از deploy/refresh لازم است تا `wait` و `X-EZ-Booking-Elapsed-Ms` به‌صورت post-change بررسی شوند.
- برای تایید Native-speed، HAR باید علاوه بر `X-EZ-Booking-Elapsed-Ms` شامل phase headers بالا نیز باشد.

## DB index gate (manual verify)

برای مسیرهای team/panel، قبل از Go-Live وضعیت indexها باید تایید شود:

- `wp_zb_booking_history (room_id, booking_time, status, booked_time)`
- `wp_zb_booking_lock (product_id, booking_time, lock_time)`

پیشنهاد: EXPLAIN روی queryهای `sans_management_web` و `check_playing` گرفته شود و full-scan نداشته باشند.
