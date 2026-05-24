# Cron و سینک لیست محصولات

همهٔ این توابع فقط وقتی `HTTP_HOST == 'escapezoom.ir'` است در کرون ثبت می‌شوند؛ نسخه‌های `*_2` فقط داخل بلاک `if (isset($_GET['get_list_of_all_products']))` با add_action به تابع اصلی وصل می‌شوند و خودشان جایگزین تابع دیگری نیستند — **قابل حذف و جایگزینی با فراخوانی مستقیم تابع اصلی.**

## ez_queryable_set_hottest_products

- **جایگزین:** تابع اصلی است؛ `ez_queryable_set_hottest_products2` فقط wrapper است.
- **کارایی:** جدول `hottest_products` را می‌خواند، رکوردهای قدیمی‌تر از ۹۰ روز را پاک می‌کند، با ez_webservice بازدید ۳۰ روز را می‌گیرد، برای هر محصول امتیاز بیز و نرمال‌سازی بازدید را محاسبه و با get_bayesian_score ترکیب می‌کند، لیست penalty را اعمال می‌کند، سپس با ez_webservice نوع `hottest_products_set` به web-service می‌فرستد.
- **بهینه‌سازی:** (۱) saeed_print را در production غیرفعال یا حذف کن. (۲) ثابت‌های C، m، max_views و لیست penalty را به بالا یا option منتقل کن. (۳) در صورت تعداد زیاد محصول، پردازش را به batch تقسیم کن یا از صف (queue) استفاده کن.

## ez_queryable_set_popular_products

- **جایگزین:** تابع اصلی است.
- **کارایی:** با WP_Query محصولات active را می‌گیرد، برای هر کدام comments_count_new و product_rates را خوانده و امتیاز popular محاسبه می‌کند، penalty اعمال می‌کند، سپس با ez_webservice نوع `popular_products_set` می‌فرستد.
- **بهینه‌سازی:** (۱) لیست penalty و فرمول امتیاز را متمرکز کن. (۲) در صورت تعداد زیاد، pagination یا batch در نظر بگیر.

## ez_queryable_set_topsale_products

- **جایگزین:** تابع اصلی است.
- **کارایی:** ابتدا update_held_sans_table_func را صدا می‌زند، جدول held_orders_list را می‌خواند، با power_map سطح را وزن می‌دهد، penalty اعمال می‌کند، سپس با ez_webservice نوع `topsale_products_set` می‌فرستد.
- **بهینه‌سازی:** power_map و لیست penalty را متمرکز کن؛ وابستگی به update_held_sans_table_func را صریح در docblock بنویس.

## ez_queryable_set_recent_products

- **جایگزین:** تابع اصلی است.
- **کارایی:** با WP_Query محصولات با product_state برابر active یا updated را می‌گیرد و فقط آرایهٔ IDها را با ez_webservice نوع `recent_products_set` می‌فرستد.
- **بهینه‌سازی:** ساده است؛ در صورت رشد زیاد محصولات، محدودیت تعداد یا مرتب‌سازی بر اساس تاریخ در نظر بگیر.

## ez_queryable_set_products_data / ez_queryable_set_products_data_nactive

- **جایگزین:** توابع اصلی هستند؛ نسخه‌های `*_2` فقط wrapper.
- **کارایی:** با WP_Query محصولات (فعال یا غیرفعال) را می‌گیرند، برای هر محصول متا و taxonomy و تصویر و غیره را جمع می‌کنند و یک آرایهٔ آبجکت برای web-service می‌سازند، سپس با ez_webservice نوع `data_products_set` یا `data_products_set_nactive` می‌فرستند.
- **بهینه‌سازی:** (۱) ساخت آبجکت را در یک تابع کمکی جدا کن تا تست و نگهداری راحت‌تر شود. (۲) در صورت حجم بالا، ارسال را batch کن. (۳) وابستگی به فیلدهای ACF/متا را در یک نقشه (map) متمرکز کن.

## ez_queryable_set_marketing_data

- **جایگزین:** تابع اصلی است.
- **کارایی:** روی init (فقط escapezoom.ir) ثبت شده؛ دادهٔ مارکتینگ را جمع کرده و به web-service می‌فرستد.
- **بهینه‌سازی:** وابستگی به optionها و ساختار خروجی را مستند کن؛ در صورت سنگین بودن، به کرون منتقل کن.

## update_comments_stars

- **جایگزین:** تابع اصلی است.
- **کارایی:** با کرون اجرا می‌شود؛ امتیاز/ستارهٔ نظرات را از دیتابیس می‌خواند و به‌روزرسانی می‌کند (و احتمالاً به web-service هم sync می‌کند).
- **بهینه‌سازی:** محدودهٔ تاریخ و دسته‌بندی محصولات را قابل تنظیم کن؛ در صورت حجم زیاد، batch کن.

## wp_zb_booking_history_today_optimize

- **جایگزین:** تابع اصلی است.
- **کارایی:** با کرون روزانه اجرا می‌شود؛ جدول/دادهٔ رزرو روز را بهینه یا جابه‌جا می‌کند و رکوردهای قدیمی را پاک می‌کند.
- **بهینه‌سازی:** کوئری‌ها را به‌صورت batch و با limit اجرا کن تا قفل جدول طولانی نشود.

## ez_satisfaction_on_comments

- **جایگزین:** تابع اصلی است.
- **کارایی:** با کرون اجرا می‌شود؛ رضایت از نظرات را محاسبه و sync می‌کند.
- **بهینه‌سازی:** محدودهٔ زمانی و batch size را قابل تنظیم کن.
