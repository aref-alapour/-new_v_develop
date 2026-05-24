# مستند معماری: گیت‌وی AJAX بدون بارگردانی کامل وردپرس و اکشن «برندها» (`brands.fragment`)

این سند **خلاصهٔ فنی کارهای انج‌شده روی استک EscapeZoom** است (mu-plugins `ez-ajax-gateway` و `ez_core`)، نه هستهٔ منتشرشدهٔ [WordPress.org](https://wordpress.org/). هدف: درک دقیق «چرا و چطور» بدون بارگذاری `wp-load.php` برای بعضی عملیات، و چه اشکالاتی در مسیر کشف و رفع شدند.

---

## ۱. چرا این معماری؟

- **مسئله:** صفحهٔ دایرکتوری برندها با HTMX هر بار صفحه‌ای از برندها را عوض می‌کند. اگر هر درخواست کل وردپرس (تم، پلاگین‌ها، hookها، autoload زیاد) را بالا بیاورد، هزینهٔ ثابت و پردازشی بالا می‌رود و مقیاس‌پذیری ضعیف می‌شود.

- **راه‌حل:** یک **endpoint اختصاصی** تحت مسیر وب مثل `/ajax` که:
  - **قبل از** اجرای `wp-load.php`، **اعتبار امضای HMAC، نانس، skew زمان، rate limit** را انجام می‌دهد؛
  - برای اکشن‌هایی که در رجیستری با `wp_level => 'none'` ثبت شده‌اند، **اصلاً وردپرس را بارگذاری نمی‌کند**؛
  - فقط لایهٔ دادهٔ سبک (`Illuminate\Database Capsule`) و ثابت‌های دیتابیس از روی `wp-config`/`secrets` را نیاز دارد.

این هم **سرعت بهتر برای UX** هم **سطح حمله کوچک‌تر**: امضاهای نامعتبر به MySQL برای نانس و امثال آن نمی‌رسند قبل از تأیید هش.

مرجوم کلی جریان: `dispatch.php` → `Gateway::handle()` (پکیج `ez-ajax-gateway`).

---

## ۲. قطعات اصلی پروژه

| قطعه | مسیر تقریبی | نقش |
|------|--------------|-----|
| نقطهٔ ورود وب | `mu-plugins/ez-ajax-gateway/dispatch.php` | تعریف `ABSPATH`، بار `secrets-bootstrap`، تلاش برای `Bootstrap::bootDataLayerOnly()`, فراخوان `Gateway::handle()` |
| رجیستری اکشن‌ها | `mu-plugins/ez-ajax-gateway/registry.php` | فقط همین آرایه معتبر است؛ اکشن خارج از آن → `UNKNOWN_ACTION` |
| گیت‌وی | `mu-plugins/ez-ajax-gateway/src/Gateway.php` | ترتیب: preflight → رجیستری → جداول gateway → تأیید امضا → ریت‌لیمت → ولیدیشن ورودی → بار شرطی WP → dispatch handler |
| پیکربندی درخواست | `mu-plugins/ez-ajax-gateway/src/Http/Request.php` | پارس JSON/form، `php://input`، خواندن `X-EZ-Action` یا `action` |
| بوت دیتابیس سبک ez_core | `mu-plugins/ez_core/src/Core/Bootstrap.php` | `bootDataLayerOnly()` فقط `CapsuleBoot::boot()`، بدون `add_action` |
| اتصال Capsule برای جداول WP | `mu-plugins/ez_core/src/Database/CapsuleBoot.php` | تنظیم connection نام `wordpress` | 
| اکشن لیست برندها (HTML) | `mu-plugins/ez_core/src/Modules/Brands/Actions/BrandsFragment.php` | کوئری به جداول `terms` / `term_taxonomy` / `termmeta` / پیوست‌ها، رندر شبکهٔ کارت + صفحه‌بندی |
| کلاینت مرورگر | `themes/escapezoom-v2/assets/js/lib/ez-ajax.js` | امضای درخواست، تبدیل `hx-get` گیت‌وی به **POST با JSON** و هدرهای امن |

---

## ۳. اکشن `brands.fragment` در رجیستری

تعریف (مفاهیم):

- **`handler`:** `EscapeZoom\Core\Modules\Brands\Actions\BrandsFragment::run`
- **`wp_level`:** `'none'` → بدون `wp-load.php`.
- **`output`:** `html` → پاسخ `text/html` (fragment برای HTMX با `swap="outerHTML"` روی `#brands-directory-swap`).
- **`inputs`:** `{ page: int|min:1|default:1 }`.
- **`rate`:** سقف برای IP و client.

یعنی کل منطق نمایش گرید و صفحه‌بندی در همان کلاس پی‌اده‌سازی شده و به hookهای وردپرس وصل نیست؛ عمداً ساده‌تر و سریع‌تر است (با تفاوت‌های آگاهانه نسبت به URL فیلترشدهٔ کامل WP — در خود فایل کلاس هم توضیح داده شده).

---

## ۴. کشف پیشوند جدول و ثابت‌های دیتابیس بدون WP

بدون بارگذاری وردپرس، شیء جهانی `$wpdb` وجود ندارد؛ بنابراین:

- **`secrets-bootstrap.php`** هنگام خواندن `wp-config.php`، با regex، **`$table_prefix`** را می‌خواند و به صورت **`EZ_AJAX_TABLE_PREFIX`** تمام‌وقت در دسترس است (مگر فایل اختصاصی secrets جلوتر آن را ست کرده باشد).
- ثابت‌های **`DB_*`** برای اتصال mysqli (nonce/rate-limit) و برای Capsule نیز بارگذاری می‌شوند.

Handlerهای برندها از همین پیشوند برای نام جداول استفاده می‌کنند (مثل `{prefix}term_taxonomy`).

---

## ۵. بارگذاری مطمئن کلاس بدون Composer dumpهای قدیمی

در `dispatch.php` بعد از `vendor/autoload`، اگر فایل `BrandsFragment.php` خواندنی باشد **`require_once`** می‌شود تا حتی در صورت عقب بودن کش autoload Composer، کلاس اکشن موجود باشد.

---

## ۶. لایهٔ داده: Capsule به‌جای wpdb در gateway

در مسیر بدون WP، دسترسی به `$wpdb->get_results` وجود ندارد؛ **`Illuminate Capsule`** (همان Laravel DB) برای driver `mysql` استفاده می‌شود تا `SELECT` پارامترشده زده شود.

### ۶.۱ اصلاح مهم اول: پارس کردن `DB_HOST` (میزبان پورت دار / سوکت)

**مشکل:** سرویس Laravel/PDO انتظار دارد **`host`** و **`port`** جدا باشند. اگر مانند بعضی تنظیمات وردپرس مقدار `DB_HOST` به شکل **`127.0.0.1:3306`** یا **`mysql`** با پورت باشد، ارسال رشتهٔ کامل تنها به فیلد `host` یک DSN نامعتبر به‌وجود می‌آورد؛ در حالی که در همان محیط، mysqli گیت‌وی (کلاس **`DbConnection`**) خودش `:` را تجزیه می‌کرد و وصل می‌شد.

**کار انجام‌شده در `CapsuleBoot`:** متد **`applyMysqlHostFlags`** مشابه منطق `DbConnection`:
- بدون `: ` → همان `host`
- `:عدد ` → جدا کردن `port`
- `:مسیرسوکت ` → ست کردن **`unix_socket`** برای DSN Laravel

به این ترتیب **یکپارچگی بین mysqli gateway و PDO Capsule** حفظ شد.

### ۶.۲ اصلاح مهم دوم: جفت بودن Charset و Collation (خطای MySQL `1253`)

**مشکل:** اگر در `wp-config` مقدار **`DB_CHARSET` برابر `utf8`** باشد، در MySQL مدرن این معمولاً به **`utf8mb3`** نگاشت می‌شود؛ اما اگر پیش‌فرض اتصال را **`utf8mb4_unicode_ci`** بگذاریم در حالی که جلسهٔ SQL روی مجموعهٔ کاراکتر **`utf8mb3`** مانده باشد، MySQL خطا می‌دهد:

```text
COLLATION 'utf8mb4_unicode_ci' is not valid for CHARACTER SET 'utf8mb3'
```

**کار انجام‌شده در `CapsuleBoot`:** متد **`resolveMysqlCollation`**:
- اگر **`DB_COLLATE` (یا معادل escapezo)** مقدار صریح دارد → همان را استفاده می‌کند (همان خط وردپرس).
- اگر خالی باشد → بر اساس `DB_CHARSET`:
  - شامل **`utf8mb4`** → `utf8mb4_unicode_ci`
  - **`utf8`** یا پیشوند **`utf8mb3`** → `utf8_unicode_ci` (سازگار با mb3)
  - نمونه‌های سادهٔ `latin1` / `latin2` → پیش‌فرض متداول هر کدام

**توصیهٔ استراتژیک:** مهاجرت دیتابیس وردپرس به **`utf8mb4`** (و collation مناسب) برای پشتیبانی کامل یونیکد؛ این کد تا قبل از آن، از تنظیمات قدیمی `utf8` بدون خطا عبور می‌کند.

---

## ۷. پیاده‌سازی `BrandsFragment` و تصمیم «SQL خام»

### ۷.۱ انگیزه

اولیهٔ کار با Query Builder و JOINهایی با نام مستعار احتمال **تضاد نام ستون‌ها، گرامر دایرکتور PDO، یا تفاوت رفتاری** ایجاد می‌کرد. برای **پیش‌بینی‌پذیری بالا در مسیر بدون WP** کوئری‌های اصلی به **`Capsule::connection(...)->select($sql, $bindings)`** (SQL پارامترشدهٔ دستی) منتقل شد.

### ۷.۲ اجزاء منطقی

1. **`countTermsInTaxonomy`** — شمار ردیف‌های `term_taxonomy` با `taxonomy = product_brand`.
2. **`fetchTermPage`** — پیجینیشن با `LIMIT`/`OFFSET` اعداد صحیح امن؛ مرتب‌سازی مطابق مد «محبوب» مشابه سمت تم (مثل `ORDER BY tt.count`).
3. **ستون reserve `count`:** نام ستون `count` برای MySQL حساس است؛ در `SELECT` و `ORDER BY` با **بکتیک** نوشته می‌شود و با alias **`ez_term_count`** خوانده می‌شود.
4. **`batchTermMeta`** — بارگذاری دسته‌جمعی `_thumbnail_id` و آدرس برند برای ترم‌ها.
5. **`batchAttachmentLargeSrcs`** — از `postmeta` و `posts` برای `_wp_attachment_metadata` و انتخاب سایز `large` نسبت به `upload_url_path` / `siteurl`.

### ۷.۳ گزینه‌ها بدون بارگذاری WP

کلاس‌ها و توابع مانند **`get_option`** یا **`wp_upload_dir`** اجرا نمی‌شوند؛ بنابراین **`optionString`** مستقیماً از جدول `options` برای کلیدهایی مثل `home`، `siteurl`، `permalink_structure`، `upload_url_path` خوانده می‌شود. مسیر پایهٔ صفحهٔ دایرکتوری با **`discoverBrandsDirectoryPageId`** از `postmeta` قالب **`page-brands.php`** کشف می‌شود و **`pagePermalinkSansQuery`** بدون **`get_permalink`** سلسله‌مراتب `post_name` را می‌سازد.

### ۷.۴ صفحه‌بندی و HTMX-Push

- **`buildPushUrlForPage`** URL مرورگر را برای تاریخچهٔ عقب‌جلو پاکسازی پارامترهای قبلی می‌کند.
- پیش از **`HX-Push-Url`** کاراکترهای کنترلی strip می‌شوند تا **`header()`** PHP به خطای نامعتبر پاسخ ندهد.

### ۷.۵ خطای کاربر در مقابل اشکال داخلی

- اگر خطا در **`try`** داخل **`run`** بیفتد: لاگ شامل نوع خطا، فایل/خط، و trace؛ به کاربر **پیام فارسی ثابت** داده می‌شود.
- اگر **`WP_DEBUG`** از `wp-config` (خوانده‌شده از bootstrap گیت‌وی) روشن باشد، **خلاصهٔ پیام انگلیسی دیتابیس/استثناء** به انتهای همان پیام HTML اضافه می‌شود تا در محیط توسعه بدون دسترسی فوری به لاگ سرور قابل تشخیص باشد.

**تفاوت با JSON `INTERNAL`:** اگر قبل از بازگشت `Response` handler از بیرون throwable پرتاب شود یا handler قابل فراخوانی نباشد، گیت‌وی پاسخ JSON استاندارد خطا می‌دهد؛ جعبهٔ قرمز فارسی یعنی **خود BrandsFragment خطا را گرفته و عمداً HTML خطا برگردانده**.

---

## ۸. کلاینت: `ez-ajax.js`

- کانونیکال امضا باید با سرور هم‌نام باشد: `METHOD|path|action|...|sha256_hex(body)` — برای درخواست‌های گیت‌وی معمولاً **بدنه JSON** تا هش قطعی باشد.
- برای لینک‌های HTMX با `hx-get` به `/ajax` رویداد `preventDefault` و **`ezFetch`** با **POST** انجام می‌شود؛ پارامتر `page` و غیر در JSON ادغام می‌شوند.

---

## ۹. خلاصهٔ باگ‌ها و رفع‌ها (به ترتیب کشف)

1. نیاز به **endpoint سریع** بدون بار کامل WP → `wp_level => none` برای `brands.fragment`.
2. **عدم دسترسی به `$wpdb`** → Capsule و ثابت‌های `DB_*` + پیشوند از `EZ_AJAX_TABLE_PREFIX`.
3. **DSN PDO با `host` ترکیبی** → پارس `DB_HOST` در `CapsuleBoot`.
4. **عدم هم‌خوانی collation با charset قدیمی `utf8`** → `resolveMysqlCollation`.
5. **پایداری کوئری** → SQL پارامترشده و بکتیک برای `count`.
6. **هدر `HX-Push-Url`** → حذف کاراکترهای کنترلی.
7. **توسعهٔ سریع‌تر** → نمایش متن خطا زمانی که `WP_DEBUG` روشن است.
8. **قابلیت لود کلاس** → `require_once` صریح `BrandsFragment.php` در dispatcher.

---

## ۱۰. امنیت (خلاصه)

- امضای HMAC با secret مشتق‌شدهٔ کوتاه‌عمر + نانس تک‌بار مصرف در MySQL.
- متد غیر از POST برای گیت‌وی رد می‌شود.
- بدنه و اندازه و شکل nonce/signature قبل از هر کار گران چک می‌شود.
- **اکشن فقط از رجیستری استاتیک** — بدون بار WP، افزونه‌ها نمی‌توانند اکشن جدید به گیت‌وی اضافه کنند (کاهش ریسک DoS سوء‌استفاده از فیلتر).

---

## ۱۱. پیش‌نیازها و بهره‌برداری

- `EZ_AJAX_SHARED_SECRET` و پارامترهای TTL/skew مطابق مستند موجود گیت‌وی.
- `wp-config` خوانده‌شونده؛ نصب `.htaccess` یا معادل rewrite برای رسیدن `dispatch.php` به `/ajax`.
- در فرانت **`__EZ_BOOT__`** شامل `ajax_url`, `sub_secret`, `kid`, `client_id`, `expires_at` و امثال آن طبق الگوی فعلی پروژه.
- کلاینت ویتی/باندل شامل **`wireHtmx()`** برای اعمال خودکار امضا.

---

## ۱۲. فایل‌های مرتبط برای مطالعهٔ عمیق‌تر

- `ez-ajax-gateway/README.md`
- `ez-ajax-gateway/dispatch.php`
- `ez-ajax-gateway/registry.php`
- `ez_core/src/Database/CapsuleBoot.php`
- `ez_core/src/Modules/Brands/Actions/BrandsFragment.php`
- `themes/escapezoom-v2/assets/js/lib/ez-ajax.js`

---

*آخرین به‌روزرسانی متن مستند هم‌سو با وضعیت پیاده‌سازی در مخزن پروژه EscapeZoom.*
