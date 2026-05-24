# گزارش جامع و کامل دیتابیس‌های EscapeZoom

**تاریخ تهیه:** ۱۴۰۴/۰۸/۱۹ (۲۰۲۶-۰۲-۰۷)  
**هدف:** مستندسازی کامل برای طراحی سیستم Migration مدرن و تحویل به Gemini  
**دیتابیس‌ها:** `escapezo_ez9920` (اصلی وردپرس)، `escapezo_queries` (جستجو و رزرو)  
**منبع ساختار دقیق:** خروجی `INFORMATION_SCHEMA.COLUMNS` (فایل COLUMNS.csv) برای escapezo_ez9920

---

## فهرست مطالب

1. [نمای کلی و اتصالات](#۱-نمای-کلی-و-اتصالات)
2. [دیتابیس escapezo_ez9920](#۲-دیتابیس-escapezo_ez9920)
3. [دیتابیس escapezo_queries](#۳-دیتابیس-escapezo_queries)
4. [قوانین CRUD و جریان داده](#۴-قوانین-crud-و-جریان-داده)
5. [نقشه روابط و وابستگی‌ها](#۵-نقشه-روابط-و-وابستگیها)
6. [پیشنهاد برای سیستم Migration](#۶-پیشنهاد-برای-سیستم-migration)
7. [خلاصه CRUD به تفکیک جدول](#۷-خلاصه-crud-به-تفکیک-جدول)
8. [پیوست A: ساختار دقیق جداول از COLUMNS.csv](#۸-پیوست-a-ساختار-دقیق-جداول-از-columnscsv)

---

## ۱. نمای کلی و اتصالات

### ۱.۱ دیتابیس‌ها

| دیتابیس | نقش | اتصال در کد |
|---------|-----|--------------|
| **escapezo_ez9920** | وردپرس اصلی، WooCommerce، جداول سفارشی مارکتینگ/لاگ/کیف‌پول/لغو | `medoo()`, `ez_db()`, `ez_table()`, connection `default` و `wordpress` |
| **escapezo_queries** | جستجوی محصولات، رزرو سانس، بازدید، تقویم، تگ‌ها، CPC، امنیت | `medoo_queries()`, `ez_reservation()`, connection `external` |

### ۱.۲ توابع دسترسی در کد

- **`ez_table($table)`**: اتصال به **escapezo_ez9920**؛ نام جدول با پیشوند `$table_prefix` (معمولاً `wp_`) ساخته می‌شود. مثال: `ez_table('orders_log')` → جدول `wp_orders_log`.
- **`ez_db()`**: همان دیتابیس اصلی (Laravel/Eloquent).
- **`ez_query($sql)`**: اجرای SQL روی escapezo_ez9920.
- **`ez_external_table($table)`** / **`ez_reservation([...])`**: دسترسی به **escapezo_queries** (از طریق API یا اتصال external).
- **`ez_query($query)`** / **`ez_external_query($query)`**: کوئری خام روی default و external.

### ۱.۳ مدل‌های Eloquent (mu-plugin escapezoom-core)

| مدل | جدول | دیتابیس |
|-----|------|---------|
| `Marketing` | `wp_markting` | default (ez9920) |
| `BookingHistory` | `wp_zb_booking_history` | external (queries) |
| `ProductData` | `products_data` | external (queries) |
| `WalletTransaction` | `wallet_transactions` | default (ez9920) |
| `CancellationRequest` | `wp_cancellation_requests` | default (ez9920) |
| `CancellationLog` | `wp_cancellation_log` | default (ez9920) |

---

## ۲. دیتابیس escapezo_ez9920

### ۲.۱ فهرست کامل جداول دیتابیس escapezo_ez9920

جداول سفارشی و مرتبط با کسب‌وکار (غیر از هسته وردپرس/ووکامرس):

| جدول | توضیح کوتاه |
|------|-------------|
| `wp_markting` | تصویر سفارش برای مارکتینگ/مالی |
| `wp_orders_log` | لاگ متنی خطا/رویداد سفارش |
| `wp_order_status_log` | تاریخچه تغییر وضعیت سفارش |
| `wp_products_search` | ایندکس جستجوی محصولات |
| `wp_cancellation_requests` | درخواست‌های لغو سانس |
| `wp_cancellation_log` | لاگ اقدامات لغو |
| `wp_call_me` | درخواست تماس |
| `wallet_transactions` | تراکنش‌های کیف پول |
| `billing_phone_summary` | خلاصه تلفن‌های صورتحساب |
| `collections` | تسویه/مجموعه‌ها |
| `comment_phones_list` | لیست تلفن‌های کامنت |
| `escapezoom_videos` | ویدیوها |
| `ez_popularity_new` | محبوبیت |
| `g_cancellation_logs` / `g_cancellation_requests` | نسخه/جدول دیگر لغو (در صورت وجود) |
| `held_orders_list` | لیست سفارش‌های نگه‌داری‌شده |
| `hottest_products` | محصولات داغ |
| `invitations` | دعوت‌ها |
| `notifications` | اعلان‌ها |
| `points` | امتیازها |
| `product_views` | بازدید محصول (در ez9920) |
| `shortlinks` | لینک کوتاه |
| `sms_sending_queue` | صف ارسال پیامک |
| `wp_zb_holiday` | تعطیلات |
| `wp_zb_orderinfo` | اطلاعات سفارش زب |
| `wp_woo_wallet_transactions` | تراکنش‌های پلاگین کیف پول ووکامرس (در صورت استفاده) |
| `wp_ywf_user_fund_log` | لاگ موجودی کاربر (در صورت استفاده) |

جداول هسته وردپرس/ووکامرس (بدون توضیح تفصیلی در این گزارش):  
`wp_posts`, `wp_postmeta`, `wp_users`, `wp_usermeta`, `wp_terms`, `wp_term_taxonomy`, `wp_term_relationships`, `wp_termmeta`, `wp_comments`, `wp_commentmeta`, `wp_options`, `wp_woocommerce_order_items`, `wp_woocommerce_order_itemmeta`, `wp_wc_order_stats`, `wp_wc_customer_lookup`, `wp_wc_product_meta_lookup`, و سایر جداول WooCommerce و پلاگین‌ها (Action Scheduler, Yoast, Rank Math, Wordfence, و غیره).

---

### ۲.۲ جدول `wp_markting`

**هدف:** ذخیره تصویر کامل هر سفارش برای گزارش‌های مالی و مارکتینگ (مشتری، سفارش، سانس، بازی).

**کلید اصلی در DB:** `ID` (bigint unsigned, AUTO_INCREMENT). **در کد (مدل Marketing):** از `order_id` به عنوان کلید تجاری استفاده می‌شود و یک رکورد به ازای هر سفارش است؛ مقدار `order_id` باید یکتا باشد.

#### ستون‌ها (کامل)

| ستون | نوع (منطقی) | توضیح |
|------|-------------|--------|
| **مشتری** | | |
| `customer_id` | int | شناسه کاربر وردپرس |
| `customer_firstname` | varchar | نام |
| `customer_lastname` | varchar | نام خانوادگی |
| `customer_phone` | varchar | تلفن |
| `customer_registered_at` | datetime | تاریخ ثبت‌نام |
| `customer_level` | int | سطح کاربری |
| **سفارش** | | |
| `order_id` | int | شناسه سفارش (PK) |
| `order_status` | varchar | وضعیت استاندارد (مثلاً wc-completed) |
| `order_created_at` | datetime | تاریخ ایجاد سفارش |
| `order_phones` | text/json | شماره بازیکنان (قابل آرایه) |
| `order_prepaid_tickets` | decimal | پیش‌پرداخت به ازای هر نفر |
| `order_tickets_quantity` | int | تعداد بلیط |
| `order_refrerr` | varchar | منبع ارجاع (UTM) |
| `order_coupon_used` | varchar | کد تخفیف استفاده‌شده |
| `order_coupon_amount` | decimal | مبلغ/درصد تخفیف |
| `order_coupon_type` | varchar | percentage / fixed |
| `order_transaction_id` | varchar | شناسه تراکنش درگاه |
| `order_happycall` | int | وضعیت تماس رضایت |
| `order_paid` | decimal | مبلغ پرداختی |
| `order_online_paid` | decimal | مبلغ پرداخت آنلاین |
| `order_payment_gateway` | varchar | عنوان درگاه |
| `order_payment_type` | varchar | نوع پرداخت (مثلاً partial) |
| `order_user_level_discount` | decimal | تخفیف سطح کاربر |
| `order_is_satisfied` | int | رضایت (-1/0/1) |
| `order_deposit` | decimal | پیش‌پرداخت |
| `order_finall_price` | decimal | قیمت نهایی (محاسبه‌شده) |
| `order_net_profit` | decimal | سود خالص (محاسبه‌شده) |
| `order_tax` | decimal | مالیات (محاسبه‌شده) |
| **سانس** | | |
| `order_sans_time` | time/varchar | زمان سانس (مثلاً H:i)؛ از booking_history |
| `order_sans_day` | varchar | روز هفته سانس |
| `order_sans_date` | date | تاریخ سانس |
| **بازی** | | |
| `game_id` | int | شناسه محصول/بازی |
| `game_name` | varchar | نام بازی |
| `game_city` | varchar/json | شهر |
| `game_area` | text | منطقه/محله |
| `game_product_type` | varchar | نوع محصول (اتاق فرار، لیزرتگ، ...) |
| `game_genres` | text | ژانرها (جدا شده با کاما) |
| `game_duration` | int | مدت (دقیقه) |
| `game_brand` | varchar | برند |
| `game_sans_manager_id` | int | مدیر سانس |
| `game_user_ebtal_id` | int | کاربر ابطال |
| `game_created_at` | datetime | تاریخ ایجاد بازی |
| **فلگ‌ها** | | |
| `order_financials_calculated` | int | محاسبه مالی انجام شده |
| `complete_change_flag` | int | فلگ تغییر کامل |

#### محتوا و منبع داده

- **مشتری:** از `$order->get_customer_id()`, `get_billing_*`, `wp_users.user_registered`, تابع `get_user_level`.
- **سفارش:** از `$order->get_status()`, `get_date_created()`, postmeta سفارش (`players_phone`, `_wc_order_attribution_*`, `_transaction_id`, `supporting_happycall`, `_order_total`, `_order_total_2`, `ez_payment_type`, `user_level_discount`, `is_satisfied`, `deposit`).
- **بازی:** از محصول ووکامرس، دسته‌بندی‌ها، تگ‌ها، postmeta (`room_duration`, `room_loc`, `sans_manager`, `user_ebtal`), برند (taxonomy).
- **سانس:** در ثبت اولیه خالی است؛ بعداً از جدول **`wp_zb_booking_history`** (دیتابیس escapezo_queries) با `booking_time` پر می‌شود و در `check_and_update_markting_table` به `order_sans_time`, `order_sans_day`, `order_sans_date` نوشته می‌شود.
- **مالی:** `order_finall_price`, `order_net_profit`, `order_tax` توسط تابع `calculate_and_update_order_financials` پر می‌شوند.

#### استفاده در کد

- گزارش مارکتینگ: `template/team/pages/marketing_report.php`
- گزارش مالی و صادرات: `financial_report_export.php`, `sales_report_search.php`, `fetch_data.php`
- پنل و داشبورد: `dashboard.php`, `profile.php`, `panel_orders_get.php`, `panel_sells_get_tables.php`, `orders_get.php`, `orders_get2.php`, `orders_actions.php`
- پس از پرداخت: `check_and_update_markting_table` در thankyou و saeed-codes

#### قوانین CRUD

| عملیات | زمان / شرط | فایل/هوک |
|--------|------------|----------|
| **CREATE** | بعد از checkout | `woocommerce_checkout_order_processed` → `save_to_markting_table` (functions.php) |
| **READ** | همه گزارش‌ها و پنل | مدل `Marketing` و کوئری‌های مستقیم |
| **UPDATE** | وجود سفارش و خالی بودن سانس یا تغییر وضعیت | `check_and_update_markting_table`؛ `update_markting_table_order_status`؛ `calculate_and_update_order_financials`؛ `orders_actions.php` |
| **DELETE** | انجام نمی‌شود (سفارش حذف شود رکورد می‌ماند) | — |

---

### ۲.۳ جدول `wp_orders_log`

**هدف:** لاگ متنی خطاها و رویدادهای مربوط به سفارش (پشتیبانی و دیباگ).

**کلید اصلی:** `id` (bigint unsigned, AUTO_INCREMENT). *مطابق COLUMNS.csv.*

#### ستون‌ها (دقیق از DB)

| ستون | نوع | کلید/پیش‌فرض | توضیح |
|------|-----|---------------|--------|
| `id` | bigint unsigned | PRI, auto_increment | PK |
| `order_id` | bigint | — | شناسه سفارش |
| `order_function` | varchar(255) | — | نام تابعی که لاگ را ثبت کرده |
| `order_log` | text | — | متن لاگ |
| `order_log_status` | tinyint(1) | default 0 | 0=جدید، 1=حل‌شده، 2=حذف/بی‌اثر |
| `order_log_view` | tinyint | default 0 | دیده شده (0/1) |
| `created_at` | datetime | default CURRENT_TIMESTAMP | زمان ثبت |
| `status` | varchar (اختیاری) | — | در صورت وجود در DB (کد چک می‌کند) |

#### محتوا و منبع

- از توابعی مثل `save_to_markting_table`, `check_and_update_markting_table`, `calculate_and_update_order_financials`, `update_markting_table_order_status` هنگام خطا یا هشدار با `log_order_error($order_id, $function_name, $log_message)`.

#### استفاده در کد

- صفحه مدیریت لاگ: `template/options/orders-log.php` (جستجو، فیلتر، علامت‌گذاری حل‌شده/حذف‌شده، حذف لاگ‌های قدیمی).
- هنگام حذف سفارش: `before_delete_post` → به‌روزرسانی `order_log_status = 2` برای لاگ‌های آن سفارش.

#### قوانین CRUD

| عملیات | زمان / شرط |
|--------|------------|
| **CREATE** | هر بار `log_order_error()` فراخوانی شود |
| **READ** | لیست و جستجو در orders-log.php |
| **UPDATE** | تغییر `order_log_status`, `order_log_view` در همان صفحه |
| **DELETE** | حذف دسته‌ای لاگ‌های قدیمی‌تر از یک ماه |

---

### ۲.۴ جدول `wp_order_status_log`

**هدف:** تاریخچه تغییر وضعیت سفارش (چه زمانی، توسط چه کسی، از چه وضعیتی به چه وضعیتی).

**کلید اصلی:** `id` (bigint, AUTO_INCREMENT). *مطابق COLUMNS.csv نام ستون PK حرف کوچک است.*

#### ستون‌ها (دقیق از DB)

| ستون | نوع | کلید/پیش‌فرض | توضیح |
|------|-----|---------------|--------|
| `id` | bigint | PRI, auto_increment | PK |
| `order_id` | bigint unsigned | — | شناسه سفارش |
| `user_id` | bigint unsigned | NULL | کاربری که تغییر داده |
| `status_log` | text | — | متن توضیحی (فارسی) |
| `function_used` | varchar(255) | — | تابع/مبدا تغییر |
| `created_at` | datetime | default CURRENT_TIMESTAMP | زمان |

#### محتوا و منبع

- از `log_order_status_change($order_id, $old_status, $new_status, $function_used, $user_id)` که از هوک `woocommerce_order_status_changed` و از `orders_actions.php` صدا زده می‌شود.

#### استفاده در کد

- `template/options/order-status-log.php` (مشاهده و جستجو، حذف لاگ‌های قدیمی‌تر از ۳ ماه).

#### قوانین CRUD

| عملیات | زمان / شرط |
|--------|------------|
| **CREATE** | هر تغییر وضعیت سفارش (به‌جز وقتی از orders_actions قبلاً لاگ شده باشد) |
| **READ** | صفحه order-status-log |
| **UPDATE** | ندارد |
| **DELETE** | حذف دوره‌ای قدیمی‌تر از ۳ ماه |

---

### ۲.۵ جدول `wp_products_search`

**هدف:** ایندکس سبک برای جستجوی سریع محصولات در سایت و پنل (بدون بار روی wp_posts/postmeta).

**کلید:** `product_id` (یک رکورد به ازای هر محصول).

#### ستون‌ها (دقیق از DB — COLUMNS.csv)

| ستون | نوع | توضیح |
|------|-----|--------|
| `product_id` | bigint unsigned | PK، شناسه محصول ووکامرس |
| `product_type` | varchar(100) | نوع (اتاق فرار، لیزرتگ، ...) |
| `product_name` | varchar(255) | عنوان |
| `product_status` | varchar(100) | وضعیت فروش (active و غیره) |
| `product_url` | text | آدرس نسبی |
| `product_image_url` | text | URL تصویر |
| `product_brand` | **json** | برند (آبجکت JSON) |
| `product_hood` | varchar(255) | محله |
| `product_city` | **json** | شهر (آبجکت JSON) |
| `product_area` | **json** | منطقه (از تگ) |
| `product_tags` | **json** | آرایه تگ‌ها |

#### محتوا و منبع

- از WooCommerce و تاکسونومی‌ها و postmeta/ACF: `ez_get_product_search_data($product_id)` در `template/func/auto-sync-products.php`.
- سینک: هوک `save_post_product` و `acf/save_post` برای محصول منتشرشده؛ اسکریپت دستی در `page-aref-test.php`.

#### استفاده در کد

- جستجوی اصلی سایت: `template/func/main-search-ajax.php`
- جستجوی پنل تیم: `app/ajax/callbacks/team/games_search.php`
- صفحات تبلیغاتی: `template/options/promotional-games.php`, `template/options/ads-landing.php`

#### قوانین CRUD

| عملیات | زمان / شرط |
|--------|------------|
| **CREATE** | اولین بار سینک محصول با `ez_sync_product_to_search_table` |
| **READ** | جستجو در سایت و پنل |
| **UPDATE** | هر بار ذخیره/ویرایش محصول یا ACF با سینک مجدد |
| **DELETE** | `ez_delete_product_from_search_table` هنگام حذف محصول |

---

### ۲.۶ جدول `wallet_transactions`

**هدف:** تراکنش‌های کیف پول (واریز، برداشت، استفاده در خرید).

**کلید اصلی:** `ID` (bigint, AUTO_INCREMENT). *مطابق COLUMNS.csv.*

#### ستون‌ها (دقیق از DB)

| ستون | نوع | کلید/پیش‌فرض | توضیح |
|------|-----|---------------|--------|
| `ID` | bigint | PRI, auto_increment | PK |
| `user_id` | bigint | NULL | کاربر |
| `amount` | decimal(11,0) | — | مبلغ (منفی برای برداشت) |
| `balance` | decimal(11,0) | — | موجودی بعد از تراکنش |
| `description` | longtext | NULL | توضیح |
| `unique_description` | varchar(255) | UNI, NULL | توضیح یکتا (در DB موجود است) |
| `type` | text | — | نوع (transaction, withdraw و ...) |
| `status` | text | — | وضعیت (در حال پردازش، انجام شد، رد شده) |
| `origin` | int | default 0 | مبدا |
| `created_at` | varchar(15) | NULL | زمان |
| `actions` | longtext | — | اقدامات (در DB موجود است) |

#### محتوا و منبع

- از پلاگین کیف پول تم: `wp-content/themes/escapezoom-v2/inc/wallet/`؛ ثابت جدول: `EZ_TRANSACTION_TABLE = 'wallet_transactions'`.
- هنگام استفاده از کیف پول در checkout، درخواست برداشت، تسویه مالکین.

#### استفاده در کد

- API و پنل کاربر و گزارش تسویه: `collections_owners_wallet_get.php`, `withdrawals_get.php`, `withdrawals_search.php`, `panel_wallet_lists_get.php`, `transactions.php`.

#### قوانین CRUD

| عملیات | زمان / شرط |
|--------|------------|
| **CREATE** | هر واریز، برداشت یا استفاده در خرید |
| **READ** | لیست تراکنش‌ها و گزارش‌ها |
| **UPDATE** | تغییر وضعیت برداشت (تأیید/رد) |
| **DELETE** | معمولاً حذف نمی‌شود |

---

### ۲.۷ جدول‌های `wp_cancellation_requests` و `wp_cancellation_log`

**ایجاد:** `app/functions/create_cancellation_tables.php` (با `dbDelta`؛ روی `after_switch_theme` و در `init` اگر جدول ساخته نشده باشد). نام نهایی با پیشوند وردپرس: `wp_cancellation_requests`, `wp_cancellation_log`.

#### `wp_cancellation_requests` (در CSV: cancellation_requests)

| ستون | نوع (از COLUMNS.csv) | توضیح |
|------|----------------------|--------|
| `ID` | bigint unsigned, PRI, auto_increment | PK |
| `order_id` | bigint unsigned, MUL | سفارش |
| `product_id` | bigint unsigned | محصول |
| `requester_id` | bigint unsigned, MUL | درخواست‌دهنده |
| `requester_role` | enum('admin','owner','customer') | نقش درخواست‌دهنده |
| `requester_type` | enum('owner','customer') | نوع: مالک/مشتری |
| `status` | enum('pending','approved','rejected','cancelled','expired') default 'pending' | وضعیت |
| `reason_id` | int unsigned NULL | دلیل لغو (اختیاری) |
| `sans_time` | varchar(20) | زمان سانس |
| `created_at` | varchar(20), MUL | زمان ایجاد |
| `updated_at` | varchar(20) NULL | زمان به‌روزرسانی |
| `auto_processed_at` | varchar(20) NULL | زمان پردازش خودکار |
| `auto_status` | enum('approved','rejected') NULL | وضعیت خودکار |

**محتوا:** از `create_cancellation_request` در `template/team/functions/cancellation_functions.php`؛ سانس از `wp_zb_booking_history` خوانده می‌شود.

**استفاده:** منطق لغو در همان فایل و صفحات تیم؛ تأیید/رد درخواست و به‌روزرسانی وضعیت.

**CRUD:** CREATE هنگام ثبت درخواست؛ READ/UPDATE در پنل؛ لاگ در `wp_cancellation_log`.

#### `wp_cancellation_log` (در CSV: cancellation_log)

| ستون | نوع (از COLUMNS.csv) | توضیح |
|------|----------------------|--------|
| `ID` | bigint unsigned, PRI, auto_increment | PK |
| `request_id` | bigint unsigned, MUL | ارجاع به درخواست |
| `product_id` | bigint unsigned | محصول |
| `user_id` | bigint unsigned, MUL | کاربر انجام‌دهنده |
| `user_role` | enum('admin','owner','customer','system') | نقش |
| `action` | enum('create','approve','reject','cancel','expire') | نوع اقدام |
| `action_time` | varchar(20) | زمان (در کد ممکن است int Unix باشد) |

**محتوا:** از همان توابع لغو هنگام create/approve/reject.

**نکته:** در کد از `ez_table('cancellation_requests')` و `ez_table('cancellation_log')` استفاده می‌شود؛ با پیشوند وردپرس به `wp_cancellation_requests` و `wp_cancellation_log` نگاشت می‌شود.

---

### ۲.۸ جدول `wp_call_me`

**هدف:** درخواست‌های «با من تماس بگیرید».

**ایجاد:** `app/functions/create_call_me_table.php` در `after_setup_theme`.

#### ستون‌ها

| ستون | نوع | توضیح |
|------|-----|--------|
| `id` | int | PK |
| `subject` | varchar | موضوع |
| `phone` | varchar | شماره |
| `status` | varchar | مثلاً pending |
| `created_at` | datetime | زمان |

---

## ۳. دیتابیس escapezo_queries

### ۳.۱ فهرست جداول (از گزارش‌ها و کد)

- `products_data`
- `wp_zb_booking_history`
- `wp_zb_booking_history_today`
- `product_views`
- `products_order`
- `calendar_data`
- `tags`
- `post_view_ip_checker`
- `cpc_tracking`
- `hackers`

---

### ۳.۲ جدول `products_data`

**هدف:** کپی خوانا و قابل جستجو از محصولات (بازی‌ها) برای API و گزارش؛ پر شدن از وردپرس/WooCommerce.

**کلید اصلی:** `ID` (AUTO_INCREMENT).

#### ستون‌ها (مطابق مدل ProductData و گزارش)

| ستون | نوع | توضیح |
|------|-----|--------|
| `ID` | int | PK |
| `product_id` | int | شناسه محصول ووکامرس |
| `product_type` | varchar | نوع محصول |
| `title` | varchar | نام |
| `price` | decimal | قیمت |
| `notable` | tinyint | قابل توجه |
| `special` | tinyint | ویژه |
| `active` | varchar/int | active / updated / inactive |
| `monopoly` | tinyint | انحصاری |
| `brand_id` | int | برند |
| `discount_data` | text/serialized | تخفیف |
| `instant_off` | text/serialized | تخفیف فوری |
| `geo` | varchar | مختصات |
| `image` | varchar | مسیر تصویر |
| `age_limit` | int | محدودیت سنی |
| `level` | int | سطح دشواری |
| `schedule` | text/serialized | برنامه سانس‌ها |
| `duration` | int | مدت (دقیقه) |
| `url` | varchar | آدرس نسبی |
| `hood` | varchar | محله |
| `city_id` | int | شهر |
| `city_name` | varchar | نام شهر |
| `tags_id` | text/serialized | آرایه شناسه تگ‌ها |
| `tags_title` | text/serialized | آرایه عنوان تگ‌ها |
| `count_min` | int | حداقل بازیکن |
| `count_max` | int | حداکثر بازیکن |
| `pish_person` | decimal | پیش‌پرداخت به ازای نفر |
| `auto_disable` | int | روز غیرفعال خودکار |
| `contact_info` | text/serialized | اطلاعات تماس |
| `owner_id` | int | صاحب |
| `manager_id` | int | مدیر |
| `comments_count` | int | تعداد نظرات |
| `rate` | float | امتیاز |

#### محتوا و منبع

- **Cron:** `ez_queryable_set_data_cron` (hourly) → `ez_queryable_set_products_data()` و `ez_queryable_set_products_data_nactive()` در `inc/saeed-codes.php`؛ داده از WooCommerce و postmeta استخراج و در این جدول نوشته می‌شود (حذف رکوردهای active/updated و درج مجدد محصولات فعال).

#### استفاده در کد

- جستجوی بازی: `game_search.php`, `marketing_report.php`, `ads-landing.php`؛ مدل `ProductData` در mu-plugin.

#### قوانین CRUD

| عملیات | زمان / شرط |
|--------|------------|
| **CREATE** | درج رکوردهای جدید در cron |
| **READ** | جستجو و گزارش |
| **UPDATE** | به‌روزرسانی انبوه در cron |
| **DELETE** | حذف رکوردهای قدیمی قبل از درج مجدد در cron |

---

### ۳.۳ جدول `wp_zb_booking_history`

**هدف:** ذخیره هر سانس رزروشده (یا باز)؛ منبع حقیقت برای «چه زمانی چه اتاقی رزرو شده».

**کلید اصلی:** `booking_id` (AUTO_INCREMENT).

#### ستون‌ها

| ستون | نوع | توضیح |
|------|-----|--------|
| `booking_id` | int | PK |
| `customer_id` | int | مشتری |
| `wc_order_id` | int | سفارش ووکامرس |
| `status` | int | 1=رزرو شده، 2=باز |
| `room_id` | int | محصول/اتاق |
| `booking_time` | int | زمان سانس (Unix) |
| `booked_time` | int | زمان ثبت رزرو (Unix) |
| `name` | varchar | نام بازیکن |
| `phone` | varchar | تلفن |
| `quantity` | int | تعداد بلیط |
| `level` | int | سطح (اختیاری) |

#### محتوا و منبع

- **ثبت رزرو پس از checkout:** در `woocommerce/checkout/thankyou.php` و در `inc/saeed-codes.php` با INSERT مستقیم به دیتابیس queries (از طریق API یا اتصال).
- **باز کردن سانس توسط تیم:** در `web-service/team/sans_management.php`, `web-service/saeed.php`, `web-service/reservation.php` با INSERT رکورد با `status=2`.

#### استفاده در کد

- پر کردن `order_sans_time` / `order_sans_day` / `order_sans_date` در `wp_markting` (تابع `check_and_update_markting_table`).
- بررسی امکان ثبت نظر بعد از سانس: `product_add_comment.php`.
- منطق لغو: `cancellation_functions.php`.
- گزارش‌ها و لیست سفارش‌ها: `orders_get.php`, `orders_get2.php`, `fetch_data.php`, و غیره.
- مدل `BookingHistory` (اتصال external).

#### قوانین CRUD

| عملیات | زمان / شرط |
|--------|------------|
| **CREATE** | پس از تکمیل checkout؛ یا باز کردن سانس از پنل/API |
| **READ** | همه گزارش‌ها، مارکتینگ، لغو، کامنت |
| **UPDATE** | تغییر وضعیت سانس (مثلاً لغو → باز) |
| **DELETE** | در صورت لغو کامل ممکن است حذف یا به status باز تغییر کند (بسته به منطق قدیمی) |

---

### ۳.۴ جدول `wp_zb_booking_history_today`

**هدف:** بهینه‌سازی کوئری برای سانس‌های «امروز»؛ کپی زیرمجموعه از `wp_zb_booking_history`.

**ستون‌ها:** مشابه `wp_zb_booking_history`.

**پر شدن:** Cron روزانه `wp_zb_booking_history_today_optimize_cron` → تابع `wp_zb_booking_history_today_optimize` در saeed-codes؛ کپی سانس‌های امروز و حذف قدیمی‌ترها.

---

### ۳.۵ جدول `product_views`

**دو استفاده در پروژه:**

1. **در escapezo_ez9920 (تم):** جدول `product_views` با ستون‌های `id`, `product_id`, `date`, `count` — در `app/ajax/callbacks/site/product_set_view.php` با `ez_table('product_views')` و همراه با `ip_checker` برای جلوگیری از شمارش تکراری در یک روز.
2. **در escapezo_queries (web-service):** جدول `product_views` با ستون‌هایی مثل `product_id`, `views` (و احتمالاً `views30`) — در `web-service/web-service.php` برای افزایش بازدید و در `post_view_ip_checker` برای محدودیت IP در ۲۴ ساعت.

برای Migration باید هر دو ساختار و دیتابیس در نظر گرفته شود.

---

### ۳.۶ جدول `products_order`

**هدف:** لیست‌های از پیش محاسبه‌شده برای نمایش (محبوب، پرفروش، جدید، ترند، داغ، نوروز).

**ستون‌ها:** `ID`, `popular`, `topsale`, `recent`, `trend`, `hottest`, `nuwruz` (معمولاً یک رکورد؛ مقادیر serialized).

**محتوا و منبع:** در `web-service/web-service.php`؛ در هر درخواست API مربوطه، اگر رکورد نباشد INSERT وگرنه UPDATE روی همان ستون.

---

### ۳.۷ جدول `calendar_data`

**هدف:** داده تقویم (تعطیلات، روزهای بسته).

**ستون‌ها:** `ID`, `data` (serialized).

**محتوا و منبع:** در `web-service/helper-functions.php`؛ تابع `get_day_type` / `get_day_type2` از این جدول می‌خواند. پر شدن از API با نوع `ez_calendar` (در صورت وجود).

---

### ۳.۸ جدول `tags`

**هدف:** تگ‌ها و لیست محصولات هر تگ.

**ستون‌ها:** `tag_id`, `tag_title`, `products` (serialized).

**محتوا و منبع:** در `web-service/helper-functions.php` تابع `logintotag` برای INSERT؛ استفاده در API تگ‌ها.

---

### ۳.۹ جدول `post_view_ip_checker`

**هدف:** جلوگیری از شمارش بازدید تکراری از یک IP برای یک محصول در ۲۴ ساعت.

**ستون‌ها:** `ID`, `product_id`, `ip`, `view_at` (Unix).

**محتوا و منبع:** در `web-service/web-service.php` در تابع مربوط به بازدید محصول؛ INSERT هنگام بازدید مجاز؛ DELETE رکوردهای قدیمی‌تر از ۲۴ ساعت.

---

### ۳.۱۰ جدول `cpc_tracking`

**هدف:** ردیابی کلیک‌های CPC (منبع، کمپین، تعداد).

**ستون‌ها:** `ID`, `ip`, `medium`, `source`, `terms`, `campaign`, `count`.

**محتوا و منبع:** در web-service هنگام کلیک روی لینک CPC؛ اگر IP وجود داشت افزایش `count`، وگرنه INSERT.

---

### ۳.۱۱ جدول `hackers`

**هدف:** لاگ امنیتی درخواست‌های با هاست/referer غیرمجاز.

**ستون‌ها:** `ID`, `host`, `referer`.

**محتوا و منبع:** در `web-service/web-service.php` و `queryable.php` هنگام تشخیص HTTP_HOST غیرمجاز.

---

## ۴. قوانین CRUD و جریان داده

### ۴.۱ خلاصه نقاط ورود اصلی

| رویداد | دیتابیس | جداول درگیر | عملیات |
|--------|---------|-------------|--------|
| Checkout تکمیل شد | ez9920 + queries | wp_markting (INSERT/UPDATE), wp_zb_booking_history (INSERT) | ذخیره سفارش و سانس |
| وضعیت سفارش عوض شد | ez9920 | wp_markting (UPDATE), wp_order_status_log (INSERT) | به‌روزرسانی وضعیت و لاگ |
| پرداخت کامل/جزئی | ez9920 | wp_markting (UPDATE), wallet_transactions در صورت استفاده | به‌روزرسانی مالی و سانس از booking_history |
| ذخیره/ویرایش محصول | ez9920 | wp_products_search (INSERT/UPDATE/DELETE) | سینک جستجو |
| Cron محصولات queries | queries | products_data (DELETE+INSERT) | به‌روزرسانی انبوه |
| بازدید محصول (سایت) | ez9920 | product_views, ip_checker (INSERT/UPDATE) | شمارش بازدید |
| بازدید محصول (web-service) | queries | product_views, post_view_ip_checker (INSERT/UPDATE/DELETE) | شمارش و محدودیت IP |
| درخواست لغو | ez9920 + queries | wp_cancellation_requests (INSERT), wp_cancellation_log (INSERT), خواندن wp_zb_booking_history | ثبت درخواست و لاگ |
| باز کردن سانس توسط تیم | queries | wp_zb_booking_history (INSERT) | سانس جدید با status=2 |

### ۴.۲ وابستگی بین دو دیتابیس

- **wp_markting.order_sans_*** وابسته به **wp_zb_booking_history.booking_time** است (خواندن از queries و نوشتن در ez9920).
- گزارش‌ها و پنل اغلب از هر دو دیتابیس استفاده می‌کنند (مثلاً مارکتینگ از ez9920، سانس از queries).
- رزرو و باز کردن سانس فقط در queries انجام می‌شود؛ نمایش و گزارش در هر دو.

---

## ۵. نقشه روابط و وابستگی‌ها

- **سفارش (order_id):** wp_posts (shop_order) ←→ wp_markting (۱:۱), wp_orders_log (۱:N), wp_order_status_log (۱:N), wp_zb_booking_history (۱:۱ یا ۱:N بسته به تعریف), wp_cancellation_requests (۱:N).
- **محصول (product_id/game_id):** wp_posts (product) ←→ wp_products_search (۱:۱), products_data (۱:۱), wp_zb_booking_history.room_id (۱:N), product_views (۱:N با date), wp_cancellation_requests (N:۱).
- **کاربر (user_id):** wp_users ←→ wp_markting.customer_id, wallet_transactions.user_id, wp_cancellation_requests.requester_id, wp_order_status_log.user_id.

---

## ۶. پیشنهاد برای سیستم Migration

1. **یکپارچه‌سازی دیتابیس (اختیاری اما توصیه‌شده):**
   - انتقال `wp_zb_booking_history` (و در صورت نیاز `wp_zb_booking_history_today`) به **escapezo_ez9920** تا وابستگی مارکتینگ به یک دیتابیس باشد و JOINها ساده شود.
   - در نظر گرفتن ادغام یا حذف تدریجی **products_data** با **wp_products_search** یا یک ایندکس واحد در یک دیتابیس (با توجه به حجم و کاربرد).

2. **ادغام لاگ سفارش:**
   - ترکیب `wp_orders_log` و `wp_order_status_log` در یک جدول واحد (مثلاً `wp_order_logs`) با فیلد `action` یا `log_type` و فیلدهای مشترک (order_id, user_id, created_at, payload).

3. **اسکیما و نسخه‌گذاری:**
   - هر جدول سفارشی با فایل migration جدا (create/alter) با نسخه و تاریخ.
   - مستند کردن دقیق نوع هر ستون (int, decimal, varchar, text, json, datetime) و مقدار پیش‌فرض و nullable.

4. **داده‌های سریال‌شده:**
   - شناسایی تمام فیلدهای serialized/JSON (مثل order_phones, product_brand, product_city, products_order.*) و در Migration جدید ترجیحاً استفاده از نوع JSON واقعی و یکسان‌سازی encode/decode.

5. **Cron و صف‌ها:**
   - لیست کردن تمام cronهایی که روی این جداول کار می‌کنند (ez_queryable_set_data_cron, wp_zb_booking_history_today_optimize_cron و ...) و در Migration جدید نگه‌داری یا جایگزینی با Job/Queue.

6. **ایندکس‌ها:**
   - استفاده از فایل‌های موجود مثل `add-indexes.sql` برای wp_markting, wp_users, wp_postmeta و غیره؛ در Migration جدید تعریف صریح همه indexها و عدم وابستگی به ایندکس‌های پیش‌فرض.

7. **تست پس از Migration:**
   - چک کردن: checkout کامل، تغییر وضعیت سفارش، محاسبه مالی، پر شدن order_sans_* از booking، جستجوی محصول، بازدید محصول، درخواست لغو، کیف پول، و گزارش‌های مارکتینگ/مالی.

---

## ۷. خلاصه CRUD به تفکیک جدول

| جدول | CREATE | READ | UPDATE | DELETE |
|------|--------|------|--------|--------|
| **escapezo_ez9920** | | | | |
| wp_markting | checkout | گزارش/پنل | وضعیت/سانس/مالی | — |
| wp_orders_log | log_order_error | orders-log.php | وضعیت/مشاهده | قدیمی‌تر از ۱ ماه |
| wp_order_status_log | log_order_status_change | order-status-log | — | قدیمی‌تر از ۳ ماه |
| wp_products_search | سینک محصول | جستجو سایت/پنل | سینک مجدد | حذف محصول |
| wallet_transactions | واریز/برداشت/خرید | API/گزارش | وضعیت برداشت | — |
| wp_cancellation_requests | create_cancellation_request | پنل/لغو | approve/reject | — |
| wp_cancellation_log | با هر اقدام لغو | ممیزی | — | — |
| wp_call_me | درخواست تماس | — | — | — |
| **escapezo_queries** | | | | |
| products_data | cron | جستجو/گزارش | cron | قبل از درج مجدد |
| wp_zb_booking_history | checkout / باز کردن سانس | مارکتینگ/لغو/کامنت | وضعیت سانس | در صورت لغو (بسته به منطق) |
| wp_zb_booking_history_today | cron | کوئری امروز | — | cron |
| product_views | بازدید/سینک | گزارش بازدید | increment | — |
| products_order | اولین درخواست لیست | API لیست‌ها | به‌روزرسانی ستون | — |
| calendar_data | API ez_calendar | get_day_type | API | — |
| tags | logintotag / API | API تگ | — | — |
| post_view_ip_checker | هر بازدید مجاز | چک IP | — | قدیمی‌تر از ۲۴ ساعت |
| cpc_tracking | اولین کلیک CPC | — | increment count | — |
| hackers | درخواست غیرمجاز | — | — | — |

---

## ۸. پیوست A: ساختار دقیق جداول از COLUMNS.csv

ساختار زیر مستقیماً از خروجی `INFORMATION_SCHEMA.COLUMNS` دیتابیس **escapezo_ez9920** (فایل COLUMNS.csv) استخراج شده است. فرمت: `COLUMN_NAME` — `COLUMN_TYPE` — `IS_NULLABLE` — `COLUMN_KEY` — `COLUMN_DEFAULT` — `EXTRA`.

### جداول بدون پیشوند wp_ (در CSV با همان نام)

**billing_phone_summary**  
`ID` bigint PRI auto_increment · `billing_phone` varchar(20) · `order_count` int · `comments_count` int default 0

**cancellation_log** (در کد با پیشوند → wp_cancellation_log)  
`ID` bigint unsigned PRI auto_increment · `request_id` bigint unsigned MUL · `product_id` bigint unsigned · `user_id` bigint unsigned MUL · `user_role` enum('admin','owner','customer','system') · `action` enum('create','approve','reject','cancel','expire') · `action_time` varchar(20)

**cancellation_requests** (در کد → wp_cancellation_requests)  
`ID` bigint unsigned PRI auto_increment · `order_id` bigint unsigned MUL · `product_id` bigint unsigned · `requester_id` bigint unsigned MUL · `requester_role` enum('admin','owner','customer') · `requester_type` enum('owner','customer') · `status` enum('pending','approved','rejected','cancelled','expired') default 'pending' · `reason_id` int unsigned NULL · `sans_time` varchar(20) · `created_at` varchar(20) MUL · `updated_at` varchar(20) NULL · `auto_processed_at` varchar(20) NULL · `auto_status` enum('approved','rejected') NULL

**collections**  
`ID` bigint PRI auto_increment · `user_id` bigint · `title` longtext · `users` longtext NULL · `items` longtext · `active` varchar(20) · `type` varchar(100) · `created_at` varchar(20) NULL

**comment_phones_list**  
`ID` bigint PRI auto_increment · `product_id` varchar(20) NULL · `order_id` bigint · `phone` bigint · `sans` bigint NULL

**escapezoom_videos**  
`ID` bigint PRI auto_increment · `post_id` int NULL · `video_id` varchar(100) NULL · `video_title` text NULL · `video_tag` text NULL · `views` bigint default 0 · `created_at` varchar(16) NULL

**ez_popularity_new**  
`ID` bigint PRI auto_increment · `room_id` bigint · `room_name` text · `overall_old` bigint · `overall_new` bigint · `comment_count` bigint · `average_rating` float · `view30` bigint · `views` bigint · `LHS` bigint · `RHS` bigint

**g_cancellation_logs**  
`id` bigint unsigned PRI auto_increment · `request_id` bigint unsigned MUL · `user_id` bigint unsigned MUL · `user_role` enum('player','collector','supporter','system') · `action` enum('create','approve','reject','cancel','auto_approve','auto_reject') · `action_time` datetime · `details` text NULL

**g_cancellation_requests**  
`id` bigint unsigned PRI auto_increment · `order_id` bigint unsigned MUL · `requestor_id` bigint unsigned MUL · `requestor_role` enum('player','collector','supporter') · `request_type` enum('player','collector') · `status` enum('pending','approved','rejected','cancelled','expired') default 'pending' · `reason_id` int unsigned NULL · `created_at` datetime · `updated_at` datetime · `auto_processed_at` datetime NULL · `auto_status` enum('approved','rejected') NULL

**held_orders_list**  
`ID` bigint PRI auto_increment · `room_id` bigint · `order_id` bigint · `count` int · `user_id` bigint default 0 · `level` bigint default 1 · `held_time` varchar(16)

**hottest_products**  
`ID` bigint PRI auto_increment · `product_id` bigint · `comment_id` bigint · `w_rate` varchar(50) · `w_comments_count` int · `time` varchar(16)

**invitations**  
`ID` bigint PRI auto_increment · `inviter_id` bigint · `invited_id` bigint · `product_id` bigint · `status` text · `created_at` varchar(20) NULL

**notifications**  
`id` bigint PRI auto_increment · `notification_id` bigint · `title` text · `content` longtext NULL · `users` longtext · `type` varchar(20) default 'notification' · `read` longtext NULL · `created_at` datetime default CURRENT_TIMESTAMP

**points**  
`ID` bigint PRI auto_increment · `user_id` bigint MUL · `point` bigint · `action` text · `description` longtext · `created_at` text

**product_views**  
`id` bigint unsigned PRI auto_increment · `product_id` bigint unsigned MUL · `date` date · `count` bigint unsigned default 0

**shortlinks**  
`id` bigint unsigned PRI auto_increment · `shortcode` varchar(50) UNI · `long_url` text · `created_at` datetime default CURRENT_TIMESTAMP

**sms_sending_queue**  
`ID` bigint PRI auto_increment · `phone` varchar(20) NULL · `text` longtext NULL · `order_id` bigint NULL · `type` varchar(20) NULL · `query_time` varchar(16) NULL · `sent_time` varchar(16) NULL

**wallet_transactions**  
`ID` bigint PRI auto_increment · `user_id` bigint NULL · `amount` decimal(11,0) · `balance` decimal(11,0) · `description` longtext NULL · `unique_description` varchar(255) UNI NULL · `type` text · `status` text · `origin` int default 0 · `created_at` varchar(15) NULL · `actions` longtext

**wallet_transactions2**  
`ID` bigint PRI auto_increment · `user_id` bigint NULL · `amount` decimal(11,0) · `balance` decimal(11,0) · `description` longtext NULL · `type` text · `status` text · `origin` int default 0 · `created_at` varchar(15) NULL · `actions` longtext

---

### جداول با پیشوند wp_

**wp_markting**  
`ID` bigint unsigned PRI auto_increment · `customer_id` bigint NULL · `customer_firstname` varchar(50) NULL · `customer_lastname` varchar(50) NULL · `customer_phone` varchar(12) NULL · `customer_level` tinyint(1) NULL · `customer_registered_at` datetime NULL · `order_id` bigint unsigned NULL · `order_status` varchar(50) NULL · `order_sans_time` varchar(50) NULL · `order_sans_day` varchar(50) NULL · `order_sans_date` date NULL · `order_phones` text NULL · `order_finall_price` bigint NULL · `order_paid` int NULL · `order_online_paid` bigint NULL · `order_payment_gateway` varchar(255) NULL · `order_payment_type` varchar(50) NULL · `order_user_level_discount` decimal(10,2) NULL · `order_is_satisfied` tinyint(1) default -1 · `order_deposit` bigint NULL · `complete_change_flag` int default 0 · `order_net_profit` bigint NULL · `order_tax` bigint NULL · `order_prepaid_tickets` int NULL · `order_tickets_quantity` int NULL · `order_refrerr` varchar(100) NULL · `order_transaction_id` varchar(30) NULL · `order_coupon_used` varchar(255) NULL · `order_coupon_amount` bigint NULL · `order_coupon_type` varchar(20) NULL · `order_happycall` tinyint(1) default 0 · `order_financials_calculated` tinyint(1) default 0 · `order_created_at` datetime NULL · `game_id` bigint NULL · `game_name` varchar(255) NULL · `game_city` varchar(50) NULL · `game_area` varchar(50) NULL · `game_product_type` varchar(50) NULL · `game_genres` text NULL · `game_duration` int NULL · `game_brand` varchar(255) NULL · `game_sans_manager_id` bigint NULL · `game_user_ebtal_id` bigint NULL · `game_created_at` datetime NULL

**wp_orders_log**  
`id` bigint unsigned PRI auto_increment · `order_id` bigint · `order_function` varchar(255) · `order_log` text · `order_log_status` tinyint(1) default 0 · `order_log_view` tinyint default 0 · `created_at` datetime default CURRENT_TIMESTAMP

**wp_order_status_log**  
`id` bigint PRI auto_increment · `order_id` bigint unsigned · `user_id` bigint unsigned NULL · `status_log` text · `function_used` varchar(255) · `created_at` datetime default CURRENT_TIMESTAMP

**wp_products_search**  
`product_id` bigint unsigned PRI · `product_type` varchar(100) NULL · `product_name` varchar(255) NULL · `product_status` varchar(100) NULL · `product_url` text NULL · `product_image_url` text NULL · `product_brand` json NULL · `product_hood` varchar(255) NULL · `product_city` json NULL · `product_area` json NULL · `product_tags` json NULL

**wp_call_me**  
`id` bigint unsigned PRI auto_increment · `subject` varchar(255) · `phone` varchar(10) · `status` int default 0 · `created_at` datetime

**wp_zb_holiday**  
`holiday_id` int PRI · `time` varchar(16) NULL · `room` int NULL

**wp_zb_orderinfo**  
`order_info_id` int unsigned PRI · `room_name` varchar(250) NULL · `product_id` int NULL · `qty_order` int · `date_order` varchar(250)

---

### نکات تطبیق با کد و Migration

1. **wp_markting:** در دیتابیس PK ستون `ID` (auto_increment) است؛ در مدل Eloquent از `order_id` به عنوان primaryKey استفاده شده و `incrementing = false`. برای Migration بهتر است یا `order_id` را UNIQUE نگه دارید یا از همان `ID` به عنوان PK استفاده کنید و در کد فقط با `order_id` کوئری بزنید.
2. **cancellation_requests:** در CSV فیلد `requester_role` و `requester_type` و enumهای `status` (شامل 'cancelled','expired') و فیلدهای `auto_processed_at`, `auto_status` وجود دارند؛ در کد create_cancellation_tables ممکن است نسخه قدیمی‌تر باشد — Migration باید با همین ساختار CSV هماهنگ شود.
3. **cancellation_log:** در CSV `user_role` enum('admin','owner','customer','system') و `action` شامل 'cancel','expire' است.
4. **wp_order_status_log:** در CSV نام ستون PK برابر `id` (حرف کوچک) است؛ در گزارش قبلی `ID` ذکر شده بود.
5. **wp_products_search:** در CSV نوع `product_brand`, `product_city`, `product_area`, `product_tags` به صورت **json** است (نه text) — برای Migration نوع JSON حفظ شود.
6. **wallet_transactions:** فیلدهای `unique_description` (UNI), `actions` (longtext) در CSV هستند؛ مدل WalletTransaction در کد ممکن است فیلد `actions` را نداشته باشد — در Migration و کد هماهنگ شود.
7. **product_views:** ساختار در ez9920: `id`, `product_id`, `date`, `count` — با استفاده در `product_set_view.php` سازگار است.

---

**پایان گزارش.**  
این سند را می‌توان به همراه فایل‌های `گزارش_دیتابیس_escapezo_queries.md` و `گزارش_دیتابیس_escapezo_ez9920.md` به Gemini داد تا طراحی سیستم Migration مدرن (مثلاً با Laravel Migrations یا ابزار مشابه) انجام شود.
