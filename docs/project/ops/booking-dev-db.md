# Dev setup: escapezo_queries (booking sans)

Booking reads `products_data`, `calendar_data`, and `wp_zb_booking_history` from the **external** MySQL database `escapezo_queries`. Without it, `/ajax` actions return `[]` and `debug.log` shows:

```text
[EZ Booking] External DB unavailable for type=get_sanses
```

## 1. Encrypted secrets (required)

Credentials live in [`wp-content/mu-plugins/ez_core/config/secrets.enc`](../../../wp-content/mu-plugins/ez_core/config/secrets.enc) (not in git). Only **`EZ_CORE_SECRETS_KEY`** is needed in Docker/host env — see [`.env.example`](../../../.env.example).

**Quick dev (one command, repo root):**

```bash
php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php
```

Creates `config/secrets.enc`, `config/secrets.plain.json`, and root `.env` with `EZ_CORE_SECRETS_KEY`. Uses `WORDPRESS_DB_*` env when set (defaults: host `mysql`, user `root`, password `arefpassword`). **No container restart required for the key** — `SecretsLoader` reads `EZ_CORE_SECRETS_KEY` from repo-root `.env` when process env is empty. Optionally also set `EZ_CORE_SECRETS_KEY` in Docker env for production.

**Manual:**

```bash
cp wp-content/mu-plugins/ez_core/config/secrets.plain.example.json wp-content/mu-plugins/ez_core/config/secrets.plain.json
# edit passwords/secrets
export EZ_CORE_SECRETS_KEY="$(php -r 'echo base64_encode(sodium_crypto_secretbox_keygen());')"
php wp-content/mu-plugins/ez_core/bin/secrets-encrypt.php
```

`secrets.plain.json` structure:

| Section | Fields |
|---------|--------|
| `external` | `host`, `database`, `username`, `password` — `escapezo_queries` (booking) |
| `wordpress` | `host`, `database`, `username`, `password`, `table_prefix` — main WP DB |
| `gateway` | `ajax_shared_secret`, `booking_use_internal`, `booking_native_sanses` |

`EZ_AJAX_SHARED_SECRET` is defined **only** from `secrets.enc` (not from `wp-config.php`). Page boot (`__EZ_BOOT__`) and light `/ajax` must use the same `gateway.ajax_shared_secret`.

**Migrate existing secrets (add `wordpress`, sync legacy ajax secret):**

```bash
php wp-content/mu-plugins/ez_core/bin/secrets-migrate.php --legacy-ajax
```

Then hard-refresh the browser.

On WordPress boot, MU-plugin defines bridge constants `DB_EXT_*` and `EZ_*` from decrypted secrets (for legacy theme code). WordPress core still uses `wp-config` `DB_*`; Capsule `wordpress` connection reads from secrets first.

**MU-plugin loader:** use only [`wp-content/mu-plugins/ez_core.php`](../../../wp-content/mu-plugins/ez_core.php) (not `ez-core.php`).

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
export EZ_CORE_SECRETS_KEY="..."
php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
```

Expect `RESULT: OK` and sample rows for product IDs `692762`, `762302`.

## 4. Parity + native path

```bash
php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 692762 <day_start_unix> 1
```

Native path is controlled in `secrets.enc` → `gateway.booking_native_sanses` (typically `true` after parity OK).

## 5. HAR verification (manual)

After fix, in Chrome Network:

| Check | Expected |
|-------|----------|
| `booking.sans_day_json` | 200, JSON with `time` / `status`, not `[]` |
| `wait` (TTFB) | &lt; ~2s with native + warm cache |
| Response header | `X-EZ-Gateway: light` on day clicks |
| Response header | `X-EZ-Booking-Path: native` when `WP_DEBUG` on |
| Reserve week navigation | one `booking.sans_day_json` with `days:7` per click (not duplicate `sans_week`) |
| `debug.log` | no new `External DB unavailable` lines |

## 6. Troubleshooting

| Symptom | Action |
|---------|--------|
| `[]` + DB unavailable log | Run health script; fix `secrets.enc`; import DB |
| Light ajax 503 SECRETS | Set `EZ_CORE_SECRETS_KEY`; run `secrets-encrypt.php` or `secrets-init-dev.php` |
| 401 `BAD_SIGNATURE` on `/ajax` | Run `secrets-migrate.php --legacy-ajax` or align `gateway.ajax_shared_secret` with boot; hard refresh |
| `EZ_AJAX_SHARED_SECRET: MISSING` in health | Re-encrypt secrets; ensure `ajax-secret.php` runs after `load-secrets.php` |
| Capsule wordpress FAIL | Add `wordpress` block to secrets; run `secrets-migrate.php` |
| `X-EZ-Booking-Path: legacy` | Set `gateway.booking_native_sanses` true in secrets |
| Reserve page shows raw JSON | Rebuild `dist/front.js`; week uses client-side JSON render |
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

Reserve week grid uses the same action with `days: 7` in the JSON body (light path). Fallback `booking.sans_week` (full WP) only if JSON path fails.

Verify in DevTools → Network → response headers:

- `X-EZ-Gateway: light`
- `X-EZ-Booking-Path: native` (when `WP_DEBUG` / dev)
- TTFB should drop from ~7–19s to well under 1s (DB + query only)
