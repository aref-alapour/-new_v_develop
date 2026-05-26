# Dev setup: escapezo_queries (booking sans)

Booking reads `products_data`, `calendar_data`, and `wp_zb_booking_history` from the **external** MySQL database `escapezo_queries`. Without it, `/ajax` actions return `[]` and `debug.log` shows:

```text
[EZ Booking] External DB unavailable for type=get_sanses
```

## 1. Environment

Copy [`.env.example`](../../../.env.example) to `.env` (or set Docker env vars):

| Variable | Typical dev value |
|----------|-------------------|
| `WORDPRESS_DB_EXT_NAME` | `escapezo_queries` |
| `WORDPRESS_DB_EXT_HOST` | `mysql` |
| `WORDPRESS_DB_EXT_USER` | same as `WORDPRESS_DB_USER` |
| `WORDPRESS_DB_EXT_PASSWORD` | **same as** `WORDPRESS_DB_PASSWORD` |

[`wp-config.php`](../../../wp-config.php) and [`wp-config-docker.php`](../../../wp-config-docker.php) both define `DB_EXT_*` and booking flags. Keep them in sync when adding new constants.

| Constant / env | `wp-config.php` default | `wp-config-docker.php` default |
|----------------|----------------------|------------------------------|
| `EZ_BOOKING_USE_INTERNAL` | env `1` | env `1` |
| `EZ_BOOKING_NATIVE_SANSES` | env `1` | env `1` |

**MU-plugin loader:** use only [`wp-content/mu-plugins/ez_core.php`](../../../wp-content/mu-plugins/ez_core.php). Do not add a second loader (`ez-core.php` was removed as duplicate).

**Front bundle after JS changes:** `cd wp-content/themes/escapezoom-v2 && npm run build:front:js`, then hard refresh.

## 2. Create / import database

```bash
php wp-content/mu-plugins/ez_core/bin/import-escapezo-queries-hint.php
```

Example (adjust container name and password):

```bash
docker exec -i escapezoom_dev-mysql-1 mysql -uroot -p"$MYSQL_PASSWORD" -e "CREATE DATABASE IF NOT EXISTS escapezo_queries CHARACTER SET utf8mb4;"
docker exec -i escapezoom_dev-mysql-1 mysql -uroot -p"$MYSQL_PASSWORD" escapezo_queries < docs/escapezo_queries.sql
```

Minimum tables: `products_data`, `calendar_data`, `wp_zb_booking_history`.

## 3. Health check

```bash
php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
```

Expect `RESULT: OK` and sample rows for product IDs `692762`, `762302`.

## 4. Parity + native path

```bash
php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 692762 <day_start_unix> 1
```

When parity is OK, ensure in `wp-config-docker.php` (or env):

```php
define( 'EZ_BOOKING_USE_INTERNAL', true );
define( 'EZ_BOOKING_NATIVE_SANSES', true );
```

## 5. HAR verification (manual)

After fix, in Chrome Network:

| Check | Expected |
|-------|----------|
| `booking.sans_day_json` | 200, JSON with `time` / `status`, not `[]` |
| `wait` (TTFB) | &lt; ~2s with native + warm cache |
| Response header | `X-EZ-Booking-Path: native` when `WP_DEBUG` on |
| `booking.sans_week` | HTML with sans buttons inside `flex justify-between` |
| `debug.log` | no new `External DB unavailable` lines |
| Request body | still plain JSON + HMAC headers (AES out of scope) |

## 6. Troubleshooting

| Symptom | Action |
|---------|--------|
| `[]` + DB unavailable log | Run health script; fix password/host; import DB |
| `X-EZ-Booking-Path: legacy` | Set `EZ_BOOKING_NATIVE_SANSES` true |
| Still slow (~14s) on legacy | Enable native flag; legacy light bootstrap only helps `get_sanses` fallback |
| Parity mismatch | Compare timezone / `calendar_data` / schedule column |
| Reserve page shows raw JSON | Rebuild `dist/front.js`; `sansWeekHtml` renders JSON to HTML client-side |
| Still slow after native on | Confirm `X-EZ-Gateway: light` on `booking.sans_day_json`; restart Apache after `.htaccess` change |
| No `X-EZ-Gateway: light` | Apache must read `.htaccess`; header `X-EZ-Action: booking.sans_day_json` required |

## 7. Light `/ajax` for `booking.sans_day_json`

Calendar day clicks on single-product use `booking.sans_day_json`. Those requests are routed to `ez-ajax.php` at the site root (no `wp-settings.php`) when:

- `POST /ajax`
- Header `X-EZ-Action: booking.sans_day_json`

`.htaccess` rule (before WordPress catch-all):

```apache
RewriteCond %{REQUEST_METHOD} POST
RewriteCond %{HTTP:X-EZ-Action} ^booking\.sans_day_json$ [NC]
RewriteRule ^ajax/?$ ez-ajax.php [L]
```

Verify in DevTools → Network → response headers:

- `X-EZ-Gateway: light`
- `X-EZ-Booking-Path: native` (when `WP_DEBUG` / dev)
- TTFB should drop from ~7–19s to well under 1s (DB + query only)

Reserve week (`booking.sans_week`) and other actions still use full WordPress via `index.php`.
