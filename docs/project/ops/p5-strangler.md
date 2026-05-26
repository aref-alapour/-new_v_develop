# P5 — Strangler backlog (post P4-B)

Track remaining migration off legacy `web-service/` and jQuery booking paths.

## Week load / performance

- [ ] Single `booking.sans_week` (or `days:7`) per calendar navigation instead of N× `sans_day_json`
- [ ] Benchmark TTFB with `gateway.payload_encrypt_reads` before enabling in production

## Front-end toolchain

- [ ] Vite-only booking bundle; remove duplicate jQuery `$.ajax` paths in theme
- [ ] Rebuild `dist/front.js` on every gateway client change (`npm run build:front:js`)

## Legacy HTTP surfaces

- [ ] Inventory all `web-service/` callers (grep `web-service/`)
- [ ] Remove or 410 `web-service/team/sans_management.php` after team + owner UIs use gateway only
- [ ] Retire `web-service/includes/reservation-handlers.inc.php` write paths

## Production hardening

- [ ] Mandatory Redis object cache (`wp_using_ext_object_cache()`) for nonce + rate limit
- [ ] Deploy `.htaccess.gateway.example` / `apache-ajax-light.conf` on each environment
- [ ] Edge CAPTCHA / WAF for anonymous read flood on `booking.sans_day_json`

## MU-plugin hygiene

- [ ] Canonical loader: `wp-content/mu-plugins/ez_core.php` only (no `ez-core.php` duplicate)
