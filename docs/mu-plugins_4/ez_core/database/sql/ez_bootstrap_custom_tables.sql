-- EZ Core — custom / snapshot tables (PRIMARY WordPress DB).
-- Core WP tables are not created here.
--
-- Optional legacy DB (Eloquent connection "escapezo") — in wp-config.php:
--   define('EZ_ESCAPEZO_DB_HOST', 'mysql');
--   define('EZ_ESCAPEZO_DB_NAME', 'escapezo_queries');
--   define('EZ_ESCAPEZO_DB_USER', '...');
--   define('EZ_ESCAPEZO_DB_PASSWORD', '...');
--   optional: EZ_ESCAPEZO_DB_CHARSET, EZ_ESCAPEZO_DB_COLLATE, EZ_ESCAPEZO_DB_PREFIX

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `wp_products_snapshot` (
  `product_id` BIGINT UNSIGNED NOT NULL,
  `product_name` VARCHAR(255) NOT NULL,
  `product_type` VARCHAR(120) DEFAULT NULL,
  `product_status` VARCHAR(50) DEFAULT 'active',
  `product_url` TEXT,
  `product_image_url` TEXT,
  `min_price` INT UNSIGNED NOT NULL DEFAULT 0,
  `min_prepayment_person_count` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `discount_data` LONGTEXT,
  `product_hood` VARCHAR(255) DEFAULT NULL,
  `product_brand` JSON DEFAULT NULL,
  `product_city` JSON DEFAULT NULL,
  `product_area` JSON DEFAULT NULL,
  `product_tags` JSON DEFAULT NULL,
  `comments_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `rate` DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `schedule` JSON DEFAULT NULL,
  `owner_id` BIGINT UNSIGNED DEFAULT NULL,
  `manager_id` BIGINT UNSIGNED DEFAULT NULL,
  `rank_popular` INT UNSIGNED DEFAULT 0,
  `rank_hottest` INT UNSIGNED DEFAULT 0,
  `rank_topsale` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`product_id`),
  KEY `idx_product_status` (`product_status`),
  KEY `idx_hood` (`product_hood`(32)),
  KEY `idx_min_price` (`min_price`),
  KEY `idx_rate` (`rate`),
  KEY `idx_comments_count` (`comments_count`),
  KEY `idx_rank_popular` (`rank_popular`),
  KEY `idx_rank_hottest` (`rank_hottest`),
  KEY `idx_rank_topsale` (`rank_topsale`),
  KEY `idx_owner` (`owner_id`),
  KEY `idx_manager` (`manager_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_product_rank_scores` (
  `product_id` BIGINT UNSIGNED NOT NULL,
  `score_popular` INT UNSIGNED NOT NULL DEFAULT 0,
  `score_hottest` INT UNSIGNED NOT NULL DEFAULT 0,
  `score_topsale` INT UNSIGNED NOT NULL DEFAULT 0,
  `scores_updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`product_id`),
  KEY `idx_score_popular` (`score_popular`),
  KEY `idx_score_hottest` (`score_hottest`),
  KEY `idx_score_topsale` (`score_topsale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_product_penalties` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` BIGINT UNSIGNED NOT NULL,
  `exclude_popular` TINYINT(1) NOT NULL DEFAULT 0,
  `exclude_hottest` TINYINT(1) NOT NULL DEFAULT 0,
  `exclude_topsale` TINYINT(1) NOT NULL DEFAULT 0,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `popular_comment_divisor` DECIMAL(5,2) DEFAULT NULL,
  `topsale_quantity_divisor` DECIMAL(5,2) DEFAULT NULL,
  `active_from` DATETIME DEFAULT NULL,
  `active_until` DATETIME DEFAULT NULL,
  `note` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ez_penalty_product` (`product_id`),
  KEY `idx_ez_penalty_active_from` (`active_from`),
  KEY `idx_ez_penalty_active_until` (`active_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_checkout_intent` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` CHAR(36) NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `cart_key` VARCHAR(64) DEFAULT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'pending',
  `payload` LONGTEXT,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_checkout_intent_uuid` (`uuid`),
  KEY `idx_checkout_intent_user` (`user_id`),
  KEY `idx_checkout_intent_status` (`status`),
  KEY `idx_checkout_intent_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_markting` (
  `order_id` BIGINT UNSIGNED NOT NULL,
  `customer_id` BIGINT UNSIGNED DEFAULT NULL,
  `customer_phone` VARCHAR(32) DEFAULT NULL,
  `game_id` BIGINT UNSIGNED DEFAULT NULL,
  `game_name` VARCHAR(255) DEFAULT NULL,
  `order_status` VARCHAR(64) DEFAULT NULL,
  `order_tickets_quantity` INT UNSIGNED DEFAULT NULL,
  `order_coupon_used` INT UNSIGNED DEFAULT 0,
  `order_finall_price` INT UNSIGNED DEFAULT 0,
  `order_level_discount` INT UNSIGNED DEFAULT 0,
  `order_method` VARCHAR(32) DEFAULT NULL,
  `order_coupon` VARCHAR(191) DEFAULT NULL,
  `order_paid` INT UNSIGNED DEFAULT 0,
  `order_created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `idx_markting_customer` (`customer_id`),
  KEY `idx_markting_game` (`game_id`),
  KEY `idx_markting_status` (`order_status`(32))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_orders_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `order_log_status` VARCHAR(64) DEFAULT NULL,
  `order_log_view` VARCHAR(64) DEFAULT NULL,
  `description` TEXT,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_orders_log_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_order_status_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `from_status` VARCHAR(64) DEFAULT NULL,
  `to_status` VARCHAR(64) DEFAULT NULL,
  `changed_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_order_status_log_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_orders_satisfaction_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `product_id` BIGINT UNSIGNED DEFAULT NULL,
  `status` VARCHAR(64) DEFAULT NULL,
  `meta` LONGTEXT,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sat_order` (`order_id`),
  KEY `idx_sat_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_popular_searches` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `query` VARCHAR(255) NOT NULL,
  `count` INT UNSIGNED NOT NULL DEFAULT 0,
  `last_seen_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_popular_query` (`query`(191)),
  KEY `idx_popular_count` (`count`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_user_search_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED NOT NULL,
  `searches` LONGTEXT,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_search_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Product review relational ratings + increment rollups (EscapeZoom theme).
-- Prefix must match WordPress `$table_prefix` (default wp_). Adjust names if not wp_.
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `wp_ez_rating_criteria` (
  `id` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug` VARCHAR(64) NOT NULL,
  `label` VARCHAR(191) NOT NULL,
  `sort_order` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_ez_rating_criteria_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_comment_rating_rows` (
  `comment_id` BIGINT UNSIGNED NOT NULL,
  `criterion_id` SMALLINT UNSIGNED NOT NULL,
  `score_raw` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_id`,`criterion_id`),
  KEY `idx_ez_comment_rating_rows_criterion` (`criterion_id`),
  KEY `idx_ez_comment_rating_rows_comment` (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_comment_rating_weights` (
  `comment_id` BIGINT UNSIGNED NOT NULL,
  `weight` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `stored_level` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`comment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_product_rating_rollups` (
  `product_id` BIGINT UNSIGNED NOT NULL,
  `criterion_id` SMALLINT UNSIGNED NOT NULL,
  `sum_weighted_score` BIGINT NOT NULL DEFAULT 0,
  `sum_weight` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`,`criterion_id`),
  KEY `idx_ez_product_rating_rollups_product` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `wp_ez_rating_criteria` (`slug`, `label`, `sort_order`, `active`) VALUES
  ('atmosphere', 'فضاسازی', 1, 1),
  ('puzzle', 'کیفیت معما', 2, 1),
  ('creativity', 'تازگی و خلاقیت', 3, 1),
  ('acting', 'بازیگردانی و اکت', 4, 1),
  ('staff', 'برخورد پرسنل', 5, 1)
ON DUPLICATE KEY UPDATE `label` = VALUES(`label`), `sort_order` = VALUES(`sort_order`), `active` = VALUES(`active`);

-- EZ AJAX Gateway (signed /ajax store — apply once via phpMyAdmin; no runtime DDL in PHP).
CREATE TABLE IF NOT EXISTS `wp_ez_ajax_nonces` (
  `nonce` VARBINARY(32) NOT NULL,
  `expires_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`nonce`),
  KEY `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;

CREATE TABLE IF NOT EXISTS `wp_ez_ajax_rate` (
  `bucket` VARBINARY(64) NOT NULL,
  `tokens` SMALLINT UNSIGNED NOT NULL,
  `refill_at` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`bucket`)
) ENGINE=InnoDB DEFAULT CHARSET=ascii;
