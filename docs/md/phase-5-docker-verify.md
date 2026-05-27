# فاز ۵ — تأیید Docker و HAR

## پیش‌نیاز

```bash
# داخل کانتینر PHP/WordPress (نه Windows host)
php wp-content/mu-plugins/ez_core/bin/secrets-init-dev.php
php wp-content/mu-plugins/ez_core/bin/booking-db-health.php
```

انتظار: `Capsule external` و `Capsule wordpress` = **OK**؛ `EZ_AJAX_SHARED_SECRET: configured`.

## Redis (اختیاری، فاز ۵.۳)

```env
WP_REDIS_HOST=redis
WP_REDIS_PORT=6379
```

Drop-in: [wp-content/object-cache.php](../../wp-content/object-cache.php)

## HAR

جزئیات جدول صفحات در [phase-5-1-crm-game-search.md](./phase-5-1-crm-game-search.md).

## Build / test

```bash
cd wp-content/mu-plugins/ez_core && vendor/bin/pest
cd wp-content/themes/escapezoom-v2 && npm run build:front:js
```
