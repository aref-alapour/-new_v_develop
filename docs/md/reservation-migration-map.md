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

- Runtime callsites removed from `wp-content` for:
  - `web-service/team/sans_management.php`
  - `web-service/includes/reservation-bootstrap.php`
  - `web-service/includes/reservation-handlers.inc.php`
- Legacy reservation include chain does not have active runtime callers in team/panel path.
- Physical legacy file cleanup is partially complete; repository still contains non-runtime `web-service` files and should be cleaned in a dedicated final deletion pass.

## Performance/Security hardening (team/panel)

- `TeamSansBridge::searchProducts()` now prioritizes prefix search and bounded fallback search instead of unconditional broad scan.
- Game-search image URL normalization now avoids double `/wp-content/uploads/` prefix.
- `EloquentBookingLockRepository` supports narrow lock fetch by requested slot times; `SansManagementWebHtmlService` consumes this narrowed path.
- `BookingGatewayActions` emits `X-EZ-Booking-Elapsed-Ms` for team/panel actions (`game_search`, `check_playing`, toggle and bulk writes).
- Panel ownership guard added at `v2_ajax_handler` entrypoint through `PanelAjaxSecurityService::assertOwnershipFromRequest()`.
- `TeamSansWriteService` bulk open/close reduced N+1 patterns:
  - batch delete for open-all
  - prefetch statuses + lock times for close-all
  - batch insert for close-all

## HAR baseline

- Before-state HAR matrix for team/panel actions is documented at:
  - `docs/md/team-panel-har-baseline.md`

## Recommended Removal Order

1. Keep `BookingDispatchService` as function-only bridge (no file include fallback).
2. Keep team/panel on `/ajax` gateway and `admin-ajax` secured callbacks only.
3. Continue monitoring non-team/non-panel reservation modules before broader cleanup.
