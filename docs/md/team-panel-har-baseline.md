# Team/Panel HAR Baseline (Before Native Refactor)

Date: 2026-05-28

This baseline is extracted from:

- `C:\Users\jobal\Desktop\team-sans_management.har`
- `C:\Users\jobal\Desktop\panel-sans-manager.har`

## Action Matrix

| Surface | Action | Endpoint | Wait (ms) observed | X-EZ-Encrypted | X-EZ-Booking-Elapsed-Ms |
|---|---|---|---:|---|---|
| team/sans_management | `booking.sans_management_web` | `/ajax` | ~18437 / ~17791 | not present in capture | not present in capture |
| team/sans_management | `booking.check_playing` | `/ajax` | ~10818 / ~7769 | not present in capture | not present in capture |
| team/sans_management | `ez_team_sans_game_search` | `wp-admin/admin-ajax.php` | ~8103 / ~8582 | n/a | n/a |
| panel/sans-manager | `booking.sans_management_web` | `/ajax` | ~31266 / ~7885 | not present in capture | not present in capture |
| panel/sans-manager | `booking.close_sans` | `/ajax` | ~15091 / ~9830 | present | not present in capture |

## Critical Findings

- Team page response contains duplicate boot script (`ez-ajax-boot`) in this capture.
- Team capture includes malformed image URL with doubled uploads prefix:
  - `.../wp-content/uploads/v.escapezoom.local/wp-content/uploads/...`
- Wait latency is still far above target on team/panel paths in this baseline.
- `/ajax` was previously served through rewrite + `template_redirect` (WordPress lifecycle overhead).
- Existing baseline capture does not contain new phase headers required for native split analysis.

## Required Re-Capture Fields (Post Change)

For each target action, capture these headers:

- `X-EZ-Booking-Elapsed-Ms`
- `X-EZ-Gateway-Phase-Rate-Ms`
- `X-EZ-Gateway-Phase-Auth-Ms`
- `X-EZ-Gateway-Phase-Crypto-Ms`
- `X-EZ-Gateway-Phase-Policy-Ms`
- `X-EZ-Gateway-Phase-Owner-Ms`
- `X-EZ-Gateway-PreDispatch-Ms`
- `X-EZ-Response-Encrypted`

## Notes

- This file is intentionally a before-state matrix.
- A new HAR capture is required after remediation deployment to evaluate pass/fail against final acceptance gates.

---

## Post-Remediation (2026-05-28)

### Transport (reservation-critical)

| Surface | Action | Endpoint after change |
|---|---|---|
| single-product | product view side-effect | `booking.product_set_view` → `/ajax` (fire-and-forget; no `admin-ajax.php`) |
| single-product | sans grid | `booking.sans_day_json` → `/ajax` (unchanged) |
| team/sans_management | day grid | `booking.sans_management_data` → `/ajax` + client render (`ezSansManagementRender`) |
| team/sans_management | game search | `booking.game_search` → `/ajax` |
| team/comments | game search | `booking.game_search` → `/ajax` (was `ez_team_sans_game_search` on admin-ajax) |
| panel/sans-manager | day grid | `booking.sans_management_data` → `/ajax` + client render |

Non-reservation team/panel flows (comments CRUD, cancellation, etc.) remain on `admin-ajax.php` by design.

### Server changes (ez_core)

- Standalone `/ajax`: `Bootstrap::bootMinimal($action)` — `product_set_view` uses WP+CRM only; `sans_day_json` uses light gateway DB.
- Rate limiter on light gateway: in-memory `ArrayStore` (avoids `/tmp/ez_cache` file I/O per request).
- `TeamSansBridge::bulkDateRange` — set-based close/open instead of per-slot queries.
- Sans management split: `SansManagementDataFetcher` / `SansManagementStateResolver` / `SansManagementPresenter`.

### Rollback switches

- Revert theme callsites to `sansManagementWeb` / `admin-ajax.php` if JSON render regresses.
- Set `EZ_AJAX_STANDALONE_ENABLED=false` to route `/ajax` through WordPress rewrite again.
- `booking.sans_management_web` HTML path remains registered for compatibility.

### Acceptance gates (re-capture required)

1. `/ajax` reservation actions: TTFB p95 &lt; 1s (native, no Redis).
2. `wait - (X-Ez-Gateway-Predispatch-Ms + X-EZ-Booking-Elapsed-Ms) < 800ms`.
3. No reservation-critical `admin-ajax.php` on target pages listed above.
4. Encryption headers present where policy requires (`X-EZ-Encrypted` / `X-EZ-Response-Encrypted` on sensitive reads).

### Build

- `npm run build:front:js` in `wp-content/themes/escapezoom-v2` (includes `ez-sans-management-render.js`, `productSetView`).
