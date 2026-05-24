-- Entity: wp_ez_users, wp_ez_user_contacts
-- پیشوند جدول: wp_

-- جدول ez_users (پروفایل و کش؛ بدون email)
CREATE TABLE IF NOT EXISTS `wp_ez_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `wp_user_id` bigint unsigned DEFAULT NULL COMMENT 'ارتباط با wp_users.ID',
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `national_id` char(10) DEFAULT NULL COMMENT 'کد ملی ۱۰ رقم',
  `iban` varchar(28) DEFAULT NULL COMMENT 'شماره شبا ۲۴ رقم با یا بدون IR',
  `avatar_id` smallint unsigned DEFAULT NULL COMMENT 'آواتار از مجموعهٔ ازپیش‌تعریف‌شده پروژه (۱–۱۵)',
  `level` tinyint unsigned DEFAULT 1 COMMENT '۱ تا ۴ بر اساس امتیاز',
  `points_total` int unsigned DEFAULT 0,
  `orders_count` int unsigned DEFAULT 0,
  `locations_cache` json DEFAULT NULL COMMENT '{"city_ids":[],"area_ids":[]}',
  `status` varchar(20) DEFAULT 'active',
  `internal_role` varchar(32) DEFAULT NULL COMMENT 'customer|owner|manager for fast role checks without meta',
  `birth_date` date DEFAULT NULL COMMENT 'for age limits and birthday features',
  `last_order_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_users_wp_user_id_unique` (`wp_user_id`),
  UNIQUE KEY `ez_users_phone_unique` (`phone`),
  KEY `ez_users_level_index` (`level`),
  KEY `ez_users_points_total_index` (`points_total`),
  KEY `ez_users_orders_count_index` (`orders_count`),
  KEY `ez_users_status_index` (`status`),
  KEY `ez_users_last_order_at_index` (`last_order_at`),
  KEY `ez_users_created_at_index` (`created_at`),
  KEY `ez_users_name_index` (`first_name`,`last_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول ez_user_contacts (مخاطبین هم‌تیمی — برای پیشنهاد در چک‌اوت)
CREATE TABLE IF NOT EXISTS `wp_ez_user_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'ez_users.id',
  `phone` varchar(20) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `usage_count` int unsigned DEFAULT 1,
  `last_used_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_user_contacts_user_phone_unique` (`user_id`,`phone`),
  CONSTRAINT `ez_user_contacts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `wp_ez_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
