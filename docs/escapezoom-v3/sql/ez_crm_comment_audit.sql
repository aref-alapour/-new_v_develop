-- جدول لاگ عملیات کامنت CRM (team/comments)
-- پیشوند wp_ را در صورت نیاز با $table_prefix وردپرس خود عوض کنید.
--
-- بعد از اجرای دستی، برای همگام‌سازی با تم این رکورد را در wp_options اضافه/به‌روز کنید:
--   option_name:  ez_crm_comment_audit_db_version
--   option_value: 1

CREATE TABLE IF NOT EXISTS `wp_ez_crm_comment_audit` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `comment_id` bigint(20) unsigned NOT NULL,
  `product_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `product_title` varchar(500) NOT NULL DEFAULT '',
  `comment_user_id` bigint(20) unsigned NOT NULL DEFAULT 0,
  `comment_author_name` varchar(255) NOT NULL DEFAULT '',
  `actor_user_id` bigint(20) unsigned NOT NULL,
  `actor_user_login` varchar(60) NOT NULL DEFAULT '',
  `actor_display_name` varchar(255) NOT NULL DEFAULT '',
  `action` varchar(32) NOT NULL DEFAULT '',
  `approve_subtype` varchar(32) NOT NULL DEFAULT '',
  `comment_created_at` datetime DEFAULT NULL,
  `operated_at` datetime NOT NULL,
  `reason` longtext,
  `details` longtext,
  PRIMARY KEY (`id`),
  KEY `comment_id` (`comment_id`),
  KEY `operated_at` (`operated_at`),
  KEY `actor_user_id` (`actor_user_id`),
  KEY `action` (`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
