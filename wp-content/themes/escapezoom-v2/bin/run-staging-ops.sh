#!/usr/bin/env bash
# Run from WordPress root on staging/production (PHP with mysqli).
set -euo pipefail
THEME_BIN="wp-content/themes/escapezoom-v2/bin"

echo "=== Action Scheduler health ==="
php "${THEME_BIN}/verify-action-scheduler-health.php"

echo ""
echo "=== thankyou_background_process dry-run ==="
php "${THEME_BIN}/cleanup-thankyou-background-as.php"

echo ""
echo "=== Static code QA ==="
php "${THEME_BIN}/qa-verify-order.php" --static

echo ""
echo "To cancel stale AS jobs: php ${THEME_BIN}/cleanup-thankyou-background-as.php --execute"
echo "After a test order:     php ${THEME_BIN}/qa-verify-order.php --order=ORDER_ID"
echo ""
echo "Manual QA on staging (per order):"
echo "  P1 online / P2 wallet-only / P3 mixed / P4 coupon"
echo "  Then: qa-verify-order.php --order=ID (expect 0 failures when paid+booked)"
echo "  Regression: double refund status, cancellation double-click, conflict page"
