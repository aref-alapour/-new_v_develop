## EscapeZoom Core – Clean state (v3)

این سند خلاصهٔ وضعیت فعلی هستهٔ `escapezoom-core` بعد از تمیزکاری است؛ کمک می‌کند سریع ببینید چه ماژول‌هایی رسمی و فعال هستند و چه چیزهایی به‌عنوان legacy/غیرفعال نگه‌داری می‌شوند.

### ماژول‌های فعال در ورودی هسته

در فایل اصلی پلاگین `escapezoom-core.php`، بعد از بوت‌شدن Eloquent (`EZ_CORE_BOOTED = true`) این اجزا رجیستر می‌شوند:

- **Core\AssetManager**: مدیریت نسخه‌گذاری و لود استاتیک‌ها از `dist/` و `assets/vendor/`.
- **AdminHeartbeatDisable**: کاهش نویز Heartbeat در ادمین مطابق قوانین کارایی.
- **Core\AdminAppearance**: شخصی‌سازی‌های ظاهری ادمین (تمیز، بدون منطق کسب‌وکار).
- **Domain\EzDomainService**: تشخیص دامنهٔ اصلی/alias و اعمال سیاست `noindex,nofollow` روی دامنه‌های `.co`.
- **Games\***:
  - `LocationTaxonomy`, `PostType\EZ_Games_CPT`, `PostType\EZ_Games_DB`, `PostType\EZ_Games_Metaboxes`
  - `Admin\AdminBootstrap` (منوها، متاباکس‌ها، AJAX شامل `EzAddEntityAjax`)
  - `Models\*` برای محصولات، مکان‌ها، نوع بازی، ژانر، استایل، تگ، سفارش، اسلات و غیره.
- **API\EzQueryEndpoint / EzQueryRestController**: درگاه داده برای فرانت‌اند (`ez_query` و REST).
- **Blocks\BlocksBootstrap**: رجیستر بلاک‌های گوتنبرگ و لود Stencil bundles از `dist/js` و `dist/css`.
- **Scheduler\JobScheduler**: زمان‌بندی Jobها (با Action Scheduler در صورت وجود).
- **Brands\BrandBootstrap**: مدیریت برندها (CPT/مدل و صفحات ادمین).
- **Archives\ArchiveBootstrap**: ماژول آرشیو/دیکشنری و نقشهٔ آرشیو.
- **Redirects\RedirectManager / RedirectAdmin / RedirectSuggestions / RedirectImportExport**: ماژول ریدایرکت و لاگ 404.

### ماژول‌ها و کلاس‌های legacy / حذف‌شده

در نسخهٔ فعلی هسته، برخی کلاس‌های قدیمی که فقط برای تنظیمات مارکتینگ یا آزمایش‌های قبلی استفاده می‌شدند، به‌طور کامل از سورس هسته حذف شده‌اند تا سطح کد و API کوچک‌تر شود:

- **Tracking\TrackingSettings** (قبلاً صفحهٔ تنظیمات GTM/Mediaad و خروجی اسکریپت‌های ردیابی):  
  اکنون به‌طور کامل از هسته حذف شده است؛ در صورت نیاز به Tracking باید از پلاگین/سِت‌آپ جداگانه استفاده شود.
- **SEO\CanonicalManager** (قبلاً مدیریت Canonical per-URL و نگاشت `.co → .ir`):  
  از هسته حذف شده و مدیریت Canonical به تنظیمات SEO فعلی (افزونهٔ SEO یا تنظیمات سرور/تم) سپرده شده است.
- **Admin\AdminAssetCleanup** (نسخهٔ قدیمی cleanup ادمین):  
  این کلاس نیز حذف شده و تنها رفتار مرتبط، در `AdminHeartbeatDisable` و تنظیمات پیشنهادی `wp-config.php` پوشش داده می‌شود.

### مدل‌ها و جداول با استفادهٔ محدود

تمام مدل‌های Eloquent در `src/Modules/Games/Models` از نظر نام جدول با اسکیمای مرجع در `docs-v2/query.sql` و `database/entities/*.sql` هم‌تراز هستند. دو مدل زیر در حال حاضر فقط به‌عنوان لایهٔ دسترسی به جداول کمکی و بدون مصرف مستقیم در سرویس‌ها استفاده می‌شوند:

- **LastMinuteSlotsCache** → جدول `wp_ez_last_minute_slots_cache`
  - نقش: کش سانس‌های لحظه‌آخری برای صفحه/فید اختصاصی last-minute (فقط خواندنی).
  - وضعیت فعلی: جدول و مدل در اسکیمای دیتابیس تعریف شده‌اند اما هیچ سرویس فعالی در هسته از این کش استفاده نمی‌کند. این بخش به‌عنوان زیرساخت قابل‌استفاده برای پیاده‌سازی/بازگردانی فیچر لحظه‌آخری نگه داشته شده است.
- **ProductLookup** → جدول `wp_ez_product_lookup`
  - نقش: lookup برای فیلتر سریع بازی‌ها بر اساس شهر، نوع، مناطق، ژانر، استایل و برچسب‌ها.
  - وضعیت فعلی: جدول در اسکیمای دیتابیس تعریف شده و مدل با آن هم‌راستاست، اما کوئری‌های اصلی لیست بازی‌ها فعلاً به‌طور مستقیم از آن استفاده نمی‌کنند. این مدل برای استفادهٔ آینده (مثلاً ایندکس سریع فیلترها) آماده است.

در صورت تصمیم برای کوچک‌سازی شدید هسته، این دو مدل می‌توانند به یک ماژول feature-flagged یا برنچ جدا منتقل شوند؛ فعلاً به‌دلیل هماهنگی کامل با اسکیمای دیتابیس و هزینهٔ نگه‌داری پایین، در هسته باقی مانده‌اند.

### نکات دیپلوی و build (خلاصه)

برای سبک نگه‌داشتن هسته در پروداکشن بدون دست‌زدن به `vendor/`:

- در محیط build/production از `composer install --no-dev --optimize-autoloader` استفاده کنید تا پکیج‌های dev و اتولودر غیرضروری حذف شوند.
- در artifact دیپلوی، پوشه‌های سنگین غیرزمان‌اجرا مثل `vendor/**/tests`, `vendor/**/docs`, `vendor/**/examples` را در سطح CI/CD حذف/ exclude کنید.
- از استاتیک‌ها فقط `dist/` و `assets/vendor/` را دیپلوی کنید و لاگ‌ها/آرتیفکت‌های داخلی build مثل `assets/.stencil/.build/*` را وارد ریپو/دیپلوی نکنید (در `.gitignore` یا اسکریپت build آن‌ها را فیلتر کنید).

این سند را می‌توانید به‌روز کنید تا اگر ماژول legacy حذف یا فعال شد، تصویر تمیز از هسته همیشه در دسترس باشد.

