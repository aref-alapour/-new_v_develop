# EscapeZoom Core (MU-Plugin)

## Purpose

The **escapezoom-core** MU-Plugin is the logic engine for EscapeZoom v3. It provides:

- **Database layer:** Eloquent (Illuminate) using WordPress config (`DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`, و پیشوند جدول از `$GLOBALS['table_prefix']` برای جداول `wp_ez_*`). No theme dependency.
- **Custom tables:** All defined in a **single SQL file** (see below). No Laravel-style migrations.
- **Models:** Thin Eloquent models aligned with `database/schema.sql`: Brand, City, Area, GameType, Genre, Style, Tag, EzUser, UserContact, Product, ProductLookup, Slot, LastMinuteSlotsCache, Order, Review, Point, AffiliateClick, AdvanceLog (و رابطه‌ها مطابق اسکیم).
- **Services:** منطق کسب‌وکار در `src/Modules/{Module}/Services/`. کنترلرها و API فقط سرویس‌ها را صدا می‌زنند.
- **API:** EZ-Query via **REST** (`/wp-json/escapezoom/v1/query`) and legacy admin-ajax (to be removed after frontend migration). Same JSON in/out: `{ success, data, errors }`.
- **Jobs:** Background jobs (e.g. `ExpirePendingSlotsJob`) and scheduling via Action Scheduler when available (rule 22).

## Folder structure

```
wp-content/mu-plugins/escapezoom-core/
├── database/
│   └── schema.sql          # Single source of truth for all custom tables (run manually)
├── src/
│   ├── Core/                # Bootstrap (autoload + Eloquent boot)
│   ├── Database/            # CapsuleBoot (Eloquent from wp-config)
│   ├── Modules/
│   │   ├── Games/
│   │   │   ├── Models/      # Brand, City, Area, GameType, Genre, Style, Tag, EzUser, UserContact, Product, ProductLookup, Slot, Order, Review, Point, LastMinuteSlotsCache, AffiliateClick, AdvanceLog
│   │   │   ├── Services/    # GameService
│   │   │   └── Repositories/# ProductRepository, SlotRepository
│   │   └── Booking/
│   │       └── Services/    # BookingService (با SlotRepository)
│   ├── API/                 # EzQueryEndpoint, EzQueryRestController (REST)
│   ├── Jobs/                # ExpirePendingSlotsJob, etc.
│   ├── Scheduler/           # JobScheduler (Action Scheduler)
│   └── Helpers/
├── escapezoom-core.php      # Main plugin file (loads bootstrap, registers API)
└── composer.json
```

## Schema (single SQL file)

- **File:** `wp-content/mu-plugins/escapezoom-core/database/schema.sql`
- **Rule:** All custom table definitions live in this file only. No migration runner, no versioned migrations.
- **Apply:** Run the SQL manually (phpMyAdmin, MySQL CLI, or a one-off script) when schema changes.
- **Tables:** همهٔ جداول در اسکیم با پیشوند وردپرس (`wp_ez_*`): brands, cities, areas, game_types, genres, styles, tags, users, user_contacts, products, product_locations, product_genres, product_styles, product_tags, product_lookup, slots, last_minute_slots_cache, orders, affiliate_clicks, reviews, points, advance_log و جداول Action Scheduler.

## Main models and relations

- **Brand** → has many **Product**
- **City** → has many **Area**, has many **Product**
- **Area** → belongs to **City**; many-to-many **Product** via `ez_product_locations`
- **GameType** → has many **Product**
- **Genre**, **Style**, **Tag** → many-to-many **Product** via `ez_product_genres`, `ez_product_styles`, `ez_product_tags`
- **EzUser** → has many **Order**, **Product** (owner/manager), **UserContact**
- **UserContact** → belongs to **EzUser**
- **Product** (game) → belongs to **Brand**, **City**, **GameType**, **EzUser** (owner, manager); has many **Slot**, **Order**, **Review**; many-to-many **Area**, **Genre**, **Style**, **Tag**
- **ProductLookup** → belongs to **Product** (فیلتر سریع از پایوت‌ها)
- **Slot** → belongs to **Product**, **Order**; status: `pending` | `booked` | `blocked` (no row = available)
- **LastMinuteSlotsCache** → belongs to **Product**, **City**, **GameType**
- **Order** → belongs to **Slot**, **Product**, **EzUser**
- **Review** → belongs to **Product**
- **Point**, **AffiliateClick**, **AdvanceLog** — مدل‌های نازک بدون رابطهٔ الزامی در این لیست

## API (EZ-Query)

- **REST (only):** `GET` or `POST` `/wp-json/escapezoom/v1/query`. No auth required for `get_game` and `list_games`. Same response format: `{ "success": true|false, "data": ..., "errors": [] }`. See **docs/api-ez-query.md**.
- **Supported actions:** `get_game` (requires `id`), `list_games` (optional `per_page`, `city_id`, `game_type_id`, `fields`, `with`). Implemented via `GameService` and `EzQueryEndpoint`.

## Games CPT (بازی‌ها)

بازی‌ها به صورت **Custom Post Type** با slug **`ez_game`** مدیریت می‌شوند. دادهٔ اصلی هر بازی در جدول **`wp_ez_products`** است و **`product_id` برابر با شناسهٔ پست (post ID)** است (رابطه ۱:۱ با وردپرس). هیچ جدول جدیدی برای CPT اضافه نمی‌شود؛ فقط از `wp_posts` و `ez_products` و جداول وابسته (پایوت‌ها) استفاده می‌شود. فیلدها و وابستگی‌ها با قوانین ۰۶ و ۰۷ و فایل **database/schema.sql** هماهنگ هستند.

- **ثبت CPT:** در `init` با اولویت ۵؛ برچسب‌ها به فارسی (بازی، بازی‌ها، …).
- **پرمالینک:** `rewrite` با slug `room` (قابل تغییر).
- **فیلدها:** از **Carbon Fields** در متاباکس «دادهٔ بازی» برای برند، شهر، نوع بازی، مالک، مدیر، قیمت، ظرفیت، سن، مدت، وضعیت، محله، مناطق/ژانر/استایل/تگ (چندبه‌چند)، و تنظیمات لحظه‌آخری و تقویم (JSON). مقادیر در ذخیرهٔ پست به **ez_products** و پایوت‌ها (ez_product_locations، ez_product_genres، ez_product_styles، ez_product_tags) همگام می‌شوند. در صورت نبود ردیف در ez_products برای آن post_id، با اولین ذخیره یک ردیف با مقادیر پیش‌فرض ایجاد می‌شود.

## One-time setup

1. **Composer:** Run `composer install` (or `composer update`) in `wp-content/mu-plugins/escapezoom-core/`. All required packages (rule 16) must be present; use latest compatible versions (rule 02).
2. **Schema:** Run `database/schema.sql` once (phpMyAdmin, MySQL CLI, or one-off script). No Laravel migrations.
3. **Credentials:** Use WordPress config (`DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_HOST`). No hardcoded credentials; for local overrides (e.g. DB host) use environment variables or a local wp-config include.
4. **Bootstrap:** Main plugin file loads `src/Core/Bootstrap.php`, which loads Composer autoload and boots the Eloquent capsule. If `vendor/` is missing, Core does not boot (no fatal).

## Deployment / build guidelines

- **Composer (production):** On production or build servers always run:
  - `composer install --no-dev --optimize-autoloader`
  - This keeps `vendor/` small (no dev packages, optimized autoloader) and avoids shipping test and example code that is not needed at runtime.
- **Vendor pruning (CI/CD stage):** If your deploy process builds an artifact (zip/tar), exclude heavy, non-runtime paths inside `vendor/` such as:
  - `vendor/**/tests`, `vendor/**/test`, `vendor/**/docs`, `vendor/**/examples`
  - Do this in the build script or CI/CD config; do **not** edit files inside `vendor/` manually.
- **Assets:** Only ship the built assets required at runtime:
  - From `escapezoom-core`: `dist/` bundles and `assets/vendor/` libraries (e.g. Leaflet).
  - Avoid committing or deploying internal build artefacts such as `assets/.stencil/.build/*`; these should be ignored or cleaned in the build step.
