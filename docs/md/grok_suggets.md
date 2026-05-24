گزارش جامع تحلیل پروژه EscapeZoom

تاریخ تهیه گزارش: 7 فوریه 2026

تهیه‌کننده: Grok (با بررسی تمام DOCUMENTهای ارائه‌شده)

هدف: ارائه خلاصه کامل پروژه بر اساس فایل‌های ارسال‌شده، شامل ساختار دیتابیس، وابستگی‌ها، سیستم‌های کلیدی (جستجو، رزرو، سفارشات، وب‌سرویس‌ها)، مسائل بهینه‌سازی، و پیشنهادات. این گزارش آماده برای بررسی توسط Gemini است تا تحلیل دقیق‌تری انجام دهد.

این گزارش بر اساس 60+ DOCUMENT (از جمله گزارش‌های تحلیلی، اسکریپت‌های SQL/PHP، مستندات توابع، شورتکدها، و SQL dump) تهیه شده. پروژه EscapeZoom یک پلتفرم رزرو آنلاین بازی‌های فرار (اتاق فرار، سینما ترس، لیزرتگ و ...) است که بر پایه وردپرس/ووکامرس ساخته شده، با تمرکز روی جستجوی پیشرفته، مدیریت سانس/رزرو، سفارشات، و پنل‌های کاربر/تیم. حالا در فاز مهاجرت به لاراول/Eloquent برای بهینه‌سازی دیتابیس و عملکرد.

1. نمای کلی پروژه

نوع پروژه: سیستم رزرو آنلاین بازی‌های فرار با ویژگی‌های e-commerce (ووکامرس برای سفارشات/پرداخت)، جستجوی هوشمند، مدیریت سانس (dynamic بدون ذخیره همه تاریخ‌ها)، پنل تیم/مجموعه‌دار، وب‌سرویس‌های API برای فرانت/اپ، و بهینه‌سازی دیتابیس (پاکسازی قدیمی‌ها، سینک جداول).

فناوری‌ها: وردپرس (تم escapezoom-v2 با توابع سفارشی در saeed-codes.php, ahmadreza, app)، PHP وب‌سرویس‌ها (reservation.php, web-service.php, helper-functions.php)، دیتابیس MySQL (escapezo_ez9920 برای وردپرس، escapezo_queries برای جستجو/رزرو).

وضعیت فعلی: سیستم فعال اما نیاز به بهینه‌سازی (پاکسازی سفارشات قدیمی‌تر از 2 ماه، سینک wp_markting برای گزارش‌گیری، normalize برای جستجو سریع).

مسائل کلیدی: سریالایز داده (مثل schedule در products_data) برای query سخت، تکرار داده بین جداول، وابستگی به session/globalها در توابع، و جستجو بر اساس وزن‌دهی (برچسب > نوع بازی > شهر > نام محصول > ...).

آینده: مهاجرت به لاراول برای migration, queue (جاب‌ها مثل SMS/sync), events (سینک داده).

2. ساختار دیتابیس و جداول کلیدی

پروژه دو دیتابیس اصلی دارد: escapezo_ez9920 (وردپرس) برای محتوا/سفارشات، و escapezo_queries برای جستجو/رزرو سریع. وابستگی‌ها بیشتر id-based (product_id, order_id) برای سینک.

2.1. جداول کلیدی در escapezo_ez9920 (وردپرس)

wp_posts: هسته سیستم (post_type = 'product' برای بازی‌ها, 'shop_order' برای سفارشات). وابسته به wp_postmeta برای جزئیات.

wp_postmeta: متادیتاها (_order_total, *booking*time, *product*meta). وابستگی به wp_posts (post_id). تحلیل نشان می‌ده 28+ متادیتا (استاندارد ووکامرس + سفارشی مثل order_ticket_tedad).

wp_markting: سینک سفارشات (29 فیلد مثل order_id, user_phone, order_status). برای گزارش‌گیری بعد پاکسازی قدیمی‌ها. فیلدهای جدید مثل order_online_paid اضافه شده.

wp_products_search: ایندکس جستجو (product_id, title, city json, hood, genres json). برای سرچ سریع، با سینک از products_data.

سایر: wp_users/usermeta برای کاربران (phone, role), wp_options برای تنظیمات (suggested/promotional).

2.2. جداول کلیدی در escapezo_queries

products_data: جزئیات محصول (product_id, title, schedule سریالایز, price, tags_title json, hood, city_name). برای جستجو/سانس. وابستگی به wp_posts (product_id).

wp_zb_booking_history: exceptions رزرو (product_id, booking_time, status). برای چک موجودی سانس.

products_order: ordering (recent/topsale/hottest با json product_ids).

product_views: ویوها (product_id, count, date). برای busyness_score.

2.3. وابستگی‌های جداول

محصول → سفارش: product_id در orders/session_exceptions. (برای رزرو سانس خاص)

محصول → شهر/منطقه/محله: string در products_data (denormalize). در پیشنهادی، id به جداول normalize.

محصول → ژانر/برچسب: json/tags_id در products_data. وابستگی به ez_genres (pivot).

سفارش → کاربر/محصول: user_id/product_id در orders.

سانس → محصول: dynamic از schedule json در meta, exceptions در session_exceptions (date/start_time).

سینک: wp_posts → products_data (via cron in saeed-codes.php). wp_markting برای سفارشات قدیمی.

وابستگی خارجی: وب‌سرویس‌ها به helper-functions.php (get_day_type, get_sanses).

حجم: products_data سنگین (json schedule بزرگ). پاکسازی سفارشات قدیمی (اسکریپت delete-old-orders) حجم wp_posts/postmeta رو کم می‌کنه.

3. سیستم جستجو

ساختار (از PDF و گزارش wp_products_[search.md](http://search.md)): جستجو چندمرحله‌ای:

قدم 1: موارد قابل جستجو: نام بازی, ژانر (لینک‌دار), نام شهر (لینک‌دار), نام منطقه (لینک‌دار), نام محله (بدون لینک), نوع بازی (game_type).

قدم 2: وزن‌دهی اولویت: برچسب > نوع بازی > شهر > نام محصول > بازی‌های شهر > محله > برند.

قدم 3: نحوه نمایش: تک‌کلمه (مثل "تهران") → صفحات مادر (اتاق فرارهای تهران, سینما ترس‌های تهران). چندکلمه (مثل "اتاق فرار نارمک") → لیست بازی‌ها. اگر هیچی نبود, "نتیجه‌ای یافت نشد". مرتب‌سازی بر اساس:

پروداکت با رزرواسیون بالا

شلوغی پروداکت بالا (views/orders)

سطح ACF بالا

پروداکت معمولی

پروداکت با شلوغی پایین

سطح ACF پایین

زون اسکیپ: Arya scape

وابستگی: به wp_products_search (سینک از products_data). جستجو در فرانت با AJAX (queryable.php, games_search.php).

بهینه: full-text index پیشنهادی برای سرعت.

4. جریان‌های کلیدی پروژه

چک‌اوت/پرداخت (از تحلیل_فرآیند_چک_اوت): ورود به checkout → پر فرم → پردازش (چک تداخل سانس) → پرداخت (زرین‌پال/زیبال) → بازگشت به thankyou (به‌روزرسانی status, رزرو سانس, SMS). وابستگی به woocommerce_order_status_changed, update_markting_table.

رزرو/سانس (از [reservation.md](http://reservation.md), sans_[management.md](http://management.md)): dynamic سانس از schedule (normals/holidays). exceptions در booking_history. پنل تیم: sans_management.php برای باز/بسته/جابجایی.

وب‌سرویس‌ها (از [web-service.md](http://web-service.md), [reservation.md](http://reservation.md)): API برای جستجو (format_products_to_html_query), پیشنهاد (game-suggested), سانس (get_sanses, close_sans).

پاکسازی (از REPORT_OLD_ORDERS_[USAGE.md](http://USAGE.md), delete-old-orders): حذف سفارشات قدیمی از wp_posts/postmeta, سینک به wp_markting برای گزارش.

توابع سفارشی (از [saeed-codes.md](http://saeed-codes.md), [ahmadreza.md](http://ahmadreza.md)): دیباگ, SMS, JWT auth, metaboxes, shortcodes برای نمایش (sliders, home pages).

5. مسائل و بهینه‌سازی‌ها

مسائل: سریالایز داده (schedule, tags) برای query سخت. تکرار (wp vs queries). وابستگی به global/session در توابع. جستجو بدون normalize کند.

بهینه‌سازی‌های انجام‌شده: پاکسازی سفارشات, فیلدهای جدید wp_markting, سینک cron. پیشنهادی: normalize جداول (ez_products با full-text), queue برای SMS/sync.

وابستگی‌های فنی: ووکامرس برای سفارشات, ACF برای فیلدهای دسته‌بندی, Yoast برای SEO.

6. نتیجه‌گیری و پیشنهادات

EscapeZoom یک سیستم成熟 با تمرکز روی جستجو/رزرو، اما نیاز به مدرن‌سازی دارد. وابستگی‌ها id-based برای سینک آسان. برای مهاجرت به لاراول، normalize داده‌ها (جداول پیشنهادی ez_*) و استفاده از Eloquent برای query/search. این گزارش پایه خوبی برای Gemini است تا تحلیل عمیق‌تر (مثل ER diagram یا migration plan) بدهد. اگر نیاز به جزئیات بیشتر داری, بگو.