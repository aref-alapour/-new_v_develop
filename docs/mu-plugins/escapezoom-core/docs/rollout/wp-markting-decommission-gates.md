# wp_markting Decommission Gates

`wp_markting` can be dropped only when all go/no-go checks remain green for 2-4 weeks.

## Hard Gates (Must be zero)
- Parity hard-fails = 0
- Duplicate `order_id` in target = 0
- Duplicate `idempotency_key` in transactions = 0
- Any `paid_amount > payable_amount` = 0
- Any negative monetary value in finance table = 0

## Soft Gates (Threshold-based)
- Missing user/game snapshots <= agreed threshold
- Optional field mapping gaps <= agreed threshold
- Legacy/new status mismatch <= agreed threshold

## Operational Signoff
- Product owner signoff
- Finance signoff
- Engineering signoff

## Rollback
- Keep dual-write path available.
- Disable `EZ_LEDGER_READ_ENABLED` to return to legacy reads.
- Keep migration checkpoints so backfill reruns are deterministic.
