-- Index برای فیلتر سریع نقش‌ها
-- اگر این index وجود نداشته باشد، اضافه کنید

-- Index برای wp_capabilities در wp_usermeta
CREATE INDEX idx_capabilities ON wp_usermeta(meta_key, user_id) 
WHERE meta_key = 'wp_capabilities';

-- یا اگر MySQL از WHERE در index پشتیبانی نمی‌کند:
-- CREATE INDEX idx_meta_key_user ON wp_usermeta(meta_key, user_id);

-- بررسی اندازه جدول
SELECT 
    COUNT(*) as total_rows,
    COUNT(DISTINCT user_id) as unique_users,
    COUNT(DISTINCT meta_key) as unique_keys
FROM wp_usermeta;

-- تست سرعت query با EXPLAIN
EXPLAIN SELECT u.ID 
FROM wp_users u 
INNER JOIN wp_usermeta m ON m.user_id = u.ID 
WHERE m.meta_key = 'wp_capabilities' 
AND m.meta_value LIKE '%"customer"%';
