# گزارش کامل و جامع دیتابیس escapezo_ez9920

**تاریخ تهیه گزارش:** 2026  
**دیتابیس:** escapezo_ez9920 (وردپرس اصلی + جداول سفارشی)  
**هدف:** مستندسازی کامل جداول، ستون‌ها، زمان ثبت/به‌روزرسانی داده و مسیرهای استفاده در کد

---

## 📋 فهرست مطالب

1. [نمای کلی دیتابیس](#نمای-کلی-دیتابیس)
2. [فهرست کامل جداول فعلی](#فهرست-کامل-جداول-فعلی)
3. [جداول سفارشی و کاربردی](#جداول-سفارشی-و-کاربردی)
  - [wp_markting](#1-جدول-wp_markting)
  - [wp_orders_log](#2-جدول-wp_orders_log)
  - [wp_order_status_log](#3-جدول-wp_order_status_log)
  - [wp_products_search](#4-جدول-wp_products_search)
  - [wallet_transactions](#5-جدول-wallet_transactions)
  - [wp_cancellation_requests](#6-جدول-wp_cancellation_requests)
  - [wp_cancellation_log](#7-جدول-wp_cancellation_log)
4. [جداول هسته وردپرس / ووکامرس](#جداول-هسته-وردپرس--ووکامرس)
5. [نقاط ورود/هوک‌ها و جریان داده](#نقاط-ورودهوکها-و-جریان-داده)
6. [چک‌لیست مانیتورینگ و بکاپ](#چکلیست-مانیتورینگ-و-بکاپ)
7. [پیشنهادهای بهبود/ترکیب](#پیشنهادهای-بهبودترکیب)

---

## نمای کلی دیتابیس

- این دیتابیس، وردپرس اصلی فروشگاه است و شامل همه جداول هسته وردپرس/ووکامرس + جداول سفارشی زیر است.  
- اتصال در کد: `medoo()` (PHP) و `ez_db()` در mu-plugin به این دیتابیس وصل می‌شوند.  
- بیشتر سینک‌ها/گزارش‌ها در تم `escapezoom-v2` پیاده شده‌اند (هوک‌های ووکامرس).  
- جداولی که به دیتابیس دوم (`escapezo_queries`) وابسته هستند، فقط از طریق `medoo_queries()` یا API web-service خوانده می‌شوند.

---

## فهرست کامل جداول فعلی

خروجی `SHOW TABLES FROM escapezo_ez9920;` (به‌روز شده بر اساس دادهٔ ارسالی):

- billing_phone_summary
- cancellation_log
- cancellation_requests
- collections
- comment_phones_list
- escapezoom_videos
- ez_popularity_new
- g_cancellation_logs
- g_cancellation_requests
- held_orders_list
- hottest_products
- invitations
- notifications
- points
- product_views
- shortlinks
- sms_sending_queue
- wallet_transactions
- wallet_transactions2
- wp_actionscheduler_actions
- wp_actionscheduler_groups
- wp_actionscheduler_logs
- wp_admin_columns
- wp_aryo_activity_log
- wp_bootcamp
- wp_call_me
- wp_commentmeta
- wp_comments
- wp_digits_mobile_otp
- wp_duplicator_entities
- wp_gf_addon_feed
- wp_gf_entry
- wp_gf_entry_meta
- wp_gf_entry_notes
- wp_gf_form
- wp_gf_form_meta
- wp_gf_form_view
- wp_gf_zaringate
- wp_gla_budget_recommendations
- wp_litespeed_avatar
- wp_litespeed_crawler
- wp_litespeed_crawler_blacklist
- wp_litespeed_img_optming
- wp_login_redirects
- wp_markting
- wp_options
- wp_order_status_log
- wp_orders_log
- wp_pmxe_exports
- wp_pmxe_google_cats
- wp_pmxe_posts
- wp_popular_searches
- wp_porsline_settings
- wp_porsline_survay_styles
- wp_postmeta
- wp_posts
- wp_prli_clicks
- wp_prli_link_metas
- wp_prli_links
- wp_products_search
- wp_rank_math_internal_links
- wp_rank_math_internal_meta
- wp_revslider_css
- wp_revslider_css_bkp
- wp_revslider_sliders
- wp_revslider_slides
- wp_revslider_static_slides
- wp_rmp_analytics
- wp_rtl_logs
- wp_sansyab_shortlinks
- wp_term_relationships
- wp_term_taxonomy
- wp_termmeta
- wp_terms
- wp_user_search_history
- wp_usermeta
- wp_users
- wp_wc_admin_note_actions
- wp_wc_admin_notes
- wp_wc_category_lookup
- wp_wc_customer_lookup
- wp_wc_order_product_lookup
- wp_wc_order_stats
- wp_wc_product_attributes_lookup
- wp_wc_product_download_directories
- wp_wc_product_meta_lookup
- wp_wc_tax_rate_classes
- wp_wfblockediplog
- wp_wfblocks7
- wp_wfconfig
- wp_wfcrawlers
- wp_wffilemods
- wp_wfhits
- wp_wfissues
- wp_wfknownfilelist
- wp_wflocs
- wp_wflogins
- wp_wfls_settings
- wp_wfnotifications
- wp_wfpendingissues
- wp_wfreversecache
- wp_wfsnipcache
- wp_wfstatus
- wp_woo_wallet_transactions
- wp_woocommerce_api_keys
- wp_woocommerce_attribute_taxonomies
- wp_woocommerce_ir
- wp_woocommerce_order_itemmeta
- wp_woocommerce_order_items
- wp_woocommerce_sessions
- wp_woocommerce_shipping_zone_locations
- wp_woocommerce_shipping_zone_methods
- wp_woocommerce_shipping_zones
- wp_wsal_metadata
- wp_wsal_occurrences
- wp_wt_iew_action_history
- wp_yoast_indexable
- wp_yoast_indexable_hierarchy
- wp_yoast_migrations
- wp_yoast_primary_term
- wp_yoast_prominent_words
- wp_yoast_seo_links
- wp_ywf_user_fund_log
- wp_zardkooh_newslater_mobile
- wp_zb_holiday
- wp_zb_orderinfo
- wp_zhk_updater_logs
- zebline

---

## جداول سفارشی و کاربردی

### 1. جدول `wp_markting`

**کارکرد:** ذخیره تصویر کامل سفارش + اطلاعات مشتری + بازی برای گزارش‌های مالی/مارکتینگ.  
**کلید:** `order_id` (PRIMARY KEY)  
**ایجاد/به‌روزرسانی:** هوک `woocommerce_checkout_order_processed` در تابع `save_to_markting_table` (تم `escapezoom-v2`).

**ستون‌های اصلی (خلاصه 29 فیلد):**

- **اطلاعات مشتری:** `customer_id`, `customer_firstname`, `customer_lastname`, `customer_phone`, `customer_registered_at`, `customer_level`
- **سفارش:** `order_id`, `order_status`, `order_created_at`, `order_phones` (JSON)، `order_prepaid_tickets` / `order_tickets_quantity`, `order_refrerr`, `order_coupon_used`, `order_coupon_amount`, `order_coupon_type`, `order_transaction_id`, `order_happycall`, `order_paid`, `order_online_paid`, `order_payment_gateway`, `order_payment_type`, `order_user_level_discount`, `order_is_satisfied`, `order_deposit`, `order_finall_price`, `order_net_profit`, `order_tax`
- **سانس:** `order_sans_time` (Unix timestamp/Time)، `order_sans_day`, `order_sans_date`
- **بازی:** `game_id`, `game_name`, `game_city` (JSON), `game_area` (JSON/متن), `game_product_type`, `game_genres`, `game_duration`, `game_brand`, `game_sans_manager_id`, `game_user_ebtal_id`, `game_created_at`
- **فیلدهای پیشنهادی (در گزارش تحلیل):** `order_key`, `order_ticket_tedad`, `order_prepaid`, شاخص روی `order_key`

**زمان و منبع داده:**

- **هنگام ثبت سفارش:** پر شدن اولیه در `save_to_markting_table` (خواندن postmeta، taxonomies و booking_history).  
- **تغییر وضعیت:** هوک `woocommerce_order_status_changed` مقدار `order_status` را آپدیت می‌کند.  
- **سانس:** مقادیر `order_sans_*` از `wp_zb_booking_history` (در escapezo_queries) خوانده و در این جدول ذخیره/آپدیت می‌شود.

**استفاده در کد:**

- گزارش‌های تیم مارکتینگ/مالی (Template: `template/team/pages/marketing_report.php`).
- APIها و اکشن‌های تم برای لیست سفارش‌ها و آنالیز.

---

### 2. جدول `wp_orders_log`

**کارکرد:** لاگ متنی عملیات/خطاهای مرتبط با سفارش (برای پشتیبانی).  
**کلید:** `ID` (AUTO_INCREMENT).  
**ایجاد/به‌روزرسانی:** در مسیرهای مختلف تم هنگام ثبت سفارش، حذف سفارش، یا رویدادهای خطا.

**ستون‌های مهم (بر اساس UI و کوئری‌ها در `template/options/orders-log.php`):**

- `id` (PK)
- `order_id` (شناسه سفارش ووکامرس)
- `order_function` (نام فانکشنی که لاگ تولید کرده)
- `order_log` (متن لاگ)
- `order_log_status` (۰: جدید، ۱: حل‌شده، ۲: حذف/بی‌اثر)
- `order_log_view` (فلگ مشاهده شده)
- `created_at` (datetime/int)
- فیلد اختیاری `status` اگر در DB ایجاد شده باشد (کد وجود ستون را چک می‌کند)

**زمان ثبت:**

- حذف سفارش (`before_delete_post`) → لاگ‌ها به حالت حذف‌شده می‌روند.  
- سایر اکشن‌های تم هنگام خطا/هشدار در فرایند سفارش.

**استفاده:**

- صفحه مدیریت لاگ سفارش‌ها (`/wp-admin/tools.php?page=ez_orders_log`) برای جستجو و تغییر وضعیت لاگ‌ها.

---

### 3. جدول `wp_order_status_log`

**کارکرد:** تاریخچه تغییر وضعیت سفارش‌ها (برای ترکینگ وضعیت و حسابرسی).  
**کلید:** `ID` (AUTO_INCREMENT).  
**ایجاد:** هوک `woocommerce_order_status_changed` در تم/افزونه‌ها.

**ستون‌های رایج (الگوی متداول در کد):**

- `ID` (PK)
- `order_id`
- `old_status`
- `new_status`
- `user_id` (کاربری که تغییر داده)
- `created_at`

**استفاده:**

- گزارش تغییر وضعیت، بررسی رگرس و هماهنگی با `wp_markting`.

---

### 4. جدول `wp_products_search`

**کارکرد:** ایندکس جستجوی سبک برای بازی‌ها/محصولات جهت جستجوی سریع در سایت و پنل.  
**منبع پر شدن:** 

- `template/func/auto-sync-products.php` (سینک خودکار روی ذخیره/ویرایش/حذف محصول ووکامرس).  
- اسکریپت دستی `page-aref-test.php` برای مایگریشن/بازسازی.

**ستون‌ها (همه به صورت JSON/String آماده نمایش):**

- `product_id`, `product_type`, `product_name`, `product_status`, `product_url`, `product_image_url`
- `product_brand` (JSON)، `product_hood`
- `product_city` (JSON)، `product_area` (JSON)
- `product_tags` (JSON array)

**استفاده:**

- جستجوی اصلی سایت (`template/func/main-search-ajax.php`).
- جستجوی پنل تیم (`template/team/ajax/callbacks/games_search.php`).
- صفحات تبلیغاتی (`template/options/promotional-games.php`).

---

### 5. جدول `wallet_transactions`

**کارکرد:** ثبت تمام تراکنش‌های کیف پول کاربران (واریز، برداشت، استفاده در خرید).  
**کلید:** `ID` (AUTO_INCREMENT).  
**ایجاد/به‌روزرسانی:** پلاگین داخلی کیف پول در `wp-content/themes/escapezoom-v2/inc/wallet/`.

**ستون‌های کلیدی (استنتاج از CRUD و کوئری‌ها):**

- `ID` (PK)
- `user_id`
- `amount` (مقدار تراکنش؛ برداشت با مقدار منفی)
- `balance` (موجودی بعد از تراکنش)
- `description` (متن تراکنش)
- `type` (`transaction` یا `withdraw` و ...)
- `status` (متن وضعیت برداشت: «در حال پردازش»، «انجام شد»، «رد شده»؛ برای واریز ممکن است خالی باشد)
- `created_at` (Unix timestamp)

**زمان ثبت:**

- هنگام استفاده از کیف پول در checkout (`using_wallet_in_checkout`).
- درخواست‌های برداشت در API (`user_wallet_withdrawal_api`).
- مدیریت تسویه‌ها در پنل تیم (`withdrawals_get.php`, `withdrawals_search.php`).

**استفاده:**

- API REST (`/user/wallet_transactions`, `/user/wallet_withdrawals`).
- گزارش‌های مالی و تسویه مالکین (`collections_owners_wallet_get.php`).

---

### 6. جدول `wp_cancellation_requests`

**کارکرد:** نگه‌داری درخواست‌های لغو سانس/سفارش.  
**ایجاد:** تابع `create_cancellation_tables()` در `app/functions/create_cancellation_tables.php` (روی `after_switch_theme` و `init`).

**ستون‌ها:**

- `ID` (PK)
- `order_id`, `product_id`
- `requester_id` (کاربری که درخواست داده)
- `requester_type` (`customer` یا `team`)
- `reason_id` (کد دلیل لغو، اختیاری)
- `status` (`pending`, `approved`, `rejected`)
- `sans_time` (timestamp سانس)
- `created_at`, `updated_at`

**استفاده:**

- منطق لغو در `template/team/functions/cancellation_functions.php` و صفحات تیم.

---

### 7. جدول `wp_cancellation_log`

**کارکرد:** تاریخچه اقدامات روی درخواست‌های لغو.  
**ایجاد:** همراه با جدول بالا در `create_cancellation_tables.php`.

**ستون‌ها:**

- `ID` (PK)
- `request_id` (ارجاع به `wp_cancellation_requests`)
- `product_id`
- `user_id`
- `user_role`
- `action` (نوع عملیات: approve/reject/update/...)
- `action_time` (timestamp)

**استفاده:**

- رهگیری تصمیمات لغو و ممیزی تیم.

---

## جداول هسته وردپرس / ووکامرس

- جداول استاندارد وردپرس (`wp_posts`, `wp_postmeta`, `wp_users`, `wp_usermeta`, ...)، ووکامرس (`wp_woocommerce_order_items`, `wp_woocommerce_order_itemmeta`, ...).  
- افزونه Action Scheduler ممکن است جدول‌های خود (`wp_actionscheduler_*`) را نیاز داشته باشد؛ در لاگ خطا دیده شده که `wp_actionscheduler_claims` ساخته نشده است. در صورت فعال بودن افزونه، اجرای `wp cli action-scheduler install` یا مراجعه به پیشخوان → Status → Scheduled Actions برای ساخت جداول لازم است.

---

## نقاط ورود/هوک‌ها و جریان داده

- **ثبت سفارش:** `woocommerce_checkout_order_processed` → `save_to_markting_table` → درج/آپدیت در `wp_markting` + خواندن متادیتا/تاکسونومی + اتصال به `wp_zb_booking_history` (DB دیگر) برای `order_sans_time`.
- **تغییر وضعیت سفارش:** `woocommerce_order_status_changed` → آپدیت `wp_markting.order_status` و درج در `wp_order_status_log`؛ برخی مسیرها لاگ متنی در `wp_orders_log` ایجاد می‌کنند.
- **پرداخت/سانس:** `woocommerce_payment_complete` در `inc/saeed-codes.php` → کپی `_order_total`، ذخیره `ticket_tedad`، تغییر وضعیت؛ سپس لاگ‌ها و مارکتینگ آپدیت می‌شود.
- **لغو:** توابع در `template/team/functions/cancellation_functions.php` → درج در `wp_cancellation_requests` و لاگ در `wp_cancellation_log` + اثر روی موجودی/سانس.
- **کیف پول:** پلاگین `inc/wallet` → تراکنش در `wallet_transactions` (واریز/برداشت/استفاده در خرید) + API‌های REST مربوطه.
- **جستجو محصولات:** کران/هوک‌های سینک (`auto-sync-products.php`, `page-aref-test.php`) → پر کردن `wp_products_search` برای جستجوی سریع؛ خوانش در AJAXهای جستجو سایت و پنل.

---

## چک‌لیست مانیتورینگ و بکاپ

- **بکاپ روزانه**: جداول سفارشی (`wp_markting`, `wp_orders_log`, `wp_order_status_log`, `wp_products_search`, `wallet_transactions`, `wp_cancellation_*`).
- **سلامت Action Scheduler**: بررسی ساخت جداول `wp_actionscheduler_*` در صورت فعال بودن افزونه (خطاهای فعلی در `wp-content/debug.log`).
- **ایندکس‌ها**: 
  - ایندکس روی `wp_markting.order_key` (در صورت افزودن)
  - ایندکس‌های موجود روی `order_id` در لاگ‌ها و `wallet_transactions.user_id` کافی نگه داشته شود.
- **تست پس از تغییر**: checkout کامل (partial/complete)، ثبت لغو، درخواست برداشت کیف پول، جستجوی محصول.

---

## پیشنهادهای بهبود/ترکیب

1. **مرکز لاگ سفارش‌ها**: ترکیب `wp_orders_log` و `wp_order_status_log` به یک جدول واحد `wp_order_logs` (فیلدهای: order_id, action, old_status, new_status, user_id, payload, created_at, view_state) برای سادگی و کاهش تکرار.
2. **هماهنگی با دیتابیس دوم**: در صورت مهاجرت `wp_zb_booking_history` به همین دیتابیس، وابستگی `wp_markting.order_sans_time` ساده‌تر می‌شود (JOIN در یک DB).
3. **پاک‌سازی/بایگانی**: لاگ‌های قدیمی `wp_orders_log` و تراکنش‌های کیف پول با وضعیت نهایی‌شده را بایگانی/پاک‌سازی دوره‌ای کنید تا حجم DB کنترل شود.
4. **ایندکس جستجو**: اگر `products_data` در `escapezo_queries` حذف یا کوچک شود، حتماً پوشش فیلدهای موردنیاز رزرو/سانس در وردپرس یا جدول جایگزین برقرار گردد (به گزارش قبلی مراجعه شود).

