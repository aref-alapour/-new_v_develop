-- ==========================================
-- FULLTEXT indexes برای جستجوی زیر ۱ ثانیه
-- یک بار اجرا کنید (مثلاً از phpMyAdmin یا mysql CLI)
-- ==========================================

-- ۱. جستجو در کاربر: لاگین، ایمیل، نام نمایشی
ALTER TABLE wp_users
ADD FULLTEXT INDEX ft_user_search (user_login, user_email, display_name);

-- ۲. جستجو در متای کاربر: نام، موبایل و...
ALTER TABLE wp_usermeta
ADD FULLTEXT INDEX ft_meta_value (meta_value);

-- ۳. جستجو در عنوان محصول (نام بازی) برای user_ebtal / sans_manager
ALTER TABLE wp_posts
ADD FULLTEXT INDEX ft_post_title (post_title);

-- اگر خطای "Duplicate key name" داد یعنی index قبلاً اضافه شده؛ مشکلی نیست.
