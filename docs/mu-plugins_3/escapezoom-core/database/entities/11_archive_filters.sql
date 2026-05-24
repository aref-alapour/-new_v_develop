-- Entity: wp_ez_archive_filters
-- فیلترهای هر مسیر آرشیو (type_id, city_id, area_id, genre_id, mood_id, theme_id).
-- پیشوند جدول: wp_

CREATE TABLE IF NOT EXISTS `wp_ez_archive_filters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `archive_map_id` bigint unsigned NOT NULL,
  `filter_type` varchar(50) NOT NULL,
  `filter_value` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_archive_filters_map_type_unique` (`archive_map_id`,`filter_type`),
  KEY `ez_archive_filters_filter_type_index` (`filter_type`),
  KEY `ez_archive_filters_filter_value_index` (`filter_value`),
  CONSTRAINT `ez_archive_filters_archive_map_id_foreign` FOREIGN KEY (`archive_map_id`) REFERENCES `wp_ez_archives_map` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
