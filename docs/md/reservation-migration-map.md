# Reservation Migration Map

## Scope

This map tracks reservation-related dependencies and migration status from legacy `web-service` to `ez_core` gateway actions.

## Status Matrix

| Area | Current path | Legacy dependency | Status |
|---|---|---|---|
| single-product day JSON | `booking.sans_day_json` via `/ajax` | none in read path | migrated |
| reserve week | `booking.sans_week` via `/ajax` | removed fallback in `booking-reserve-week.php` | migrated |
| team sans management HTML | `booking.sans_management_web` via `/ajax` | none | migrated |
| team check playing | `booking.check_playing` via `/ajax` | no direct dispatch | migrated |
| team game search | `ez_team_sans_game_search` admin-ajax + core service | none | migrated |
| open/close sans | `booking.open_sans` / `booking.close_sans` | none (`TeamSansWriteService`) | migrated |
| bulk day open/close | `booking.open_all_sanses` / `booking.close_all_sanses` | none (`TeamSansWriteService`) | migrated |
| bulk date range | `booking.bulk_date_range` | core bridge/service only | migrated |
| panel sensitive callbacks | `v2_ajax_handler` + `PanelAjaxSecurityService` | legacy callback files still present | secured |
| team comments game search | `admin-ajax` `ez_team_sans_game_search` | direct web-service URL removed | migrated |
| old reservation helpers (`get_sanses`, `ez_webservice`) | theme helper paths | no runtime web-service include in team/panel | isolated |

## Legacy Removal Result (team/panel scope)

- Removed: `web-service/team/sans_management.php`
- Removed: `web-service/ez-sans-mojavezedar-wp.php`
- Removed: `web-service/includes/reservation-dispatch.php`
- Removed: `web-service/includes/reservation-bootstrap.php`
- Removed: `web-service/includes/reservation-handlers.inc.php`
- Removed: `web-service/includes/reservation-functions.inc.php`

## Recommended Removal Order

1. Keep `BookingDispatchService` as function-only bridge (no file include fallback).
2. Keep team/panel on `/ajax` gateway and `admin-ajax` secured callbacks only.
3. Continue monitoring non-team/non-panel reservation modules before broader cleanup.
