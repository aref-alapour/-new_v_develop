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
