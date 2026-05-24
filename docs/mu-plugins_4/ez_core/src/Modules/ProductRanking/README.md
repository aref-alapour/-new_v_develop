# ProductRanking (incremental)

## Tables

- `{prefix}product_rank_scores` — cached facet scores for carousel sort
- `{prefix}ez_product_penalties` — per-product exclusions (REST + wp-admin **EscapeZoom → پنالتی محصولات**, DaisyUI + HTMX)

Existing DB migrations:

```sql
ALTER TABLE {prefix}ez_product_penalties
  ADD COLUMN active_from DATETIME DEFAULT NULL AFTER topsale_quantity_divisor,
  ADD COLUMN is_enabled TINYINT(1) NOT NULL DEFAULT 1 AFTER exclude_topsale,
  ADD KEY idx_ez_penalty_active_from (active_from);
```

Admin assets: `npm run build:penalties-admin:js` in theme (outputs `dist/product-penalties-admin.js`).

## Events

Theme/helpers call `do_action('ez_ranking_recalculate', $productId, $facets)` or `ez_product_ranking_sync_after_review_change()`.

`trash_comment` (priority 5) removes `hottest_products` rows before theme rollup recalc. `delete_comment` does the same after permanent delete.

Weekly cron `ez_ranking_reconcile_weekly` logs mismatches via `error_log` and filter `ez_ranking_reconcile_report`.

## Staging rollout checklist

1. Import DDL from `database/sql/ez_bootstrap_custom_tables.sql` (match `$wpdb->prefix`).
2. Log in as admin; open page **aref-test-3**.
3. Run ranking wizard steps in order:
   - `ez_rank_step=verify_ranking` — if message says penalties table is empty, run `seed_penalties`.
   - `seed_penalties`
   - `scores` with `auto=1` and `batch=50`
   - `ratings_meta`
   - `topsale_held` (requires `EZ_ESCAPEZO_*` for booking data)
   - `purge_hottest`
   - `validate_ranking`
4. Manual QA:
   - Approve / edit / **trash** a product review (CRM) — hottest score should drop after trash.
   - Product view (`product_set_view`).
   - Completed order → `held_orders_list` / topsale score.
   - Carousels: `sort_type=popular|hottest|topsale`.
5. From `wp-content/mu-plugins/ez_core`: `composer test`.
6. Production: repeat; monitor PHP error log for `ez_core ranking reconcile` weekly messages.

Legacy batch crons (`ez_queryable_set_*`) are deprecated when `ez_core_incremental_ranking_enabled` is true.
