-- Entity: Order System (Session-less, order_id-centric)
-- Core order tables normalized around order_id.

CREATE TABLE IF NOT EXISTS `wp_ez_orders` (
  `order_id` bigint unsigned NOT NULL COMMENT 'Canonical key shared with wp_markting.order_id',
  `user_id` bigint unsigned DEFAULT NULL,
  `product_id` bigint unsigned DEFAULT NULL,
  `slot_start_at` datetime DEFAULT NULL,
  `slot_end_at` datetime DEFAULT NULL,
  `quantity` smallint unsigned NOT NULL DEFAULT 1,
  `stage_status` enum('wc-incart','wc-checkout','wc-bank-pending','wc-complete','wc-failed','wc-expired') NOT NULL DEFAULT 'wc-incart',
  `order_status` enum('draft','pending','partially_paid','paid','failed','cancelled','refunded','expired') NOT NULL DEFAULT 'draft',
  `expires_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `ez_orders_user_id_index` (`user_id`),
  KEY `ez_orders_product_id_index` (`product_id`),
  KEY `ez_orders_stage_status_index` (`stage_status`),
  KEY `ez_orders_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_order_finance` (
  `order_id` bigint unsigned NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'IRR',
  `payment_type` enum('complete','prepaid','installment') NOT NULL DEFAULT 'complete',
  `price_unit` decimal(14,0) NOT NULL DEFAULT 0,
  `gross_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `coupon_discount_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `level_discount_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `payable_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `wallet_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `online_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `installment_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `paid_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `remaining_amount` decimal(14,0) NOT NULL DEFAULT 0,
  `coupon_code` varchar(64) DEFAULT NULL,
  `pricing_snapshot_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  CONSTRAINT `chk_ez_order_finance_non_negative` CHECK (
    price_unit >= 0
    AND gross_amount >= 0
    AND coupon_discount_amount >= 0
    AND level_discount_amount >= 0
    AND payable_amount >= 0
    AND wallet_amount >= 0
    AND online_amount >= 0
    AND installment_amount >= 0
    AND paid_amount >= 0
    AND remaining_amount >= 0
  )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_order_user_snapshot` (
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `display_name` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(190) DEFAULT NULL,
  `user_level` tinyint unsigned DEFAULT NULL,
  `registered_at_snapshot` datetime DEFAULT NULL,
  `snapshot_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `ez_order_user_snapshot_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_order_game_snapshot` (
  `order_id` bigint unsigned NOT NULL,
  `game_id` bigint unsigned DEFAULT NULL,
  `game_name` varchar(255) DEFAULT NULL,
  `game_city` varchar(100) DEFAULT NULL,
  `game_area` varchar(100) DEFAULT NULL,
  `game_duration` int DEFAULT NULL,
  `game_brand` varchar(255) DEFAULT NULL,
  `manager_id` bigint unsigned DEFAULT NULL,
  `price_at_order` decimal(14,0) DEFAULT NULL,
  `snapshot_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  KEY `ez_order_game_snapshot_game_id_index` (`game_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `wp_ez_order_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `is_main_customer` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ez_order_participants_order_id_index` (`order_id`),
  KEY `ez_order_participants_phone_index` (`phone_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
