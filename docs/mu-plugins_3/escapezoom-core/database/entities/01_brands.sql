-- Entity: wp_ez_brands
-- استاب CPT ez_brand؛ توضیحات با wp_editor؛ تصویر شاخص در logo و thumbnail_url
-- هم‌راستا با docs-v2/query.sql: logo, score, reputation
-- پیشوند جدول: wp_ (یا پیشوند سایت). منبع حقیقت: database/entities + init.php

CREATE TABLE IF NOT EXISTS `wp_ez_brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `thumbnail_url` varchar(1000) DEFAULT NULL COMMENT 'آدرس کامل تصویر شاخص (ادیتور)',
  `description` longtext DEFAULT NULL COMMENT 'ادیتور پیشرفته (wp_editor)',
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