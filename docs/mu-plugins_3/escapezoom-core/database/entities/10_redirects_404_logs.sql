-- Entity: Redirect 404 Logs (wp_ez_redirect_404_logs)
-- ثبت مسیرهای 404 برای گزارش‌گیری و پیشنهاد ریدایرکت.
-- پیشوند جدول: wp_ (یا پیشوند سایت). منبع حقیقت: database/entities + init.php

CREATE TABLE IF NOT EXISTS `wp_ez_redirect_404_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `path` varchar(500) NOT NULL COMMENT 'مسیر نرمال‌شده، مثلاً /missing-page/ (بدون دامنه)',
  `referrer` varchar(1000) DEFAULT NULL COMMENT 'Sample referrer (در صورت وجود)',
  `user_agent` varchar(500) DEFAULT NULL COMMENT 'Sample user agent (در صورت وجود)',
  `hit_count` bigint unsigned NOT NULL DEFAULT 0,
  `last_hit_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_redirect_404_logs_path_unique` (`path`),
  KEY `ez_redirect_404_logs_last_hit_at_index` (`last_hit_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

