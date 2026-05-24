-- Add city_id to collections table (required for panel/my-collections city filtering)
-- Apply manually on the WordPress DB where `collections` lives.

ALTER TABLE `collections`
  ADD COLUMN `city_id` INT NULL AFTER `type`,
  ADD INDEX `idx_collections_user_type_city` (`user_id`, `type`, `city_id`);

