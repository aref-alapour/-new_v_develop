# Ajax Gateway security (P4-A / P4-B)

Signed POST `/ajax` requests go through a single pipeline in `GatewayDispatcher` (light `ez-ajax.php` and full WordPress router).

## Pipeline order

1. POST only
2. `EZ_AJAX_SHARED_SECRET` from `secrets.enc`
3. Parse action + headers
4. **RateLimiter** → `429` + `Retry-After` + `RATE_LIMITED`
5. **SignatureVerifier** (HMAC on **wire body**) → `401`
6. **NonceStore** (replay) → `401 REPLAY`
7. **PayloadCipher** decrypt when envelope or encryption required → `400`
8. **ActionPolicy** (read/write × client_kind × light) → `403`
9. **SansManagementAuthorizationService** (write only, full path) → `403` / `AUTH_REQUIRED` — uses `client_kind` from `X-EZ-Client-Kind`
10. `ActionRegistry::dispatch()` → **GatewayResponse** encrypts success body when `payload_encrypt_reads` / `payload_encrypt_writes` match action class (`X-EZ-Response-Encrypted: v1`)

When `gateway.payload_encrypt_writes` or `payload_encrypt_reads` is true, the client sends an AES-GCM envelope; the HMAC is computed over the envelope JSON string, not the inner plaintext.

## Action classification

| Class | Actions |
|-------|---------|
| Read | `booking.sans_day_json`, `booking.sans_day`, `booking.sans_week` |
| Write | `booking.open_sans`, `booking.close_sans`, `booking.open_all_sanses`, `booking.close_all_sanses`, `booking.bulk_date_range` |
| Write HTML | `booking.sans_management_web`, `booking.check_playing`, `booking.game_search` |

## Policy matrix

| Context | `web-anon` | `web-user` / `web-team` |
|---------|------------|-------------------------|
| Light gateway (`ez-ajax.php`) | Read only | Read only |
| Full WP gateway | Read only | Read + write after login; `web-user` → owner/manager; `web-team` → CRM sans-management roles |

Light path is Apache-routed only for `booking.sans_day_json` (see `booking-dev-db.md` §7 and `apache-ajax-light.conf`).

**Writes never use the light gateway** — owner checks require full WordPress bootstrap.

## Boot `client_kind`

Theme emits `apply_filters( 'ez_ajax_boot_data', $boot )`. Core sets:

- `web-user` on WooCommerce `sans-manager` when logged in
- `web-team` on `/team/sans_management/` (`team_page` query var + menu access)

Boot surfaces (`ez_ajax_should_boot()`): product single, `reserve`, `sans-manager`, team sans-management. Team layout prints boot inline before `wp_head()`.

## Payload encryption (P4-B)

Secrets (`secrets.enc`):

- `gateway.payload_encrypt_writes` — write actions: request **and** success response use envelope `{ "ez_enc": "v1", "iv", "ct" }`
- `gateway.payload_encrypt_reads` — read actions: request **and** success response encrypted when enabled (light `sans_day_json`, `sans_day`, `sans_week`)

Boot exposes `encrypt_writes` / `encrypt_reads` to `ez-ajax.js` (`shouldEncryptPayload` for requests).

**Request:** client sets `X-EZ-Encrypted: v1`; HMAC is over the wire envelope JSON.

**Response (P4-B.2):** `GatewayResponse` encrypts `raw()` / `html()` / success `json()` when the flag matches the action class; header `X-EZ-Response-Encrypted: v1` and `Content-Type: application/json` are sent in one phase from `sendWireBody` (light gateway diagnostic headers are queued via `$GLOBALS['ez_gateway_response_headers']` in `ez-ajax.php`, not `header()` before dispatch). Error envelopes `{ ok: false }` stay **plain** (401/403/validation).

Client: `readGatewayBodyText()` in `ez-ajax.js` decrypts when the wire body is an envelope `{ ez_enc, iv, ct }` **or** when `X-EZ-Response-Encrypted: v1` is present (header is a signal, not required for decrypt). Plaintext is then passed to `parseGatewaySansJson` / HTML swap.

Server: `PayloadCipher` (AES-256-GCM, key = decoded `sub_secret`). Client: `@noble/ciphers` GCM (same envelope; GCM tag is inside `ct`, no separate `tag` field).

## Closed bypasses (P4-A.1)

| Surface | Status |
|---------|--------|
| Owner `sans-manager` bulk open/close day | `booking.open_all_sanses` / `booking.close_all_sanses` via `ezFetch` |
| Team `sans_management` grid / toggle / bulk day | `booking.sans_management_web`, toggle, bulk + `web-team` |
| Team `check_playing`, `game_search`, bulk date range | `booking.check_playing`, `booking.game_search`, `booking.bulk_date_range` (P5.0) |
| Legacy `web-service/team/sans_management.php` | Deprecated for team UI; file kept for other callers until P5.1 |

## Rate limits

Defaults (override in `secrets.enc` → `gateway.rate_limits`):

| Key | per IP / min | per client_id / min |
|-----|--------------|---------------------|
| `booking.sans_day_json` | 120 | 60 |
| `booking.game_search` | 30 | 20 |
| `booking.check_playing` | 60 | 40 |
| `booking.bulk_date_range` | 10 | 5 |
| `default` | 60 | 30 |

Store: WordPress object cache when `wp_using_ext_object_cache()` (Redis recommended in production); otherwise file cache under `sys_get_temp_dir()/ez_cache`.

Filter: `ez_gateway_rate_limit` — `(array $config, string $action)`.

## Authorization (write)

`SansManagementAuthorizationService` (delegates owner check to `BookingAuthorizationService` for `web-user`):

- Never trust `user_id` from JSON body; use `get_current_user_id()`.
- `manage_options` → allow (all kinds).
- `web-user`: `products_data.owner_id` / `manager_id` on connection `external`; filter `ez_booking_can_manage_product`.
- `web-team`: roles from filter `ez_team_sans_management_roles` (default: administrator, supervisor, poshtiban, team_admin). `booking.game_search` requires team access only (no `product_id`).
- Optional flag: `EZ_BOOKING_SANS_PANELS_GATEWAY` (default true) disables boot on sans panels when set false.

## Ops checks

```bash
cd wp-content/mu-plugins/ez_core
php bin/booking-db-health.php   # sodium, ActionPolicy smoke, encrypt flags
php vendor/bin/pest --testsuite=Unit
```

Deploy: copy `docs/project/ops/apache-ajax-light.conf` or `.htaccess.gateway.example` into site `.htaccess`.

## P5 backlog

See [p5-strangler.md](./p5-strangler.md).
