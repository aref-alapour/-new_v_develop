# Reservation Migration Map

## Scope

This map tracks reservation-related dependencies and migration status from legacy `web-service` to `ez_core` gateway actions.

## Status Matrix

| Area | Current path | Legacy dependency | Status |
|---|---|---|---|
| single-product day JSON | `booking.sans_day_json` via `/ajax` | none in read path | migrated |
| reserve week | `booking.sans_week` via `/ajax` | removed fallback in `booking-reserve-week.php` | migrated |
| team sans management HTML | `booking.sans_management_web` via `/ajax` | helper include (`web-service/ez-sans-mojavezedar-wp.php`) | partial |
| team check playing | `booking.check_playing` via `/ajax` | no direct dispatch | migrated |
| team game search | `ez_team_sans_game_search` admin-ajax + core service | none | migrated |
| open/close sans | `booking.open_sans` / `booking.close_sans` | none (`TeamSansWriteService`) | migrated |
| bulk day open/close | `booking.open_all_sanses` / `booking.close_all_sanses` | none (`TeamSansWriteService`) | migrated |
| bulk date range | `booking.bulk_date_range` | core bridge/service only | migrated |
| panel sensitive callbacks | `v2_ajax_handler` + `PanelAjaxSecurityService` | legacy callback files still present | partial |
| old reservation helpers (`get_sanses`, `ez_webservice`) | theme helper paths | yes (`inc/saeed-codes.php`, legacy templates) | pending cleanup |

## Remaining High-Priority Legacy Hotspots

- `wp-content/themes/escapezoom-v2/inc/saeed-codes.php`
- `wp-content/mu-plugins/ez_core/src/Modules/Booking/BookingDispatchService.php`
- `wp-content/mu-plugins/ez_core/src/Modules/Booking/Infrastructure/LegacySansAdapter.php`
- `web-service/team/sans_management.php`
- `web-service/includes/reservation-handlers.inc.php`

## Recommended Removal Order

1. Keep `BookingDispatchService` unavailable for reservation read paths (already done).
2. Replace remaining reservation callsites in theme with `/ajax` gateway actions.
3. Gate `web-service/team/sans_management.php` to internal-only for all migrated types.
4. Remove `LegacySansAdapter` and read fallback diagnostics after regression window.
5. Delete/Archive reservation sections from `web-service` once no active caller remains.
