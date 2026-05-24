# Step 1 Execution Report: Freeze + Map + Bridge Design

## Executive Summary
- Step 1 با رویکرد analysis-only اجرا شد و هیچ جابه‌جایی runtime در `init.php` انجام نشد.
- دامنه هدف روی `payment / checkout / booking` محدود شد تا قدم 2 کم‌ریسک و قابل rollback بماند.
- Hookهای حیاتی ووکامرس و callbackهای پرداخت/رزرو استخراج و owner-map شدند.
- وابستگی‌های `ez_reservation()` و `ez_webservice()` در دامنه پرداخت/رزرو به تفکیک read/write/query_execution دسته‌بندی شدند.
- ریسک‌های اصلی مهاجرت شناسایی شد (به‌خصوص raw SQL over HTTP و side effectهای پخش‌شده در status hooks).
- backlog اجرای Step 2 با ترتیب کم‌ریسک به پرریسک و معیار قبولی هر آیتم آماده شد.

## Completed Artifacts
- Hook Inventory (payment/booking scope) - این فایل
- Function Ownership Map (legacy -> target module) - این فایل
- Web-service/DB2 Interaction Matrix - این فایل
- Step 2 Sequenced Backlog + Acceptance Criteria - این فایل
- Go/No-Go Gate for Step 2 - این فایل

## Source Files Analyzed
- `c:\Users\jobal\docker\new_escapezoom_wp\wp-content\themes\escapezoom-v2\inc\init.php`
- `c:\Users\jobal\docker\new_escapezoom_wp\web-service\reservation.php`
- `c:\Users\jobal\docker\new_escapezoom_wp\web-service\web-service.php`
- `c:\Users\jobal\docker\new_escapezoom_wp\web-service\db-connect.php`
- `c:\Users\jobal\docker\new_escapezoom_wp\wp-content\debug.log`

## 1) Hook Inventory (Payment/Booking)

### Direct Payment/Checkout Hooks
- `woocommerce_review_order_after_order_total` -> `ez_review_order_prices_table` (checkout price breakdown rendering)
- `woocommerce_calculated_total` -> `ez_final_payment_amount` (critical total mutation path, priority 10, args 2)
- `woocommerce_checkout_update_order_meta` -> `store_ez_payment_method` (persists `ez_payment_type`)
- `woocommerce_coupons_enabled` -> `disable_multiple_coupons` (enforces single coupon flow)
- `woocommerce_available_payment_gateways` -> `switch_zarinpal_gateway_by_domain` (gateway list mutation by host)
- `woocommerce_payment_complete` -> `my_change_status_function` (status rewrite + payment snapshot meta)
- `woocommerce_after_checkout_validation` -> `conflict_before_place_order_validation` (pre-order conflict guard)

### Booking/Payment Pipeline Hooks
- `woocommerce_order_status_changed` -> anonymous callback around `thankyou_background_process` enqueue (async pipeline starter)
- `thankyou_background_process` -> anonymous callback (core booking+wallet settlement pipeline)
- `woocommerce_order_status_changed` -> `if_order_status_changed` (refund flow + wallet reversal + reservation cleanup)
- `woocommerce_before_save_order_items` -> `custom_checkout_create_order_line_item` (admin quantity change sync into booking data)
- `wp` -> `visit_single_room_unlock_booking` (booking lock cleanup behavior)
- `wp` -> `tracking_back_btn_in_checkout_page` (checkout/back behavior affecting lock flow)

### Related Schedulers Affecting Payment Reliability
- `zarinpal_paid_transactions_process_cron` -> `zarinpal_paid_transactions_process`
- `zarinpal_co_paid_transactions_process_cron` -> `zarinpal_co_paid_transactions_process`

## 2) Function Ownership Map (Legacy -> Target Module)

### Payment Amount and Method
- `ez_final_payment_amount` -> `core/payment/AmountCalculator`
- `store_ez_payment_method` -> `core/payment/CheckoutMetaWriter`
- `ez_get_coupon_discount_amount` -> `core/payment/CouponDiscountCalculator`
- `disable_multiple_coupons` -> `core/payment/CouponPolicyService`

### Gateway and Verification
- `switch_zarinpal_gateway_by_domain` -> `core/payment/GatewaySelector`
- `verify_zarinpal_payment` -> `core/payment/PaymentVerifier`
- `zarinpal_paid_transactions_process` -> `core/payment/ZarinpalPaidPoller`
- `zarinpal_co_paid_transactions_process` -> `core/payment/ZarinpalCoPaidPoller`

### Booking Pipeline and Conflicts
- `my_change_status_function` -> `core/booking/OrderStatusTransitionService`
- booking pipeline helpers:
  - `ez_booking_pipeline_is_done` -> `core/booking/PipelineStateStore`
  - `ez_booking_pipeline_finalize` -> `core/booking/PipelineStateStore`
  - `ez_booking_conflict_with_other_order` -> `core/booking/ConflictDetector`
  - `ez_booking_exists_for_order` -> `core/booking/BookingRepository`
- lock helpers:
  - `ez_add_booking_lock`, `ez_remove_booking_lock`, `ez_get_booking_lock` -> `core/booking/BookingLockService`

### Refund/Reverse Path
- `if_order_status_changed` -> `core/booking/RefundReconciliationService`

## 3) Web-service / DB2 Interaction Matrix (Payment/Booking Scope)

`DB2` here is currently backed by `web-service/db-connect.php` using database `escapezo_queries`.

### Reservation Endpoint (`web-service/reservation.php`)
- `type=query_execution`
  - Usage in payment/booking: conflict checks, booking existence checks, booking cleanup on refund, quantity sync
  - Data direction: read + write
  - Risk: very high (accepts raw SQL string)
- `type=add_sans_lock`
  - Usage: pre-checkout reservation lock
  - Data direction: write
- `type=get_sans_lock`
  - Usage: checkout conflict validation
  - Data direction: read
- `type=remove_sans_lock`
  - Usage: unlock flows (partly commented in source)
  - Data direction: write
- `type=update_product_sub_data`
  - Usage: product schedule/payment config sync from admin
  - Data direction: write

### Web-service Endpoint (`web-service/web-service.php`)
- `type=single_schedule_products_set`
  - Usage: schedule updates impacting held/booking consistency
  - Data direction: write
- `type=update_product_discount_data`
  - Usage: discount data updates affecting payment amount path
  - Data direction: write
- `type=ez_calendar`
  - Usage: calendar sync, indirectly impacts booking visibility/availability
  - Data direction: write

## 4) Bridge Boundary Design (Step-2 Ready)

### Boundary Rules
- `init.php` stays as runtime bridge in step 2 (no rename/delete yet).
- Legacy function signatures must stay unchanged at wrapper level.
- Request parsing (`$_POST`, `$_SESSION`, host checks) stays in Adapter layer first.
- Core service layer receives normalized arguments only.
- No business-rule change in first extraction wave.

### Proposed Bridge Contracts
- `PaymentAdapter`:
  - `finalAmountFromLegacy($total, $cart, $requestPayload)`
  - `storePaymentMethodFromLegacy($orderId, $postData)`
- `ReservationClient`:
  - `queryExecution($sql, $singleValue = false)` (temporary, to be killed later)
  - `addSansLock($productId, $bookingTime)`
  - `getSansLock($productId)`
  - `removeSansLock($productId, $bookingTime)`
- `GatewaySelector`:
  - `filterAvailableGateways($availableGateways, $host)`

## 5) Risk Register + Mitigation (For Step 2)

- **R1 - Raw SQL over HTTP (`query_execution`)**
  - Impact: critical security + data integrity risk
  - Mitigation: start replacing per-query with explicit reservation client methods; ban new raw SQL calls.

- **R2 - Hidden side effects in status transitions**
  - Impact: duplicate or inconsistent booking/wallet state
  - Mitigation: isolate status transition logic into one orchestration service and keep idempotency checks (`booking_pipeline_done_at`).

- **R3 - Mixed request source in amount calculation**
  - Impact: nondeterministic totals due to `$_POST`/`post_data` parsing differences
  - Mitigation: normalize payment type in adapter before calling calculator.

- **R4 - Coupon/wallet/order total interaction coupling**
  - Impact: financial mismatch between displayed total and settled total
  - Mitigation: keep strict calculation order parity in first extraction; add baseline assertions in tests.

- **R5 - Gateway branch by domain**
  - Impact: wrong gateway available in production domains
  - Mitigation: isolate host resolution and add explicit host test matrix in step 2.

## 6) Step 2 Backlog (Sequenced, Low-to-High Risk)

1. Extract `switch_zarinpal_gateway_by_domain`
   - Acceptance:
     - same gateway list output for `.ir` and non-`.ir`
     - no checkout gateway regression
2. Extract `ez_get_coupon_discount_amount`
   - Acceptance:
     - percent/fixed coupons produce same numeric result as legacy
3. Extract `store_ez_payment_method`
   - Acceptance:
     - `ez_payment_type` meta unchanged across partial/complete scenarios
4. Extract `ez_final_payment_amount`
   - Acceptance:
     - parity on final online payable amount across: no coupon, coupon, privileged user, wallet usage
5. Extract Zarinpal verify/poller set
   - Acceptance:
     - successful paid transaction still marks order paid once
     - no duplicate completion side effect
6. Extract booking finalize/wallet side effects from async pipeline
   - Acceptance:
     - no duplicate booking rows
     - wallet transactions stay balanced
     - pipeline idempotency preserved

## 7) Test Checklist Baseline (For every extraction in Step 2+)
- Checkout with `partial` payment
- Checkout with `complete` payment
- Coupon applied (percent/fixed)
- Wallet balance full coverage / partial coverage / zero balance
- Gateway visibility by host
- Payment verify callback success path
- Refund flow and reservation cleanup
- Booking lock create/get/remove behavior

## 8) Go / No-Go Gate for Step 2
- **Decision: GO**
- Rationale:
  - boundaries are clear
  - extraction order is sequenced
  - critical risks are known with mitigations
  - no runtime refactor was done in step 1

## 9) End-of-Step Agent Report Template

Use this template at the end of each future step:

1. Executive summary (5-8 lines)
2. Completed artifacts with absolute paths
3. Domain map updates (entrypoints -> adapter -> service -> external deps)
4. DB2 removal readiness (ready items / blocked items)
5. Risks found + mitigation enacted
6. Verification checklist result
7. Go/No-Go for next step
8. Debug log status (`clean` or `has errors`) with latest relevant lines
