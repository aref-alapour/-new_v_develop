# Module: Booking (sans, lock, conflict, reservation)

> **Template module doc** — follow this structure for `payment.md`, `wallet.md`, etc.  
> **Principles only in Cursor:** `docs/.cursor/rules/05-business-domain-principles.mdc`

## Goal

Manage **sans (time slot) availability**, temporary **locks** during checkout, **conflict-free confirmation**, and lifecycle through cancel/refund — without double booking on a live, high-traffic site.

Payment amounts and gateway flows are **`checkout.md` / `payment.md`** (separate). This doc covers **slot integrity** and reservation data paths.

## Scope

| In scope | Out of scope (other modules) |
|----------|------------------------------|
| Slot availability display | Final price, coupons, Zarinpal/Zibal |
| `add_sans_lock` / lock expiry | Commission waterfall |
| Conflict before order | Wallet settlement after play |
| Reservation row in `wp_zb_booking_history` / future `wp_ez_*` | Points accrual |
| Unlock on product view / checkout back | SMS content (Queue) |

## Legacy sources (read before changing)

### Theme (live runtime)

| File / symbol | Role |
|---------------|------|
| `wp-content/themes/escapezoom-v2/inc/saeed-codes.php` | `ez_add_booking_lock`, `ez_remove_booking_lock`, `ez_get_booking_lock`, `visit_single_room_unlock_booking`, `tracking_back_btn_in_checkout_page` |
| `wp-content/themes/escapezoom-v2/woocommerce/checkout/form-checkout.php` | Sans expiry check, `wp_zb_booking_history` conflict query via `ez_reservation` |
| `woocommerce_after_checkout_validation` → `conflict_before_place_order_validation` | Pre-place-order guard |
| `wp-content/themes/escapezoom-v2/inc/checkout-intent.php` | Checkout intent token / resume URL (`book`, `add-to-cart` query args) |
| `wp-content/themes/escapezoom-v2/reserve.php` | Reservation UI |

### Web-service (escapezo_queries)

| Endpoint | Types |
|----------|--------|
| `web-service/reservation.php` | Legacy HTTP entry (CORS); **thin wrapper** → `includes/reservation-dispatch.php` |
| `web-service/includes/reservation-dispatch.php` | `ez_reservation_dispatch()` — shared with internal WP calls |
| `wp-content/themes/escapezoom-v2/inc/shop/booking/reservation-bridge.php` | Hot-path shortcuts: locks, whitelisted `query_execution`, `get_pending_sanses` |
| `POST /ajax` (ez_core `AjaxGateway`) | Signed actions: `booking.sans_day`, `booking.sans_week`, `booking.sans_management_web`, `booking.open_sans`, `booking.close_sans` |
| DB table `booking_lock_schedule` | `product_id`, `booking_time` (unix), `lock_time` (unix) |

### Analysis docs (read-only)

- `docs/md/step1-freeze-map-bridge-report.md` — hook inventory, ownership map, bridge design
- `docs/md/تحلیل_فرآیند_چک_اوت_و_بازگشت_از_بانک.md` — checkout race notes (conflict check at page load vs submit)

## Data stores

| Store | Usage today | Target (`wp_ez_*`) |
|-------|-------------|---------------------|
| `escapezo_queries.booking_lock_schedule` | Temporary lock rows | `wp_ez_slot_locks` or lock columns on `wp_ez_slots` (TBD in schema) |
| `wp_zb_booking_history` (via reservation SQL) | Confirmed / in-progress booking rows | `wp_ez_orders` / booking linkage (Rule 01 migration) |
| Woo order meta `sans_time` | Binds order to slot | Keep during bridge; mirror to new store |
| `$_SESSION['book']` | Selected sans timestamp | Replace with signed intent / server session in core |

## Flow (current legacy → target)

### 1. User selects sans (product page / reserve)

1. User picks date/time → `book` = Unix timestamp.
2. Optional: call `ez_reservation({ type: 'add_sans_lock', product_id, booking_time })` or theme `ez_add_booking_lock`.
3. UI shows slot unavailable if lock exists for same `booking_time`.

### 2. Checkout entry

1. `form-checkout.php` validates `book` > `time()`.
2. Loads conflict: `SELECT` on `wp_zb_booking_history` for `room_id` + `booking_time`.
3. **Known gap:** conflict at **page load** may stale before **submit** — core must re-validate in `ConflictDetector` at place-order (see analysis doc).

### 3. Lock maintenance

- On product single: `visit_single_room_unlock_booking` removes locks where `lock_time + 300s < now` (**5 minutes** legacy).
- Same 5-minute rule in `web-service/saeed.php` lock cleanup loops.

### 4. Order placed / paid

- Pipeline hooks (`thankyou_background_process`, status changes) finalize booking and wallet — ownership → `OrderStatusTransitionService`, `PipelineStateStore` (see step1 map).
- `detect_zibal_payment_method_for_lock` (commented hook) shows optional lock after certain payment methods.

### 5. Cancel / refund path

- `if_order_status_changed` → reservation cleanup, wallet reversal — document in `cancel.md`; booking Service releases sans.

### Target flow (core)

```
View → BookingAvailabilityService.read
     → BookingLockService.acquire (TTL ≤ 15m, Rule 05)
Checkout → ConflictDetector.assert (DB transaction / row lock)
Pay success → BookingConfirmationService.reserve
Cancel → BookingCancelService.release + audit
```

## Business rules (executable detail)

### Double booking

- At most **one active reservation** per `(product_id, sans_time)` for confirmed state.
- Lock does **not** replace reservation — two users cannot confirm same sans; lock only blocks concurrent checkout attempts.

### Lock TTL

| Environment | TTL |
|-------------|-----|
| **Legacy production** | **5 minutes** (`lock_time + 300`) |
| **New core target** | **≤ 15 minutes** (Rule 05) — implement in `BookingLockService`; change production only with Rule 01 flag + parity |

### States (mapping)

| Legacy signal | Target state |
|---------------|--------------|
| No row, no lock | Available |
| Row in `booking_lock_schedule` | Locked |
| Row in `wp_zb_booking_history` / paid order | Reserved |
| Owner block flags | Blocked |
| Refund/cancel pipeline | Cancelled → Available |

## Target core modules (Rule 02)

From `step1-freeze-map-bridge-report.md`:

| Legacy | Core Service |
|--------|----------------|
| `ez_add_booking_lock` / remove / get | `BookingLockService` |
| `ez_booking_conflict_with_other_order` | `ConflictDetector` |
| `ez_booking_exists_for_order` | `BookingRepository` |
| Pipeline helpers | `PipelineStateStore`, `OrderStatusTransitionService` |
| Refund path | `RefundReconciliationService` |

Thin adapters in theme/`init.php` bridge until Phase 3 (Rule 01).

## Migration (Rule 01)

Suggested module flag prefix: `EZ_BOOKING_*`

| Phase | Behaviour |
|-------|-----------|
| **1 – Expand** | Write locks/reservations to `wp_ez_*`; optional dual-write to `booking_lock_schedule` / `wp_zb_booking_history` |
| **2 – Backfill** | Historical bookings via Queue; **merged read** for conflict checks |
| **3 – Decommission** | Drop reliance on `query_execution` string SQL and legacy tables |

**Red lines:** no big-bang on checkout; no drop `booking_lock_schedule` until parity report clean.

Rollout playbook (when started): `escapezoom-core/docs/rollout/booking-cutover.md`

## API surface

### Web (live v2)

| Surface | When | Notes |
|---------|------|--------|
| `POST /ajax` + HMAC | `single-product`, `reserve`, `sans-manager` when `EZ_AJAX_SHARED_SECRET` set | Boot: `inc/theme/ez-ajax-boot-data.php`; bundle: `dist/front.js` (Vite, Alpine+HTMX+ez-ajax) |
| `booking.sans_day_json` | single-product calendar click | Raw JSON (legacy `get_sanses` shape); `BuildSans` → `ezBookingApi.sansDayJson` |
| `booking.sans_week` | reserve.php week tabs | HTML partial; `days=7` via same `BookingService` |
| Native sans read (P3) | `EZ_BOOKING_NATIVE_SANSES` | `SansAvailabilityService` + Eloquent (`external`); **no** `md-connect` / handlers |
| `ez_reservation()` internal | `EZ_BOOKING_USE_INTERNAL` (legacy fallback) | `LegacySansAdapter` → `reservation-dispatch.php` when native flag off |
| `web-service/reservation.php` | **Removed** | Block at nginx with `410` (see `docs/project/ops/nginx-reservation-deprecation.conf`) |

### Config (`secrets.enc`)

Booking DB + gateway flags live in [`wp-content/mu-plugins/ez_core/config/secrets.enc`](../../../wp-content/mu-plugins/ez_core/config/secrets.enc) (encrypted). Set **`EZ_CORE_SECRETS_KEY`** in Docker/host env only — see [`.env.example`](../../../.env.example) and [`docs/project/ops/booking-dev-db.md`](../ops/booking-dev-db.md).

On MU-plugin load, bridge constants `DB_EXT_*` and `EZ_*` are defined for legacy theme code. **`wp-config-docker.php` does not store booking credentials.**

### Dev setup checklist

Full steps: [`docs/project/ops/booking-dev-db.md`](../ops/booking-dev-db.md)

```bash
php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 692762 <day_start_unix> 1
```

Gateway dev header (when `WP_DEBUG`): `X-EZ-Booking-Path: native|legacy`.

**Symptom `[]`:** run health script; import `escapezo_queries` if empty; verify `secrets.enc` external DB password.

**Theme build (escapezoom-v2):**

```bash
cd wp-content/themes/escapezoom-v2
npm install --registry https://registry.npmjs.org/
npm run build   # dist/front.css + dist/front.js
npm run dev     # watch CSS + JS (like v3)
```

**Core classes:** `BookingService.php`, `SansAvailabilityService.php`, `SansAvailabilityCalculator.php`, `Infrastructure/LegacySansAdapter.php`, `Actions/GetSansesJsonAction.php`, domain `DayTypeResolver`, `DaySlotBuilder`, repos `EloquentProductDataRepository`, `EloquentCalendarRepository`, `EloquentBookingHistoryRepository`.

**Parity check (dev):**

```bash
php wp-content/mu-plugins/ez_core/bin/compare-sans-parity.php 762302 <day_start_unix> 1
```

### Mobile (future)

REST after `Auth` — availability + lock endpoints delegate to same Services (Rule 05).

## Tests (Rule 04 — Pest)

| Suite | Cases |
|-------|--------|
| **Unit** | `BookingLockService`: acquire, expiry, release, double-acquire same user |
| **Unit** | `ConflictDetector`: busy sans rejects; expired lock allows |
| **Integration** | Transactional confirm + parallel confirm → one winner |
| **Parity** | Legacy lock row vs `wp_ez_*` lock for same product/sans (Phase 2) |

Run: `composer test` from `wp-content/mu-plugins/ez_core` (or deployed core path).

## Edge cases & known issues

1. **Race at checkout submit** — legacy checks conflict on GET; must re-run in Service on `checkout_place_order`.
2. **Back button on checkout** — `tracking_back_btn_in_checkout_page` adds `?back=1`; `remove_sans_lock` on back is **commented out** — locks may linger until TTL.
3. **`query_execution`** — arbitrary SQL over HTTP; **do not extend**; replace with parameterized Repository methods.
4. **Dual lock paths** — theme `$wpdb->booking_lock_schedule` and `ez_reservation` — migration must define single write path with dual-write flag.
5. **Past sans** — redirect/error if `book < time()` on checkout load.

## Related modules

- [checkout.md](./checkout.md) — amounts, gateway, `ez_final_payment_amount`, submit validation
- [cancel.md](./cancel.md) — to write: refund + sans release
- `finance.md` — post-pay `wp_markting` / commission (to write)

## Changelog

| Date | Change |
|------|--------|
| 2026-05-24 | Initial module doc (template + legacy mapping from step1 / v2 / web-service) |
| 2026-05-24 | Gateway migration: dispatch extract, `reservation-bridge`, ez_core `/ajax`, v2 `product-booking` bundle, internal `ez_reservation` flag |
| 2026-05-24 | Phase A: `booking.sans_day_json`, `BookingService`/`LegacySansAdapter`, Vite `dist/front.js` (v3-style), `BuildSans` → gateway |
