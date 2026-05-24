# گزارش کامل و جامع دیتابیس escapezo_queries

**تاریخ تهیه گزارش:** 2024  
**دیتابیس:** escapezo_queries  
**هدف:** بررسی کامل جداول، ساختار داده‌ها و زمان ثبت اطلاعات

---

## 📋 فهرست مطالب

1. [جداول دیتابیس escapezo_queries](#جداول-دیتابیس-escapezo_queries)
2. [جداول دیتابیس escapezo_ez9920 (جداول سفارشی)](#جداول-دیتابیس-escapezo_ez9920)
3. [تحلیل و پیشنهادات ترکیب جداول](#تحلیل-و-پیشنهادات-ترکیب-جداول)
4. [راهنمای مهاجرت و ترکیب](#راهنمای-مهاجرت-و-ترکیب)

---

## جداول دیتابیس escapezo_queries

### 1. جدول `products_data`

**توضیحات:** این جدول اطلاعات کامل محصولات (بازی‌ها) را ذخیره می‌کند.

**ستون‌های اصلی:**
- `ID` - شناسه رکورد (AUTO_INCREMENT)
- `product_id` - شناسه محصول در WooCommerce
- `product_type` - نوع محصول (اتاق فرار، لیزرتگ، سینما ترس، ...)
- `title` - نام محصول
- `price` - قیمت محصول
- `notable` - محصول قابل توجه (0/1)
- `special` - محصول ویژه (0/1)
- `active` - وضعیت فعال بودن ('active', 'updated', 'inactive')
- `monopoly` - انحصاری بودن
- `brand_id` - شناسه برند
- `discount_data` - داده‌های تخفیف (serialized)
- `instant_off` - تخفیف فوری (serialized)
- `geo` - مختصات جغرافیایی (lat, lng)
- `image` - مسیر تصویر محصول
- `age_limit` - محدودیت سنی
- `level` - سطح دشواری
- `schedule` - برنامه زمانی سانس‌ها (serialized)
- `duration` - مدت زمان بازی (دقیقه)
- `url` - آدرس نسبی محصول
- `hood` - محله
- `city_id` - شناسه شهر
- `city_name` - نام شهر
- `tags_id` - شناسه‌های تگ‌ها (serialized array)
- `tags_title` - عنوان تگ‌ها (serialized array)
- `count_min` - حداقل تعداد بازیکن
- `count_max` - حداکثر تعداد بازیکن
- `pish_person` - پیش پرداخت به ازای هر نفر
- `auto_disable` - تعداد روز برای غیرفعال شدن خودکار
- `contact_info` - اطلاعات تماس (serialized)
- `owner_id` - شناسه صاحب محصول
- `manager_id` - شناسه مدیر محصول
- `comments_count` - تعداد نظرات
- `rate` - امتیاز محصول

**زمان ثبت داده:**
- **هنگام:** اجرای cron job `ez_queryable_set_data_cron` (روزانه)
- **فرآیند:** 
  - حذف رکوردهای با `active = 'active'` یا `active = 'updated'`
  - درج مجدد تمام محصولات فعال از WordPress
- **منبع:** داده‌ها از WooCommerce products و postmeta استخراج می‌شوند

**استفاده در کد:**
- جستجوی محصولات در `game_search.php`
- نمایش در `marketing_report.php`
- استفاده در `ads-landing.php` برای جستجوی بازی‌ها

---

### 2. جدول `wp_zb_booking_history`

**توضیحات:** این جدول تاریخچه کامل رزروها و سانس‌های رزرو شده را ذخیره می‌کند.

**ستون‌های اصلی:**
- `booking_id` - شناسه رزرو (PRIMARY KEY)
- `customer_id` - شناسه مشتری
- `wc_order_id` - شناسه سفارش WooCommerce
- `status` - وضعیت رزرو (1=رزرو شده، 0=لغو شده)
- `room_id` - شناسه اتاق/محصول
- `booking_time` - زمان سانس (UNIX timestamp)
- `booked_time` - زمان ثبت رزرو (UNIX timestamp)
- `name` - نام بازیکن
- `phone` - شماره تلفن بازیکن
- `quantity` - تعداد بلیط/بازیکن
- `level` - سطح بازیکن (اختیاری)

**زمان ثبت داده:**
- **هنگام:** 
  - ثبت سفارش در WooCommerce (`woocommerce_checkout_order_processed`)
  - تغییر وضعیت سفارش
  - مدیریت سانس‌ها توسط تیم
- **فرآیند:** 
  - درج رکورد جدید هنگام تکمیل checkout
  - آپدیت هنگام تغییر وضعیت
  - حذف هنگام لغو کامل سفارش

**استفاده در کد:**
- بررسی موجود بودن سانس در checkout
- نمایش تاریخچه رزروها
- محاسبه آمار فروش
- مدیریت سانس‌ها در پنل تیم

---

### 3. جدول `wp_zb_booking_history_today`

**توضیحات:** جدول بهینه‌سازی شده برای سانس‌های امروز (برای بهبود عملکرد).

**ستون‌های اصلی:**
- مشابه `wp_zb_booking_history`

**زمان ثبت داده:**
- **هنگام:** اجرای cron job `wp_zb_booking_history_today_optimize_cron` (روزانه)
- **فرآیند:** 
  - کپی سانس‌های امروز از `wp_zb_booking_history`
  - حذف سانس‌های قدیمی‌تر از امروز

**هدف:** بهبود سرعت query برای سانس‌های امروز

---

### 4. جدول `product_views`

**توضیحات:** شمارنده بازدیدهای هر محصول.

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `product_id` - شناسه محصول
- `views` - تعداد بازدیدها (احتمالاً)

**زمان ثبت داده:**
- **هنگام:** 
  - اولین بازدید از محصول (درج رکورد جدید)
  - هر بار که محصول مشاهده می‌شود (افزایش شمارنده)
- **منبع:** `web-service.php` - تابع `product_view`

---

### 5. جدول `products_order`

**توضیحات:** ذخیره لیست‌های مرتب‌سازی محصولات (محبوب، پرفروش، جدید، ترند، داغ، نوروز).

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `popular` - لیست محصولات محبوب (serialized)
- `topsale` - لیست محصولات پرفروش (serialized)
- `recent` - لیست محصولات جدید (serialized)
- `trend` - لیست محصولات ترند (serialized)
- `hottest` - لیست محصولات داغ (serialized)
- `nuwruz` - لیست محصولات نوروزی (serialized)

**زمان ثبت داده:**
- **هنگام:** درخواست API برای دریافت لیست‌های مختلف
- **فرآیند:** 
  - اگر رکورد وجود نداشت: INSERT
  - اگر رکورد وجود داشت: UPDATE
- **منبع:** `web-service.php` - انواع مختلف `data_products_*`

---

### 6. جدول `calendar_data`

**توضیحات:** ذخیره داده‌های تقویم (احتمالاً برای نمایش تاریخ‌های خاص).

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `data` - داده‌های تقویم (serialized)

**زمان ثبت داده:**
- **هنگام:** درخواست API با نوع `ez_calendar`
- **فرآیند:** 
  - اگر رکورد وجود نداشت: INSERT
  - اگر رکورد وجود داشت: UPDATE

---

### 7. جدول `tags`

**توضیحات:** ذخیره تگ‌ها و محصولات مرتبط با هر تگ.

**ستون‌های اصلی:**
- `tag_id` - شناسه تگ
- `tag_title` - عنوان تگ
- `products` - لیست محصولات (serialized)

**زمان ثبت داده:**
- **هنگام:** درخواست API برای تگ‌ها
- **منبع:** `helper-functions.php`

---

### 8. جدول `post_view_ip_checker`

**توضیحات:** بررسی IP برای جلوگیری از شمارش بازدید تکراری.

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `product_id` - شناسه محصول
- `ip` - آدرس IP
- `view_at` - زمان بازدید (UNIX timestamp)

**زمان ثبت داده:**
- **هنگام:** هر بازدید از محصول
- **فرآیند:** 
  - بررسی وجود IP برای محصول در 24 ساعت گذشته
  - اگر وجود نداشت: درج رکورد جدید و افزایش شمارنده
  - حذف خودکار رکوردهای قدیمی‌تر از 24 ساعت

---

### 9. جدول `cpc_tracking`

**توضیحات:** ردیابی کمپین‌های CPC (Cost Per Click).

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `ip` - آدرس IP
- `medium` - رسانه (مثلاً 'cpc')
- `source` - منبع (مثلاً 'google')
- `terms` - کلمات کلیدی (serialized)
- `campaign` - نام کمپین
- `count` - تعداد کلیک‌ها

**زمان ثبت داده:**
- **هنگام:** کلیک روی لینک CPC
- **فرآیند:** 
  - بررسی وجود IP
  - اگر وجود داشت: افزایش count
  - اگر وجود نداشت: درج رکورد جدید

---

### 10. جدول `hackers`

**توضیحات:** لاگ امنیتی برای ثبت تلاش‌های دسترسی غیرمجاز.

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `host` - هاست درخواست
- `referer` - referer درخواست

**زمان ثبت داده:**
- **هنگام:** درخواست از هاست‌های غیرمجاز
- **منبع:** `web-service.php` و `queryable.php` - بررسی HTTP_HOST

---

## جداول دیتابیس escapezo_ez9920 (جداول سفارشی)

### 1. جدول `wp_markting`

**توضیحات:** جدول اصلی برای ذخیره اطلاعات کامل سفارش‌ها و بازاریابی.

**ستون‌های اصلی:**

**اطلاعات مشتری:**
- `customer_id` - شناسه مشتری
- `customer_firstname` - نام
- `customer_lastname` - نام خانوادگی
- `customer_phone` - شماره تلفن
- `customer_registered_at` - تاریخ ثبت‌نام
- `customer_level` - سطح کاربری

**اطلاعات سفارش:**
- `order_id` - شناسه سفارش (PRIMARY KEY)
- `order_status` - وضعیت سفارش
- `order_created_at` - تاریخ ایجاد سفارش
- `order_phones` - شماره تلفن‌های بازیکنان
- `order_prepaid_tickets` - پیش پرداخت به ازای هر نفر
- `order_tickets_quantity` - تعداد بلیط
- `order_refrerr` - منبع ارجاع (UTM source)
- `order_coupon_used` - کد تخفیف استفاده شده
- `order_coupon_amount` - مبلغ تخفیف
- `order_coupon_type` - نوع تخفیف (percentage/fixed)
- `order_transaction_id` - شناسه تراکنش
- `order_happycall` - وضعیت تماس رضایت
- `order_paid` - مبلغ پرداختی
- `order_online_paid` - مبلغ پرداخت آنلاین
- `order_payment_gateway` - درگاه پرداخت
- `order_payment_type` - نوع پرداخت
- `order_user_level_discount` - تخفیف سطح کاربری
- `order_is_satisfied` - رضایت مشتری (-1/0/1)
- `order_deposit` - پیش پرداخت
- `order_finall_price` - قیمت نهایی
- `order_net_profit` - سود خالص
- `order_tax` - مالیات
- `order_sans_time` - زمان سانس (UNIX timestamp)
- `order_sans_day` - روز هفته سانس
- `order_sans_date` - تاریخ سانس

**اطلاعات بازی:**
- `game_id` - شناسه بازی
- `game_name` - نام بازی
- `game_city` - شهر بازی (JSON)
- `game_area` - منطقه بازی (JSON)
- `game_product_type` - نوع محصول
- `game_genres` - ژانرها
- `game_duration` - مدت زمان
- `game_brand` - برند
- `game_sans_manager_id` - شناسه مدیر سانس
- `game_user_ebtal_id` - شناسه کاربر ابطال
- `game_created_at` - تاریخ ایجاد بازی

**زمان ثبت داده:**
- **هنگام:** 
  - تکمیل checkout (`woocommerce_checkout_order_processed`)
  - تغییر وضعیت سفارش
  - به‌روزرسانی اطلاعات سفارش
- **فرآیند:** 
  - درج رکورد جدید هنگام checkout
  - آپدیت هنگام تغییر وضعیت یا اطلاعات
  - آپدیت `order_sans_time` از `wp_zb_booking_history`

**استفاده در کد:**
- گزارش‌های بازاریابی
- گزارش‌های مالی
- نمایش سفارش‌ها در پنل
- تحلیل فروش

---

### 2. جدول `wp_orders_log`

**توضیحات:** لاگ تغییرات سفارش‌ها.

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `order_id` - شناسه سفارش
- `action` - نوع عملیات
- `old_status` - وضعیت قبلی
- `new_status` - وضعیت جدید
- `user_id` - شناسه کاربر انجام‌دهنده
- `created_at` - زمان ثبت

**زمان ثبت داده:**
- **هنگام:** هر تغییر در سفارش
- **منبع:** `functions.php` - تابع `log_order_status_change`

---

### 3. جدول `wp_order_status_log`

**توضیحات:** لاگ تغییرات وضعیت سفارش‌ها.

**ستون‌های اصلی:**
- مشابه `wp_orders_log`

**زمان ثبت داده:**
- **هنگام:** تغییر وضعیت سفارش
- **منبع:** `functions.php`

---

### 4. جدول `wp_products_search`

**توضیحات:** ایندکس جستجوی محصولات برای بهبود سرعت جستجو.

**ستون‌های اصلی:**
- `product_id` - شناسه محصول
- `product_type` - نوع محصول
- `product_name` - نام محصول
- `product_status` - وضعیت
- `product_url` - آدرس نسبی
- `product_image_url` - URL تصویر
- `product_brand` - برند (JSON)
- `product_hood` - محله
- `product_city` - شهر (JSON)
- `product_area` - منطقه (JSON)
- `product_tags` - تگ‌ها (JSON array)

**زمان ثبت داده:**
- **هنگام:** اجرای cron job برای به‌روزرسانی ایندکس
- **منبع:** `page-aref-test.php` - اسکریپت به‌روزرسانی ایندکس

---

### 5. جدول `wallet_transactions`

**توضیحات:** تراکنش‌های کیف پول کاربران.

**ستون‌های اصلی:**
- `ID` - شناسه تراکنش
- `user_id` - شناسه کاربر
- `type` - نوع تراکنش (deposit/withdraw)
- `amount` - مبلغ
- `balance` - موجودی بعد از تراکنش
- `description` - توضیحات
- `status` - وضعیت
- `created_at` - زمان ثبت

**زمان ثبت داده:**
- **هنگام:** 
  - واریز به کیف پول
  - برداشت از کیف پول
  - استفاده از کیف پول برای پرداخت

---

### 6. جدول `wp_cancellation_requests`

**توضیحات:** درخواست‌های لغو سفارش.

**ستون‌های اصلی:**
- `ID` - شناسه درخواست
- `order_id` - شناسه سفارش
- `product_id` - شناسه محصول
- `requester_id` - شناسه درخواست‌دهنده
- `requester_type` - نوع درخواست‌دهنده (customer/team)
- `reason_id` - شناسه دلیل لغو
- `status` - وضعیت (pending/approved/rejected)
- `sans_time` - زمان سانس
- `created_at` - زمان ایجاد
- `updated_at` - زمان به‌روزرسانی

**زمان ثبت داده:**
- **هنگام:** 
  - درخواست لغو از طرف مشتری
  - درخواست لغو از طرف تیم
- **منبع:** `create_cancellation_tables.php`

---

### 7. جدول `wp_cancellation_log`

**توضیحات:** لاگ عملیات‌های لغو سفارش.

**ستون‌های اصلی:**
- `ID` - شناسه رکورد
- `request_id` - شناسه درخواست
- `product_id` - شناسه محصول
- `user_id` - شناسه کاربر
- `user_role` - نقش کاربر
- `action` - نوع عملیات
- `action_time` - زمان عملیات

**زمان ثبت داده:**
- **هنگام:** هر عملیات روی درخواست لغو
- **منبع:** `create_cancellation_tables.php`

---

## تحلیل و پیشنهادات ترکیب جداول

### 🔄 جداول قابل ترکیب

#### 1. `products_data` (escapezo_queries) ↔ `wp_products_search` (escapezo_ez9920)

**تحلیل:**
- هر دو جدول اطلاعات محصولات را ذخیره می‌کنند
- `products_data` کامل‌تر است و شامل تمام اطلاعات محصول
- `wp_products_search` فقط برای جستجو بهینه شده

**پیشنهاد:**
- **ترکیب:** می‌توان `wp_products_search` را حذف کرد و از `products_data` استفاده کرد
- **یا:** `products_data` را به `escapezo_ez9920` منتقل کرد و `wp_products_search` را حذف کرد
- **مزایا:** 
  - کاهش تکرار داده
  - یک منبع واحد برای اطلاعات محصولات
  - ساده‌سازی کد

---

#### 2. `wp_zb_booking_history` (escapezo_queries) ↔ `wp_markting` (escapezo_ez9920)

**تحلیل:**
- `wp_zb_booking_history` اطلاعات سانس‌های رزرو شده را دارد
- `wp_markting` اطلاعات سفارش‌ها را دارد و `order_sans_time` را از `wp_zb_booking_history` می‌گیرد
- رابطه: یک سفارش می‌تواند چند سانس داشته باشد (یک به چند)

**پیشنهاد:**
- **ترکیب:** نمی‌توان این دو را به یک جدول تبدیل کرد (رابطه یک به چند)
- **بهینه‌سازی:** 
  - می‌توان `wp_zb_booking_history` را به `escapezo_ez9920` منتقل کرد
  - یا از JOIN استفاده کرد برای اتصال دو دیتابیس
- **مزایا:** 
  - تمام داده‌های مرتبط در یک دیتابیس
  - بهبود عملکرد JOIN ها

---

#### 3. `wp_orders_log` و `wp_order_status_log` (escapezo_ez9920)

**تحلیل:**
- هر دو جدول لاگ تغییرات سفارش را ذخیره می‌کنند
- احتمالاً تکرار دارند

**پیشنهاد:**
- **ترکیب:** می‌توان این دو را به یک جدول `wp_order_logs` تبدیل کرد
- **مزایا:** 
  - کاهش تکرار
  - مدیریت ساده‌تر

---

### 📊 جداول مستقل (نیازی به ترکیب ندارند)

1. **`product_views`** - شمارنده بازدیدها (مستقل)
2. **`products_order`** - لیست‌های مرتب‌سازی (مستقل)
3. **`calendar_data`** - داده‌های تقویم (مستقل)
4. **`tags`** - تگ‌ها (مستقل)
5. **`post_view_ip_checker`** - بررسی IP (مستقل)
6. **`cpc_tracking`** - ردیابی CPC (مستقل)
7. **`hackers`** - لاگ امنیتی (مستقل)
8. **`wallet_transactions`** - تراکنش‌های کیف پول (مستقل)
9. **`wp_cancellation_requests`** - درخواست‌های لغو (مستقل)
10. **`wp_cancellation_log`** - لاگ لغوها (مستقل)

---

## راهنمای مهاجرت و ترکیب

### مرحله 1: آماده‌سازی

```sql
-- 1. بکاپ از هر دو دیتابیس
mysqldump -u root -p escapezo_queries > backup_queries.sql
mysqldump -u root -p escapezo_ez9920 > backup_ez9920.sql

-- 2. بررسی تعداد رکوردها
SELECT COUNT(*) FROM escapezo_queries.products_data;
SELECT COUNT(*) FROM escapezo_ez9920.wp_products_search;
```

### مرحله 2: انتقال `wp_zb_booking_history` به `escapezo_ez9920`

```sql
-- ایجاد جدول در escapezo_ez9920
CREATE TABLE escapezo_ez9920.wp_zb_booking_history LIKE escapezo_queries.wp_zb_booking_history;

-- انتقال داده‌ها
INSERT INTO escapezo_ez9920.wp_zb_booking_history 
SELECT * FROM escapezo_queries.wp_zb_booking_history;

-- بررسی صحت انتقال
SELECT COUNT(*) FROM escapezo_ez9920.wp_zb_booking_history;
SELECT COUNT(*) FROM escapezo_queries.wp_zb_booking_history;
```

### مرحله 3: حذف `wp_products_search` و استفاده از `products_data`

```sql
-- بکاپ از wp_products_search
CREATE TABLE escapezo_ez9920.wp_products_search_backup LIKE escapezo_ez9920.wp_products_search;
INSERT INTO escapezo_ez9920.wp_products_search_backup SELECT * FROM escapezo_ez9920.wp_products_search;

-- حذف جدول (بعد از اطمینان از کارکرد صحیح)
-- DROP TABLE escapezo_ez9920.wp_products_search;
```

**تغییرات در کد:**
- تغییر تمام query های `wp_products_search` به `products_data`
- تغییر اتصال از `medoo()` به `medoo_queries()`

### مرحله 4: ترکیب `wp_orders_log` و `wp_order_status_log`

```sql
-- ایجاد جدول جدید
CREATE TABLE escapezo_ez9920.wp_order_logs (
    ID int(11) NOT NULL AUTO_INCREMENT,
    order_id int(11) NOT NULL,
    action varchar(50) NOT NULL,
    old_status varchar(50) DEFAULT NULL,
    new_status varchar(50) DEFAULT NULL,
    old_data text DEFAULT NULL,
    new_data text DEFAULT NULL,
    user_id int(11) DEFAULT NULL,
    user_role varchar(50) DEFAULT NULL,
    created_at datetime NOT NULL,
    PRIMARY KEY (ID),
    KEY order_id (order_id),
    KEY created_at (created_at)
);

-- انتقال داده‌ها
INSERT INTO escapezo_ez9920.wp_order_logs (order_id, action, old_status, new_status, user_id, created_at)
SELECT order_id, 'status_change', old_status, new_status, user_id, created_at 
FROM escapezo_ez9920.wp_order_status_log;

INSERT INTO escapezo_ez9920.wp_order_logs (order_id, action, user_id, created_at)
SELECT order_id, action, user_id, created_at 
FROM escapezo_ez9920.wp_orders_log;

-- بکاپ جداول قدیمی
RENAME TABLE escapezo_ez9920.wp_orders_log TO escapezo_ez9920.wp_orders_log_backup;
RENAME TABLE escapezo_ez9920.wp_order_status_log TO escapezo_ez9920.wp_order_status_log_backup;
```

### مرحله 5: به‌روزرسانی کد

**فایل‌های نیازمند تغییر:**

1. **`wp-content/themes/escapezoom-v2/inc/medoo/init.php`**
   - بررسی نیاز به تغییر اتصال‌ها

2. **`wp-content/themes/escapezoom-v2/functions.php`**
   - تغییر query های `wp_products_search` به `products_data`
   - تغییر query های `wp_orders_log` و `wp_order_status_log` به `wp_order_logs`

3. **`wp-content/themes/escapezoom-v2/template/team/pages/marketing_report.php`**
   - تغییر query های `products_data` (اگر نیاز به تغییر دیتابیس باشد)

4. **`web-service/web-service.php`**
   - بررسی query های `wp_zb_booking_history` (اگر به دیتابیس دیگر منتقل شده)

---

## خلاصه و توصیه‌های نهایی

### ✅ اقدامات پیشنهادی

1. **انتقال `wp_zb_booking_history` به `escapezo_ez9920`**
   - تمام داده‌های مرتبط با سفارش‌ها در یک دیتابیس
   - بهبود عملکرد JOIN ها

2. **حذف `wp_products_search` و استفاده از `products_data`**
   - کاهش تکرار داده
   - یک منبع واحد برای اطلاعات محصولات

3. **ترکیب `wp_orders_log` و `wp_order_status_log`**
   - ساده‌سازی ساختار
   - کاهش تکرار

4. **نگه‌داری جداول مستقل در دیتابیس‌های فعلی**
   - `product_views`, `products_order`, `calendar_data`, `tags`, etc.

### ⚠️ نکات مهم

1. **همیشه بکاپ بگیرید** قبل از هر تغییر
2. **تست در محیط development** قبل از production
3. **به‌روزرسانی تدریجی** - یک جدول در هر مرحله
4. **مانیتورینگ عملکرد** بعد از هر تغییر
5. **مستندسازی تغییرات** برای تیم

### 📝 چک‌لیست مهاجرت

- [ ] بکاپ از هر دو دیتابیس
- [ ] تست در محیط development
- [ ] انتقال `wp_zb_booking_history`
- [ ] حذف `wp_products_search`
- [ ] ترکیب لاگ‌ها
- [ ] به‌روزرسانی کد
- [ ] تست کامل عملکرد
- [ ] مستندسازی تغییرات
- [ ] اجرا در production

---

**تهیه شده توسط:** AI Assistant  
**آخرین به‌روزرسانی:** 2024
