-- Entity: Redirects (wp_ez_redirects)
-- قوانین ریدایرکت قابل مدیریت از پنل ادمین.
-- پیشوند جدول: wp_ (یا پیشوند سایت). منبع حقیقت: database/entities + init.php

CREATE TABLE IF NOT EXISTS `wp_ez_redirects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `from_path` varchar(500) NOT NULL COMMENT 'فقط path بدون دامنه، مثلاً /old-page/ یا /blog/old/',
  `to_url` varchar(1000) NOT NULL COMMENT 'می‌تواند نسبی (/room/new/) یا مطلق (https://example.com/new) باشد',
  `status_code` smallint unsigned NOT NULL DEFAULT 301 COMMENT 'یکی از 301, 302, 307',
  `match_type` enum('exact','prefix','regex') NOT NULL DEFAULT 'exact',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `hits` bigint unsigned NOT NULL DEFAULT 0,
  `last_hit_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ez_redirects_from_path_index` (`from_path`),
  KEY `ez_redirects_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

