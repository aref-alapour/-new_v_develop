# جدول `products_data` (دیتابیس escapezo_queries) در مقابل `wp_products_search`

## نکته مهم
نام جدول **`products_data`** است (با s)، نه `product_data`.  
این جدول داخل دیتابیس **escapezo_queries** قرار دارد و توسط **web-service** (و در برخی جاها توسط تم با اتصال به همین دیتابیس یا دیتابیس وردپرس) استفاده می‌شود.

---

## ۱. ساختار جدول `products_data` (escapezo_queries)

ستون‌های جدول بر اساس `INSERT` در `web-service/web-service.php`:

| ستون | نوع داده (تقریبی) | توضیح |
|------|-------------------|--------|
| `ID` | auto | احتمالاً کلید اولیه (در INSERT ذکر نشده؛ ممکن است AUTO_INCREMENT باشد) |
| `product_id` | varchar/int | شناسه محصول (پست ووکامرس) |
| `product_type` | varchar | نوع محصول (مثلاً اتاق فرار، سینما ترس) |
| `title` | varchar | عنوان محصول |
| `price` | int/varchar | قیمت |
| `notable` | varchar | قابل توجه (۰/۱) |
| `special` | varchar | ویژه/تبلیغاتی (۰/۱) |
| `active` | varchar | وضعیت: active, updated, deactivated, soon, expired, temp |
| `monopoly` | varchar | انحصاری |
| `brand_id` | int | شناسه برند |
| `discount_data` | blob (serialized) | داده تخفیف (شیء سریال‌شده) |
| `instant_off` | blob (serialized) | تخفیف فوری |
| `geo` | varchar | مختصات (مثلاً "lat,lng") |
| `image` | varchar | مسیر نسبی تصویر (مثلاً 2021/04/fall-300-370.jpg) |
| `age_limit` | int | حداقل سن |
| `level` | int | سطح سختی |
| `schedule` | blob (serialized) | برنامه سانس‌ها (normals, holidays و زمان/قیمت هر سانس) |
| `duration` | int | مدت زمان (دقیقه) |
| `url` | varchar | اسلاگ URL محصول |
| `hood` | varchar | محله |
| `city_id` | varchar | شناسه شهر |
| `city_name` | varchar | نام شهر |
| `tags_id` | blob (serialized) | آرایه شناسه تگ‌ها |
| `tags_title` | blob (serialized) | آرایه عنوان تگ‌ها (برخی با پیشوند \|\|\|\|\| برای ژانر) |
| `count_min` | int | حداقل تعداد نفر |
| `count_max` | int | حداکثر تعداد نفر |
| `pish_person` | varchar/int | پیش‌پرداخت به ازای هر نفر |
| `auto_disable` | int | دقیقه قبل از سانس برای غیرفعال شدن سانس |
| `contact_info` | blob (serialized) | owner_phone, chat_id, manager_chat_id, manager_phone |
| `owner_id` | varchar | شناسه مالک (کاربر وردپرس) |
| `manager_id` | varchar | شناسه مدیر سانس |
| `comments_count` | int | تعداد نظرات |
| `rate` | decimal | امتیاز |

---

## ۲. کجا و به چه عنوان از `products_data` خوانده/نوشته می‌شود؟

### ۲.۱ نوشتن (Write)

| منبع | فایل | عملیات | ستون‌های درگیر |
|------|------|--------|-----------------|
| سینک فعال | `web-service/web-service.php` | `data_products_set`: DELETE سپس INSERT همه محصولات فعال | همه ستون‌ها |
| سینک غیرفعال | `web-service/web-service.php` | `data_products_set_nactive`: DELETE سپس INSERT محصولات غیرفعال | همه ستون‌ها |
| به‌روزرسانی برنامه | `web-service/web-service.php` | `schedule_products_set`: UPDATE | `schedule` |
| یک سانس رزرو/حذف | `web-service/web-service.php` | `single_schedule_products_set`: SELECT سپس UPDATE | `schedule` |
| تخفیف | `web-service/web-service.php` | `update_product_discount_data`: UPDATE | `discount_data` |
| سانس + پیش‌پرداخت | `web-service/saeed.php` | بعد از lock/رزرو: UPDATE | `schedule`, `pish_person` |
| سانس + پیش‌پرداخت | `web-service/reservation.php` | مشابه saeed: UPDATE | `schedule`, `pish_person` |

داده اولیه از وردپرس (از طریق `ez_webservice( array('type' => 'data_products_set', 'data' => $product_data) )`) از `inc/saeed-codes.php` (توابع `ez_queryable_set_products_data` و `ez_queryable_set_products_data_nactive`) ارسال و در `products_data` ذخیره می‌شود.

### ۲.۲ خواندن (Read) – به تفکیک فایل و فیلد

#### web-service/web-service.php
- **sort_products_get**:  
  - فیلتر محصولات با `active`, `product_id`, `exclude_products`  
  - خواندن همه ردیف‌ها؛ استفاده از: `product_id`, `active`, `special`, `tags_title`, `tags_id`, `schedule`, `discount_data`, و در تابع خروجی: `product_type`, `title`, `price`, `special`, `image`, `age_limit`, `level`, `duration`, `url`, `city_id`, `city_name`, `hood`, `geo`, `genres`/`tags`, `count_min`, `count_max`, `comments_count`, `rate`, `active`
- **products_get_by_id**:  
  - `SELECT * FROM products_data WHERE product_id IN (...)`  
  - استفاده: همه فیلدها برای فرمت خروجی (مثلاً HTML)
- **update_product_discount_data**: فقط UPDATE روی `discount_data`
- **single_schedule_products_set**: خواندن `schedule` برای یک محصول، سپس به‌روزرسانی
- تبلیغات (فقط برای hottest): `SELECT product_id FROM products_data WHERE active LIKE 'active' AND special LIKE 1`

#### web-service/queryable.php
- **get_search_result_func**:  
  - `SELECT * FROM products_data`  
  - استفاده: `product_id`, `product_type`, `url`, `title`, `city_name`, `image` (برای جستجوی متنی و خروجی API جستجو)

#### web-service/helper-functions.php
- **get_sanses**:  
  - `SELECT * FROM products_data WHERE product_id = ?`  
  - استفاده: فقط `schedule` (unserialize و برگرداندن سانس‌ها)

#### web-service/comments-order.php
- **product_id, duration**:  
  - `SELECT product_id, duration FROM products_data WHERE product_id IN (...)`  
  - برای محاسبه بازه زمانی سفارشات و ارتباط با کامنت

#### web-service/saeed.php
- چندین جا `SELECT * FROM products_data WHERE product_id = ?`:
  - **discount_data**: محاسبه تخفیف برای رزرو/نمایش
  - **owner_id / manager_id**: بررسی دسترسی (compiler vs sans_manager)
  - **schedule**: نمایش سانس‌ها و رزرو
  - **contact_info**: owner_phone, chat_id, manager_phone, manager_chat_id
  - **title, count_min, count_max, pish_person**: نمایش صفحه رزرو/پرداخت
  - **auto_disable**: محاسبه زمان غیرفعال شدن سانس

#### web-service/reservation.php
- همان الگوی saeed: `SELECT * FROM products_data` برای یک محصول و استفاده از:  
  `schedule`, `discount_data`, `owner_id`, `manager_id`, `contact_info`, `title`, `count_min`, `count_max`, `pish_person`, `auto_disable` و به‌روزرسانی `schedule` و `pish_person`.

#### web-service/team/sans_management.php
- `SELECT * FROM products_data` برای یک یا چند محصول:
  - **auto_disable**, **duration**: محاسبه زمان و نمایش
  - **product_id**, **title**, و بقیه فیلدها برای لیست/مدیریت سانس

#### web-service/game-promotional.php و web-service/game-suggested.php
- `SELECT product_id FROM products_data` با فیلتر `product_id IN (...)` و `tags_id[~]` برای پیشنهاد/تبلیغ بر اساس تگ.

#### تم (با ez_db() → دیتابیس وردپرس escapezo_ez9920)
**توجه:** `ez_db()` در mu-plugin escapezoom-core به دیتابیس **escapezo_ez9920** (وردپرس) وصل می‌شود. اگر در تم از `products_data` با ez_db() خوانده می‌شود، یا این جدول در همان DB وردپرس هم وجود دارد (کپی/سینک) یا باید اتصال جدا به escapezo_queries برای این کوئری‌ها استفاده شود.
- **template/options/ads-landing.php**:  
  - جستجو در بازی‌ها برای صفحه تبلیغات  
  - فیلدهای خوانده‌شده: `product_id`, `title`, `product_type`, `image`, `city_name`, `hood`
- **template/team/ajax/callbacks/game_search.php** (نسخهٔ قدیمی که هنوز ممکن است جایی صدا زده شود):  
  - جستجوی سریع بازی: `product_id`, `title`, `product_type`, `image`
- **template/team/pages/marketing_report.php**:  
  - لیست بازی‌ها برای گزارش بازاریابی: فقط `product_id`, `title`

---

## ۳. ساختار جدول `wp_products_search`

این جدول در دیتابیس **وردپرس (escapezo_ez9920)** با اتصال `ez_db()` پر و خوانده می‌شود.

| ستون | توضیح |
|------|--------|
| `product_id` | شناسه محصول |
| `product_type` | نوع محصول (از دسته‌بندی) |
| `product_name` | عنوان محصول |
| `product_status` | وضعیت فروش: active, updated, ... (معادل active در products_data) |
| `product_url` | URL نسبی |
| `product_image_url` | URL کامل تصویر |
| `product_brand` | JSON برند (id, name, slug, image) |
| `product_hood` | محله |
| `product_city` | JSON شهر (id, name, slug) |
| `product_area` | JSON منطقه (title, url) |
| `product_tags` | JSON آرایه تگ‌ها (title, url) |

پر شدن از طریق:
- **template/func/auto-sync-products.php**: سینک خودکار روی save/ویرایش/حذف محصول ووکامرس
- **page-aref-test.php**: مایگریشن دستی از محصولات وردپرس

خواندن از طریق:
- **template/team/ajax/callbacks/games_search.php**: جستجوی بازی برای پنل (فقط `product_id`, `product_name` و بعداً owner/sans_manager از postmeta)
- **template/func/main-search-ajax.php**: جستجوی اصلی سایت (کش از همه ردیف‌ها؛ استفاده از product_type, product_city, product_area, product_hood, product_name)
- **template/options/promotional-games.php**: لیست بازی‌ها برای صفحه تبلیغاتی بر اساس نوع و شهر با `product_id`, `product_name`, `product_city`

---

## ۴. چه چیزهایی در `products_data` هست که در `wp_products_search` نیست؟

این فیلدها فقط در `products_data` هستند و برای حذف/ادغام جدول باید منبع جایگزین یا منطق جدید داشته باشند:

| فیلد/داده | کاربرد فعلی | منبع جایگزین پیشنهادی |
|------------|-------------|-------------------------|
| **schedule** | برنامه سانس‌ها (normals/holidays)، رزرو، قفل سانس، نمایش زمان خالی | جدول/متا جدا یا سرویس جدا؛ نمی‌توان فقط از وردپرس کشید |
| **discount_data** | تخفیف ویژه (تاریخ، درصد)، به‌روزرسانی از API | post_meta یا گزینه در وردپرس |
| **duration** | مدت بازی (دقیقه)، comments-order و محاسبه بازه رزرو | post_meta یا فیلد محصول |
| **owner_id** | تشخیص مالک (compiler) برای دسترسی | الان در تم از `wp_postmeta` با کلید `user_ebtal` خوانده می‌شود |
| **manager_id** | تشخیص مدیر سانس برای دسترسی | الان در تم از `wp_postmeta` با کلید `sans_manager` خوانده می‌شود |
| **contact_info** | owner_phone, chat_id, manager_phone, manager_chat_id | post_meta یا فیلدهای جدا |
| **pish_person** | پیش‌پرداخت به ازای هر نفر | post_meta |
| **auto_disable** | دقیقه قبل از سانس برای غیرفعال شدن | post_meta |
| **price** | قیمت پایه | ووکامرس (قیمت محصول/ویریشن) |
| **active** (و مقادیر دیگر وضعیت) | فیلتر لیست و سورت | معادل: product_status در wp_products_search + post_meta در وردپرس |
| **special** | تبلیغ/پین شده | post_meta یا taxonomy |
| **geo** | نقشه | post_meta |
| **age_limit**, **level** | حد سن، سختی | post_meta |
| **count_min**, **count_max** | حداقل/حداکثر نفر | post_meta |
| **notable**, **monopoly**, **brand_id** | متادیتای دیگر | post_meta / taxonomy |
| **instant_off** | تخفیف فوری | post_meta |
| **comments_count**, **rate** | نظرات و امتیاز | محاسبه از وردپرس یا جداول کامنت |
| **tags_id** / **tags_title** (ژانر با \|\|\|\|\|) | جستجو و فیلتر در API، game-promotional / game-suggested | معادل در وردپرس: product_tag؛ در wp_products_search به صورت JSON در product_tags |

خلاصه:  
- **جستجو و لیست ساده** (عنوان، نوع، شهر، تصویر، محله، تگ، برند) در `wp_products_search` پوشش داده می‌شود.  
- **رزرو، سانس، قیمت سانس، تخفیف، مالک/مدیر، تماس، پیش‌پرداخت، duration، auto_disable** وابسته به `products_data` یا باید در وردپرس (post_meta + ساختار فعلی رزرو) نگه داشته شوند یا در یک سرویس/جدول جدا مدیریت شوند.

---

## ۵. خلاصه استفاده به تفکیک فایل

| فایل | جدول | عملیات | فیلدهای کلیدی |
|------|--------|--------|-----------------|
| web-service/web-service.php | products_data | سینک کامل، به‌روز schedule/discount، sort، products_get_by_id | همه فیلدها |
| web-service/queryable.php | products_data | جستجوی متنی | product_id, type, url, title, city_name, image |
| web-service/helper-functions.php | products_data | get_sanses | schedule |
| web-service/comments-order.php | products_data | لیست سفارش/کامنت | product_id, duration |
| web-service/saeed.php | products_data | رزرو، دسترسی، نمایش | schedule, discount_data, owner_id, manager_id, contact_info, title, count_min/max, pish_person, auto_disable |
| web-service/reservation.php | products_data | مشابه saeed | همان‌ها |
| web-service/team/sans_management.php | products_data | مدیریت سانس | auto_disable, duration, title, ... |
| web-service/game-promotional.php | products_data | پیشنهاد تبلیغاتی | product_id, tags_id |
| web-service/game-suggested.php | products_data | پیشنهاد بازی | product_id, tags_id |
| template/options/ads-landing.php | products_data | جستجوی بازی تبلیغات | product_id, title, product_type, image, city_name, hood |
| template/team/ajax/callbacks/game_search.php | (قدیمی: products_data) | جستجوی بازی پنل | الان از wp_products_search استفاده می‌شود |
| template/team/pages/marketing_report.php | products_data | لیست بازی‌ها گزارش | product_id, title |
| template/func/main-search-ajax.php | wp_products_search | جستجوی اصلی سایت | همه ستون‌ها (کش) |
| template/team/ajax/callbacks/games_search.php | wp_products_search | جستجوی بازی پنل | product_id, product_name |
| template/options/promotional-games.php | wp_products_search | صفحه تبلیغاتی بازی‌ها | product_id, product_name, product_city |

---

## ۶. برای ترکیب/حذف `products_data` چه کار می‌شود کرد؟

### گزینه الف: حذف کامل و انتقال همه‌چیز به وردپرس
- **غیرعملی** برای داده‌های زندهٔ رزرو و سانس: `schedule` و به‌روزرسانی لحظه‌ای آن از API در وردپرس به این شکل فعلی پیاده‌سازی نشده و ساختار رزرو (مثلاً wp_zb_booking_history) وابسته به همین داده‌هاست.

### گزینه ب: نگه داشتن فقط «داده‌های وابسته به رزرو» در escapezo_queries
- یک جدول باریک‌تر فقط برای چیزهایی که واقعاً باید در دیتابیس queries باشند:
  - **schedule** (برنامه سانس‌ها و به‌روزرسانی لحظه‌ای)
  - **discount_data** (در صورت تمایل نگه‌داری در همین DB)
  - **duration**, **auto_disable**, **pish_person** (یا فقط ارجاع به product_id و خواندن از متا وردپرس)
- بقیه (جستجو، لیست، فیلتر نوع/شهر/تگ) از **wp_products_search** + وردپرس باشد.

### گزینه ج: دو منبع موازی تا زمان مهاجرت کامل
- برای **جستجو و لیست** (صفحه اصلی، پنل، تبلیغات، گزارش بازاریابی): فقط از **wp_products_search** استفاده شود و تمام فایل‌های تم و web-service که فقط برای لیست/جستجو از products_data می‌خوانند به wp_products_search (یا API وردپرس) منتقل شوند.
- برای **رزرو، سانس، دسترسی مالک/مدیر، تخفیف، تماس**: همچنان از جدول/سرویس فعلی (products_data یا جدول باریک‌شدهٔ پیشنهادی) در escapezo_queries استفاده شود؛ با این شرط که دادهٔ اولیهٔ محصول (title, city, image, …) از وردپرس یا wp_products_search گرفته شود تا تک‌منبعی شود.

### گزینه د: یک جدول واحد در وردپرس به‌جای products_data (برای داده‌های غیر رزرو)
- گسترش **wp_products_search** (یا یک جدول custom در دیتابیس وردپرس) با ستون‌های اضافه برای: price، duration، discount_data، schedule (یا لینک به جدول schedule)، owner_id، manager_id، contact_info، pish_person، auto_disable و غیره.
- پر کردن این جدول از وردپرس (متا، taxonomy، ووکامرس) و یک job برای به‌روزرسانی schedule/discount از API به این جدول یا به یک جدول جدا فقط برای «برنامه و رزرو».

پیشنهاد عملی برای قدم بعدی:
1. **فاز ۱**: تمام خوانش‌های «فقط لیست/جستجو» از products_data (مثل queryable، ads-landing، marketing_report، game_search قدیمی) را به **wp_products_search** یا API وردپرس منتقل کن.
2. **فاز ۲**: در web-service فقط برای رزرو، سانس، دسترسی و تخفیف از products_data (یا جدول باریک‌شده) استفاده کن و بقیه فیلدهای نمایشی را از وردپرس/wp_products_search بگیر.
3. **فاز ۳**: در صورت تمایل، جدول products_data را به یک یا دو جدول کوچک‌تر (مثلاً product_schedule + product_meta_extra) تقسیم و مابقی را از وردپرس بخوان.

اگر بگی کدام گزینه (ب، ج یا د) مدنظرت است، می‌توانیم قدم‌به‌قدم برای همان طرح، لیست تغییرات فایل‌به‌فایل را بنویسیم.
