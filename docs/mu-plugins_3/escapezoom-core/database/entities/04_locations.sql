-- Entity: wp_ez_cities, wp_ez_areas (شهر و منطقه؛ رابطه many-to-one)
-- بدون wp_ez_locations. پیشوند جدول: wp_

-- جدول شهرها
CREATE TABLE IF NOT EXISTS `wp_ez_cities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_cities_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول مناطق (وابسته به شهر)
CREATE TABLE IF NOT EXISTS `wp_ez_areas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `city_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_areas_city_slug_unique` (`city_id`,`slug`),
  KEY `ez_areas_city_id_index` (`city_id`),
  CONSTRAINT `ez_areas_city_id_foreign` FOREIGN KEY (`city_id`) REFERENCES `wp_ez_cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
