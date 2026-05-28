# فاز ۵.۱ — نتایج verify (CLI + HAR)

## CLI (project-only mode — 2026-05-27)

| Check | Command | Result |
|-------|---------|--------|
| Boot probe | `php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php` | PASS — `sub_secret` derivable without `secrets.enc` |
| DB health | `php wp-content/mu-plugins/ez_core/bin/booking-db-health.php` | PARTIAL — `EZ_AJAX_SHARED_SECRET` configured; DB checks fail خارج از WP runtime |

**نتیجه:** بلاکر boot از نظر کد رفع شد و secret از WordPress keys مشتق می‌شود.

## HAR (browser — local runtime)

| صفحه | درخواست | معیار | وضعیت |
|------|---------|--------|--------|
| `/team/sans_management/` | `ez_team_sans_game_search` | < 500ms | Pending browser |
| همان | `booking.sans_management_web` | 200 HTML | Pending browser |
| `/panel/sans-manager/` | View Source boot | `sub_secret` + `client_kind: web-user` | Pending browser |
| همان | `booking.sans_management_web` | ≥1 موفق | Pending browser |
| single-product | `booking.sans_day_json` | `X-EZ-Gateway: light` | Pending browser |
| بار دوم همان روز | warm cache | < 1s با Redis | Pending browser |

### HAR یافته‌های فعلی (2026-05-27)

- `booking.sans_day_json` و `booking.sans_management_web` از مسیر `/ajax` فعال هستند.
- در 3 HAR اخیر، `wait` برای بعضی درخواست‌ها حدود `22s` تا `30s` بوده است.
- برای ردیابی bottleneck، هدر `X-EZ-Booking-Elapsed-Ms` به پاسخ‌های gateway اضافه شد تا زمان سمت PHP از زمان network/browser جدا شود.

### دستور capture

1. DevTools → Network → Preserve log
2. Hard refresh (`Ctrl+Shift+R`)
3. Export HAR per page
4. ذخیره در `docs/har/phase-5-1/` (local، gitignored)

### smoke browser

- View Source باید `id="ez-ajax-boot"` داشته باشد.
- Console باید `window.__EZ_BOOT__.sub_secret` غیرخالی نشان دهد.
- در Network، کلیک روز در single-product باید `/ajax` با `booking.sans_day_json` بزند.
