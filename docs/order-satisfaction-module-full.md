# EscapeZoom Order Satisfaction Module - Full Documentation

## 1) Purpose and Scope

This module manages **order satisfaction status** and its **auditable history** in EscapeZoom.

It covers:

- Satisfaction status lifecycle on orders (`PENDING`, `SATISFIED`, `DISSATISFIED`)
- Comment-driven effects (review add/edit/approve/trash/delete)
- Cancellation and wallet-conversion effects
- Comment-to-order binding and legacy fallback resolution
- CRM report generation (KPIs, comparison, sortable/paginated history table)
- CRM detail modals (comment detail, owner-cancellation detail)

It does **not** change WooCommerce order state itself; it focuses on satisfaction state and reporting layers.

---

## 2) Main Data Model

## 2.1 Primary Tables

- `wp_markting`
  - Source of truth for current satisfaction status on each order (`order_satisfaction_status`)
- `wp_orders_satisfaction_history`
  - Snapshot/history rows tied to order changes
  - Key fields: `order_id`, `game_id`, `old_status`, `new_status`, `source`, `details`, `created_at`, `updated_at`
- `wp_comments` and `wp_commentmeta`
  - Review comment data and metadata
- `cancellation_requests`
  - Cancellation source data used in revert logic and CRM detail views

## 2.2 Satisfaction Status Constants

- `PENDING`
- `SATISFIED`
- `DISSATISFIED`

---

## 3) Core Files and Responsibilities

- `wp-content/themes/escapezoom-v2/app/functions/helper/order_satisfaction.php`
  - Core domain logic and orchestrators
- `wp-content/themes/escapezoom-v2/app/ajax/callbacks/product_add_comment.php`
  - Entry point for new review submission
- `wp-content/themes/escapezoom-v2/app/ajax/callbacks/product_edit_comment.php`
  - Entry point for review edits
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/comments_actions.php`
  - CRM actions on comments (approve/hold/trash/edit)
- `wp-content/themes/escapezoom-v2/template/team/functions/order_satisfaction_report.php`
  - CRM report builder and table payload
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/satisfaction_report_get.php`
  - CRM report AJAX endpoint
- `wp-content/themes/escapezoom-v2/template/team/pages/satisfaction_report.php`
  - CRM report UI (filters, charts, table, sort/pagination, detail actions)
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/satisfaction_report_detail_get.php`
  - CRM detail modal endpoint (`comment` + `owner_cancel`)
- `wp-content/themes/escapezoom-v2/page-aref-test-user.php`
  - Comment-meta-only backfill tool for legacy comments

---

## 4) Business Rules (Implemented)

## 4.1 Comment Impact Eligibility

A comment affects satisfaction only when:

- `comment_type = review`
- Comment is approved (`comment_approved = 1`)
- Bound order resolves correctly
- Comment author matches leader customer for that bound order

## 4.2 Hold / Unapprove Policy

When a review is put on hold/unapproved:

- Existing satisfaction impact is **not** removed
- No revert from hold/unapprove transitions

## 4.3 Trash / Delete Policy

Only on `trashed_comment` / `deleted_comment`, comment effect is detached.

Revert order:

1. If order is owner-refunded -> keep/return `DISSATISFIED`
2. Else if wallet-converted and session passed -> return `SATISFIED`
3. Else -> `PENDING`

Also:

- Relevant comment effect record in history is deleted (with source/comment safety checks)

## 4.4 Migration / Shift Safety

When a comment gets rebound to a new latest eligible order:

- Old order effect is detached first
- Then new order effect is applied
- Prevents double impact across two orders

If user is teammate now (no valid leader-bound new order):

- Existing old binding/effect remains untouched

---

## 5) Comment Binding Architecture

## 5.1 Comment Meta Keys

Stored on each review comment:

- `EZ_ORDER_SATISFACTION_META_ORDER_ID`
- `EZ_ORDER_SATISFACTION_META_BOUND_AT`
- `EZ_ORDER_SATISFACTION_META_BINDING_SOURCE`
- `EZ_ORDER_SATISFACTION_META_BINDING_CONFIDENCE`

## 5.2 Binding Flow

Primary orchestrator:

- `ez_order_satisfaction_sync_comment_effect(...)`

High-level steps:

1. Validate review and product context
2. Read current binding from comment meta
3. Resolve new runtime binding (latest eligible leader order)
4. If moved: detach old effect first
5. Apply leader rating impact on new bound order
6. Persist updated binding meta

## 5.3 Remove Flow

- `ez_order_satisfaction_remove_comment_effect(...)`

Resolution order:

1. Meta binding (preferred)
2. Safe legacy resolver fallback (confidence-gated)
3. Detach effect from resolved order
4. Clear binding meta

---

## 6) Non-Comment Triggers

## 6.1 Cancellation Approval

Function:

- `ez_order_satisfaction_on_cancellation_refund_approved(...)`

Behavior:

- Owner requester -> `DISSATISFIED`
- Customer requester -> `PENDING`

## 6.2 Wallet Conversion

Function:

- `ez_order_satisfaction_on_wallet_conversion(...)`

Behavior:

- Sets `SATISFIED` unless current status already `DISSATISFIED`

---

## 7) History Storage Semantics

History is managed by upsert-style logic per order:

- `ez_order_satisfaction_history_upsert(...)`

Important notes:

- `PENDING` is marketing-only in final policy (no history insert when target is pending)
- Non-pending changes can create/update history
- `details` JSON carries machine-readable context (comment ids, cancellation request ids, etc.)

---

## 8) CRM Report Module

## 8.1 Report Builder

File:

- `template/team/functions/order_satisfaction_report.php`

Produces:

- Filters echo
- KPI block
- Chart datasets
- Table rows
- Table metadata

## 8.2 KPI Fields

Includes:

- `total_rows`, `sat_count`, `reduction_count`
- `comment_negative_count`, `cancel_negative_count`, `other_negative_count`
- Percentage fields:
  - `sat_percent`
  - `reduction_percent`
  - `comment_share_percent`
  - `cancel_share_percent`
  - `other_share_percent`

## 8.3 Table Server Features

Implemented request params:

- `page`
- `per_page`
- `sort_by` in whitelist:
  - `source`, `new_status`, `old_status`, `created_at`, `updated_at`
- `sort_dir`: `asc|desc`

Response metadata:

- `table_meta.page`
- `table_meta.per_page`
- `table_meta.total_rows`
- `table_meta.total_pages`
- `table_meta.sort_by`
- `table_meta.sort_dir`

Per row detail flags:

- `has_comment_detail`
- `comment_id`
- `has_owner_cancel_detail`
- `cancellation_request_id`

---

## 9) CRM Report UI Behavior

File:

- `template/team/pages/satisfaction_report.php`

Features:

- Game search and selection
- Main date range + comparison date range
- KPI and chart rendering
- Sortable history table
- Pagination controls on top and bottom
- Detail action buttons in details column:
  - `مشاهده کامنت`
  - `مشاهده کنسلی مالک`
- Unified detail modal overlay

Column changes:

- `game_id` column removed from table UI

---

## 10) Detail Modal Endpoint

File:

- `template/team/ajax/callbacks/satisfaction_report_detail_get.php`

Supported `detail_type` values:

- `comment`
- `owner_cancel`

## 10.1 Comment Detail (Current Final)

Shows:

- Author info + avatar
- Comment date
- Publish state badge from `comment_approved`:
  - `1` => منتشر شده
  - `0` or `hold` => عدم انتشار
  - `trash` => حذف شده
  - fallback => نامشخص
- Average rating from `comment_meta('rating')`
- Parameterized sub-ratings from `comment_meta('comment_rating')`:
  - `1094` فضاسازی
  - `1098` تازگی و خلاقیت
  - `1095` کیفیت و معما
  - `1096` بازیگردانی و اکت
  - `1097` برخورد پرسنل
- User level badge from stored review level (`comment_meta('user_level')`), using:
  - `ez_comment_badge_by_stored_level(...)` when available
  - fallback label when unavailable
- Comment text
- First approved owner reply block, if exists

## 10.2 Owner Cancellation Detail

Resolution order:

1. `request_id`
2. fallback via `order_id` + `requester_type=owner`

Shows:

- Order id
- Request datetime
- Session datetime
- Game title
- Player name + phone
- Owner cancellation reason (when available)

---

## 11) Comment Level and Badge Logic

Reference file:

- `app/functions/helper/user_level_system/functions.php`

Key helpers:

- `ez_comment_badge_by_stored_level(...)`
- `user_badge_by_level(...)`
- `get_user_level(...)`

Stored review level comes from comment meta (`user_level`) and can represent legacy/product-review context more accurately than live recalculation.

---

## 12) Legacy Backfill Tool

File:

- `page-aref-test-user.php`

Operational mode:

- `commentmeta_only`

Purpose:

- Backfill missing comment binding metadata safely
- Must not modify `wp_markting` or `wp_orders_satisfaction_history` in this mode

---

## 13) Security and Stability Guardrails

- SQL order-by is strict whitelist mapped
- Inputs are sanitized server-side
- Legacy/detail JSON parse is defensive
- Helper function usage guarded via `function_exists`
- UI has graceful fallback for missing meta values
- Owner-cancel branch is isolated from comment branch

---

## 14) Manual QA Matrix

## 14.1 Report Table

- Pagination works for pages > 1
- Sort toggles asc/desc on all sortable columns
- Top and bottom pagers both work via AJAX
- Compare mode still computes deltas correctly

## 14.2 Comment Detail Modal

Test each:

- Published comment with full 5 sub-ratings + reply
- Hidden/unapproved comment
- Trashed comment (if reachable via history row)
- Comment without `comment_rating`
- Comment without `user_level`

Verify:

- State badge correct
- Average rating shown
- Sub-ratings shown only when available
- Level badge shown (or fallback)
- Reply rendered correctly

## 14.3 Owner Cancel Detail Modal

- Resolve by request id
- Resolve by order id fallback
- Reason and identity fields correctly rendered

---

## 15) Operational Troubleshooting (Server)

If UI changes do not appear after deploy:

1. Confirm active theme is `escapezoom-v2`
2. Purge all caches (plugin/server/CDN)
3. Reset OPcache / restart PHP-FPM
4. Check page source for new UI markers:
   - `satisfaction-detail-modal-overlay`
   - sortable header markers
5. Check AJAX response of `satisfaction_report_get` for:
   - `table_meta`
   - row detail flags (`has_comment_detail`, etc.)

---

## 16) Related Paths Index

- `wp-content/themes/escapezoom-v2/app/functions/helper/order_satisfaction.php`
- `wp-content/themes/escapezoom-v2/app/ajax/callbacks/product_add_comment.php`
- `wp-content/themes/escapezoom-v2/app/ajax/callbacks/product_edit_comment.php`
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/comments_actions.php`
- `wp-content/themes/escapezoom-v2/template/team/functions/order_satisfaction_report.php`
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/satisfaction_report_get.php`
- `wp-content/themes/escapezoom-v2/template/team/pages/satisfaction_report.php`
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/satisfaction_report_detail_get.php`
- `wp-content/themes/escapezoom-v2/template/team/ajax/callbacks/comments_get.php`
- `wp-content/themes/escapezoom-v2/app/functions/helper/user_level_system/functions.php`
- `wp-content/themes/escapezoom-v2/page-aref-test-user.php`

---

## 17) Change Log Snapshot (Recent)

- Centralized comment effect orchestration and binding metadata
- Trash/delete meta-first remove flow with safe legacy fallback
- CRM report table upgraded with server pagination and sorting
- CRM table detail actions and modal endpoints added
- Comment detail modal enhanced with publish state, average score, parameterized scores, and user level badge

