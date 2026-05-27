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
WP_REDIS_DATABASE=0
```

Drop-in: [wp-content/object-cache.php](../../wp-content/object-cache.php)

اگر Redis extension یا host در دسترس نباشد، drop-in به cache.php پیش‌فرض WP fallback می‌کند.

### Docker compose snippet (اضافه به stack موجود)

```yaml
services:
  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"
  wordpress:
    environment:
      WP_REDIS_HOST: redis
      WP_REDIS_PORT: 6379
```

## Boot probe (بدون WordPress bootstrap)

```bash
php wp-content/mu-plugins/ez_core/bin/gateway-boot-probe.php
```

## HAR

جزئیات جدول صفحات در [phase-5-1-crm-game-search.md](./phase-5-1-crm-game-search.md).

## Build / test

```bash
cd wp-content/mu-plugins/ez_core && vendor/bin/pest
cd wp-content/themes/escapezoom-v2 && npm run build:front:js
```
