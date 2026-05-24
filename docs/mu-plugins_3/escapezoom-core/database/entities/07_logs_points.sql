-- Entity: Logs & Points
-- امتیازات کاربران و لاگ پیشرفتهٔ درخواست‌ها.
-- پیشوند جدول: wp_ (یا پیشوند سایت). منبع حقیقت: database/entities + init.php

-- جدول امتیازات (ez_points) — یک امتیاز یونیک به ازای هر (user_id, reason, related_type, related_id)
CREATE TABLE IF NOT EXISTS `wp_ez_points` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL COMMENT 'کاربر دریافت‌کننده (wp_users.ID)',
  `point` int NOT NULL COMMENT 'مقدار امتیاز',
  `reason` varchar(64) NOT NULL COMMENT 'کد دلیل (مثلاً submit-comment, place-order-leader)',
  `action` varchar(128) DEFAULT NULL COMMENT 'برچسب نوع فعالیت برای نمایش',
  `description` text DEFAULT NULL COMMENT 'توضیح متنی',
  `related_type` varchar(32) DEFAULT NULL COMMENT 'نوع موجودیت: review, order, collection, user یا NULL',
  `related_id` bigint unsigned DEFAULT NULL COMMENT 'شناسه موجودیت وابسته',
  `created_at` timestamp NULL DEFAULT NULL COMMENT 'تاریخ و زمان ثبت امتیاز',
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_points_user_reason_related_unique` (`user_id`,`reason`,`related_type`,`related_id`),
  KEY `ez_points_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- جدول wp_ez_advance_log — لاگ درخواست‌های HTTP، admin-ajax و غیره
CREATE TABLE IF NOT EXISTS `wp_ez_advance_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `request_url` varchar(2048) DEFAULT NULL,
  `source_page` varchar(512) DEFAULT NULL,
  `duration` float DEFAULT NULL,
  `log_time` datetime DEFAULT NULL,
  `request_type` varchar(64) DEFAULT NULL,
  `action_name` varchar(128) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

