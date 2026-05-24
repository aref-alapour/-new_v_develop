-- Entity: Products (wp_ez_products و جداول وابسته)
-- شهر/منطقه: city_id → wp_ez_cities؛ مناطق محصول در wp_ez_product_areas. بدون تگ.
-- پیشوند جدول: wp_

-- جدول ez_products (هستهٔ محصول/بازی)
CREATE TABLE IF NOT EXISTS `wp_ez_products` (
  `product_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL COMMENT 'یکتا؛ برای URL مثلاً /room/{slug}/',
  `brand_id` bigint unsigned DEFAULT NULL,
  `city_id` bigint unsigned DEFAULT NULL COMMENT 'id شهر در wp_ez_cities',
  `game_type_id` tinyint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL,
  `manager_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `brand_title_cache` varchar(255) DEFAULT NULL,
  `city_name_cache` varchar(255) DEFAULT NULL COMMENT 'نام شهر برای سرچ',
  `areas_cache` text DEFAULT NULL COMMENT 'نام مناطق برای سرچ',
  `hood_name` varchar(255) DEFAULT NULL COMMENT 'محله فقط برای جستجو و نمایش',
  `genres_cache` text DEFAULT NULL,
  `moods_cache` text DEFAULT NULL,
  `themes_cache` text DEFAULT NULL,
  `url_path_cache` varchar(500) DEFAULT NULL COMMENT 'مسیر نسبی مثلاً /room/slug/',
  `image_url_cache` varchar(1000) DEFAULT NULL,
  `min_price` mediumint unsigned DEFAULT 0,
  `difficulty_level` tinyint unsigned DEFAULT NULL,
  `schedule_config` json DEFAULT NULL,
  `status` varchar(20) DEFAULT 'publish',
  `sale_status` varchar(30) DEFAULT 'active',
  `sales_count` bigint unsigned DEFAULT 0,
  `capacity_min` smallint unsigned DEFAULT NULL,
  `capacity_max` smallint unsigned DEFAULT NULL,
  `age_limit` tinyint unsigned DEFAULT NULL,
  `duration_minutes` smallint unsigned DEFAULT NULL,
  `booking_cutoff_min` smallint unsigned DEFAULT 30 COMMENT 'حداقل فاصله زمانی مجاز بین رزرو و شروع سانس (دقیقه)',
  `satisfaction_count` int unsigned DEFAULT NULL,
  `satisfaction_positive_count` int unsigned DEFAULT NULL,
  `hot_rank` int unsigned DEFAULT NULL COMMENT 'رتبه داغ',
  `topsale_rank` int unsigned DEFAULT NULL COMMENT 'رتبه پرفروش کشوری',
  `popular_rank` int unsigned DEFAULT NULL COMMENT 'رتبه محبوب',
  `published_at` timestamp NULL DEFAULT NULL,
  `comments_count` int unsigned DEFAULT 0,
  `lm_discount_reg` tinyint unsigned DEFAULT 0,
  `lm_discount_hol` tinyint unsigned DEFAULT 0,
  `lm_trigger_reg` smallint unsigned DEFAULT 0,
  `lm_trigger_hol` smallint unsigned DEFAULT 0,
  `lm_trigger_min` smallint unsigned DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  UNIQUE KEY `ez_products_slug_unique` (`slug`),
  KEY `ez_products_brand_id_foreign` (`brand_id`),
  KEY `ez_products_city_id_foreign` (`city_id`),
  KEY `ez_products_owner_id_foreign` (`owner_id`),
  KEY `ez_products_manager_id_foreign` (`manager_id`),
  KEY `ez_products_title_index` (`title`),
  KEY `ez_products_brand_title_cache_index` (`brand_title_cache`),
  KEY `ez_products_city_name_cache_index` (`city_name_cache`),
  KEY `ez_products_min_price_index` (`min_price`),
  KEY `ez_products_difficulty_level_index` (`difficulty_level`),
  KEY `ez_products_sale_status_index` (`sale_status`),
  KEY `ez_products_capacity_max_index` (`capacity_max`),
  KEY `ez_products_age_limit_index` (`age_limit`),
  KEY `ez_products_duration_minutes_index` (`duration_minutes`),
  KEY `ez_products_hot_rank_index` (`hot_rank`),
  KEY `ez_products_topsale_rank_index` (`topsale_rank`),
  KEY `ez_products_popular_rank_index` (`popular_rank`),
  KEY `idx_lm_scanner` (`status`,`lm_trigger_reg`,`lm_trigger_hol`),
  KEY `idx_lm_logic` (`status`,`lm_trigger_min`),
  KEY `idx_city_hot` (`city_id`,`hot_rank`),
  KEY `idx_city_sale` (`city_id`,`topsale_rank`),
  KEY `idx_city_pop` (`city_id`,`popular_rank`),
  KEY `idx_type_hot` (`game_type_id`,`hot_rank`),
  KEY `idx_type_sale` (`game_type_id`,`topsale_rank`),
  KEY `idx_type_pop` (`game_type_id`,`popular_rank`),
  KEY `ez_products_published_at_index` (`published_at`),
  KEY `ez_products_comments_count_index` (`comments_count`),
  KEY `ez_products_url_path_cache_index` (`url_path_cache`(255)),
  FULLTEXT KEY `ez_products_fulltext` (`title`,`brand_title_cache`,`city_name_cache`,`areas_cache`,`hood_name`,`genres_cache`,`moods_cache`,`themes_cache`),
  CONSTRAINT `ez_products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `wp_ez_brands` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `wp_ez_cities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_game_type_id_foreign` FOREIGN KEY (`game_type_id`) REFERENCES `wp_ez_game_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `wp_ez_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `ez_products_manager_id_foreign` FOREIGN KEY (`manager_id`) REFERENCES `wp_ez_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول محتوا و سئو محصول (۱:۱ با ez_products)
CREATE TABLE IF NOT EXISTS `wp_ez_product_content` (
  `product_id` bigint unsigned NOT NULL,
  `short_intro` text DEFAULT NULL,
  `scenario` longtext DEFAULT NULL,
  `rules` longtext DEFAULT NULL,
  `gallery` json DEFAULT NULL,
  `banner_image_url` varchar(1000) DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `full_address` text DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `embed_videos` json DEFAULT NULL,
  `mobile_numbers` json DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` text DEFAULT NULL,
  `og_image_url` varchar(1000) DEFAULT NULL,
  `canonical_url` varchar(500) DEFAULT NULL,
  `shortlink` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  CONSTRAINT `ez_product_content_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- پایوت محصول ↔ منطقه (wp_ez_areas)
CREATE TABLE IF NOT EXISTS `wp_ez_product_areas` (
  `product_id` bigint unsigned NOT NULL,
  `area_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`area_id`),
  KEY `idx_product_areas_area_product` (`area_id`,`product_id`),
  CONSTRAINT `ez_product_areas_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `ez_product_areas_area_id_foreign` FOREIGN KEY (`area_id`) REFERENCES `wp_ez_areas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- پایوت محصول ↔ ژانر
CREATE TABLE IF NOT EXISTS `wp_ez_product_genres` (
  `product_id` bigint unsigned NOT NULL,
  `genre_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`genre_id`),
  KEY `idx_genre_product` (`genre_id`,`product_id`),
  CONSTRAINT `ez_product_genres_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `ez_product_genres_genre_id_foreign` FOREIGN KEY (`genre_id`) REFERENCES `wp_ez_genres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- پایوت محصول ↔ مود
CREATE TABLE IF NOT EXISTS `wp_ez_product_moods` (
  `product_id` bigint unsigned NOT NULL,
  `mood_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`mood_id`),
  KEY `idx_mood_product` (`mood_id`,`product_id`),
  CONSTRAINT `ez_product_moods_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `ez_product_moods_mood_id_foreign` FOREIGN KEY (`mood_id`) REFERENCES `wp_ez_moods` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- پایوت محصول ↔ تم
CREATE TABLE IF NOT EXISTS `wp_ez_product_themes` (
  `product_id` bigint unsigned NOT NULL,
  `theme_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`product_id`,`theme_id`),
  KEY `idx_theme_product` (`theme_id`,`product_id`),
  CONSTRAINT `ez_product_themes_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `ez_product_themes_theme_id_foreign` FOREIGN KEY (`theme_id`) REFERENCES `wp_ez_themes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول wp_ez_product_lookup – فیلتر سریع (city_id، area_ids در json)
CREATE TABLE IF NOT EXISTS `wp_ez_product_lookup` (
  `product_id` bigint unsigned NOT NULL,
  `city_id` bigint unsigned DEFAULT NULL COMMENT 'id شهر در wp_ez_cities',
  `type_id` int unsigned DEFAULT NULL,
  `area_ids` json DEFAULT NULL COMMENT 'آرایه id مناطق در wp_ez_areas',
  `genre_ids` json DEFAULT NULL,
  `mood_ids` json DEFAULT NULL,
  `min_price` mediumint unsigned DEFAULT 0,
  `rating` decimal(3,1) DEFAULT 0.0,
  `status` varchar(20) DEFAULT 'publish',
  PRIMARY KEY (`product_id`),
  KEY `idx_city_type_price` (`city_id`,`type_id`,`min_price`),
  CONSTRAINT `ez_product_lookup_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `wp_ez_products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `ez_product_lookup_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `wp_ez_cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
