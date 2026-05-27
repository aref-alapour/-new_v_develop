# فاز ۵.۱ — نتایج verify (CLI + HAR)

## CLI (host — 2026-05-27)

| Check | Command | Result |
|-------|---------|--------|
| Secrets init | `php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php` | PASS — `secrets.enc` created |
| Boot probe | `php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php` | PASS — `sub_secret` derivable |
| DB health | `php wp-content/mu-plugins/ez_core/bin/booking-db-health.php` | PARTIAL — Secrets OK, Capsule FAIL (host cannot reach `mysql`) |

**نتیجه:** بلاکر boot از نظر کد/secrets رفع شد. برای PASS کامل DB باید health داخل Docker اجرا شود.

## HAR (browser — داخل Docker)

| صفحه | درخواست | معیار | وضعیت |
|------|---------|--------|--------|
| `/team/sans_management/` | `ez_team_sans_game_search` | < 500ms | Pending browser |
| همان | `booking.sans_management_web` | 200 HTML | Pending browser |
| `/panel/sans-manager/` | View Source boot | `sub_secret` + `client_kind: web-user` | Pending browser |
| همان | `booking.sans_management_web` | ≥1 موفق | Pending browser |
| single-product | `booking.sans_day_json` | `X-EZ-Gateway: light` | Pending browser |
| بار دوم همان روز | warm cache | < 1s با Redis | Pending browser |

### دستور capture

1. DevTools → Network → Preserve log
2. Hard refresh (`Ctrl+Shift+R`)
3. Export HAR per page
4. ذخیره در `docs/har/phase-5-1/` (local، gitignored)

### پس از Docker up

```bash
docker compose exec wordpress php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
docker compose exec wordpress php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php
```

Hard refresh single-product → Console: `window.__EZ_BOOT__.sub_secret` باید string غیرخالی باشد.
