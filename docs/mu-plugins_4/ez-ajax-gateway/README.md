# EZ AJAX Gateway — Phase 0/1

Central signed-request endpoint for the EscapeZoom storefront. All client JS/HTMX traffic that
needs cheap, plugin-free request lifecycle goes through `POST /ajax`.

## 1. Configure secrets

Two options — pick one.

**Option A** (dev-friendly): edit `wp-config.php` and replace the placeholder block already
added below `SAVEQUERIES`.

```php
define( 'EZ_AJAX_SHARED_SECRET', 'REPLACE-WITH-openssl-rand-base64-48-OUTPUT' );
define( 'EZ_AJAX_NONCE_TTL', 60 );
define( 'EZ_AJAX_TIMESTAMP_SKEW', 30 );
define( 'EZ_AJAX_SUB_SECRET_TTL', 900 );
define( 'EZ_BRANDS_USE_GATEWAY', false );
// Optional: max sub-secret TTL (seconds) when the theme extends lifetime per route (default 86400).
// define( 'EZ_AJAX_SUB_SECRET_TTL_MAX', 86400 );
```

**Per-route boot TTL (theme):** The storefront default remains `EZ_AJAX_SUB_SECRET_TTL` (e.g. 900s for `/brands/`). The theme (`escapezoom-v3`) can raise TTL for specific shells via `ez_ajax_sub_secret_ttl_rules` and helpers in `inc/theme/ez-ajax-sub-secret-rules.php` — first matching rule wins; values are clamped to `[60, EZ_AJAX_SUB_SECRET_TTL_MAX]` (filter `ez_ajax_sub_secret_ttl_max`). The gateway itself does not change; only `expires_at` minted into `window.__EZ_BOOT__` varies per page load.

Generate the secret with:

```bash
openssl rand -base64 48
```

**Option B** (prod-friendly, lower TTFB): copy `wp-content/mu-plugins/ez-ajax-secrets.example.php`
to `wp-content/mu-plugins/ez-ajax-secrets.php`, edit the value, and add the new path to your
`.gitignore`. The dispatcher loads this file first — no regex parsing of `wp-config.php`.

⚠️ **Never commit the real `EZ_AJAX_SHARED_SECRET`.** If you accidentally do, rotate it
immediately (regenerate, redeploy, force a hard-reload on all open clients).

## 1b. Database tables (one-time)

Gateway store tables (`{prefix}ez_ajax_nonces`, `{prefix}ez_ajax_rate`) are defined in
`wp-content/mu-plugins/ez_core/database/sql/ez_bootstrap_custom_tables.sql` — **not** created at
runtime. Apply that file once per environment (phpMyAdmin or CLI). Without these tables, signed
requests fail at the nonce store with `INTERNAL` / HTTP 500.

## 2. Tests (Pest — canonical)

Maintainable tests live in **ez_core** (PHPUnit/Pest). Run inside Docker (PHP 8.5+):

```bash
cd wp-content/mu-plugins/ez_core
composer install
composer test:gateway
```

See [`ez_core/tests/README.md`](../ez_core/tests/README.md) for env vars and suites.

## 2b. Quick smoke (procedural)

Run all preflight, store, HTTP, and parity checks in one command:

```bash
php wp-content/mu-plugins/ez-ajax-gateway/tests/run-full-gateway-tests.php
```

Options:

| Flag | Effect |
|------|--------|
| `--base-url=http://wo.escapezoom.local/ajax` | Gateway URL (default: local Docker host) |
| `--skip-http` | Preflight + in-process store/parity only |
| `--skip-rate-store` | Skip token-bucket burst test |
| `--json-only` | JSON on STDOUT, no STDERR table |
| `--verbose` | Extra detail on HTTP failures |
| `--ignore-php-platform` | Allow PHP &lt; 8.5 on host/WSL (bypasses Composer `platform_check`; dev only) |
| `--connect-timeout=3` | Seconds for DB/HTTP TCP probe before boot (avoids long hangs) |
| `--http-timeout=8` | Per-request HTTP timeout for smoke tests |

Exit code `0` when every check passes; `1` on failure; `2` on misconfiguration.

Example JSON summary: `"summary": {"pass": 18, "fail": 0, ...}, "ok": true`.

**PHP 8.5+** is required (`ez_core` Composer platform). WSL/host PHP 8.4 will fatal on autoload unless you use Docker or pass `--ignore-php-platform`.

**Run inside Docker** when host PHP lacks `pdo_mysql`, is below 8.5, or `DB_HOST=mysql` is not reachable from the host:

```bash
docker compose exec -T wordpress php wp-content/mu-plugins/ez-ajax-gateway/tests/run-full-gateway-tests.php
```

(Adjust service name to match your stack.)

## 3. Smoke test `ping`

```bash
cd wp-content/mu-plugins/ez-ajax-gateway/tools
php sign.php --action=ping --url=http://wo.escapezoom.local/ajax
```

It prints a ready-to-run `curl` line. Pipe it into `bash`:

```bash
php sign.php --action=ping --url=http://wo.escapezoom.local/ajax | tail -n +5 | bash
```

Expected response:

```json
{"ok":true,"data":{"pong":true,"server_now":1737000000,"took_ms":3}}
```

with `HTTP 200`, `TTFB < 0.100s` on a warm cache.

### 3b. Smoke test `brands.count` (Eloquent without wp-load)

Proves Capsule + DB constants work from `dispatch.php` with `wp_level=none`:

```bash
php sign.php --action=brands.count --url=http://wo.escapezoom.local/ajax | tail -n +5 | bash
```

Expected JSON includes `"wp_loaded":false` when WordPress was not bootstrapped.

## 4. Smoke test `brands.fragment`

```bash
php sign.php \
  --action=brands.fragment \
  --body='{"page":2}' \
  --url=http://wo.escapezoom.local/ajax \
  | tail -n +5 | bash
```

Expected: HTML fragment starting with `<div id="brands-directory-swap" ...`, headers
`HX-Push-Url: /brands?page=2`, `Vary: HX-Request, Accept`.

TTFB target: < 200ms in phase 1 (we still boot full WP); < 50ms in phase 1.2 (shortinit).

## 5. Enable in the browser

After parity testing, flip the flag in `wp-config.php`:

```php
define( 'EZ_BRANDS_USE_GATEWAY', true );
```

Open `/brands/` and click pagination. The HTMX requests will now go to `/ajax` instead of
`/brands?ez_brands_hx=1`. To revert, set the flag back to `false` — no code change required.

## 6. Files at a glance

```
wp-content/mu-plugins/
├── ez-ajax-gateway.php                ← loader (WP only auto-loads files directly under mu-plugins)
└── ez-ajax-gateway/
    ├── bootstrap.php                   ← autoload + (when WP available) hooks
    ├── dispatch.php                    ← entry hit by .htaccess rewrite of /ajax
    ├── secrets-bootstrap.php           ← reads EZ_AJAX_* + DB_* from wp-config.php without WP
    ├── registry.php                    ← static map of actions (action → wp_level, handler, …)
    ├── secrets.example.php             ← reference for wp-config.php constants
    ├── tests/run-full-gateway-tests.php ← automated preflight + smoke + parity suite
    ├── tests/lib/{EzAjaxSigner,GatewayHttpClient,TestReporter}.php
    ├── tools/sign.php                  ← CLI helper that prints a signed curl command
    ├── tools/dos-test.sh               ← invalid-signature burst test (avg TTFB should stay &lt; 50ms)
    ├── src/Gateway.php                 ← orchestrator
    ├── src/Loader/WpLevel.php          ← conditional wp-load.php inclusion
    ├── src/Auth/SignatureVerifier.php  ← HMAC + timestamp + nonce
    ├── src/Auth/SubKey.php             ← deriveBase64Url() + uuidV4()
    ├── src/Store/{Eloquent*,*}.php     ← nonce / rate store interfaces + ez_core repositories
    ├── src/Http/{Request,Response}.php ← framework-free request/response value objects
    ├── src/Registry/ActionRegistry.php ← static map + filter `ez_ajax_actions`
    ├── src/Logging/RequestLogger.php   ← JSONL writer to wp-content/ez-ajax-gateway.log
    └── src/Actions/Ping.php            ← wp_level=none reference action
```

## 7. TTFB benchmark recipe

```bash
# Warm DNS / opcache:
curl -sS -o /dev/null http://wo.escapezoom.local/

# Then run ping ten times and take the median:
for i in $(seq 1 10); do
  php wp-content/mu-plugins/ez-ajax-gateway/tools/sign.php \
       --action=ping --url=http://wo.escapezoom.local/ajax \
     | tail -n +5 \
     | bash 2>/dev/null \
     | grep TTFB
done
```

Targets:

| Endpoint           | wp_level   | TTFB target |
|--------------------|------------|-------------|
| `ping`             | none       | < 100 ms    |
| `brands.count`     | none       | < 100 ms    |
| `brands.fragment`  | full       | < 250 ms    |
| `brands.fragment`  | shortinit  | < 80 ms (phase 1.2) |

## 8. Security checklist

Implemented in phase 0/1:

- [x] **Hotfix:** Preflight header shape checks + **HMAC verification BEFORE `wp-load.php`** — fake
      `/ajax` requests cannot burn PHP workers booting plugins/themes (DoS-safe reject path).
- [x] **Strict routing:** Unknown `action` keys → `404 UNKNOWN_ACTION` without loading WP (must add
      new endpoints to `registry.php`).
- [x] HMAC-SHA256 signature (sub-secret derived from master via `kid|client_id|expires_at`).
- [x] Constant-time signature comparison (`hash_equals`).
- [x] Timestamp skew window (`EZ_AJAX_TIMESTAMP_SKEW`, default 30s).
- [x] Single-use nonce (atomic `INSERT IGNORE`, `EZ_AJAX_NONCE_TTL`).
- [x] Sub-secret expiry (`expires_at < now` rejected).
- [x] Per-IP + per-client token-bucket rate limit.
- [x] Sealed error codes (`BAD_SIGNATURE` / `BAD_TIMESTAMP` / `NONCE_REPLAY` / `RATE_LIMITED`
      / `UNKNOWN_ACTION` / `INTERNAL` / `BAD_REQUEST`) — never leak details.
- [x] HTTPS enforcement when `WP_DEBUG=false` (override with `EZ_AJAX_REQUIRE_HTTPS`).
- [x] Action whitelist via static registry (no reflective handler resolution).

Deferred to phase 2:

- [ ] Redis nonce store + rate limiter (Lua atomic) for sub-millisecond hot path.
- [ ] **`auth.bootstrap`** — mint a fresh sub-secret without a full page reload (design sketch below).
- [ ] Web-user / RN-user `client_kind` (sub-secret stored in HttpOnly cookie, not HTML).
- [ ] Per-action audit log signed with a separate key.
- [ ] CSRF Origin/Referer pinning when sub-secret stored cookie-side.

### Future design sketch: `auth.bootstrap`

Roadmap only — requires a separate security review before implementation.

1. **Registry:** Add an action in `registry.php` with an appropriate `wp_level` (likely full WP so auth/session helpers exist). Handler returns JSON in the **same shape as theme boot** (`kid`, `client_id`, `sub_secret`, `expires_at`, …) without exposing the master secret.
2. **Trust model:** Decide how the **first** bootstrap or **post-expiry** mint is authorized — e.g. valid logged-in WP session (cookies), CSRF binding, rate limits, and whether prior signature/nonce are required. Document threats (replay, session fixation, abuse of bootstrap).
3. **Client (`ez-ajax.js` and callers):** On proactive expiry or after `BAD_TIMESTAMP`, call bootstrap once, merge into `window.__EZ_BOOT__`, **retry** the original gateway request; keep **`location.reload()`** as fallback when bootstrap fails or policy forbids silent refresh.
