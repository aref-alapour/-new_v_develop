-- Entity: Checkout Ledger (Transactions only)
-- Source of truth ledger: wp_ez_payment_transactions
-- Prefix: wp_ (or site prefix)

CREATE TABLE IF NOT EXISTS `wp_ez_payment_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `gateway` varchar(50) DEFAULT NULL COMMENT 'zarinpal|digipay|wallet|manual|...',
  `channel` enum('online','wallet','installment','refund','adjustment') NOT NULL,
  `event_type` enum('wc-incart','bank-request','callback-success','callback-fail','authorize','capture','settle','refund','reverse','fail','cancel') NOT NULL,
  `direction` enum('debit','credit') NOT NULL COMMENT 'debit=money taken, credit=money returned',
  `status` enum('pending','success','failed','reversed') NOT NULL DEFAULT 'pending',
  `amount` decimal(14,0) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'IRR',
  `idempotency_key` varchar(100) NOT NULL,
  `gateway_transaction_id` varchar(100) DEFAULT NULL,
  `gateway_reference_id` varchar(100) DEFAULT NULL,
  `gateway_payload` json DEFAULT NULL,
  `error_code` varchar(50) DEFAULT NULL,
  `error_message` varchar(500) DEFAULT NULL,
  `occurred_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ez_payment_transactions_idempotency_key_unique` (`idempotency_key`),
  KEY `ez_payment_transactions_order_time_index` (`order_id`,`occurred_at`),
  KEY `ez_payment_transactions_gateway_tx_index` (`gateway`,`gateway_transaction_id`),
  KEY `ez_payment_transactions_gateway_ref_index` (`gateway_reference_id`),
  KEY `ez_payment_transactions_status_time_index` (`status`,`occurred_at`),
  KEY `ez_payment_transactions_order_id_index` (`order_id`),
  CONSTRAINT `chk_ez_payment_transactions_amount_positive` CHECK (`amount` > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
