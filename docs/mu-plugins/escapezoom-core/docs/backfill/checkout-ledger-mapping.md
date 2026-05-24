# Checkout Ledger Backfill Mapping

This mapping defines how legacy rows are transformed into ledger-first tables:
- `wp_ez_checkout_orders`
- `wp_ez_payment_transactions`

## Source Tables
- `wp_markting`
- `wallet_transactions`
- `wp_posts` (`shop_order`)
- `wp_postmeta` (order meta)
- optional reconciliation: `points`

## Table 1 Mapping: `wp_markting` -> `wp_ez_checkout_orders`

| Target column | Source | Mapping rule |
|---|---|---|
| `order_id` | `wp_markting.order_id` | direct and unique |
| `user_id` | `wp_markting.customer_id` | cast to bigint, nullable |
| `product_id` | `wp_markting.game_id` | cast to bigint, nullable |
| `slot_start_at` | `wp_markting.order_sans_time` + `order_sans_date` | normalize to datetime |
| `quantity` | `wp_markting.order_tickets_quantity` | default `1` when null/0 |
| `payment_type` | `wp_markting.order_method` + payment meta | map to `complete|prepaid|installment` |
| `order_status` | `wp_markting.order_status` | map legacy statuses to target enum |
| `currency` | fixed | `IRR` |
| `gross_amount` | `wp_markting.order_total` OR computed from order meta | numeric sanitize, floor at 0 |
| `coupon_discount_amount` | `wp_markting.order_coupon_used` OR wc coupon totals | numeric sanitize, floor at 0 |
| `level_discount_amount` | user-level discount fields/meta | numeric sanitize, floor at 0 |
| `payable_amount` | derived | `gross - coupon - level` |
| `wallet_amount` | wallet evidence | aggregated successful debit for order |
| `online_amount` | gateway evidence | aggregated successful online debit |
| `installment_amount` | installment evidence | aggregated successful installment debit |
| `paid_amount` | derived | `wallet + online + installment - credits` |
| `remaining_amount` | derived | `payable - paid` floor at 0 |
| `coupon_code` | order coupon/meta | nullable string |
| `is_coupon_applied` | derived | `coupon_discount_amount > 0` |
| `is_level_discount_applied` | derived | `level_discount_amount > 0` |

## Table 2 Mapping: Financial Events -> `wp_ez_payment_transactions`

Each payment evidence row/event becomes one ledger row.

| Target column | Source | Mapping rule |
|---|---|---|
| `order_id` | `wp_ez_checkout_orders.order_id` | canonical linkage key across checkout/ledger/markting |
| `order_id` | order source | canonical id (`wp_markting.order_id`) |
| `user_id` | order/customer source | nullable |
| `gateway` | gateway meta/source | e.g. `zarinpal`, `digipay`, `wallet`, `manual` |
| `channel` | source type | `online|wallet|installment|refund|adjustment` |
| `event_type` | source state | `authorize|capture|settle|refund|reverse|fail|cancel` |
| `direction` | source semantic | `debit` for charge, `credit` for refund |
| `status` | source status | `pending|success|failed|reversed` |
| `amount` | source amount | positive integer/decimal(14,0) |
| `currency` | fixed | `IRR` |
| `idempotency_key` | generated deterministic key | `${source}:${order_id}:${event_ref}` |
| `gateway_transaction_id` | source reference | nullable |
| `gateway_reference_id` | source reference | nullable |
| `gateway_payload` | source raw data | JSON-encoded snapshot |
| `error_code` | source fail data | nullable |
| `error_message` | source fail data | nullable |
| `occurred_at` | source time | normalized UTC/local canonical |

## Legacy Status Mapping

| Legacy | Target `order_status` |
|---|---|
| `wc-completed`, `wc-completed-paid` | `paid` |
| `wc-partially-paid`, `wc-held` | `partially_paid` |
| `wc-pending` | `pending` |
| `wc-failed` | `failed` |
| `wc-cancelled` | `cancelled` |
| `wc-refunded` | `refunded` |
| unknown/null | `draft` |

## Validation Rules During Backfill

- `payable_amount` must match formula.
- `paid_amount` must match successful debit-credit ledger sum.
- `remaining_amount` must be non-negative.
- no duplicate `order_id` in `wp_ez_checkout_orders`.
- no duplicate `idempotency_key` in `wp_ez_payment_transactions`.

