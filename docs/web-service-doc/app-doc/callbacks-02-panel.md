# AJAX Callbacks — پنل کاربری (Panel)

**مسیر پوشه:** `wp-content/themes/escapezoom-v2/app/ajax/callbacks/`  
**فراخوانی:** با action `v2_ajax_handler` و پارامتر `callback` برابر نام فایل بدون `.php`. اغلب نیاز به کاربر لاگین‌شده دارند.

---

## فهرست Callbacks (panel_*)

| فایل | نقش کلی |
|------|----------|
| panel_orders_get.php | لیست سفارشات کاربر با فیلتر وضعیت (reserved, held, cancelled, all) و صفحه‌بندی؛ داده از جدول wp_markting با medoo |
| panel_points_get.php | دریافت امتیاز و تاریخچه امتیاز کاربر |
| panel_products_get.php | لیست محصولات (بازی‌ها) کاربر برای پنل فروشنده |
| panel_profile_save.php | ذخیره پروفایل (نام، نام خانوادگی، شهر، آواتار و…) |
| panel_wallet_lists_get.php | لیست تراکنش‌های کیف پول |
| panel_wallet_withdrawal.php | درخواست برداشت از کیف پول |
| panel_collection_*.php | کالکشن: افزودن، حذف، ویرایش نام، افزودن/حذف محصول، به‌روزرسانی، دریافت، toggle لایک |
| panel_comments_list_get.php | لیست نظرات کاربر |
| panel_comments_reply_add.php | افزودن پاسخ به نظر |
| panel_invitation_get_invited.php | لیست دعوت‌شدگان |
| panel_invitation_change_status.php | تغییر وضعیت دعوت |
| panel_notifications_read.php | علامت‌گذاری اعلان‌ها به عنوان خوانده‌شده |
| panel_sans_manager_get.php | داده‌های مدیریت سانس برای فروشنده |
| panel_sans_settings_update.php | به‌روزرسانی تنظیمات سانس |
| panel_sells_get_summary.php | خلاصه فروش |
| panel_sells_get_tables.php | جداول/لیست فروش |
| panel_auto_disable_update.php | به‌روزرسانی تنظیم غیرفعال‌سازی خودکار (محصول/سانس) |

---

## panel_orders_get.php (نمونه)

- **ورودی (POST):** `status` (reserved | held | cancelled | all), `page`.
- **منطق:** کاربر جاری از `get_current_user_id()`؛ فیلتر وضعیت به وضعیت‌های ووکامرس (مثلاً wc-partially-paid, wc-walletx, wc-completed, wc-admin-cancelled, wc-refunded, wc-conflict)؛ کوئری از جدول `wp_markting` با medoo؛ خروجی شامل order_id, game_id, game_name, تعداد بلیت، تاریخ سانس، مبلغ، وضعیت و احتمالاً لینک لغو/جزئیات.
- **خروجی:** JSON با آیتم‌ها و صفحه‌بندی (total_pages و غیره).
- **وابستگی:** تابع `medoo()` برای اتصال به دیتابیس؛ جدول wp_markting.

---

## panel_collection_* (خلاصه)

- **panel_collection_add.php:** ایجاد کالکشن جدید؛ احتمالاً do_action('collection_add') برای امتیاز.
- **panel_collection_delete.php:** حذف کالکشن.
- **panel_collection_edit_name.php:** تغییر نام کالکشن.
- **panel_collection_get.php:** دریافت لیست کالکشن‌های کاربر.
- **panel_collection_product_add.php / panel_collection_product_remove.php:** افزودن/حذف محصول به/از کالکشن.
- **panel_collection_toggle.php:** لایک/آنلایک کالکشن؛ احتمالاً do_action('collection_like').
- **panel_collection_update.php:** به‌روزرسانی کلی کالکشن.

---

## استفاده در سایت

- صفحه «سفارشات من»، «کیف پول»، «کالکشن‌ها»، «پروفایل»، «فروش»، «مدیریت سانس» و اعلان/دعوت در پنل کاربری و فروشنده.

---

## وابستگی

- medoo برای wp_markting و احتمالاً جداول دیگر.
- توابع سطح کاربر (get_user_level, user_features_access) برای محدودیت قابلیت‌ها.
- جدول‌های collections, invitations, points و متاهای کاربر.

---

## بهینه‌سازی پیشنهادی

1. در همه callbacks بررسی لاگین و در صورت نیاز نقش (مثلاً فروشنده) قبل از اجرای منطق.
2. استفاده از nonce برای درخواست‌های حساس (برداشت، حذف، به‌روزرسانی).
3. یکسان‌سازی ساختار پاسخ JSON (مثلاً همیشه items + pagination در لیست‌ها).
