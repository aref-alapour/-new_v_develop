-- Entity: wp_ez_archives_map (سبک — Relation-Based)
-- فقط شناسنامه مسیر؛ فیلترها در wp_ez_archive_filters.
-- پیشوند جدول: wp_

CREATE TABLE IF NOT EXISTS `wp_ez_archives_map` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT 'عنوان نمایشی در ادمین',
  `path_type` enum('city','type','genre','theme','mood') NOT NULL,
  `slug` varchar(500) NOT NULL,
  `post_id` bigint unsigned NOT NULL COMMENT 'wp_posts.ID (post_type=ez_archive)',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_archives_map_path_slug_unique` (`path_type`,`slug`),
  KEY `ez_archives_map_post_id_index` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
