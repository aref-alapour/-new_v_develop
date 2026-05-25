## خلاصه‌ی کلی `web-service.php`

این فایل، **وب‌سرویس مرکزی مدیریت محصولات/بازی‌ها** در EscapeZoom است و بیشتر روی **داده‌های ثابت و لیست‌های نمایش** تمرکز دارد، نه روی رزرو سانس (که در `reservation.php` و `saeed.php` بود). مهم‌ترین نقش‌های این فایل:

- دریافت و ذخیره‌سازی **داده‌ی کامل محصولات** (`products_data`) و انواع لیست‌های مرتب‌سازی (محبوب‌ترین، پرفروش‌ترین، ترند، داغ‌ترین، نوروزی و ... در جدول `products_order`).
- مدیریت **تقویم کاری** (`calendar_data`) که در توابع `get_day_type`/`get_day_type2` استفاده می‌شود.
- پیاده‌سازی یک API بسیار قدرتمند برای **فراخوانی لیست محصولات با انواع فیلترها و چینش‌ها** (`sort_products_get`) که تمام صفحه‌های Home، لیست شهر/ژانر/نوع و نقشه (map search) از آن استفاده می‌کنند.
- فرمت کردن داده‌ی محصولات برای خروجی **API** یا **HTML** (کارت‌های اسلایدر، لیست‌ها) با در نظر گرفتن تخفیف‌ها، سانس‌های خالی امروز، برچسب «به‌زودی»، «اکسپایر شده»، تبلیغ (`ads`) و غیره.
- APIهایی برای:
  - دریافت محصولات بر اساس لیست `product_id`‌ها (`get_by_products_id`),
  - ثبت و محدود کردن **بازدید صفحات بازی** (`post_view_process`),
  - ثبت **ورودی‌های CPC/utm** (`cpc_tracking`),
  - تولید HTML لیست روزها در صفحه PLP (`load_plp_days`),
  - آپدیت تکی `discount_data`.

تمام endpointها زیر یک `type` روی `POST` قرار دارند و بر اساس مقدار `data->type` فعال می‌شوند.

---

## سربرگ، امنیت و ورودی

- CORS برای همه‌ی originها باز است و متدها/هدرهای لازم برای AJAX تنظیم شده‌اند.
- فقط روی چند دامنه‌ی مجاز (`escapezoom.ir`, `escapezoom.co`, `bak.escapezoom.ir`, `dev-api.escapezoom.ir`, `goriza.ir`, `dev.escapezoom.local`) پاسخ می‌دهد؛ مابقی در `hackers` لاگ و با پیام `Get outta here` بسته می‌شوند.
- فقط متد **POST**:
  - `application/json` → بدنه‌ی JSON → `$data`.
  - `application/x-www-form-urlencoded` → `$_POST` → آبجکت → `$data`.
  - سایر `CONTENT_TYPE`ها → `415 Unsupported Media Type`.
  - غیر POST → `405 Invalid Request Method`.
- `home_url` بر اساس `HTTP_HOST` تنظیم می‌شود (لوکال/https).

ساختار عمومی ورودی:

```json
{
  "type": "<scenario>",
  "data": { ... }
}
```

در ادامه، سناریوهای اصلی را دسته‌بندی می‌کنیم.

---

## ۱. ست‌کردن لیست‌های مرتب‌سازی محصولات (`products_order`)

این endpointها، اطلاعاتی که از جای دیگر (مثلاً کران وردپرس، یا داشبورد مدیریتی) محاسبه شده را داخل جدول `products_order` ذخیره می‌کنند.

- **`popular_products_set`**:
  - ورودی: `data` شامل آبجکت‌هایی به شکل `{ product_id => { comments_count, rate } }`.
  - برای هر `product_id`:
    - از جدول `product_views`، `views` و آرایه‌ی `views30` را می‌گیرد.
    - `views30_sum` را به‌عنوان مجموع بازدید ۳۰ روز اخیر حساب می‌کند.
    - یک امتیاز محبوبیت محاسبه می‌کند:

    \[
    \text{score} \approx (\text{comments_count} * \text{rate}) + \frac{\text{views} * \text{views30\_sum}}{925000}
    \]

  - محصولات را بر اساس این امتیاز sort می‌کند و لیست `product_id`های مرتب‌شده را به‌صورت `popular` در `products_order` ذخیره/آپدیت می‌کند.

- **`topsale_products_set`**, **`recent_products_set`**, **`trend_products_set`**, **`nuwruz_products_set`**:
  - ورودی: `data` یک آرایه/لیست از `product_id`هاست که قبلاً بیرون محاسبه شده.
  - همان لیست را در ستون‌های مناسب `topsale`, `recent`, `trend`, `nuwruz` جدول `products_order` ذخیره/آپدیت می‌کنند.

- **`hottest_products_set`**:
  - ورودی: `data` یک لیست `product_id`های «داغ‌ترین» محصولات است.
  - از `products_order` لیست `recent` را (اگر وجود داشته باشد) می‌گیرد.
  - `hottest_products_ids` = لیست داغ‌ها.
  - یک `lookup` از آن می‌سازد و سپس هر محصول `recent` که در `hottest` نبوده را به انتهای لیست اضافه می‌کند:
    - نتیجه: `final_products = hottest + recent_not_in_hottest`.
  - `final_products` را در ستون `hottest` ذخیره می‌کند.

این داده‌ها توسط `sort_products_get` برای ساخت لیست‌ها استفاده می‌شوند.

---

## ۲. تقویم: `ez_calendar`

**هدف**: نگهداری داده‌ی تقویم (روزهای تعطیل/بسته/عادی) در جدول `calendar_data` برای استفاده در توابع `get_day_type` و `get_day_type2`.

- ورودی: `data` یک ساختار سریال‌پذیر (معمولاً object شامل `holidays`, `closed_days` و ...) است.
- اگر رکوردی در `calendar_data` وجود داشته باشد:
  - `UPDATE calendar_data SET data = serialize(calendar_data)`.
- در غیر این صورت:
  - `INSERT INTO calendar_data (data) VALUES (serialize(calendar_data))`.

توابع `get_day_type*` در `helper-functions.php` این `data` را `unserialize` کرده و از آن برای تشخیص نوع روز سانس استفاده می‌کنند.

---

## ۳. وارد کردن و به‌روزرسانی داده‌ی محصولات (`products_data`)

### ۳.۱. `data_products_set`

**هدف**: پاک‌کردن و دوباره‌سازی رکوردهای محصولات با `active = 'active'` یا `'updated'`.

- ابتدا:

```sql
DELETE FROM products_data WHERE active = 'active' OR active = 'updated';
```

- سپس `data->data` (لیستی از objectهای محصول) را به JSON-→object تبدیل می‌کند.
- برای هر محصول:
  - یک `INSERT` بزرگ در `products_data` می‌زند که ستون‌هایی مانند:
    - `product_id`, `product_type`, `title`, `price`, `notable`, `special`, `active`, `monopoly`, `brand_id`,
    - `discount_data` (سریال شده در صورت وجود),
    - `instant_off`,
    - `geo`, `image`,
    - `age_limit`, `level`,
    - `schedule` (سریال شده),
    - `duration`, `url`, `hood`, `city_id`, `city_name`,
    - `tags_id`, `tags_title` (سریال شده),
    - `count_min`, `count_max`, `pish_person`, `auto_disable`,
    - `contact_info` (سریال شده: شامل owner_phone, chat_id, manager_chat_id),
    - `owner_id`, `manager_id`, `comments_count`, `rate`
    را پر می‌کند.
  - مطمئن می‌شود در `product_views` رکوردی برای آن `product_id` وجود دارد (در صورت نبود، `INSERT` می‌کند).
- در پایان، اگر به هر دلیلی چند رکورد برای یک `product_id` درج شده باشد، با یک DELETE self-join، چندباره‌ها را پاک می‌کند و فقط رکورد با `ID` کمتر را نگه می‌دارد.

### ۳.۲. `data_products_set_nactive`

**مشابه بالا**، با این تفاوت که روی محصولات غیر فعال کار می‌کند:

- ابتدا:

```sql
DELETE FROM products_data WHERE (active != 'active' AND active != 'updated');
```

- سپس همان فرآیند `INSERT` را برای محصولات غیرفعال/سایر استیت‌ها انجام می‌دهد (با castهای قوی‌تر روی عددها).

### ۳.۳. `schedule_products_set` و `single_schedule_products_set`

- `schedule_products_set`:
  - ورودی: `data` لیستی از objectهای `{ id, schedule }`.
  - برای هر کدام:

```sql
UPDATE products_data SET schedule = serialize(product.schedule) WHERE product_id = product.id;
```

- `single_schedule_products_set`:
  - ورودی: آبجکت `reserved_booking` شامل `product_id`, `booking`, `state ('add'|'remove')`.
  - `schedule` فعلی را از `products_data` می‌خواند و `unserialize` می‌کند.
  - اگر `state == 'add'` → یک مقدار جدید به schedule اضافه می‌کند.
  - اگر `state == 'remove'` → اگر `booking` در schedule باشد، آن را `unset` می‌کند.
  - سپس `schedule` به‌روزشده را در DB ذخیره می‌کند.

---

## ۴. API قدرتمند لیست محصولات: `sort_products_get`

این مهم‌ترین و پیچیده‌ترین endpoint این فایل است و ستون فقرات صفحه‌های:

- خانه (اسلایدرها و کوئیک سرچ)،
- صفحات city / type / genre / hood،
- map search،
- event/discount listing،
- و سانس‌یاب (`cat_sansyab`)

است.

### ۴.۱. ورودی‌ها

ساختار `data`:

```json
{
  "source": "home_trends" | "home_cities_escaperoom" | "map_search" | ...,
  "limit": 40,
  "sort_type": "hottest" | "recent" | "trend" | "popular" | ...,
  "page": 1,
  "is_mobile": 0|1,
  "only_events": bool,
  "event_type": "discount",
  "most_discount": bool,
  "only_ads": bool,
  "deactivate": bool,
  "exclude_ads": bool,
  "format": "html_swiper" | "html_list" | "api",
  "unpin_ads": bool,
  "badge_ads": bool,
  "random": bool,
  "random_memory": "1,2,3",
  "show_more": bool,
  "show_more_url": "..."
  "only_free_sanses": bool,
  "active_soon": bool,
  "url": "escapezoom.ir" | ...
  "params": {
    "brand_id": ...,
    "product_type": ...,
    "city_id": ... or [ids],
    "tag": ... or [tag_ids] (مثبت/منفی),
    "schedule": [from_ts, to_ts],
    "price": [min, max],
    "level": ...,
    "monopoly": ...,
    "age": ...,
    "duration": ...,
    "count": ...,
    "bounds": { sw: {lat,lng}, ne: {lat,lng} },
    "exclude_products": [...],
    "slug": ...,
    "page": ...,
    "sort_type": ...,
    "random_memory": ...,
    ...
  }
}
```

### ۴.۲. تفسیر `source` و تنظیم پیش‌فرض‌ها

بخش بزرگی از ابتدای این سناریو به این اختصاص دارد که اگر `source` مشخص باشد، بر اساس آن:

- `sort_type`, `limit`, `random`, `only_events`, `event_type`, `only_ads`, `badge_ads`, `unpin_ads`, `deactivate`, `only_free_sanses`, `format`, `active_soon`, و حتی بخشی از `params` (مثل `city_id`, `product_type`, `tag`) را تنظیم کند.

نمونه‌ها:

- `home_trends`:
  - `sort_type = 'trend'`, `random = true`, `limit = 40`.

- `home_quick_search`:
  - `sort_type = 'recent'`, `limit = 150`, `random = true`, `deactivate = true`.

- `home_cities_escaperoom` / `home_cities_cinema` / `home_cities_lasertag`:
  - `limit = 40`, `sort_type` از `params.sort_type` یا `hottest`,
  - `params.product_type` = نوع محصول خاص.

- `home_discounts_event`:
  - `only_events = true`, `event_type = 'discount'`, `sort_type = 'recent'`, `limit = 40`, `random = true` (مگر زمانی که `most_discounts` فعال باشد).

- `map_search`:
  - `sort_type = 'recent'`, `limit = 60`, `only_free_sanses = true`, `random = true`, `format = 'api'`.

- `genre_page`, `hood_page`, `type_page_cat_*`, `typecity_page_*`, `type_page_escaperoom_genre_*`, `type_page_discounts_event_*`، `city_page_product_*`, `city_page_discounts_event_*`:
  - بر اساس slug موجود در `source`، `params.city_id`, `params.product_type`, `params.tag` و ... را تنظیم می‌کند.

- `cat_sansyab`:
  - سانس‌یاب: `sort_type` بر اساس `params.sort_type`، `limit` تا ۲۰۰، `page` از `params.page`, `max_num_pages = true`.

به‌طور کلی: `source` یک meta-layer است برای کانفیگ سریع endpoint برای صفحه‌های مختلف سایت.

### ۴.۳. تعیین `home_url`

- اگر `args->url` ست شده باشد:
  - روی اساس `HTTP_HOST` و `url`، `home_url` را برای ساخت لینک‌ها تنظیم می‌کند (لوکال/http در مقابل https).

### ۴.۴. خواندن لیست مرجع از `products_order`

- اگر `sort_type == 'hottest'` و `!unpin_ads`:
  - لیست `only_ads_rows` را از `products_data` (محصولات با `special = 1`) می‌گیرد تا با لیست `hottest` merge شوند.

- سپس:

```php
$result = $conn->query("SELECT $sort_type FROM products_order");
$products_id = unserialize($row[$sort_type]); // لیست اصلی
$products_id = array_unique(array_merge($products_id, $only_ads_rows));
```

- اگر `source == 'suggested'`:
  - `products_id` را از `products_id[$args->params->slug]` می‌گیرد (ساختار خاص).

- اگر `random == true`:
  - `products_id` = `products_id - random_memory`, سپس `shuffle($products_id)`.

### ۴.۵. ساخت کوئری `products_data` و اضافه‌کردن city/genre/tag

- شرط فعال بودن محصول:
  - اگر `deactivate`:
    - همه‌ی stateها (`active`, `deactivated`, `soon`, `expired`, `temp`, `updated`) مجازند.
  - در غیر این صورت:
    - `active IN ('active', 'updated')` و اگر `sort_type == 'recent'` و `active_soon` → `active = 'soon'` هم اضافه می‌شود.

- اگر `params.exclude_products` تعریف شده باشد:
  - `product_id NOT IN (...)`.

- سپس:

```sql
SELECT * FROM products_data WHERE <شرط‌ها>
```

- خروجی را در دو map پر می‌کند:
  - `$products_data[product_id]` برای محصولات active/updated،
  - `$nactive_products_data[product_id]` برای سایر stateها.

- روی هر محصول:
  - `tags_title` و `tags_id` را `unserialize` می‌کند.
  - هر tag که نامش شامل `|||||` باشد، به عنوان `genre` (`genres[]` با title و id) در نظر گرفته می‌شود.
  - سایر tags در `tags[]` قرار می‌گیرند.

- سپس:
  - `sorted_product_list` = products_data به ترتیبی که در `products_id` آمده.
  - `products` = `sorted_product_list` + `nactive_products_data`.

### ۴.۶. فیلتر بر اساس `schedule` (سانس‌های خالی)

اگر `params.schedule` ست شده و بازه زمانی معتبر باشد:

- `schedule_initial_date` را از timestamp اولیه می‌سازد (تاریخ، نه ساعت).
- اگر این تاریخ امروز یا بعد باشد:
  - `day_type = get_day_type2(schedule_arg[0])`.
  - از `wp_zb_booking_history` تمامی رزروهای آینده (`booking_time >= now`) را می‌گیرد و بر اساس `room_id` در `room_booked[room_id][]` نگه می‌دارد.
  - برای هر محصول:
    - `sanses = schedule` آن آبجکت (با `get_day_type2`).
    - برای هر سانس، `firstTimeTs` را می‌سازد.
    - اگر `firstTimeTs` در `room_booked[product_id]` نباشد → سانس خالی است و در `products_schedule[product_id][]` ذخیره می‌شود.
- بعداً در فاز فیلتر `params.schedule`:
  - تنها محصولاتی نگه داشته می‌شوند که برای آن‌ها `firstTimeTs` در بازه `[from, to]` وجود داشته باشد.

### ۴.۷. پین‌کردن Ads و محصولات «به زودی»

- اگر `sort_type == 'hottest'` و `!unpin_ads`:
  - محصولات را به دو دسته‌ی `special` (تبلیغ) و `non_special` تقسیم و `special` را پس از `shuffle` در ابتدای لیست می‌گذارد.

- اگر `sort_type == 'recent'` و `active_soon`:
  - محصولات با `active='soon'` را جدا کرده، `shuffle` می‌کند و در ابتدای لیست می‌گذارد.

### ۴.۸. فیلترها بر اساس `params`

بعد از آماده شدن لیست `products`، انواع فیلترها روی آنها اعمال می‌شود:

- `brand_id`:
  - برابر بودن با مقدار داده‌شده.
- `product_type`:
  - برابر بودن با نوع محصول (مثلاً «اتاق فرار»، «سینما ترس» و ...).
- `city_id`:
  - اگر آرایه: OR روی city_idهای لیست.
  - اگر 0/false: حذف سه شهر خاص (فیلتر خاص برای استثنا).
- `tag`:
  - پشتیبانی از مجموعه‌ای از `tag_id` مثبت و منفی:
    - مثبت: محصول باید حداقل یکی از این تگ‌ها را داشته باشد.
    - منفی (مثلاً `-124`): محصول نباید آن تگ را داشته باشد (حذف).
  - مراقبت از حذف تکرار محصول در خروجی.
- `schedule`:
  - فقط محصولاتی که در `products_schedule[product_id]` حداقل یک سانس در بازه دارند.
- `price`:
  - بازه‌ی `[min,max]` روی فیلد `price`.
- `level`, `monopoly`:
  - برابر بودن با مقدار.
- `age`, `duration`:
  - قرار داشتن مقدار محصول در بازه‌ی `[param,param]` (در کد فعلی شرط `param <= value <= param` است؛ یعنی برابر).
- `count`:
  - `count_max >= param`.
- `bounds`:
  - استفاده از `is_point_within_bounds(product.geo, bounds)` برای فیلتر مکانی (مثلاً برای عملیات روی نقشه).

### ۴.۹. فیلترهای event/ads

- اگر `only_events == true` و `event_type == 'discount'`:
  - فقط محصولاتی نگه داشته می‌شوند که:
    - `discount_data` غیرخالی دارند،
    - و `special_discount_date` آنها در آینده است.
  - اگر `most_discounts == true`:
    - `event_arr` را بر اساس `special_discount_percentage` به‌صورت نزولی sort می‌کند.

- اگر `only_ads == true`:
  - فقط محصولاتی که `special` دارند نگه داشته شده و `shuffle` می‌شوند.

- اگر `exclude_ads == true`:
  - فقط محصولاتی که `special == 0` هستند نگه می‌مانند.

### ۴.۱۰. صفحه‌بندی و خروجی

- اگر `page` یا `limit` مشخص نباشد، از پیش‌فرض‌ها استفاده می‌کند.
- `max_num_pages = ceil(count(products)/limit)`.
- اگر `random == true`:
  - `products = array_slice(products, 0, limit)`.
- در غیر این صورت:
  - `products = array_slice(products, (page - 1) * limit, limit)`.
- `products_clone = products` برای ذخیره‌ی `product_id`ها زمانی که `random == true` (برای `random_memory` بعدی).
- سپس:

```php
$products = format_products_to_html_query(...);
```

- شیء خروجی:

```json
{
  "products": <لیست فرمت‌شده>,
  "max_num_pages": <اختیاری>,
  "products_id": [ids...]  // فقط در حالت random
}
```

---

## ۵. فرمت کردن محصولات: API و HTML

### ۵.۱. `format_products_to_html_query`

**نقش**: با توجه به `format`، محصولات را:

- یا برای API استاندارد می‌کند (`standardization_products`),
- یا برای HTML swiper (`standardization_products_html_swiper`),
- یا برای لیست HTML (`standardization_products_html_list`).

### ۵.۲. `standardization_products`

این تابع، objectهای خام `products_data` را به **ساختار API استاندارد** تبدیل می‌کند:

- ابتدا `product_id`های محصولات را استخراج و از `wp_zb_booking_history` رزروهای آینده را می‌گیرد تا `room_booked[room_id][]` بسازد.
- برای هر محصول:
  - `discount_data` را (اگر وجود داشته باشد) به صورت:
    - `event = { off_percentage, expire_date }`
    در خروجی قرار می‌دهد.
  - سانس‌های امروز را از `schedule` با `get_day_type2(time())` جمع می‌کند و `schedule_list` شامل سانس‌های خالی (نه رزرو شده و نه غیرفعال به‌خاطر `auto_disable`) می‌سازد.
  - `free_sanses = count(schedule_list)` (یا null).
  - اگر `only_free_sanses == true` و `free_sanses` نباشد → محصول skip می‌شود.
  - `city_name` را با حذف پیشوندهای دسته‌بندی مثل «اتاق فرار»، «لیزرتگ»، «سینما ترس»، ... تمیز می‌کند.
  - سپس object نهایی:

```json
{
  "product_id": <int>,
  "type": "<نوع محصول به انگلیسی/کلید>",
  "title": "...",
  "price": <int>,
  "ads": true|false,
  "image": "<home_url>/wp-content/uploads/<image>",
  "age": <int>,
  "level": <int>,  // با تبدیل 5 - level
  "duration": <int>,
  "url": "...",
  "city_id": <int>,
  "city_name": "... (تمیز شده)",
  "hood_name": "...",
  "genres": [...],
  "tags": [...],
  "number_min": <int>,
  "number_max": <int>,
  "event": { ... } | [],
  "comments_count": <int>,
  "rate": <float/string>,
  "free_sanses": <int|null>,
  "geo": "lat,lng",
  "active": "active|soon|expired|deactivated|temp|updated"
}
```

### ۵.۳. `standardization_products_html_swiper` و HTML List

این توابع:

- ابتدا محصولات را با `standardization_products` استاندارد می‌کنند،
- سپس برای هر محصول کارت HTML تولید می‌کنند:
  - تمایز بین stateهای مختلف:
    - `temp` / `deactivated`: کارت با overlay «غیرفعال».
    - `expired`: overlay «اکسپایر شده».
    - `soon`: badge «به زودی».
    - `active` / `updated`: کارت عادی با اطلاعات قیمت، امتیاز، آدرس، badge تخفیف/ads (بخشی کامنت شده).
  - لینک‌ها به `/room/{url}/` روی `home_url`.
  - استفاده از کلاس‌ها و ساختار Tailwind/utility CSS برای نسخه‌ی جدید UI.

خروجی `html_swiper` و `html_list`، بلوک HTML کامل قابل inject در فرانت‌اند است.

---

## ۶. `get_by_products_id`

**نقش**: گرفتن چند محصول مشخص (order شده بر اساس همان لیست ورودی) و فرمت‌کردن خروجی با همان سیستم `format_products_to_html_query`.

**ورودی**:

```json
{
  "type": "get_by_products_id",
  "data": {
    "products_id": [123, 456, ...],
    "format": "html_swiper" | "html_list" | "api"
  }
}
```

**منطق**:

- `SELECT * FROM products_data WHERE product_id IN (...) ORDER BY FIELD(product_id, ...)`.
- مانند جاهای دیگر، `tags` و `genres` را از `tags_title`/`tags_id` استخراج می‌کند.
- `format_products_to_html_query` را صدا می‌زند.
- خروجی را JSON می‌کند.

---

## ۷. شمارش بازدید: `post_view_process`

**هدف**: افزایش شمارنده‌ی `views` و آرایه‌ی `views30` در جدول `product_views` به‌ازای هر IP منحصربه‌فرد، با محدودیت ۲۴ ساعته.

**ورودی**:

- `product_id`, `ip`, `user_agent`.

**منطق**:

- اگر `user_agent` شامل نام یکی از botهای لیست سیاه (`Googlebot`, `TelegramBot`, `WhatsApp`, `AhrefsBot`, `SemrushBot`, ...) باشد → exit;.
- جدول `post_view_ip_checker` چک می‌کند:
  - اگر IP برای این `product_id` در ۲۴ ساعت اخیر ثبت نشده:
    - از `product_views` رکورد می‌گیرد،
    - `views` را +۱ می‌کند.
    - `views30` (سریال شده) را load کرده و index روز جاری را (بر اساس diff روزها نسبت به تاریخ ثابت 2023-08-13) +۱ می‌کند.
    - `product_views` را به‌روزرسانی می‌کند.
    - IP را در `post_view_ip_checker` با timestamp فعلی ثبت می‌کند.
- سپس رکوردهای قدیمی‌تر از ۲۴ ساعت در `post_view_ip_checker` پاک می‌شوند.

---

## ۸. ردیابی CPC / UTM: `cpc_tracking`

**هدف**: ثبت ورودی‌های ترافیک پولی (CPC) با استفاده از پارامترهای `utm_*` در URL referrer.

**ورودی**:

- `ip`, `ref` (referrer URL).

**منطق**:

- Botها را مثل قبل از روی `user_agent` حذف می‌کند (توجه: در snippet دیده شده `user_agent` تعریف نشده، اما فرض می‌شود در نسخه‌ی کامل موجود است).
- با `parse_url` و `parse_str` query-stringهای URL را استخراج می‌کند.
- اگر `utm_source` خالی باشد → exit.
- اگر IP قبلاً در جدول `cpc_tracking` ثبت نشده:
  - یک رکورد جدید با:
    - `source = utm_source`,
    - `medium = utm_medium`,
    - `campaign = utm_campaign`,
    - `terms = [ { term: utm_term, time: now } ]`,
    - `count = 1`
    ذخیره می‌کند.
- اگر IP قبلاً ثبت شده:
  - `terms` را unserialize می‌کند، اگر `utm_term` تکراری است فقط `time` را آپدیت می‌کند، وگرنه term جدید اضافه می‌کند.
  - `count` را +۱ می‌کند.

این داده‌ها برای تحلیل کمپین‌های تبلیغاتی استفاده می‌شوند.

---

## ۹. لیست روزهای PLP: `load_plp_days`

**هدف**: تولید HTML برای **نوار انتخاب روزها** در صفحه لیست (PLP) یک محصول.

**ورودی**:

- `product_id`, `wp_is_mobile`.

**منطق**:

- `get_time` = timestamp شروع امروز (00:00).
- یک div اول با برچسب «امروز» (نمای موبایل/دسکتاپ متفاوت) رندر می‌کند.
- سپس برای ۲۱ روز بعدی:
  - هر روز یک div با `data-time` و `data-ezservice` ثبت می‌شود.
  - در موبایل: `jdate('j')` و `jdate('l')` (روز ماه و نام روز هفته).
  - در دسکتاپ: نام روز (`jdate('l')`)، روز ماه (`jdate('j')`), نام ماه (`jdate('F')`).
- خروجی HTML با `ob_get_clean()` گرفته و به صورت JSON برگردانده می‌شود.

---

## ۱۰. به‌روزرسانی تخفیف: `update_product_discount_data`

**هدف**: تنظیم/به‌روزرسانی `discount_data` برای یک محصول خاص.

**ورودی**:

- `product_id`, `discount_data` (object).

**منطق**:

- فقط:

```sql
UPDATE products_data SET discount_data = serialize(discount_data) WHERE product_id = <product_id>;
```

---

## جمع‌بندی

- `web-service.php` مرکز مدیریت **داده‌ی محصولات** و **لیست‌ها/سورت‌ها/فیلترها** در سیستم شماست:
  - همه‌ی صفحات فرانت‌اند که لیست اتاق‌ها/بازی‌ها را با شروط مختلف نشان می‌دهند، در نهایت به `sort_products_get` متکی‌اند.
  - import کامل/partial محصولات از منبع بیرونی (وردپرس/داشبورد) نیز از طریق `data_products_set` و دوستانش انجام می‌شود.
- توابع فرمت‌کننده (`standardization_products`, `standardization_products_html_swiper`, ...) یک لایه‌ی abstraction روی DB هستند تا API و HTML فرانت‌اند فقط با یک ساختار منسجم کار کنند.
- این فایل در کنار `reservation.php` و `saeed.php` سه‌گانه‌ی اصلی وب‌سرویس EscapeZoom را می‌سازد:
  - این فایل: «چه بازی‌هایی داریم و چطور نشانشان بدهیم؟»
  - آن دو فایل: «برای هر بازی، سانس‌ها چطور رزرو/مدیریت شوند؟»

