-- Entity: Commerce (Slots, Orders, Reviews, Affiliate Clicks)
-- جداول رزرو سانس، کش لحظه‌آخری، سفارش‌ها و نظرات.
-- business logic رزرو و پرداخت در سرویس‌های Core پیاده‌سازی می‌شود؛ اینجا فقط اسکیمای دیتابیس است.
-- پیشوند جدول: wp_ (یا پیشوند سایت). منبع حقیقت: database/entities + init.php

-- جدول ez_slots (ترنزاکشنال فقط: فقط سانس‌های pending/booked/blocked ردیف دارند؛ سانس «آزاد» = بدون ردیف)
CREATE TABLE IF NOT EXISTS `wp_ez_slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT 'ez_products.product_id',
  `slot_start_at` datetime NOT NULL COMMENT 'شروع سانس (تاریخ روز کاری + زمان)',
  `slot_end_at` datetime DEFAULT NULL COMMENT 'پایان سانس',
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending|booked|blocked only; no row = available',
  `order_id` bigint unsigned DEFAULT NULL COMMENT 'wc_order_id هنگام booked (اختیاری؛ لینک به سفارش)',
  `price_at_booking` mediumint unsigned DEFAULT NULL COMMENT 'مبلغ در زمان رزرو (واحد کوچک‌ترین)',
  `pending_expires_at` datetime DEFAULT NULL COMMENT 'انقضای رزرو موقت (pending)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ez_slots_slot_start_at_index` (`slot_start_at`),
  KEY `ez_slots_status_index` (`status`),
  KEY `idx_product_status` (`product_id`,`status`),
  UNIQUE KEY `idx_unique_slot` (`product_id`,`slot_start_at`),
  CONSTRAINT `ez_slots_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول کش سانس‌های لحظه‌آخری (city_id → wp_ez_cities؛ بدون tags_cache)
CREATE TABLE IF NOT EXISTS `wp_ez_last_minute_slots_cache` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `slot_start_at` datetime NOT NULL,
  `city_id` bigint unsigned DEFAULT NULL COMMENT 'id شهر در wp_ez_cities',
  `game_type_id` tinyint unsigned DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `city_name_cache` varchar(255) DEFAULT NULL,
  `area_names_cache` varchar(500) DEFAULT NULL,
  `game_type_title_cache` varchar(100) DEFAULT NULL,
  `genre_names_cache` text DEFAULT NULL,
  `moods_cache` text DEFAULT NULL,
  `image_url_cache` varchar(1000) DEFAULT NULL,
  `url_path_cache` varchar(500) DEFAULT NULL,
  `capacity_min` smallint unsigned DEFAULT NULL,
  `capacity_max` smallint unsigned DEFAULT NULL,
  `age_limit` tinyint unsigned DEFAULT NULL,
  `difficulty_level` tinyint unsigned DEFAULT NULL,
  `lm_discount` tinyint unsigned DEFAULT 0,
  `price_before` mediumint unsigned DEFAULT NULL,
  `price_after` mediumint unsigned DEFAULT NULL,
  `computed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_lm_product_time` (`product_id`,`slot_start_at`),
  KEY `idx_lm_city_type_time` (`city_id`,`game_type_id`,`slot_start_at`),
  KEY `idx_lm_time` (`slot_start_at`),
  CONSTRAINT `ez_lm_cache_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `ez_lm_cache_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `wp_ez_cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_lm_cache_game_type_id_foreign` FOREIGN KEY (`game_type_id`) REFERENCES `wp_ez_game_types` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_orders — پل بین سانس رزروشده و سفارش ووکامرس (وضعیت پرداخت، کنسلی، مبلغ، بلیط)
-- wc_order_id = wp_posts.ID (post_type = shop_order) — بدون FK چون ممکن است در دیتابیس دیگر باشد
CREATE TABLE IF NOT EXISTS `wp_ez_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wc_order_id` bigint unsigned NOT NULL COMMENT 'سفارش ووکامرس = wp_posts.ID',
  `slot_id` bigint unsigned DEFAULT NULL COMMENT 'سانس رزرو‌شده؛ هنگام کنسل/refund با حذف ردیف سانس، NULL می‌شود (ON DELETE SET NULL)',
  `product_id` bigint unsigned DEFAULT NULL COMMENT 'کش برای گزارش و فیلتر (ez_products.product_id)',
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'مشتری در سیستم ez (ez_users.id)',
  `payment_status` varchar(30) DEFAULT NULL COMMENT 'paid|pending|failed|refunded',
  `order_status` varchar(30) DEFAULT NULL COMMENT 'completed|cancelled|refunded|pending',
  `total_amount` decimal(12,0) DEFAULT NULL COMMENT 'مبلغ (واحد کوچک‌ترین)',
  `quantity` smallint unsigned DEFAULT 1 COMMENT 'تعداد (مثلاً نفر)',
  `is_last_minute` tinyint(1) DEFAULT 0 COMMENT '۱ = رزرو لحظه‌آخری بر اساس قوانین فعلی محصول در زمان رزرو',
  `lm_discount_percent` tinyint unsigned DEFAULT 0 COMMENT 'درصد تخفیف لحظه‌آخری اعمال‌شده روی این سفارش',
  `price_before_discount` mediumint unsigned DEFAULT NULL COMMENT 'مبلغ قبل از اعمال تخفیف لحظه‌آخری (واحد کوچک‌ترین)',
  `payment_type` enum('complete','partial') NOT NULL DEFAULT 'complete' COMMENT 'پرداخت کامل یا اقساط/کیف پول',
  `customer_level` tinyint unsigned DEFAULT 1 COMMENT 'سطح مشتری ۱–۴ برای وزن سهم پرفروش',
  `topsale_contribution` decimal(12,2) DEFAULT NULL COMMENT 'سهم این سفارش در امتیاز پرفروش (quantity × وزن)',
  `ticket_issued_at` timestamp NULL DEFAULT NULL COMMENT 'زمان صدور بلیط',
  `customer_phone` varchar(20) DEFAULT NULL COMMENT 'شماره تلفن خریدار برای تطبیق با ez_users.phone و participants',
  `participants` json DEFAULT NULL COMMENT 'لیست شماره همراهان (extra_participants_phones) — آرایهٔ json از شماره‌ها',
  `affiliate_id` bigint unsigned DEFAULT NULL COMMENT 'wp_users.ID یا ez_users.id وابسته به طراحی؛ برای کمیسیون همکاری در فروش',
  `created_weekday` varchar(20) DEFAULT NULL COMMENT 'نام روز هفته به فارسی: شنبه، یکشنبه، ...',
  `created_time` varchar(5) DEFAULT NULL COMMENT 'ساعت ثبت به فرمت H:i مثلاً 14:30',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_orders_wc_order_slot_unique` (`wc_order_id`,`slot_id`),
  KEY `ez_orders_wc_order_id_index` (`wc_order_id`),
  KEY `ez_orders_slot_id_foreign` (`slot_id`),
  KEY `ez_orders_product_id_foreign` (`product_id`),
  KEY `ez_orders_user_id_foreign` (`user_id`),
  KEY `ez_orders_affiliate_id_index` (`affiliate_id`),
  KEY `ez_orders_payment_status_index` (`payment_status`),
  KEY `ez_orders_order_status_index` (`order_status`),
  KEY `ez_orders_ticket_issued_at_index` (`ticket_issued_at`),
  KEY `ez_orders_customer_phone_index` (`customer_phone`),
  KEY `idx_ez_orders_lastminute` (`is_last_minute`,`lm_discount_percent`),
  KEY `idx_ez_orders_marketing` (`created_weekday`,`created_time`),
  CONSTRAINT `ez_orders_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `wp_ez_slots` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE SET NULL,
  CONSTRAINT `ez_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `wp_ez_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول کلیک‌های وابسته (affiliate click tracking)
CREATE TABLE IF NOT EXISTS `wp_ez_affiliate_clicks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `affiliate_id` bigint unsigned NOT NULL COMMENT 'wp_users.ID یا ez_users.id وابسته',
  `ip_address` varchar(45) DEFAULT NULL,
  `landing_url` varchar(500) DEFAULT NULL,
  `session_id` varchar(64) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_affiliate_clicks_affiliate_id` (`affiliate_id`),
  KEY `idx_affiliate_clicks_created_at` (`created_at`),
  KEY `idx_affiliate_clicks_session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_reviews — نظرات محصولات
CREATE TABLE IF NOT EXISTS `wp_ez_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT 'ez_products.product_id',
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'شناسه کاربر وردپرس (wp_users.ID) یا NULL برای مهمان',
  `content` text,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `score_decor` tinyint unsigned DEFAULT NULL COMMENT 'امتیاز دکور (۱–۲۰)',
  `score_puzzle` tinyint unsigned DEFAULT NULL COMMENT 'امتیاز معما (۱–۲۰)',
  `score_scare` tinyint unsigned DEFAULT NULL COMMENT 'امتیاز ترس (۱–۲۰)',
  `score_behavior` tinyint unsigned DEFAULT NULL COMMENT 'امتیاز رفتار تیم (۱–۲۰)',
  `score_creative` tinyint unsigned DEFAULT NULL COMMENT 'امتیاز خلاقیت (۱–۲۰)',
  `avg_rating` decimal(3,2) DEFAULT NULL COMMENT 'میانگین نرمال‌شده ۱–۵',
  `weight` tinyint unsigned NOT NULL DEFAULT 1 COMMENT 'وزن رأی (۱، ۲، ۷، ۲۰) بر اساس سطح کاربر',
  `reply_content` text DEFAULT NULL COMMENT 'پاسخ مجموعه به این نظر',
  `reply_updated_at` timestamp NULL DEFAULT NULL COMMENT 'زمان به‌روزرسانی پاسخ',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ez_reviews_user_id_index` (`user_id`),
  KEY `ez_reviews_status_index` (`status`),
  KEY `ez_reviews_product_status_index` (`product_id`,`status`),
  CONSTRAINT `ez_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

