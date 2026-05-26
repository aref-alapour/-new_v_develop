# Ajax Gateway security (P4-A)

Signed POST `/ajax` requests go through a single pipeline in `GatewayDispatcher` (light `ez-ajax.php` and full WordPress router).

## Pipeline order

1. POST only
2. `EZ_AJAX_SHARED_SECRET` from `secrets.enc`
3. Parse action + headers
4. **RateLimiter** → `429` + `Retry-After` + `RATE_LIMITED`
5. **SignatureVerifier** (HMAC) → `401`
6. **NonceStore** (replay) → `401 REPLAY`
7. **ActionPolicy** (read/write × client_kind × light) → `403`
8. **BookingAuthorizationService** (write only, full path) → `403` / `AUTH_REQUIRED`
9. `ActionRegistry::dispatch()`

## Action classification

| Class | Actions |
|-------|---------|
| Read | `booking.sans_day_json`, `booking.sans_day`, `booking.sans_week` |
| Write | `booking.open_sans`, `booking.close_sans` |
| Write HTML | `booking.sans_management_web` |

## Policy matrix

| Context | `web-anon` | `web-user` / `web-team` |
|---------|------------|-------------------------|
| Light gateway (`ez-ajax.php`) | Read only | Read only |
| Full WP gateway | Read only | Read + write after login + owner/manager check |

Light path is Apache-routed only for `booking.sans_day_json` (see `booking-dev-db.md` §7).

## Boot `client_kind`

Theme emits `apply_filters( 'ez_ajax_boot_data', $boot )`. Core sets `web-user` on WooCommerce `sans-manager` when logged in (`GatewayModule::filterBootClientKind`).

## Rate limits

Defaults (override in `secrets.enc` → `gateway.rate_limits`):

| Key | per IP / min | per client_id / min |
|-----|--------------|---------------------|
| `booking.sans_day_json` | 120 | 60 |
| `default` | 60 | 30 |

Store: WordPress object cache when `wp_using_ext_object_cache()` (Redis recommended in production); otherwise file cache under `sys_get_temp_dir()/ez_cache`.

Filter: `ez_gateway_rate_limit` — `(array $config, string $action)`.

## Authorization (write)

- Never trust `user_id` from JSON body; use `get_current_user_id()`.
- `manage_options` → allow.
- Else `products_data.owner_id` / `manager_id` on connection `external`.
- Filter: `ez_booking_can_manage_product`.

## Deferred (P4-B+)

Payload AES-GCM, sans_week dedupe, removing `web-service/reservation.php`, CAPTCHA/WAF.
