-- Run once on production after deploying player-wallet-settle unique_description patch.
-- MySQL allows multiple NULL unique_description rows; only non-NULL values must be unique.

ALTER TABLE wallet_transactions
  ADD UNIQUE KEY uq_wallet_unique_description (unique_description);
