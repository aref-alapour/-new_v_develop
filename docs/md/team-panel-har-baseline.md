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

---

## Standalone gateway rollout (2026-05-28)

### Infra (required for full speed win)

1. **Apache** — block in [`.htaccess`](../../.htaccess) (before WordPress rules) routes `POST /ajax` → `wp-content/mu-plugins/ez_core/ez-ajax-standalone.php`. Snippet: [`docs/project/ops/apache-ajax-light.conf`](../project/ops/apache-ajax-light.conf).
2. **Nginx** — see [`docs/project/ops/nginx-ajax-standalone.conf`](../project/ops/nginx-ajax-standalone.conf).
3. **Env** — `EZ_AJAX_STANDALONE_ENABLED=true` in repo [`.env`](../../.env) (also read via `load-secrets.php` dotenv fallback).

### Code shipped with rollout

- WP rewrite fallback: in-memory rate limiter when standalone is not used (`GatewayDispatcher::ensureLightGatewayForWpRewrite`).
- Root [`ez-ajax.php`](../../ez-ajax.php) forwards to standalone (deprecated direct use).
- Client: `sansDayJson` promise coalescing + single-product guards (one initial `sans_day_json` per day).

### Central game search (`booking.game_search`) — 2026-05-28

- **Data source:** `wp_products_search` only (`WordpressProductsSearchRepository`); no `wp_posts`, `wp_postmeta`, or external `products_data`.
- **Query:** prefix `LIKE term%` on `product_name`, limit 50, ordered by name.
- **Service:** `GameSearchService` — `searchItems()` + 60s `wp_cache`; HTML built client-side via `ez-game-search-render.js`.
- **Gateway:** `CLASS_READ` with **forced AES** on request/response (like `sans_day_json`); client `shouldEncryptPayload` always on.
- **Speed:** after any team `/ajax` with wp-load, session cached 5min (`X-EZ-Gateway-Session: cached`) → `game_search` skips wp-load (~2s saved).
- **Boot:** `Bootstrap::bootMinimal('booking.game_search')` → `CapsuleManager::bootGameSearchOnly()` (wordpress connection only).
- **Auth:** `web-team` / `web-user` load `wp-load` **before** polyfills (`ez_core_gateway_needs_wp_bootstrap`); avoids `GATEWAY_BOOT` 500.
- **Boot:** `EZ_GATEWAY_INCOMING_ACTION` + `bootMinimal()` per action; `EZ_GATEWAY_SKIP_WP_PLUGINS` skips WooCommerce/other plugins on team/panel `/ajax` (ez_core only).
- **Build:** `standalone-wp-session-v2` + `X-EZ-Gateway-WpBoot-Ms` (measure wp-load cost).
- **Theme:** `ezBookingApi.gameSearchHtml()` → `gameSearchItems()` + `window.ezGameSearchRender`; used on team `sans_management` and `comments`.

### Panel sans-manager (`booking.sans_management_data`)

- Client: `sansManagementData()` promise coalescing (`productId:day:version`); panel page uses load token to drop stale responses.
- Server: `SansManagementWebHtmlService::getData()` cached 120s (`ez_sans_mgmt_data_*`); invalidates with HTML cache on writes.
- Encryption: respects `encrypt_reads` (not forced AES for `sans_management_data` when reads encryption is off).

### HAR verification checklist (post-deploy)

Capture fresh HAR on: single-product, reserve, team/sans_management, panel/sans-manager.

| Check | Pass criteria |
|-------|----------------|
| Standalone active | Response includes `X-EZ-Gateway-Build: standalone-p0-v2` |
| No WP cache headers on `/ajax` | No `Expires: 1984` on gateway responses |
| Rate phase | `X-EZ-Gateway-Phase-Rate-Ms` &lt; ~15ms (standalone) or &lt; ~50ms (WP fallback) |
| Reservation transport | No `admin-ajax.php` for actions in Post-Remediation table above |
| single-product dedupe | At most one in-flight `booking.sans_day_json` per `product_id:day_start_time` on initial load |
| SLA | p95 TTFB `/ajax` &lt; 1s; `wait - (Predispatch + Elapsed)` &lt; 800ms |

### CLI verify (local)

```bash
php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php
# expect: RESULT: PASS

cd wp-content/themes/escapezoom-v2 && npm run build:front:js
```

Pest: run inside Docker/CI where `pdo_mysql` and DB are available; host PHP may fail DB driver tests.
