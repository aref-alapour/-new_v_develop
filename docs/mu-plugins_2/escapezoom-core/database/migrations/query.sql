-- ─────────────────────────────────────────────────────────────────────────────
-- EscapeZoom Core - جداول اختصاصی
-- برای اجرا: کپی و paste در phpMyAdmin > تب SQL
-- ─────────────────────────────────────────────────────────────────────────────

-- جدول ez_brands
CREATE TABLE IF NOT EXISTS `ez_brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `description` text,
  `address` varchar(255) DEFAULT NULL,
  `score` decimal(3,1) DEFAULT 0,
  `reputation` bigint unsigned DEFAULT 0,
  `game_types` json DEFAULT NULL,
  `teams` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_brands_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_cities (stub — ستون‌های دیگر با migration بعدی اضافه می‌شود)
CREATE TABLE IF NOT EXISTS `ez_cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_areas (stub — ستون‌های دیگر با migration بعدی اضافه می‌شود)
CREATE TABLE IF NOT EXISTS `ez_areas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_users (stub — ستون‌های دیگر با migration بعدی اضافه می‌شود)
CREATE TABLE IF NOT EXISTS `ez_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_products (product_id = wp_posts.ID — رابطه ۱:۱ با وردپرس)
CREATE TABLE IF NOT EXISTS `ez_products` (
  `product_id` bigint unsigned NOT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL,
  `area_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `manager_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `brand_title_cache` varchar(255) DEFAULT NULL,
  `city_name_cache` varchar(255) DEFAULT NULL,
  `area_name_cache` varchar(255) DEFAULT NULL,
  `hood_name` varchar(255) DEFAULT NULL,
  `game_type` varchar(255) NOT NULL,
  `genres_cache` json DEFAULT NULL,
  `url_path_cache` varchar(500) DEFAULT NULL,
  `image_url_cache` varchar(1000) DEFAULT NULL,
  `min_price` int unsigned DEFAULT 0,
  `difficulty_level` tinyint unsigned DEFAULT NULL,
  `schedule_config` json DEFAULT NULL,
  `status` varchar(20) DEFAULT 'publish',
  `sale_status` varchar(30) DEFAULT 'active',
  `sales_count` bigint unsigned DEFAULT 0,
  `capacity_min` smallint unsigned DEFAULT NULL COMMENT 'حداقل تعداد نفر',
  `capacity_max` smallint unsigned DEFAULT NULL COMMENT 'حداکثر تعداد نفر',
  `age_limit` tinyint unsigned DEFAULT NULL COMMENT 'حداقل سن (سال)',
  `duration_minutes` smallint unsigned DEFAULT NULL COMMENT 'مدت بازی (دقیقه)',
  `satisfaction_count` int unsigned DEFAULT NULL COMMENT 'تعداد کل رأی رضایت',
  `satisfaction_positive_count` int unsigned DEFAULT NULL COMMENT 'تعداد رضایت مثبت',
  `hot_score` decimal(10,4) DEFAULT NULL COMMENT 'امتیاز داغ (بیز + بازدید)',
  `topsale_score` decimal(12,2) DEFAULT NULL COMMENT 'امتیاز پرفروش (وزن‌دار)',
  `published_at` timestamp NULL DEFAULT NULL COMMENT 'تاریخ انتشار (post_date)',
  `post_modified_at` timestamp NULL DEFAULT NULL COMMENT 'تاریخ آپدیت (post_modified)',
  `comments_count` int unsigned DEFAULT 0 COMMENT 'تعداد نظرات والد',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `ez_products_brand_id_foreign` (`brand_id`),
  KEY `ez_products_city_id_foreign` (`city_id`),
  KEY `ez_products_area_id_foreign` (`area_id`),
  KEY `ez_products_owner_id_foreign` (`owner_id`),
  KEY `ez_products_manager_id_foreign` (`manager_id`),
  KEY `ez_products_title_index` (`title`),
  KEY `ez_products_brand_title_cache_index` (`brand_title_cache`),
  KEY `ez_products_city_name_cache_index` (`city_name_cache`),
  KEY `ez_products_area_name_cache_index` (`area_name_cache`),
  KEY `ez_products_hood_name_index` (`hood_name`),
  KEY `ez_products_game_type_index` (`game_type`),
  KEY `ez_products_min_price_index` (`min_price`),
  KEY `ez_products_difficulty_level_index` (`difficulty_level`),
  KEY `ez_products_status_index` (`status`),
  KEY `ez_products_sale_status_index` (`sale_status`),
  KEY `ez_products_capacity_max_index` (`capacity_max`),
  KEY `ez_products_age_limit_index` (`age_limit`),
  KEY `ez_products_duration_minutes_index` (`duration_minutes`),
  KEY `ez_products_hot_score_index` (`hot_score`),
  KEY `ez_products_topsale_score_index` (`topsale_score`),
  KEY `ez_products_published_at_index` (`published_at`),
  KEY `ez_products_post_modified_at_index` (`post_modified_at`),
  KEY `ez_products_comments_count_index` (`comments_count`),
  KEY `ez_products_url_path_cache_index` (`url_path_cache`(255)),
  FULLTEXT KEY `ez_products_fulltext` (`title`,`brand_title_cache`,`city_name_cache`,`area_name_cache`,`hood_name`),
  CONSTRAINT `ez_products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `ez_brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `ez_cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `ez_areas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `ez_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `ez_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_slots (سانس‌های قابل رزرو — حداقلی؛ ستون‌های بیشتر با migration بعدی)
-- روز کاری: ۰۸:۰۰ تا ۰۷:۵۹ فردا؛ سانس بعد از نیمه‌شب با تاریخ همان روز ذخیره می‌شود
CREATE TABLE IF NOT EXISTS `ez_slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL COMMENT 'ez_products.product_id = wp_posts.ID',
  `slot_start_at` datetime NOT NULL COMMENT 'شروع سانس (تاریخ روز کاری + زمان)',
  `slot_end_at` datetime DEFAULT NULL COMMENT 'پایان سانس',
  `status` varchar(20) DEFAULT 'available' COMMENT 'available|booked|blocked|cancelled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ez_slots_product_id_foreign` (`product_id`),
  KEY `ez_slots_slot_start_at_index` (`slot_start_at`),
  KEY `ez_slots_status_index` (`status`),
  KEY `ez_slots_product_start_index` (`product_id`,`slot_start_at`),
  CONSTRAINT `ez_slots_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `ez_products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_orders — پل بین سانس رزروشده و سفارش ووکامرس (وضعیت پرداخت، کنسلی، مبلغ، بلیط)
-- wc_order_id = wp_posts.ID (post_type = shop_order) — بدون FK چون ممکن است در دیتابیس دیگر باشد
CREATE TABLE IF NOT EXISTS `ez_orders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wc_order_id` bigint unsigned NOT NULL COMMENT 'سفارش ووکامرس = wp_posts.ID',
  `slot_id` bigint unsigned NOT NULL COMMENT 'سانس رزرو‌شده',
  `product_id` bigint unsigned DEFAULT NULL COMMENT 'کش برای گزارش و فیلتر (ez_products.product_id)',
  `user_id` bigint unsigned DEFAULT NULL COMMENT 'مشتری در سیستم ez (ez_users.id)',
  `payment_status` varchar(30) DEFAULT NULL COMMENT 'paid|pending|failed|refunded',
  `order_status` varchar(30) DEFAULT NULL COMMENT 'completed|cancelled|refunded|pending',
  `total_amount` decimal(12,0) DEFAULT NULL COMMENT 'مبلغ (واحد کوچک‌ترین)',
  `quantity` smallint unsigned DEFAULT 1 COMMENT 'تعداد (مثلاً نفر)',
  `ticket_issued_at` timestamp NULL DEFAULT NULL COMMENT 'زمان صدور بلیط',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_orders_wc_order_slot_unique` (`wc_order_id`,`slot_id`),
  KEY `ez_orders_wc_order_id_index` (`wc_order_id`),
  KEY `ez_orders_slot_id_foreign` (`slot_id`),
  KEY `ez_orders_product_id_foreign` (`product_id`),
  KEY `ez_orders_user_id_foreign` (`user_id`),
  KEY `ez_orders_payment_status_index` (`payment_status`),
  KEY `ez_orders_order_status_index` (`order_status`),
  KEY `ez_orders_ticket_issued_at_index` (`ticket_issued_at`),
  CONSTRAINT `ez_orders_slot_id_foreign` FOREIGN KEY (`slot_id`) REFERENCES `ez_slots` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ez_orders_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `ez_products` (`product_id`) ON DELETE SET NULL,
  CONSTRAINT `ez_orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `ez_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
