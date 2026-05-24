-- Add collection_term_id to collections table.
-- Stores the resolved product_cat term_id (city_id + type child) so
-- search/add operations can filter directly by term_id without re-resolving
-- via cities_ids_settings on every request.
-- Apply manually on the WordPress DB where `collections` lives.

ALTER TABLE `collections`
  ADD COLUMN `collection_term_id` INT NULL AFTER `city_id`,
  ADD INDEX `idx_collections_user_type_term` (`user_id`, `type`, `collection_term_id`);
