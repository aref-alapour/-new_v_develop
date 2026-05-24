# Database Schema Comparison (Design vs schema.sql)

Comparison with the phased design (settlement 24h, last-minute cache, SMS/points in queue) and current `escapezoom-core/database/schema.sql`.

## 1. Missing in schema.sql (suggested ALTERs)

### 1.1 Settlement (Phase 5: تسویه ۲۴ ساعت بعد از اجرای سانس)

**Design:** A job finds orders where `slot_start_at + 24h < now()` and not yet settled, then marks them as settled. Requires a column to track settlement.

**Current:** `wp_ez_orders` has no `settlement_status` or `settled_at` column.

**Suggested (run before production data):**

```sql
-- Add settlement tracking to wp_ez_orders (Phase 5)
ALTER TABLE `wp_ez_orders`
  ADD COLUMN `settlement_status` varchar(20) DEFAULT 'pending' COMMENT 'pending|settled|skipped' AFTER `order_status`,
  ADD COLUMN `settled_at` timestamp NULL DEFAULT NULL COMMENT 'زمان تسویه' AFTER `settlement_status`;
ALTER TABLE `wp_ez_orders` ADD INDEX `idx_ez_orders_settlement` (`settlement_status`, `order_status`);
```

For the settlement job to find "completed orders not yet settled" you will join with `wp_ez_slots` on `slot_id` and filter `slot_start_at + INTERVAL 24 HOUR < NOW()` and `settlement_status = 'pending'`. Optionally add an index that supports that query (e.g. on `slot_id` already exists).

### 1.2 Index for ExpirePendingSlotsJob (Phase 2 / Rule 22)

**Design:** Job runs every 1–2 minutes and deletes slots where `status = 'pending'` and `pending_expires_at < now()`.

**Current:** `wp_ez_slots` has `ez_slots_status_index` and `ez_slots_slot_start_at_index` but no composite index on `(status, pending_expires_at)`.

**Suggested:**

```sql
-- Speed up ExpirePendingSlotsJob (pending expiry scan)
ALTER TABLE `wp_ez_slots` ADD INDEX `idx_slots_pending_expires` (`status`, `pending_expires_at`);
```

(If you already have a composite that starts with `status`, avoid a duplicate single-column index on `status` per INDEX RULE in schema.sql.)

### 1.3 ez_advance_log for job failures (Audit Trail)

**Design:** Job failures must be logged with exception details (rule 22).

**Current:** `wp_ez_advance_log` has `request_url`, `source_page`, `request_type`, `action_name` but no dedicated `message` or `exception` column. Using `request_url` to store the exception message (truncated to 2048) is acceptable; optionally add a dedicated column:

```sql
-- Optional: dedicated column for job/exception message
ALTER TABLE `wp_ez_advance_log` ADD COLUMN `message` text DEFAULT NULL COMMENT 'Exception or job message' AFTER `action_name`;
```

## 2. Already aligned with design

- **wp_ez_slots:** `status` (pending|booked|blocked), `pending_expires_at`, `order_id`; "available" = no row; release = DELETE. ✓
- **wp_ez_last_minute_slots_cache:** Exists and is used for Phase 6 (کش لحظه‌آخری). ✓
- **wp_ez_orders:** Has `order_status`, `payment_status`, `slot_id` (FK to slots, ON DELETE SET NULL). Only settlement columns were missing (see 1.1). ✓
- **wp_ez_points:** Exists for Phase 7 (امتیاز در صف). ✓
- **Action Scheduler tables:** Present in schema.sql; ExpirePendingSlotsJob is scheduled via Action Scheduler when available. ✓

## 3. Contradictions / notes

- **ez_orders.slot_id:** Schema uses `ON DELETE SET NULL`; when a pending slot row is deleted (ExpirePendingSlotsJob), no order row references it yet (pending slots have no final order). So no conflict.
- **Settlement job (Phase 5):** Will need to join `ez_orders` with `ez_slots` to get `slot_start_at`. Currently `ez_orders.slot_id` can be NULL after slot deletion; for "completed, not settled" we only consider orders that still have a non-null `slot_id` and completed status.

## 4. Summary

| Item                         | In schema.sql? | Action |
|-----------------------------|----------------|--------|
| Settlement columns on orders | No            | Add `settlement_status`, `settled_at` + index (see 1.1) |
| Index (status, pending_expires_at) on ez_slots | No | Add (see 1.2) |
| ez_advance_log.message      | No (optional) | Add if you want a dedicated message column (see 1.3) |

Apply the ALTERs in a maintenance window before relying on settlement and expiry jobs in production.
