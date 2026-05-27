# فاز ۵.۱ — مسیر جستجوی بازی CRM

## تغییر (2026-05)

جستجوی بازی در **CRM `/team/sans_management/`** دیگر از gateway action `booking.game_search` استفاده نمی‌کند.

| قبل | بعد |
|-----|-----|
| `POST /ajax` + `booking.game_search` | `POST admin-ajax.php` + `action=ez_team_sans_game_search` |
| اسکن `products_data` (external) | `wp_products_search` (WordPress DB) + batch metadata از `products_data` |
| ~13s | هدف &lt;500ms |

## Core

- [GameSearchService.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Team/GameSearchService.php)
- [WordpressProductsSearchRepository.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Infrastructure/Eloquent/WordpressProductsSearchRepository.php)
- [TeamGameSearchAjaxController.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Ajax/TeamGameSearchAjaxController.php)

## Theme

- [sans_management.php](../../wp-content/themes/escapezoom-v2/template/team/pages/sans_management.php) — `fetch(teamAjaxUrl)` با nonce `team-ajax-nonce`

## گرید سانس

`sans_management_web` از [SansManagementWebHtmlService.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Team/SansManagementWebHtmlService.php) (Eloquent) — بدون `BookingDispatchService`.

## single-product سرعت

- کش `BookingService` TTL **60s**
- dev: `payload_encrypt_reads: false` در `secrets-init-dev.php`
- light gateway: `X-EZ-Action: booking.sans_day_json` → [ez-ajax.php](../../ez-ajax.php)
- Redis (اختیاری): `WP_REDIS_HOST` + [object-cache.php](../../wp-content/object-cache.php)

## وضعیت `reserving` در گرید CRM

[SansManagementWebHtmlService.php](../../wp-content/mu-plugins/ez_core/src/Modules/Booking/Services/Team/SansManagementWebHtmlService.php) از `booking_lock_schedule` با TTL **300s** (مثل legacy) وضعیت `reserving` را نشان می‌دهد.

## چک‌لیست HAR / Docker (بستن ۵.۱)

داخل کانتینر WordPress:

```bash
php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php   # اگر secrets.enc نیست
php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
```

| صفحه | درخواست | معیار |
|------|---------|--------|
| `/team/sans_management/` | `ez_team_sans_game_search` | &lt; 500ms |
| همان | `booking.sans_management_web` | 200 HTML |
| `/panel/sans-manager/` | View Source boot | `sub_secret`, `client_kind: web-user` |
| همان | `booking.sans_management_web` | ≥1 موفق |
| single-product | `booking.sans_day_json` | `X-EZ-Gateway: light` |
| بار دوم همان روز | همان action | &lt; 1s با Redis |

`secrets.enc` در git نیست — در deploy باید `EZ_CORE_SECRETS_KEY` به کانتینر برسد.

نتایج verify: [phase-5-1-har-results.md](./phase-5-1-har-results.md)
