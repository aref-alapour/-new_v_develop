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

[`wp-config-docker.php`](../../../wp-config-docker.php) maps these to `DB_EXT_*` constants for Capsule and `web-service/db-connect.php`.

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
