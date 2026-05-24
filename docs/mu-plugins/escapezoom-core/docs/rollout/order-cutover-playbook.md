# Order Cutover Playbook (Session-less Order System)

## Flags
- `EZ_LEDGER_READ_ENABLED`
- `EZ_LEDGER_WRITE_ENABLED`
- `EZ_LEDGER_BACKFILL_MODE`

## Rollout Stages

### Stage 0 - Schema Ready
- Apply `08_checkout_ledger.sql` + `09_order_system.sql`.
- Do not switch runtime reads.

### Stage 1 - Dual Write
- Enable writes to new order tables while keeping `wp_markting` writes.
- Track failures in logs and parity reports.

### Stage 2 - Backfill
- Run:
  - `wp ez migrate-orders --dry-run`
  - `wp ez migrate-orders --write --chunk=500`
- Resume using `--from-order-id` / `--to-order-id`.

### Stage 3 - Shadow Read
- For selected endpoints/reports, compare legacy vs new.
- Do not switch user-visible outputs until hard-fail mismatches are zero.

### Stage 4 - Progressive Read Cutover
- Move read paths module-by-module behind feature toggles.
- Keep fast rollback by disabling read flag.

### Stage 5 - Decommission
- Only after gate checks pass for sustained window.

## Verification
- Syntax lint clean.
- Daily parity report generated.
- Callback flow checks:
  - `wc-incart -> bank-request -> callback-success`
  - `wc-incart -> bank-request -> callback-fail`
