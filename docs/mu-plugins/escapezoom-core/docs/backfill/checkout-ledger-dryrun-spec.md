# Checkout Ledger Dry-Run Backfill Specification

## Objective
Validate parity between legacy payment/order data and new ledger tables **without switching runtime writes**.

## Execution Modes

1. `analyze-only`
   - Read legacy sources.
   - Build transformed in-memory records.
   - No inserts/updates.
2. `dry-run-report`
   - Same as analyze-only.
   - Emit mismatch reports and aggregate metrics.
3. `write-enabled` (out of dry-run scope)
   - Insert into ledger tables after approval.

## Input Sources

- `wp_markting` (order summary)
- `wallet_transactions` (wallet financial evidence)
- Woo order + meta (`wp_posts`, `wp_postmeta`)
- optional: `points` for anomaly correlation

## Dry-Run Steps

1. Load candidate legacy orders from `wp_markting`.
2. Map each order into expected `checkout_order` row.
3. Resolve related financial events and map expected ledger transactions.
4. Recompute expected aggregate columns:
   - `payable_amount`
   - `paid_amount`
   - `remaining_amount`
5. Run checks and emit structured mismatches.

## Required Reports

### 1) Coverage Report
- total legacy orders scanned
- orders mapped successfully
- orders skipped
- orders with missing required keys (`order_id`, user/product references)

### 2) Monetary Parity Report
- `sum(gross_amount)` legacy vs mapped
- `sum(coupon_discount_amount)` legacy vs mapped
- `sum(level_discount_amount)` legacy vs mapped
- `sum(payable_amount)` mapped
- `sum(paid_amount)` mapped from transaction evidence
- `sum(remaining_amount)` mapped

### 3) Status Parity Report
- count per legacy status
- count per mapped target status
- unknown/unmapped status values

### 4) Constraint Violation Report
- negative amounts
- duplicate `order_id`
- duplicate `idempotency_key`
- invalid enum values
- missing `order_id` linkage for mapped transactions

### 5) High-Risk Financial Exceptions
- `paid_amount > payable_amount` (potential double-charge or duplicate event)
- `paid_amount = 0` but status mapped to paid
- refund credit without prior debit evidence
- wallet deductions with no corresponding order

## Output Format (suggested)

- `summary.json`
- `coverage.csv`
- `status_parity.csv`
- `money_parity.csv`
- `violations.csv`
- `high_risk_exceptions.csv`

## Go/No-Go Criteria

Go only if all are true:
- duplicate key violations = 0
- invalid enum violations = 0
- high-risk financial exceptions = 0
- mapped coverage >= agreed threshold (recommended 99.5%+)
- monetary parity deltas within agreed tolerance (recommended exact integer parity)

## Rollback Safety

- Dry-run writes nothing.
- Runtime write path remains legacy.
- No schema destructive operations in dry-run.

