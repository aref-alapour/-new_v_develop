-- ==========================================
-- فایل SQL برای اضافه کردن Indexes
-- هدف: کاهش زمان query از 9s به زیر 1s
-- ==========================================

USE escapezo_ez9920;

-- بررسی indexes موجود
SELECT 
    TABLE_NAME,
    INDEX_NAME,
    GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX) as COLUMNS
FROM INFORMATION_SCHEMA.STATISTICS 
WHERE TABLE_SCHEMA = 'escapezo_ez9920'
AND TABLE_NAME IN ('wp_users', 'wp_usermeta', 'wp_posts', 'wp_postmeta')
GROUP BY TABLE_NAME, INDEX_NAME
ORDER BY TABLE_NAME, INDEX_NAME;

-- ==========================================
-- 1. wp_users - جدول کاربران
-- ==========================================

-- Index برای مرتب‌سازی و فیلتر
ALTER TABLE wp_users 
ADD INDEX IF NOT EXISTS idx_user_registered (user_registered);

-- Index برای جستجو در username
ALTER TABLE wp_users 
ADD INDEX IF NOT EXISTS idx_user_login (user_login(50));

-- Index برای جستجو در email
ALTER TABLE wp_users 
ADD INDEX IF NOT EXISTS idx_user_email (user_email(100));

-- ==========================================
-- 2. wp_usermeta - متادیتای کاربران
-- ==========================================

-- Composite index برای فیلتر سریع meta
ALTER TABLE wp_usermeta 
ADD INDEX IF NOT EXISTS idx_user_meta_search (user_id, meta_key, meta_value(100));

-- Index برای جستجو در meta values
ALTER TABLE wp_usermeta 
ADD INDEX IF NOT EXISTS idx_meta_key_value (meta_key, meta_value(100));

-- Index اختصاصی برای wp_capabilities (پرکاربردترین)
ALTER TABLE wp_usermeta 
ADD INDEX IF NOT EXISTS idx_capabilities (meta_key, user_id) 
WHERE meta_key = 'wp_capabilities';

-- ==========================================
-- 3. wp_posts - محصولات و سفارشات
-- ==========================================

-- Index برای فیلتر محصولات
ALTER TABLE wp_posts 
ADD INDEX IF NOT EXISTS idx_post_type_status (post_type, post_status);

-- Index برای مرتب‌سازی
ALTER TABLE wp_posts 
ADD INDEX IF NOT EXISTS idx_post_date (post_date);

-- ==========================================
-- 4. wp_postmeta - متادیتای پست‌ها
-- ==========================================

-- Composite index برای user_ebtal و sans_manager
ALTER TABLE wp_postmeta 
ADD INDEX IF NOT EXISTS idx_post_meta_search (post_id, meta_key, meta_value(100));

-- Index برای جستجوی مدیران بازی
ALTER TABLE wp_postmeta 
ADD INDEX IF NOT EXISTS idx_meta_managers (meta_key, meta_value(20)) 
WHERE meta_key IN ('user_ebtal', 'sans_manager');

-- ==========================================
-- 5. wp_markting - جدول مارکتینگ (اگر وجود دارد)
-- ==========================================

-- Index برای order_id (کلید اصلی)
ALTER TABLE wp_markting 
ADD INDEX IF NOT EXISTS idx_order_id (order_id);

-- Index برای customer_id
ALTER TABLE wp_markting 
ADD INDEX IF NOT EXISTS idx_customer_id (customer_id);

-- Index برای order_status
ALTER TABLE wp_markting 
ADD INDEX IF NOT EXISTS idx_order_status (order_status);

-- Index برای تاریخ سفارش
ALTER TABLE wp_markting 
ADD INDEX IF NOT EXISTS idx_order_created (order_created_at);

-- Composite index برای گزارش‌ها
ALTER TABLE wp_markting 
ADD INDEX IF NOT EXISTS idx_status_date (order_status, order_created_at);

-- ==========================================
-- بررسی نهایی
-- ==========================================

-- نمایش تمام indexes اضافه شده
SHOW INDEX FROM wp_users WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM wp_usermeta WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM wp_posts WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM wp_postmeta WHERE Key_name LIKE 'idx_%';
SHOW INDEX FROM wp_markting WHERE Key_name LIKE 'idx_%';

-- ==========================================
-- تست Performance
-- ==========================================

-- تست جستجوی کاربر
EXPLAIN SELECT * FROM wp_users 
WHERE user_login LIKE '%test%' 
ORDER BY user_registered DESC 
LIMIT 50;

-- تست جستجو در meta
EXPLAIN SELECT DISTINCT u.ID 
FROM wp_users u
WHERE EXISTS (
    SELECT 1 FROM wp_usermeta m 
    WHERE m.user_id = u.ID 
    AND m.meta_key = 'first_name'
    AND m.meta_value LIKE '%test%'
);

-- ==========================================
-- پاکسازی Cache (اختیاری)
-- ==========================================

-- پاک کردن query cache برای تست دقیق
RESET QUERY CACHE;

-- یا غیرفعال/فعال کردن
SET GLOBAL query_cache_type = OFF;
SET GLOBAL query_cache_type = ON;

-- ==========================================
-- نکات مهم
-- ==========================================

-- 1. این indexes حدود 10-100MB فضا می‌گیرند
-- 2. INSERT/UPDATE کمی کندتر می‌شوند (اما SELECT خیلی سریع‌تر)
-- 3. برای جداول بزرگ (>1M rows) ممکن است چند دقیقه طول بکشد
-- 4. قبل از اجرا در production، حتماً backup بگیرید

-- بررسی فضای استفاده شده
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)',
    ROUND((index_length / 1024 / 1024), 2) AS 'Index Size (MB)'
FROM information_schema.TABLES 
WHERE table_schema = 'escapezo_ez9920'
AND table_name IN ('wp_users', 'wp_usermeta', 'wp_posts', 'wp_postmeta', 'wp_markting')
ORDER BY (data_length + index_length) DESC;
