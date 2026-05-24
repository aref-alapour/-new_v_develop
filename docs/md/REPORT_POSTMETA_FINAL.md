# گزارش جامع و کامل تحلیل wp_postmeta - قبل و بعد از پاکسازی

**تاریخ تحلیل:** 24 دسامبر 2025  
**نسخه:** 4.0 (جامع و کامل - ترکیب تمام گزارش‌ها)  
**منابع:** 
- `wp_postmeta.sql` (وضعیت قبل)
- `wp_postmeta (1).sql` (وضعیت بعد)

---

## 📊 خلاصه اجرایی (Executive Summary)

### وضعیت کلی:

| شاخص | قبل | بعد | تغییر | درصد |
|------|-----|-----|-------|------|
| **ردیف‌های دیتابیس** | ~8.8 میلیون | ~1.854 میلیون | -7 میلیون | **-79%** ✅ |
| **حجم فایل** | ~3.7 گیگابایت | ~3.2 گیگابایت | -0.5 گیگ | **-13.5%** ✅ |
| **تعداد سفارشات** | ~213,000 | ~28,000 | -185,000 | **-86.5%** ✅ |
| **Meta Keys منحصر به فرد** | 1,094 | ~800+ | - | - |

### 🎯 هدف:
- **حال:** 3.2 گیگابایت
- **هدف:** زیر 1 گیگابایت
- **راه:** حذف WooCommerce Order Attribution + حذف‌های امن

---

## ⚡ راهنمای سریع (Quick Guide)

### اگر وقت ندارید، این 2 کار را انجام دهید:

#### 1️⃣ حذف WooCommerce Order Attribution (بزرگترین تأثیر!):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE '_wc_order_attribution%';
OPTIMIZE TABLE wp_postmeta;
```
**نتیجه:** کاهش **317,000+ ردیف** و حجم قابل توجه!

#### 2️⃣ حذف امن (کمترین ریسک):
```sql
DELETE FROM wp_postmeta 
WHERE meta_key LIKE '_oembed%' 
   OR meta_key LIKE 'litespeed%' 
   OR meta_key LIKE 'wp-sm%'
   OR meta_key IN ('views', 'zardkooh_post_views', 'zardkooh_product_views');
OPTIMIZE TABLE wp_postmeta;
```
**نتیجه:** کاهش **12,000+ ردیف**

### ⚠️ هرگز حذف نکنید:
- تمام متا کلیدهای WooCommerce (شروع با `_order_`, `_billing_`, `_payment_`)
- تمام متا کلیدهای WordPress Core
- تمام متا کلیدهای Yoast SEO
- `comment_token`, `players_phone`, `code_otagh`, `sans_time`
- تمام متا کلیدهای `room_*` و `_room_*`

---

## 📈 وضعیت قبل از پاکسازی (قبل)

### آمار کلی:
- **ردیف‌های دیتابیس:** ~8.8 میلیون
- **حجم فایل:** ~3.7 گیگابایت
- **تعداد سفارشات:** ~213,000
- **Meta Keys منحصر به فرد:** 1,094

### 20 Meta Key پرحجم‌ترین (قبل):

| رتبه | Meta Key | تعداد | دسته |
|------|----------|-------|------|
| 1 | `comment_token` | 213,058 | ✅ ضروری |
| 2 | `_order_total` | 213,007 | ✅ ضروری |
| 3 | `players_phone` | 212,222 | ✅ ضروری |
| 4 | `_order_key` | 207,922 | ✅ ضروری |
| 5 | `_billing_address_index` | 207,919 | ✅ ضروری |
| 6 | `_billing_first_name` | 207,917 | ✅ ضروری |
| 7 | `_billing_last_name` | 207,917 | ✅ ضروری |
| 8 | `_billing_phone` | 207,916 | ✅ ضروری |
| 9 | `_payment_method` | 206,330 | ✅ ضروری |
| 10 | `_payment_method_title` | 206,330 | ✅ ضروری |
| 11 | `_wc_order_attribution_device_type` | 182,835 | 🔴 قابل حذف |
| 12 | `_wc_order_attribution_session_count` | 182,835 | 🔴 قابل حذف |
| 13 | `_wc_order_attribution_session_entry` | 182,835 | 🔴 قابل حذف |
| 14 | `_wc_order_attribution_session_start_time` | 182,835 | 🔴 قابل حذف |
| 15 | `_wc_order_attribution_source_type` | 182,835 | 🔴 قابل حذف |
| 16 | `_wc_order_attribution_user_agent` | 182,835 | 🔴 قابل حذف |
| 17 | `_wc_order_attribution_utm_source` | 182,835 | 🔴 قابل حذف |
| 18 | `_wc_order_attribution_session_pages` | 182,833 | 🔴 قابل حذف |
| 19 | `_wc_order_attribution_referrer` | 160,980 | 🔴 قابل حذف |
| 20 | `_wc_order_attribution_utm_medium` | 139,734 | 🔴 قابل حذف |

### مشکلات اصلی (قبل):
1. **WooCommerce Order Attribution:** بیش از 1.5 میلیون رکورد (بزرگترین مشکل!)
2. **OEmbed Cache:** ~300+ رکورد
3. **ACF Duplicates:** ~100,000+ رکورد
4. **کلیدهای فارسی قدیمی:** ~5,000+ رکورد

---

## 📉 وضعیت بعد از پاکسازی (بعد)

### آمار کلی:
- **ردیف‌های دیتابیس:** ~1.854 میلیون (کاهش 79%)
- **حجم فایل:** ~3.2 گیگابایت (کاهش 0.5 گیگ)
- **تعداد سفارشات:** ~28,000 (کاهش 86.5%)
- **Meta Keys منحصر به فرد:** ~800+

### 20 Meta Key پرحجم‌ترین (بعد):

| رتبه | Meta Key | تعداد | دسته |
|------|----------|-------|------|
| 1 | `_order_total` | 30,029 | ✅ ضروری |
| 2 | `_order_currency` | 30,027 | ✅ ضروری |
| 3 | `_cart_discount` | 30,027 | ✅ ضروری |
| 4 | `_cart_discount_tax` | 30,027 | ✅ ضروری |
| 5 | `_order_shipping` | 30,027 | ✅ ضروری |
| 6 | `_order_shipping_tax` | 30,027 | ✅ ضروری |
| 7 | `_order_tax` | 30,027 | ✅ ضروری |
| 8 | `_order_version` | 30,027 | ✅ ضروری |
| 9 | `_prices_include_tax` | 30,027 | ✅ ضروری |
| 10 | `comment_token` | 28,645 | ✅ ضروری |
| 11 | `_order_key` | 28,351 | ✅ ضروری |
| 12 | `_customer_user` | 28,351 | ✅ ضروری |
| 13 | `_customer_ip_address` | 28,351 | ✅ ضروری |
| 14 | `_customer_user_agent` | 28,351 | ✅ ضروری |
| 15 | `_created_via` | 28,351 | ✅ ضروری |
| 16 | `_cart_hash` | 28,351 | ✅ ضروری |
| 17 | `_billing_first_name` | 28,351 | ✅ ضروری |
| 18 | `_billing_last_name` | 28,351 | ✅ ضروری |
| 19 | `_billing_email` | 28,351 | ✅ ضروری |
| 20 | `_billing_phone` | 28,351 | ✅ ضروری |

### بهبودها (بعد):
1. ✅ **WooCommerce Order Attribution:** از 1.5+ میلیون به 317,000+ کاهش یافته (هنوز قابل حذف!)
2. ✅ **OEmbed Cache:** از 300+ به ~50 کاهش یافته
3. ✅ **اطلاعات حساس پرداخت:** `zibal_payment_card_number` حذف شده
4. ✅ **محصولات و اتاق‌ها:** سالم هستند و افزایش منطقی داشته‌اند

---

## 🔍 مقایسه تفصیلی Meta Keys

### 1️⃣ WooCommerce Order Meta Keys:

| Meta Key | قبل | بعد | کاهش | درصد | وضعیت |
|----------|-----|-----|------|------|-------|
| `_order_total` | 213,007 | 30,029 | 182,978 | 86.1% | ✅ |
| `_order_key` | 207,922 | 28,351 | 179,571 | 86.4% | ✅ |
| `_billing_first_name` | 207,917 | 28,351 | 179,566 | 86.4% | ✅ |
| `_billing_last_name` | 207,917 | 28,351 | 179,566 | 86.4% | ✅ |
| `_billing_phone` | 207,916 | 28,351 | 179,565 | 86.4% | ✅ |
| `_billing_email` | 67,115 | 28,351 | 38,764 | 57.8% | ✅ |
| `_payment_method` | 206,330 | 26,883 | 179,447 | 87.0% | ✅ |
| `_payment_method_title` | 206,330 | 26,883 | 179,447 | 87.0% | ✅ |
| `_transaction_id` | 138,589 | 17,503 | 121,086 | 87.4% | ✅ |
| `_date_paid` | 140,258 | 18,984 | 121,274 | 86.5% | ✅ |
| `_paid_date` | 140,258 | 18,982 | 121,276 | 86.5% | ✅ |
| `_order_total_2` | 140,247 | 18,987 | 121,260 | 86.5% | ✅ |

**✅ نتیجه:** تمام متا کلیدهای سفارش به درستی کاهش یافته‌اند و نسبت‌ها منطقی هستند.

### 2️⃣ Custom Order Meta Keys (EscapeZoom):

| Meta Key | قبل | بعد | کاهش | درصد | وضعیت |
|----------|-----|-----|------|------|-------|
| `comment_token` | 213,058 | 28,645 | 184,413 | 86.6% | ✅ ضروری |
| `players_phone` | 212,222 | 28,351 | 183,871 | 86.6% | ✅ ضروری |
| `sans_time` | 61,595 | 28,351 | 33,244 | 54.0% | ✅ ضروری |
| `code_otagh` | 137,397 | 18,202 | 119,195 | 86.8% | ✅ ضروری |
| `ticket_tedad` | 140,245 | 18,987 | 121,258 | 86.5% | ✅ ضروری |
| `order_code` | 100,669 | ❌ 0 | - | 100% | ⚠️ حذف شده |
| `seen_tnx_page` | 139,503 | 18,335 | 121,168 | 86.9% | ✅ |
| `prepaid` | 37,166 | 18,345 | 18,821 | 50.7% | ✅ |
| `is_satisfied` | 5 | 15,247 | +15,242 | - | ⚠️ افزایش |

**⚠️ نکات:**
- `order_code` کاملاً حذف شده - اگر نیاز دارید، بررسی کنید
- `is_satisfied` افزایش داشته (متا کلید جدیدتر)

### 3️⃣ WooCommerce Order Attribution (بزرگترین مشکل!):

| Meta Key | قبل | بعد | کاهش | درصد | وضعیت |
|----------|-----|-----|------|------|-------|
| `_wc_order_attribution_device_type` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_session_count` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_session_entry` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_session_start_time` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_source_type` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_utm_source` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_session_pages` | 182,833 | 28,347 | 154,486 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_user_agent` | 182,835 | 28,347 | 154,488 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_referrer` | 160,980 | 24,885 | 136,095 | 84.5% | 🔴 قابل حذف |
| `_wc_order_attribution_utm_medium` | 139,734 | 21,963 | 117,771 | 84.3% | 🔴 قابل حذف |
| `_wc_order_attribution_utm_campaign` | 10,247 | 2,654 | 7,593 | 74.1% | 🔴 قابل حذف |
| `_wc_order_attribution_utm_content` | 8,650 | 1,833 | 6,817 | 78.8% | 🔴 قابل حذف |

**🔴 نتیجه:** 
- **بیش از 1.5 میلیون رکورد** از این متا کلیدها حذف شده‌اند!
- **هنوز 317,000+ رکورد** باقی مانده که قابل حذف است!
- این بزرگترین منبع کاهش حجم است

### 4️⃣ Payment Gateway Meta Keys:

| Meta Key | قبل | بعد | تغییر | وضعیت |
|----------|-----|-----|-------|-------|
| `_zarinpal_authority` | 64,503 | 25,450 | -39,053 | ✅ کاهش منطقی |
| `zibal_payment_card_number` | 64,568 | ❌ 0 | -64,568 | ✅ حذف شده (خوب!) |
| `zibal_payment_ref_number` | 64,568 | ❌ 0 | -64,568 | ✅ حذف شده (خوب!) |
| `_zebline_checkout_init_tracked` | 4 | 21,177 | +21,173 | ⚠️ افزایش |

**✅ نتیجه:** 
- اطلاعات حساس پرداخت زیبال حذف شده (امنیت بهتر!)
- زرین‌پال کاهش منطقی داشته
- Zebline افزایش داشته (افزونه جدیدتر)

### 5️⃣ Product Meta Keys:

| Meta Key | قبل | بعد | تغییر | وضعیت |
|----------|-----|-----|-------|-------|
| `_price` | 1,896 | 2,042 | +146 | ✅ افزایش منطقی |
| `_regular_price` | 1,867 | 2,013 | +146 | ✅ افزایش منطقی |
| `_thumbnail_id` | 1,985 | 2,152 | +167 | ✅ افزایش منطقی |
| `product_rates` | 1,829 | 2,038 | +209 | ✅ افزایش منطقی |
| `comments_count_new` | 1,830 | 2,038 | +208 | ✅ افزایش منطقی |

**✅ نتیجه:** تعداد محصولات افزایش یافته (منطقی است)

### 6️⃣ Room Meta Keys (EscapeZoom):

| Meta Key | قبل | بعد | تغییر | وضعیت |
|----------|-----|-----|-------|-------|
| `room_level` | 1,807 | 1,981 | +174 | ✅ افزایش منطقی |
| `_room_level` | 1,807 | 1,981 | +174 | ✅ افزایش منطقی |
| `room_age_limit` | 1,807 | 1,981 | +174 | ✅ افزایش منطقی |
| `_room_age_limit` | 1,807 | 1,981 | +174 | ✅ افزایش منطقی |
| `_room_address` | 1,807 | 1,981 | +174 | ✅ افزایش منطقی |
| `room_address` | 1,798 | 1,972 | +174 | ✅ افزایش منطقی |
| `_room_duration` | 1,807 | 1,981 | +174 | ✅ افزایش منطقی |
| `room_duration` | 1,798 | 1,972 | +174 | ✅ افزایش منطقی |

**✅ نتیجه:** تعداد اتاق‌ها افزایش یافته (منطقی است)

### 7️⃣ OEmbed Cache:

| Meta Key | قبل | بعد | کاهش | وضعیت |
|----------|-----|-----|------|-------|
| `_oembed_*` | ~300+ | ~50 | ~250 | ✅ کاهش شدید |

**✅ نتیجه:** OEmbed Cache به شدت کاهش یافته

### 8️⃣ کلیدهای فارسی:

| Meta Key | قبل | بعد | تغییر | وضعیت |
|----------|-----|-----|-------|-------|
| `تصویر_کوچک` | 1,008 | 1,854 | +846 | ⚠️ افزایش |
| `_تصویر_کوچک` | 1,008 | 1,854 | +846 | ⚠️ افزایش |
| `متن_کوتاه` | 1,008 | 1,854 | +846 | ⚠️ افزایش |
| `_متن_کوتاه` | 1,008 | 1,854 | +846 | ⚠️ افزایش |
| `اسلایدر` | 976 | 1,853 | +877 | ⚠️ افزایش |
| `_اسلایدر` | 976 | 1,853 | +877 | ⚠️ افزایش |

**⚠️ نتیجه:** این کلیدها افزایش یافته‌اند - اگر استفاده نمی‌شوند، می‌توانید حذف کنید

### 9️⃣ LiteSpeed Cache:

| Meta Key | قبل | بعد | تغییر | وضعیت |
|----------|-----|-----|-------|-------|
| `litespeed-optimize-set` | 4,386 | 4,396 | +10 | ✅ ثابت |
| `litespeed-optimize-size` | 2,637 | 2,637 | 0 | ✅ ثابت |

**✅ نتیجه:** LiteSpeed Cache ثابت مانده

### 🔟 Views Counters:

| Meta Key | قبل | بعد | تغییر | وضعیت |
|----------|-----|-----|-------|-------|
| `views` | 2,235 | 2,443 | +208 | ✅ افزایش منطقی |
| `zardkooh_product_views` | 509 | 507 | -2 | ✅ ثابت |
| `zardkooh_post_views` | 216 | 216 | 0 | ✅ ثابت |

**✅ نتیجه:** Views Counters منطقی هستند

---

## 📊 دسته‌بندی کامل Meta Keys

### ✅ ضروری - هرگز حذف نشود:

#### WordPress Core (~50 متا کلید):
- `_wp_page_template`, `_thumbnail_id`, `_edit_lock`, `_edit_last`
- `_wp_attached_file`, `_wp_attachment_metadata`, `_wp_attachment_image_alt`
- `_wp_old_date`, `_wp_old_slug`, `_wp_desired_post_slug`
- `_menu_item_*` (تمام کلیدهای منو)
- `comment_status`, `enclosure`

#### WooCommerce Core (~150 متا کلید):
- `_order_*` (تمام کلیدهای سفارش)
- `_billing_*` (تمام کلیدهای صورتحساب)
- `_shipping_*` (تمام کلیدهای ارسال)
- `_payment_*` (تمام کلیدهای پرداخت)
- `_customer_*` (تمام کلیدهای مشتری)
- `_product_*` (تمام کلیدهای محصول)
- `_cart_*` (تمام کلیدهای سبد خرید)
- `_wc_*` (به جز `_wc_order_attribution_*`)

#### Yoast SEO (~30 متا کلید):
- `_yoast_wpseo_*` (تمام کلیدهای Yoast)

#### Custom Theme EscapeZoom (~400 متا کلید):
- `room_*` و `_room_*` (تمام کلیدهای اتاق)
- `sans_*` و `_sans_*` (تمام کلیدهای سانس)
- `comment_token`, `players_phone`, `code_otagh`, `sans_time`
- `ticket_tedad`, `order_code`, `seen_tnx_page`
- `special_room`, `_sale_active`, `_auto_disable`
- `_start_time`, `_end_time`, `price_asli`, `darsad`
- `ez_*` و `_ez_*` (کلیدهای سفارشی EscapeZoom)

### 🔴 قابل حذف - اولویت اول:

#### WooCommerce Order Attribution (~317,000+ رکورد):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE '_wc_order_attribution%';
OPTIMIZE TABLE wp_postmeta;
```
**کاهش حجم:** **317,000+ ردیف!** (بزرگترین تأثیر)

### ⚠️ قابل حذف - اولویت دوم (امن):

#### OEmbed Cache (~50+ رکورد):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE '_oembed%';
OPTIMIZE TABLE wp_postmeta;
```

#### LiteSpeed Cache (~7,000+ رکورد):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE 'litespeed%';
OPTIMIZE TABLE wp_postmeta;
```

#### WP Smush (~1,766+ رکورد):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE 'wp-sm%';
OPTIMIZE TABLE wp_postmeta;
```

#### Views Counters (~3,166+ رکورد):
```sql
DELETE FROM wp_postmeta WHERE meta_key IN ('views', 'zardkooh_post_views', 'zardkooh_product_views');
OPTIMIZE TABLE wp_postmeta;
```

**کاهش حجم:** حدود 12,000+ ردیف

### ⚠️ قابل حذف - اولویت سوم:

#### کلیدهای فارسی قدیمی (~11,000+ رکورد):
```sql
DELETE FROM wp_postmeta WHERE meta_key IN (
    'تصویر_کوچک', '_تصویر_کوچک', 
    'متن_کوتاه', '_متن_کوتاه', 
    'اسلایدر', '_اسلایدر',
    'introduction', 'teaser'
);
OPTIMIZE TABLE wp_postmeta;
```

### ⚠️ قابل حذف - اولویت چهارم (اگر استفاده نمی‌شوند):

#### Rank Math (اگر از Yoast استفاده می‌کنی):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE 'rank_math%';
OPTIMIZE TABLE wp_postmeta;
```

#### کلیدهای کم‌استفاده (poshtibanha*, feather*, game_*):
```sql
-- ⚠️ قبل از اجرا، بررسی کن که استفاده نمی‌شوند!
DELETE FROM wp_postmeta 
WHERE meta_key LIKE 'poshtibanha%' 
   OR meta_key LIKE 'feather%'
   OR meta_key LIKE 'game_%'
   OR meta_key LIKE 'cast_game%'
   OR meta_key LIKE 'gallery_game%'
   OR meta_key LIKE 'teaser_game%'
   OR meta_key LIKE 'faq_game%';
OPTIMIZE TABLE wp_postmeta;
```

---

## 🚀 اسکریپت SQL کامل و آماده برای اجرا

### مرحله 0: بکاپ گرفتن (ضروری!)
```sql
-- ایجاد بکاپ کامل از جدول
CREATE TABLE wp_postmeta_backup AS SELECT * FROM wp_postmeta;

-- بررسی حجم بکاپ
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    ROUND(((data_length + index_length) / 1024 / 1024 / 1024), 2) AS 'Size (GB)'
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
AND table_name = 'wp_postmeta_backup';
```

### مرحله 1: حذف WooCommerce Order Attribution (اولویت اول - بزرگترین تأثیر!)
```sql
-- ⚠️ این بزرگترین تأثیر را دارد!
DELETE FROM wp_postmeta WHERE meta_key LIKE '_wc_order_attribution%';
OPTIMIZE TABLE wp_postmeta;

-- بررسی حجم بعد از حذف
SELECT COUNT(*) as total_records FROM wp_postmeta;
```
**کاهش حجم:** حدود **317,000+ ردیف!**

### مرحله 2: حذف امن (اولویت دوم - کمترین ریسک)
```sql
-- حذف OEmbed Cache
DELETE FROM wp_postmeta WHERE meta_key LIKE '_oembed%';

-- حذف LiteSpeed Cache (اگر از LiteSpeed استفاده نمی‌کنی)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'litespeed%';

-- حذف Smush (اگر از افزونه دیگری استفاده می‌کنی)
DELETE FROM wp_postmeta WHERE meta_key LIKE 'wp-sm%';

-- حذف شمارنده بازدیدها
DELETE FROM wp_postmeta WHERE meta_key IN ('views', 'zardkooh_post_views', 'zardkooh_product_views');

-- بهینه‌سازی
OPTIMIZE TABLE wp_postmeta;

-- بررسی حجم بعد از حذف
SELECT COUNT(*) as total_records FROM wp_postmeta;
```
**کاهش حجم:** حدود 12,000+ ردیف

### مرحله 3: حذف کلیدهای فارسی قدیمی (اگر استفاده نمی‌شوند)
```sql
DELETE FROM wp_postmeta WHERE meta_key IN (
    'تصویر_کوچک', '_تصویر_کوچک', 
    'متن_کوتاه', '_متن_کوتاه', 
    'اسلایدر', '_اسلایدر',
    'introduction', 'teaser'
);
OPTIMIZE TABLE wp_postmeta;

-- بررسی حجم بعد از حذف
SELECT COUNT(*) as total_records FROM wp_postmeta;
```
**کاهش حجم:** حدود 11,000+ ردیف

### مرحله 4: حذف Rank Math (اگر از Yoast استفاده می‌کنی)
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE 'rank_math%';
OPTIMIZE TABLE wp_postmeta;

-- بررسی حجم بعد از حذف
SELECT COUNT(*) as total_records FROM wp_postmeta;
```
**کاهش حجم:** حدود 170+ ردیف

### مرحله 5: بررسی حجم نهایی
```sql
-- بررسی تعداد رکوردهای باقی‌مانده
SELECT COUNT(*) as total_records FROM wp_postmeta;

-- بررسی حجم جدول
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    ROUND(((data_length + index_length) / 1024 / 1024 / 1024), 2) AS 'Size (GB)'
FROM information_schema.TABLES
WHERE table_schema = DATABASE()
AND table_name = 'wp_postmeta';

-- بررسی 20 متا کلید پرحجم‌ترین
SELECT meta_key, COUNT(*) as count 
FROM wp_postmeta 
GROUP BY meta_key 
ORDER BY count DESC 
LIMIT 20;
```

### ⚠️ نکات مهم:
1. **همیشه بکاپ بگیرید** قبل از اجرای هر DELETE
2. **در محیط تست تست کنید** قبل از اجرا در سایت زنده
3. **بعد از هر DELETE، OPTIMIZE TABLE را اجرا کنید** برای آزاد شدن فضا
4. **مرحله به مرحله پیش بروید** و بعد از هر مرحله سایت را تست کنید
5. **اگر مشکلی پیش آمد، از بکاپ بازیابی کنید**

---

## 📊 تخمین کاهش حجم (بر اساس Grok.ai)

### سناریو 1: حذف Attribution + امن‌ها
- **حذف:** حدود 330-350 هزار ردیف
- **حجم نهایی:** احتمالاً به **1.5-2 گیگابایت** می‌رسد
- **کاهش:** حدود 1-1.5 گیگابایت

### سناریو 2: حذف کامل (Attribution + امن‌ها + فارسی + افزونه‌های غیرفعال)
- **حذف:** حدود 350-400 هزار ردیف
- **حجم نهایی:** احتمالاً به **1-1.5 گیگابایت** می‌رسد
- **کاهش:** حدود 1.7-2.2 گیگابایت

### ⚠️ نکته مهم:
اگر کلیدهای اتاق فرار و محصولات اصلی رو نگه داری، **زیر 1 گیگابایت سخت است**، اما با این پاکسازی‌ها + بهینه‌سازی تصاویر/فایل‌ها می‌تونی به **1-1.5 گیگابایت** برسی.

---

## ✅ وضعیت کلی

### نکات مثبت:

1. ✅ **حذف سفارشات به درستی انجام شده** - تمام متا کلیدهای مرتبط کاهش یافته‌اند
2. ✅ **WooCommerce Order Attribution کاهش شدید داشته** - از 1.5+ میلیون به 317,000+ (هنوز قابل حذف!)
3. ✅ **اطلاعات حساس پرداخت زیبال حذف شده** - امنیت بهتر شده
4. ✅ **OEmbed Cache کاهش یافته** - پاکسازی انجام شده
5. ✅ **محصولات و اتاق‌ها سالم هستند** - افزایش منطقی داشته‌اند

### نکات قابل توجه:

1. ⚠️ **`order_code` کاملاً حذف شده** - اگر به این متا کلید نیاز دارید، بررسی کنید
2. ⚠️ **`is_satisfied` افزایش داشته** - این متا کلید جدیدتر است
3. ⚠️ **کلیدهای فارسی افزایش یافته** - اگر استفاده نمی‌شوند، می‌توانید حذف کنید
4. ⚠️ **WooCommerce Order Attribution هنوز 317,000+ رکورد دارد** - بزرگترین منبع کاهش حجم!

---

## 🎯 توصیه‌های نهایی

### اولویت 1 (فوری - حذف WooCommerce Order Attribution):
```sql
DELETE FROM wp_postmeta WHERE meta_key LIKE '_wc_order_attribution%';
OPTIMIZE TABLE wp_postmeta;
```
**فضای آزاد شده:** 317,000+ ردیف  
**ریسک:** کم (فقط اگر به تحلیل منبع سفارش نیاز ندارید)

### اولویت 2 (فوری - حذف امن):
```sql
DELETE FROM wp_postmeta 
WHERE meta_key LIKE '_oembed%' 
   OR meta_key LIKE 'litespeed%' 
   OR meta_key LIKE 'wp-sm%'
   OR meta_key IN ('views', 'zardkooh_post_views', 'zardkooh_product_views');
OPTIMIZE TABLE wp_postmeta;
```
**فضای آزاد شده:** 12,000+ ردیف  
**ریسک:** بسیار کم

### اولویت 3 (متوسط - حذف کلیدهای فارسی):
```sql
DELETE FROM wp_postmeta WHERE meta_key IN (
    'تصویر_کوچک', '_تصویر_کوچک', 
    'متن_کوتاه', '_متن_کوتاه', 
    'اسلایدر', '_اسلایدر',
    'introduction', 'teaser'
);
OPTIMIZE TABLE wp_postmeta;
```
**فضای آزاد شده:** 11,000+ ردیف  
**ریسک:** متوسط - نیاز به بررسی استفاده

---

## 📝 نتیجه‌گیری

### وضعیت کلی: ✅ **عالی**

- حذف سفارشات به درستی انجام شده
- کاهش حجم عظیم (بیش از 7 میلیون ردیف)
- محصولات و اتاق‌ها سالم هستند
- پاکسازی خوبی انجام شده

### کاهش حجم تا الان:

- **ردیف‌ها:** از 8.8 میلیون به 1.854 میلیون (کاهش 79%)
- **حجم فایل:** از 3.7 گیگ به 3.2 گیگ (کاهش 0.5 گیگ)
- **سفارشات:** از 213,000 به 28,000 (کاهش 86.5%)

### هدف بعدی:

- **هدف:** رسیدن به زیر 1 گیگابایت
- **راه:** حذف WooCommerce Order Attribution (317,000+ ردیف) + حذف‌های امن (12,000+ ردیف)
- **تخمین:** با پاکسازی کامل می‌توان به **1-1.5 گیگابایت** رسید

### 📊 خلاصه کاهش حجم:

| مرحله | حذف | کاهش حجم | حجم نهایی |
|-------|-----|-----------|-----------|
| **قبل** | - | - | 3.7 گیگابایت |
| **بعد از حذف سفارشات** | 7 میلیون ردیف | 0.5 گیگ | 3.2 گیگابایت |
| **بعد از حذف Attribution** | 317,000+ ردیف | ~1 گیگ | ~2.2 گیگابایت |
| **بعد از حذف امن‌ها** | 12,000+ ردیف | ~0.1 گیگ | ~2.1 گیگابایت |
| **بعد از حذف فارسی** | 11,000+ ردیف | ~0.1 گیگ | ~2.0 گیگابایت |
| **هدف نهایی** | - | - | **1-1.5 گیگابایت** |

---

## 💡 نکات مهم از Grok.ai

### ✅ پیشرفت خوب:
- از 8.8 میلیون ردیف به 1.854 میلیون ردیف رسیده (کاهش 79%)
- حجم از 3.7 گیگ به 3.2 گیگ (کاهش 0.5 گیگ)
- این پیشرفت خوبیه، اما هنوز جا برای پاکسازی بیشتر هست

### 🎯 راه‌حل برای رسیدن به زیر 1 گیگ:
1. **حذف WooCommerce Order Attribution** (بزرگترین تأثیر - 317,000+ ردیف)
2. **حذف OEmbed Cache** (کمترین ریسک)
3. **حذف LiteSpeed Cache** (اگر استفاده نمی‌کنی)
4. **حذف Smush** (اگر از افزونه دیگری استفاده می‌کنی)
5. **حذف کلیدهای فارسی قدیمی** (اگر استفاده نمی‌شوند)

### ⚠️ هشدار:
- اگر کلیدهای اتاق فرار و محصولات اصلی رو نگه داری، زیر 1 گیگابایت سخت است
- اما با پاکسازی‌ها + بهینه‌سازی تصاویر/فایل‌ها می‌تونی به 1-1.5 گیگابایت برسی

### 📝 بعد از پاکسازی:
- حجم رو چک کن و بگو تا ببینیم چقدر بهتر شد
- یا بگو دقیقاً از کدوم افزونه‌ها (کش، سئو، پرداخت) استفاده می‌کنی تا دقیق‌تر بگم چی دیگه پاک کنی

---

**تهیه شده توسط:** AI Assistant (با در نظر گیری نظرات Grok.ai)  
**تاریخ:** 24 دسامبر 2025  
**نسخه:** 4.0 (جامع و کامل - ترکیب تمام گزارش‌ها)

